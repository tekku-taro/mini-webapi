<?php
use PHPUnit\Framework\TestCase;

use App\Models\User;
use App\Models\Session;
use Bootstrap\Config;
use Lib\Auth;
use Lib\JWT;
use Lib\AppCore\DB;

class AuthTest extends TestCase
{
    public static $user1;
    public static $user2;

    public static function setUpBeforeClass(): void
    {
        require_once('./vendor/autoload.php');
        Config::load('.env');
        require_once('./bootstrap/database.php');

        DB::beginTransaction();

        static::$user1 = User::create(['name'=>'hoge','password'=>"pass"]);
        static::$user2 = User::create(['name'=>'hoge2','password'=>"pass",'active'=>false]);
    }

    public static function tearDownBeforeClass(): void
    {
        DB::rollback();
    }

    public function setUp():void
    {
        // DB::beginTransaction();

        $this->auth = new Auth;
    }

    public function tearDown():void
    {
        
        // DB::rollback();
    }

    public function provideVerifyData()
    {
        // name=hoge,password=pass	verifyCredentials()	戻り値	same	[id=1,name=hoge,password=hashed,active=1,login_attempts=0]
        //          login_attempts in record	same	0
        // name=hoge,password=wrong	verifyCredentials()	戻り値	same	FALSE
        //         login_attempts in record	same	1
        // name=hoge,password=wrong	verifyCredentials()	戻り値	same	FALSE
        //         login_attempts in record	same	2
        // name=hoge,password=wrong	verifyCredentials()	戻り値	same	FALSE
        //         login_attempts in record	same	3
        // name=hoge,password=pass	verifyCredentials()	戻り値	same	FALSE
        //         login_attempts in record	same	0
        // user:[id=2,name=hoge,password=hashed,active=0,login_attempts=0]
        // name=hoge,password=pass	verifyCredentials()	戻り値	same	FALSE
        //         login_attempts in record	same	1

        return [
            ['hoge','pass',1,User::class,0],
            ['hoge','wrong',1,false,1],
            ['hoge','pass',1,false,0],
            ['hoge','wrong',1,false,1],
            ['hoge','wrong',1,false,2],
            ['hoge','wrong',1,false,3],
            ['hoge','pass',1,false,4],
            ['hoge2','pass',2,false,1],
        ];
    }

    /**
     * testVerifyCredentials function
     *
     * @dataProvider provideVerifyData
     *
     * @param string $name
     * @param string $pass
     * @param integer $user_id
     * @param mixed $return
     * @param integer $loginAttempts
     * @return void
     */
    public function testVerifyCredentials($name, $pass, $user_no, $return, $loginAttempts)
    {
        // $name = 'hoge';
        // $pass = 'pass';
        $result = $this->auth->verifyCredentials($name, $pass);

        if ($result === false) {
            $this->assertEquals($return, $result);
        } else {
            $this->assertInstanceOf(User::class, $result);
        }

        $varName = "user" . $user_no;
        // print(static::$$varName->id);
        $user = User::find(static::$$varName->id);
        $this->assertSame($loginAttempts, $user->login_attempts);
    }

    public function testValidateToken()
    {
        $newUser = User::create(['name'=>'hoge3','password'=>"pass",'role'=>'user']);

        $jwt = new JWT;
        $tokenData = $jwt->get($newUser);
        // print_r($tokenData);
        $token = $tokenData["access_token"];

        $session= Session::where('refresh_token', $tokenData['refresh_token'])->first();

        $jwtMock = \Mockery::mock('Lib\JWT');
        $jwtMock->shouldReceive('validate')
        ->andReturnUsing(function ($token, $checkAdmin) use ($session) {
            if ($checkAdmin) {
                return false;
            } else {
                return $session;
            }
        });

        $apiName = "UsersAPI";
        $result = $this->auth->validateToken($token, $apiName);

        $this->assertFalse($result);

        $apiName = "ZipcodesAPI";
        $result = $this->auth->validateToken($token, $apiName);

        $this->assertInstanceOf(Session::class, $result);
    }

    public function provideValidateRefreshTokenData()
    {
        $past = (new DateTime("-1 months"))->getTimestamp();
        $future = (new DateTime("+1 months"))->getTimestamp();

        $sessData =[
        ["user_id"=>"1", "jti"=>uniqid(), "access_token"=>"ACCESS", "refresh_token"=>"REFRESH", "refresh_token_expiry"=>$future, "invalidated"=>false],
        ["user_id"=>"1", "jti"=>uniqid(), "access_token"=>"ACCESS", "refresh_token"=>"REFRESH2", "refresh_token_expiry"=>$past, "invalidated"=>false],
        ["user_id"=>"1", "jti"=>uniqid(), "access_token"=>"ACCESS", "refresh_token"=>"REFRESH3", "refresh_token_expiry"=>$future, "invalidated"=>true],
        ["user_id"=>"1", "jti"=>uniqid(), "access_token"=>"ACCESS", "refresh_token"=>"REFRESH4", "refresh_token_expiry"=>$future, "invalidated"=>false],
        [],
        ];
        // sessData,access,refresh,return
        return [
            [$sessData[0],"ACCESS","REFRESH",Session::class],
            [$sessData[1],"ACCESS","REFRESH2",false],
            [$sessData[2],"ACCESS","REFRESH3",false],
            [$sessData[3],"WRONG_ACCESS","REFRESH4",false],
            [$sessData[4],"ACCESS","REFRESH5",false],
        ];
    }

    /**
     * testValidateRefreshToken function
     *
     * @dataProvider provideValidateRefreshTokenData
     * 
     * @param array $sessData
     * @param string $access
     * @param string $refresh
     * @param mixed $return session/false
     * @return void
     */
    public function testValidateRefreshToken($sessData,$access,$refresh,$return)
    {
        // print("test refresh");
        if(!empty($sessData)){
            $session = Session::create($sessData);
        }

        $result = $this->auth->validateRefreshToken($access, $refresh);

        if($result === false){
            $this->assertEquals($return,$result);
        }else{
            $this->assertInstanceOf($return,$result);
        }
    }
}

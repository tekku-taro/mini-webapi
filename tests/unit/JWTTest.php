<?php
use PHPUnit\Framework\TestCase;

use App\Models\User;
use App\Models\Session;
use Bootstrap\Config;
use Lib\JWT;
use Lib\AppCore\DB;

class JWTTest extends TestCase
{
    protected $user;
    protected $jwt;
    protected $token;
    
    public static function setUpBeforeClass(): void
    {
        require_once('./vendor/autoload.php');
        Config::load('.env');
        require_once('./bootstrap/database.php');
    }

    public function setUp():void
    {       
        $this->user = User::find(1); //taro
        $this->jwt = new JWT; 
        
        DB::beginTransaction();
    }

    public function tearDown():void
    {
        
        DB::rollback();
    }

    public function testGet()
    {
        $result = $this->jwt->get($this->user);

        $this->assertSame("you are authorized successfully.",$result['message']);
        $this->assertArrayHasKey("access_token",$result);
        $this->assertArrayHasKey("refresh_token",$result);
        $this->assertSame("success",$result['status']);
    
    }

    public function testCreateAccess()
    {
        $this->token = $this->jwt->createAccess($this->user);
        print($this->token);

        list($base64urlHeader,$base64urlPayload,$base64urlSignature) = explode(".", $this->token);

        $header = json_decode(base64_decode(strtr($base64urlHeader,"-_","+/")));
        $payload = json_decode(base64_decode(strtr($base64urlPayload,"-_","+/")));
        $signature = base64_decode(strtr($base64urlSignature,"-_","+/"));

        // print_r($header);print_r($payload);
        $expctedHeader = ['typ'=>'JWT', 'alg'=>'HS256'];
        $expectedPayload = ['name'=>'taro', 'admin'=>1];
        $this->assertSame($expctedHeader['typ'],$header->typ);
        $this->assertSame($expctedHeader['alg'],$header->alg);
        $this->assertSame('taro', $payload->name);
        $this->assertTrue($payload->admin);
        $this->assertObjectHasAttribute('exp',$payload);
        $this->assertObjectHasAttribute('jti',$payload);
        $this->assertObjectHasAttribute('iat',$payload);
        $this->assertObjectHasAttribute('sub',$payload);


    }

    public function testCreateRefresh()
    {
        $this->token = $this->jwt->createAccess($this->user);
        $property = $this->getProtectedProperty("jti");
        $property->setValue($this->jwt, uniqid());
        $this->refresh = $this->jwt->createRefresh($this->user, $this->token);

        print($this->refresh);

        $this->assertEquals("string",gettype($this->refresh));

        $count = Session::where("jti",$property->getValue($this->jwt))->count();

        $this->assertNotEmpty($count);

    }

    public function provideValidateData()
    {
        // token(alg:HS256,exp:未来),false	戻り値		Session                    
        // token(alg:HS256,exp:過去),false	戻り値		FALSE                   
        // token(alg:null,exp:未来),false	戻り値		FALSE                    
        // token(alg:HS256,exp:未来),true	戻り値		FALSE                     
        // token(alg:HS256,exp:未来,wrongsig),false	戻り値	FALSE

        $future = 60*15;
        $past = -60*15;

        return [
            ["HS256",$future,false, Session::class],
            ["HS256",$past,false, false],
            [null,$future,false, false],
            ["HS256",$future,true, Session::class],
            ["HS256",$future,false, false,true],
        ];
    }


    /**
     * testValidate function
     * 
     * @dataProvider provideValidateData
     *
     * @return void
     */
    public function testValidate($alg,$exp,$checkAdmin,$return,$changeSig = false)
    {
        $offsetProp = $this->getProtectedProperty("offset");
        $offsetProp->setValue($this->jwt, $exp);
        $algProp = $this->getProtectedProperty("algorithm");
        $algProp->setValue($this->jwt, ['label'=>$alg,'algo'=>"sha256"]);
        $this->token = $this->jwt->get($this->user)['access_token'];

        if($changeSig){
            $this->token .="wrong";
        }

        $result = $this->jwt->validate($this->token,$checkAdmin);

        if($return === false){
            $this->assertEquals($return, $result);
        }else{
            $this->assertInstanceOf($return,$result);
        }
        
    }


    public function testRefresh()
    {
        $property = $this->getProtectedProperty("jti");
        // $property->setValue($this->jwt, uniqid());
        $jti_before = '5e2548db0fc01';
        $session = Session::create([
            'user_id'=>1,'jti'=>$jti_before,
            'access_token'=>'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI1ZTI1NDhkYjBmYzAxIiwic3ViIjoxLCJpYXQiOjE1Nzk1MDE3ODcsImV4cCI6MTU3OTUwMjY4NywibmFtZSI6InRhcm8iLCJhZG1pbiI6dHJ1ZX0.uawR09x-GxBV8aBNkB7V4ycBkmgsppLjEIxYgkZovEk',
            'refresh_token'=>'?6Y???K?q_?E$$?^,?0٫))?vHQ?*',
            'refresh_token_expiry'=>'1579760987',
        ]);

            $result = $this->jwt->refresh($session->id);

        $this->assertSame("Token got refreshed successfully.",$result['message']);
        $this->assertArrayHasKey("access_token",$result);
        $this->assertArrayHasKey("refresh_token",$result);
        $this->assertSame("success",$result['status']);            

        $count = Session::where("jti",$jti_before)->count();
        $this->assertEmpty($count);
        
        $count = Session::where("jti",$property->getValue($this->jwt))->count();
        $this->assertNotEmpty($count);
    }

    protected function getProtectedMethod($methodName)
    {
        $reflection = new ReflectionClass(JWT::class);
        $method = $reflection->getMethod($methodName);

        $method->setAccessible(true);

        return $method;
    }
    protected function getProtectedProperty($propertyName)
    {
        $reflection = new ReflectionClass(JWT::class);
        $property = $reflection->getProperty($propertyName);

        $property->setAccessible(true);

        return $property;
    }
}

<?php
use PHPUnit\Framework\TestCase;

use Route\Request;
use App\Models\User;
use App\Models\Session;
use Bootstrap\Config;
use Lib\AppCore\Response;
use Lib\AppCore\ResourceAPI;
use Lib\Auth;
use Lib\JWT;
use Lib\AppCore\DB;

class ResourceAPITest extends TestCase
{
    public $request;
    public $token;
    public $beforeFilterMethod;

    public static function setUpBeforeClass(): void
    {
        require_once('./vendor/autoload.php');
        Config::load('.env');
        require('./bootstrap/database.php');

    }


    public function setUp():void
    {
        DB::beginTransaction();

        $requestReflection = new ReflectionClass(Request::class);
        $this->tokenProp = $requestReflection->getProperty('token');
        // アクセス許可
        $this->tokenProp->setAccessible(true);

        $_SERVER['REQUEST_METHOD'] = "POST";
        $this->request = new Request();

        //token
        $user = User::first();
        $jwt = new JWT;
        $tokenData = $jwt->get($user);
        $this->token = $tokenData['access_token'];


        $this->api = new ConcreteAPI($this->request);
    }

    public function tearDown():void
    {
        
        DB::rollback();
    }

    /**
     * testBeforeFilter function
     * 
     * @runInSeparateProcess
     *
     * @return void
     */
    public function testBeforeFilter()
    {

        $reflection = new ReflectionClass(ResourceAPI::class);
        $method = $reflection->getMethod('beforeFilter');
        // アクセス許可
        $method->setAccessible(true);  

        $action = "post";
        $arguments = "";
        // トークンあり
        $this->tokenProp->setValue($this->request, $this->token);
        $result = $method->invoke($this->api,$action, $arguments);
        // $result = $this->api->beforeFilter($action, $arguments);
        $this->assertTrue($result);

        // トークンなし
        $this->tokenProp->setValue($this->request, null);        
        $result = $method->invoke($this->api,$action, $arguments);
        $this->assertFalse($result);

        // 無効なトークン
        $this->tokenProp->setValue($this->request, $this->token."WRONG");      
        $result = $method->invoke($this->api,$action, $arguments);
        $this->assertFalse($result);


    }

    public function testFormatResponse()
    {
        $jwtMock = \Mockery::mock('overload:Lib\AppCore\Response');
        $jwtMock->shouldReceive('formatData')
        ->andReturnUsing(function ($modelName, $action, $success, $id, $response) {
            if($success){
                if($id){
                    return "action is success.";
                }else{
                    return "getIndex is success.";
                }
            }else{
                return "failure response";
            }
        });

        $reflection = new ReflectionClass(ResourceAPI::class);
        $method = $reflection->getMethod('formatResponse');
        // アクセス許可
        $method->setAccessible(true); 

        $response = new stdClass();
        $response->id = 1;
        $action = "post";
        $params = "";

        $result = $method->invoke($this->api,$response, $action, $params);
        $expected = "action is success.";
        $this->assertSame($expected,$result);

        $action = "getIndex";
        $result = $method->invoke($this->api,$response, $action, $params);
        $expected = "getIndex is success.";
        $this->assertSame($expected,$result);

        $response = ["failure"];
        $result = $method->invoke($this->api,$response, $action, $params);
        $expected = "failure response";
        $this->assertSame($expected,$result);


    }

}

class ConcreteAPI extends ResourceAPI
{

}
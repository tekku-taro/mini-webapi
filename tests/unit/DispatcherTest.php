<?php
use PHPUnit\Framework\TestCase;

use Route\Request;
use Bootstrap\Config;
use App\Models\User;
use Route\Dispatcher;
use Lib\JWT;
use Lib\AppCore\DB;

class DispatcherTest extends TestCase
{
    public $request;
    public $dispatcherRequest;
    public $fileIn;

    public static function setUpBeforeClass(): void
    {

        require_once('./vendor/autoload.php');
        Config::load('.env');
        require('./bootstrap/database.php');
    }    
    protected function setUp(): void
    {        
        DB::beginTransaction();
        

        $reflection = new ReflectionClass(Request::class);
        $this->fileIn = $reflection->getProperty('fileIn');
        // アクセス許可
        $this->fileIn->setAccessible(true);

        $reflection = new ReflectionClass(Dispatcher::class);
        $this->dispatcherRequest = $reflection->getProperty('request');
        // アクセス許可
        $this->dispatcherRequest->setAccessible(true);

        //token
        $user = User::first();
        $jwt = new JWT;
        $tokenData = $jwt->get($user);

        $postData = ['id'=>1,'name'=>'hoge','role'=>'user'];
        $path = dirname(__FILE__) . "/files/data.json";

        $_GET['url'] = 'admin/users/1';
        $_GET['key'] = 'value';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $accept = 'application/json';
        $_SERVER['HTTP_ACCEPT'] = $accept;
        $token= $tokenData['access_token'];
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        $this->request = new Request();
        // 値の上書き
        $this->fileIn->setValue($this->request, $path);
        $this->request->parseURL();

    }
    protected function tearDown(): void
    {        
        DB::rollback();
        
       unset($this->request);
    }    
    public function testAddAction()
    {
        $dispatcher = new Dispatcher($this->request);

        $dispatcher->addAction(["test","POST"],"posttest");
        $dispatcher->addAction(["test2","PUT"],"puttest");
        $dispatcher->addAction(["?","GETT"],"gettest");
        $request = $this->request;
        $dispatcher->addAction(["test3","DELETE"],function() use($request) {
            if(empty($request->params) or isset($request->params['page']) ){
                return "getIndex";
            }else{
                return "get";
            }
        });

        $expected=['test@POST'=>"posttest","test2@PUT"=>"puttest",
        "?@GETT"=>"gettest","test3@DELETE"=>function() use($request) {
            if(empty($request->params) or isset($request->params['page']) ){
                return "getIndex";
            }else{
                return "get";
            }
        }];

        foreach ($expected as $key => $action) {
            $this->assertEquals($action,$dispatcher->actionRoutes[$key]);
        }

    }

    public function testConstruct()
    {
        $dispatcher = new Dispatcher($this->request);
        $dispatchRequest = $this->dispatcherRequest->getValue($dispatcher);
        $this->assertInstanceOf(Request::class,$dispatchRequest);
        $this->assertEquals($this->request,$dispatchRequest);   


    }

    public function testMatchAction()
    {
        $dispatcher = new Dispatcher($this->request);
        
        $reflection = new ReflectionClass($dispatcher);
        $method = $reflection->getMethod('matchAction');

        // アクセス許可
        $method->setAccessible(true);

        // メソッド実行
        $result = $method->invoke($dispatcher,"users","POST");        
        $expected = "post";
        $this->assertSame($expected,$result);
        // メソッド実行
        $result = $method->invoke($dispatcher,"sessions","POST");        
        $expected = "login";
        $this->assertSame($expected,$result);
        // メソッド実行
        $result = $method->invoke($dispatcher,"zipcodes","GET");        
        $expected = "get";
        $this->assertSame($expected,$result);
        // メソッド実行
        $result = $method->invoke($dispatcher,"sessions","PUT");        
        $expected = "refresh";
        $this->assertSame($expected,$result);


    }


    public function testGetNamespace()
    {
        $dispatcher = new Dispatcher($this->request);
        
        $reflection = new ReflectionClass($dispatcher);
        $method = $reflection->getMethod('getNamespace');

        // アクセス許可
        $method->setAccessible(true);

        // メソッド実行
        $result = $method->invoke($dispatcher);
        
        $expected = "App\\Api\\Admin\\";
        $this->assertSame($expected,$result);


    }

    /**
     * testSend
     * 
     * @runInSeparateProcess
     * 
     * @return void
     */
    public function testSend()
    {
        $data = ['id'=>1,'name'=>'hoge','role'=>'admin'];
        
        $apiMock = \Mockery::mock('App\Api\Admin\UsersAPI');
        $apiMock->shouldReceive('postAction')->once();
        

        $dispatcher = new Dispatcher($this->request);
        
        $expected = json_encode($data);

        // ob_start();
        $result = $dispatcher->send();
        // $json = ob_get_clean();

        $this->assertNull($result);
        

    }

}
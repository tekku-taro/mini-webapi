<?php
use PHPUnit\Framework\TestCase;

use Route\Request;
use Bootstrap\Config;
use Lib\AppCore\API;
use Lib\JWT;
use Lib\AppCore\DB;
use App\Models\User;

class APITest extends TestCase
{
    public $request;
    public $fileIn;
    public static function setUpBeforeClass(): void
    {
        require_once('./vendor/autoload.php');
        Config::load('.env');
    }

    public function setUp():void
    {
        $reflection = new ReflectionClass(Request::class);
        $this->fileIn = $reflection->getProperty('fileIn');
        // アクセス許可
        $this->fileIn->setAccessible(true);
      
        $path = dirname(__FILE__) . "/files/data.json";

        $_GET['url'] = 'admin/users/1';
        $_GET['key'] = 'value';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $accept = 'application/json';
        $_SERVER['HTTP_ACCEPT'] = $accept;

        $this->request = new Request();
        // 値の上書き
        $this->fileIn->setValue($this->request, $path);
        $this->request->parseURL();
    }

    protected function tearDown(): void
    {
        // DB::rollback();
        
        unset($this->request);
    }

    /**
     * test__call
     *
     * @runInSeparateProcess
     *
     * @return void
     */
    public function test__call()
    {
        $api = new extAPI($this->request);
        $name = "someAction";
        // resonse data
        $responseData = ['action'=>'some with arg_value'];
        
        
        $expected = json_encode($responseData);
        
        // メソッド実行
        ob_start();
        $result = $api->__call($name, []);
        $json = ob_get_clean();

        $this->assertEquals($expected, $json);
    }

    public function testCallMethod()
    {
        $api = new extAPI($this->request);
        $expected = ['action'=>'some with arg_test'];

        $mock = \Mockery::mock($api)
        ->shouldAllowMockingProtectedMethods();

        $action = "some";
        $params =["key"=>"test"];
        // メソッド実行

        $result = $mock->callMethod($action, $params);

        $this->assertSame($expected, $result);
    }

    public function testDivideParamsIntoTwo()
    {
        $api = new extAPI($this->request);
        $reflection = new ReflectionClass($api);
        $method = $reflection->getMethod('divideParamsIntoTwo');

        // アクセス許可
        $method->setAccessible(true);

        // メソッド実行
        $action = "afterFilter";
        $params =['name'=>'Hoge','arguments'=>'1234','extra'=>'value'];
        $result = $method->invoke($api, $action, $params);
        
        $expected = [['Hoge','1234'],['extra'=>'value']];
        $this->assertSame($expected, $result);
        
        $action = "afterFilter";
        $params =['extra'=>'value'];
        $result = $method->invoke($api, $action, $params);
        
        $expected = [[null,null],['extra'=>'value']];
        $this->assertSame($expected, $result);
    }


    /**
     * testSendBack
     *
     * @runInSeparateProcess
     *
     * @return void
     */
    public function testSendBack()
    {
        $api = new extAPI($this->request);
        $reflection = new ReflectionClass($api);
        $method = $reflection->getMethod('sendBack');

        // アクセス許可
        $method->setAccessible(true);

        // resonse data
        $responseData = ['msg'=>'success'];
 
        
        $expected = json_encode($responseData);
        
        ob_start();
        $result = $method->invoke($api, $responseData);
        $json = ob_get_clean();

        $this->assertEquals($expected, $json);
    }
}

class extAPI extends API
{
    public function some($key)
    {
        return ['action'=>'some with arg_'.$key];
    }
}

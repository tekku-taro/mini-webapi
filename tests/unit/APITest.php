<?php
use PHPUnit\Framework\TestCase;

use Route\Request;
use Bootstrap\Config;
use Lib\AppCore\API;
class APITest extends TestCase
{
    public $request;
    public $fileIn;    
    public static function setUpBeforeClass(): void
    {

        require('./vendor/autoload.php');
        Config::load('.env');
    }    

    public function setUp():void
    {
        $reflection = new ReflectionClass(Request::class);
        $this->fileIn = $reflection->getProperty('fileIn');
        // アクセス許可
        $this->fileIn->setAccessible(true);

        $postData = ['id'=>1,'name'=>'hoge','role'=>'user'];
        $path = dirname(__FILE__) . "/files/data.json";

        $_GET['url'] = 'admin/users/1';
        $_GET['key'] = 'value';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $accept = 'application/json';
        $_SERVER['HTTP_ACCEPT'] = $accept;
        $token= "awefjiawepfoi";
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        $this->request = new Request();
        // 値の上書き
        $this->fileIn->setValue($this->request, $path);
        $this->request->parseURL();
    }

    public function testCallMethod()
    {
        $message = "[afterFilter]";

        $mock = \Mockery::mock(API::class)->makePartial()
        ->shouldAllowMockingProtectedMethods();        
        // afterFilter() のモックメソッド定義
        $mock->shouldReceive('afterFilter')
        ->andReturn($message);

        $action = "afterFilter";$params =[];
        // メソッド実行

        $result = $mock->callMethod($action,$params);

        $this->assertSame($message,$result);
    }

    public function testCreateMethodParams()
    {
        $api = new extAPI($this->request);
        $reflection = new ReflectionClass($api);
        $method = $reflection->getMethod('createMethodParams');

        // アクセス許可
        $method->setAccessible(true);

        // メソッド実行
        $action = "afterFilter";
        $params =['action'=>'Hoge','arguments'=>'1234','extra'=>'value']; 
 
        $result = $method->invoke($api,$action,$params);
        
        $expected = ['Hoge','1234'];
        $this->assertSame($expected,$result);

    }
 
}

class extAPI extends API
{

}


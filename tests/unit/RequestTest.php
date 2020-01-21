<?php
use PHPUnit\Framework\TestCase;

use Route\Request;
use Bootstrap\Config;
class RequestTest extends TestCase
{
    public $property;
    public static function setUpBeforeClass(): void
    {

        require_once('./vendor/autoload.php');
        Config::load('.env');
    }

    public static function tearDownAfterClass(): void
    {
        unset($_SERVER['HTTP_ACCEPT']);
        unset($_SERVER['HTTP_AUTHORIZATION']);
        unset($_SERVER['REQUEST_METHOD']);  
    }
    protected function setUp(): void
    {
        $reflection = new ReflectionClass(Request::class);
        $this->property = $reflection->getProperty('fileIn');
        // アクセス許可
        $this->property->setAccessible(true);
    }

    protected function tearDown(): void
    {
       
    }

    public function testCheckUrlAndMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['url'] = 'users/1';

        $request = new Request();

        $this->assertSame('users/1',$request->url);
        $this->assertSame('GET',$request->method);

        $_SERVER['REQUEST_METHOD'] = 'DELETE';

        $request = new Request();

        $this->assertSame('DELETE',$request->method);
        
    }
    public function testCheckOtherProperties()
    {
        $postData = ['id'=>1,'name'=>'hoge','role'=>'user'];
        $path = dirname(__FILE__) . "/files/data.json";

        $_GET['url'] = 'admin/users/1';
        $_GET['key'] = 'value';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $accept = 'json';
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $token= "awefjiawepfoi";
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;        


        $request = new Request();
        // 値の上書き
        $this->property->setValue($request, $path);
        $request->parseURL();            
        print_r($request);
        $this->assertSame(['id'=>1,'key'=>'value'],$request->params);
        $this->assertSame('POST',$request->method);
        $this->assertSame('users',$request->api);
        $this->assertSame($postData,$request->data);
        $this->assertTrue($request->isAdmin);
        $this->assertSame($token,$request->token);
        $this->assertSame($accept,$request->accept);
        
    }

}

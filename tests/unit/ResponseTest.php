<?php
use PHPUnit\Framework\TestCase;

use Bootstrap\Config;
use Lib\AppCore\Response;

class ResponseTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {

        require_once('./vendor/autoload.php');
        Config::load('.env');
    }

    /**
     * testResponseSentInFormat function
     *
     * @runInSeparateProcess
     * 
     * @return void
     */
    public function testResponseSentInFormat()
    {
        $data = ['id'=>1,'name'=>'hoge','role'=>'admin'];
        
        $expected = json_encode($data);
        ob_start();
        Response::json($data);
        $json = ob_get_clean();

        $this->assertSame($expected,$json);

        $data = ['id'=>1,'name'=>'hoge','role'=>'admin'];
        
        $expected = '<?xml version="1.0"?>'
        .'<data><id>1</id><name>hoge</name>'.
        '<role>admin</role></data>';
        ob_start();
        Response::xml($data);
        $xml = ob_get_clean();
        $xml = preg_replace("/\s+/", "", $xml);
        $expected = preg_replace("/\s+/", "", $expected);
        // $this->assertEquals(200, http_response_code());
        $this->assertSame($expected,$xml);
    }

}
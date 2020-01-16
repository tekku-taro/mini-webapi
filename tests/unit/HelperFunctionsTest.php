<?php
use PHPUnit\Framework\TestCase;

class HelperFunctionsTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
    }

    public static function tearDownAfterClass(): void
    {
    }
    protected function setUp(): void
    {
    }

    protected function tearDown(): void
    {
    }
    public function testGetTableName()
    {
        $string = "TableName";
        $result = getTableName($string);
        $expected = "table_names";
        $this->assertSame($expected, $result);
    }
    public function testGetAPIName()
    {
        $string = "hoge_names";
        $result = getAPIName($string);
        $expected = "HogeNamesAPI";
        $this->assertSame($expected, $result);
    }

    public function testIsJson()
    {
        $string = "[name,pass]";
        $result = isJson($string);
        $this->assertFalse($result);

        $string = '{"name":"hoge","pass":"234sdf"}';
        $result = isJson($string);
        $this->assertTrue($result) ;
    }

    public function testXmlToArray()
    {
        $xml = "[name,pass]";
        $result = xmlToArray($xml);
        $this->assertFalse($result);
        
        $xml = '<?xml version="1.0" ?><request><data>
        <name>hoge</name>
        <count>2</count></data>
        </request>';
        $expected = ['data'=>['name'=>'hoge','count'=>'2']];
        $result = xmlToArray($xml);
        // print_r($result);
        $this->assertSame($expected, $result) ;
    }

    public function testJsonToArray()
    {
        $json = '{"data":{"name":"hoge","count":2}}';
        $result = jsonToArray($json);
        $expected = ['data'=>['name'=>'hoge','count'=>2]];
        $this->assertSame($expected, $result) ;
    }


    public function testArrayToXML()
    {
        $array = ["user"=>[["name"=>"hoge","count"=>2],["name"=>"hoge2","count"=>4]]];
        $result = arrayToXML($array);
        $expected = '<?xml version="1.0" ?><data>
        <user><name>hoge</name>
        <count>2</count>
        </user>
        <user><name>hoge2</name>
        <count>4</count>
        </user>
        </data>';
        $result = preg_replace("/\s+/", "", $result);
        $expected = preg_replace("/\s+/", "", $expected);
        $this->assertSame($expected, $result) ;

        $array = ["user"=>[1,2,3,4]];
        $result = arrayToXML($array);
        $expected = '<?xml version="1.0" ?><data>
        <user><item>1</item></user>
        <user><item>2</item></user>
        <user><item>3</item></user>
        <user><item>4</item></user>
        </data>';
        $result = preg_replace("/\s+/", "", $result);
        $expected = preg_replace("/\s+/", "", $expected);
        $this->assertSame($expected, $result) ;
    }
}

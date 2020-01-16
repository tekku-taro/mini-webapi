<?php
use PHPUnit\Framework\TestCase;

use Bootstrap\Config;

class ConfigTest extends TestCase
{
    public static $property;
    public static function setUpBeforeClass(): void
    {
        require("./bootstrap/Config.php");
        $reflection = new ReflectionClass(Config::class);
        static::$property = $reflection->getProperty('data');

        // アクセス許可
        static::$property->setAccessible(true);        
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
    public function testSet()
    {
        Config::set('key1', "value1");

        // 値の取得
        $data = static::$property->getValue(Config::class);

        $this->assertSame("value1",$data['key1']);

    }
    public function testGet()
    {
        Config::set('key2', "value2");        
        // 値の上書き
        // static::$property->setValue(Config::class, ["key2"=>"value2"]);

        $this->assertSame("value2",Config::get("key2"));        
        $this->assertSame(null,Config::get("key3"));        
    }

    public function testLoad()
    {
        print(dirname(__FILE__));
        $path = "./tests/unit/files/.env";
        Config::load($path);
        // 値の取得
        $data = static::$property->getValue(Config::class);

        print_r($data);

        $this->assertSame("taro/webapi_fw",$data['APP_NAME']);        
        $this->assertSame("development",$data['APP_ENV']);        
        $this->assertSame("localhost",$data['DB_HOST']);        
        $this->assertSame("root",$data['DB_USER']);        

    }
}

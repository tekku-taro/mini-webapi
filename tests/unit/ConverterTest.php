<?php
use PHPUnit\Framework\TestCase;

use Engine\Conversion\Converter;
use Engine\FileManager\TemplateManager;
use Bootstrap\Config;

class ConverterTest extends TestCase
{
    protected $fileManager;

    public static function setUpBeforeClass(): void
    {
        require_once('./vendor/autoload.php');
        Config::load('.env');
        require_once('./bootstrap/database.php');

        // 各種パス定数の定義
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
            define('ROOT', dirname(__DIR__, 2));
            define('ENGINE', ROOT . DS . "console/template_engine");
        }
    }

    protected function setUp(): void
    {
        $this->fileManager = new TemplateManager(ENGINE . '/templates');
    }

    protected function tearDown(): void
    {
    }


    public function DataForConvert()
    {
        // $content,$vars,$template model/api,$return

        return
        [
            ['{% include "/somefile" %}',[],"api","\r\necho \"somefile content\";\r\n"],
            ['{% include "/otherfile" %}',[],"api",''],
            ["{% for person in users %}\r\n{{ person }} is selected.\r\n{% endfor %}",
            ["users"=>["user1","user2","user3"]],"api",
            "user1 is selected.\r\nuser2 is selected.\r\nuser3 is selected.\r\n"],
            ["{{ var1 }}",["var1"=>"hoge"],"api",'hoge'],
            ["{{ person.name }} is {{ person.age }} years old",["person"=>["name"=>"hoge","age"=>18]],"api",
            'hoge is 18 years old'],
            ["{% if 2 % 2 == 0 %}\r\nprint(\"true\");\r\n{% endif %}",[],"api",
            'print("true");'],
            ['{% if 4 % 4 == 1 %}
            print("none");
            {% endif %}',[],"api",''],
            ["{% if 2 % 2 == 0 %}\r\nprint(\"true\");\r\n{%else%}\r\nprint(\"none\");\r\n{% endif %}",[],"api",
            'print("true");'],
            ["{% if 4 % 4 == 1 %}\r\nprint(\"none\");\r\n{%else%}\r\nprint(\"false\");\r\n{% endif %}",[],"api",
            'print("false");'],
            ["{% if getVar == \"user\" %}\r\nprint(\"getVar == user\");\r\n{% endif %}",["getVar"=>"user"],"api",
            'print("getVar == user");'],
            ["{% if tag.name == \"h1\" %}\r\nprint(\"tag.name == h1\");\r\n{% endif %}",["tag"=>["name"=>"h1"]],"api",
            'print("tag.name == h1");'],
            ["{% if modelName == \"wrongmodel\" %}\r\nprint(\"none\");\r\n{%else%}\r\nprint(\"modelName != wrongmodel\");\r\n{% endif %}",["modelName"=>"Zipcode"],"api",
            'print("modelName != wrongmodel");'],
            ["{% if age > 15 %}\r\nYou are over the age of 15!\r\n{% endif %}",["age"=>16],"api",
            'You are over the age of 15!'],
            ["{% for user in users %}\r\n{{ user.name }} is {{ user.age }} years old.\r\n{% endfor %}",
            ["users"=>[
                ["name"=>"user1","age"=>19],
                ["name"=>"user2","age"=>20],
                ["name"=>"user3","age"=>21],
                ],
            ],
            "api",
            "user1 is 19 years old.\r\nuser2 is 20 years old.\r\nuser3 is 21 years old.\r\n"],
            ['{{ fillable }}',["modelName"=>'Zipcode'],"model","'id', 'zipcode', 'address'"],
            ['{{ rules }}',["modelName"=>'Zipcode'],"model",
            "'id'=>[],\r\n            'zipcode'=>[],\r\n            'address'=>[]"],
        ];
    }

    /**
     * testConvert
     *
     * @dataProvider DataForConvert
     *
     * @return void
     */
    public function testConvert($content, $vars, $template, $expected)
    {
        $converter = new Converter($content, $vars, $template, $this->fileManager);
        $result = $converter->convert();

        $this->assertEquals($expected, $result);
    }
}

<?php
use PHPUnit\Framework\TestCase;

use Lib\Validator;
use App\Models\User;
use Bootstrap\Config;

class ValidatorTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {

        require('./vendor/autoload.php');
        Config::load('.env');
        require('./bootstrap/database.php');
    }

    protected function setUp(): void
    {
    }

    protected function tearDown(): void
    {
    }
    
    public function testAddValidations()
    {
        $rules = ['name'=>['require','min'=>4,'max'=>5,'int','invalid'],
                  'password'=>['require','password','len'=>5,'string']];

        $this->validator = new Validator($rules);

        $expected =['name'=>['require','min'=>4,'max'=>5,'int'],
        'password'=>['require','password','len'=>5,'string']];
        $this->assertEquals($expected, $this->validator->rules);
    }

    public function DataForvalidateColumn()
    {
        return
            [
            ['password','ase33',[]],
            ['password',"",["password"=>["password は必須項目です。","password はパスワードの書式でありません。","password の文字数は 5 文字です。"]]],
            ['password',"awfwaefe", ["password"=>["password の文字数は 5 文字です。"]]],
            ['password',234, ["password"=>["password の文字数は 5 文字です。","password が文字列でありません。"]]],
            ['email',"hoge", ["email"=>["email はemailの形式でありません。"]]],
            ['password',"あああいい", ["password"=>["password はパスワードの書式でありません。"]]],
            ['name',"hog", ["name"=>["name は 4 文字以上でなければいけません。"]]],
            ['name',"hogehoge", ["name"=>["name は 5 文字以下でなければいけません。"]]],
            ['active',"hoge", ["active"=>["active がブール型(True/False)でありません。"]]],
            ['zipcode',"10123432", ["zipcode"=>["zipcode は郵便番号の書式(000-0000)でありません。"]]],
            ['role',"value1", ["role"=>["role はリストに含まれている値( admin,user )でなければいけません。"]]],
            ['other',5, ["other"=>["other はカスタム関数を満たす必要があります。"]]],
            ['id',1, ["id"=>["id の値 1 は既に使われています。"]]],
            ['column1',"hoge", ["column1"=>["column1 が数値型でありません。"]]],
            ['column2',"1.2", ["column2"=>["column2 が整数型でありません。"]]],
        ];
    }

    /**
     * testValidateColumn
     *
     * @dataProvider DataForvalidateColumn
     *
     * @return void
     */
    public function testValidateColumn($column, $value, $expected)
    {
        $rules = ['name'=>['require','min'=>4,'max'=>5,'string'],
                  'password'=>['require','password','len'=>5,'string'],
                  'email'=>['email'],'zipcode'=>['zipcode'],'active'=>['bool'],
                  'role'=>['list'=>['admin','user']],
                  'other'=>['custom'=>function ($value) {
                      return $value %2 == 0;
                  }],
                  'id'=>['unique'],
                  'column1'=>['num'],
                  'column2'=>['int'],
                ];

        $this->validator = new Validator($rules, User::class);
        
        $columnRules = $this->validator->rules[$column];
        $this->validator->validateColumn($column, $columnRules, $value);
        // $expected = [];
        $this->assertEquals($expected, $this->validator->errors);
    }

    public function testValidate()
    {
        $rules = ['name'=>['require','min'=>4,'max'=>5,'string'],
                  'password'=>['require','password','len'=>5,'string'],
                  'email'=>['email'],'zipcode'=>['zipcode'],'active'=>['bool'],
                  'role'=>['list'=>['admin','user']],
                  'other'=>['custom'=>function ($value) {
                      return $value %2 == 0;
                  }]
                ];

        $this->validator = new Validator($rules);
        
        $data =['name'=>'hoge','password'=>'fwe23'];
        $errors = $this->validator->validate($data);
        $expected = [];
        $this->assertEquals($expected, $errors);
        
        $data =['name'=>34,'password'=>'fwesf23'];
        $errors = $this->validator->validate($data);

        $expected = ["name"=>["name は 4 文字以上でなければいけません。","name が文字列でありません。"],
                     "password"=>["password の文字数は 5 文字です。"]];

        $this->assertEquals($expected, $errors);
    }
}

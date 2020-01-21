<?php
use PHPUnit\Framework\TestCase;

use App\Models\Zipcode;
use Bootstrap\Config;
use Lib\AppCore\DB;

class ModelTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once('./vendor/autoload.php');
        Config::load('.env');
        require_once('./bootstrap/database.php');
    }

    protected function setUp(): void
    {        
        DB::beginTransaction();
        

        $this->model = new Zipcode();
        $this->model->numPerPage = 2;
        //insert zipcode records
        Zipcode::insert([
            ['zipcode'=>'111-0001','address'=>'address0'],
            ['zipcode'=>'111-0001','address'=>'address1'],
            ['zipcode'=>'111-0001','address'=>'address2'],
            ['zipcode'=>'111-0003','address'=>'address3'],
            ['zipcode'=>'111-0004','address'=>'address4'],
            ['zipcode'=>'111-0005','address'=>'address5'],
        ]);

    }

    protected function tearDown(): void
    {        
        DB::rollback();
        
    }

    public function testGetPageRecords()
    {
        $page = 2;
        $params = ['zipcode'=>'111-0001'];
        $result = $this->model->getPageRecords($page, $params)->first();
        $expected = "address2";
        $this->assertEquals($expected,$result->address);        
    }

    public function testGetFromParams()
    {
        $params = ['zipcode'=>'111-0001'];

        $result = $this->model->getFromParams($params);
        $this->assertInstanceOf(Zipcode::class,$result->first());
        $this->assertEquals(3, $result->count());        
    }

    public function testCreateWhereData()
    {
        $params = ['col1'=>1,'col3'=>3,'col4'=>4];
        $fillable = ['col1','col2','col3'];
        $result = $this->model->createWhereData($params, $fillable);
        $expected = ['col1'=>1,'col3'=>3];
        $this->assertEquals($expected, $result);
    }

    public function testValidate()
    {

        $data = ["zipcode"=>"127-0001","address"=>"fwe23"];
        $errors = [];
        $result = $this->model->validate($data);

        $this->assertSame($errors, $result);

        $data = ["zipcode"=>"127001","address"=>"fwe23"];
        $errors = ['zipcode'=>['zipcode の文字数は 8 文字です。']];
        $result = $this->model->validate($data);

        $this->assertSame($errors, $result);
    }



}
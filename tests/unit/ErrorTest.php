<?php
use PHPUnit\Framework\TestCase;

use Lib\AppCore\Response;
use Bootstrap\Config;
use Lib\Error;

class ErrorTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once('./vendor/autoload.php');
        // 各種パス定数の定義
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
            define('ROOT', dirname(__DIR__));
            define('LOG', ROOT . DS . "logs");
        }
    }


    public function testErrorHandlerReturnNull()
    {
        error_reporting(0);
        $result = Error::errorHandler(1, "", null, null);

        $this->assertNull($result);

        error_reporting(E_ERROR);
    }

    public function setUp():void
    {
        $this->error = new Error;
    }
    /**
     * testErrorThrowErrorException
     *
     * @return void
     */
    public function testErrorThrowErrorException()
    {
        $this->expectException(ErrorException::class);

        $result = Error::errorHandler(1, "", null, null);
    }

    /**
     * testExceptionHandlerSendMessageForProd function
     *
     * @runInSeparateProcess
     *
     * @return void
     */
    public function testExceptionHandlerSendMessageForProd()
    {
        Config::load('./tests/unit/files/prod.logOff.env');

        $e = new ErrorException("test message", 404, 1, "file", 100);
        $errorData = ['message'=>'An error has occurred.'];


        ob_start();
        $this->error->exceptionHandler($e);
        $json = ob_get_clean();
        $expected = json_encode($errorData);
        $this->assertSame($expected, $json);
    }

    /**
     * testExceptionHandlerSendMessageForDev function
     *
     * @runInSeparateProcess
     *
     * @return void
     */
    public function testExceptionHandlerSendMessageForDev()
    {
        Config::load('./tests/unit/files/dev.logOff.env');

        $e = new ErrorException("test message", 404, 1, "file", 100);
        $errorData = [
            'message'=>$e->getMessage(),
            'code'=>$e->getCode(),
            'file'=>$e->getFile(),
            'line'=>$e->getLine(),
            'trace'=>$e->getTraceAsString(),
        ];

        ob_start();
        $this->error->exceptionHandler($e);


        $json = ob_get_clean();
        $expected = json_encode($errorData);
        $this->assertSame($expected, $json);
    }


    public function testErrorLog()
    {
        $e = new ErrorException("test message", 404, 1, "file", 100);

        $reflection = new ReflectionClass(Error::class);
        $method = $reflection->getMethod('errorLog');

        $method->setAccessible(true);

        $result = $method->invoke($this->error, $e);
        $expected = true;

        $this->assertSame($expected, $result);
    }
}

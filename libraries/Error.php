<?php
namespace Lib;

use Lib\AppCore\Response;
use Bootstrap\Config;
use DateTime;
use Exception;

class Error
{
    public static function errorHandler($severity, $message, $file, $line)
    {
        if (!(error_reporting() & $severity)) {
            // このエラーコードが error_reporting に含まれていない場合
            return;
        }
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public function exceptionHandler($e)
    {
        if (!empty($e->getCode())) {
            $code = $e->getCode();
        } else {
            $code = 500;
        }
        
        if (Config::get('LOG') === 'On') {
            static::errorLog($e);
        }

        if (Config::get('APP_ENV') === 'production') {
            $response = ['message'=>'An error has occurred.'];
        } else {
            $response = static::generateErrorData($e, $code);
        }

        Response::json($response, $code);
    }

    protected function errorLog($e)
    {
        ini_set('log_errors', 'On');
        
        $errorMsg = "Uncaught Exception: '". get_class($e) . "' on line ".$e->getLine(). " in ".$e->getFile() .
        "\nMessage: ".$e->getMessage() .
        "\nStackTrace: " . $e->getTraceAsString();

        return error_log($errorMsg);
    }

    protected function generateErrorData($e, $code = 500)
    {
        $errorData = [
            'message'=>$e->getMessage(),
            'code'=>$code,
            'file'=>$e->getFile(),
            'line'=>$e->getLine(),
            'trace'=>$e->getTraceAsString(),
        ];

        return $errorData;
    }
}

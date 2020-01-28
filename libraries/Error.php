<?php
namespace Lib;

use Lib\AppCore\Response;
use Bootstrap\Config;
use DateTime;
use Exception;

/**
 * Error class
 * エラー・例外ハンドラークラス
 */
class Error
{
    /**
     * エラーハンドラー関数
     *　エラーメッセージはErrorExceptionへ変換する
     *
     * @param integer $severity
     * @param string $message
     * @param string $file
     * @param integer $line
     * @return void
     */
    public static function errorHandler($severity, $message, $file, $line)
    {
        if (!(error_reporting() & $severity)) {
            // このエラーコードが error_reporting に含まれていない場合
            return;
        }
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * 例外ハンドラ関数
     *
     * @param Throwable $e
     * @return void
     */
    public function exceptionHandler($e)
    {
        if (!empty($e->getCode())) {
            $code = $e->getCode();
        } else {
            $code = 500;
        }
        //エラー内容をログに保存
        if (Config::get('LOG') === 'On') {
            static::errorLog($e);
        }

        if (Config::get('APP_ENV') === 'production') {
            $response = ['message'=>'An error has occurred.'];
        } else {
            $response = static::generateErrorData($e, $code);
        }

        //エラーメッセージをjsonデータでクライアントに送信
        Response::json($response, $code);
    }

    /**
     * エラーメッセージを作成し、ログに保存
     *
     * @param Throwable $e
     * @return boolean
     */
    protected function errorLog($e)
    {
        ini_set('log_errors', 'On');
        
        $errorMsg = "Uncaught Exception: '". get_class($e) . "' on line ".$e->getLine(). " in ".$e->getFile() .
        "\nMessage: ".$e->getMessage() .
        "\nStackTrace: " . $e->getTraceAsString();

        return error_log($errorMsg);
    }

    /**
     * エラーデータの作成
     *
     * @param Throwable $e
     * @param integer $code
     * @return array
     */
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

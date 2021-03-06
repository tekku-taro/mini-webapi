<?php
//システムの起動処理

// 各種パス定数の定義
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__DIR__));
define('BOOTSTRAP', dirname(__FILE__));
define('LOG', ROOT . DS . "logs");

//エラーの設定
error_reporting(E_ALL ^ E_DEPRECATED);
ini_set('display_errors', 0);
// ログの保存先
$logpath = LOG . DS .  (new DateTime())->format("Y-m-d") . ' error.log';
ini_set('error_log', $logpath);


// composer autoload
require(ROOT . DS . 'vendor/autoload.php');

// error handling
set_error_handler("Lib\Error::errorHandler");
set_exception_handler([(new Lib\Error),"exceptionHandler"]);


// 設定ファイルの読込
Bootstrap\Config::load(ROOT . DS .  '.env');



// databaseへの接続
require(BOOTSTRAP . DS . 'database.php');

//リクエストの処理とAPIへの送信
$request = (new Route\Request)->parseURL();

$dispatcher = new Route\Dispatcher($request);


(new Route\Dispatcher($request))->send();

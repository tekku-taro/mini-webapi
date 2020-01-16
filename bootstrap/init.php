<?php

// 各種パス定数の定義
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__DIR__));
define('BOOTSTRAP', dirname(__FILE__));

//エラーの設定
error_reporting(E_ALL ^ E_DEPRECATED);
ini_set('log_errors', 'On'); // ログの保存先
ini_set('error_log', ROOT . DS . 'logs/php_error.log');


// echo dirname(__DIR__);
// composer autoload
require(ROOT . DS . 'vendor/autoload.php');


// 設定ファイルの読込
Bootstrap\Config::load(ROOT . DS .  '.env');
// print_r(Config::all());
// エラーの表示
$app_env = Bootstrap\Config::get('APP_ENV');
if ($app_env === 'production') {
    // エラー出力しない
    ini_set('display_errors', 0);
} else {
    // エラー出力する
    ini_set('display_errors', 1);
}

// databaseへの接続
require(BOOTSTRAP . DS . 'database.php');

//アプリの起動処理
$request = (new Route\Request)->parseURL();
// var_dump($request);
$dispatcher = new Route\Dispatcher($request);


(new Route\Dispatcher($request))->send();

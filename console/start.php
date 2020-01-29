<?php
/**
 * start.php
 * テンプレートから雛形を作成するスクリプト
 *
 * コマンドラインかbashでmaker.bat/maker.shを実行し、
 * APIクラスやモデルクラスの雛形を作成
 *
 * 作成したファイルの場所
 * console/output
 *
 * 作成方法
 * 1 コマンドライン
 * # ZipcodesAPI クラス
 * console/maker.bat make:api ZipcodesAPI
 * # Zipcode モデルクラス
 * console/maker.bat make:model Zipcode
 * # -mオプションで別のモデルを指定可能
 * console/maker.bat make:api SessionsAPI -m User
 *
 * 2 bash
 *  # ZipcodesAPI クラス
 * console/maker.sh make:api ZipcodesAPI
 *  Zipcode モデルクラス
 * console/maker.sh make:model Zipcode
 * # -mオプションで別のモデルを指定可能
 * console/maker.sh make:api SessionsAPI -m User
 *
 */

define('ROOT', dirname(__DIR__));
define('ENGINE', dirname(__FILE__).'/template_engine');

require_once ROOT.'/vendor/autoload.php';

$template = "";
//コマンドライン引数の処理
require_once dirname(__FILE__). '/parseArgs.php';

use Engine\FileManager\TemplateManager;

// Engine 初期化
$fileManager = new TemplateManager(ENGINE . '/templates');
$engine = new \Engine\FileRender\Renderer($fileManager);

// テンプレートからファイルデータ作成
$vars = [
    'className'=>$className,
    'varName'=>$varName,
    'modelName'=>$modelName,
];


// テンプレート作成
$output = $engine->render($template, $vars);

// ファイルの保存
$fileManager->save("{$className}.php", $output);

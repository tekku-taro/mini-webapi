<?php
define('ROOT', dirname(__DIR__));
define('ENGINE', dirname(__FILE__).'/template_engine');

require_once ROOT.'/vendor/autoload.php';

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

<?php
define('ROOT', dirname(__DIR__));
define('ENGINE', dirname(__FILE__).'/template_engine');

require_once ROOT.'/vendor/autoload.php';


if ($argc != 3) {
    echo "you should pass two parameters to use maker command";
    exit;
}

$template = $argv[1];

if (preg_match('/make:(.+)/', $argv[1], $matches)) {
    $template = $matches[1];
} else {
    echo "maker command is not correct. abort proccess.";
    exit;
}

$className = $argv[2];
$modelName = rtrim(str_replace('API', '', $className), "s");
$varName = strtolower($modelName);



use Engine\FileManager\TemplateManager;

// Engine 初期化
$fileManager = new TemplateManager(ENGINE . '/templates');
$engine = new \Engine\FileRender\Renderer($fileManager);
$name = ['id'=>1];

// テンプレートからファイルデータ作成
$vars = [
    'className'=>$className,
    'varName'=>$varName,
    'modelName'=>$modelName,
];
//for test.twig
// $vars = [
//     'className'=>$className,
//     'getVar'=>$varName,
//     'modelName'=>$modelName,
//     'users'=>['user1','user2','user3'],
//     'tag'=>['name'=>'h1','text'=>'My Blog']
// ];

// テンプレート作成
$output = $engine->render($template, $vars);

// ファイルの保存
$fileManager->save("{$className}.php", $output);

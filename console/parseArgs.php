<?php
/**
 * parseArgs.php
 *
 * コマンドライン引数から、$className, $template, $modelName, $varName
 * を設定する
 *
 * $className 作成するクラス名
 * $template 使用するテンプレート名
 * $modelName 利用するモデル名
 * $varName モデルのインスタンス名
 */

if ($argc < 3) {
    echo "you should pass at least two parameters to use maker command";
    exit;
}

$task = getNamedArg('task');
$className = getNamedArg('class');


$template = getTemplateName($task);

$val = getNamedArg('model', true);
if ($val) {
    $modelName = $val;
} else {
    $modelName = rtrim(str_replace('API', '', $className), "s");
}

$varName = strtolower($modelName);

// echo $className . PHP_EOL . $modelName . PHP_EOL . $varName . PHP_EOL . $template;
// exit;

/**
 * $taskからテンプレート名を取得して返す
 *
 * @param string $task
 * @return mixed
 */
function getTemplateName($task)
{
    if (preg_match('/make:(.+)/', $task, $matches)) {
        $template = $matches[1];
    } else {
        echo "maker command is not correct. abort proccess.";
        exit;
    }
    
    if (!in_array($template, ["api","model"])) {
        echo "maker command is not correct. abort proccess.";
        exit;
    }
    return $template;
}

/**
 * コマンドライン引数から$nameのオプションを取得して返す
 *
 * @param string $name
 * @param boolean $isOption
 * @return mixed
 */
function getNamedArg($name, $isOption = false)
{
    $val = getopt(null, ["${name}:"]);
    if (!empty($val[$name])) {
        return $val[$name];
    }
    if ($isOption) {
        return false;
    } else {
        echo "maker command is not correct. abort proccess.";
        exit;
    }
}

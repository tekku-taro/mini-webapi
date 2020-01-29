<?php
namespace Engine\Conversion;

use ErrorException;
use Engine\DB\Model;
use Engine\FileManager\TemplateManager;

/**
 * Converter class
 *
 * 変数リストを用いてテンプレートのtwigライクな構文を変換するクラス
 *
 * 変換対象
 * 外部ファイルのインクルード {{ INCLUDE filename }}
 * モデルテンプレートのカラムリスト変数 fillable, rules
 * 変数の表示 {{ identifier }}
 * IF構文 {{ IF condition }}{{ ENDIF }}
 * IFELSE構文 {{ IF condition }}{{ ELSE }}{{ ENDIF }}
 * FOR構文 {{ FOR identifier IN variable }}{{ ENDFOR }}
 *
 */
class Converter
{
    /**
     * 変換対象データ
     *
     * @var string
     */
    protected $content;

    /**
     * 変換に用いる変数リスト
     * ["identifier"=>"value"] または ["identifier"=>["key"=>"keyval"]]
     *
     * @var array
     */
    protected $vars;

    /**
     * テンプレート名
     *
     * @var string
     */
    protected $template;

    /**
     * TemplateManagerオブジェクト
     *
     * @var TemplateManager
     */
    protected $fileManager;

    /**
     * 対象データ、変数、テンプレート名の設定
     *
     * @param string $content
     * @param array $vars
     * @param string $template
     * @param TemplateManager $fileManager
     */
    public function __construct($content, $vars, $template, TemplateManager $fileManager)
    {
        $this->content = $content;
        $this->vars = $vars;
        $this->template = $template;
        $this->fileManager = $fileManager;
    }

    /**
     * 対象データを変換する
     *
     * @return string $content
     */
    public function convert()
    {
        $this->disablePHPCode();

        $this->insertColumns();

        $this->convertIncludes();

        $this->convertFors();
        
        $this->convertIfElses();

        $this->convertIfs();
        
        $this->insertVars();

        $this->enablePHPCode();

        return $this->content;
    }

    /**
     * 対象データ内のphpタグをプレースホルダーで置き換える
     *
     * @return string $content
     */
    protected function disablePHPCode()
    {
        $this->content = str_replace("<?php", "CODESTART", $this->content);
        $this->content = str_replace("?>", "CODEEND", $this->content);
        return $this->content;
    }

    /**
     * 対象データ内のプレースホルダーをphpタグに戻す
     *
     * @return string $content
     */
    protected function enablePHPCode()
    {
        $this->content = str_replace("CODESTART", "<?php", $this->content);
        $this->content = str_replace("CODEEND", "?>", $this->content);
        return $this->content;
    }

    /**
     * モデルテンプレートの場合に、関連するテーブルカラムを取得し
     * fillable, rules変数に設定する
     *
     * @return void
     */
    protected function insertColumns()
    {
        if ($this->template != 'model') {
            return;
        }

        $model = new Model($this->getTableName($this->vars['modelName']));
        $columns = $model->getTableColumns();

        $this->vars['fillable'] = implode(", ", $columns);
        $columns = array_map(function ($column) {
            return $column . "=>[]";
        }, $columns);
        $this->vars['rules'] = implode("," . PHP_EOL . "            ", $columns);
    }

    /**
     * モデル名からテーブル名を取得
     *
     * @param string $modelName
     * @return string
     */
    protected function getTableName($modelName)
    {
        return strtolower($modelName) . "s";
    }

    /**
     * $varsから変数$identifierの値を取得する
     *
     * @param mixed $identifier
     * @return mixed
     */
    protected function getVarValue($identifier)
    {
        if (isset($this->vars[$identifier])) {
            return $this->vars[$identifier];
        } else {
            return "no $identifier variable";
        }
    }

    /**
     * 対象データの{{ identifier }}を$varsの変数$identifierの値で置き換える
     *
     * @return void
     */
    protected function insertVars()
    {
        foreach ($this->vars as $identifier => $value) {
            if (!is_array($value)) {
                $this->content = preg_replace("/\{\{\s*".$identifier."\s*\}\}/", $value, $this->content);
            } else {
                //    'tag'=>['name'=>'h1','text'=>'My Blog']
                foreach ($value as $key => $keyVal) {
                    if (!is_numeric($key)) {
                        $this->content = preg_replace("/\{\{\s*" . $identifier . "." . $key."\s*\}\}/", $keyVal, $this->content);
                    }
                }
            }
        }
    }

    /**
     * 対象データの{{ INCLUDE filename }}をfilenameのファイルデータで置き換える
     *
     * @return void
     */
    protected function convertIncludes()
    {
        $pattern = "/\{%\s*INCLUDE\s+(.*?)\s*%\}/i";

        $matches = $this->getMatchedBlocks($pattern);
        
        if (empty($matches[0])) {
            return;
        }
        for ($i=0; $i < count($matches[0]); $i++) {
            // $filepath = $this->getFilepath($matches[1][$i]);
            $includeContent = $this->getIncludeContent($matches[1][$i]);
            if ($includeContent) {
                // $incBlock = "include '". $filepath ."';";
                $this->content = str_replace($matches[0][$i], $includeContent, $this->content);
            } else {
                $this->content = str_replace($matches[0][$i], "", $this->content);
            }
        }
    }

    /**
     * $filelabelのファイルの中身をphpタグを除いて取得する
     *
     * @param string $filepath
     * @return mixed
     */
    protected function getIncludeContent($filelabel)
    {
        $fileName = str_replace(["'",'"'], ["",""], $filelabel) ;
        if (strpos($fileName, ".php") === false) {
            $fileName .= ".php";
        }
        $content = $this->fileManager->load($fileName);
        if ($content) {
            return str_replace("?>", "", str_replace("<?php", "", $content));
        }
        return false;
    }



    /**
     * 対象データの{{ IF condition }} stmt {{ ENDIF }}を実行した結果と置き換える
     *
     * @return void
     */
    protected function convertIfs()
    {
        $pattern = "/\{%\s*IF\s+(.*?)\s*%\}[\s]*?(.*?)[\s]*?\{%\s*ENDIF\s*%\}/i";

        $matches = $this->getMatchedBlocks($pattern);
        
        if (empty($matches[0])) {
            return;
        }
        for ($i=0; $i < count($matches[0]); $i++) {
            $condition = $matches[1][$i];
            $trueVal = $matches[2][$i];

            $condition = $this->insertVarsInCodeBlock($condition, "'");
            $result = $this->executeCodeBlock($condition);

            if ($result) {
                $this->content = str_replace($matches[0][$i], $trueVal, $this->content);
            } else {
                $this->content = str_replace($matches[0][$i], "", $this->content);
            }
        }
    }

    /**
     * 対象データの{{ IF condition }} stmt {{ ELSE }} stmt {{ ENDIF }}を実行した結果と置き換える
     *
     * @return void
     */
    protected function convertIfElses()
    {
        $pattern = "/\{%\s*IF\s+(.*?)\s*%\}[\s]*?(.*?)[\s]*?\{%\s*ELSE\s*%\}[\s]*?(.*?)[\s]*?\{%\s*ENDIF\s*%\}/i";

        $matches = $this->getMatchedBlocks($pattern);

        if (empty($matches[0])) {
            return;
        }
        for ($i=0; $i < count($matches[0]); $i++) {
            $condition = $matches[1][$i];
            $trueVal = $matches[2][$i];
            $falseVal = $matches[3][$i];

            $condition = $this->insertVarsInCodeBlock($condition, "'");
            $result = $this->executeCodeBlock($condition);

            if ($result) {
                $this->content = str_replace($matches[0][$i], $trueVal, $this->content);
            } else {
                $this->content = str_replace($matches[0][$i], $falseVal, $this->content);
            }
        }
    }

    /**
     * 対象データの{{ FOR identifier IN variable }} stmt {{ ENDFOR }}を実行した結果と置き換える
     *
     * @return void
     */
    protected function convertFors()
    {
        $pattern = "/\{%\s*FOR\s+?(?P<identifier>.+?)\s+?IN\s+?(?P<variable>.+?)\s*%\}[\s]*?(?P<stmt>.*?)[\s]*?\{%\s*?ENDFOR\s*?%\}/i";

        $matches = $this->getMatchedBlocks($pattern);

        if (empty($matches[0])) {
            return;
        }
        for ($i=0; $i < count($matches[0]); $i++) {
            $item = $matches['identifier'][$i];
            $items = $matches['variable'][$i];
            $stmt = $matches['stmt'][$i];

            $itemsValue = $this->getVarValue($items);

            if (!is_array($itemsValue)) {
                throw new ErrorException("$itemsValue is not an array,and can not be pushed into foreach function");
            }

            $blockInForLoop = "";

            foreach ($itemsValue as $key => $value) {
                $blockInForLoop .= $this->insertVarInStmt($stmt, $item, $value). PHP_EOL;
            }

            $this->content = str_replace($matches[0][$i], $blockInForLoop, $this->content);
        }
    }

    /**
     * $contentデータの $identifier あるいは $identifier.$key を
     * $varsの変数$identifier あるいは $identifier[$key] の値で置き換える
     *
     * @param string $content
     * @param string $quote
     * @return string $content
     */
    protected function insertVarsInCodeBlock($content, $quote = "")
    {
        foreach ($this->vars as $identifier => $value) {
            if (!is_array($value)) {
                $content = str_replace($identifier, $quote. $value .$quote, $content);
            } else {
                //    'tag'=>['name'=>'h1','text'=>'My Blog']
                foreach ($value as $key => $keyVal) {
                    if (!is_numeric($key)) {
                        $content = str_replace($identifier . "." . $key, $quote. $keyVal .$quote, $content);
                    }
                }
            }
        }
        return $content;
    }

    /**
     * $contentデータの {{ identifier }} あるいは {{ identifier.key }} を
     * $varsの変数$identifier あるいは $identifier[$key] の値で置き換える
     *
     * @param string $content
     * @param string $identifier
     * @param mixed $value
     * @param string $quote
     * @return string $content
     */
    protected function insertVarInStmt($content, $identifier, $value, $quote = "")
    {
        if (!is_array($value)) {
            $content = preg_replace("/\{\{\s*".$identifier."\s*\}\}/", $quote. $value .$quote, $content);
        } else {
            //    'tag'=>['name'=>'h1','text'=>'My Blog']
            foreach ($value as $key => $keyVal) {
                if (!is_numeric($key)) {
                    $content = preg_replace("/{{\s*".$identifier."\.".$key."\s*}}/", $quote. $keyVal .$quote, $content);
                }
            }
        }

        return $content;
    }

    /**
     * $codeBlockのphpコードを実行して、結果を返す
     *
     * @param string $codeBlock
     * @return void
     */
    protected function executeCodeBlock($codeBlock)
    {
        return eval("return $codeBlock ;");
    }


    /**
     * 対象データを$patternでパターンマッチングした結果全てを返す
     *
     * @param string $pattern
     * @return array $matches
     */
    protected function getMatchedBlocks($pattern)
    {
        preg_match_all($pattern, $this->content, $matches);

        return $matches;
    }
}

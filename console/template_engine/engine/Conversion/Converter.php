<?php
namespace Engine\Conversion;

use ErrorException;
use Engine\DB\Model;

class Converter
{
    protected $content;
    protected $vars;
    protected $template;

    public function __construct($content, $vars, $template)
    {
        $this->content = $content;
        $this->vars = $vars;
        $this->template = $template;
    }

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

    protected function disablePHPCode()
    {
        $this->content = str_replace("<?php", "CODESTART", $this->content);
        $this->content = str_replace("?>", "CODEEND", $this->content);
        return $this->content;
    }

    protected function enablePHPCode()
    {
        $this->content = str_replace("CODESTART", "<?php", $this->content);
        $this->content = str_replace("CODEEND", "?>", $this->content);
        return $this->content;
    }

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

    protected function getTableName($modelName)
    {
        return strtolower($modelName) . "s";
    }

    protected function getVarValue($identifier)
    {
        if (isset($this->vars[$identifier])) {
            return $this->vars[$identifier];
        } else {
            return "no $identifier variable";
        }
    }

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

    protected function convertIncludes()
    {
        $pattern = "/\{%\s*INCLUDE\s+(.*?)\s*%\}/i";

        $matches = $this->getMatchedBlocks($pattern);
        
        if (empty($matches[0])) {
            return;
        }
        for ($i=0; $i < count($matches[0]); $i++) {
            $filepath = $this->getFilepath($matches[1][$i]);
            if (file_exists($filepath)) {
                // $incBlock = "include '". $filepath ."';";
                $includeContent = $this->getIncludeContent($filepath);
                $this->content = str_replace($matches[0][$i], $includeContent, $this->content);
            } else {
                $this->content = str_replace($matches[0][$i], "", $this->content);
            }
        }
    }

    protected function getIncludeContent($filepath)
    {
        $content = file_get_contents($filepath);

        return str_replace("?>", "", str_replace("<?php", "", $content));
    }
    protected function getFilepath($filelabel)
    {
        $fileName = str_replace(["'",'"'], ["",""], $filelabel) ;
        if (strpos($fileName, ".php") === false) {
            $fileName .= ".php";
        }
        $filepath = ENGINE . '/templates' . $fileName;
        return $filepath;
    }

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

    protected function executeCodeBlock($codeBlock)
    {
        return eval("return $codeBlock ;");
    }


    protected function getMatchedBlocks($pattern)
    {
        preg_match_all($pattern, $this->content, $matches);

        return $matches;
    }
}

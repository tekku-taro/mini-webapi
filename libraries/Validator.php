<?php
namespace Lib;
class Validator
{
    public $errors=[];
    public $rules;
    protected $ruleList =  ['require','email','password','zipcode', 'num','bool',
                            'string','int','list','min','max','len','custom'];

                            
    public $messages=[
        'require'=>':column は必須項目です。',
        'string'=>':column が文字列でありません。',
        'len'=>':column の文字数は :constraint 文字です。',
        'email'=>":column はemailの形式でありません。",
        'password'=>":column はパスワードの書式でありません。",
        'min'=>":column は :constraint 文字以上でなければいけません。",
        'max'=>":column は :constraint 文字以下でなければいけません。",
        'int'=>":column が整数型でありません。",
        'num'=>":column が数値型でありません。",
        'bool'=>":column がブール型(True/False)でありません。",
        'zipcode'=>":column は郵便番号の書式(000-0000)でありません。",
        'list'=>":column はリストに含まれている値( :constraint )でなければいけません。",
        'custom'=>":column はカスタム関数を満たす必要があります。",       
    ];
                                
    public function __construct($validationRules)
    {
        foreach ($validationRules as $column => $rules) {
            // 各columnにたいして
            $this->addValidation($column,$rules);
        }
                
    }

    protected function addValidation($column,$rules)
    {
        $this->rules[$column] = [];
        foreach ($rules as $key => $data) {
            list($rule,$constraint) = $this->getRuleAndConstraint($key,$data);
            // ruleListのどれかならば
            if(in_array($rule,$this->ruleList)){
                if(empty($constraint)){
                    $this->rules[$column][] = $rule;
                }else{
                    $this->rules[$column][$rule] = $constraint;
                }
            }
            
        }

    }
    
    protected function getRuleAndConstraint($key,$value)
    {
        if(is_string($key)){
            $rule = $key;
            $constraint = $value;
        }else{
            $rule = $value;
            $constraint = null;
        }       
        return [$rule,$constraint]; 
    }

    public function validate($data)
    {
        // 全てのcolumnについて columnRules = rules[columns]
        foreach ($this->rules as $column => $columnRules) {
            // dataを一つずつ対応するruleで検証し
            if(isset($data[$column])){
                $this->validateColumn($column,$columnRules,$data[$column]);
            }else{
                // 必須項目ならば
                if(in_array("require",$columnRules)){
                    $this->validateColumn($column,$columnRules,null);
                }
            }
        }
        // errorsを返す
        return $this->errors;

    }
    

    public function validateColumn($column,$columnRules,$value)
    {

        // columnRulesで場合分け
        foreach ($columnRules as $key => $data) {
            // rule + constraintに分解
            list($rule,$constraint) = $this->getRuleAndConstraint($key,$data);
            // validationの実行            
            $result = call_user_func_array([$this,$rule],[$value,$constraint]);
    
            // ruleに反していれば
            // errors[column][] = messages[rule](プレースホルダー変更)
            if($result === false){
                $message = $this->replacePlaceHolders($this->messages[$rule],$column,$constraint);

                $this->addError($column,$message);
            }

        }

    }

    protected function addError($column,$message)
    {
        if(!isset($this->errors[$column]))
        {
            $this->errors[$column] = [];
        }

        $this->errors[$column][] = $message;       
    }

    protected function replacePlaceHolders($message,$column,$constraint)
    {
        $message = str_replace(":column",$column,$message);
        if(!empty($constraint) and !is_callable($constraint)){
            if(is_array($constraint)){
                $constraint = implode(",",$constraint);
            }

            $message = str_replace(":constraint",$constraint,$message);  

        }
        
        return $message;
    }
    

    // require:必須項目かどうか確認
    protected function require($value) 
    {
        return !empty($value) or $value === 0;
    }

    // email:Emailの形式か確認(filter_val)
    protected function email($value) 
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    // password:Passwordの書式か確認（[a-zA-Z_0-9]+）
    protected function password($value)
    {
        return preg_match("/^[a-zA-Z_0-9]+$/",$value) === 1;
    }
    // min:最低文字数あるか確認
    protected function min($value,$min) 
    {
        return mb_strlen($value) >= $min;
    }
    // max:最大文字数以内か確認
    protected function max($value,$max)  
    {
        return mb_strlen($value) <= $max;
    }
    // len:文字数が指定の数か確認
    protected function len($value,int $length) 
    {
        return mb_strlen($value) === $length;
    }
    // string:文字列か確認
    protected function string($value) 
    {
        return is_string($value);
    }
    // bool:true,falseか確認
    protected function bool($value) 
    {
        return is_bool($value);
    }
    // zipcode:数字7文字の文字列か確認
    protected function zipcode($value) 
    {
        return preg_match("/[0-9]{3}-[0-9]{4}/",$value) === 1;
    }
    // list:指定のlistのどれかにあたるか確認
    protected function list($value,array $list) 
    {
        return in_array($value,$list);
    }
    // custom:無名関数を実行して結果を返す
    protected function custom($value,$customFunc) 
    {
        return $customFunc($value);
    }
                            

}

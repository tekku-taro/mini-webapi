<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Lib\AppCore\Model;

class Session extends Eloquent
{
    use Model;

    public $timestamps = false; 

    protected $fillable =[
        'id', 'user_id', 'jti', 'access_token', 'refresh_token', 'refresh_token_expiry', 'invalidated', 'updated_at', 'created_at'
    ];

    /**
    * rules
    * テーブルカラムの validation ルールを設定
    *  cf. 'username'=>["require","string","len"=>8],
    *  
    */
    public function rules()
    {
        return [
            'id'=>[],
            'user_id'=>[],
            'jti'=>[],
            'access_token'=>[],
            'refresh_token'=>[],
            'refresh_token_expiry'=>[],
            'invalidated'=>[],
            'updated_at'=>[],
            'created_at'=>[]
        ];
    }

    // 使用できるルール一覧

    // require:必須項目かどうか確認
    // email:Emailの形式か確認(filter_val)
    // password:Passwordの書式か確認（[a-zA-Z_0-9]+）
    // min:最低文字数あるか確認
    // max:最大文字数以内か確認
    // len:文字数が指定の数か確認
    // int:整数か確認
    // num:数値か確認
    // string:文字列か確認
    // bool:true,falseか確認
    // zipcode:8文字の郵便番号文字列か確認
    // list:指定のlistのどれかにあたるか確認
    // custom:無名関数を実行して結果を返す


}

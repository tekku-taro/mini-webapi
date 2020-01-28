<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Lib\AppCore\Model;

/**
 * User class
 * Userモデルのクラス
 * users tableに対応
 */
class User extends Eloquent
{
    use Model;

    public $timestamps = ['created_at','updated_at','deleted_at'];

    protected $fillable =[
        'name',
        'password',
        'role',
        'active',
        'login_attempts',
        'created_at',
        'updated_at',
        'deleted_at'
    ];


    /**
     * Userモデルのvalidationルール
     *
     * @return array $validationRules
     */
    public function rules()
    {
        return[
            'name'=>["require","string","min"=>4,"unique"],
            'role'=>["require","string","list"=>["admin","user"]],
            'active'=>["int","len"=>1],
            'login_attempts'=>["int"],
            'password'=>["require","min"=>6,'password'],
        ];
    }

    /**
     * パスワードを保存・更新する際は、ハッシュ値に変換する
     *
     * @param array $options
     * @return boolean
     */
    public function save(array $options = [])
    {
        // before save code
        if (!empty($this->password)) {
            if (isset($this->id)) {
                if ($this->isDirty('password')) {
                    // password has changed
                    $this->password = password_hash($this->password, PASSWORD_DEFAULT);
                }
            } else {
                $this->password = password_hash($this->password, PASSWORD_DEFAULT);
            }
        }

        $result = parent::save($options); // returns boolean
       // after save code
       return $result; // do not ignore it eloquent calculates this value and returns this, not just to ignore
    }
}

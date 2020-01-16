<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Lib\AppCore\Model;

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



    public static function rules()
    {
        return[
            'name'=>["require","string","min"=>4],
            'role'=>["require","string","list"=>["admin","user"]],
            'active'=>["int","len"=>1],
            'login_attempts'=>["int"],
            'password'=>["require","min"=>6,'password'],
        ];
            
    }

}

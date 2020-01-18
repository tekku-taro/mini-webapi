<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Lib\AppCore\Model;

class Session extends Eloquent
{
    use Model;

    public $timestamps = ['created_at'];

    protected $fillable =[
        'user_id',
        'access_token',
        'refresh_token',
        'refresh_token_expiry',
        'invalidated',
        'created_at'
    ];

    public static function rules()
    {
        return [
            'user_id'=>["require","int"],
            'access_token'=>["require","string"],
            'refresh_token'=>["require","string"],
            'refresh_token_expiry'=>["require","int"],
            'invalidated'=>["bool"],
        ];
    }
}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Lib\AppCore\Model;

class Session extends Eloquent
{
    use Model;

    public $timestamps = ['updated_at','created_at'];

    protected $fillable =[
        'user_id',
        'jti',
        'access_token',
        'refresh_token',
        'refresh_token_expiry',
        'invalidated',
        'updated_at',
        'created_at'
    ];

    public function rules()
    {
        return [
            'user_id'=>["require","int"],
            'access_token'=>["require"],
            'refresh_token'=>["require","string"],
            'refresh_token_expiry'=>["require","int"],
            'invalidated'=>["bool"],
        ];
    }
}

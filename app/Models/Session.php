<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Session extends Eloquent
{
    public $timestamps = ['created_at'];

    protected $fillable =[
        'user_id',
        'access_token',
        'refresh_token',
        'refresh_token_expiry',
        'invalidated',
        'created_at'
    ];
}

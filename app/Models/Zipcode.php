<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Lib\AppCore\Model;

class Zipcode extends Eloquent
{
    use Model;

    public $timestamps = false; 

    protected $fillable =[
        'zipcode',
        'address'
    ];

    public static function rules()
    {
        return [
            'zipcode'=>["require","string","len"=>8],
            'address'=> ["require","string"],
        ];
    }



}

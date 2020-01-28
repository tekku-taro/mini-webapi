<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Lib\AppCore\Model;

/**
 * Zipcode class
 * Zipcodeモデルのクラス
 * zipcodes tableに対応
 */
class Zipcode extends Eloquent
{
    use Model;

    public $timestamps = false;

    protected $fillable =[
        'zipcode',
        'address'
    ];

    /**
     * Zipcodeモデルのvalidationルール
     *
     * @return array $validationRules
     */
    public function rules()
    {
        return [
            'zipcode'=>["require","string","len"=>8],
            'address'=> ["require","string"],
        ];
    }
}

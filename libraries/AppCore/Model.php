<?php
namespace Lib\AppCore;

use Lib\Validator;

/**
 * Model trait
 * モデルクラスで使用し、queryやvalidation機能を提供するtrait
 */
trait Model
{
    /**
     * $page指定時の1ページのレコード数
     *
     * @var integer
     */
    public $numPerPage = 15;

    /**
     * $pageで指定したページのレコードデータを返す
     *
     * @param integer $page
     * @param array $params
     * @return void
     */
    public function getPageRecords($page, $params = [])
    {
        if ($page < 1) {
            $page = 1;
        }
        $whereData=[];
        if (!empty($params)) {
            $whereData = $this->createWhereData($params, $this->fillable);
        }

        return $this->where($whereData)->offset(($page-1)*$this->numPerPage)->limit($this->numPerPage)->get();
    }

    /**
     * パラメータ配列の条件でレコードを取得し返す
     *
     * @param array $params
     * @return void
     */
    public function getFromParams($params)
    {
        $whereData = $this->createWhereData($params, $this->fillable);

        return $this->where($whereData)->limit(1000)->get();
    }

    /**
     * queryのwhereデータをパラメータから作成
     *
     * @param array $params
     * @param array $fillable
     * @return void
     */
    public function createWhereData($params, $fillable)
    {
        $whereData = [];
        foreach ($fillable as $key => $column) {
            if (isset($params[$column])) {
                $whereData[$column] = $params[$column];
            }
        }

        return $whereData;
    }


    /**
     * validation rules
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * validation ruleで$dataを検証する
     *
     * @param array $data
     * @param integer $model_id
     * @return array $error
     */
    public function validate($data, $model_id = null)
    {
        $validator = new Validator($this->rules(), get_class($this), $model_id);
        return $validator->validate($data);
    }
}

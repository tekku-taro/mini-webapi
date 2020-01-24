<?php
namespace Lib\AppCore;

use Lib\Validator;

trait Model
{
    public $numPerPage = 15;

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

    public function getFromParams($params)
    {
        $whereData = $this->createWhereData($params, $this->fillable);

        return $this->where($whereData)->limit(1000)->get();
    }

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


    public function rules()
    {
        return [];
    }

    public function validate($data, $model_id = null)
    {
        $validator = new Validator($this->rules(), get_class($this), $model_id);
        return $validator->validate($data);
    }
}

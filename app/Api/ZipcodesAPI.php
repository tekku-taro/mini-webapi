<?php
namespace App\Api;

use App\Models\Zipcode;
use Lib\AppCore\ResourceAPI;

class ZipcodesAPI extends ResourceAPI
{
    public function getIndex($page)
    {
        if (!empty($page)) {
            return (new Zipcode)->getPageRecords($page, $this->params);
        } elseif (!empty($this->params)) {
            return (new Zipcode)->getFromParams($this->params);
        } else {
            return Zipcode::limit(1000)->get();
        }
    }

    public function get($id)
    {
        if (!empty($id)) {
            return Zipcode::find($id);
        } else {
            return null;
        }
    }

    public function post()
    {
        $zipcode = new Zipcode();
        $errors = $zipcode->validate($this->request->data);

        if (!empty($errors)) {
            return ['errors'=>$errors];
        }

        $zipcode = $zipcode->fill($this->request->data);
        if ($zipcode->save()) {
            return $zipcode;
        } else {
            return $this->request->data;
        }
    }
    
    public function put($id)
    {
        $zipcode = Zipcode::find($id);
        
        if (!$zipcode) {
            return $this->request->data;
        }

        $zipcode->fill($this->request->data);

        $errors = $zipcode->validate($zipcode->toArray());

        if (!empty($errors)) {
            return ['errors'=>$errors];
        }

        if ($zipcode->save()) {
            return $zipcode;
        } else {
            return $this->request->data;
        }
    }

    public function delete($id)
    {
        $zipcode = Zipcode::find($id);
        
        if (!$zipcode) {
            return null;
        }

        if ($zipcode->delete()) {
            return $zipcode;
        } else {
            return $zipcode->toArray();
        }
    }
}

<?php
namespace App\Api;

use App\Models\Example;
use Lib\AppCore\ResourceAPI;

class ExamplesAPI extends ResourceAPI
{
    public function getIndex($page)
    {
        if (!empty($page)) {
            return (new Example)->getPageRecords($page, $this->params);
        } elseif (!empty($this->params)) {
            return (new Example)->getFromParams($this->params);
        } else {
            return Example::all();
        }
    }

    public function get($id)
    {
        if (!empty($id)) {
            return Example::find($id);
        } else {
            return null;
        }
    }

    public function post()
    {
        $example = new Example();
        $errors = $example->validate($this->request->data);

        if (!empty($errors)) {
            return ['errors'=>$errors];
        }

        $example = $example->fill($this->request->data);
        if ($example->save()) {
            return $example;
        } else {
            return $this->request->data;
        }
    }
    
    public function put($id)
    {
        $example = Example::find($id);
        
        if (!$example) {
            return $this->request->data;
        }

        $example->fill($this->request->data);

        $errors = $example->validate($example->toArray());

        if (!empty($errors)) {
            return ['errors'=>$errors];
        }

        if ($example->save()) {
            return $example;
        } else {
            return $this->request->data;
        }
    }

    public function delete($id)
    {
        $example = Example::find($id);
        
        if (!$example) {
            return null;
        }

        if ($example->delete()) {
            return $example;
        } else {
            return $example->toArray();
        }
    }
}

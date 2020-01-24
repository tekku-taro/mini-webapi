<?php
namespace App\Api;

use App\Models\Sample;
use Lib\AppCore\ResourceAPI;

class SamplesAPI extends ResourceAPI
{
    public function getIndex($page)
    {
        if (!empty($page)) {
            return (new Sample)->getPageRecords($page, $this->params);
        } elseif (!empty($this->params)) {
            return (new Sample)->getFromParams($this->params);
        } else {
            return Sample::all();
        }
    }

    public function get($id)
    {
        if (!empty($id)) {
            return Sample::find($id);
        } else {
            return null;
        }
    }

    public function post()
    {
        $sample = new Sample();
        $errors = $sample->validate($this->request->data);

        if (!empty($errors)) {
            return ['errors'=>$errors];
        }

        $sample = $sample->fill($this->request->data);
        if ($sample->save()) {
            return $sample;
        } else {
            return $this->request->data;
        }
    }
    
    public function put($id)
    {
        $sample = Sample::find($id);
        
        if (!$sample) {
            return $this->request->data;
        }

        $sample->fill($this->request->data);

        $errors = $sample->validate($sample->toArray());

        if (!empty($errors)) {
            return ['errors'=>$errors];
        }

        if ($sample->save()) {
            return $sample;
        } else {
            return $this->request->data;
        }
    }

    public function delete($id)
    {
        $sample = Sample::find($id);
        
        if (!$sample) {
            return null;
        }

        if ($sample->delete()) {
            return $sample;
        } else {
            return $sample->toArray();
        }
    }
}

<?php
namespace App\Api;

use App\Models\Session;
use Lib\AppCore\ResourceAPI;

class SamplesAPI extends ResourceAPI
{
    public function getIndex($page)
    {
        if (!empty($page)) {
            return (new Session)->getPageRecords($page, $this->params);
        } elseif (!empty($this->params)) {
            return (new Session)->getFromParams($this->params);
        } else {
            return Session::all();
        }
    }

    public function get($id)
    {
        if (!empty($id)) {
            return Session::find($id);
        } else {
            return null;
        }
    }

    public function post()
    {
        $session = new Session();
        $errors = $session->validate($this->request->data);

        if (!empty($errors)) {
            return ['errors'=>$errors];
        }

        $session = $session->fill($this->request->data);
        if ($session->save()) {
            return $session;
        } else {
            return $this->request->data;
        }
    }
    
    public function put($id)
    {
        $session = Session::find($id);
        
        if (!$session) {
            return $this->request->data;
        }

        $session->fill($this->request->data);

        $errors = $session->validate($session->toArray());

        if (!empty($errors)) {
            return ['errors'=>$errors];
        }

        if ($session->save()) {
            return $session;
        } else {
            return $this->request->data;
        }
    }

    public function delete($id)
    {
        $session = Session::find($id);
        
        if (!$session) {
            return null;
        }

        if ($session->delete()) {
            return $session;
        } else {
            return $session->toArray();
        }
    }
}

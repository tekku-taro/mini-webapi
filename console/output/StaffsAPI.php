<?php
namespace App\Api;

use App\Models\Session;
use Lib\AppCore\ResourceAPI;
/**
 * StaffsAPI class
 *
 * Session モデルを操作し、getIndex/get/post/put/delete アクションを行うクラス
 */
class StaffsAPI extends ResourceAPI
{
    /**
     * getIndex アクション
     *
     * @param integer $page
     * @return mixed
     */    
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

    /**
     * get アクション
     *
     * @param integer $id
     * @return mixed
     */
    public function get($id)
    {
        if (!empty($id)) {
            return Session::find($id);
        } else {
            return null;
        }
    }

    /**
     * post アクション
     *
     * @return mixed
     */
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

    /**
     * put アクション
     *
     * @param integer $id
     * @return mixed
     */    
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

    /**
     * delete アクション
     *
     * @param integer $id
     * @return mixed
     */
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

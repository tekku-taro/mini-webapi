<?php
namespace App\Api\Admin;

use Route\Request;
use Lib\AppCore\API;
use App\Models\User;

class UsersAPI extends API
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function getIndex($page)
    {
        if (!empty($page)) {
            return (new User)->getPageRecords($page, $this->params);
        } elseif (!empty($this->params)) {
            return (new User)->getFromParams($this->params);
        } else {
            return User::all();
        }
    }

    public function get($id)
    {
        if (!empty($id)) {
            return User::find($id);
        } else {
            return null;
        }
    }

    public function post()
    {
        $errors = User::validate($this->request->data);

        if (!empty($errors)) {
            return ['errors'=>$errors];
        }

        $User = User::create($this->request->data);
        if ($User) {
            return $User;
        } else {
            return $this->request->data;
        }
    }
    
    public function put($id)
    {
        $User = User::find($id);
        
        if (!$User) {
            return $this->request->data;
        }

        $User->fill($this->request->data);

        $errors = User::validate($User->toArray(),$User->id);

        if(!empty($errors)){
            return ['errors'=>$errors];
        }

        if ($User->save()) {
            return User::find($id);
        } else {
            return $this->request->data;
        }
    }

    public function delete($id)
    {
        $User = User::find($id);
        
        if (!$User) {
            return null;
        }

        if ($User->delete()) {
            return $User;
        } else {
            return $User->toArray();
        }
    }
}

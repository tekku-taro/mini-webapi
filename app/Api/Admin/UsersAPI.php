<?php
namespace App\Api\Admin;

use Lib\AppCore\ResourceAPI;
use App\Models\User;

class UsersAPI extends ResourceAPI
{
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
        $user = new User();
        $errors = $user->validate($this->request->data);

        if (!empty($errors)) {
            return ['errors'=>$errors];
        }

        $user = $user->fill($this->request->data);
        if ($user->save()) {
            return $user;
        } else {
            return $this->request->data;
        }
    }
    
    public function put($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return $this->request->data;
        }

        $user->fill($this->request->data);

        $errors = $user->validate($user->toArray(), $user->id);

        if (!empty($errors)) {
            return ['errors'=>$errors];
        }

        if ($user->save()) {
            return $user;
        } else {
            return $this->request->data;
        }
    }

    public function delete($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return null;
        }

        if ($user->delete()) {
            return $user;
        } else {
            return $user->toArray();
        }
    }
}

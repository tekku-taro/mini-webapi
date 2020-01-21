<?php
namespace App\Api\Session;

use Lib\AppCore\API;
use Lib\Auth;
use Lib\JWT;

class SessionsAPI extends API
{
    public function login()
    {
        // postdataからname,passwordを取得し、Auth::verifyCredentials()
        $auth = new Auth;
        $user = $auth->verifyCredentials($this->request->data['name'], $this->request->data['password']);
        if ($user) {
            // getJWT()で取得したトークン返す
            $jwt = new JWT;
            return $jwt->get($user);
        } else {
            // falseならエラーを返す
            return ['status'=>'error','message'=>'Your credentials are not verified.'];
        }
    }

    public function refresh()
    {
        if (!isset($this->request->token) or !isset($this->request->data['access_token'])) {
            return [
                'status'=>'error',
                'message'=>'Token refresh error. Token could not get refreshed.',
            ];
        }

        $refresh = $this->request->token;
        $access = $this->request->data['access_token'];
        // リフレッシュトークンの検証
        $session = Auth::validateRefreshToken($access, $refresh);
        // 更新したトークンを返す
        $jwt = new JWT;
        return $jwt->refresh($session->id);
    }
    
    public function logout()
    {
        if (!isset($this->request->token)) {
            return [
                'status'=>'error',
                'message'=>'Session delete error. You can not end the session.',
            ];
        }

        // Auth::validateToken(token,session)
        $session = Auth::validateToken($this->request->token, get_class($this));
        if ($session) {
            if ($session->delete()) {
                return [
                    'status'=>'success',
                    'message'=>'You have ended the session successfully.',
                ];
            }
        }

        return [
            'status'=>'error',
            'message'=>'Session delete error. You can not end the session.',
        ];
    }
}

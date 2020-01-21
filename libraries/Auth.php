<?php
namespace Lib;

use App\Models\User;
use App\Models\Session;
use DateTime;
use Lib\JWT;

class Auth
{
    public function verifyCredentials($name, $password)
    {
        if(empty($name) or empty($password)){
            return false;
        }

        // user = User::findOne(name)
        $user = User::where('name', $name)->first();

        if(!$user){
            return false;
        }

        // activeでなければincrementして、false
        if (!$user->active) {
            $this->incrementLoginAttempts($user);
            return false;
        }
        // 3<attemptsならincrementして、false
        if ($user->login_attempts >= 3) {
            $this->incrementLoginAttempts($user);
            return false;
        }

        // password_verify(password,hash)：
        if (!password_verify($password, $user->password)) {
            // incrementして、false返す
            $this->incrementLoginAttempts($user);
            return false;
        }
        
        // 検査に通れば、clearしてuser返す
        $this->clearLoginAttempts($user);

        return $user;
    }

    protected function clearLoginAttempts($user)
    {
        // attemptsを0にする
        $user->login_attempts = 0;
        // update(user)
        return $user->save();
    }

    protected function incrementLoginAttempts($user)
    {
        // attemptsを1プラス
        $user->login_attempts += 1;
        // update(user)
        return $user->save();
    }


    // トークンの検証
    public static function validateToken($token,$apiName)
    {
        $jwt = new JWT;
        // ユーザー管理(admin)：
        if($apiName === 'UsersAPI'){
            // 有効なトークンか・管理者か
            $result = $jwt->validate($token,true);
        }else{
            // サービス(zipcodes/session)：
            // 有効なトークンかJWT::validate()
            $result = $jwt->validate($token,false);

        }
        // 結果(session/false)を返す
        return $result;
        
    }
        
    public static function validateRefreshToken($access,$refresh)
    {
        // refreshTokenで検索し、accessTokenが合っているか、invalidatedか、期限内か確認
        $sessions = Session::where('refresh_token', $refresh)->get();

        if($sessions->count() !== 1){
            return false;
        }
        
        $session = $sessions->first();
        if($session->access_token !== $access){
            return false;
        }
        if($session->invalidated){
            return false;
        }
        $timestamp = (new DateTime())->getTimestamp();
        if($session->refresh_token_expiry < $timestamp){
            return false;
        }
        


        // 結果(session/false)を返す
        return $session;
    }
    
    
}

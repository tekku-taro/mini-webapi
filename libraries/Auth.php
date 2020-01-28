<?php
namespace Lib;

use App\Models\User;
use App\Models\Session;
use DateTime;
use Lib\JWT;

/**
 * Auth class
 * ユーザー認証、トークン認証用クラス
 */
class Auth
{
    /**
     * ユーザー認証情報を検証し、結果を返す
     *
     * @param string $name
     * @param string $password
     * @return mixed
     */
    public function verifyCredentials($name, $password)
    {
        if (empty($name) or empty($password)) {
            return false;
        }

        // user = User::findOne(name)
        $user = User::where('name', $name)->first();

        if (!$user) {
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

    /**
     * ユーザーのlogin_attemptsを0でクリア
     *
     * @param User $user
     * @return boolean
     */
    protected function clearLoginAttempts($user)
    {
        // attemptsを0にする
        $user->login_attempts = 0;
        // update(user)
        return $user->save();
    }

    /**
     * ユーザーのlogin_attemptsをインクリメント
     *
     * @param User $user
     * @return boolean
     */
    protected function incrementLoginAttempts($user)
    {
        // attemptsを1プラス
        $user->login_attempts += 1;
        // update(user)
        return $user->save();
    }


    /**
     * jwtトークンの検証
     * apiがUsersAPIの時は、管理者かどうかも検証する
     *
     * @param string $token
     * @param string $apiName
     * @return mixed
     */
    public static function validateToken($token, $apiName)
    {
        $jwt = new JWT;
        
        if ($apiName === 'UsersAPI') {// ユーザー管理(admin)：

            // 有効なトークンか・管理者か
            $result = $jwt->validate($token, true);
        } else {// サービス(zipcodes/session)：

            // 有効なトークンか
            $result = $jwt->validate($token, false);
        }
        // 結果(session/false)を返す
        return $result;
    }

    /**
     * リフレッシュトークンの検証
     *
     * @param string $refresh
     * @return mixed
     */
    public static function validateRefreshToken($refresh)
    {
        // refreshTokenで検索し、invalidatedか、期限内か確認
        $sessions = Session::where('refresh_token', $refresh)->get();

        if ($sessions->count() !== 1) {
            return false;
        }
        
        $session = $sessions->first();
 
        
        if ($session->invalidated) {
            return false;
        }
        $timestamp = (new DateTime())->getTimestamp();
        if ($session->refresh_token_expiry < $timestamp) {
            return false;
        }

        // 結果(session/false)を返す
        return $session;
    }
}

<?php
namespace Lib;

use DateTime;
use App\Models\User;
use App\Models\Session;

class JWT
{
    private $secret = "secret";
    private $algorithm = ['label'=>'HS256','algo'=>"sha256"];
    protected $jti;
    protected $offset = 15 * 60;
    

    public function get(User $user)
    {
        // JWTトークンを作成し、返す
        $acccessToken = $this->createAccess($user);
        $refreshToken = $this->createRefresh($user, $acccessToken);

        if ($refreshToken) {
            return [
                'status'=>'success',
                'access_token'=>$acccessToken,
                'refresh_token'=>$refreshToken,
                'message'=>'you are authorized successfully.',
            ];
        } else {
            return [
                'status'=>'error',
                'message'=>'Token generation error. Token could not be created.',
            ];
        }
        // 'access_token':ACCESS_TOKEN,
        // 'refresh_token':REFRESH_TOKEN,
        // 'message':'you are authorized successfully.'
    }

    public function createRefresh(User $user, $acccessToken)
    {
        // リフレッシュトークン作成
        $refresh = bin2hex(random_bytes(20));

        // 3日後
        $timestamp = $this->getTimestamp(3 * 60 * 60 * 24);

        // リフレッシュトークンをセッションに保存
        // Session::create()
        $session = new Session();
        $session->fill([
            'jti'=>$this->jti,
            'user_id'=>$user->id,
            'access_token'=>$acccessToken,
            'refresh_token'=>$refresh,
            'refresh_token_expiry'=> $timestamp,
        ]);

        $errors = $session->validate($session->toArray());

        if (empty($errors) and $session->save()) {
            return $refresh;
        } else {
            return false;
        }
    }

    public function createAccess(User $user)
    {
        // 仕様に基づき作成
        $this->jti = uniqid();
        $encodedHeader = $this->getHeader();
        $encodedPayload = $this->getPayload($user);

        $encodedSignature = $this->getSignature($encodedHeader, $encodedPayload);

        $jwt = $encodedHeader . "." . $encodedPayload . "." . $encodedSignature;

        return $jwt;
    }

    protected function getHeader()
    {
        // ヘッダー作成
        $header = ['typ'=>'JWT', 'alg'=>$this->algorithm['label']];
        return $this->base64UrlEncode(json_encode($header));
    }

    protected function getPayload(User $user)
    {
        $timestamp = $this->getTimestamp();
        // 15分後
        $expTimestamp = $this->getTimestamp($this->offset);

        // ペイロード作成
        $payload = [
            'jti'=>$this->jti,
            'sub'=>$user->id,
            'iat'=> $timestamp,
            'exp'=> $expTimestamp,
            'name'=>$user->name,
            'admin'=>($user->role === 'admin')? true: false,
        ];

        return $this->base64UrlEncode(json_encode($payload));
    }

    protected function getSignature($encodedHeader, $encodedPayload)
    {
        // シグニチャ作成
        $signature = $this->hashToken($encodedHeader . "." . $encodedPayload);

        return $this->base64UrlEncode($signature);
    }

    protected function base64UrlEncode($input)
    {
        return str_replace(['+','/','='], ['-','_',''],base64_encode($input));
    }
       
    protected function base64UrlDecode($input)
    {
        return base64_decode(str_replace( ['-','_'], ['+','/'],$input));
    }

    protected function hashToken($token)
    {
        return hash_hmac($this->algorithm['algo'], $token, $this->secret, true);
    }

    protected function getTimestamp($offset = 0)
    {
        $now = new DateTime();
        return $now->getTimestamp() + $offset;
    }

    public function validate($token, $checkAdmin)
    {
        $tokenArray = explode(".", $token);

        // トークンの形式がおかしい
        if(count($tokenArray) != 3){
            return false;
        }

        // トークンをデコードし、
        list($encodedHeader, $encodedPayload, $encodedSignature) = $tokenArray;
        $header = json_decode($this->base64UrlDecode($encodedHeader));
        $payload = json_decode($this->base64UrlDecode($encodedPayload));

        // 有効期限が過ぎたらfalse
        $now = $this->getTimestamp();
        if ($payload->exp < $now) {
            return false;
        }


        // alg確認 不適切ならfalse
        if (empty($header->alg) or $header->alg !== $this->algorithm['label']) {
            return false;
        }

        // 管理者チェック
        if ($checkAdmin and $payload->admin == false) {
            return false;
        }

        // シグニチャを正しいものと比較
        $targetSignature = $this->getSignature($encodedHeader, $encodedPayload);
        if ($encodedSignature !== $targetSignature) {
            return false;
        }

        // sessionを返す
        return Session::where('jti', $payload->jti)->first();
    }


    public function refresh($session_id)
    {
        // Session::delete(id)
        $session = Session::find($session_id);
        $user = User::find($session->user_id);

        $session->delete();

        // JWTトークンを作成
        $acccessToken = $this->createAccess($user);
        // createRefresh()
        $refreshToken = $this->createRefresh($user, $acccessToken);

        if ($refreshToken) {
            return [
                'status'=>'success',
                'access_token'=>$acccessToken,
                'refresh_token'=>$refreshToken,
                'message'=>'Token got refreshed successfully.',
            ];
        } else {
            return [
                'status'=>'error',
                'message'=>'Token refresh error. Token could not get refreshed.',
            ];
        }
    }
}

<?php
namespace Lib;

use DateTime;
use App\Models\User;
use App\Models\Session;

class JWT
{
    private $secret = 'secret';
    private $algorithm = ['label'=>'HS256','algo'=>'sha256'];
    protected $jti;
    

    public function get(User $user)
    {
        // JWTトークンを作成し、返す
        $acccessToken = $this->create($user);
        $refreshToken = $this->createRefresh($user, $acccessToken);

        if($refreshToken){
            return [
                'status'=>'success',
                'access_token'=>$acccessToken,
                'refresh_token'=>$refreshToken,
                'message'=>'you are authorized successfully.', 
            ];
        }else{
            return [
                'status'=>'error',
                'message'=>'Token generation error. Token could not be created.', 
            ];
        }
        // 'access_token':ACCESS_TOKEN,
        // 'refresh_token':REFRESH_TOKEN,
        // 'message':'you are authorized successfully.'
        

    }

    public function createRefresh(User $user,$acccessToken)
    {
        // リフレッシュトークン作成
        $refresh = bin2hex(random_bytes(20));

        $hashedRefresh = $this->hashToken($refresh);

        // 3日後
        $timestamp = $this->getTimestamp(3 * 60 * 60 * 24);

        // リフレッシュトークンをセッションに保存
        // Session::create()
        $session = new Session();
        $session->fill([
            'jti'=>$this->jti,
            'user_id'=>$user->id,
            'access_token'=>$acccessToken,
            'refresh_token'=>$hashedRefresh,
            'refresh_token_expiry'=> $timestamp,
        ]);

        $errors = Session::validate($session->toArray());

        if(empty($errors) and $session->save()){
            return $hashedRefresh;
        }else{
            return false;
        }
        
    }

    public function create(User $user)
    {
        // 仕様に基づき作成
        $this->jti = uniqid();
        $encodedHeader = $this->getHeader();
        $encodedPayload = $this->getPayload($user);

        $encodedSignature = $this->getSignature($encodedHeader, $encodedPayload);

        $jwt = $encodedHeader . "." . $encodedPayload . $encodedSignature;

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
        // 15分後
        $timestamp = $this->getTimestamp(15 * 60);

        // ペイロード作成
        $payload = [
            'jti'=>$this->jti,
            'sub'=>$user->id,
            'iat'=> $timestamp,
            'exp'=> $timestamp,
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
        return strtr(base64_encode($input), '+/=', '~_-');
    }
       
    protected function base64UrlDecode($input) 
    {
        return base64_decode(strtr($input, '~_-', '+/='));
    }    

    protected function hashToken($token)
    {
        return hash_hmac($this->algorithm['algo'], $token, $this->secret);
    }

    protected function getTimestamp($offset = 0)
    {
        $now = new DateTime();
        return $now->getTimestamp() + $offset;        
    }

    public function validate($token, $checkAdmin):Session
    {
        // トークンをデコードし、
        list($encodedHeader, $encodedPayload, $encodedSignature) = explode(".", $token);
        $header = json_decode( $this->base64UrlDecode($encodedHeader));
        $payload = json_decode( $this->base64UrlDecode($encodedPayload));

        // 有効期限が過ぎたらfalse
        $now = $this->getTimestamp();
        if($payload->exp > $now){
            return false;
        }


        // alg確認 不適切ならfalse
        if($header->alg !== $this->algorithm['label']){
            return false;
        }

        // 管理者チェック
        if($checkAdmin and $payload->admin == false){
            return false;
        }

        // シグニチャを正しいものと比較
        $restoredSignature = $this->getSignature($encodedHeader, $encodedPayload);
        if($encodedSignature !== $restoredSignature){
            return false;
        }

        // sessionを返す
        return Session::find()->where('jti',$payload->jti)->first();
        
    }


    public function refresh($session_id)
    {
        // Session::delete(id)
        $session = Session::find($session_id);
        $user = User::find($session->user_id);

        $session->delete();

        // JWTトークンを作成
        $acccessToken = $this->create($user);
        // createRefresh()
        $refreshToken = $this->createRefresh($user, $acccessToken);

        if($refreshToken){
            return [
                'status'=>'success',
                'access_token'=>$acccessToken,
                'refresh_token'=>$refreshToken,
                'message'=>'Token got refreshed successfully.', 
            ];
        }else{
            return [
                'status'=>'error',
                'message'=>'Token refresh error. Token could not get refreshed.', 
            ];
        }        
    }


}

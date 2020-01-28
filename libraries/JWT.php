<?php
namespace Lib;

use DateTime;
use App\Models\User;
use App\Models\Session;

/**
 * JWT class
 * jwtトークンの作成、更新、検証のためのクラス
 */
class JWT
{
    /**
     * jwt作成時に使用するsecret
     *
     * @var string
     */
    private $secret = "secret";

    /**
     * jwtトークンの暗号アルゴリズム
     *
     * @var array
     */
    private $algorithm = ['label'=>'HS256','algo'=>"sha256"];

    /**
     * jwtトークンID
     *
     * @var string
     */
    protected $jti;

    /**
     * アクセストークン有効期限(exp)を現在から何秒後に設定するか
     *
     * @var integer
     */
    protected $offset = 15 * 60;// 15分後

    /**
     * リフレッシュトークン有効期限を現在から何秒後に設定するか
     *
     * @var integer
     */
    protected $r_offset = 3 * 60 * 60 * 24;// 3日後
    

    /**
     * JWTトークンを作成し、返す
     *
     * @param User $user
     * @return array
     */
    public function get(User $user)
    {
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
    }

    /**
     * リフレッシュトークン作成
     *
     * @param User $user
     * @param string $acccessToken
     * @return mixed
     */
    public function createRefresh(User $user, $acccessToken)
    {
        $refresh = bin2hex(random_bytes(20));

        // リフレッシュトークンの有効期限
        $timestamp = $this->getTimestamp($this->r_offset);

        // リフレッシュトークンをセッションに保存
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

    /**
     * アクセストークンの作成
     *
     * @param User $user
     * @return string $jwt
     */
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

    /**
     * ヘッダー作成
     *
     * @return string
     */
    protected function getHeader()
    {
        $header = ['typ'=>'JWT', 'alg'=>$this->algorithm['label']];
        return $this->base64UrlEncode(json_encode($header));
    }

    /**
     * ペイロード作成
     *
     * @param User $user
     * @return string
     */
    protected function getPayload(User $user)
    {
        $timestamp = $this->getTimestamp();
        
        $expTimestamp = $this->getTimestamp($this->offset);

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

    /**
     * シグニチャ作成
     *
     * @param string $encodedHeader
     * @param string $encodedPayload
     * @return string
     */
    protected function getSignature($encodedHeader, $encodedPayload)
    {
        $signature = $this->hashToken($encodedHeader . "." . $encodedPayload);

        return $this->base64UrlEncode($signature);
    }

    /**
     * base64でurlセーフなエンコードを行う
     *
     * @param string $input
     * @return string
     */
    protected function base64UrlEncode($input)
    {
        return str_replace(['+','/','='], ['-','_',''], base64_encode($input));
    }

    /**
     * base64でurlセーフなデコードを行う
     *
     * @param string $input
     * @return string
     */
    protected function base64UrlDecode($input)
    {
        return base64_decode(str_replace(['-','_'], ['+','/'], $input));
    }

    /**
     * HMACメソッドを使って設定したアルゴリズムでトークンからハッシュ値を生成
     *
     * @param string $token
     * @return string
     */
    protected function hashToken($token)
    {
        return hash_hmac($this->algorithm['algo'], $token, $this->secret, true);
    }

    /**
     * タイムスタンプの作成
     *
     * @param integer $offset
     * @return integer
     */
    protected function getTimestamp($offset = 0)
    {
        $now = new DateTime();
        return $now->getTimestamp() + $offset;
    }

    /**
     * jwtトークンを分析し、検証して結果を返す
     *
     * @param string $token
     * @param boolean $checkAdmin
     * @return mixed
     */
    public function validate($token, $checkAdmin)
    {
        $tokenArray = explode(".", $token);

        // トークンの形式がおかしい
        if (count($tokenArray) != 3) {
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


    /**
     * jwtトークンを更新する
     *
     * @param integer $session_id
     * @return array
     */
    public function refresh($session_id)
    {
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

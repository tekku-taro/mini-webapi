<?php
namespace Route;

use Bootstrap\Config;

/**
 * Request class
 * リクエストデータを処理
 */
class Request
{
    /**
     * url文字列
     *
     * @var string
     */
    public $url;

    /**
     * api名
     *
     * @var string
     */
    public $api;

    /**
     * 管理者向けapiか
     *
     * @var boolean
     */
    public $isAdmin = false;

    /**
     * 認証用apiか
     *
     * @var boolean
     */
    public $isSession = false;

    /**
     * システムで提供するAPIリスト
     *
     * @var array
     */
    protected $apiTypes = [];

    /**
     * リクエストメソッド GET/POST/PUT/DELETE
     *
     * @var string
     */
    public $method;

    /**
     * パラメータ配列
     *
     * @var array
     */
    public $params = [];

    /**
     * リクエストボディのデータ
     *
     * @var array
     */
    public $data;

    /**
     * jwtトークン文字列
     *
     * @var string
     */
    public $token;

    /**
     * HTTP_ACCEPT の簡略形 xml/json
     *
     * @var string
     */
    public $accept;

    /**
     * 読み込み用のストリーム
     * リクエストボディから生のデータを読み込む
     *
     * @var string
     */
    protected $fileIn = 'php://input';

    /**
     * システムで利用可能なMIMEタイプ
     *
     * @var array
     */
    protected $supportedTypes = ['xml'=>['text/xml','application/xml'],'json'=>['application/json']];

    /**
     * url、リクエストメソッド、APIタイプの設定
     */
    public function __construct()
    {
        if (isset($_GET['url'])) {
            $this->url = $_GET['url'];
            unset($_GET['url']);
        } else {
            $this->url = '/';
        }

        $this->apiTypes = Config::get('API');
        
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * リクエストデータを分解して各変数に保存
     *
     * @return void
     */
    public function parseURL()
    {
        // urlをsanitizeして、右端の/を削除
        $url = rtrim(filter_var($this->url, FILTER_SANITIZE_URL), "/");
        // 'http://test_com/users/1?key=value'
        // /で分割し、serviceNameかparamsに格納
        $url = explode("/", $url);
        
        // serviceName
        if (!empty($url[0])) {
            if (in_array($url[0], $this->apiTypes)) {
                $this->api = $url[0];
                array_shift($url);
                if ($this->api === 'sessions') {
                    $this->isSession = true;
                }
            } elseif ($url[0] === 'admin') {
                $this->isAdmin = true;
                array_shift($url);
                if (isset($url[0]) and in_array($url[0], $this->apiTypes)) {// users
                    $this->api = $url[0];
                    array_shift($url);
                }
            } else {
                throw new \ErrorException("page not found.", 404);
            }
        } else {
            throw new \ErrorException("page not found.", 404);
        }
        // id or page
        if (isset($url[0])) {
            if (ctype_digit($url[0])) {
                $this->params['id'] = intval($url[0]);
            } elseif ($url[0] === 'page') {
                if (isset($url[1]) and ctype_digit($url[1])) {
                    $this->params['page'] = intval($url[1]);
                }
            }
        }

        $this->params = array_merge($this->params, $_GET);

        // headerからaccept/contentTypeを取得
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            if (in_array($_SERVER['HTTP_ACCEPT'], $this->supportedTypes['xml'])) {
                $this->accept = 'xml';
            } elseif (in_array($_SERVER['HTTP_ACCEPT'], $this->supportedTypes['json'])) {
                $this->accept = 'json';
            }
        }
        // convPostData(postdata)をdataに格納
        if (!empty($_POST)) {
            $this->data = $this->convPostData($_POST);
        } elseif (!empty($data = file_get_contents($this->fileIn))) {
            $this->data = $this->convPostData($data);
        }
        // Auth headerからaccesstokenをtokenに格納
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeaders = explode(" ", $_SERVER['HTTP_AUTHORIZATION']) ;
            if (!empty($authHeaders[1])) {
                $this->token = $authHeaders[1];
            }
        }


        return $this;
    }

    /**
     * リクエストボディのjsonデータまたはxmlデータを配列に変換
     *
     * @param mixed $data
     * @return mixed
     */
    protected function convPostData($data)
    {
        // request bodyにより配列に変換
        if (isJson($data)) {//conv to json
            return jsonToArray($data);
        } else {//conv to xml
            $result = xmlToArray($data);
            if ($result) {
                return $result;
            } else {//return raw data
                return $data;
            }
        }
    }
}

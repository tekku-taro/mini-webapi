<?php
namespace Route;

use Route\Request;

/**
 * Dispatcher class
 * リクエストを受け取り、適切なAPIクラスのアクションを呼び出す
 *
 */
class Dispatcher
{
    /**
     * リクエストオブジェクト
     *
     * @var Request
     */
    protected $request;

    /**
     * アクションルートリスト
     *
     * @var array
     */
    public $actionRoutes = [];

    /**
     * Requestオブジェクトを受け取り、全てのアクションルートを設定
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->addAction(["sessions","POST"], "login");
        $this->addAction(["sessions","PUT"], "refresh");
        $this->addAction(["sessions","DELETE"], "logout");

        $this->addAction(["?","GET"], function () use ($request) {
            if (isset($request->params['id'])) {
                return "get";
            } else {
                return "getIndex";
            }
        });
        $this->addAction(["?","POST"], "post");
        $this->addAction(["?","PUT"], "put");
        $this->addAction(["?","DELETE"], "delete");
    }

    /**
     * アクションルートの追加
     *
     * @param array $data   api名(?は任意)とリクエストメソッド
     * @param string $action    対応するクラスのアクション名
     * @return void
     */
    public function addAction($data, $action)
    {
        list($api, $method) = $data;
        $this->actionRoutes[$api . "@" . $method] = $action;
    }

    /**
     * リクエストのapiとメソッドから対応するアクション名を返す
     *
     * @param string $api
     * @param string $method
     * @return void
     */
    protected function matchAction($api, $method)
    {
        $key = $api . "@" . $method;
        $defaultKey = "?@" . $method;

        if (isset($this->actionRoutes[$key])) {
            $action = $this->actionRoutes[$key];
        } elseif (isset($this->actionRoutes[$defaultKey])) {
            $action =  $this->actionRoutes[$defaultKey];
        } else {
            return false;
        }

        if (is_callable($action)) {
            return $action();
        } else {
            return $action;
        }
    }

    /**
     * 適切なAPIクラスにリクエストオブジェクトを渡す
     * リクエストに対応するアクションを呼び出す
     *
     * @return void
     */
    public function send()
    {
        // リクエストのAPIクラスの存在を確認後、
        $apiClass = $this->getNamespace() . getAPIName($this->request->api);
        if (!class_exists($apiClass)) {
            throw new \ErrorException("apiClass {$apiClass} not found.");
        }
        // new API(request) requestを渡す
        $apiObj = new $apiClass($this->request);

        $action = $this->matchAction($this->request->api, $this->request->method) . "Action";
        if ($action === false) {
            throw new \ErrorException("no matched action in  apiClass {$apiClass}");
        }

        // メソッドを呼び出し
        $apiObj->$action();
    }



    /**
     * 適切なネームスペースを返す
     *
     * @return void
     */
    protected function getNamespace()
    {
        if ($this->request->isAdmin) {
            return "App\\Api\\Admin\\";
        } elseif ($this->request->isSession) {
            return "App\\Api\\Session\\";
        } else {
            return "App\\Api\\";
        }
    }
}

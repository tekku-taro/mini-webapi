<?php
namespace Lib\AppCore;

use Route\Request;
use Lib\AppCore\Response;

/**
 * API class
 * 全てのAPIクラスの親クラス
 */
abstract class API
{
    /**
     * リクエストオブジェクト
     *
     * @var Request
     */
    protected $request;

    /**
     * パラメータ配列
     *
     * @var array
     */
    protected $params;

    /**
     * リクエストオブジェクトを取得
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    /**
     * アクションを呼び出すときに最初に呼ばれる
     * beforeFilter, アクション, afterFilterを順に呼び出し、
     * 最後にsendBackに返されたデータを渡す
     *
     * @param string $name
     * @param array $arguments
     * @return void
     */
    public function __call(string $name, array $arguments)
    {
        $action = str_replace("Action", "", $name);

        $responseData = [];

        if (!$this->beforeFilter($action, $arguments)) {
            return false;
        }
        
        $responseData = $this->callMethod($action, $this->request->params);
        
        if (!$this->afterFilter($action, $arguments)) {
            return false;
        }

        $this->sendBack($responseData);
    }

    /**
     * アクションの前処理
     *
     * @param string $name
     * @param array $arguments
     * @return boolean true/false 処理の結果
     */
    protected function beforeFilter(string $name, array $arguments)
    {
        return true;
    }

    /**
     * アクションの後処理
     *
     * @param string $name
     * @param array $arguments
     * @return boolean true/false 処理の結果
     */
    protected function afterFilter(string $name, array $arguments)
    {
        return true;
    }

    /**
     * アクション関数に引数を渡して呼び出し、
     * 戻り値をフォーマットして返す
     *
     * @param string $action
     * @param array $params
     * @return void
     */
    protected function callMethod($action, $params)
    {
        if (!method_exists($this, $action)) {
            throw new \ErrorException("method {$action} doesn't exists in  apiClass ". get_class($this));
        }

        list($args, $this->params) = $this->divideParamsIntoTwo($action, $params);

        $response = call_user_func_array([$this,$action], $args);

        return $this->formatResponse($response, $action, $params);
    }

    /**
     * リスポンスデータのフォーマット用関数
     *
     * @param array $response
     * @param string $action
     * @param array $params
     * @return void
     */
    protected function formatResponse($response, $action, $params)
    {
        return $response;
    }

    /**
     * 呼び出されるアクション関数の引数が$paramsにあれば
     * $argsに格納し、$paramsの残りとともに返す
     *
     * @param string $action
     * @param array $params
     * @return void
     */
    protected function divideParamsIntoTwo($action, $params)
    {
        $reflectionMethod = new \ReflectionMethod($this, $action);
        $args = [];
        foreach ($reflectionMethod->getParameters() as $parameter) {
            if (isset($params[$parameter->name])) {
                $args[] = $params[$parameter->name];
                unset($params[$parameter->name]);
            } else {
                $args[] = null;
            }
        }

        return [$args,$params];
    }


    /**
     * API処理の結果をjsonかxmlの形式でクライアントに送信
     *
     * @param array $responseData
     * @return void
     */
    protected function sendBack($responseData)
    {
        // acceptによって、
        // 戻り値をResponse::json()かxml()に渡す
        if ($this->request->accept === 'xml') {
            Response::xml($responseData);
        } else {
            Response::json($responseData);
        }
    }
}

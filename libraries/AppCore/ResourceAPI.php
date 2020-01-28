<?php
namespace Lib\AppCore;

use Lib\AppCore\Response;
use Lib\AppCore\API;
use Lib\Auth;

/**
 * ResourceAPI class
 * 一般的なリソース操作用APIクラスの親クラス
 */
abstract class ResourceAPI extends API
{
    /**
     * リクエストオブジェクト
     *
     * @var Request
     */
    protected $request;


    /**
     * アクションを呼び出す前に、
     * リクエストのトークンを検証する
     * 検証失敗したら、エラーメッセージをクライアントに返す
     *
     *
     * @param string $action
     * @param array $arguments
     * @return boolean
     */
    protected function beforeFilter(string $name, array $arguments)
    {
        $apiName = get_class($this);

        if (isset($this->request->token)) {
            $session = Auth::validateToken($this->request->token, $apiName);
            if ($session) {
                return true;
            }
        }
        // falseならエラー処理
        $errorMessage = [
            'status'=>'error',
            'message'=>"You are not authorized to access {$apiName}.",
        ];

        $this->sendBack($errorMessage);
        
        return false;
    }


    /**
     * リスポンスデータのフォーマット用関数
     * アクションと処理の結果によりResponse::formatData関数で
     * 適切な書式でデータを整形して返す
     *
     * @param array $response
     * @param string $action
     * @param array $params
     * @return array
     */
    protected function formatResponse($response, $action, $params)
    {
        $id = null;
        $modelName = getModelNameFromAPI((new \ReflectionClass(static::class))->getShortName());

        if ($response and !is_array($response)) {//処理成功
            if ($action === 'getIndex') {
                return Response::formatData($modelName, $action, true, null, $response);
            } else {
                return Response::formatData($modelName, $action, true, $response->id, $response);
            }
        } else {//処理失敗
            if (isset($params['id'])) {
                $id = $params['id'];
            }
            return Response::formatData($modelName, $action, false, $id, $response);
        }
    }
}

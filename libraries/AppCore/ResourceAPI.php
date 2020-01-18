<?php
namespace Lib\AppCore;

use Lib\AppCore\Response;
use Lib\AppCore\API;
use Lib\Auth;

abstract class ResourceAPI extends API
{
    protected $request;
    protected $authHeader;


    protected function beforeFilter($action, $arguments)
    {
        if (isset($this->request->token)) {
            $access = $this->request->token;
            $apiName = get_class($this);
            $session = Auth::validateToken($access, $apiName);
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


    protected function formatResponse($response, $action, $params)
    {
        $id = null;
        $modelName = getModelNameFromAPI((new \ReflectionClass(static::class))->getShortName());
        // var_dump($response);die();
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

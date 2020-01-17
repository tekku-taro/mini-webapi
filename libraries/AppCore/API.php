<?php
namespace Lib\AppCore;

use Route\Request;
use Lib\AppCore\Response;

abstract class API
{
    protected $request;
    protected $params;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    public function __call($name, $arguments)
    {
        $action = str_replace("Action", "", $name);


        $this->beforeFilter($action, $arguments);
        $responseData = $this->callMethod($action, $this->request->params);
        $this->afterFilter($action, $arguments);

        $this->sendBack($responseData);
    }

    protected function beforeFilter($action, $arguments)
    {
        // print(" [beforeFilter] ");
    }

    protected function afterFilter($action, $arguments)
    {
        // print(" [afterFilter] ");
    }

    protected function callMethod($action, $params)
    {
        if (!method_exists($this, $action)) {
            throw new \ErrorException("method {$action} doesn't exists in  apiClass ". get_class($this));            
        }

        list($args, $this->params) = $this->divideParamsIntoTwo($action, $params);
        // var_dump($this->params);
        $response = call_user_func_array([$this,$action], $args);
        $id = null;
        $modelName = getModelNameFromAPI((new \ReflectionClass(static::class))->getShortName());        
        // var_dump($response);die();
        if($response and !is_array($response)){//処理成功
            if($action === 'getIndex'){
                return Response::formatData($modelName,$action,true,null,$response);
            }else{
                return Response::formatData($modelName,$action,true,$response->id,$response);
            }
        }else{//処理失敗
            if(isset($params['id'])){
                $id = $params['id'];
            }
            return Response::formatData($modelName,$action,false,$id,$response);
        }         
    }

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


    protected function sendBack($responseData)
    {
        // acceptによって、
        // 戻り値をResponse::json()かxml()に渡す
        if($this->request->accept === 'xml'){
            Response::xml($responseData);
        }else{
            Response::json($responseData);
        }
    }    
}

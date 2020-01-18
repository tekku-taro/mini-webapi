<?php
namespace Lib\AppCore;

use Route\Request;
use Lib\AppCore\Response;

abstract class API
{
    protected $request;
    protected $authHeader;
    protected $params;

    public function __construct(Request $request)
    {
        $this->request = $request;

    }


    public function __call($name, $arguments)
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

    protected function beforeFilter($action, $arguments)
    {
        // print(" [beforeFilter] ");
        return true;
    }

    protected function afterFilter($action, $arguments)
    {
        // print(" [afterFilter] ");
        return true;
    }

    protected function callMethod($action, $params)
    {
        if (!method_exists($this, $action)) {
            throw new \ErrorException("method {$action} doesn't exists in  apiClass ". get_class($this));
        }

        list($args, $this->params) = $this->divideParamsIntoTwo($action, $params);
        // var_dump($this->params);
        $response = call_user_func_array([$this,$action], $args);

        return $this->formatResponse($response, $action, $params);
    }

    protected function formatResponse($response, $action, $params)
    {
        return $response;
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
        if ($this->request->accept === 'xml') {
            Response::xml($responseData);
        } else {
            Response::json($responseData);
        }
    }
}

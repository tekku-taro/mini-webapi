<?php
namespace Route;

use Route\Request;

class Dispatcher
{
    protected $request;
    public $actionRoutes = [];

    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->addAction(["sessions","POST"],"login");
        $this->addAction(["sessions","PUT"],"refresh");
        $this->addAction(["sessions","DELETE"],"logout");

        $this->addAction(["?","GET"],function() use($request) {
            if(isset($request->params['id']) ){
                return "get";
            }else{
                return "getIndex";
            }
        });
        $this->addAction(["?","POST"],"post");
        $this->addAction(["?","PUT"],"put");
        $this->addAction(["?","DELETE"],"delete");        

    }

    public function addAction($data,$action)
    {
        list($api,$method) = $data;
        $this->actionRoutes[$api . "@" . $method] = $action;
    }

    protected function matchAction($api, $method)
    {
        $key = $api . "@" . $method;
        $defaultKey = "?@" . $method;

        if(isset($this->actionRoutes[$key])){
            $action = $this->actionRoutes[$key];
        }elseif(isset($this->actionRoutes[$defaultKey])){
            $action =  $this->actionRoutes[$defaultKey];
        }else{
            return false;
        }

        if(is_callable($action)){
            return $action();
        }else{
            return $action;
        }

    }

    public function send()
    {
        // リクエストのAPIクラスの存在を確認後、
        $apiClass = $this->getNamespace() . getAPIName($this->request->api);
        if(!class_exists($apiClass)){
            throw new \ErrorException("apiClass {$apiClass} not found.");

        }
        // new API(request) requestを渡す
        $apiObj = new $apiClass($this->request);

        $action = $this->matchAction($this->request->api, $this->request->method) . "Action";
        if($action === false){
            throw new \ErrorException("no matched action in  apiClass {$apiClass}");
         
        }

        // メソッドを呼び出し
        $apiObj->$action();



    }



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

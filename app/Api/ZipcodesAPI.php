<?php
namespace App\Api;

use Route\Request;
use Lib\AppCore\API;
use App\Models\Zipcode;

class ZipcodesAPI extends API
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function getIndex($page)
    {
        if (!empty($page)) {
            return (new Zipcode)->getPageRecords($page,$this->params);
        } elseif (!empty($this->params)) {
            return (new Zipcode)->getFromParams($this->params);            
        } else {
            return Zipcode::all();
        }

    }

    public function get($id)
    {
        if (!empty($id)) {
            return Zipcode::find($id);
        }else{
            return null;
        }

    }

    public function post()
    {
        $errors = Zipcode::validate($this->request->data);

        if(!empty($errors)){
            return ['errors'=>$errors];
        }

        $zipcode = Zipcode::create($this->request->data);
        if($zipcode){
            return $zipcode;
        }else{
            return $this->request->data;
        }              

    }
    
    public function put($id)
    {

        $zipcode = Zipcode::find($id);
        
        if(!$zipcode){
            return $this->request->data;
        }

        $zipcode->fill($this->request->data);

        $errors = Zipcode::validate($zipcode->toArray());

        if(!empty($errors)){
            return ['errors'=>$errors];
        }

        if($zipcode->save()){
            return Zipcode::find($id);
        }else{
            return $this->request->data;
        }

    }

    public function delete($id)
    {
        $zipcode = Zipcode::find($id);
        
        if(!$zipcode){
            return null;
        }

        if($zipcode->delete()){
            return $zipcode;
        }else{
            return $zipcode->toArray();
        }        
    }
}

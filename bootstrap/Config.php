<?php
namespace Bootstrap;

class Config
{
    protected static $data;

    public static function get($key)
    {
        if (isset(static::$data[$key])) {
            return static::$data[$key];
        } else {
            return null;
        }
    }

    public static function all()
    {
        return static::$data;
    }

    public static function set($key, $value)
    {
        static::$data[$key] =$value;
    }

    public static function load($path)
    {
        // $lines = ["APP_NAME=taro/webapi_fw", "APP_ENV=development", ...];
        $lines = file($path, FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $key => $line) {
            $line = trim(explode("#",$line)[0]);
            if(!empty($line)){
                list($itemName, $itemValue) = explode("=", $line);
                if(strpos($itemValue,",") !==false){
                    $itemValue = array_map("trim", explode(",",$itemValue));
                }else{
                    $itemValue = trim($itemValue);
                }
                static::$data[trim($itemName)] = $itemValue; 
            }
        }
    }
}

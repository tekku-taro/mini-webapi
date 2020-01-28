<?php
namespace Bootstrap;

/**
 * Config class
 */
class Config
{
    /**
     * 設定データ
     *
     * @var array $data
     */
    protected static $data;

    /**
     * getter
     *
     * @param string $key
     * @return void
     */
    public static function get($key)
    {
        if (isset(static::$data[$key])) {
            return static::$data[$key];
        } else {
            return null;
        }
    }

    /**
     *設定データを全て返す
     *
     * @return array $data
     */
    public static function all()
    {
        return static::$data;
    }

    /**
     * setter
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set($key, $value)
    {
        static::$data[$key] =$value;
    }

    /**
     * 設定ファイル(.env)を$dataに読み込む
     *
     * @param string $path
     * @return void
     */
    public static function load($path)
    {
        // $lines = ["APP_NAME=taro/webapi_fw", "APP_ENV=development", ...];
        $lines = file($path, FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $key => $line) {
            $line = trim(explode("#", $line)[0]);
            if (!empty($line)) {
                list($itemName, $itemValue) = explode("=", $line);
                if (strpos($itemValue, ",") !==false) {
                    $itemValue = array_map("trim", explode(",", $itemValue));
                } else {
                    $itemValue = trim($itemValue);
                }
                static::$data[trim($itemName)] = $itemValue;
            }
        }
    }
}

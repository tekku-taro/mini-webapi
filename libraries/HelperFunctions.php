<?php
function getTableName($class)
{
    // クラス名からテーブル名を取得
    // TableName => table_names
    return ltrim(strtolower(preg_replace("/[A-Z]/", "_$0", $class)), "_") . "s";
}

function getModelNameFromAPI($api)
{
    // API名からモデル名を取得
    // TableNamesAPI => TableName    
    return rtrim(rtrim($api,"API"),"s");
}

function getAPIName($name)
{
    // urlのリソース名からAPI名を取得
    // hoge_names => HogeNamesAPI
    return str_replace(" ","",ucwords(str_replace("_"," ", $name))) . "API";

}

function isJson($string)
{
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}
   
function xmlToArray($xml)
{
    libxml_use_internal_errors(true);

    // xmlデータを配列に変換して返す
    $doc = simplexml_load_string($xml);
    if ($doc) {
        $json = json_encode($doc);
        return json_decode($json, true);
    } else {
        return false;
    }
}

function jsonToArray($json)
{
    // jsonデータを配列に変換して返す
    return json_decode($json, true);
}

function arrayToXML($data)
{
    // 配列(data)をxmlデータに変換して返す
    $xml = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
   
    convertSubTree($data, $xml);

    return $xml->asXML();
}

function convertSubTree($data, &$parentNode)
{
    if(!is_array($data)){
        $parentNode->addChild("item", htmlspecialchars($data));        
    }else{
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subVal) {
                    $subNode = $parentNode->addChild($key);
                    convertSubTree($subVal, $subNode);
                }
            } else {
                $parentNode->addChild($key, htmlspecialchars($value));
            }
        }

    }

}

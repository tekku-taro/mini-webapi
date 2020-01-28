<?php
/**
 * HelperFunctions.php
 * データ変換用ヘルパー関数
 */

/**
 * クラス名からテーブル名を取得
 * TableName => table_names
 *
 * @param string $class
 * @return string
 */
function getTableName($class)
{
    return ltrim(strtolower(preg_replace("/[A-Z]/", "_$0", $class)), "_") . "s";
}

/**
 * API名からモデル名を取得
 * TableNamesAPI => TableName
 *
 * @param string $api
 * @return string
 */
function getModelNameFromAPI($api)
{
    return rtrim(rtrim($api, "API"), "s");
}

/**
 * urlのリソース名からAPI名を取得
 * hoge_names => HogeNamesAPI
 *
 * @param string $name
 * @return string
 */
function getAPIName($name)
{
    return str_replace(" ", "", ucwords(str_replace("_", " ", $name))) . "API";
}

/**
 * $stringがjsonオブジェクトかどうかチェック
 *
 * @param string $string
 * @return boolean
 */
function isJson($string)
{
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}
   
/**
 * xmlデータを配列に変換して返す
 *
 * @param string $xml
 * @return mixed
 */
function xmlToArray($xml)
{
    libxml_use_internal_errors(true);

    $doc = simplexml_load_string($xml);
    if ($doc) {
        $json = json_encode($doc);
        return json_decode($json, true);
    } else {
        return false;
    }
}

/**
 * jsonデータを配列に変換して返す
 *
 * @param string $json
 * @return array
 */
function jsonToArray($json)
{
    return json_decode($json, true);
}

/**
 * 配列(data)をxmlデータに変換して返す
 *
 * @param array $data
 * @return mixed
 */
function arrayToXML($data)
{
    $xml = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
   
    convertSubTree($data, $xml);

    return $xml->asXML();
}

/**
 * xmlオブジェクトのノードに再帰的にデータを追加する
 *
 * @param mixed $data
 * @param SimpleXMLElement $parentNode
 * @return void
 */
function convertSubTree($data, $parentNode)
{
    if (!is_array($data)) {
        $parentNode->addChild("item", htmlspecialchars($data));
    } else {
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

<?php
namespace Lib\AppCore;

/**
 * Response class
 * リスポンスデータ処理用クラス
 */
class Response
{
    /**
     * データをjsonフォーマットに変換してクライアントに返す
     *
     * @param mixed $data
     * @param integer $statusCode
     * @return void
     */
    public static function json($data, $statusCode = 200)
    {
        // jsonデータでデータを返す
        header("Access-Control-Allow-Origin: *");
        header('Content-Type: application/json', true, $statusCode);
        print(json_encode($data));
    }
    
    /**
     * データをxmlフォーマットに変換してクライアントに返す
     *
     * @param mixed $data
     * @param integer $statusCode
     * @return void
     */
    public static function xml($data, $statusCode = 200)
    {
        // xmlデータで返す
        $xml = arrayToXML($data);

        header("Access-Control-Allow-Origin: *");
        header('Content-Type: application/xml', true, $statusCode);
        print($xml);
    }
    
    /**
     * 引数の内容に従ってデータを一定の形式でフォーマットして返す
     *
     *  [
     *      'data':[
     *          [id=1,zipcode="101-0001",address="A県B市C町"],
     *          [id=1,zipcode="101-0002",address="A県B市D町"],
     *      ],
     *      'count': 100,
     *      'message':'100 zipcode records found.'
     *  ]
     *
     *  errorsがある場合
     *  ...
     *  'data':['errors'=>[...]]
     *  ...
     *
     * @param string $modelName
     * @param string $action
     * @param boolean $success
     * @param integer $id
     * @param mixed $data
     * @return array
     */
    public static function formatData($modelName, $action, $success = true, $id = null, $data = null)
    {
        if (isset($data) and $action === 'getIndex') {
            $count = $data->count();
        } elseif (isset($data) and !isset($data['errors'])) {
            $count = 1;
        } else {
            $count = 0;
        }
        $message = static::createMessage($id, $action, $success, $modelName, $count);
        return ['data'=>$data,'count'=>$count,'message'=>$message];
    }

    /**
     * アクションの種類と成功したかで結果のメッセージを作成して返す
     *
     * @param integer $id
     * @param string $action
     * @param boolean $success
     * @param string $modelName
     * @param integer $count
     * @return string
     */
    public static function createMessage($id, $action, $success, $modelName, $count)
    {
        switch ($action) {
            case 'getIndex':
                if ($success) {
                    return "$count $modelName records found";
                } else {
                    return "No $modelName records found";
                }
                break;
            case 'get':
                if ($success) {
                    return "$modelName record (ID:{$id}) found";
                } else {
                    return "$modelName record not found";
                }
                return "$count $modelName record found";
                break;
            case 'post':
                if ($success) {
                    return "$modelName record (ID:{$id}) created successfully";
                } else {
                    return "$modelName record can not be created";
                }
                break;
            case 'put':
                if ($success) {
                    return "$modelName record (ID:{$id}) updated successfully";
                } else {
                    return "$modelName record (ID:{$id}) can not be updated";
                }
                break;
            case 'delete':
                if ($success) {
                    return "$modelName record (ID:{$id}) deleted successfully";
                } else {
                    return "$modelName record (ID:{$id}) can not be deleted";
                }
                break;
            default:
                if ($success) {
                    return "$action Action complete successfully";
                } else {
                    return "$action Action failed";
                }
                break;
        }
    }
}

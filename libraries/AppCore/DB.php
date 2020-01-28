<?php
namespace Lib\AppCore;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * DB class
 * トランザクション制御用のクラス
 */
class DB extends Eloquent
{
    /**
     * トランザクションの開始
     *
     * @return void
     */
    public static function beginTransaction()
    {
        self::getConnectionResolver()->connection()->beginTransaction();
    }

    /**
     * トランザクションの確定処理
     *
     * @return void
     */
    public static function commit()
    {
        self::getConnectionResolver()->connection()->commit();
    }

    /**
     * トランザクションの取り消し処理
     *
     * @return void
     */
    public static function rollBack()
    {
        self::getConnectionResolver()->connection()->rollBack();
    }
}

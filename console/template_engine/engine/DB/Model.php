<?php
namespace Engine\DB;

use Bootstrap\Config;
use PDO;

/**
 * Model class
 *
 * データベース管理用クラス
 */
class Model
{
    /**
     * PDOインスタンス変数
     *
     * @var PDO
     */
    protected static $db;

    /**
     * 操作するテーブル名
     *
     * @var string
     */
    protected static $table;

    /**
     * 設定ファイルをロードし、setInstanceを呼ぶ
     *
     * @param string $table
     */
    public function __construct($table)
    {
        if (!is_null(static::$db)) {
            return;
        }

        Config::load(ROOT . "/.env");
        static::$table = $table;
        $this->setInstance();
    }

    /**
     * 設定データのDB接続情報からPDOでデータベースに接続し、
     * $dbにPDO インスタンスを格納
     *
     * @return void
     */
    protected function setInstance()
    {
        try {
            $options = array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET CHARACTER SET 'utf8'");
            $dsn =  Config::get("DB_DRIVER").':host='.Config::get("DB_HOST").';dbname='.Config::get("DB_NAME");
            static::$db = new PDO($dsn, Config::get("DB_USER"), Config::get("DB_PASS"), $options);
            static::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            echo $e->getMessage();
            exit;
            // throw new \ErrorException($e->getMessage());
        }
    }

    /**
     * $tableの全てのカラムを取得して配列で返す
     *
     * @return array $columns
     */
    public function getTableColumns()
    {
        try {
            $querystring = 'SELECT * FROM '.static::$table.' LIMIT 0;';
    
            $rs = static::$db->query($querystring);
            for ($i = 0; $i < $rs->columnCount(); $i++) {
                $col = $rs->getColumnMeta($i);
                $columns[] ="'". $col['name'] ."'";
            }
            return $columns;
        } catch (\PDOException $e) {
            echo $e->getMessage();
            exit;
            // throw new \ErrorException($e->getMessage());
        }
    }
}

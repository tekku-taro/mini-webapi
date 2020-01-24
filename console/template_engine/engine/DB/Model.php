<?php
namespace Engine\DB;

use Bootstrap\Config;
use PDO;

class Model
{
    protected static $db;
    protected $table;

    public function __construct($table)
    {
        if (!is_null(static::$db)) {
            return;
        }

        Config::load(ROOT . "/.env");
        $this->table = $table;
        $this->setInstance();
    }

    protected function setInstance()
    {
        try {
            $options = array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET CHARACTER SET 'utf8'");
            $dsn =  Config::get("DB_DRIVER").':host='.Config::get("DB_HOST").';dbname='.Config::get("DB_NAME");
            
            static::$db = new PDO($dsn, Config::get("DB_USER"), Config::get("DB_PASS"), $options);
            static::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new \ErrorException($e->getMessage());
        }
    }

    public function getTableColumns()
    {
        $rs = static::$db->query('SELECT * FROM '.$this->table.' LIMIT 0');
        for ($i = 0; $i < $rs->columnCount(); $i++) {
            $col = $rs->getColumnMeta($i);
            $columns[] ="'". $col['name'] ."'";
        }
        return $columns;
    }
}

<?php
namespace Lib\AppCore;

use Illuminate\Database\Eloquent\Model as Eloquent;

class DB extends Eloquent
{
     public static function beginTransaction()
     {
          self::getConnectionResolver()->connection()->beginTransaction();
     }

     public static function commit()
     {
         self::getConnectionResolver()->connection()->commit();
     }

     public static function rollBack()
     {
         self::getConnectionResolver()->connection()->rollBack();
     }    
}
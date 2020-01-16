<?php
use Illuminate\Database\Capsule\Manager as Capsule;
use Bootstrap\Config;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => Config::get('DB_DRIVER'),//mysql
    'host'      => Config::get('DB_HOST'),//'localhost',
    'database'  => Config::get('DB_NAME'),//'database',
    'username'  => Config::get('DB_USER'),//'root',
    'password'  => Config::get('DB_PASS'),//'password',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();

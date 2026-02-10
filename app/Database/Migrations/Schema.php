<?php

namespace Phantom\Database\Migrations;

use Phantom\Core\Container;

class Schema
{
    public static function create($table, \Closure $callback)
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        
        $sql = $blueprint->toSql();
        return Container::getInstance()->make('db')->query($sql);
    }

    public static function dropIfExists($table)
    {
        $sql = "DROP TABLE IF EXISTS `{$table}`";
        return Container::getInstance()->make('db')->query($sql);
    }
}

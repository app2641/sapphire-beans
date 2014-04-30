<?php


namespace Sapphire\Database;

use Sapphire\Database\Database;
use Sapphire\Utility\Registry;

class Helper
{

    /**
     * データベース接続を行う
     *
     * @param  String    database.iniの区画名
     * @return Database
     **/
    public static function connection ($database_block)
    {
        // Registryに登録され、コネクションも同じ場合
        if (Registry::getInstance()->ifKeyExists('db')) {
            $db = Registry::get('db');

        } else {
            $db = new Database($database_block);
            Registry::set('db', $db);
        }

        return $db;
    }
}

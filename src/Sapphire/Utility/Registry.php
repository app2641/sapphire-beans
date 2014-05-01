<?php


namespace Sapphire\Utility;

class Registry extends \ArrayObject
{

    /**
     * @var Registry
     **/
    private static $instance;


    /**
     * Registryインスタンスを返す
     *
     * @return Registry
     **/
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Registry();
        }

        return self::$instance;
    }



    /**
     * 格納物を取得する
     *
     * @param String $index  格納キー
     * @return object
     **/
    public static function get ($index)
    {
        $instance = self::getInstance();

        if (! $instance->ifKeyExists($index)) {
            throw new \Exception($index.' は登録されていません！');
        }

        return $instance->offsetGet($index);
    }



    /**
     * 格納する
     *
     * @param String $index  格納キー
     * @param Object $value  格納物
     * @return void
     **/
    public static function set ($index, $value)
    {
        $instance = self::getInstance();
        $instance->offsetSet($index, $value);
    }



    /**
     * 指定格納キーが既に登録されているか
     *
     * @param String $index  格納キー
     * @return boolean
     **/
    public function ifKeyExists ($index)
    {
        return array_key_exists($index, $this);
    }
}

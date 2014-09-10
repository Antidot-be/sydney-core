<?php

abstract class Sydney_Singleton
{

    private static $instance = array();

    private function __construct()
    {
    }

    public static function getInstance()
    {
        $className = get_called_class();
        if (!isset(self::$instance[$className])) {
            self::$instance[$className] = new $className;
        }

        return self::$instance[$className];
    }

}

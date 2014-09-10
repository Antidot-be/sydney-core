<?php

/**
 * Utilities for directories info and manipulation
 *
 */
class Sydney_Tools_Debug extends Sydney_Log
{

    private static $instance = array();

    public static function getInstance()
    {
        $className = get_called_class();
        if (!isset(self::$instance[$className])) {
            self::$instance[$className] = new $className;
        }

        return self::$instance[$className];
    }

}

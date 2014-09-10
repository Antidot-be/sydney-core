<?php

class Sydney_Search_Files_Result
{

    private static $result = array();

    private static $currentModule = '';
    private static $currentContentType = '';
    private static $currentKey = '';

    public static function init($module, $contentType, $key)
    {
        self::$currentModule = $module;
        self::$currentContentType = $contentType;
        self::$currentKey = $key;
    }

    public static function add($name, $value)
    {
        self::$result[self::$currentModule][self::$currentContentType][self::$currentKey][$name] = $value;
    }

    public static function get()
    {
        return self::$result;
    }

}

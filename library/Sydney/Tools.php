<?php

/**
 * Convenience class giving access to all the static methods from the classes present in ./Tools
 *
 * @package AntidotLibrary
 * @subpackage Tools
 */
class Sydney_Tools
{
    protected static $classes = array();
    protected static $date_locale = 'en_BE';

    /**
     * Overloading this class with all the methods from the classes found in ./Tools
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws Exception
     */
    public static function __callStatic($name, $arguments)
    {
        foreach (self::_getClassList() as $fil) {
            $fidir = __DIR__ . '/Tools/' . $fil . '.php';
            if (file_exists($fidir)) {
                include_once($fidir);
                $class = 'Sydney_Tools_' . $fil;
                if (method_exists($class, $name)) {
                    return call_user_func_array("$class::$name", $arguments);
                    break;
                }
            }
        }
        throw new Exception('Static method could not be found in ./Tools');
    }

    /**
     * Returns the list of available classes in ./Tools
     * @return array
     */
    private static function _getClassList()
    {
        if (count(self::$classes) == 0) {
            include_once __DIR__ . '/Tools/Dir.php';
            foreach (Sydney_Tools_Dir::getDirList(__DIR__ . '/Tools/') as $s) {
                self::$classes[] = preg_replace('/.php$/', '', $s);
            }
        }

        return self::$classes;
    }
}

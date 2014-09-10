<?php

/**
 * Utilities for arrays manipulation
 *
 */
class Sydney_Tools_Arrays extends Sydney_Tools
{

    /**
     *
     * @param array $array
     * @param unknown_type $trim
     * @param unknown_type $firstCall
     */
    public static function flat(array $array, $trim = true, $firstCall = true)
    {
        static $myvalue = array();

        if ($firstCall) {
            $myvalue = array();
        }

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                self::flat($value, true, false);
            } else {
                if (!$trim || ($trim && !empty($value))) {
                    $myvalue[] = $value;
                }
            }
        }

        if ($firstCall) {
            return $myvalue;
        }
    }

    /**
     *
     * @param unknown_type $glue
     * @param unknown_type $array
     * @param unknown_type $firstCall
     * @param unknown_type $onlySpecificKey
     */
    public static function implode($glue, $array, $firstCall = true, $onlySpecificKey = '')
    {
        static $myvalue = array();

        if ($firstCall) {
            $myvalue = array();
        }

        if (!is_array($array)) {
            return false;
        }

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                self::implode($glue, $value, false, $onlySpecificKey);
            } else {
                if (empty($onlySpecificKey)) {
                    $myvalue[] = $value;
                } else {
                    if ($key === $onlySpecificKey) {
                        $myvalue[] = $array[$key];
                    }
                }
            }
        }

        if ($firstCall) {
            return implode($glue, $myvalue);
        }
    }

}

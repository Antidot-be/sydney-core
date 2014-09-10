<?php

/**
 * utilities for global data found in sydney
 */
class Sydney_Tools_Sydneyglobals extends Sydney_Tools
{

    /**
     *
     */
    public static function getSafinstancesId()
    {
        return Zend_Registry::getInstance()->get('config')->db->safinstances_id;
    }

    /**
     *
     * @param unknown_type $key
     */
    public static function getConf($key = '')
    {
        if (empty($key)) {
            return Zend_Registry::get("config");
        } else {
            return Zend_Registry::get("config")->$key;
        }
    }

}

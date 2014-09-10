<?php

/**
 * Utilities for user info
 *
 */
class Sydney_Tools_User extends Sydney_Tools
{
    /**
     *
     */
    public static function who()
    {
        $who = 'guest';
        $udata = new Zend_Session_Namespace('userdata');
        if (is_object($udata)) {
            $who = $udata->user['fname'] . ' ' . $udata->user['lname'];
        }

        return $who;
    }

    /**
     *
     */
    public static function isAtLeastSuperadmin()
    {
        if (in_array(6, Sydney_Tools::getUserdata('member_of_groups'))) {
            return true;
        }

        return false;

    }

    /**
     *
     */
    public static function isAdmin()
    {
        if (in_array(3, Sydney_Tools::getUserdata('member_of_groups'))) {
            return true;
        }

        return false;
    }

    /**
     * Returns users info/data stored in session.
     * format as follow:
     * <code>
     *     [users_id] => 1431
     *     [login] => gilles@antidot.com
     *     [usersgroups_name] => developer
     *     [usersgroups_id] => 7
     *     [fname] => Gilles
     *     [lname] => Demaret
     *     [email] => gdemaret@antidot.com
     *     [id] => 7
     *     [name] => developer
     *     [desc] => Developer with all access
     *     [parent_id] => 6
     *     [member_of_groups] => Array
     *     (
     *     [0] => 7
     *     [1] => 6
     *     [2] => 3
     *     [3] => 2
     *     [4] => 1
     *     [5] => 0
     *     )
     *
     *     [member_of_wrkgroups] => Array
     *     (
     *     [0] => 32
     *     )
     * </code>
     * @param unknown_type $key
     */
    public static function getUserdata($key = '')
    {
        $usersData = array();
        $udata = new Zend_Session_Namespace('userdata');

        if (isset($udata->user)) {
            $usersData = $udata->user;
        } else {
            return false;
        }

        if (!empty($key) && key_exists($key, $usersData)) {
            return $usersData[$key];
        }

        return $usersData;
    }
}

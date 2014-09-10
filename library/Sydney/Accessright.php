<?php

/**
 * Class for checking the access rights
 * vs users and groups
 *
 * @author Arnaud Selvais
 * @since Jan 31 2011
 *
 */
class Sydney_Accessright
{
    static protected $db = null;
    static protected $log = array();
    static protected $safinstancesId = null;
    static protected $usersId = null;
    static public $request = null;
    static public $module = null;
    static public $controller = null;
    static public $action = null;
    static protected $accessModuleInactive = true;

    /**
     * Initialize the required vars.
     * If the vars are not passed as arg, we will use session, registry and DB data.
     *
     * @param int $lusersId
     * @param int $lsafinstancesId
     * @param Zend_Db $ldb
     */
    protected static function init($lusersId = null, $lsafinstancesId = null, $ldb = null)
    {
        $userNamespace = new Zend_Session_Namespace('userdata');
        $config = Zend_Registry::get('config');

        if ($ldb == null) {
            self::$db = Zend_Registry::get('db');
        } else {
            self::$db = $ldb;
        }

        if ($lsafinstancesId != null) {
            self::$safinstancesId = $lsafinstancesId;
        } else {
            self::$safinstancesId = $config->db->safinstances_id;
        }

        if ($lusersId != null) {
            self::$usersId = $lusersId;
        } else {
            self::$usersId = $userNamespace->user['users_id'];
        }

        if ($config->accessrightsenabled == 'yes') {
            if ($r = self::$db->fetchAll("SELECT count(*) AS nbrs FROM safinstances_safmodules WHERE safmodules_id = '71' AND safinstances_id = '" . self::$safinstancesId . "'")) {
                if (is_array($r) && count($r) == 1 && is_array($r[0]) && isset($r[0]['nbrs']) && $r[0]['nbrs'] == 1) {
                    self::$accessModuleInactive = false;
                }
            }
        }
    }

    /**
     *
     * @param unknown_type $module
     * @param unknown_type $controller
     * @param unknown_type $action
     */
    protected static function initRequestData($module = null, $controller = null, $action = null)
    {
        if ($module == null && is_object(self::$request)) {
            self::$module = self::$request->module;
        } else {
            self::$module = $module;
        }
        if ($controller == null && is_object(self::$request)) {
            self::$controller = self::$request->controller;
        } else {
            self::$controller = $controller;
        }
        if ($action == null && is_object(self::$request)) {
            self::$action = self::$request->action;
        } else {
            self::$action = $action;
        }
    }

    /**
     * Returns true if authorization is granted for a certain user, safinstance and module/controller/action
     * if no param is passed we will used the context.
     *
     * @param int $lusersId Users Id
     * @param string $module Module name
     * @param string $controller Controller name
     * @param string $action Action Name
     * @param int $lsafinstancesId Safinstance Id
     * @param Zend_Db $ldb DB object
     */
    public static function isAuthorized($lusersId = null, $module = null, $controller = null, $action = null, $lsafinstancesId = null, $ldb = null)
    {
        self::init($lusersId, $lsafinstancesId, $ldb);
        if (self::$accessModuleInactive) {
            return true;
        }
        self::initRequestData($module, $controller, $action);
        if (self::getAuthRightsForModule() && self::getAuthRightsForController() && self::getAuthRightsForAction()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @param unknown_type $lusersId
     * @param unknown_type $module
     * @param unknown_type $controller
     * @param unknown_type $lsafinstancesId
     * @param unknown_type $ldb
     */
    public static function isAuthorized2controller($lusersId = null, $module = null, $controller = null, $lsafinstancesId = null, $ldb = null)
    {
        self::init($lusersId, $lsafinstancesId, $ldb);
        if (self::$accessModuleInactive) {
            return true;
        }
        self::initRequestData($module, $controller);
        if (self::getAuthRightsForModule() && self::getAuthRightsForController()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @param unknown_type $lusersId
     * @param unknown_type $module
     * @param unknown_type $lsafinstancesId
     * @param unknown_type $ldb
     */
    public static function isAuthorized2module($lusersId = null, $module = null, $lsafinstancesId = null, $ldb = null)
    {
        self::init($lusersId, $lsafinstancesId, $ldb);
        if (self::$accessModuleInactive) {
            return true;
        }
        self::initRequestData($module);

        return self::getAuthRightsForModule();
    }
}

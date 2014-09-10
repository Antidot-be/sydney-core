<?php
include_once('Zend/Db/Table.php');

/**
 * This class extends the Zend_Db_Table and adds interesting methods
 * for data manipulation and the Sydney_Admin_Generator tool.
 * All the objects mapped from the DB should be an extension of this class.
 *
 * @package AntidotLibrary
 * @subpackage Db
 * @version $Id: Trace.php 144 2008-08-06 03:58:19Z arnaud $
 * @author Gilles Demaret <gilles.demaret@antidot.com>
 * @since 23-Aug-10
 *
 */
class Sydney_Db_Trace extends Zend_Db_Table
{
    private static $logger;

    private static function getLogger()
    {
        if (!is_object(self::$logger)) {
            self::$logger = new Sydney_Log();
            self::$logger->addWriter(new Zend_Log_Writer_Db(Zend_Registry::getInstance()->get('db'), 'safactivitylog', array(
                'timestamp'       => 'timestamp',
                'priorityName'    => 'priorityName',
                'priority'        => 'priority',
                'message'         => 'message',
                'users_id'        => 'users_id',
                'login'           => 'login',
                'fname'           => 'fname',
                'lname'           => 'lname',
                'module'          => 'module',
                'action'          => 'action',
                'module_table'    => 'module_table',
                'module_ids'      => 'module_ids',
                'parent_id'       => 'parent_id',
                'safinstances_id' => 'safinstances_id'
            )));
        }

        return self::$logger;
    }

    private function setEventItem($name, $value)
    {
        self::getLogger()->setEventItem($name, $value);
    }

    /**
     *
     * @param String $message
     * @param String $module
     * @param String $moduleTable
     * @param String $action
     * @param Integer $id
     * @param Integer $parentId
     */
    public static function add($message, $module, $moduleTable, $action, $id = 0, $parentId = 0)
    {
        $udata = new Zend_Session_Namespace('userdata');

        if (is_object($udata) && !empty($udata->user['users_id'])) {
            self::getLogger()->setEventItem('users_id', $udata->user['users_id']);
            self::getLogger()->setEventItem('login', $udata->user['login']);
            self::getLogger()->setEventItem('fname', $udata->user['fname']);
            self::getLogger()->setEventItem('lname', $udata->user['lname']);
        } else {
            self::getLogger()->setEventItem('users_id', 0);
            self::getLogger()->setEventItem('login', "guest");
            self::getLogger()->setEventItem('fname', "guest");
            self::getLogger()->setEventItem('lname', "anonymous");
        }

        self::getLogger()->setEventItem('module', $module);
        self::getLogger()->setEventItem('action', $action);
        self::getLogger()->setEventItem('module_table', $moduleTable);
        self::getLogger()->setEventItem('module_ids', $id);
        self::getLogger()->setEventItem('parent_id', $parentId);
        self::getLogger()->setEventItem('safinstances_id', Sydney_Tools::getSafinstancesId());

        self::getLogger()->log($message, Zend_Log::INFO);

    }

}

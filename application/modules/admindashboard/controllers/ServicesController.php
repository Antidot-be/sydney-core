<?php
/**
 * Controller
 */

/**
 * Default controller
 *
 * @package Admindashboard
 * @subpackage Controller
 * @author Arnaud Selvais <arnaud@antidot.com>
 * @since Mar 6, 2009
 * @copyright Antidot Inc. / S.A.
 */
class Admindashboard_ServicesController extends Sydney_Controller_Action
{
    /**
     * Zend_Config object instance
     * @var Zend_Config
     */
    protected $_config;
    /**
     * Defines the views types the actions should bring back
     * @var array
     */
    public $contexts = array(
        'getsomething'      => array('json'),
        'getlistactivities' => array('xml')
    );

    /**
     * Controller initialization
     */
    public function init()
    {
        parent::init();
        $this->getResponse()->setHeader("Cache-Control", "no-cache, must-revalidate");
        $this->_helper->contextSwitch()->initContext();
        $this->_helper->layout->disableLayout();
    }

    public function getlistactivitiesAction()
    {
        // init params
        $userid = $this->getRequest()->user;
        // get actyivity log
        $oActivity = new Safactivitylog;
        $oActivity->setFilterUser($userid);
        $this->view->activities = $oActivity->getLastActivities()->reorderingForDashboard();
    }

}

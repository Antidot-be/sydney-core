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
 * @since Mar 5, 2009
 * @copyright Antidot Inc. / S.A.
 */
class Admindashboard_IndexController extends Sydney_Controller_Action
{
    public function init()
    {
        parent::init();
    }

    /**
     *
     */
    public function indexAction()
    {
        $this->setSubtitle(Sydney_Tools_Localization::_('Recent activity'));
        $this->setSideBar('index', 'dashboard');

        // get actyivity log
        $oActivity = new Safactivitylog;
        $this->view->activities = $oActivity->getLastActivities()->reorderingForDashboard();

        $this->render('index');
        $this->render('listactivities');
    }
}

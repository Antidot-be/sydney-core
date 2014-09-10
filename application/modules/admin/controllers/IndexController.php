<?php
/**
 * Controller
 */

/**
 * Default controller for the admindb containing the menu
 *
 * @package Admin
 * @subpackage Controller
 * @author Arnaud Selvais <arnaud@antidot.com>
 * @since 24-Jun-08
 * @copyright Antidot Inc. / S.A.
 */
class Admin_IndexController extends Sydney_Controller_Action
{
    /**
     * Initialize object, overrides the parent method
     * @return void
     */
    public function init()
    {
        parent::init();
        if (!in_array(7, $this->usersData['member_of_groups'])) {
            $this->redirect('/admindashboard/index/index/');
        }
    }

    /**
     * Welcome page
     */
    public function indexAction()
    {
        $this->redirect('/admindashboard/index/index/');
    }
}

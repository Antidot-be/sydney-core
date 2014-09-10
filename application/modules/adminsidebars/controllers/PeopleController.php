<?php

class Adminsidebars_PeopleController extends Sydney_Controller_Action
{
    public function init()
    {
        //parent::init();
        $usersData = Sydney_Tools::getUserdata();
        $this->view->cdn = Sydney_Tools::getRootUrlCdn();
        $this->view->allowPermission = in_array(7, $usersData['member_of_groups']);
    }

    /**
     *
     * @return void
     */
    public function indexAction()
    {
    }

    /**
     *
     * @return void
     */
    public function profileAction()
    {
        $this->render('edit');
    }

    /**
     *
     * @return void
     */
    public function editAction()
    {
    }

    /**
     *
     * @return void
     */
    public function permissionsAction()
    {
        //$this->render('edit');
    }
}

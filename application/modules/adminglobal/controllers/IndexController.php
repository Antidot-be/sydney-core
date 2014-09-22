<?php

/**
 * Management of all the webinstances
 */
class Adminglobal_IndexController extends Sydney_Controller_Action
{
    /**
     * Initialization of the basics
     */
    public function init()
    {
        parent::init();
        $this->setSubtitle('Global Admin');
        $this->setSideBar('index', 'adminglobal');
        $this->layout->langswitch = false;
        $this->layout->search = false;
    }

    /**
     *
     */
    public function indexAction()
    {
        $this->setSubtitle2('Webinstances');
        $sDB = new Safinstances();
        $this->view->webinstances = $sDB->fetchAll();
    }

    /**
     *
     */
    public function editsafinstancesAction()
    {
        $r = $this->getRequest();
        $safinstance = false;
        if (isset($r->id) && preg_match('/^[0-9]{1,50}$/', $r->id)) {
            $sDB = new Safinstances();
            $s = $sDB->find($r->id);
            if (count($s) == 1) {
                $safinstance = $s[0];
            }
        }
        $this->view->safinstanceid = $safinstance->id;
        $this->setSubtitle2('Webinstances : Edit');
        // Main safinstance info form
        $form = new SafinstancesFormOp();
        $safform = $form->getMainform();


        if($r->isPost()){
            $p = $r->getPost();
            if($safform->isValid($p)) {
                foreach ($p as $k => $v) {
                    if (isset($safinstance->$k) && $k != 'id') {
                        $safinstance->$k = $v;
                    }
                }
                $safinstance->save();
            }
        } else {
            if ($safinstance) {
                $safform->populate($safinstance->toArray());
            }
        }
        $this->view->safinstancesForm = $safform;
    }

    /**
     *
     */
    public function usersgroupsAction()
    {
        $this->setSubtitle2('Groups');
        $userGroups = new Usersgroups();
        $this->view->userGroups = $userGroups->fetchAll();
    }
}

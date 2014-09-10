<?php

class Adminfolders_IndexController extends Sydney_Controller_Action
{
    /**
     * (non-PHPdoc)
     * @see Sydney_Controller_Action::init()
     */
    public function init()
    {
        parent::init();
        $this->setSubtitle('Adminfolders');
        $this->setSideBar('index', 'adminfolders');
        $this->layout->langswitch = false;
        $this->layout->search = false;
        $this->view->headLink()->appendStylesheet($this->view->cdnurl . '/sydneyassets/styles/zendform.css');
        $this->view->headLink()->appendStylesheet($this->view->cdnurl . '/sydneyassets/styles/multilinktable.css');
    }

    /**
     *
     */
    public function indexAction()
    {
        $this->redirect('/adminfolders/index/filfolders');
    }

    /**
     *
     */
    public function filfoldersAction()
    {
        $this->setSubtitle2('Filfolders');
    }

    /**
     *
     */
    public function editfilfoldersAction()
    {
        $this->_createformAction('Filfolders', 'FilfoldersFormOp', 'Edit / Add Filfolders');
    }

}

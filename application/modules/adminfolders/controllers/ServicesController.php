<?php

class Adminfolders_ServicesController extends Sydney_Controller_Action
{
    /**
     * Defines the views types the actions should bring back
     * @var array
     */
    public $contexts = array(
        'index'            => array('json'),
        'getfilfolders'    => array('json'),
        'editfilfolders'   => array('json'),
        'deletefilfolders' => array('json'),
        'updatefilfolders' => array('json'),
        'getnwsfolders'    => array('json'),
        'editnwsfolders'   => array('json'),
        'deletenwsfolders' => array('json'),
        'updatenwsfolders' => array('json'),

    );

    /**
     * Controller initialization
     */
    public function init()
    {
        $this->_isService = true;
        parent::init();
        $this->getResponse()->setHeader("Cache-Control", "no-cache, must-revalidate");
        $this->_helper->contextSwitch()->initContext();
        $this->_helper->layout->disableLayout();
        $this->_initDataTableRequest();
    }

    /**
     *
     */
    public function indexAction()
    {

    }

    /**
     *
     */
    public function getfilfoldersAction()
    {
        $where = 'safinstances_id = ' . Sydney_Tools::getSafinstancesId();
        $sortBy = 'pagorder';

        $sDB = new Filfolders();
        $this->view->ResultSet = $sDB->fetchdatatoYUI($where, $sortBy, $this->results, $this->startIndex, $this->hidefields);
    }

    /**
     *
     */
    public function editfilfoldersAction()
    {
        $rowId = $this->_editfieldAction('Filfolders', 'FilfoldersForm', array('safinstances_id' => Sydney_Tools::getSafinstancesId()));

    }

    /**
     *
     */
    public function deletefilfoldersAction()
    {
        $r = $this->getRequest();
        $sDB = new Filfolders();
        foreach ($sDB->deleteRowForGrid($r->id, $this->safinstancesId) as $k => $v) {
            $this->view->$k = $v;
        }
    }

    /**
     *
     */
    public function updatefilfoldersAction()
    {
        $sDB = new Filfolders();
        $this->view->result = $sDB->updateOneField($this->getRequest()->getPost());
    }

    /**
     *
     */
    public function getnwsfoldersAction()
    {
        $where = 'safinstances_id = ' . Sydney_Tools::getSafinstancesId();
        $sortBy = 'pagorder';

        $sDB = new Nwsfolders();
        $this->view->ResultSet = $sDB->fetchdatatoYUI($where, $sortBy, $this->results, $this->startIndex, $this->hidefields);
    }

    /**
     *
     */
    public function editnwsfoldersAction()
    {
        $rowId = $this->_editfieldAction('Nwsfolders', 'NwsfoldersForm', array('safinstances_id' => Sydney_Tools::getSafinstancesId()));
    }

    /**
     *
     */
    public function deletenwsfoldersAction()
    {
        $r = $this->getRequest();
        $sDB = new Nwsfolders();
        foreach ($sDB->deleteRowForGrid($r->id, $this->safinstancesId) as $k => $v) {
            $this->view->$k = $v;
        }
    }

    /**
     *
     */
    public function updatenwsfoldersAction()
    {
        $sDB = new Nwsfolders();
        $this->view->result = $sDB->updateOneField($this->getRequest()->getPost());
    }

}

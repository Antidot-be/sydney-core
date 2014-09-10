<?php

/**
 * Controllers for structure edition
 *
 * @package Adminpages
 * @subpackage Controller
 * @author Arnaud Selvais <arnaud@antidot.com>
 * @since Mar 5, 2009
 * @copyright Antidot Inc. / S.A.
 * @todo make the structure editor multilangual ready (editing structures for several languages)
 */
class Adminpages_RecyclebinController extends Sydney_Controller_Action
{

    /**
     * Defines the views types the actions should bring back
     * @var array
     */
    public $contexts = array(
        'restore'    => array('json'),
        'deletenode' => array('json')
    );

    /**
     * Controller initialization
     */
    public function init()
    {
        parent::init();
        //$this->view->addHelperPath(Zend_Registry::get("config")->general->rootPath.'/core/application/modules/adminpages/views/helpers', 'Adminpages_Views_Helpers');

        $this->getResponse()->setHeader("Cache-Control", "no-cache, must-revalidate");
        $this->_helper->contextSwitch()->initContext();
    }

    /**
     * Shows the structure editor
     */
    public function indexAction()
    {
        $this->setSubtitle('Recycle Bin');
        $this->setSideBar('index', 'Recyclebin');

        $this->layout->langswitch = true;
        $this->layout->search = true;

        $pgs = new Pagstructure();
        $pgs->setFilter('isDeleted', 1);
        $this->view->structureArray = $pgs->getRecyclebin();

    }

    /**
     *
     */
    public function deletenodeAction()
    {
        $msg = 'error! nodes not deleted!';
        $status = 0;
        $data = Zend_Json::decode($this->getRequest()->jsondata);
        try {
            $pgsd = new Pagstructure();
            $pgsd->emptyRecyclebin();
            $msg = 'Nodes deleted';
            $status = 1;

            /*
             * GDE : 08/10/2010
             * Add trace of current action
             */
            Sydney_Db_Trace::add('trace.event.empty_recyclebin', // message
                'adminpages', // module
                Sydney_Tools::getTableName($pgsd), // module table name
                'deletepage', // action
                0 // id
            );
            // */

        } catch (Exception $e) {
            $msg = 'error! ' . $e->getMessage();
        }
        $this->view->ResultSet = array(
            'message' => $msg,
            'status'  => $status,
            'modal'   => false
        );
    }

    /*
     *
     */
    public function restoreAction()
    {

        $this->view->ResultSet = array(
            'message' => 'Node not restored !',
            'status'  => 0,
            'modal'   => true
        );
        $data = Zend_Json::decode($this->getRequest()->jsondata);
        try {
            if ($data['dbId'] > 0) {

                // restore node
                $pgsd = new Pagstructure();
                $node = $pgsd->get($data['dbId']);
                $pgsd->restoreNode($data['dbId']);

                /*
                 * GDE : 27/08/2010
                 * Add trace of current action
                 */
                Sydney_Db_Trace::add('trace.event.restore_page'
                    . ' [' . $node->label . ']', // message
                    'adminpages', // module
                    Sydney_Tools::getTableName($pgsd), // module table name
                    'restorepage', // action
                    $data['dbId'] // id
                );
                // */

                $this->view->ResultSet = array(
                    'message' => 'Node restored !',
                    'status'  => 1,
                    'modal'   => false
                );
            } else {
                $this->view->ResultSet = array(
                    'message' => 'Node not found !',
                    'status'  => 0,
                    'modal'   => true
                );
            }
        } catch (Exception $e) {
            $this->view->ResultSet = array(
                'message' => 'error! ' . $e->getMessage(),
                'status'  => 0,
                'modal'   => true
            );
        }

    }

    public function sidebarAction()
    {
        $this->_helper->layout->disableLayout();
        $pgs = new Pagstructure();
        $this->view->countDeletedNodes = $pgs->countDeletedNodes();
    }

}

<?php
/**
 * Controller Adminglobal Services
 */

/**
 *
 * @package Adminglobal
 * @subpackage Controller
 * @author Arnaud Selvais
 * @since 11/10/10
 * @copyright Antidot Inc. / S.A.
 */
class Adminglobal_ServicesController extends Sydney_Controller_Action
{
    /**
     * Defines the views types the actions should bring back
     * @var array
     */
    public $contexts = array(
        'getsafinstances'           => array('json'),
        'getusersgroups'            => array('json'),
        'getusers'                  => array('json'),
        'updatesafinstances'        => array('json'),
        'updatecompanies'           => array('json'),
        'updateusers'               => array('json'),
        'updateusersgroups'         => array('json'),
        'getsafmodules'             => array('json'),
        'updatesafmodules'          => array('json'),
        'editpagmenussafinstances'  => array('json'),
        'editsafinstancescompanies' => array('json'),
        'editsafinstancesmodules'   => array('json'),
        'editsafinstances'          => array('json'),
        'editsafmodules'            => array('json'),
        'deletesafinstances'        => array('json'),
        'deletesafmodules'          => array('json'),
        'getsafinstancestype'       => array('json'),
        'editsafinstancestype'      => array('json'),
        'deletesafinstancestype'    => array('json'),
        'updatesafinstancestype'    => array('json'),
        'getinstancesize'           => array('json'),

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
    public function getsafinstancesAction()
    {
        $sDB = new Safinstances();
        $this->view->ResultSet = $sDB->fetchdatatoYUI(null, $this->sort . ' ' . $this->dir, $this->results, $this->startIndex, $this->hidefields);
    }

    /**
     * Updates one field of the table
     */
    public function updatesafinstancesAction()
    {
        $sDB = new Safinstances();
        $this->view->result = $sDB->updateOneField($this->getRequest()->getPost());
    }

    /**
     * Add or edit webinstances
     */
    public function editsafinstancesAction()
    {
        $p = $this->getRequest()->getPost();
        $resp = array('Error...', 0);
        $msgs = array();
        $odata = array();
        $row = false;
        $isNewEntry = false;
        $form = new SafinstancesFormOp();
        $safform = $form->getMainform();

        if (!$safform->isValid($p)) {
            $msgs = $form->getMessages();
            $odata = $p;
            $resp = array('Error in the form', 0);
        } else {
            $sDB = new Safinstances();
            if (isset($p['id']) && preg_match('/^[0-9]{1,50}$/', $p['id'])) {
                $rows = $sDB->find($p['id']);
                if (count($rows) == 1) {
                    $row = $rows[0];
                } else {
                    $resp = array('Error data not found', 0);
                }
            } else {
                $row = $sDB->createRow();
                $isNewEntry = true;
            }
            if ($row) {
                foreach ($p as $k => $v) {
                    if (isset($row->$k) && $k != 'id') {
                        $row->$k = $v;
                    }
                }
                $rowId = $row->save();
                if ($isNewEntry && $rowId) {
                    $mng = new Sydney_Admin_Global_Manager();
                    $mng->setRow($row);
                    // create the webinstance
                    if ($mng->createWebInstanceStructure()) { // create the cronjob file for the sitemap
                        $mng->createSitemapCronjobFile();
                        // create the apache config file
                        $mng->createApacheConfigFile();
                    }
                    // create the home page with welcome content
                    $this->_createWelcomePage($row);
                    // create a user to manage the site (or link my user to the instance)
                    $this->_linkUserToInstance($row);

                    $resp = array($mng->getFlatLog(), 1);
                } else {
                    $resp = array('OK', 1);
                }
                $odata = $row->toArray();
            }
        }
        $this->view->timeout = 4;
        $this->view->modal = false;
        $this->view->ResultSet = array('errors' => $msgs, 'entry' => $odata);
        $this->view->message = $resp[0];
        $this->view->status = $resp[1];
    }

    /**
     *
     */
    public function editsafmodulesAction()
    {
        $p = $this->getRequest()->getPost();
        $resp = array('Error...', 0);
        $msgs = array();
        $odata = array();
        $form = new SafmodulesForm();

        if (!$form->isValid($p)) {
            $msgs = $form->getMessages();
            $odata = $p;
            $resp = array('Error in the form', 0);
        } else {
            $sDB = new Safmodules();
            if (isset($p['id']) && preg_match('/^[0-9]{1,50}$/', $p['id'])) {
                $rows = $sDB->find($p['id']);
                if (count($rows) == 1) {
                    $row = $rows[0];
                } else {
                    $resp = array('Error data not found', 0);
                }
            } else {
                $row = $sDB->createRow();
                $isNewEntry = true;
            }
            if ($row) {
                foreach ($p as $k => $v) {
                    if (isset($row->$k) && $k != 'id') {
                        $row->$k = $v;
                    }
                }
                $rowId = $row->save();
                $resp = array('OK', 1);
                $odata = $row->toArray();
            }
        }

        $this->view->timeout = 4;
        $this->view->modal = false;
        $this->view->ResultSet = array('errors' => $msgs, 'entry' => $odata);
        $this->view->message = $resp[0];
        $this->view->status = $resp[1];
    }

    /**
     *
     */
    public function editsafinstancescompaniesAction()
    {
        $p = $this->getRequest()->getPost();
        $resp = array('Error...', 0);
        $msgs = array();
        $odata = array();
        if (isset($p['SafinstancesCompanies']) && is_array($p['SafinstancesCompanies'])) {
            $sDB = new SafinstancesCompanies();
            $sDB->delete("safinstances_id = " . $this->_db->quote($p['id']) . " ");
            foreach ($p['SafinstancesCompanies'] as $e) {
                $data = array(
                    'safinstances_id' => $p['id'],
                    'companies_id'    => $e
                );
                $sDB->insert($data);
            }
        }
        $this->view->timeout = 4;
        $this->view->modal = false;
        $this->view->ResultSet = array('errors' => $msgs, 'entry' => $odata);
        $this->view->message = $resp[0];
        $this->view->status = $resp[1];
    }

    /**
     *
     */
    public function editsafinstancesmodulesAction()
    {
        $p = $this->getRequest()->getPost();
        $resp = array('Error...', 0);
        $msgs = array();
        $odata = array();

        if (isset($p['SafinstancesSafmodules']) && is_array($p['SafinstancesSafmodules'])) {
            $sDB = new SafinstancesSafmodules();
            $sDB->delete("safinstances_id = " . $this->_db->quote($p['id']) . " ");
            foreach ($p['SafinstancesSafmodules'] as $e) {
                $data = array(
                    'safinstances_id' => $p['id'],
                    'safmodules_id'   => $e
                );
                $sDB->insert($data);
            }
        }

        $this->view->timeout = 4;
        $this->view->modal = false;
        $this->view->ResultSet = array('errors' => $msgs, 'entry' => $odata);
        $this->view->message = $resp[0];
        $this->view->status = $resp[1];
    }

    /**
     * Edit links to menus
     */
    public function editpagmenussafinstancesAction()
    {
        $p = $this->getRequest()->getPost();
        $resp = array('Error...', 0);
        $msgs = array();
        $odata = array();

        if (isset($p['PagmenusSafinstances']) && is_array($p['PagmenusSafinstances'])) {
            $sDB = new PagmenusSafinstances();
            $sDB->delete("safinstances_id = " . $this->_db->quote($p['id']) . " ");
            foreach ($p['PagmenusSafinstances'] as $e) {
                $data = array(
                    'safinstances_id' => $p['id'],
                    'pagmenus_id'     => $e
                );
                $sDB->insert($data);
            }
        }

        $this->view->timeout = 4;
        $this->view->modal = false;
        $this->view->ResultSet = array('errors' => $msgs, 'entry' => $odata);
        $this->view->message = $resp[0];
        $this->view->status = $resp[1];
    }

    /**
     * Delete a row or multiple rows
     * @todo TODO improve security, this is not sql injection safe so do not use it as a base for something else
     */
    public function deletesafinstancesAction()
    {
        $this->_deleteRow('Safinstances');
    }

    /**
     *
     */
    public function deletesafmodulesAction()
    {
        $this->_deleteRow('Safmodules');
    }

    /**
     *
     */
    public function getusersgroupsAction()
    {
        $sDB = new Usersgroups();
        $this->view->ResultSet = $sDB->fetchdatatoYUI(null, $this->sort . ' ' . $this->dir, $this->results, $this->startIndex, $this->hidefields);
    }

    /**
     * Updates one field of the table
     */
    public function updateusersgroupsAction()
    {
        $sDB = new Usersgroups();
        $this->view->result = $sDB->updateOneField($this->getRequest()->getPost());
    }

    /**
     *
     */
    public function getusersAction()
    {
        $sDB = new Users();
        $this->view->ResultSet = $sDB->fetchdatatoYUI(null, $this->sort . ' ' . $this->dir, $this->results, $this->startIndex, $this->hidefields);
    }

    /**
     * Updates one field of the table
     */
    public function updateusersAction()
    {
        $sDB = new Users();
        $this->view->result = $sDB->updateOneField($this->getRequest()->getPost());
    }

    /**-------------------------------------------------------------------**/

    /**
     *
     */
    public function getsafmodulesAction()
    {
        $sDB = new Safmodules();
        $this->view->ResultSet = $sDB->fetchdatatoYUI(null, $this->sort . ' ' . $this->dir, $this->results, $this->startIndex, $this->hidefields);
    }

    /**
     * Updates one field of the table
     */
    public function updatesafmodulesAction()
    {
        $sDB = new Safmodules();
        $this->view->result = $sDB->updateOneField($this->getRequest()->getPost());
    }

    /**
     *
     * create the home page with welcome content
     * @param Zend_Db_Table_Row $row
     */
    protected function _createWelcomePage($safrow)
    {
        // create structure element
        $sDB = new Pagstructure();
        $strRows = $sDB->find('3991');
        if (count($strRows) == 1) {
            $strRowA = $strRows[0]->toArray();
        } else {
            return false;
        }
        unset($strRowA['id']);
        $strRowA['parent_id'] = 0;
        $strRowA['safinstances_id'] = $safrow->id;
        $strRowA['ishome'] = 1;
        $strRowA['hits'] = 0;
        $strRown = $sDB->createRow($strRowA);
        $pagStructureId = $strRown->save();
        if ($pagStructureId) {
            // create divs
            $sDB2 = new PagstructurePagdivs();
            $sDB3 = new Pagdivs();
            $divsIds = array();
            foreach ($sDB2->fetchAll("pagstructure_id = '3991' ") as $el) {
                $divsIds[] = $el->pagdivs_id;
            }
            foreach ($sDB3->fetchAll("id IN (" . implode(',', $divsIds) . ") ") as $div) {
                $diva = $div->toArray();
                unset($diva['id']);
                $divaRow = $sDB3->createRow($diva);
                $pagdivsId = $divaRow->save();

                $rowLink = $sDB2->createRow(
                    array(
                        'pagstructure_id' => $pagStructureId,
                        'pagdivs_id'      => $pagdivsId
                    )
                );
                $rowLink->save();
            }
        }
    }

    /**
     * Link the current logged user to the instance
     * @param Zend_Db_Table_Row $row
     */
    protected function _linkUserToInstance($safrow)
    {
        $sDB = new SafinstancesUsers();
        $row = $sDB->createRow();
        $row->safinstances_id = $safrow->id;
        $row->users_id = $this->usersId;
        $row->save();
    }

    /**
     *
     * @param String $model
     */
    protected function _deleteRow($model)
    {
        if (in_array($model, array('Safinstances', 'Safmodules'))) {
            $r = $this->getRequest();
            $nbrRowsDeleted = 0;
            $rowsid = 0;
            if (isset($r->id) && preg_match('/^[0-9,]{1,50}$/', $r->id)) {
                $sDB = new $model;
                $nbrRowsDeleted = $sDB->delete("id IN (" . $r->id . ") ");
                $rowsid = $r->id;
            }
            $this->view->rowsDeleted = $rowsid;
            $this->view->timeout = 2;
            $this->view->modal = false;
            $this->view->message = 'Rows deleted: ' . $nbrRowsDeleted;
            if ($nbrRowsDeleted == 0) {
                $this->view->status = 0;
            } else {
                $this->view->status = 1;
            }
        }
    }

    /**
     *
     */
    public function getsafinstancestypeAction()
    {
        $sDB = new Safinstancestype();
        $this->view->ResultSet = $sDB->fetchdatatoYUI(null, $this->sort . ' ' . $this->dir, $this->results, $this->startIndex, $this->hidefields);
    }

    /**
     *
     */
    public function editsafinstancestypeAction()
    {
        $rowId = $this->_editfieldAction('Safinstancestype', 'SafinstancestypeForm');
    }

    /**
     *
     */
    public function deletesafinstancestypeAction()
    {
        $r = $this->getRequest();
        $sDB = new Safinstancestype();
        foreach ($sDB->deleteRowForGrid($r->id, $this->safinstancesId) as $k => $v) {
            $this->view->$k = $v;
        }
    }

    /**
     *
     */
    public function updatesafinstancestypeAction()
    {
        $sDB = new Safinstancestype();
        $this->view->result = $sDB->updateOneField($this->getRequest()->getPost());
    }

    public function getinstancesizeAction()
    {
        $this->view->ResultSet = Sydney_Server_Tools::getWebinstancesDiskUsed();
    }

}

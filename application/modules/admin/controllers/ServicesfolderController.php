<?php

/**
 * Generic controller for getting and setting data
 * to a m2m table and organize the linked labels.
 *
 * It can be used for folders linked to files or other table having the same kind of architecture
 *
 * @author Arnaud Selvais
 * @since 16/04/10
 */
class Admin_ServicesfolderController extends Sydney_Controller_Action
{
    /**
     * Defines the views types the actions should bring back
     * @var array
     */
    public $contexts = array(
        'getdata'                 => array('json'),
        'addelement'              => array('json'),
        'delelement'              => array('json'),
        'link-filfiles-to-folder' => array('json'),
        'link-nwsnews-to-folder'  => array('json'),
        'reorderfolder'           => array('json'),
        'editlabel'               => array('json'),
        'link-tasks-to-folder'    => array('json'),
        'link-users-to-folder'    => array('json'),
        'link-pagdivs-to-folder'  => array('json'),

    );

    /**
     * Controller initialization
     */
    public function init()
    {
        parent::init();
        $this->getResponse()->setHeader("Cache-Control", "no-cache, must-revalidate");
        $this->_helper->contextSwitch()->initContext();

        // get the request data
        $r = $this->getRequest();
        $this->jsonstr = false;
        $this->fileid = false;
        $this->newsid = false;
        if (isset($r->jsonstr)) {
            $this->jsonstr = Zend_Json::decode($r->jsonstr);
        }
        if (isset($r->fileid) && $r->fileid != 'undefined') {
            $this->fileid = Zend_Json::decode($r->fileid);
        }
        if (isset($r->datatable)) {
            $this->datatable = $r->datatable;
        }
    }

    /**
     *
     * @return void
     */
    public function indexAction()
    {

    }

    public function editlabelAction()
    {
        $request = $this->getRequest();
        if (!empty($request->id) && !empty($request->table)) {
            $id = str_replace('structure_', '', $request->id);
            $className = ucfirst($request->table);
            $myobject = new $className();

            if (@method_exists($myobject, "serviceFolderEditlabel")) {
                $myobject->serviceFolderEditlabel($id, $request->label, $this->getRequest()->getParams());
                $this->_setDataMsg('OK!', 1);
            } else {
                $mylabel = $myobject->find($id)->current();
                $mylabel->label = $request->label;
                $mylabel->save();
            }
        }
    }

    /**
     *
     * url: /admin/servicesfolder/link-filfiles-to-folder/format/json/
     * @return void
     */
    public function linkFilfilesToFolderAction()
    {
        /*
         * $this->fileid: 6406
         * $this->jsonstr:(
         * 				[0] => structure_871
         * 			    [1] => structure_3026
         * 			)
         **/
        array_walk($this->jsonstr, create_function('&$v,$k', '$v = str_replace("structure_","",$v);'));
        $oLinkFolder = new FilfoldersFilfiles();
        $oLinkFolder->setFilfilesLinkedTo($this->fileid, $this->jsonstr);
        $this->_setDataMsg('OK, link recorded', 1);
    }

    /**
     *
     */
    public function linkPagdivsToFolderAction()
    {
        array_walk($this->jsonstr, create_function('&$v,$k', '$v = str_replace("structure_","",$v);'));
        $oLinkFolder = new PagstructurePagdivs();
        $oLinkFolder->setPagdivsLinkedToSafe($this->fileid, $this->jsonstr);
        $this->_setDataMsg('OK, link recorded', 1);
    }

    /**
     *
     * url: /admin/servicesfolder/link-users-to-folder/format/json/
     * @return void
     */
    public function linkUsersToFolderAction()
    {
        /*
         * $this->fileid: 6406
         * $this->jsonstr:(
         * 				[0] => structure_871
         * 			    [1] => structure_3026
         * 			)
         **/
        array_walk($this->jsonstr, create_function('&$v,$k', '$v = str_replace("structure_","",$v);'));
        $oLinkFolder = new FilfoldersUsers();
        $oLinkFolder->setUsersLinkedTo($this->fileid, $this->jsonstr);
        $this->_setDataMsg('OK, link recorded', 1);
    }

    /**
     *
     * url: /admin/servicesfolder/link-file-to-folder/format/json/
     * @return void
     */
    public function reorderfolderAction()
    {
        /*
         * $this->datatable
         * $this->fileid: 6406
         * $this->jsonstr:Array
            (
                [0] => Array
                    (
                        [key] => structure_871
                        [position] => 1
                        [parent] => _1
                    )

                [1] => Array
                    (
                        [key] => structure_811
                        [position] => 2
                        [parent] => _1
                    )

                [2] => Array
                    (
                        [key] => structure_801
                        [position] => 3
                        [parent] => _1
                    )
                )

         **/
        $oFolder = new Filfolders();

        foreach ($this->jsonstr as $node) {
            $parent = (($node['parent'] == '_1') ? 0 : str_replace('structure_', '', $node['parent']));
            $oFolder->setPosition($node['position'], str_replace('structure_', '', $node['key']), $parent);
        }
        $this->_setDataMsg('OK, order changed', 1);
    }

    /**
     *
     * url: /admin/servicesfolder/index/format/json/
     * @return void
     */
    public function getdataAction()
    {
        $cortable = false;
        $labeltable = false;
        $datatable = false;
        $labelfield = 1;
        $parentrelation = true;
        $sorttable = 'label';

        if (isset($this->jsonstr['parentrelation'])) {
            $parentrelation = $this->jsonstr['parentrelation'];
        }
        if (isset($this->jsonstr['cortable'])) {
            $cortable = $this->jsonstr['cortable'];
        }
        if (isset($this->jsonstr['labeltable'])) {
            $labeltable = $this->jsonstr['labeltable'];
        }
        if (isset($this->jsonstr['datatable'])) {
            $datatable = $this->jsonstr['datatable'];
        }
        if (isset($this->jsonstr['sorttable'])) {
            $sorttable = $this->jsonstr['sorttable'];
        }
        if (isset($this->jsonstr['selectedid'])) {
            $selectedid = $this->jsonstr['selectedid'];
        }
        if (isset($this->jsonstr['labelfield'])) {
            switch ($this->jsonstr['labelfield']) {
                case '2':
                    $labelfield = " CONCAT(" . $labeltable . ".fname, ' ', " . $labeltable . ".lname) as label";
                    break;
                default:
                    $labelfield = $labeltable . '.label  as label ';
                    break;
            }
        }

        if ($cortable && $labeltable && $datatable && ($cortable == ($labeltable . '_' . $datatable) || $cortable == ($datatable . '_' . $labeltable))) {
            $sql = "SELECT
					" . $labelfield . ", ";
            if ($parentrelation) {
                $sql .= $labeltable . ".parent_id, ";
            } else {
                $sql .= "0 as parent_id, ";
            }
            $sql .= $labeltable . ".id as labeltable_id ";
            if ($labeltable == 'filfolders') {
                $sql .= ", isSystemFolder ";
            }
            $sql .= "FROM
					 $labeltable
				WHERE
					" . $labeltable . ".safinstances_id = '" . $this->safinstancesId . "' ";
            if ($labeltable == 'pagstructure') {
                $sql .= " AND isDeleted	= 0 ";
            }

            $sql .= "
				ORDER BY
					$sorttable
			";

            $this->_setDataMsg('OK!', 1);
            $this->view->ResultSet = $this->_db->fetchAll($sql);
        } else {
            // Call model getdata if exist
            $objectName = ucfirst($labeltable);
            $model = new $objectName;
            if (@method_exists($model, "serviceFolderGetdata")) {
                $this->_setDataMsg('OK!', 1);
                $this->view->ResultSet = $model->serviceFolderGetdata($this->getRequest()->getParams())->toArray();
            } else {
                $this->_setDataMsg('Error!', 0);
            }
        }
    }

    /**
     *
     * @return void
     */
    public function addelementAction()
    {
        $lbladd = '';
        $cortable = false;
        $labeltable = false;
        $datatable = false;
        if (isset($this->jsonstr['lbladd'])) {
            $lbladd = $this->jsonstr['lbladd'];
        }
        if (isset($this->jsonstr['cortable'])) {
            $cortable = $this->jsonstr['cortable'];
        }
        if (isset($this->jsonstr['labeltable'])) {
            $labeltable = $this->jsonstr['labeltable'];
        }
        if (isset($this->jsonstr['datatable'])) {
            $datatable = $this->jsonstr['datatable'];
        }

        $tbldname = ucfirst($labeltable);
        $tdb = new $tbldname;

        if ($cortable && $labeltable && $datatable && ($cortable == ($labeltable . '_' . $datatable) || $cortable == ($datatable . '_' . $labeltable))) {
            $row = $tdb->createRow();
            $row->label = $lbladd;
            $row->safinstances_id = $this->safinstancesId;
            $id = $row->save();

            $this->_setDataMsg('OK!', 1);
        } else {
            if (@method_exists($tdb, "serviceFolderAddelement")) {
                $tdb->serviceFolderAddelement($lbladd, $this->getRequest()->getParams());
                $this->_setDataMsg('OK!', 1);
            } else {
                $this->_setDataMsg('Error!', 0);
            }
        }
    }

    /**
     * Deletes a label element
     * @return void
     */
    public function delelementAction()
    {
        $labeltable = false;
        $idtodel = false;
        if (isset($this->jsonstr['labeltable'])) {
            $labeltable = $this->jsonstr['labeltable'];
        }
        if (isset($this->jsonstr['idtodel'])) {
            $idtodel = $this->jsonstr['idtodel'];
        }
        if ($labeltable && $idtodel && preg_match("/^[0-9]{1,100}$/", $idtodel)) {
            $tbldname = ucfirst($labeltable);
            $tdb = new $tbldname;
            if (@method_exists($tdb, "serviceFolderDelelement")) {
                $tdb->serviceFolderDelelement($idtodel, $this->getRequest()->getParams());
                $this->_setDataMsg('OK!', 1);
            } else {
                $sql = "DELETE FROM " . addslashes($labeltable) . " WHERE safinstances_id = '" . $this->safinstancesId . "' AND id = '" . $idtodel . "' ";
                if ($this->_db->query($sql)) {
                    $this->_setDataMsg('OK, Item deleted', 1);
                } else {
                    $this->_setDataMsg('Error!', 0);
                }
            }
        }
    }

    /**
     * Sets data in the view for the message box
     *
     * @param String $msg Message to show in the message box
     * @param int $status The error status. 0 will show an error box (color red)
     * @return void
     */
    private function _setDataMsg($msg = '', $status = 1)
    {
        $this->view->status = $status;
        $this->view->message = $msg;
        $this->view->showtime = 1;
        $this->view->modal = false;
    }
}

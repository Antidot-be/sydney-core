<?php

/**
 * Class containing method to get page contend (the divs we can find in a page).
 *
 * @package Admindb
 * @subpackage Model
 */
abstract class PagdivsOp extends Sydney_Db_Table
{
    protected $row = null; // row object
    protected $rowParent = null; // row object

    private $logicalDelete = true;

    public function __construct($id = 0)
    {
        parent::__construct();
        if (is_numeric($id) && $id > 0) {
            $rowset = $this->find($id);
            $this->row = $rowset[0];
            if (method_exists($this, 'loadParent')) {
                $this->loadParent();
            }
        }
    }

    public function getParent()
    {
        return $this->rowParent;
    }

    protected function setParent($value)
    {
        $this->rowParent = $value;
    }

    public function get($id = 0, $forceReload = false)
    {
        return $this->row;
    }

    public function getDiv($divId)
    {
        $sql = "SELECT * FROM pagdivtypes, pagdivs
	    WHERE
	    pagdivtypes.id = pagdivs.pagdivtypes_id AND
	    pagdivs.id = " . $divId;

        $data = $this->_db->fetchAll($sql);

        return $data[0];
    }

    /**
     *
     * @param Integer $dbId the div id you want to put offline/online
     * @return Mixed On success a string with the new status (offline/online). false on error.
     */
    public static function toggleonline($dbId)
    {
        $row = new Pagdivs($dbId);
        $online = $row->get()->online == '0' ? '1' : '0';
        $row->update(array('online' => $online), 'id = ' . $dbId);

        return $online == '0' ? 'offline' : 'online';

    }

    /**
     *
     * @param Integer $dbid the div id you want to put offline/online
     * @return Mixed On success a string with the new status (offline/online). false on error.
     */
    public static function duplicate($dbid = 0, $nodeId = 0)
    {
        $row = new Pagdivs($dbid);
        $datasSet = $row->get();
        $datas = $datasSet->toArray();
        $datas['online'] = 0;
        unset($datas['id']);

        $insertRow = new Pagdivs();

        return $insertRow->insert($datas);
    }

    /**
     * Method for checking the users can access the current div.
     * We check against the instance ID and the structure to see if this div
     * is part of the structure.
     *
     * @param $pagdiv_id Int ID of a div
     * @param $safinstancesId
     * @return boolean return true if we are authorized
     */
    public function checkRightFromId($pageDivsId = 0, $safinstancesId = 0, $eModule = 'pages')
    {
        if ($eModule == 'pages') {
            $sql = 'SELECT count(pagdivs.id) AS result FROM pagdivs, pagstructure_pagdivs, pagstructure
					WHERE pagdivs.id = ' . $pageDivsId . '
					AND pagstructure_pagdivs.pagdivs_id = pagdivs.id
					AND pagstructure_pagdivs.pagstructure_id = pagstructure.id
					AND pagstructure.safinstances_id = ' . $safinstancesId;
        } elseif ($eModule == 'news') {
            $sql = 'SELECT count(pagdivs.id) AS result FROM pagdivs, nwsnews_pagdivs, nwsnews
					WHERE pagdivs.id = ' . $pageDivsId . '
					AND nwsnews_pagdivs.pagdivs_id = pagdivs.id
					AND nwsnews_pagdivs.nwsnews_id = nwsnews.id
					AND nwsnews.safinstances_id = ' . $safinstancesId;
        }

        $r = $this->_db->fetchAll($sql);
        if ($r[0]['result'] >= 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * delete all contents of page $pageid
     * @param $pageId        id of the pagstructure
     * @param $rollback        determine if the content are deleted or restored
     */
    public function deleteContentFromPage($pageId, $rollback = false)
    {
        // search all content from page
        $PageDiv = new PagstructurePagdivs();
        $selector = $PageDiv->select()->from(Sydney_Tools::getTableName($PageDiv), 'pagdivs_id')->where('pagstructure_id = ' . $pageId);
        $rowset = $PageDiv->fetchAll($selector)->toArray();
        $listContentId = Sydney_Tools::implode(',', $rowset);

        // delete content
        if (!empty($listContentId)) {
            if ($this->isLogicalDelete()) {
                return $this->update(array('isDeleted' => !$rollback), 'id IN (' . $listContentId . ')');
            } else {
                return $this->delete('id IN (' . $listContentId . ')');
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isLogicalDelete()
    {
        return $this->logicalDelete;
    }

    /**
     * logical delete, the flag isDeleted will be set to 1
     */
    public function setLogicalDelete()
    {
        $this->logicalDelete = true;
    }

    /**
     * physical delete, the row will be deleted from the database
     */
    public function setPhysicalDelete()
    {
        $this->logicalDelete = false;
    }

    /**
     * Checks if the nodes are editable according to the workflow rules
     * @param array $nodes
     */
    protected function checkIsEditable($nodes)
    {
        for ($i = 0; $i < count($nodes); $i++) {
            $isEditable = true;

            $msgNotEditable = '';

            $nodes[$i]['isEditable'] = $isEditable;
            $nodes[$i]['msgNotEditable'] = $msgNotEditable;
            $nodes[$i]['workflowEnabled'] = false;
            $nodes[$i]['accessRightsEnabled'] = false;
            $nodes[$i]['accessRightsFiltered'] = false;
        }

        return $nodes;
    }

    /**
     *
     */
    public function getDBobj()
    {
        return $this->_db;
    }

    /**
     * Update a pagdiv order (in both places)
     *
     * @param int $order
     * @param int $id
     * @return int Number of rows updated
     */
    public function updateOrder($order = 0, $id = 0, $pagstructureId = 0)
    {
        if ($order > 0 && $id > 0 && $pagstructureId > 0) {
            $sql = " UPDATE pagstructure_pagdivs SET `order` = '" . $order . "' WHERE pagstructure_id	= '" . $pagstructureId . "' AND pagdivs_id	= '" . $id . "'; ";
            $sql .= " UPDATE pagdivs SET `order` = '" . $order . "' WHERE id = '" . $id . "'; ";
            try {
                $this->_db->query($sql);

                return 1;
            } catch (Exception $e) {
                print_r($e->getMessage() . ' ' . $sql);

                return 0;
            }
        }

        return 0;
    }

    /**
     * @since 13/02/2014
     * @param $id
     * @param $pageStructureId
     * @param $zone
     * @return int
     */
    public function updateZone($id, $pageStructureId, $zone)
    {
        $sql = "
			UPDATE pagstructure_pagdivs
			SET zone = '" . $zone . "'
			WHERE pagstructure_id = '" . $pageStructureId . "' AND pagdivs_id= '" . $id . "'";
        try {
            $this->_db->query($sql);

            return 1;
        } catch (Exception $e) {
            print_r($e->getMessage() . ' ' . $sql);

            return 0;
        }
    }
}

<?php

require_once('Pagstructure.php');

/**
 * Structure of the page editor object gathering usefull operation
 * we could have to do on it.
 *
 * @package Admindb
 * @subpackage Model
 * @author Arnaud Selvais
 * @since 31/05/09
 */
class PagstructureOp extends Sydney_Db_Table
{

    /**
     *
     * Enter description here ...
     * @var unknown_type
     */
    public $stringNodes = array();

    /**
     *
     * Enter description here ...
     * @var unknown_type
     */
    private $filterStatus = array();

    /**
     *
     * Enter description here ...
     * @var unknown_type
     */
    private $filter = array();

    /**
     *
     * Enter description here ...
     * @var unknown_type
     */
    private $nodesLoaded = array();

    /**
     *
     * Enter description here ...
     * @var unknown_type
     */
    protected $row = null;
    private $rowStats = null;

    /**
     *
     * Enter description here ...
     * @var unknown_type
     */
    private $logicalDelete = true;
    private $numberNodesDeleted = 0;

    /**
     *
     * @param unknown_type $safinstancesId
     * @param unknown_type $parentId
     * @return Array
     */
    public static function getCacheNames($safinstancesId = 0, $parentId = 0)
    {
        $cacheName = 'PagstructureOp_toArray_' . $safinstancesId . '_' . $parentId;
        $cacheName2 = 'PagstructureOpStrnodes_toArray_' . $safinstancesId . '_' . $parentId;

        return array($cacheName, $cacheName2);
    }

    /**
     *
     * @param unknown_type $safinstancesId
     * @param unknown_type $parentId
     * @return unknown_type
     */
    public static function cleanCache($safinstancesId = 0, $parentId = 0)
    {
        $cache = Zend_Registry::get('cache');
        $cacheName = self::getCacheNames($safinstancesId, $parentId);
        foreach ($cacheName as $cName) {
            $cache->remove($cName);
        }
    }

    /**
     *
     * Clean cache of page $pagstructure_id and all pages redirected to this page
     * @param $pageStructureId
     */
    public static function cleanPageCache($pageStructureId)
    {
        $cache = Zend_Registry::get('cache');
        $cache->remove('publicms_' . md5('/publicms/index/view/page/' . $pageStructureId));

        // search page redirected
        $page = new Pagstructure();
        $pageRowset = $page->fetchAll($page->select()->where('redirecttoid = ' . $pageStructureId));
        foreach ($pageRowset as $row) {
            $cache->remove('publicms_' . md5('/publicms/index/view/page/' . $row->id));
        }
    }

    /**
     *
     * Enter description here ...
     */
    public function __construct($id = 0)
    {
        parent::__construct();
        if (is_numeric($id) && $id > 0) {
            $this->load($id);
        }
    }

    /**
     *
     * Enter description here ...
     * @param $nodeId
     */
    public function get($nodeId = 0, $forceReload = false)
    {
        if ($nodeId != 0 && ($this->row === null || $this->row->id != $nodeId)) {
            $this->load($nodeId);
        }

        return $this->row;
    }

    /**
     *
     * Enter description here ...
     */
    public function getIds()
    {
        $sql = "SELECT
				  id
				FROM
				  pagstructure
				WHERE
				 status = 'published'
				 AND isDeleted = 0
				 AND safinstances_id = " . Sydney_Tools::getSafinstancesId();

        $resultRequest = $this->_db->fetchAll($sql);
        $toReturn = array();
        foreach ($resultRequest as $el) {
            $toReturn[] = $el['id'];
        }

        return $toReturn;
    }

    public function getStats($nodeId = 0)
    {
        if ($nodeId != 0 && ($this->row === null || $this->row->id != $nodeId)) {
            $this->load($nodeId);
        }

        return $this->rowStats;
    }

    public function getStringNodes()
    {
        return $this->stringNodes;
    }

    public function getLatestPoitionInNode($nodeId)
    {
        $tempObject = new Pagstructure();
        $selector = $tempObject->select(true)
            ->where('parent_id = ?', $nodeId)
            ->where('safinstances_id = ?', Sydney_Tools::getSafinstancesId())
            ->order('pagorder DESC');
        $rowObject = $tempObject->fetchRow($selector);

        return ++$rowObject->pagorder;
    }

    /**
     *
     * Enter description here ...
     */
    public function getModule()
    {
        return 'adminpages';
    }

    /**
     * Returns the home page ID for this webinstance
     * @return Int
     * @param object $safinstancesId [optional]
     */
    public function getHomeId($safinstancesId = 0)
    {
        $where = 'safinstances_id = ' . $safinstancesId . ' AND ishome = 1 ';
        $result = $this->fetchAll($where);
        if (count($result) > 0) {
            return $result[0]->id;
        } else {
            return 0;
        }
    }

    public function getIdBySlug($slug, $safinstanceId)
    {
        $where = 'safinstances_id = ' . $safinstanceId . ' AND url = "'.$slug.'" ';
        $result = $this->fetchAll($where);
        if (count($result) > 0) {
            return $result[0]->id;
        } else {
            return 0;
        }
    }

    /**
     * Returns an array with data of all the parent of the element
     * with the id passed as argument.
     * @param int $safinstancesId
     * @return array
     */
    public function getBreadCrumData($safinstancesId = 0, $elementId)
    {
        $toReturn = array();
        while ($elementId > 0) {
            $rows = $this->fetchAll('safinstances_id = ' . $safinstancesId . ' AND id = ' . $elementId, 'pagorder');
            if (count($rows) == 1) {
                $row = $rows[0];
                $toReturn[$row->id] = array(
                    'id'          => $row->id,
                    'label'       => $row->label,
                    'isCollapsed' => false,
                    'status'      => $row->status,
                    'ishome'      => $row->ishome
                );
                $elementId = $row->parent_id;
            } else {
                $elementId = 0;
            }
        }

        return array_reverse($toReturn);
    }

    /**
     *
     * Enter description here ...
     */
    public function getRecyclebin()
    {
        $this->cleanNodeLoaded();
        $toReturn = array();
        $selector = $this->select()
            ->where('safinstances_id = ?', Sydney_Tools::getSafinstancesId())
            ->where('isDeleted = ?', 1)
            ->order('pagorder ASC')->order('datemodified DESC');

        foreach ($this->fetchAll($selector) as $row) {
            if (!$this->isNodeLoaded($row->id)) {
                $isCollapsed = false;
                $toReturn[$row->id] = array(
                    'id'             => $row->id,
                    'label'          => $row->label,
                    'isCollapsed'    => $isCollapsed,
                    'status'         => $row->status,
                    'ishome'         => $row->ishome,
                    'iscachable'     => $row->iscachable,
                    'cachetime'      => $row->cachetime,
                    'menusid'        => null,
                    'redirecttoid'   => $row->redirecttoid,
                    'usersgroups_id' => $row->usersgroups_id,
                    'shortdesc'      => $row->shortdesc,
                    'colorcode'      => $row->colorcode,
                    'layout'         => $row->layout,
                    'kids'           => array()
                );
                // store nodes loaded
                $this->setNode($row->id);

                // get the kids now if any
                $kids = $this->toArrayLoop(Sydney_Tools::getSafinstancesId(), $row->id);
                if (count($kids) > 0) {
                    $toReturn[$row->id]['kids'] = $kids;
                }
            }
        }

        return $toReturn;
    }

    /**
     *
     * @param $id
     * @return array
     */
    public function getKidsIds($id)
    {
        $nodeIds = array($id);
        foreach ($this->getKids($id) as $node) {
            if (isset($node->id) && $node->id != null) {
                $nodeIds[] = $node->id;
                $kids = $this->getKidsIds($node->id);
                if (count($kids) > 0) {
                    $nodeIds = array_merge($nodeIds, $kids);
                }
            }
        }

        return $nodeIds;
    }

    /**
     * Returns an array of childrend nodes of the on with the ID passed as param
     *
     * @param $id Parent node
     * @return array
     */
    public function getKids($id)
    {
        return $this->fetchAll("parent_id = " . $id);
    }

    /**
     *
     * @return unknown_type
     */
    protected function getFilterStatus()
    {
        $statusFilter = '';
        if (count($this->filterStatus) > 0) {
            $statusFilter = 'AND status IN ("' . implode('","', $this->filterStatus) . '")';
        }

        return $statusFilter;
    }

    /**
     *
     * Enter description here ...
     */
    protected function getFilter()
    {
        return $this->filter;
    }

    /**
     *
     * Enter description here ...
     */
    public function getContentHtmlToText($controller = null)
    {
        if ($this->row === null) {
            return '';
        } else {
            if ($controller) {
                $view = $controller->view;
            }

            set_time_limit(120);
            if (!$controller) {
                $html = file_get_contents(Sydney_Tools::getRootUrl() . '/publicms/index/view/page/' . $this->get()->id . '/layout/no');
                if (Sydney_Search_Indexation_Pages_Build::$debug) {
                    echo chr(10), 'Indexed: ', '/publicms/index/view/page/' . $this->get()->id . '/layout/no';
                }
            } else {
                $html = $view->action('view', 'index', 'publicms', array(
                    'format' => 'xml',
                    'page'   => $this->get()->id,
                    'layout' => 'no'
                ));
            }

            // JTO - 06/09/2013 - on enlève les scripts JS et leur contenu de la recherche
            $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
            $text = strip_tags($html, '<br><p>');

            // Re-init controller principal
            if ($controller) {
                $controller->init();
            }

            return $text;
        }
    }

    /**
     *
     * Enter description here ...
     */
    public function __toString()
    {
        return $this->get()->label;
    }

    /**
     * Returns the structure in an array form which could be used
     * to display the HTML in the page module.
     *
     * @return Array
     * @param int $safinstancesId [optional]
     * @param int $parentId [optional]
     */
    public function toArray($safinstancesId = 0, $parentId = null)
    {
        return $this->toArrayLoop($safinstancesId, $parentId);
    }

    /**
     * Returns the structure in an array form which could be used
     * to display the HTML in the page module.
     *
     * @return Array
     * @param int $safinstancesId [optional]
     * @param int $parentId [optional]
     */
    public function toArrayLoop($safinstancesId = 0, $parentId = null)
    {
        $toReturn = array();
        $selector = $this->select()
            ->where('safinstances_id = ' . $safinstancesId)
            ->order('pagorder');
        if ($parentId === null) {
            $selector->where('parent_id IS NULL');
        } else {
            $selector->where('parent_id = ' . $parentId);
        }
        $selector = $this->applyFilter($selector);

        // Load translation
        $translate = new Translate_Content_Node();

        foreach ($this->fetchAll($selector) as $row) {
            if (!$this->isNodeLoaded($row->id)) {
                $menusId = array();
                $menuIdsDb = new PagstructurePagmenus();
                foreach ($menuIdsDb->fetchAll('pagstructure_id = ' . $row->id) as $menuIdValue) {
                    $menusId[] = $menuIdValue->pagmenus_id;
                }

                $pageStats = new Pagstats();
                $stats = $pageStats->loadStats($row->id);

                $isCollapsed = false;
                $toReturn[$row->id] = array(
                    'id'                      => $row->id,
                    'label'                   => $translate->_($row->id, $row->label),
                    'htmltitle'               => $row->htmltitle,
                    'url'                     => $row->url,
                    'isCollapsed'             => $isCollapsed,
                    'status'                  => $row->status,
                    'metadesc'                => $row->metadesc,
                    'metakeywords'            => $row->metakeywords,
                    'datemodified'            => $row->datemodified,
                    'date_lastupdate_content' => $row->date_lastupdate_content,
                    'who_modified'            => $row->who_modified,
                    'who_lastupdate_content'  => $row->who_lastupdate_content,
                    'ishome'                  => $row->ishome,
                    'iscachable'              => $row->iscachable,
                    'cachetime'               => $row->cachetime,
                    'menusid'                 => $menusId,
                    'redirecttoid'            => $row->redirecttoid,
                    'usersgroups_id'          => $row->usersgroups_id,
                    'pagorder'                => $row->pagorder,
                    'shortdesc'               => $row->shortdesc,
                    'colorcode'               => $row->colorcode,
                    'layout'                  => $row->layout,
                    'kids'                    => array(),
                    'stats'                   => $stats
                );

                // store node loaded
                $this->setNode($row->id);

                // get the kids now if any
                $kids = $this->toArrayLoop($safinstancesId, $row->id);
                if (count($kids) > 0) {
                    $toReturn[$row->id]['kids'] = $kids;
                }
                $this->stringNodes[$row->id] = $toReturn[$row->id];
            }
        }

        return $toReturn;
    }

    /**
     * Returns a JSON String representing the structure
     *
     * @return String JSON string
     * @param int $safinstancesId [optional]
     * @param int $parentId [optional]
     */
    public function toJSON($safinstancesId = 0, $parentId = 0)
    {
        include_once('Zend/Json.php');

        return $json = Zend_Json::encode($this->toArray4JSON($safinstancesId, $parentId));
    }

    /**
     * Returns an object representation of the structure.
     * This can be used to be converted as a JSON string and used by
     * a Javascript GUI.
     *
     * @return object representation of the structure in a PHP object
     * @param int $safinstancesId [optional]
     * @param int $parentId [optional]
     */
    public function toArray4JSON($safinstancesId = 0, $parentId = 0)
    {
        $toReturn = array();
        $i = 0;
        foreach ($this->fetchAll('safinstances_id = ' . $safinstancesId . ' AND parent_id = ' . $parentId . ' AND id != 0 ' . $this->getFilterStatus(), 'pagorder') as $row) {
            $toReturn[$i] = new stdClass();
            $toReturn[$i]->id = $row->id;
            $toReturn[$i]->label = $row->label;
            $toReturn[$i]->status = $row->status;
            $toReturn[$i]->isHome = $row->ishome;
            $toReturn[$i]->kids = $this->toArray4JSON($safinstancesId, $row->id);
            $i++;
        }

        return $toReturn;
    }

    /**
     *
     */
    public function set($nodeId)
    {
        if (is_object($nodeId) && get_class($nodeId) == 'Zend_Db_Table_Row') {
            $this->row = $nodeId;
        } else {
            $this->load($nodeId);
        }

        return $this;
    }

    /**
     *
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setFilter($name, $value)
    {
        $this->filter[$name] = $value;

        return $this;
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $nodeId
     */
    protected function setNode($nodeId)
    {
        $this->nodesLoaded[] = $nodeId;

        return $this;
    }

    /**
     *
     * Enter description here ...
     */
    public function setLogicalDelete()
    {
        $this->logicalDelete = true;

        return $this;
    }

    /**
     *
     * Enter description here ...
     */
    public function setPhysicalDelete()
    {
        $this->logicalDelete = false;

        return $this;
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $nodeId
     */
    public function isRootNode($nodeId)
    {
        $row = $this->get($nodeId);
        if ($row !== null) {
            if ($row->parent_id > 0) {
                return false;
            }
        }

        return true;
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $nodeId
     */
    protected function isNodeLoaded($nodeId)
    {
        return in_array($nodeId, $this->nodesLoaded);
    }

    /**
     *
     * Enter description here ...
     */
    public function isLogicalDelete()
    {
        return $this->logicalDelete;
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $nodeId
     */
    public function hasParent($nodeId)
    {
        $row = $this->get($nodeId);
        if ($row !== null) {
            $selector = $this->select()
                ->where('id = ?', $row->parent_id)
                ->where('isDeleted = ?', 0);
            $row = $this->fetchRow($selector);
            if ($row !== null) {
                return $row;
            }
        }

        return false;
    }

    /**
     *
     * @return
     * @param object $id [optional]
     * @param object $safinstancesId [optional]
     */
    public function deleteNode($id = 0, $safinstancesId = 0, $rollback = false, $firstCall = true)
    {
        // init
        $pageContent = new Pagdivspage();
        $newStatus = $rollback ? 'restored' : 'deleted';

        if ($firstCall) {
            $this->numberNodesDeleted = 0;
        }

        // delete node
        $where = 'id = ' . $id . ' AND safinstances_id = ' . $safinstancesId . ' AND id != 0';
        if ($this->isLogicalDelete()) {
            $data = array(
                "isDeleted"    => !$rollback,
                "status"       => $newStatus,
                "datemodified" => new Zend_Db_Expr('now()')
            );
            $this->numberNodesDeleted += $this->update($data, $where);

        } else {
            $pageContent->setPhysicalDelete();
            $this->numberNodesDeleted += $this->delete($where);
        }

        // delete content of the node
        $pageContent->deleteContentFromPage($id, $rollback);

        // get the kids now if any
        $this->setFilter('isDeleted', $rollback);
        $kids = $this->toArrayLoop(Sydney_Tools::getSafinstancesId(), $id);
        if (count($kids) > 0) {
            foreach ($kids as $kid) {
                $this->deleteNode($kid['id'], Sydney_Tools::getSafinstancesId(), $rollback, false);
            }
        }

        if ($firstCall) {
            return $this->numberNodesDeleted;
        }
    }

    /**
     *
     * Enter description here ...
     */
    public function emptyRecyclebin()
    {
        $this->setPhysicalDelete();
        $nodes = $this->getRecyclebin();
        foreach ($nodes as $node) {
            $this->deleteNode($node['id'], Sydney_Tools::getSafinstancesId());
        }
        $this->setLogicalDelete();
    }

    /**
     *
     * Enter description here ...
     * @param $nodeId
     */
    private function load($nodeId)
    {
        $this->row = $this->find($nodeId)->current();

        $pageStats = new Pagstats();
        $this->rowStats = $pageStats->getStatsOfPage($nodeId);

        return $this;
    }

    /**
     *
     * @return
     * @param object $id [optional]
     * @param object $safinstances_id [optional]
     */
    public function restoreNode($nodeId = 0)
    {
        // search if parent exist on structure
        // if is not the case then update his parent to 0
        if (!$this->isRootNode($nodeId) && !$this->hasParent($nodeId)) {
            if (!$this->hasParent($nodeId)) {
                $this->update(array('parent_id' => 0), 'id = ' . $nodeId);
            }
        }

        // restore the node
        $this->deleteNode($nodeId, Sydney_Tools::getSafinstancesId(), true);
    }

    /**
     *
     * Enter description here ...
     */
    public function countDeletedNodes()
    {
        $selector = $this->select()
            ->where('isDeleted = 1')
            ->where('safinstances_id = ' . Sydney_Tools::getSafinstancesId());

        return $this->fetchAll($selector)->count();
    }

    /**
     * Updates the groups which has access to a node and its children.
     *
     * @param $id ID of the parent node
     * @param $usersGroupsId ID of the group
     * @param $cascade If true we update all the kids too
     * @return void
     */
    public function updateAccessRights($id, $usersGroupsId = 1, $cascade = true)
    {
        $nodeIds = array($id);
        if ($cascade) {
            $nodeIds = $this->getKidsIds($id);
        }
        if (!empty($usersGroupsId)) {
            $sql = "UPDATE pagstructure SET usersgroups_id = " . $usersGroupsId . " WHERE id IN (" . implode(',', $nodeIds) . ") AND usersgroups_id < " . $usersGroupsId;
            $this->_db->query($sql);
        }
    }

    /**
     *
     * @param $id
     * @return unknown_type
     */
    public function publish($id)
    {
        $safeInstanceId = Zend_Registry::getInstance()->get('config')->get('db')->safinstances_id;
        $sql = "UPDATE pagstructure SET status = 'published',datemodified=NOW(),who_modified = '" . Sydney_Tools::who() . "' WHERE id = " . $id;
        $this->_db->query($sql);

        // clean cache of the structure
        PagstructureOp::cleanCache($safeInstanceId);
        // clean cache of pages
        $cache = new Sydney_Cache_Manager;
        $cache->clearPageCache($safeInstanceId);
    }

    /**
     *
     * @param $id
     * @return unknown_type
     */
    public function unpublish($id)
    {
        $safeInstanceId = Zend_Registry::getInstance()->get('config')->get('db')->safinstances_id;
        $sql = "UPDATE pagstructure SET status = 'draft',datemodified=NOW(),who_modified = '" . Sydney_Tools::who() . "' WHERE id = " . $id;
        $this->_db->query($sql);

        // clean cache of the structure
        PagstructureOp::cleanCache($safeInstanceId);
        // clean cache of pages
        $cache = new Sydney_Cache_Manager;
        $cache->clearPageCache($safeInstanceId);
    }

    public function duplicatenode($id)
    {
        $this->_duplicateNode($id);
        $msg = 'Node duplicated';

        return array(
            'msg'    => $msg,
            'status' => 1,
        );
    }

    private function _duplicateNode($id, $newParentId = null)
    {
        // Load the node data
        $node = new Pagstructure($id);
        // Duplicate node
        $duplicateNode = $node->createRow($node->__toArray());
        $duplicateNode->id = 0;
        $duplicateNode->ishome = 0; // avoid two homepage
        $duplicateNode->status = 'draft'; // unpublish the copy
        if ($newParentId !== null) {
            $duplicateNode->parent_id = $newParentId;
        }
        $duplicateNode->datecreated = new Zend_Db_Expr("NOW()");
        $duplicateNode->datemodified = new Zend_Db_Expr("NOW()");
        $duplicateNode->who_modified = Sydney_Tools::who();
        $duplicateNode->save();

        // Duplicate node content
        $page = new Pagdivspage();
        $pageDivs = $page->getDivs($id, false);
        PagdivspageOp::resetFictivePagDivsOrder();
        foreach ($pageDivs as $pageDiv) {
            Pagdivspage::duplicate($pageDiv['pagdivs_id'], $duplicateNode->id, $pageDiv['order_pagstructure_pagdiv']);
        }

        // Access right to duplicate?
        // Menu presence to duplicate?

        // Duplicate child, if any
        $kids = $node->getKids($id);
        if (count($kids) > 0) {
            foreach ($kids as $kid) {
                $this->_duplicateNode($kid->id, $duplicateNode->id);
            }
        }

        return $duplicateNode->id;
    }

    /**
     *
     * @return unknown_type
     */
    protected function applyFilter(Zend_Db_Select $selector)
    {
        $addFilterNoDeletedNode = true;
        if (is_array($this->filter)) {
            foreach ($this->filter as $name => $value) {
                if ($name == 'isDeleted') {
                    $addFilterNoDeletedNode = false;
                }
                $selector = $selector->where($name . ' = ?', $value);
            }
        }

        if ($addFilterNoDeletedNode) {
            $selector = $selector->where('isDeleted = 0');
        }

        return $selector;
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $nodeId
     * @param unknown_type $published
     */
    public function saveLastupdateContent($nodeId, $published = true)
    {
        $who = Sydney_Tools::who();
        $row = $this->get($nodeId);
        if ($published) {
            $row->datemodified = new Zend_Db_Expr("NOW()");
            $row->who_modified = $who;
        }
        $row->date_lastupdate_content = new Zend_Db_Expr("NOW()");
        $row->who_lastupdate_content = $who;

        $row->save();
    }

    /**
     *
     * Enter description here ...
     */
    protected function cleanNodeLoaded()
    {
        $this->nodesLoaded = array();
    }

}

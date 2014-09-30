<?php
/**
 * Controller
 */

/**
 * Default controller
 *
 * @package Adminpages
 * @subpackage Controller
 * @author Arnaud Selvais <arnaud@antidot.com>
 * @since Mar 6, 2009
 * @copyright Antidot Inc. / S.A.
 */
class Adminpages_ServicesController extends Sydney_Controller_Action
{
    /**
     * Defines the views types the actions should bring back
     * @var array
     */
    public $contexts = array(
        'getstructure'        => array('json'),
        'savediv'             => array('json'),
        'deletediv'           => array('json'),
        'rollbackdiv'         => array('json'),
        'updatestrorder'      => array('json'),
        'updatepagerorder'    => array('json'),
        'updatezoneforpagdiv' => array('json'),
        'deletenode'          => array('json'),
        'emptycache'          => array('json'),
        'setcacheall'         => array('json'),
        'getaction'           => array('json'),
        'publishdiv'          => array('json'),
        'unpublishdiv'        => array('json'),
        'duplicatenode'       => array('json'),
        'setwrkstatuses'      => array('json'),
        'getlistpagdivs'      => array('json'),
        'toggleonline'        => array('json'),
        'updatelabel'         => array('json'),
        'duplicate'           => array('json'),
        'setaccesswstatuses'  => array('json'),
        'getcleanlabelpage'   => array('json'),
        'getcleanurlpage'     => array('json'),
    );
    /**
     * Defines the module used for linking the divs (ex to a page structure or to a news item, ...)
     * @var string
     */
    protected $eModule = 'pages';

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

        $r = $this->getRequest();
        if (isset($r->module)) {
            $this->eModule = $r->emodule;
        } else {
            $this->eModule = 'pages';
        }
    }

    /**
     * Returns the structure for the current safinstance.
     * URL example : http://sydney.localhost.prv/adminpages/services/getstructure/format/json
     *
     * @return void
     */
    public function getstructureAction()
    {
        include_once('PagstructureOp.php');
        $safinstancesId = $this->_config->db->safinstances_id;
        $parentId = 0;
        $pags = new Pagstructure();
        $this->view->ResultSet = $pags->toArray4JSON($safinstancesId, $parentId);
    }

    /**
     * Returns the structure for the current safinstance.
     * URL example : http://sydney.localhost.prv/adminpages/services/getstructure/format/json
     *
     * @return void
     */
    public function getactionAction()
    {
        $request = $this->getRequest();
        $this->view->dbid = $request->dbid;
        $this->view->page = new Pagstructure($this->view->dbid);
    }

    /**
     *
     */
    public function getdivwitheditorAction()
    {
        $divId = $this->_getParam('dbid', false);
        $this->view->forcedStatus = $this->_getParam('status', '');

        if ($divId == false) {
            $this->view->div = "not loaded!";
        } else {
            $registry = new Zend_Registry();
            $this->view->customHelpers = $registry->get('customhelpers');
            $div = new Pagdivspage();
            $data = $div->getDiv($divId);
            $this->view->div = $data;
        }
    }

    /**
     * Creates or updates a pagdiv element in the DB.
     * This is used in the page edition when editing an element inline.
     * URL : /adminpages/services/savediv/format/json
     * @return void
     */
    public function savedivAction()
    {
        $divs = new Pagdivspage();
        $request = $this->getRequest();

        /*
         * @change GDE - 05/2014 - Content Translation
         * Load translation
         */
        $this->contentTranslate = new Translate_Content_Content();

        $status = 0;
        if ($divs->checkRightFromId($request->id, $this->_config->db->safinstances_id, $this->eModule) || $request->id == 0) {
            try {
                $isNewRow = false;
                if ($request->id > 0) {
                    $div = $divs->fetchRow('id=' . $request->id);
                    $div->datemodified = new Zend_Db_Expr("NOW()");
                } else {
                    $isNewRow = true;
                    $div = $divs->createRow();
                    $div->datecreated = new Zend_Db_Expr("NOW()");
                    $div->content_type_label = $request->content_type_label;
                    $div->save(); // Create record on database
                }
                if (isset($request->status)) {
                    $div->status = $request->status;
                }
                if (isset($request->order)) {
                    $div->order = $request->order;
                }
                if ($div->status == 'draft') {
                    if (isset($request->content)) {
                        /*
                         * @change GDE - 05/2014 - Content Translation
                         * Save translation of content (on native table for default language and on translation table for others)
                         */
                        $div->content_draft = $this->contentTranslate->translate($div->content_draft, $request->content, $div->id, 'draft');
                    }
                    if (isset($request->params)) {
                        $div->params_draft = $request->params;
                    }
                } else {
                    /*
                     * @change GDE - 05/2014 - Content Translation
                     * Save translation of content (on native table for default language and on translation table for others)
                     */
                    $div->content_draft = $this->contentTranslate->translate($div->content_draft, '', $div->id, 'draft');
                    if (isset($request->content)) {
                        /*
                         * @change GDE - 05/2014 - Content Translation
                         * Save translation of content (on native table for default language and on translation table for others)
                         */
                        $div->content = $this->contentTranslate->translate($div->content, $request->content, $div->id);
                    }
                    if (isset($request->params)) {
                        $div->params = $request->params;
                    }
                }

                $div->save();

                if ($isNewRow) {
                    if ($this->eModule == 'pages') {
                        $cors = new PagstructurePagdivs();
                    }
                    $cor = $cors->createRow();
                    if ($this->eModule == 'pages') {
                        $cor->pagstructure_id = $request->pagstructureid;
                    }
                    $cor->pagdivs_id = $div->id;
                    $cor->save();
                }
                /*
                 * GDE : 27/08/2010
                 * Add trace of current action
                 */
                if ($this->eModule == 'pages') {
                    $orderPage = new Pagstructure;
                    $orderPage->saveLastupdateContent($request->pagstructureid, ($div->status == 'draft' ? false : true));
                    $rowSetPage = $orderPage->find($request->pagstructureid);
                    $rowPage = $rowSetPage->current();

                    // suffix label for tracing
                    $suffixMessage = ' [' . $rowPage->label . ']';

                    // clean cache for parent page
                    PagstructureOp::cleanPageCache($request->pagstructureid);

                    // re-index the page
                    if ($div->status == 'published') {
                        //Sydney_Search_Indexation_Pages_Update::execute($po->pagstructureid,$this);
                    }

                    $module = 'adminpages';
                    $traceMessageUpdate = 'trace.event.update_content_page';
                    $traceMessageCreate = 'trace.event.create_content_page';
                }

                $action = 'updatecontent';
                $traceMessage = $traceMessageUpdate;
                if ($isNewRow) {
                    $action = 'createcontent';
                    $traceMessage = $traceMessageCreate;
                }
                Sydney_Db_Trace::add($traceMessage . $suffixMessage, // message
                    $module, // module
                    Sydney_Tools::getTableName($div), // module table name
                    $action, // action
                    $div->id, // id
                    $request->pagstructureid // parent id
                );

                $msg = 'Element saved.'; //.'ID of the element '.$div->id;
                $status = 1;
                $dbId = $div->id;
            } catch (Exception $e) {
                $msg = 'Error while saving the element in the database!' . $e->getMessage();
                $status = 0;
            }
        } else {
            $msg = 'Access rights error';
            $status = 0;
            $dbId = 0;
        }
        $this->view->ResultSet = array(
            'message' => $msg,
            'status'  => $status,
            'dbid'    => $dbId,
            'modal'   => false
        );
    }

    /**
     * Deletes a DIV from the DB
     *
     * @return void
     */
    public function deletedivAction()
    {
        $divs = new Pagdivspage();
        $request = $this->getRequest();
        $status = 0;
        if (!$request->id) {
            $msg = 'Ok';
            $status = 1;
        } else {
            /*
             * Get detail for Add trace of current action
             */
            if ($this->eModule == 'pages') {
                $orderPageDiv = new Pagdivspage($request->id);
                $rowPage = $orderPageDiv->getParent();
                $parentId = $rowPage->get()->id;

                $orderPage = new Pagstructure;
                $orderPage->saveLastupdateContent($parentId);

                $module = 'adminpages';
                $traceMessage = Sydney_Tools::_('trace.event.delete_content_page') . ' [' . $rowPage->get()->label . ']';
            }

            if ($request->id <= 0) {
                $msg = 'Technical error. The entry ID has an inconcistent value!';
                $status = 0;
            } elseif ($this->eModule == 'pages' && $divs->checkRightFromId($request->id, $this->_config->db->safinstances_id)) {
                $divs->delete('id = ' . $request->id);
                $msg = 'Element deleted.';
                $status = 1;

            } else {
                // @todo TODO : quick fix here to make deletable work for news but we do not check the access rights anymore... it is a problem
                $divs->delete('id = ' . $request->id);
                $msg = 'Element deleted.';
                $status = 1;
            }

            if ($status == 1) {
                /*
                 * Add trace of current action
                 */
                Sydney_Db_Trace::add($traceMessage, // message
                    $module, // module
                    Sydney_Tools::getTableName($divs), // module table name
                    'deletecontent', // action
                    $request->id, // [id]
                    $parentId // [parent id]
                );
            }
        }

        $this->view->ResultSet = array(
            'message' => $msg,
            'status'  => $status,
            'dbid'    => $request->id,
            'modal'   => false
        );
    }

    /**
     * Rollback a DIV from the DB
     *
     * @return void
     */
    public function rollbackdivAction()
    {
        $request = $this->getRequest();
        $status = 0;

        /*
         * GDE : 27/08/2010
         * Get detail for Add trace of current action
         */
        if ($this->eModule == 'pages') {
            $divs = new Pagdivspage($request->id);
            $rowPage = $divs->getParent();
            $parent_id = $rowPage->get()->id;

            $oPage = new Pagstructure;
            $oPage->saveLastupdateContent($parent_id, false);

            $module = 'adminpages';
            $traceMessage = 'trace.event.rollback_content_page' . ' [' . $rowPage->label . ']';
        } else {
            $divs = new Pagdivsnews($request->id);
            $rowNews = $divs->getParent();
            $parent_id = $rowNews->get()->id;

            $module = 'adminnews';
            $traceMessage = 'trace.event.rollback_content_news' . ' [' . $rowNews->shortdesc . ']';
        }
        // */

        if ($request->id <= 0) {
            $msg = 'Technical error. The entry ID has an inconcistent value!';
            $status = 0;
        } elseif ($this->eModule == 'pages' && $divs->checkRightFromId($request->id, $this->_config->db->safinstances_id)) {
            $divd = $divs->update(array(
                'content_draft' => '',
                'params_draft'  => '',
                'status'        => 'published'
            ), 'id = ' . $request->id);
            $msg = 'Element updated.';
            $status = 1;

        } else {
            // @todo TODO : quick fix here to make deletable work for news but we do not check the access rights anymore... it is a problem
            $divd = $divs->update(array(
                'content_draft' => '',
                'params_draft'  => '',
                'status'        => 'published'
            ), 'id = ' . $request->id);
            $msg = 'Element updated.';
            $status = 1;
        }

        if ($status == 1) {
            /*
             * GDE : 27/08/2010
             * Add trace of current action
             */
            Sydney_Db_Trace::add($traceMessage, // message
                $module, // module
                Sydney_Tools::getTableName($divs), // module table name
                'restorecontent', // action
                $request->id, // id
                $parent_id // parent id
            );
            // */
        }

        $this->view->ResultSet = array(
            'message' => $msg,
            'status'  => $status,
            'dbid'    => $request->id,
            'modal'   => false
        );
    }

    /**
     * Updates the structure order for all posted nodes (for the current saf instance).
     * URL : /adminpages/services/updatestrorder/format/json
     * @return void
     */
    public function updatestrorderAction()
    {
        $msg = 'error! Generic';
        $status = 0;
        try {
            $data = Zend_Json::decode($this->getRequest()->jsondata);
            $i = 0;
            $sql = '';
            foreach ($data as $n) {
                $sql .= "UPDATE pagstructure SET
							parent_id = '" . $n['parentid'] . "',
							pagorder = '" . $n['ndborder'] . "'
							WHERE id = '" . $n['dbid'] . "'
							AND safinstances_id='" . $this->safinstancesId . "'; ";
                $i++;
            }
            $this->_db->query($sql);
            $msg = 'Structure order updated. ' . $i . ' nodes affected.';
            PagstructureOp::cleanCache($this->safinstancesId);
            $status = 1;

            Sydney_Db_Trace::add('trace.event.reorder_page', // message
                'adminpages', // module
                'pagstructure', // module table name
                'updatepageorder', // action
                '', // id
                '' // parent id
            );
            // */

        } catch (Exception $e) {
            $msg = 'error! the order could not be saved. error : ' . $e;
            $status = 0;
        }
        $this->view->ResultSet = array(
            'message' => $msg,
            'status'  => $status,
            'modal'   => false
        );
    }

    /**
     *
     * @todo IMPORTANT: Add security here so we can not reorder data we do not own
     * @TODO Changer le nom de cette méthode car pas contextuelle du tout... On change l'order des pagdiv pas des pages
     * @return void
     */
    public function updatepagerorderAction()
    {
        $msg = 'error! Generic in updatepagerorderAction';
        $status = 0;
        try {
            $data = Zend_Json::decode($this->getRequest()->jsondata);
            $pagstructure_id = Zend_Json::decode($this->getRequest()->pagstructureid);
            $i = 1;
            foreach ($data as $n) {
                $nodes = new Pagdivs();
                $nodes->updateOrder($i, $n, $pagstructure_id);
                $i++;
            }
            $msg = 'Items order saved.';
            $status = 1;

        } catch (Exception $e) {
            $msg = 'error! The order could not be save';
        }
        $this->view->ResultSet = array(
            'message' => $msg,
            'status'  => $status,
            'modal'   => false
        );
    }

    /**
     * Modifie la zone pour un pagdiv
     * @since 12/11/2013
     */
    public function updatezoneforpagdivAction()
    {
        $msg = '';
        $status = 0;
        try {
            $pagstructureId = Zend_Json::decode($this->getRequest()->pagstructureid);
            $zone = $this->getRequest()->zone;
            $pagdivId = Zend_Json::decode($this->getRequest()->pagdivid);

            $pagDiv = new Pagdivs();
            $pagDiv->updateZone($pagdivId, $pagstructureId, $zone);

            $msg = 'Zone for pagdiv saved';
            $status = 1;
        } catch (Exception $e) {
            $msg = 'error! The zone could not be save : ' . $e->getMessage() . '(' . $this->getRequest()->zone . ')';
        }
        $this->view->ResultSet = array(
            'message' => $msg,
            'status'  => $status,
            'modal'   => false
        );
    }

    /**
     * Delete a node (and it's subnodes because we work with innoDB)
     * URL : /adminpages/services/deletenode/format/json
     * @return void
     */
    public function deletenodeAction()
    {
        $msg = 'error! Generic in deletenodeAction';
        $status = 0;
        $data = Zend_Json::decode($this->getRequest()->jsondata);
        try {
            if ($data['dbId'] > 0) {
                $pagsData = new Pagstructure();
                $rowPage = $pagsData->find($data['dbId']);

                $traceMessage = 'trace.event.settorecyclebin_page' . ' [' . $rowPage[0]->label . ']';
                if ($data['src'] == 'recyclebin') {
                    $traceMessage = 'trace.event.delete_page' . ' [' . $rowPage[0]->label . ']';
                    $pagsData->setPhysicalDelete();
                }
                $status = $pagsData->deleteNode($data['dbId'], $this->safinstancesId);
                $msg = $status . ' node(s) deleted';
                //$status = 1;

                /*
                 * GDE : 27/08/2010
                 * Add trace of current action
                 */
                Sydney_Db_Trace::add($traceMessage, // message
                    'adminpages', // module
                    Sydney_Tools::getTableName($pagsData), // module table name
                    'deletepage', // action
                    $data['dbId'] // id
                );
                // */

            } else {
                $msg = 'error! We could not find this node...';
            }
        } catch (Exception $e) {
            $msg = 'error! ' . $e->getMessage();
        }
        $this->view->ResultSet = array(
            'message' => $msg,
            'status'  => $status,
            'modal'   => false
        );
    }

    /**
     * Empty the structure cache and the page cache
     * @return void
     */
    public function emptycacheAction()
    {
        $data = Zend_Json::decode($this->getRequest()->jsondata);
        $msg = 'error! Could not clean cache!';
        $status = 0;
        if (isset($data['mode'])) {
            $cc = new Sydney_Cache_Manager();
            if ($data['mode'] == 'page') {
                if ($cc->clearPageCache($this->safinstancesId)) {
                    $msg = 'Success ! Cache empty ! ';
                    $status = 1;
                }
            }
            if ($data['mode'] == 'all') {
                if ($cc->clearAllCache($this->safinstancesId)) {
                    $msg = 'Success ! Cache empty ! ';
                    $status = 1;
                }
            }
        }
        $this->view->ResultSet = array(
            'message' => $msg,
            'status'  => $status,
            'modal'   => false
        );
    }

    /**
     * Sets the cache of all page to true with a validity of 24h
     * @return void
     */
    public function setcacheallAction()
    {
        $msg = 'error! Generic in setcacheallAction';
        $status = 0;
        $data = Zend_Json::decode($this->getRequest()->jsondata);
        if (isset($data['caching'])) {
            if ($data['caching'] == 'on') {
                $sql = "UPDATE pagstructure SET
				iscachable = 1,
				cachetime = 86400
				WHERE safinstances_id = '" . $this->safinstancesId . "'";
            }
            if ($data['caching'] == 'off') {
                $sql = "UPDATE pagstructure SET
				iscachable = 0
				WHERE safinstances_id = '" . $this->safinstancesId . "'";
            }
            try {
                $this->_db->query($sql);
                $msg = 'Success! Cache properties updated.';
                $status = 1;
            } catch (Exception $e) {
                $msg = 'error! Could not update cache data.';
            }
        } else {
            $msg = 'error! Invalid parameter';
        }
        $this->view->ResultSet = array(
            'message' => $msg,
            'status'  => $status,
            'modal'   => false
        );
    }

    /**
     *
     */
    public function htmlcleanAction()
    {
        $r = $this->getRequest();
        $html = $r->htmlstr;
        echo htmlentities(strip_tags($html));
        $this->render('blank');
    }

    /**
     *
     */
    public function publishpageAction()
    {
        $data = Zend_Json::decode($this->getRequest()->jsondata);
        $status = 0;
        try {
            $pagsData = new Pagstructure();
            $pagsData->publish($data['dbId']);

            /*
             * GDE : 27/08/2010
             * Add trace of current action
             */
            $rowPage = $pagsData->find($data['dbId']);
            Sydney_Db_Trace::add('trace.event.publish_page'
                . ' [' . $rowPage[0]->label . ']', // message
                'adminpages', // module
                Sydney_Tools::getTableName($pagsData), // module table name
                'publispage', // action
                $data['dbId'] // id
            );
            // */

            $msg = 'Element published.'; //.'ID of the element '.$div->id;
            $status = 1;
            $dbid = $data['dbId'];
        } catch (Exception $e) {
            $msg = 'Error while publishing the element in the database!' . $e->getMessage();
            $status = 0;
        }
        $this->view->ResultSet = array(
            'message' => $msg,
            'status'  => $status,
            'dbid'    => $dbid,
            'modal'   => false
        );
    }

    /**
     *
     */
    public function unpublishpageAction()
    {
        $data = Zend_Json::decode($this->getRequest()->jsondata);
        $status = 0;
        try {
            $pagsData = new Pagstructure();
            $pagsData->unpublish($data['dbId']);

            /*
             * GDE : 27/08/2010
             * Add trace of current action
             */
            $rowPage = $pagsData->find($data['dbId']);
            Sydney_Db_Trace::add('trace.event.unpublish_page'
                . ' [' . $rowPage[0]->label . ']', // message
                'adminpages', // module
                Sydney_Tools::getTableName($pagsData), // module table name
                'unpublishpage', // action
                $data['dbId'] // id
            );
            // */

            $msg = 'Element UNpublished.'; //.'ID of the element '.$div->id;
            $status = 1;
            $dbId = $data['dbId'];
        } catch (Exception $e) {
            $msg = 'Error while publishing the element in the database!' . $e->getMessage();
            $status = 0;
            $dbId = '';
        }
        $this->view->ResultSet = array(
            'message' => $msg,
            'status'  => $status,
            'dbid'    => $dbId,
            'modal'   => false
        );
    }

    /**
     *
     */
    public function blankAction()
    {
    }

    /**
     * Publish a div when it was a draft.
     *
     */
    public function publishdivAction()
    {
        $dbId = 0;
        $msg = 'Unknown status';
        $request = $this->getRequest();

        if ('pages' == $this->eModule) {
            $divs = new Pagdivspage();
            $traceMsg = 'trace.event.update_content_pages';
        } else {
            $divs = new Pagdivsnews();
            $traceMsg = 'trace.event.update_content_news';
        }

        $status = 0;
        if ($divs->checkRightFromId($request->id, $this->_config->db->safinstances_id, $this->eModule) || $request->id > 0) {
            $dbId = $request->id;
            $div = $divs->fetchRow('id=' . $request->id);
            $div->datemodified = new Zend_Db_Expr("NOW()");
            $div->status = 'published';
            $div->params = $div->params_draft;
            $div->params_draft = null;
            $div->content = $div->content_draft;
            $div->content_draft = null;
            $div->save();
            $status = 1;
            $msg = 'Content element saved as actual content';

            Sydney_Db_Trace::add($traceMsg, $request->getModuleName(),
                Sydney_Tools::getTableName($div), $request->getActionName(),
                $div->id, $request->pagstructureid);

        }
        $this->view->ResultSet = array(
            'message' => $msg,
            'status'  => $status,
            'dbid'    => $dbId,
            'modal'   => false
        );
    }

    /**
     * Unpublish a div (set a div as draft)
     *
     */
    public function unpublishdivAction()
    {
        $dbId = 0;
        $msg = 'Unknown status';
        $request = $this->getRequest();

        if ('pages' == $this->eModule) {
            $divs = new Pagdivspage();
            $traceMsg = 'trace.event.update_content_pages';
        } else {
            $divs = new Pagdivsnews();
            $traceMsg = 'trace.event.update_content_news';
        }

        $status = 0;
        if ($divs->checkRightFromId($request->id, $this->_config->db->safinstances_id, $this->eModule) || $request->id > 0) {
            $dbId = $request->id;
            $div = $divs->fetchRow('id=' . $request->id);
            $div->datemodified = new Zend_Db_Expr("NOW()");
            $div->status = 'draft';
            $div->params_draft = $div->params;
            // $div->params=null;
            $div->content_draft = $div->content;
            // $div->content=null;
            $div->save();
            $status = 1;
            $msg = 'Content element save as draft';
            Sydney_Db_Trace::add($traceMsg, $request->getModuleName(),
                Sydney_Tools::getTableName($div), $request->getActionName(),
                $div->id, $request->pagstructureid);

        }
        $this->view->ResultSet = array(
            'message' => $msg,
            'status'  => $status,
            'dbid'    => $dbId,
            'modal'   => false
        );
    }

    public function duplicatenodeAction()
    {
        $status = 0;
        $data = Zend_Json::decode($this->getRequest()->jsondata);
        if (!isset($data['dbId']) || $data['dbId'] == 0) {
            $msg = 'No node given';
        } else {
            $pages = new Pagstructure();
            $return = $pages->duplicatenode($data['dbId']);
            $msg = $return['msg'];
            $status = $return['status'];
        }

        $this->view->ResultSet = array(
            'message' => $msg,
            'status'  => $status,
            'dbid'    => $dbId,
            'modal'   => false,
        );
    }

    /**
     *
     */
    public function toggleonlineAction()
    {
        $dbId = $this->_getParam('id', null);
        $request = $this->getRequest();
        $status = 0;

        if (null == $dbId) {
            $msg = 'A problem occur.';
        } else {
            $result = Pagdivs::toggleonline($dbId);

            if (false == $result) {
                $msg = 'Unknow status';
            } else {
                $msg = 'Element ' . $result;
                $status = 1;

                if ('pages' == $this->eModule) {
                    $traceMsg = 'trace.event.update_content_pages';
                } else {
                    $traceMsg = 'trace.event.update_content_news';
                }

                Sydney_Db_Trace::add($traceMsg, $request->getModuleName(),
                    'pagdivs', $request->getActionName(),
                    $dbId, $request->pagstructureid);
            }
        }

        $this->view->ResultSet = array(
            'message' => $msg,
            'status'  => $status,
            'dbid'    => $dbId,
            'modal'   => false,
        );
    }

    public function duplicateAction()
    {
        $dbId = $this->_getParam('id', null);
        $request = $this->getRequest();
        $status = 0;

        if (null == $dbId) {
            $msg = 'A problem occur.';
        } else {

            if ('pages' == $this->eModule) {
                // Soucis : il faudrait récuperer l'ordre du type de contenu
                $result = Pagdivspage::duplicate($dbId, $request->pagstructureid);
                $traceMsg = 'trace.event.duplicate_content_pages';
            } else {
                $result = Pagdivsnews::duplicate($dbId, $request->pagstructureid);
                $traceMsg = 'trace.event.duplicate_content_news';
            }

            if (false == $result) {
                $msg = 'Unknow status';
            } else {
                $msg = 'Element ' . $result;
                $status = 1;

                Sydney_Db_Trace::add($traceMsg, $request->getModuleName(),
                    'pagdivs', $request->getActionName(),
                    $result, $request->pagstructureid);
            }
        }

        $this->view->ResultSet = array(
            'message' => $msg,
            'status'  => $status,
            'dbid'    => $result,
            'modal'   => false,
        );
    }

    /**
     *
     * Enter description here ...
     */
    public function getlistpagdivsAction()
    {
        $r = $this->getRequest();
        if (isset($r->dbid)) {
            $node = new Pagdivspage;
            $this->view->listContents = $node->getDivs($r->dbid);
        }
    }

    /**
     * Update label of pagdiv object
     *
     */
    public function updatelabelAction()
    {
        $request = $this->getRequest();
        $status = 0;
        $msg = 'Label NOT saved!';
        if (isset($request->dbid) && $request->dbid > 0) {
            $pagdivs = new Pagdivs();
            if ($pagdivs->update($data = array('label' => $request->label), 'id = ' . $request->dbid)) {
                $status = 1;
                $msg = 'Label saved';
            }
        }
        $this->view->ResultSet = array(
            'message' => $msg,
            'status'  => $status,
            'dbid'    => $request->dbid,
            'modal'   => false
        );
    }

    public function internlinkbrowserAction()
    {
        $pages = new Pagstructure();
        $this->view->structureArray = $pages->toArray($this->safinstancesId);
    }


    public function getcleanlabelpageAction()
    {
        $label = $this->_getParam('label', null);
        //
        $this->view->resultSet = array('cleanLabel' => Sydney_Tools_Friendlyurls::getUrlLabel($label));
    }

    public function getcleanurlpageAction()
    {
        $label = $this->_getParam('label', null);
        $id = $this->_getParam('id', null);

        $this->view->resultSet = array('cleanUrl' => Sydney_Tools_Friendlyurls::getFriendlyUrl($id, $label, 'page', new Zend_View_Helper_Url()));
    }

    /**
     * @since 19/02/2014
     */
    public function getcleanurlpagebyidnodeAction()
    {
        $id = (int) $this->_getParam('id', null);

        $page = new Pagstructure();
        $data = $page->get($id);

        $label = ((Sydney_Tools_Sydneyglobals::getConf('general')->url->newFormat) && !empty($data['url'])) ? $data['url'] : Sydney_Tools_Friendlyurls::getUrlLabel($data['label']);

        $this->view->resultSet = array('url' => Sydney_Tools_Friendlyurls::getFriendlyUrl($id, $label, 'page', new Zend_View_Helper_Url()));
    }

    public function getpreviewlayoutAction()
    {

        $layoutName = $this->_getParam('layoutname');

        $layout = new Sydney_Layout_Layout();
        $layout->setName($layoutName);

        $this->view->resultSet = array('preview' => $layout->calculatePreview()->getPreview());
    }
}

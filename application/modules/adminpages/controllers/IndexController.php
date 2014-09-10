<?php
include_once('PagstructureOp.php');
/**
 * Controller
 */

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
class Adminpages_IndexController extends Sydney_Controller_Action
{

    public function init()
    {
        /*
         * @change GDE - 05/2014 - Content Translation
         * Load translation
         */
        $this->view->nodeTranslate = new Translate_Content_Node();
        parent::init();
    }

    /**
     * Shows the structure editor
     */
    public function indexAction()
    {
        $this->setSubtitle('Structure Editor');
        $this->setSideBar('index', 'pages');
        $this->layout->langswitch = false;
        $this->layout->search = true;
        $pgs = new Pagstructure();
        $this->view->structureArray = $pgs->toArray($this->safinstancesId);
    }

    /**
     * Shows the structure editor
     */
    public function selectAction()
    {
        if (isset($_GET['source'])) {
            $this->_helper->layout->setLayout('ckeditor-browser');
        } else {
            $this->_helper->layout->setLayout('ckeditor');
        }

        $this->view->ckeditor_context = $this->getRequest()->context;
        $helper = $this->view->getHelper('StructureEditor');
        $helper->setContext($this->getRequest()->context);

        $this->layout->langswitch = true;
        $this->layout->search = true;
        $pgs = new Pagstructure();
        $this->view->structureArray = $pgs->toArray($this->safinstancesId);

        // CKEditor params
        $this->view->CKEditorFuncNum = $this->getRequest()->CKEditorFuncNum;
        $this->view->langCode = $this->getRequest()->langCode;
        $this->view->CKEditor = $this->getRequest()->CKEditor;

        $this->render('index');
    }

    /**
     * Create a page in the structure or edit it's properties
     * @return void
     */
    public function createAction()
    {
        $this->view->node = null;
        if ($this->getRequest()->parentid) {
            $this->view->parentid = $this->getRequest()->parentid;
        } else {
            $this->view->parentid = null;
        }

        $usrgDB = new Usersgroups();
        $this->view->usersgroups = $usrgDB->fetchLabelstoFlatArray();

        $pgs = new Pagstructure();
        $this->view->structureArray = $pgs->toArray($this->safinstancesId);
        $this->view->menusArray = $this->getMenusArray(0, true);
        $this->setSubtitle('Create a new page');
        $this->setSideBar('create', 'pages');
    }

    /**
     * @since 24/03/2014
     */
    public function editAction()
    {
        $this->_forward('editproperties');
    }

    /**
     * Edit the properties of a page
     * @return void
     */
    public function editpropertiesAction()
    {
        $usrgDB = new Usersgroups();
        $this->view->usersgroups = $usrgDB->fetchLabelstoFlatArray();

        $nodes = new Pagstructure();
        $where = 'id = ' . $this->getRequest()->id . ' AND safinstances_id = ' . $this->safinstancesId;

        $this->view->node = $nodes->fetchRow($where);
        if ($this->view->node) {
            $pgs = new Pagstructure();
            $this->view->structureArray = $pgs->toArray($this->safinstancesId);
            $this->view->pagid = $this->getRequest()->id;
            $this->view->menusArray = $this->getMenusArray($this->getRequest()->id, false);

            $this->view->editType = 'normal';
            $this->setSubtitle2($this->view->node->label);
            $this->setSubtitle('Properties');
            $this->setSideBar('settings', 'pages');
            $this->render('create');
        } else {
            $this->render('index');
        }
    }

    /**
     * @return void
     */
    public function editadvancedpropertiesAction()
    {
        // Layout optionnel li� � l'instance et d�fini dans le default.config.ini
        $this->view->layoutsopt = isset($this->_config->general->layoutsopt) ? preg_split('/,/', $this->_config->general->layoutsopt) : array();

        $nodes = new Pagstructure();
        $where = 'id = ' . $this->getRequest()->id . ' AND safinstances_id = ' . $this->safinstancesId;

        $this->view->node = $nodes->fetchRow($where);
        if ($this->view->node) {
            $pgs = new Pagstructure();
            $this->view->structureArray = $pgs->toArray($this->safinstancesId);
            $this->view->pagid = $this->getRequest()->id;
            $this->view->menusArray = $this->getMenusArray($this->getRequest()->id, false);

            $this->view->editType = 'advanced';
            $this->setSubtitle2($this->view->node->label);
            $this->setSubtitle('Advanced properties');
            $this->setSideBar('settings', 'pages');
            $this->render('advancedproperties');
        } else {
            $this->render('index');
        }
    }

    /**
     * Edit or create a node of the structure.
     *
     * @return void
     * @todo implement validators
     * @todo implement the advanced options (templates...)
     */
    public function editprocessAction()
    {
        $vNotEmpty = new Zend_Validate_NotEmpty();
        $r = $this->getRequest();
        $eid = 0;
        if ($vNotEmpty->isValid($r->label)) {
            $table = new Pagstructure();
            if ($r->ishome == '1') {
                $ishome = 1;
                $homeRowsTbl = new Pagstructure();
                $homeRow = $homeRowsTbl->fetchRow('ishome = 1 AND safinstances_id = ' . $this->safinstancesId);
                if (is_object($homeRow)) {
                    if (isset($homeRow->id) && $homeRow->id > 0 && $homeRow->id != $r->id) {
                        $homeRow->ishome = 1;
                    } else {
                        $homeRow->ishome = 0;
                    }
                }
            } else {
                $ishome = 0;
            }

            // Create a page
            if ($r->id <= 0 || !$r->id) {
                $oldnode = null;
                $data = array(
                    'label'           => ($r->label),
                    'htmltitle'       => ($r->htmltitle),
                    'url'             => Sydney_Tools_Friendlyurls::getUrlLabel($r->url),
                    'parent_id'       => ($r->parent_id) ? $r->parent_id : null,
                    'ishome'          => $ishome,
                    'safinstances_id' => $this->safinstancesId,
                    'metakeywords'    => '',
                    'metadesc'        => '',
                    'iscachable'      => '',
                    'cachetime'       => '',
                    'redirecttoid'    => '',
                    'usersgroups_id'  => $r->usersgroups_id,
                    'shortdesc'       => '',
                    'colorcode'       => '',
                    'layout'          => ''
                );
                $eid = $table->insert($data);
                // GDE : 27/08/2010 Add trace of current action
                Sydney_Db_Trace::add(
                    'trace.event.create_page' . ' [' . $r->label . ']', // message
                    'adminpages', // module
                    Sydney_Tools::getTableName($table), // module table name
                    'createpage', // action
                    $eid // id
                );
            } elseif ($r->id > 0) { // edit an entry

                $nodeDB = new Pagstructure();
                $node = $nodeDB->fetchRow('id = ' . $r->id . ' AND safinstances_id = ' . $this->safinstancesId);

                // #120 - place on latest position in new node if parent change
                if ($node->parent_id != (int) $r->parent_id) {
                    // get last position
                    $node->pagorder = $nodeDB->getLatestPoitionInNode($r->parent_id);
                }

                /*
                 * @change GDE - 05/2014 - Content Translation
                 * Save translation of content (on native table for default language and on translation table for others)
                 */
                $node->label = $this->view->nodeTranslate->translate($node->label, $r->label, $r->id);
                $node->htmltitle = $this->view->nodeTranslate->translate($node->htmltitle, $r->htmltitle, $r->id, 'htmltitle');

                $requestUrl = Sydney_Tools_Friendlyurls::getUrlLabel($r->url);
                $node->url = $this->view->nodeTranslate->translate($node->url, $requestUrl, $r->id, 'url');

                $node->parent_id = ($r->parent_id) ? $r->parent_id : null;
                $node->ishome = $ishome;
                $node->redirecttoid = $r->redirecttoid;
                if ($node->usersgroups_id != $r->usersgroups_id) {
                    $node->usersgroups_id = $r->usersgroups_id;
                    $nodeDB->updateAccessRights($node->id, $r->usersgroups_id, true);
                }
                $node->save();
                $eid = $r->id;
                // GDE : 27/08/2010 - Add trace of current action
                Sydney_Db_Trace::add(
                    'trace.event.update_page_properties' . ' [' . $r->label . ']', // message
                    'adminpages', // module
                    Sydney_Tools::getTableName($nodeDB), // module table name
                    'editproperties', // action
                    $eid // id
                );
            }
            // save the update of the homepage if everything went OK and we change the home page
            if ($r->ishome == '1' && is_object($homeRow) && $homeRow->id != $r->id) {
                $homeRow->save();
            }
        }

        if ($eid > 0) {
            // update menu
            $mns = new PagstructurePagmenus();
            $mns->delete('pagstructure_id = ' . $eid);

            if (is_array($r->menus)) {
                foreach ($r->menus as $mid) {
                    $crow = $mns->createRow();
                    $crow->pagstructure_id = $eid;
                    $crow->pagmenus_id = $mid;
                    $crow->save();
                }
            }
            PagstructureOp::cleanCache($this->safinstancesId);
            $this->redirect('/adminpages/pages/edit/id/' . $eid);
        }
    }

    /**
     *
     */
    public function editadvancedprocessAction()
    {

        $r = $this->getRequest();
        $eid = 0;

        if ($r->id > 0) {
            $nodeDB = new Pagstructure();
            $node = $nodeDB->fetchRow('id = ' . $r->id . ' AND safinstances_id = ' . $this->safinstancesId);

            $node->metakeywords = $r->metakeywords;
            $node->metadesc = $r->metadesc;
            $node->iscachable = $r->iscachable;
            $node->cachetime = $r->cachetime;
            $node->redirecttoid = $r->redirecttoid;
            $node->shortdesc = $r->shortdesc;
            $node->colorcode = $r->colorcode;
            $node->layout = $r->layout;
            $node->save();
            $eid = $r->id;

            // GDE : 27/08/2010 - Add trace of current action
            Sydney_Db_Trace::add(
                'trace.event.update_advanced_properties' . ' [' . $node->label . ']', // message
                'adminpages', // module
                Sydney_Tools::getTableName($nodeDB), // module table name
                'editadvancedproperties', // action
                $eid // id
            );
        }

        // update the linked menu
        $mns = new PagstructurePagmenus();
        $mns->delete('pagstructure_id = ' . $eid);
        //Zend_Debug::dump($r->menus);
        if (is_array($r->menus)) {
            foreach ($r->menus as $mid) {
                $crow = $mns->createRow();
                $crow->pagstructure_id = $eid;
                $crow->pagmenus_id = $mid;
                $crow->save();
            }
        }
        PagstructureOp::cleanCache($this->safinstancesId);
        $this->redirect('/adminpages/pages/edit/id/' . $eid);
    }

    /**
     * Shows the page advanced settings (I am not sure this is useful)
     * @return void
     */
    public function settingsAction()
    {
        $this->setSubtitle('Settings');
        $this->setSideBar('settings', 'pages');
        $this->render('index');
    }

    /**
     * Search results
     * @todo add pagination
     * @todo Show only a part of the result content (part with the data found)
     */
    public function searchAction()
    {
        $r = $this->getRequest();
        $sr = array();
        $this->view->safinstances_id = $this->safinstancesId;
        if (isset($r->q) && $r->q != '') {
            $this->view->q = $r->q;
            $sql = "SELECT
			  pagstructure.label,
			  pagstructure.status AS pstatus,
			  pagdivs.status AS dstatus,
			  pagdivs.content,
			  pagdivs.content_draft,
			  pagstructure.id
			FROM
			  pagstructure, pagstructure_pagdivs, pagdivs
			WHERE
			 pagstructure.id = pagstructure_id AND
			 pagdivs.id = pagdivs_id AND
			 pagstructure.safinstances_id = " . $this->safinstancesId . " AND
			 (	pagstructure.label LIKE '%" . addslashes($r->q) . "%'
			 	OR pagdivs.content LIKE '%" . addslashes($r->q) . "%'
			 	OR pagdivs.content_draft LIKE '%" . addslashes($r->q) . "%'
			 )
			GROUP BY
			 pagstructure.id";

            $sr = $this->_db->fetchAll($sql);
        }
        $this->view->sr = $sr;
    }

    /**
     * Returns an array which can be used to represent the menus linked to a page structure
     *
     * @param $pagStructureId Int ID of the entry in the structure
     * @param $isNewPage Boolean true if it's a new page (so we check the default menu)
     * @return Array Structure of the array elements : array( id , label, desc , isChcecked )
     */
    public function getMenusArray($pagStructureId = 0, $isNewPage = true)
    {
        $selectedDb = new PagstructurePagmenus();
        $selected = $selectedDb->getPagmenusLinkedTo($pagStructureId);

        $toReturn = array();
        $sql = 'SELECT pagmenus.id AS id, pagmenus.label AS label, pagmenus.desc AS descr
				FROM pagmenus, pagmenus_safinstances
				WHERE pagmenus.id = pagmenus_safinstances.pagmenus_id
				AND  pagmenus_safinstances.safinstances_id = ' . $this->safinstancesId . '
				AND pagmenus.active = 1';
        $db = $this->_registry->get('db');
        foreach ($db->fetchAll($sql) as $it) {
            if ($it['id'] == 1 && $isNewPage) {
                $isChecked = true;
            } elseif (in_array($it['id'], $selected)) {
                $isChecked = true;
            } else {
                $isChecked = false;
            }
            $toReturn[] = array($it['id'], $it['label'], $it['descr'], $isChecked);
        }

        return $toReturn;
    }

}

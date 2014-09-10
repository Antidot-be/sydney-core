<?php
/**
 * Controller Adminglobal Index
 */

/**
 * Management of all the webinstances (also called meta-admin for whatever reason).
 * This tool can be used to create webinstances, put them offline and so on...
 *
 * @package Admininvoicer
 * @subpackage Controller
 * @author Arnaud Selvais
 * @since 06/10/10
 * @copyright Antidot Inc. / S.A.
 */
class Adminglobal_IndexController extends Sydney_Controller_Action
{
    /**
     * Initialization of the basics
     * @return
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
        $this->setSubtitle2('Dashboard');
    }

    /**
     *
     */
    public function safinstancesAction()
    {
        $this->setSubtitle2('Webinstances');
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
        $this->view->headLink()->appendStylesheet($this->view->cdnurl . '/sydneyassets/styles/zendform.css');
        // Main safinstance info form
        $form = new SafinstancesFormOp();
        $safform = $form->getMainform();
        if ($safinstance) {
            $safform->populate($safinstance->toArray());
        }
        $this->view->safinstancesForm = $safform;
        // Link to menus form
        $menusForm = new SafinstancesFormOp();
        $linked = new PagmenusSafinstances();
        $menusFormF = $menusForm->getmenusForm();
        $menusFormF->populate(array('PagmenusSafinstances' => $linked->getPagmenusLinkedTo($safinstance->id)));
        $menusFormF->setRowId($safinstance->id);
        $this->view->menusForm = $menusFormF;
        // Link to companies form

        // Link to modules
        $modulesForm = new SafinstancesFormOp();
        $modForm = $modulesForm->getmodulesForm();
        $linked = new SafinstancesSafmodules();
        $modForm->populate(array('SafinstancesSafmodules' => $linked->getSafmodulesLinkedTo($safinstance->id)));
        $modForm->setRowId($safinstance->id);
        $this->view->modulesForm = $modForm;
        /*-----------------------*/
        $configdefault = parse_ini_file(Sydney_Tools::getRootPath() . '/core/webinstances/sydney/config/config.default.ini', true);
        $this->view->configdefault = $configdefault;
        /*-----------------------*/
        $configsite = false;
        if ($safinstance) {
            $fpath = Sydney_Tools::getRootPath() . '/webinstances/' . $safinstance->rootpath . '/config/config.default.ini';
            if (file_exists($fpath)) {
                $configsite = parse_ini_file($fpath, true);
            }
        }
        $this->view->configsite = $configsite;
        /*-----------------------*/
        $cfapapath = '/etc/apache2/sites-enabled/';
        $configapache = false;
        if ($safinstance) {
            if (file_exists($cfapapath . $safinstance->rootpath)) {
                $configapache = implode("", file($cfapapath . $safinstance->rootpath));
            }
        }
        $this->view->configapache = $configapache;
    }

    /**
     *
     */
    public function usersgroupsAction()
    {
        $this->setSubtitle2('Groups');
    }

    /**
     *
     */
    public function usersAction()
    {
        $this->setSubtitle2('Users');
    }

    /**
     *
     */
    public function safmodulesAction()
    {
        $this->setSubtitle2('Modules');

        $sDB = new Safmodules();
        $safmodules = array();

        foreach ($sDB->fetchAll() as $e) {
            $safmodules[] = $e->name;
        }

        $rModPath = '/www/sydney/core/application/modules/';
        $realModules = array();
        if ($handle = opendir($rModPath)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && $file != ".svn") {
                    $realModules[] = $file;
                }

            }
            closedir($handle);
        }

        $comp1 = array_diff($safmodules, $realModules);
        $comp2 = array_diff($realModules, $safmodules);
        sort($comp1);
        sort($comp2);
        $this->view->comp1 = $comp1;
        $this->view->comp2 = $comp2;

    }

    /**
     *
     */
    public function editsafmodulesAction()
    {
        $r = $this->getRequest();
        $safmodule = false;
        if (isset($r->id) && preg_match('/^[0-9]{1,50}$/', $r->id)) {
            $sDB = new Safmodules();
            $s = $sDB->find($r->id);
            if (count($s) == 1) {
                $safmodule = $s[0];
            }
        }
        $this->setSubtitle2('Modules : Edit / Add');
        $this->view->headLink()->appendStylesheet($this->view->cdnurl . '/sydneyassets/styles/zendform.css');
        // Main safinstance info form
        $form = new SafmodulesForm();
        if ($safmodule) {
            $form->populate($safmodule->toArray());
        }
        $this->view->safmodulesForm = $form;
    }

}

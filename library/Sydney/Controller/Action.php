<?php
include_once('Zend/Controller/Action.php');

/**
 * This abstract class should be the parent of every controller of the sydney admin interface.
 * It takes care of some usefull global variables (for the view and the controller).
 *
 * @author Arnaud Selvais
 * @package SydneyLibrary
 * @subpackage Controller
 */
abstract class Sydney_Controller_Action extends Sydney_Controller_Actionpublic
{
    private $generateJsOnTheFly = false;
    private $generateCssOnTheFly = false;
    private $concatenateJs = false;

    /**
     * Permet de définir un utilisateur comme dev
     * Attention si le l'attribut est à "vrai" tous les utilisateurs seront developpeur
     * Impact connue : affichage des type de contenu pour dev au autres rangs
     * JTO - 156 - 12/09/2013 - Le contenu "dev" est désormais seulement réserver à ceux-ci
     * @var bool
     */
    protected $isDeveloper = false;
    /**
     * The webinsetance (safinstance) id found in the config file
     * @var int
     */
    protected $safinstancesId;
    /**
     * The user's ID from the DB (if he is authenticated)
     * @var int
     */
    protected $usersId;
    /**
     * @var Sydney_Db_Table
     */
    protected $_db;
    /**
     * Array of interesting data about the authenticated user coming from the DB
     * @var array
     */
    protected $usersData;
    /**
     * Contains the application registry
     * @var Zend_Registry
     */
    protected $_registry;
    /**
     * Contains the translation object
     * @var Zend_Translate
     */
    protected $_translate;
    /**
     * Configuration
     * @var Zend_Config
     */
    protected $_config;
    /**
     *
     * @var Zend_Auth
     */
    protected $_auth;
    /**
     * Defines if the controller is a service (JSON and so on) or not
     * @var boolean
     */
    protected $_isService = false;
    /**
     * Javascript we will use
     * @var array
     */
    protected $jscripts = array(
        '/admin/jscripts/index/vfname/sydneyscripts.js'
        //'/sydneyassets/scripts/sydneyscripts.js'
    );
    protected $jscriptsDev = array(
        '/admin/jscripts/index/vfname/sydneyscripts.js'
    );
    protected $csses = array(
        '/admin/jscripts/index/vfname/sydneystyles.css'
        //'/sydneyassets/styles/sydneystyles.css'
    );
    protected $cssesDev = array(
        '/admin/jscripts/index/vfname/sydneystyles.css'
    );
    /**
     * @var Sets this var to 'no' to use another layout than the sydney one
     */
    public $sydneyLayout = false;
    /**
     * Available modules
     * @var array
     */
    protected $availableModules;

    /**
     * Auto initialization of important params for sydney
     * @return void
     */
    public function init()
    {
        // add the general helper path for sydney
        $this->view->addHelperPath(Sydney_Tools_Paths::getCorePath() . '/library/Sydney/View/Helper', 'Sydney_View_Helper');
        $this->view->addHelperPath(Sydney_Tools_Paths::getCorePath() . '/application/modules/adminpages/views/helpers',
            'Adminpages_View_Helper');
        $this->getResponse()->setHeader('Accept-encoding', 'gzip,deflate');
        // setup the basics
        $this->_registry = Zend_Registry::getInstance();
        $this->_config = $this->_registry->get('config');
        $this->_db = $this->_registry->get('db');
        $this->safinstancesId = $this->_config->db->safinstances_id;
        $this->view->safinstances_id = $this->_config->db->safinstances_id;
        $this->view->config = $this->_config;
        $this->_translate = $this->_registry->get('Zend_Translate');
        $this->_auth = Sydney_Auth::getInstance();

        $this->usersData = Sydney_Tools::getUserdata();
        if (isset($this->usersData['users_id'])) {
            $this->usersId = $this->usersData['users_id'];
            if (in_array(7, $this->usersData['member_of_groups'])) {
                $this->isDeveloper = true;
            }
        }

        $this->view->cdn = $this->_config->general->cdn;
        $this->view->isDeveloper = $this->isDeveloper;

        $this->view->moduleName = $this->_getParam('module');
        $this->view->controllerName = $this->_getParam('controller');
        $this->view->actionName = $this->_getParam('action');

        // On vérifie si on a un mapping entre le module courant et les tables qu'il utilise
        // ce mapping est utilisé pour charger ensuite automatiquement toutes les traductions de contenu de ce module
        if (!is_null($this->_config->general->modules->{$this->view->moduleName}) && !empty($this->_config->general->modules->{$this->view->moduleName}->tables)) {

            $translate = new Zend_Translate(
                array(
                    'adapter' => 'Sydney_Translate_Adapter_Db',
                    'tableName' => $this->_config->general->modules->{$this->view->moduleName}->tables,
                    'locale' => $this->config->general->locale
                )
            );

            echo $translate->_('test');

            $this->_registry->set('Zend_Translate_Content', $translate);
        }

        // set up the log
        $this->logger = new Sydney_Log();
        $this->logger->setEventItem('className', get_class($this));
        $this->logger->addFilterDatabase();

        $safmodulesDB = new Safmodules();
        $this->availableModules = $safmodulesDB->getAvailableAvModules($this->safinstancesId, $this->usersData['member_of_groups'], true);

        if (!$this->_isService) {
            $this->setUpLayoutProps();
        }
        if (isset($this->getRequest()->sydneylayout) && $this->getRequest()->sydneylayout == 'no') {
            $this->_helper->layout->disableLayout();
            $this->view->sydneylayout = $this->getRequest()->sydneylayout;
        }

    }

    /**
     *
     */
    protected function setupCSS()
    {
        $this->view->headLink()->appendStylesheet($this->view->cdnurl . $this->csses[0]);
    }

    /**
     *
     */
    protected function setupScripts()
    {
        // @todo TODO change that for i18n according to the language
        $this->view->headScript()->appendFile($this->view->cdn . '/sydneyassets/scripts/i18n/en.js', 'text/javascript');
        $this->view->headScript()->appendFile($this->view->cdnurl . $this->jscripts[0], 'text/javascript');
    }

    /**
     *
     * @return void
     */
    public function setUpLayoutProps()
    {
        $this->layout = $this->_helper->layout();
        // setup the layout
        if ((isset($this->_config->general->sydneylayout) && $this->_config->general->sydneylayout == 'no') || (isset($this->sydneyLayout) && $this->sydneyLayout == 'no')) {
            // do not use the sydney layout if it is defined as it in the ini file

        } else {
            $this->layout->setLayoutPath(Sydney_Tools_Paths::getCorePath() . '/webinstances/sydney/layouts');
            $this->layout->setLayout('layoutSydney');
        }
        $this->view->cdnurl = $this->_config->general->cdn;
        $this->layout->cdnurl = $this->_config->general->cdn;
        $this->setupCSS();

        // setup some layout vars
        $this->layout->registry = $this->_registry;
        $this->layout->_config = $this->_config;
        $this->layout->auth = $this->_auth;
        $this->layout->users_id = $this->usersId;
        $this->layout->translate = $this->_registry->get('Zend_Translate');
        $this->view->translate = $this->_registry->get('Zend_Translate');
        $this->layout->avmodules = $this->availableModules;

        // define the current module
        $this->layout->currentModule = $this->_request->getModuleName();

        $this->setupScripts();


        // set up the title
        $this->view->headTitle()->setSeparator(' / ');
    }

    /**
     * Sets the sub section we are in.
     * It should be the action (inside a controler)
     *
     * @param $txt String the title to push
     * @return void
     */
    protected function setSubtitle($txt)
    {
        $this->view->headTitle($txt);
        $this->layout->subtitle = $txt;
    }

    /**
     * Sets the subtitle (level 3) in the layout
     * @param $txt String the title to push
     * @return void
     */
    protected function setSubtitle2($txt)
    {
        $this->layout->subtitle2 = $txt;
    }

    /**
     * Sets the sidebar to be used from the module adminsidebars
     *
     * @param $action
     * @param $controller
     * @return void
     */
    protected function setSideBar($action = 'index', $controller = 'index', $module = 'adminsidebars')
    {
        $this->layout->sidebaraction = array($action, $controller, $module);
    }


    /**
     * Initialize the required request params for the YUI DataTable sorting and pagination
     */
    protected function _initDataTableRequest()
    {
        $this->r = $this->getRequest();
        if (isset($this->r->sort)) {
            $this->sort = $this->r->sort;
        } else {
            $this->sort = 'id';
        }
        if (isset($this->r->dir) && strtolower($this->r->dir) == 'asc') {
            $this->dir = 'asc';
        } else {
            $this->dir = 'desc';
        }
        if (isset($this->r->startIndex)) {
            $this->startIndex = $this->r->startIndex;
        } else {
            $this->startIndex = 0;
        }
        if (isset($this->r->results)) {
            $this->results = $this->r->results;
        } else {
            $this->results = null;
        }
        if (isset($this->r->hidefields)) {
            $this->hidefields = $this->r->hidefields;
        } else {
            $this->hidefields = null;
        }
        if (isset($this->r->paramsurl)) {
            parse_str($this->r->paramsurl, $this->paramsurl);
        } else {
            $this->paramsurl = array();
        }
    }

    /**
     * Creates a form and populates data from a table.
     * This method can be used to display a form for editing table data
     * typically in the generic modules using the YUI datatable and modelforms.
     *
     * @param String $modelName
     * @param String $formName
     * @param String $subTitle
     */
    protected function _createformAction($modelName, $formName, $subTitle = '')
    {
        $r = $this->getRequest();
        $row = false;
        $id = false;

        // GDE - 19/04/2011 - set values to object form before instanciation
        call_user_func(array($formName, 'setParams'), $r, 'request');
        if (isset($r->id) && preg_match('/^[0-9]{1,50}$/', $r->id)) {
            $sDB = new $modelName;
            $s = $sDB->find($r->id);
            if (count($s) == 1) {
                $row = $s[0];
                // GDE - 19/04/2011 - set values to object form before instanciation
                call_user_func(array($formName, 'setParams'), $row, 'row');
            }
            $id = $r->id;
        }
        if ($subTitle != '') {
            $this->setSubtitle2($subTitle);
        }
        $this->view->headLink()->appendStylesheet($this->view->cdnurl . '/sydneyassets/styles/zendform.css');

        $form = new $formName;
        if ($row) {
            $form->populate($row->toArray());
        }

        $this->view->row = $row;
        $this->view->form = $form;

        return $id;
    }

    /**
     * Editing a row from a table this is typically used in the service controller combined with
     * the YUI datatable, forms and helpers
     *
     * @param String $modelName Model to affect name
     * @param String $formName Form class containing the data
     * @param array $addToRequest Associative array of fields => values to add to the request (to be inserted)
     * @param array $m2mtables Array containing the list of m2m tables to update example: array('FormelementsFormfilters','FormelementsFormvalidators') the values are coming from a subform (usually a list of checkboxes)
     * @return Int The row ID we just update or created
     */
    protected function _editfieldAction($modelName, $formName, $addToRequest = array(), $m2mtables = array())
    {
        $p = $this->getRequest()->getPost();
        $resp = array('Error...', 0);
        $msgs = array();
        $odata = array();
        $form = new $formName;

        if (!$form->isValid($p)) {
            $msgs = $form->getMessages();
            $odata = $p;
            $resp = array('Error in the form', 0);
        } else {
            if (count($addToRequest) > 0) {
                foreach ($addToRequest as $k => $v) {
                    $p[$k] = $v;
                }
            }
            $sDB = new $modelName;
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
                    //if (isset($row->$k) && $k != 'id') $row->$k = $v;
                    // interface with GTP system has needed the id on datas,
                    // this is the most easy way to get the id
                    if (isset($row->$k)) {
                        switch ($k) {
                            case (substr($k, -4) == 'Date') :
                                $row->$k = Sydney_Tools::getDate($v, Zend_Date::ISO_8601);
                                break;
                            default:
                                $row->$k = $v;
                                break;
                        }
                    }
                }

                try {
                    $rowId = $row->save();
                    $resp = array('OK', 1);
                    $odata = $row->toArray();
                } catch (Exception $e) {
                    $msgs = $e->getMessage();
                    $resp = array($msgs, 0);
                }

                if (method_exists($row->getTable(), 'getGtpInterface')) {
                    $gtp = $row->getTable()->getGtpInterface();
                }
            }
        }
        // update potential m2m links
        if ($form->isValid($p) && $rowId > 0 && count($m2mtables) > 0) {
            // $this->view->debug = array();
            // update the m to m tables
            foreach ($m2mtables as $v) {
                if (class_exists($v)) {
                    $subform = new $v;

                    if (method_exists($subform, 'formsUpdate')) {
                        call_user_func(array($subform, 'formsUpdate'), $p);
                    } else {
                        if (isset($p[$v])) {
                            $dt = $p[$v];
                        } else {
                            $dt = array();
                        }
                        $tblns = preg_split('/_/', $subform->getTableName());
                        if (count($tblns) == 2) {
                            $subform->delete($sDB->getTableName() . "_id = '" . $rowId . "' ");
                            foreach ($dt as $dtEl) {
                                $inserts = array();
                                $inserts[$sDB->getTableName() . '_id'] = $rowId;
                                foreach ($tblns as $lmn) {
                                    if (!isset($inserts[$lmn . '_id'])) {
                                        $inserts[$lmn . '_id'] = $dtEl;
                                    }
                                }
                                // options if any
                                $paramField = 'param-' . $v . '-' . $dtEl;
                                if (isset($p[$paramField]) && in_array('voptions', $subform->fieldsNames)) {
                                    $inserts['voptions'] = $p[$paramField];
                                }
                                $rinm = $subform->insert($inserts);
                                // $this->view->debug[] = array($v , $inserts, $rinm );
                            }
                        }
                    }
                    // END IF METHOD EXIST
                }
            }
        }

        $this->view->timeout = 4;
        $this->view->modal = false;
        $this->view->ResultSet = array('errors' => $msgs, 'entry' => $odata);
        $this->view->message = $resp[0];
        $this->view->status = $resp[1];

        if (is_object($gtp)) {
            if ($gtp->getProcess()->hasError()) {

                // rollback of insert action when gtp error
                if ($isNewEntry && $rowId) {
                    $rollBackObject = new $modelName;
                    $rollBackObject->delete('id = ' . $rowId, false);
                }

                $this->view->timeout = 6;
                $this->view->status = 0;
                $this->view->message = 'GTP ERROR: ' . $gtp->getProcess()->getErrorDescription();
                $this->view->ResultSet = array(
                    'errors' => 'GTP ERROR: ' . $gtp->getProcess()->getErrorDescription(),
                    'entry'  => $odata
                );
                $this->view->ResultSet['gtp']['error_code'] = $gtp->getProcess()->getError();
                $this->view->ResultSet['gtp']['error_description'] = $gtp->getProcess()->getErrorDescription();
            }
        }

        return $rowId;
    }
}

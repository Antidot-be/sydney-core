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
abstract class Sydney_Controller_Actionpublic extends Zend_Controller_Action
{
    /**
     * @var string
     */
    public $sydneyLayout = 'yes';
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
    protected $_db;
    /**
     * Javascript we will use
     * @var array
     */
    protected $jscripts = array(
        '/sydneyassets/scripts/sydneyscripts.js'
    );
    protected $jscriptsDev = array(
        '/admin/jscripts/index/vfname/sydneyscripts.js'
    );
    /**
     * Available modules
     * @var array
     */
    protected $availableModules = array();

    /**
     * PagstructureOp instance
     * @var PagstructureOp
     */
    public $structure;


    /**
     * Auto initialization of important params for sydney
     * @return void
     */
    public function init()
    {
        // register general sydney helpers
        $this->view->addHelperPath(Sydney_Tools_Paths::getCorePath() . '/library/Sydney/View/Helper', 'Sydney_View_Helper');
        $this->_initWebInstanceHelpers();
        // setup the basics
        $this->_registry = Zend_Registry::getInstance();
        $this->_config = $this->_registry->get('config');
        $this->_db = $this->_registry->get('db');
        $this->safinstancesId = $this->_config->db->safinstances_id;
        $this->_translate = $this->_registry->get('Zend_Translate');
        $this->view->translate = $this->_registry->get('Zend_Translate');
        $this->_auth = Sydney_Auth::getInstance();


        // Auto Login if identity and credentials stored in cookie
        $u = $this->getRequest()->getParam('username');
        $p = $this->getRequest()->getParam('password');
        if (!$this->_auth->hasIdentity() && $this->_getParam('action') != 'login' && $this->_getParam('action') != 'logout' && empty($u) && empty($p)) {
            $adapter = Sydney_Auth_Adaptater_DbTable::getAuthAdapter($this->getRequest());
            if ($adapter instanceof Zend_Auth_Adapter_Interface) {
                $auth = Sydney_Auth::getInstance();
                if ($auth->authenticate($adapter)->isValid()) {
                    $this->_helper->redirector->gotoUrl($this->getRequest()->getRequestUri());
                    exit;
                }
            }
        }

        // Init list secured pages
        $this->setAuthPagelist();

        // setup user
        $udata = new Zend_Session_Namespace('userdata');
        if (isset($udata->user)) {
            $this->usersData = $udata->user;
        }
        if (isset($this->usersData['users_id'])) {
            $this->usersId = $this->usersData['users_id'];
        }

        // sets some interesting vars in the view
        $this->view->config = $this->_config;
        $this->view->cdn = $this->_config->general->cdn;
        $this->view->users_data = $this->usersData;
        $this->view->safinstances_id = $this->safinstancesId;
        $this->view->auth = $this->_auth;
        $this->view->siteTitle = $this->_config->general->siteTitle;
        $this->view->printme = $this->_getParam('printme', 'no');

        // @todo TODO change this ...
        $llg = 'en';
        if (isset($this->_config->general->lang) && $this->_config->general->lang != '') {
            $llg = $this->_config->general->lang;
        }
        $this->view->headScript()->appendFile($this->view->cdn . '/sydneyassets/scripts/i18n/' . $llg . '.js', 'text/javascript');

        // setup some layout vars
        if ($this->layout !== null) {
            $this->layout->registry = $this->_registry;
            $this->layout->auth = $this->_auth;
            $this->layout->translate = $this->_registry->get('Zend_Translate');
            $this->layout->avmodules = $this->availableModules;
        }
        $this->view->moduleName = $this->_getParam('module');
        $this->view->controllerName = $this->_getParam('controller');
        $this->view->actionName = $this->_getParam('action');

        // set up the log
        $this->logger = new Sydney_Log();
        $this->logger->setEventItem('className', get_class($this));
        $this->logger->addFilterDatabase();

        if (isset($this->getRequest()->sydneylayout) && $this->getRequest()->sydneylayout == 'no') {
            $this->_helper->layout->disableLayout();
            $this->sydneyLayout = 'no';
        }
        if (isset($this->getRequest()->sydneylayout) && $this->getRequest()->sydneylayout != 'no' && $this->getRequest()->sydneylayout != 'yes') {
            $this->_helper->layout->setLayout('layout-' . $this->getRequest()->sydneylayout);
        }
        $this->view->sydneylayout = $this->sydneyLayout;

        /**
         * load structure if not exist
         */
        if (!is_array($this->view->structure) && $this->getRequest()->layout != 'no') {
            $this->structure = new Pagstructure();

            $this->view->adminmode = false;

            // if identified then get structure from database
            if (Sydney_Auth::getInstance()->hasIdentity()) {

                $this->structure->setFilter('status', 'published');
                $this->view->structure = $this->structure->toArray($this->safinstancesId);
            } else { // else use structure cached or build cache
                $cache = Zend_Registry::get('cache');
                $cn = PagstructureOp::getCacheNames($this->safinstancesId);
                $this->view->structure = $cache->load($cn[0]); //cn[0] > cachename
                $this->structure->stringNodes = $cache->load($cn[1]); //cn[1] > cachename2

                if (!is_array($this->view->structure)) {
                    $this->structure->setFilter('status', 'published');
                    $this->view->structure = $this->structure->toArray($this->safinstancesId);
                    $cache->save($this->view->structure, $cn[0]);
                    $cache->save($this->structure->getStringNodes(), $cn[1]);
                }
            }

            $r = $this->getRequest();
            if (isset($r->layout) && $r->layout == 'no') {
                Zend_Layout::getMvcInstance()->disableLayout(true);
            }
            $pages = $this->_getPageId();
            $this->view->breadCrumData = $this->structure->getBreadCrumData($this->safinstancesId, $pages[0]);
        }

        // change language if necessary
        $settingsNms = new Zend_Session_Namespace('appSettings');
        $curLang = $this->getCurrentLangCode();
        if ($settingsNms->ApplicationLanguage != $curLang) {
            $settingsNms->ApplicationLanguage = $curLang;
            $bootstrapper = Zend_Registry::get('bootstrapper');
            $bootstrapper->setRequestLang($curLang);
            $bootstrapper->setTranslationObject();
        }
        $this->view->lang = $settingsNms->ApplicationLanguage;

        $pages = (isset($pages)) ? $pages : $this->_getPageId();
        $this->_manageCanonicalLinks($pages[0]);
    }

    protected function _initWebInstanceHelpers()
    {
        $localPath = Sydney_Tools_Paths::getLocalPath();
        if($localPath){
            if (is_dir($localPath . '/layouts/helpers')) {
                $this->view->addHelperPath($localPath . '/layouts/helpers', 'Menu_View_Helper');
            }
            if(is_dir($localPath . '/library/helpers')){
                $this->view->addHelperPath($localPath . '/library/helpers', 'Helper');
            }
        }
    }

    /**
     * Manage canonanical
     * @param int $currentPageId
     */
    private function _manageCanonicalLinks($currentPageId)
    {
        $currentNode = $this->structure->stringNodes[$currentPageId];
        $this->view->alreadySetCanonicalLink = false;
        // JTO - 546 - Si on est sur la homepage
        if ($this->_isNodeHomepage($currentNode)) {
            $this->_addHomeRedirectionToCanonicalLink();
        }
        // JTO - 546 - 16/01/2014 - Ajout de la balise canonical pour les pages qui redirigent vers une autres
        $redirectNodeId = $currentNode['redirecttoid'];
        if (!empty($redirectNodeId)) {
            // On r�cup�re le noeud vers lequel on redirige
            $redirectNode = $this->_getNodeByNodeId($this->structure->stringNodes, $redirectNodeId);

            if ($this->_isNodeHomepage($redirectNode)) {
                $this->_addHomeRedirectionToCanonicalLink();
            } else {
                $viewHelp = new Sydney_View_Helper_SydneyUrl();
                $canonicalUrl = $viewHelp->SydneyUrl($redirectNodeId, $redirectNode['label']);
                $this->_addUrlToCanonicalLink($_SERVER['HTTP_HOST'] . $canonicalUrl);
            }
        }
    }

    /**
     * 546 - Ajoute � la 2�me homepage un lien canonical (si la page contient quelque chose dans l'url en plus du /)
     * @author JTO
     * @since 20/01/2014
     */
    private function _addHomeRedirectionToCanonicalLink()
    {
        $requestUri = trim($_SERVER['REQUEST_URI'], '/');
        if (strlen($requestUri) > 1) {
            $this->_addUrlToCanonicalLink($_SERVER['HTTP_HOST']);
        }
    }

    /**
     *
     * @author JTO
     * @since 20/01/2014
     * @param $url
     */
    private function _addUrlToCanonicalLink($url)
    {
        if (!$this->view->alreadySetCanonicalLink) {
            $this->view->alreadySetCanonicalLink = true;
            $this->view->headLink()->headLink(array(
                "rel" => "canonical",
                "href" => 'http://' . $url
            ), "PREPEND");
        }
    }

    /**
     * @author JTO
     * @since 17/01/2014
     * @param $node
     * @return bool
     */
    private function _isNodeHomepage($node)
    {
        return $node['ishome'] == 1;
    }

    /**
     * etourne un noeud en fonction de son ID
     * @since 17/01/2014
     * @param array $nodeList la liste des noeuds ou chercher
     * @param int $searchedNodeId l'id noeud dont on souhaite le label
     * @return string le label
     */
    private function _getNodeByNodeId($nodeList, $searchedNodeId)
    {
        if (isset($nodeList[$searchedNodeId])) {
            return $nodeList[$searchedNodeId];
        }

        return $this->_getNodeLabel($nodeList['kids'], $searchedNodeId);
    }

    /**
     *
     */
    protected function setAuthPagelist()
    {

        // Get list of pages secured by groups
        $listSecuredPages = array();
        $pages = new Pagstructure();
        $selector = $pages->select(false)->from($pages->getTableName(), array(
            'id',
            'usersgroups_id'
        ))
            ->where('safinstances_id = ?', Sydney_Tools::getSafinstancesId())
            ->where('usersgroups_id > ?', 1);
        $rowObjectList = $pages->fetchAll($selector);
        foreach ($rowObjectList as $rowObject) {
            $listSecuredPages[$rowObject->id] = $rowObject->usersgroups_id;
        }
        Zend_Registry::set('listSecuredPages', $listSecuredPages);
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
    protected function setSideBar($action = 'index', $controller = 'index')
    {
        $this->layout->sidebaraction = array($action, $controller);
    }

    /**
     * Manage the content of the meta keyword and description
     * for ALL the webinstances
     */
    protected function _manageMetaTags()
    {
        $metadesc = '';
        $metakeywords = '';
        // metadesc
        if (is_array($this->view->thisnode) && isset($this->view->thisnode['metadesc']) && $this->view->thisnode['metadesc'] != '') {
            $metadesc = $this->view->thisnode['metadesc'];
        }
        // metakeywords
        if (is_array($this->view->thisnode) && isset($this->view->thisnode['metakeywords']) && $this->view->thisnode['metakeywords'] != '') {
            $metakeywords = $this->view->thisnode['metakeywords'];
        }

        if (empty($metakeywords) || empty($metadesc)) {
            $saf = new Safinstances();
            $row = $saf->find($this->safinstancesId)->current();

            if (empty($metakeywords)) {
                $metakeywords = $row->metakeywords;
            }
            if (empty($metadesc)) {
                $metadesc = $row->description;
            }
        }
        $this->view->headMeta()->appendName('keywords', $metakeywords);
        $this->view->headMeta()->appendName('description', $metadesc);
    }

    /**
     *
     */
    protected function loadInstanceViewHelpers()
    {
        $localInstanceHelpersPath = Sydney_Tools_Paths::getLocalPath() . '/library/View/Helper';
        if (file_exists($localInstanceHelpersPath)) {
            $this->view->addHelperPath($localInstanceHelpersPath, 'Publicms_View_Helper');
        }
    }

    /**
     * Returns the ID of the home page for this SAF instance
     * @return int
     */
    protected function _getPageId()
    {
        $pages = array();
        $f = new Zend_Filter_Digits();
        if (isset($this->getRequest()->page)) {
            foreach (preg_split('/,/', $this->getRequest()->page) as $page) {
                $pages[] = $f->filter($page);
            }
        } else {
            $nodes = new Pagstructure();
            $pages[] = $nodes->getHomeId($this->safinstancesId);
        }

        return $pages;
    }

    /**
     * Returns the 2 letter lang code based on the structure (if the 1st nodes are langues '2 letters' )
     * or false if undefined
     * @returns array|false
     */
    public function getCurrentLangCode($andId = false)
    {
        // return the lang from the _GET param
        if (isset($_GET) && isset($_GET['slang']) && $_GET['slang'] != '' && in_array($_GET['slang'], Sydney_Tools_Localization::$authLanguages)) {
            return $_GET['slang'];
        }
        // or go the process to get lang from the top node in the structure
        if (is_array($this->view->breadCrumData) && count($this->view->breadCrumData) > 0 && isset($this->view->breadCrumData[0]) && isset($this->view->breadCrumData[0]['label'])) {
            $langcode = strtolower($this->view->breadCrumData[0]['label']);
            if (in_array($langcode, Sydney_Tools_Localization::$authLanguages)) {
                if (!$andId) {
                    return $langcode;
                } else {
                    return array(
                        $langcode,
                        $this->view->breadCrumData[0]['id']
                    );
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Sets a custom layout if any and check for the auto lang layout if applicable
     *
     * @param bool $page
     * @param string $layout Name of the layout we want to use (optional)
     */
    protected function _setCustomLayout($page = false, $layoutname = '')
    {
        $strnodes = $this->structure->stringNodes;
        $layout = Zend_Layout::getMvcInstance();
        if (
            isset($this->_config->general->layoutsopt)
            && $this->_config->general->layoutsopt != ''
            && $page !== false
            && isset($strnodes[$page])
            && isset($strnodes[$page]['layout'])
        ) {
            if ($strnodes[$page]['layout'] != '') {
                $layout->setLayout($strnodes[$page]['layout']);
            }
        }
        // use the current language version if layoutautolang
        if (
            isset($this->_config->general->layoutautolang)
            && $this->_config->general->layoutautolang == 'yes'
            && isset($this->_config->general->layoutsopt)
            && $this->_config->general->layoutsopt != ''
        ) {
            $settingsNms = new Zend_Session_Namespace('appSettings');
            $allLayouts = preg_split('/,/', $this->_config->general->layoutsopt);
            $layoutInLang = $layout->getLayout() . ucfirst($settingsNms->ApplicationLanguage);

            if (in_array($layoutInLang, $allLayouts)) {
                $layout->setLayout($layoutInLang);
            }
        }
        // use the layout passed as param
        if ($layoutname != '' && $layoutname != 'no' && strlen($layoutname) > 5) {
            $settingsNms = new Zend_Session_Namespace('appSettings');
            $allLayouts = preg_split('/,/', $this->_config->general->layoutsopt);
            if (in_array($layoutname, $allLayouts)) {
                $layout->setLayout($layoutname);
            }
        }

    }

    /**
     * Build an URL containing the module/controller/action from the params in the request.
     *
     * @param $paramArray The params in the requestion ($this->getRequest()->getParams());
     * @return string
     * @change JTO - Appel unifi� de la g�n�ration d'url
     */
    protected function _buildUrlFromParams($paramArray)
    {

        $helper = new Sydney_View_Helper_SydneyUrl();

        return (!empty($paramArray['page'])) ? $helper->SydneyUrl($paramArray['page'], $paramArray['slug']) : null;
    }

}

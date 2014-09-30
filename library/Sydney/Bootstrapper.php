<?php

/**
 * Classe Sydney_Bootstrapper
 */
class Sydney_Bootstrapper
{
    private $corePath;
    private $zendPath;
    private $webInstancePath;
    /**
     * @var Zend_Registry
     */
    private $registry;
    /**
     * @var Zend_Controller_Front
     */
    private $frontController;
    /**
     * @var Sydney_Auth
     */
    private $auth;
    private $requestLang;
    private $modules;
    private $customModules = array();
    private $cacheEnable = false;
    private $cacheParams = array();
    private $cache;
    private $config;
    private $debug = false;
    private $debugTranslationsNotFound = false;

    /**
     * Will contains an array of helper that user will want to use
     * @var array
     */
    private $customHelpers;

    /**
     * Constructor initializing the basics
     */
    public function __construct()
    {
        $this->customHelpers = new Sydney_View_Helper_ContentTypeCollection();
    }


    public function setInstancePath($path)
    {
        $this->webInstancePath = $path;
    }

    public function setCorePath($path)
    {
        $this->corePath = $path;
    }

    /**
     * Register an array of default helper
     */
    public function registerDefaultHelpers()
    {
       $this->customHelpers = Sydney_View_Helper_Config::getDefault();
    }

    public function setZendPath($path)
    {
        $this->zendPath = $path;
    }

    /**
     *
     * @param $identifier string unique identifier
     * @param $labelHelper string Will be display in the admin
     * @param $publicFuncToCall string Method call in the public part
     * @param $privateFuncToCall string Method call in the admin preview
     * @param $editorMethod string
     */
    public function registerContentTypeHelper($identifier, $labelHelper, $publicFuncToCall, $privateFuncToCall, $editorMethod)
    {
        $this->customHelpers->add($identifier, new Sydney_View_Helper_ContentType($labelHelper, $publicFuncToCall, $privateFuncToCall, $editorMethod));
    }

    private function _setConfigToRegistry($applicationEnv)
    {
        $configHandler = new Sydney_IniConfig($applicationEnv);
        $configHandler->addConfigFile($this->corePath . '/webinstances/sydney/config/config.default.ini');
        $configHandler->addConfigFile($this->corePath . '/webinstances/sydney/config/parameters.default.ini');
        $configHandler->addConfigFile($this->webInstancePath . '/config/config.ini');
        $configHandler->addConfigFile($this->webInstancePath . '/config/parameters.ini');
        $configHandler->addConfigFile($this->webInstancePath . '/config/instance.ini.lock');
        $this->registry->set('config', $configHandler->getConfig());
        $this->config = $this->registry->get('config');
    }


    /**
     * Sets the main paths and include paths
     * and sets the root path prop at the same time
     */
    private function _setPaths()
    {
        $includePath = get_include_path();

        // On va ajouter les dossiers library, models/Tables, models/Tables/Objects et modelsforms pour chaque module
        $modules = scandir($this->corePath . '/application/modules');
        foreach ($modules as $module) {
            $modulePath = $this->corePath . '/application/modules/' . $module;
            foreach (array(
                         '/library',
                         '/models/Tables',
                         '/models/Tables/Objects',
                         '/forms'
                     ) as $subFolder) {

                if (file_exists($modulePath . $subFolder)) {
                    $includePath .= PATH_SEPARATOR . $modulePath . $subFolder;
                }
            }
        }

        // specific ones for the webinstance - library, models, modelsform
        foreach (array(
                     '/library',
                     '/application/models/Tables',
                     '/application/models/Tables/Objects',
                     '/application/modelsforms'
                 ) as $v) {
            if (file_exists($this->webInstancePath . $v)) {
                $includePath .= PATH_SEPARATOR . $this->webInstancePath . $v;
            }
        }
        set_include_path($includePath);
        $this->registry->set('corePath', $this->corePath);
        $this->registry->set('instancePath', $this->webInstancePath);
        Sydney_Tools_Paths::setCorePath($this->corePath);
        Sydney_Tools_Paths::setWebInstancePath($this->webInstancePath);
    }

    private function _setRegistredHelpersToRegistry()
    {
        $this->registry->set('customhelpers', $this->customHelpers);
    }

    /**
     * Execute all the default method.
     * This is the quick mode to make the bootstrapper work.
     */
    public function run()
    {
        // Common initialisation
        $this->registry = Zend_Registry::getInstance();
        $this->_setConfigToRegistry((getenv('APPLICATION_ENV')) ? getenv('APPLICATION_ENV') : 'general');
        $this->_setPaths();
        $this->frontController = Zend_Controller_Front::getInstance();

        if (!Zend_Session::sessionExists()) {
            Zend_Session::start();
        }
        $this->auth = Sydney_Auth::getInstance();
        // set default timezone (could be useful)
        date_default_timezone_set($this->config->general->defaulttimezone);
        // set this in the registry so we can change the translations according to the page content if needed
        $this->registry->set('bootstrapper', $this);

        $this->setRoutes();
        $this->setErrorMode($this->config->general->env);
        $this->setDebugMode();
        $this->setCacheGlobalParams();
        $this->setLanguageSettings();
        $this->setTranslationObject();
        $this->setLocalization();
        $this->_setRegistredHelpersToRegistry();

        try {
            $this->setDatabaseConnection();
        } catch (Exception $e) {
            header('Location: ' . Sydney_Tools_Paths::getRootUrlCdn() . '/install/index.php');
            exit;
        }

        try {
            $this->initCustomModules();
        } catch (Exception $e) {
            echo 'ERROR initCustomModules', '<br>';
            echo $e->getMessage();
            header('Location: ' . Sydney_Tools_Paths::getRootUrlCdn() . '/install/index.php');
        }

        try {
            $this->registerAuthPlugin();
        } catch (Exception $e) {
            echo 'ERROR registerAuthPlugin', '<br>';
        }

        try {
            $this->initModules();
        } catch (Exception $e) {
            echo 'ERROR initModules', '<br>';
        }

        $this->setLayout();

        // set frontController plugins
        $this->registerFriendlyurlsPlugin();
        $this->registerCachePlugin();

        $this->dispatch();
    }

    /**
     * Routes for URL friendly transforamtion
     */
    public function setRoutes()
    {
        Sydney_Tools_Friendlyurls::setDefaultRoutes($this->frontController->getRouter());
    }

    /**
     * Sets the modules for this webinstance
     */
    public function setModules(array $modules)
    {
        $this->modules = $modules;
    }

    /**
     * Sets the custom modules contained in the webinstance only
     */
    public function setCustomModules(array $modules)
    {
        $this->customModules = $modules;
    }

    /**
     * Add custom tabs in the admin for custom module added within the bootstrap
     * @param String $title Title showed in the tab
     * @param Int $showInTab 0 or 1 should we show it in the tab menu
     * @param string $module Module name (on a ZF point of view)
     * @param string $icon path to the icon - from the cdn
     * @param string $divId ID of the content div in the admin
     */
    public function addCustomModulesAdminTabs($title, $module, $showInTab, $icon, $divId)
    {
        if ($this->registry->isRegistered('customavmodules')) {
            $customAvailableModules = $this->registry->get('customavmodules');
        } else {
            $customAvailableModules = array();
        }
        $customAvailableModules[$module] = array(
            $title,
            $showInTab,
            $module,
            $icon,
            $divId
        );
        $this->registry->set('customavmodules', $customAvailableModules);
    }

    /**
     * Sets the general caching method.
     * The object will also be in the registry so we can use it from anywhere
     * @return void
     */
    public function setCacheGlobalParams()
    {
        $this->cacheParams = array(
            'frontend'        => 'Core',
            'backend'         => 'File',
            'frontendOptions' => array(
                'lifetime'                => 60 * 60 * 24,
                'automatic_serialization' => true
            ),
            'backendOptions'  => array('cache_dir' => Sydney_Tools_Paths::getCachePath())
        );

        $this->cache = Zend_Cache::factory(
            $this->cacheParams['frontend'],
            $this->cacheParams['backend'],
            $this->cacheParams['frontendOptions'],
            $this->cacheParams['backendOptions']
        );
        $this->registry->set('cache', $this->cache);
    }

    /**
     * Sets the error mode for PHP
     * @param String $erm Could be DEV, TEST or PROD
     */
    public function setErrorMode($erm = 'DEV')
    {
        error_reporting(E_ALL & ~E_NOTICE);
        if ($erm != 'PROD') {
            ini_set('display_errors', 1);
        } else {
            ini_set('display_errors', 0);
        }
    }

    /**
     * Langue settings for the current session
     */
    public function setLanguageSettings()
    {
        $settingsNms = new Zend_Session_Namespace('appSettings');

        // Init Content language
        if (Sydney_Tools_Localization::isMultiLanguageContentActive()) {
            // Define Content language
            // set language to what we found in the $_GET['clang']
            if (isset($_GET['clang']) && in_array($_GET['clang'], Sydney_Tools_Localization::getContentLanguages())) {
                // set lang code in the session
                $settingsNms->ContentLanguage = $_GET['clang'];
                // Clear cache
                $cache = $this->registry->get('cache');
                $cache->clean(Zend_Cache::CLEANING_MODE_ALL);
            } elseif (!isset($settingsNms->ContentLanguage)) {
                // OR set language from the config file
                $settingsNms->ContentLanguage = Sydney_Tools_Localization::getDefaultContentLanguage();
            }
        }

        // set language to what we found in the $_GET['slang']
        if (isset($_GET['slang'])) {
            // set lang code in the session
            $settingsNms->ApplicationLanguage = $_GET['slang'];
            // no idea what that is
            if (isset($_GET['slangexit']) && $_GET['slangexit'] == 1) {
                exit();
            }
        } // OR set language from the config file
        elseif (!isset($settingsNms->ApplicationLanguage)) {
            $settingsNms->ApplicationLanguage = $this->config->general->lang;
        }

        // sets the lang from session to local var
        if (isset($settingsNms->ApplicationLanguage)) {
            $this->requestLang = $settingsNms->ApplicationLanguage;
        }
    }

    /**
     * Translation object setup
     */
    public function setTranslationObject()
    {
        // set language
        if (isset($this->requestLang)) {
            $lg = $this->requestLang;
        } else {
            $lg = $this->config->general->lang;
        }
        if ($lg == '' || !$lg) {
            $lg = 'en';
        }
        $this->registry->set('language', $lg);

        $translate = new Zend_Translate('csv', $this->corePath . '/application/language/en.csv', $this->config->general->lang);
        $translate->setLocale($this->config->general->locale);
        Zend_Validate_Abstract::setDefaultTranslator($translate);
        Zend_Form::setDefaultTranslator($translate);
        $this->registry->set('Zend_Translate', $translate);

        $path1 = $this->corePath . '/application/language/';
        $path2 = Sydney_Tools::getLocalPath() . '/application/language/';
        // general global translations
        if (is_dir($path1) && file_exists($path1 . $lg . '.csv')) {
            $translate->addTranslation($path1 . $lg . '.csv', $lg);
        }
        if (is_dir($path2) && file_exists($path2 . $lg . '.csv')) {
            $translate->addTranslation($path2 . $lg . '.csv', $lg);
        }

        // Create a log instance
        if ($this->debugTranslationsNotFound) {
            $writer = new Zend_Log_Writer_Stream(Sydney_Tools_Paths::getLogPath() . '/' . $lg . '-translation-notfound.log');
            $log = new Zend_Log($writer);
            // Attach it to the translation instance
            $translate->setOptions(array(
                'log'             => $log,
                'logUntranslated' => $this->config->general->env != 'PROD'
            ));
        }
    }

    /**
     * Localization (for date and formats)
     */
    public function setLocalization()
    {
        $locale = new Zend_Locale($this->config->general->locale);
        Zend_Locale::setDefault($this->config->general->locale);
        $this->registry->set('locale', $locale);
    }

    /**
     * Database setup
     */
    public function setDatabaseConnection()
    {
        $db = Zend_Db::factory($this->config->db);
        // define the DB format as UTF8
        $db->getConnection()->exec("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'");
        Zend_Db_Table::setDefaultAdapter($db);
        $this->registry->set('db', $db);
        if ($this->config->general->env != 'CMD') {
            /**
             * Cache the DB_Table metadata to speed up the requests
             */
            if ($this->cacheEnable) {
                Zend_Db_Table_Abstract::setDefaultMetadataCache($this->cache);
            }
        }
    }

    /**
     * Authentication and authorization plugin
     */
    public function registerAuthPlugin()
    {
        $mkey = 'ACLobject';
        $acl = $this->getCache($mkey);
        if (!$acl) {
            $acl = new Sydney_Acl($this->auth, $this->config->db->safinstances_id);
            if (is_array($this->customModules) && count($this->customModules) > 0) {
                $acl->addCustomModules($this->customModules);
            }
            $this->cache->save($acl, $mkey);
        }
        $this->registry->set('acl', $acl);
        $this->frontController->setParam('auth', $this->auth);
        $this->frontController->setParam('acl', $acl);
        $this->frontController->registerPlugin(new Sydney_Controller_Plugin_Auth($this->auth, $acl));

    }

    /**
     * Caching of the pages plugin for the controller
     * @return void
     */
    public function registerCachePlugin()
    {
        $this->frontController->registerPlugin(new Sydney_Controller_Plugin_Caching($this->cacheParams));
    }

    public function registerFriendlyurlsPlugin()
    {
        $this->frontController->registerPlugin(new Sydney_Controller_Plugin_Friendlyurls());
    }

    /**
     * Show debug info (to supress in prod)
     */
    public function setDebugMode()
    {
        if ($this->config->general->env != 'PROD') {
            if ($this->config->general->env != 'CMD' && is_object($this->frontController)) {
                $this->frontController->throwExceptions(true);
            }
            $this->debug = true;
        }
    }

    public function setRequestLang($lang)
    {
        $this->requestLang = $lang;
    }

    /**
     * Add modules
     */
    public function initModules()
    {
        $this->frontController->addModuleDirectory($this->corePath . '/application/modules');

        foreach ($this->modules as $module) {
            $this->frontController->addControllerDirectory($this->corePath . '/application/modules/' . $module . '/controllers', $module);
        }
        $this->frontController->setParam('useDefaultControllerAlways', true);
        $this->frontController->setBaseUrl($this->config->general->baseUrl);
        $this->registry->set('baseUrl', $this->config->general->baseUrl);
    }

    /**
     * Add modules
     */
    public function initCustomModules()
    {
        if (count($this->customModules) > 0) {
            $this->frontController->addModuleDirectory(Sydney_Tools_Paths::getCustomapPath() . '/modules');
            foreach ($this->customModules as $module => $role) {
                $this->frontController->addControllerDirectory(Sydney_Tools_Paths::getCustomapPath() . '/modules/' . $module . '/controllers', $module);
            }
        }
    }

    /**
     * Add the layout around the content (varies according to the ini file)
     */
    public function setLayout()
    {
        Zend_Layout::startMvc(array('layoutPath' => '../layouts'));
        $layout = Zend_Layout::getMvcInstance();
        $this->registry->set('layouttype', 'desktop');
        $layout->setLayout($this->config->general->layout);
    }

    /**
     * Do the MVC magic... Dispatch the frontcontroller
     */
    private function dispatch()
    {
        /**
         * Do the magic
         */
        try {
            $this->frontController->dispatch();
        } catch (Exception $e) {
            if ($this->config->general->env == 'DEV') {
                /**
                 * Custom error message if any
                 */
                $prms = $this->frontController->getRequest();
                if ($prms->format == 'json') {
                    $outp = array();
                    $outp['CaughtException'] = get_class($e);
                    $outp['message'] = $e->getMessage();
                    $outp['code'] = $e->getCode();
                    $outp['file'] = $e->getFile();
                    $outp['line'] = $e->getLine();
                    $outp['trace'] = $e->getTrace();
                    $outp['tracehtml'] = '';
                    foreach ($outp['trace'] as $le) {
                        $outp['tracehtml'] .= "" .
                            //"<b>File</b>: ...". substr($le['file'], -7) ." | ".
                            "<b>Line</b>: " . $le['line'] . " | " .
                            "<b>Function</b>: " . $le['function'] . " | " .
                            "<b>Class</b>: " . $le['class'] . " | " .
                            // "<b>Type</b>:". $le['type'] ." | ".
                            //"<b>Args: ". $le['args'] ."<br>\n";
                            "<br>";
                    }
                    print Zend_Json::encode($outp);

                } else {
                    $outp = '';
                    $outp .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html xmlns="http://www.w3.org/1999/xhtml">
					<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<title>APPLICATION ERROR</title>
					<link href="' . $this->config->general->cdn . '/sydneyassets/styles/main.css" rel="stylesheet" type="text/css" />
					</head><style>.errorbug { font-face: courrier; } th { text-align: left; background: #EEE; } </style><body>';
                    $outp .= '<div class="errorbug subBox"><h1>Sydney debug info</h1><ul>';
                    $outp .= "<li><b>Caught exception:</b> " . get_class($e) . "</li>\n";
                    $outp .= "<li><b>Message:</b> " . $e->getMessage() . "</li>\n";
                    $outp .= "<hr>\n";
                    $outp .= "<li><b>Code:</b> " . $e->getCode() . "</li>\n";
                    $outp .= "<li><b>File:</b> " . $e->getFile() . "</li>\n";
                    $outp .= "<li><b>Line:</b> " . $e->getLine() . "</li>\n";
                    $outp .= "<li><b>Trace:</b><table><tr><th class=\"errortrace\">" . implode('</td></tr><tr><th class="errortrace">', explode("\n", $e->getTraceAsString())) . "</td></tr></table>";
                    $outp .= '</ul></div>';
                    $outp .= '</body></html>';
                    print $this->_errologtreat($outp);
                }
            } else {
                print "APPLICATION ERROR... Please contact the technical support";
            }
        }
    }

    /**
     * @param $cachename
     * @return mixed
     */
    protected function getCache($cachename)
    {
        return $this->cache->load($cachename);
    }

    /**
     * @param $txt
     * @return mixed
     */
    protected function _errologtreat($txt)
    {
        foreach (array(
                     "/\/Applications\/MAMP/"               => "",
                     "/\/htdocs\/phplibs\//"                => "",
                     "/\/htdocs\/sydney/"                   => "",
                     "/ZendFramework-[0-9.]{1,10}-minimal/" => "",
                     "/\/core\/library\//"                  => '/library/',
                     "/\)\: /"                              => ')</th><td>',
                     "/library/"                            => '<span style="color:red;">library</span>',
                     "/Zend/"                               => '<span style="color:brown;">Zend</span>',
                     "/Db/"                                 => '<span style="color:brown;">Db</span>',
                     "/Statement/"                          => '<span style="color:brown;">Statement</span>',
                     "/Table/"                              => '<span style="color:brown;">Table</span>',
                     "/Adapter/"                            => '<span style="color:brown;">Adapter</span>',
                     "/Action/"                             => '<span style="color:brown;">Action</span>',
                     "/Pdo/"                                => '<span style="color:brown;">Pdo</span>',
                     "/Abstract/"                           => '<span style="color:brown;">Abstract</span>',
                     "/core/"                               => '<span style="color:green;">core</span>',
                     "/webinstances/"                       => '<span style="color:blue;">webinstance</span>',
                     "/Sydney/"                             => '<span style="color:orange;">Antidot</span>',
                     "/Bootstrapper/"                       => '<span style="color:orange;">Bootstrapper</span>',
                     "/\.php/"                              => '<span style="font-style:italic;font-weight:normal;">.php </span>',
                     "/Controller/"                         => '<span style="color:blue;">Controller</span>',
                     "/]: /"                                => ']:<br>',
                     "/{main}/"                             => '{main}</th><td>------------------------',

                 ) as $k => $v) {
            $txt = preg_replace($k, $v, $txt);
        }

        return $txt;
    }
}

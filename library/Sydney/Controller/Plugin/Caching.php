<?php

/**
 * Caching plugin
 *
 * @uses Zend_Controller_Plugin_Abstract
 */
class Sydney_Controller_Plugin_Caching extends Zend_Controller_Plugin_Abstract
{
    /**
     * @var bool Whether or not to disable caching
     */
    public static $doNotCache = false;

    /**
     * @var Zend_Cache_Frontend
     */
    public $cache;

    /**
     * @var string Cache key
     */
    public $key;

    /**
     * Constructor: initialize cache
     *
     * @param  array|Zend_Config $options
     * @return void
     * @throws Exception
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        if (!is_array($options)) {
            throw new Exception('Invalid cache options; must be array or Zend_Config object');
        }

        if (array(
                'frontend',
                'backend',
                'frontendOptions',
                'backendOptions'
            ) != array_keys($options)
        ) {
            throw new Exception('Invalid cache options provided');
        }

        $options['frontendOptions']['automatic_serialization'] = true;

        $this->cache = Zend_Cache::factory(
            $options['frontend'],
            $options['backend'],
            $options['frontendOptions'],
            $options['backendOptions']
        );
    }

    /**
     * Start caching
     *
     * Determine if we have a cache hit. If so, return the response; else,
     * start caching.
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $request = $this->getRequest();
        // $request = new Zend_Controller_Request_Http();
        $moduleName = $request->getModuleName();
        $params = $request->getParams();

        /**
         * On récupère la homeId uniquement si on arrive à la racine du site
         * @author GDE
         * @project Belgium Telecom
         * @since 10/01/2013
         */
        $pagstrDB = new Pagstructure();
        if (count($params) == 3 && $params['module'] == 'publicms' && $params['controller'] == 'index' && $params['action'] == 'view') {
            $params['page'] = $pagstrDB->getHomeId(Sydney_Tools_Sydneyglobals::getSafinstancesId());
        }

        if ($moduleName == 'publicms' && isset($params['page'])) {

            $pagstr = $pagstrDB->find($params['page']);
            if (count($pagstr) == 1) {
                /**
                 * Enregistre en session le rootid courant
                 * Utilisé pour savoir quel index de recherche utiliser
                 * FAR: Utile aussi pour la séparation par langue
                 * @author GDE
                 * @project Belgium Telecom
                 * @since 10/01/2013
                 */
                $breadCrumData = $pagstrDB->getBreadCrumData(Sydney_Tools_Sydneyglobals::getSafinstancesId(), $this->_treatPageData($params['page']));
                $rootid = $breadCrumData[0]['id'];
                $lucenesearch = new Zend_Session_Namespace('current-page-rootid');
                if (!empty($rootid)) {
                    $lucenesearch->rootid = $rootid;
                }

                $struct = $pagstr[0];
                $struct->hits++;
                $struct->save();
                if (!Sydney_Auth::getInstance()->hasIdentity() && $struct->iscachable != 0 && $struct->cachetime > 0) {
                    $this->cache->setLifetime($struct->cachetime);
                    if (!$request->isGet()) {
                        self::$doNotCache = true;

                        return;
                    }
                    $path = $request->getPathInfo();
                    $this->key = $moduleName . '_' . md5($path);
                    if (false !== ($response = $this->getCache())) {
                        $response->sendResponse();
                        exit;
                    }
                }
            }
        }
    }

    /**
     * @param $d
     * @return mixed
     */
    private function _treatPageData($d)
    {
        if (!preg_match('/,/', $d)) {
            return $d;
        } else {
            $m = preg_split('/,/', $d);

            return $m[0];
        }
    }

    /**
     * Store cache
     *
     * @return void
     */
    public function dispatchLoopShutdown()
    {
        if (self::$doNotCache
            || $this->getResponse()->isRedirect()
            || (null === $this->key)
        ) {
            return;
        }
        $this->cache->save($this->getResponse(), $this->key);
    }

    /**
     *
     * @return mixed
     */
    public function getCache()
    {
        if (($response = $this->cache->load($this->key)) != false) {
            return $response;
        }

        return false;
    }

}

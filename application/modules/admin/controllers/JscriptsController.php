<?php
/**
 * Controller admin Jscripts
 */

/**
 * Controller managing the JS concatenation and compression
 *
 * @package admin
 * @subpackage Controller
 * @author Arnaud Selvais
 * @since
 * @copyright Antidot Inc. / S.A.
 */
class Admin_JscriptsController extends Sydney_Controller_Action
{
    private $vfname;
    private $currentType;

    /**
     * (non-PHPdoc)
     * @see library/Sydney/Controller/Sydney_Controller_Action#init()
     */
    public function init()
    {
        parent::init();
        $this->_helper->layout->disableLayout();
    }

    /**
     *
     */
    public function indexAction()
    {
        $r = $this->getRequest();
        if (isset($r->vfname)) {
            $this->vfname = $r->vfname;
        }
        $ex = explode('.', $this->vfname);
        $this->currentType = $ex[1];

        if ($this->currentType == 'js') {
            $this->getResponse()->setHeader('Content-type', 'text/javascript');
        }
        if ($this->currentType == 'css') {
            $this->getResponse()->setHeader('Content-type', 'text/css');
        }

        $fomnameCache = $ex[0];
        $frontendOptions = array(
            'lifetime'                => 2000000,
            'automatic_serialization' => true
        );
        $backendOptions = array('cache_dir' => Sydney_Tools::getCachePath());
        $cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
        if (!$result = $cache->load($fomnameCache)) {
            $result = $this->_concatScripts();
            $cache->save($result, $fomnameCache);
        }
        $this->view->script = $result;
    }

    /**
     *
     */
    public function ckeditorAction()
    {
        // no dynamic specific content
        // check view
    }

    /**
     * Concatenates the JS files
     * @return string The js concatenated
     */
    private function _concatScripts()
    {
        return Sydney_Tools::concatScripts($this->currentType, $this->view, true);
    }
}

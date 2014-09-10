<?php
include_once('Sydney/Controller/Action.php');

/**
 *
 * @package Default
 * @subpackage Controller
 */
class IndexController extends Sydney_Controller_Action
{
    /**
     *
     */
    public function init()
    {
        parent::init();
        $reg = Zend_Registry::getInstance();
        $this->config = $reg->get('config');
        if (isset($this->config->general->defaultmodule)) {
            $this->defaultModule = $this->config->general->defaultmodule;
        } else {
            $this->defaultModule = 'admin';
        }
    }

    /**
     *
     */
    public function indexAction()
    {
        $this->view->headTitle('Authentication');
        $this->_helper->redirector('index', 'index', $this->defaultModule);
    }

}

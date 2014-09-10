<?php
/**
 * Controller Adminfiles Services
 */

/**
 *
 *
 * @package Adminfiles
 * @subpackage Controller
 * @author Arnaud Selvais
 * @since
 * @copyright Antidot Inc. / S.A.
 */
class Adminfiles_LinksController extends Sydney_Controller_Action
{
    /**
     * Defines the views types the actions should bring back
     * @var array
     */
    public $contexts = array();

    /**
     * Controller initialization
     */
    public function init()
    {
        parent::init();
        $this->getResponse()->setHeader("Cache-Control", "no-cache, must-revalidate");
        $this->_helper->contextSwitch()->initContext();
        $this->_helper->layout->disableLayout();
    }

    public function indexAction()
    {
        $this->view->links = Sydney_Search_Files_Links::getInstance()->getLinks($this->getRequest()->fileid);
    }

}

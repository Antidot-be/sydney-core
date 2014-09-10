<?php

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
class Adminsidebars_RecyclebinController extends Zend_Controller_Action
{
    /**
     * (non-PHPdoc)
     * @see Controller/Zend_Controller_Action::init()
     */
    public function init()
    {
        $reg = Zend_Registry::getInstance();
        $config = $reg->get('config');
        $this->view->cdn = $config->general->cdn;
    }

    /**
     * Shows the structure editor
     */
    public function indexAction()
    {
    }

    /**
     *
     */
    public function restoreAction()
    {
    }

    /**
     *
     */
    public function sidebarAction()
    {
    }

}

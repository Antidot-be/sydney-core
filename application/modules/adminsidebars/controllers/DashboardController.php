<?php
/**
 * Controller
 */

/**
 * Default controller
 *
 * @package Adminsidebars
 * @subpackage Controller
 * @author Arnaud Selvais <arnaud@antidot.com>
 * @since Mar 9, 2009
 * @copyright Antidot Inc. / S.A.
 */
class Adminsidebars_DashboardController extends Zend_Controller_Action
{
    public function init()
    {
        $reg = Zend_Registry::getInstance();
        $config = $reg->get('config');
        $this->view->cdn = $config->general->cdn;
    }

    /**
     *
     */
    public function indexAction()
    {
    }
}

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
class Adminsidebars_SafactivitylogController extends Zend_Controller_Action
{
    public function init()
    {
        parent::init();
        $this->getResponse()->setHeader("Cache-Control", "no-cache, must-revalidate");
        $this->_helper->contextSwitch()->initContext();
        $this->_helper->layout->disableLayout();
    }

    /**
     *
     */
    public function pagesAction()
    {
        $oLastEvents = new Safactivitylog();
        $this->view->lastEvents = $oLastEvents->getLastActivitiesForPage()->toObject();
    }

    /**
     *
     */
    public function contentpagesAction()
    {

        if ((int) $this->view->pagid == 0) {
            $this->view->pagid = $this->getRequest()->pagid;
        }

        $oLastEvents = new Safactivitylog();
        $this->view->lastEvents = $oLastEvents->getLastActivitiesForContentPage($this->view->pagid)->toObject();
        $this->render('pages');
    }

    /**
     *
     */
    public function newsAction()
    {
        $oLastEvents = new Safactivitylog();
        $this->view->lastEvents = $oLastEvents->getLastActivitiesForNews()->toObject();
        $this->render('pages');
    }

    /**
     *
     */
    public function contentnewsAction()
    {

        if ((int) $this->view->pagid == 0) {
            $this->view->pagid = $this->getRequest()->pagid;
        }

        $oLastEvents = new Safactivitylog();
        $this->view->lastEvents = $oLastEvents->getLastActivitiesForContentPage($this->view->pagid)->toObject();
        $this->render('pages');
    }

}

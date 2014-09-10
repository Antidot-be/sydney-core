<?php

/**
 *
 * @package Default
 * @subpackage Controller
 */
class ErrorController extends Sydney_Controller_Action
{

    public function init()
    {
        $this->view->titletxt = 'ERROR ! ';
    }

    /**
     * Handle errors
     *
     * @return void
     */
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler', false);
        $this->view->errorstrrr = 'Undefined application error';
        if (isset($this->getRequest()->error)) {
            $errors = new stdClass();
            $this->view->errorstrrr = $this->getRequest()->error;

            if ($this->getRequest()->error == 404) {
                $errors->type = Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION;
            }
            if ($this->getRequest()->error == 500) {
                $errors->type = Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER;
            }
        }
        if (!$errors) {
            // Unknown application error
            $this->view->errorstrrr = 'Unknown application error';

            return $this->render('500');
        }
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER :
                // Application (500) error
                $this->view->errorstrrr = 'EXCEPTION_NO_CONTROLLER';
                $this->render('500');
                break;
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION :
                // Page not found (404) error
                $this->view->errorstrrr = 'EXCEPTION_NO_ACTION';
                $this->render('404');
                break;
            default :
                // Application (500) error
                $this->view->errorstrrr = 'DEFAULT ERROR HANDELING';
                $this->render('500');
                break;
        }
    }
}

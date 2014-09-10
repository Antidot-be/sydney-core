<?php
/**
 * Controller Publicms Search
 */

/**
 * This will display the content from the CMS for the public part of the website
 *
 * @package Publicms
 * @subpackage Controller
 * @author Arnaud Selvais
 * @since
 * @copyright Antidot Inc. / S.A.
 */
class Publicms_ProfileController extends Sydney_Controller_Actionpublic
{

    /**
     * Defines the views types the actions should bring back
     * @var array
     */
    public $contexts = array(
        'processuser'  => array('json'),
        'filesindex'   => array('json'),
        'changeavatar' => array('json'),
        'searchfile'   => array('json'),
        'uploadfile'   => array('json'),
        'getftypes'    => array('json'),
    );

    /**
     * Init of the helpers for this controller. We are calling the parent init() first
     * @return
     */
    public function init()
    {
        parent::init();
        $this->_helper->contextSwitch()->initContext();
        $this->loadInstanceViewHelpers();

    }

    /**
     * Redirects to the right action
     */
    public function indexAction()
    {
        $usersData = Sydney_Tools::getUserdata();
        $usersId = $usersData['users_id'];

        if (empty($usersId)) {
            //$this->_helper->redirector->gotoUrl($url, $options);
            //$this->_forward('login','login','default');
            $this->setJs();
            //$this->_helper->actionStack('editindex','index','adminpeople',array('id'=>$users_id));
            // Set a custom form user on publicms if exist for the current instance
            //if ($this->view->moduleName == 'publicms' && @class_exists('UsersPublicForm'.Sydney_Tools::getSafinstancesId())) {
            $this->view->form = Users::getForm(null, false, true);
        } else {
            $this->setJs();

            $oUser = new Users();

            //$this->_helper->actionStack('editindex','index','adminpeople',array('id'=>$users_id));
            // Set a custom form user on publicms if exist for the current instance
            //if ($this->view->moduleName == 'publicms' && @class_exists('UsersPublicForm'.Sydney_Tools::getSafinstancesId())) {
            $this->view->form = Users::getForm($usersData['member_of_groups'], true, true);
            $this->view->form->populate($oUser->get($usersId)->toArray());
        }
    }

    /**
     * Redirects to the right action
     */
    public function createAction()
    {
        $this->forward('index');
    }

    public function processuserAction()
    {
        $data = $this->getRequest()->getPost();
        $objectUser = new Users();
        if (!$this->view->status = $objectUser->save($data, true)) {
            $msg = Sydney_Messages::getInstance()->getMessages();
            $this->view->message = 'Validation errors found...';
            if (!is_array($msg)) {
                $this->view->message .= ' (' . $msg . ')';
            }

            $this->view->ResultSet = array(
                'errors' => Sydney_Messages::getInstance()->getMessages(),
                'entry'  => array()
            );
        } else {

            // send email
            if (!$objectUser->isEditMode($data)) {
                $data['id'] = $objectUser->get()->id;
                $mailSubject = str_replace('[SITE_TITLE]', Sydney_Tools::getConf('general')->siteTitle, Sydney_Tools::_('subjectMailSubscribe'));
                $objectUser->registermail($objectUser->get()->login, $mailSubject, $data, $confirmationAction = '/publicms/profile/confirm/init/2', false, array(
                    'management/partialmailconfirm.phtml',
                    'publictimedex'
                ));
            }

            $this->view->message = Sydney_Messages::getInstance()->getMessages();
            $this->view->ResultSet = array(
                'errors' => Sydney_Messages::getInstance()->getMessages(),
                'entry'  => $objectUser->get()->toArray()
            );
        }

        $this->view->showtime = 3;
        $this->view->modal = false;
    }

    public function confirmAction()
    {
        $data = $this->getRequest()->getParams();
        $objectBypass = new Sydney_Auth_Bypass();

        $this->view->isConfirmed = false;
        if ($objectBypass->isValid($data['passid'], $data['pass'])) {
            $oUser = new Users();
            if ($oUser->confirm($data['passid'])) {
                $this->view->isConfirmed = true;
            }
        }
    }

    public function filesindexAction()
    {
        $this->_helper->actionStack('index', 'index', 'adminfiles');
    }

    public function changeavatarAction()
    {
        $this->_helper->actionStack('changeavatar', 'services', 'adminpeople');
    }

    public function thumbAction()
    {
        $this->_helper->actionStack('thumb', 'file', 'adminfiles');
    }

    public function searchfileAction()
    {
        $this->_helper->actionStack('searchfile', 'services', 'adminfiles');
    }

    public function uploadfileAction()
    {
        $this->_helper->actionStack('uploadfile', 'services', 'adminfiles');
    }

    public function getftypesAction()
    {
        $this->_helper->actionStack('getftypes', 'services', 'adminfiles');
    }

}

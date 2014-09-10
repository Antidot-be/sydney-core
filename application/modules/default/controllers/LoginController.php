<?php
include_once('Sydney/Controller/Action.php');
include_once('Sydney/Form.php');
include_once('Zend/Form/Element/Captcha.php');
include_once('Zend/Auth/Adapter/DbTable.php');

/**
 * This module can be used to manage the log in, log out and
 * public registration.
 *
 * @author arnaud selvais <arnaud@antidot.ca>
 * @package Default
 * @subpackage Controller
 */
class LoginController extends Sydney_Controller_Actionpublic
{

    public $jsonr = null;
    public $contexts = array(
        'jsonprocess' => array('json'),
    );

    /**
     * Initialize object, overrides the parent method
     * @return void
     */
    public function init()
    {
        if ($this->view->sydneylayout == 'no') {
            $this->sydneyLayout = 'no';
        }
        parent::init();
        $this->getResponse()->setHeader("Cache-Control", "no-cache, must-revalidate");
        $this->_helper->contextSwitch()->initContext();

        $this->view->config = $this->_config;

        // get the JSON data posted
        if (isset($this->getRequest()->jsonString)) {
            $this->jsonr = Zend_Json::decode($this->getRequest()->jsonString);
        }

        $this->view->headTitle('Authentication');
    }

    /**
     * Overrides the parent method
     * Pre-dispatch routines Called before action method. If using class with
     *
     * @return void
     */
    public function preDispatch()
    {
        if (!Sydney_Auth::getInstance()->hasIdentity()) {
            if ('logout' == $this->getRequest()->getActionName()) {
                $r = $this->getRequest();
                $this->_helper->redirector('index', 'index', $r->getParam('redirectmodule', 'index'));
            }
        }
    }

    /**
     * Default action
     * @return void
     */
    public function indexAction()
    {
        // Use layout members if existing
        if (file_exists(Zend_Layout::getMvcInstance()->getLayoutPath() . '/layout-members.phtml')) {
            Zend_Layout::getMvcInstance()->setLayout('layout-members');
        }

        $r = $this->getRequest();
        $this->view->adminmodule = (boolean) preg_match('/^\/admin/i', $r->getRequestUri());
        if (isset($r->errormessage)) {
            $this->view->errormessage = $r->errormessage;
        }
        $this->view->form = $this->getLoginForm();
    }

    /**
     * takes care of the default side bar
     * @return void
     */
    public function sidebarAction()
    {
        $this->_helper->layout->disableLayout();
    }

    /**
     * Default action
     * @return void
     */
    public function loginAction()
    {
        $this->view->form = $this->getLoginForm();
        $this->render('index');
    }

    /**
     * Displays the registration form
     * @return void
     */
    public function registerAction()
    {
        $this->view->form = $this->getRegistrationForm();
    }

    /**
     * Do the process of registration
     * @todo add a link to a safinstance if the user exists and he wants to register from another safinstance
     * @return void
     */
    public function registerprocessAction()
    {
        $request = $this->getRequest();

        // Check if we have a POST request
        if (!$request->isPost()) {
            return $this->_helper->redirector('login');
        } else {
            // Get our form and validate it
            $form = $this->getRegistrationForm();
            $this->view->form = $form;
            $params = $request->getPost();

            // check the form is valid
            if (!$form->isValid($request->getPost())) {
                return $this->render('register');
            } // check if both passwords match
            else if ($params['password'] != $params['password2']) {
                $this->view->form->setDescription($this->_translate->_('Both password do not match'));

                return $this->render('register');
            } // continue the registration process
            else {
                // check if the login doen't exist already
                $users = new Users();
                $rows = $users->fetchAll("login LIKE '" . ($params['username']) . "'");
                // user is not unique
                if (count($rows) > 0) {
                    $this->view->form->setDescription($this->_translate->_('This user exists already'));

                    return $this->render('register');
                } // register the user for this safinstance
                else {
                    // insert the new user in the table
                    $data = array(
                        'login'           => addslashes($params['username']),
                        'password'        => md5(addslashes($params['password'])),
                        'usersgroups_id'  => 2,
                        'email'           => addslashes($params['username']),
                        'active'          => 1,
                        'safinstances_id' => $this->_config->db->safinstances_id,
                        'subscribedate'   => date("Y-m-d H:i:s"),
                        'ip'              => $_SERVER['REMOTE_ADDR']
                    );
                    $uid = $users->insert($data);
                    // insert the link to the safinstance
                    $corDB = new SafinstancesUsers();
                    $row = $corDB->createRow();
                    $row->safinstances_id = $this->_config->db->safinstances_id;
                    $row->users_id = $uid;
                    $row->save();

                    // process login with the information provided
                    $adapter = $this->getAuthAdapter($request);
                    $auth = Sydney_Auth::getInstance();
                    $result = $auth->authenticate($adapter);
                    if ($result->isValid()) {
                        $r = $this->getRequest();
                        if (isset($r->redirectmodule)) {
                            $this->_helper->redirector('index', 'index', $r->getParam('redirectmodule', 'index'));
                        } elseif ($r->redirectpage) {
                            $this->_helper->redirector('view', 'index', 'publicms', array('page' => $r->redirectpage));
                        } else {
                            $this->_helper->redirector('index', 'index', $r->getParam('redirectmodule', 'index'));
                        }
                        // $this->logger->log('New user registered', Zend_Log::WARN);
                        // return $this->render('register');
                    } else {
                        $this->view->form->setDescription($this->_translate->_('An unexpected error occured... please contact the support.'));

                        return $this->render('register');
                    }
                }

            }
        }

    }

    /**
     * Processes the log out action
     * @return void
     */
    public function logoutAction()
    {
        // Clean cookie
        Sydney_Http_Cookie::cleanAuthCookie();

        Sydney_Auth::getInstance()->clearIdentity();
        Zend_Session::forgetMe();
        Zend_Session::destroy();
        $this->logger->log('User logged OUT', Zend_Log::NOTICE);

        $this->redirect('/');
    }

    /**
     * Processes the login action
     * @todo Add a check to make sure the user can access the current safinstance
     */
    public function processAction()
    {
        $request = $this->getRequest();

        // Check if we have a POST request
        if (!$request->isPost()) {
            return $this->_helper->redirector('login');
        }

        // Get our form and validate it
        $data = $request->getPost();
        $form = $this->getLoginForm(!empty($data['new_password']));
        // add special validator for password
        if (!empty($data['new_password']) || !empty($data['confirm_new_password'])) {
            $form->getElement('confirm_new_password')->addValidator(new Zend_Validate_Identical($data['new_password']));
            $form->getElement('new_password')->addValidator(new Zend_Validate_Identical($data['confirm_new_password']));
        }

        if (!$form->isValid($data)) {
            if (substr($request->redirectmodule, 0, 5) == 'admin') {
                $this->setLayoutLoginAdmin();
            }
            // Invalid entries
            $this->view->form = $form;

            return $this->render('index'); // re-render the login form
        }

        // Get our authentication adapter and check credentials
        $params = $form->getValues();
        $adapter = $this->getAuthAdapter($request);
        $auth = Sydney_Auth::getInstance();
        $result = $auth->authenticate($adapter);

        // ERROR authentification
        if (!$result->isValid()) {
            // User must change password - depend of authenticate result
            if (in_array('change_password', $result->getMessages())) {
                // Init
                $canUpdateUser = false;
                // Check the validity of the new password
                if (!empty($data['new_password']) && !empty($data['confirm_new_password'])) {
                    // get datas of user authenticated
                    $dataUser = $adapter->getResultRowObject();
                    $oUser = new Users($dataUser->id);
                    if ($oUser->isValidPassword($data['new_password'])) {
                        $canUpdateUser = true;
                    }
                }

                // Save new password
                if ($canUpdateUser) {
                    // Change password
                    $oUser->changePassword($data['new_password']);
                    // Authenticate user with new password
                    $auth->authenticate($this->getAuthAdapter($request->setParam('password', $data['new_password'])));
                } else {
                    $this->view->form = $this->getLoginForm(true);
                    $this->view->form->setDescription($this->_translate->_('Please use another password!'));
                    $this->view->form->populate($data);

                    return $this->render('index'); // re-render the login form
                }
            } else {
                // Invalid credentials
                $form->setDescription($this->_translate->_('Invalid credentials provided'));
                $this->logger->log('User failed to log in with login ' . $params['username'], Zend_Log::WARN);
                $this->view->form = $form;

                if (substr($request->redirectmodule, 0, 5) == 'admin') {
                    $this->setLayoutLoginAdmin();
                }

                return $this->render('index'); // re-render the login form
            }


        }

        // We're authenticated! Redirect to the home page
        $this->logger->log('User LOGGED IN with login ' . $params['username'], Zend_Log::WARN);

        // Redirect
        if (isset($request->redirectmodule)) {
            $this->_helper->redirector('index', 'index', $request->getParam('redirectmodule', 'index'));
        } elseif ($request->redirectpage) {
            $this->_helper->redirector('view', 'index', 'publicms', array('page' => $request->redirectpage));
        } else {
            $this->_helper->redirector('index', 'index', $request->getParam('redirectmodule', 'index'));
        }
    }

    /**
     * Authentication with JSON
     * @return void
     */
    public function jsonprocessAction()
    {

        if (is_array($this->jsonr) && isset($this->jsonr['login']) && isset($this->jsonr['password'])) {

            $adapter = $this->getAuthAdapter($this->getRequest(), true);
            $auth = Sydney_Auth::getInstance();
            $result = $auth->authenticate($adapter);
            if (!$result->isValid()) {
                $this->view->ResultSet = $this->getEmptyArray(0, 0, 1, 'Error! Authentication failed.', 1);
            } else {
                $this->view->ResultSet = $this->view->ResultSet = $this->getEmptyArray(0, 0, 0, 'Authentication successful.', 0);
            }
        } else {
            $this->view->ResultSet = $this->getEmptyArray(0, 0, 1, 'Error! Authentication failed.', 1);
        }
    }

    private function setLayoutLoginAdmin()
    {
        $this->view->headLink()->appendStylesheet($this->view->cdn . '/sydneyassets/styles/reset.css');
        $this->view->headLink()->appendStylesheet($this->view->cdn . '/sydneyassets/styles/main.css');
        $this->view->headLink()->appendStylesheet($this->view->cdn . '/sydneyassets/styles/antidot.css');
        $this->view->headLink()->appendStylesheet($this->view->cdn . '/sydneyassets/styles/antidotprint.css', 'print');
        $this->view->headLink()->appendStylesheet('/sydneyassets/jslibs/jquery/css/smoothness/jquery-ui-1.10.4.min.css');
        $this->view->headScript()->appendFile(Sydney_Tools::getRootUrlCdn() . '/sydneyassets/scripts/sydneyscripts.js', 'text/javascript');
        $this->_helper->layout->setLayoutPath(Sydney_Tools::getRootPath() . '/core/webinstances/sydney/layouts');
        $this->_helper->layout->setLayout('login');
    }

    /**
     * Returns the login form
     * @return Sydney_Form
     */
    private function getLoginForm($renewalPassword = false)
    {
        $r = $this->getRequest();

        if (isset($r->redirectmodule)) {
            $action = '/default/login/process/redirectmodule/' . $r->getParam('redirectmodule', 'admindashboard');
        } elseif (isset($r->redirectpage)) {
            $action = '/default/login/process/redirectpage/' . $r->redirectpage;
        } elseif (substr($r->module, 0, 5) == 'admin') {
            $module = $r->module;
            if ('admin' == $module) {
                $module = 'admindashboard';
            }
            $this->setLayoutLoginAdmin();
            $action = '/default/login/process/redirectmodule/' . $module;
        } elseif (null != $r->getParam('page', null)) {
            $action = '/default/login/process/redirectpage/' . $r->getParam('page');
        } else {
            $action = '/default/login/process/';
        }

        $config = new Zend_Config_Ini(__DIR__ . '/../config/default.login.index.ini', 'loginform');
        $form = new Sydney_Form($config);
        $form->setAction($action);

        // Set decorator to checkbox element 'Remember Me'
        $form->addPrefixPath('Sydney_Decorator', 'Sydney/Decorator/', 'decorator');
        $form->addElementPrefixPath('Sydney_Decorator', 'Sydney/Decorator/', 'decorator');
        $form->getElement('rememberme')->setDecorators(
            array(
                'CheckboxloginDecorator'
            )
        );
        /**/

        // Check concurrent access
        // if params MaxLogin exist then check the number of session
        $this->view->maxLoginExceeded = false;

        if ($renewalPassword) {
            $form->setDescription(Sydney_Tools::_('Your password has expired. Please change it!'));

            // clone submit
            $submit = clone $form->getElement('submit');
            $form->removeElement('submit');

            // clone remember me
            $rememberme = clone $form->getElement('rememberme');
            $form->removeElement('rememberme');

            // clone password
            $newPassword = clone $form->getElement('password');
            $newPassword->setName('new_password');
            $newPassword->setLabel('New password');

            // clone password2
            $confirmNewPassword = clone $form->getElement('password');
            $confirmNewPassword->setName('confirm_new_password');
            $confirmNewPassword->setLabel('Confirm new password');

            $form->addElements(array(
                $newPassword,
                $confirmNewPassword,
                $rememberme,
                $submit
            ));
        }

        return $form;
    }

    /**
     *
     * @return Sydney_Form
     */
    private function getLostpassForm()
    {
        // TODO check if we need a redirect page method here... or go to the same page at least...
        $action = '/default/login/lostpassword/';
        $config = new Zend_Config_Ini(__DIR__ . '/../config/default.login.index.ini', 'lostpassform');
        $form = new Sydney_Form($config);
        $form->setAction($action);

        return $form;
    }

    /**
     * Returns the registration form
     * @return Sydney_Form
     */
    private function getRegistrationForm()
    {
        $r = $this->getRequest();

        if (isset($r->redirectmodule)) {
            $action = '/default/login/registerprocess/redirectmodule/' . $r->getParam('redirectmodule', 'admindashboard');
        } elseif (isset($r->redirectpage)) {
            $action = '/default/login/registerprocess/redirectpage/' . $r->redirectpage;
        } else {
            $action = '/default/login/registerprocess/';
        }

        $config = new Zend_Config_Ini(__DIR__ . '/../config/default.login.index.ini', 'register');
        $form = new Sydney_Form($config);

        if (isset($this->_config->register->showlegalagreement) && $this->_config->register->showlegalagreement == 'yes') {
            $form->addElement('radio', 'legalnotes', array(
                'required'     => true,
                'order'        => 5,
                'label'        => 'Do you accept our legal agreement ?',
                'text'         => 'hello man!',
                'validators'   => array(
                    'NotEmpty',
                    array('Identical', false, 'yes')
                ),
                'multioptions' => array('yes' => 'Yes', 'no' => 'No')
            ));
        }
        $form->setAction($action);

        return $form;
    }

    /**
     *
     * @return void
     */
    public function lostpasswordAction()
    {
        $form = $this->getLostpassForm();
        $request = $this->getRequest();
        $this->view->showform = true;
        // Check if we have a POST request
        if ($request->isPost() && !$form->isValid($request->getPost())) {
            // Invalid entries
            $this->view->form = $form;
        }
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $this->view->showform = false;
            $usrDB = new Users();
            $user = $usrDB->fetchRow("login LIKE '" . addslashes($request->username)
                . "' AND safinstances_id = '" . $this->safinstancesId . "' ");

            if ($user) {
                $strl = 'qwertyuiopasdfghjklzxcvbnm12345678902@#$!';
                $strll = strlen($strl);
                $npwd = '';
                for ($i = 0; $i <= 8; $i++) {
                    $rdd = rand(0, ($strll - 1));
                    $npwd .= $strl[$rdd];
                }
                $user->password = md5($npwd);
                $user->lastpwdchanges = Sydney_Tools::getMySQLFormatedDate();
                $user->save();
                // send the email
                $tmsg = "Dear user,

Your password has been modified as requested.
You will now be able to use the following credentials:

login: " . $user->login . "
password: " . $npwd . "

We suggest you change your password as soon as possible for security reason.
If you did not request a password change, please contact our support.

Regards,
" . $this->_config->general->siteTitle . " team.

";
                $mail = new Zend_Mail();
                $mail->setBodyText($tmsg);
                $mail->setFrom($this->_config->general->siteEmail, $this->_config->general->siteTitle);
                $mail->addTo($user->login, $user->login);
                $mail->setSubject($this->_config->general->siteTitle . ' new password.');
                $mail->send();

                $this->view->mmsg = /*nl2br($tmsg). !!! WTF !!! */
                    'Thank you! Your new password has been sent to your email. Please check your email and use this new password for authentication.';
            } else {

                $this->view->mmsg = 'We could not find this user in our database for this website... Are you sure you are registered?';
            }

        }
        $this->view->form = $form;
    }

    /**
     *
     * @todo Change the $dbAdapter for making it automatic according to the config, here we are stuck with MySQL
     * @param $params
     * @return Zend_Auth_Adapter_DbTable
     */
    private function getAuthAdapter(Zend_Controller_Request_Http $request, $encryptedPass = false)
    {
        return Sydney_Auth_Adaptater_DbTable::getAuthAdapter($request, $encryptedPass);
    }

    /**
     * This method returns an empty array maching the YUI standard for JSON response.
     *
     * @param $totalResultsAvailable Total result available after a request
     * @param $totalResultsReturned Total result returned
     * @param $firstResultPosition Position of the first result found in the Result array
     * @param $message Message to send which will be shown as a feedback in the player
     * @param $playertype The mode to be used by the player (video, audio, image, text, ...)
     * @param $result Array containing all the records we need to return
     * @return Array
     */
    private function getEmptyArray($totalResultsAvailable = 0,
                                   $totalResultsReturned = 0,
                                   $firstResultPosition = 1,
                                   $message = null,
                                   $errorCode = 0,
                                   $result = array())
    {
        return array(
            'totalResultsAvailable' => $totalResultsAvailable,
            'totalResultsReturned'  => $totalResultsReturned,
            'firstResultPosition'   => $firstResultPosition,
            'message'               => $message,
            'errorcode'             => $errorCode,
            'Result'                => $result
        );
    }
}

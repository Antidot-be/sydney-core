<?php
include_once('Zend/Controller/Plugin/Abstract.php');
include_once('Users.php');

/**
 * Zend controller plugin for checking the access rights and authenticate.
 *
 * @package AntidotLibrary
 * @subpackage ControllerPlugin
 */
class Sydney_Controller_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{
    protected $_auth = null;
    protected $_acl = null;
    protected $_registry = null;
    protected $_config = null;
    protected $request;
    protected $userNamespace;
    protected $safinstancesId;

    /**
     *
     * @param $auth
     * @param $acl
     * @return unknown_type
     */
    public function __construct(Zend_Auth $auth, Zend_Acl $acl)
    {
        $this->_auth = $auth;
        $this->_acl = $acl;
        $this->_registry = Zend_Registry::getInstance();
        $this->_config = $this->_registry->get('config');
        $this->safinstancesId = $this->_config->db->safinstances_id;
        $this->logger = new Sydney_Log();
        $this->logger->setEventItem('className', get_class($this));
        $this->logger->addFilterDatabase();
    }

    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Plugin_Abstract::preDispatch()
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->request = $request;
        $this->userNamespace = new Zend_Session_Namespace('userdata');
        if ($this->_auth->hasIdentity()) {
            if ($this->_registry->isRegistered('userGroup')) {
                $role = $this->_registry->get('userGroup');
            } else {
                if (!$role = $this->getGroupName($this->_auth->getIdentity())) {
                    $role = 'guest';
                }
                $this->_registry->set('userGroup', $role);
            }
        } else {
            $role = 'guest';
        }

        if ($this->checkRights($role)) {
            if (!$this->grantAccessRights()) {
                $this->redirecting('default', 'login', 'index', 'code01');
            }
        }

    }

    /**
     * Check the access rights agains the ACL or the DB info
     * @param $role
     * @return bool
     */
    private function checkRights($role)
    {
        $resource = $this->request->module;
        if ($resource == 'publicms') {
            $this->checkRightsPublicms($role);
        } else {
            if (!$this->_acl->has($resource)) {
                $resource = null;
            }
            if (!$this->_acl->isAllowed($role, $resource)) {
                $this->logger->log("User tried to access an unauthorized ressource. Role:$role, Ressource:$resource", Zend_Log::CRIT);
                if ($this->_auth->hasIdentity()) {
                    $this->redirecting('default', 'index', 'index', 'code02');
                } else {
                    $this->redirecting('default', 'login', 'index', 'code03');
                }

                return false;
            }
            // else return true;
        }

        // check if password must be changed
        // if yes, check if time is expired
        return true;
    }

    /**
     * Checks access right against the config in the DB (see adminaccess for detail)
     */
    private function grantAccessRights()
    {
        if ($this->_config->accessrightsenabled == 'yes') {
            Sydney_Accessright::$request = $this->request;

            return Sydney_Accessright::isAuthorized();
        } else {
            return true;
        }
    }

    /**
     * Check the access rights agains the DB if we are in the publicms module
     *
     * @param $role
     * @return void
     */
    private function checkRightsPublicms($role)
    {
        // check if instance is offline
        $safinstanceDB = new Safinstances();
        $safinstances = $safinstanceDB->find($this->safinstancesId);

        if (count($safinstances) != 1) {
            print "FATAL ERROR 452 in Sydney_Controller_Plugin_Auth::checkRightsPublicms(" . $this->safinstancesId . ")";
            header('Location: ' . Sydney_Tools_Paths::getRootUrlCdn() . '/install/instance/index.php/referrer/PluginAuth/checkRightsPublicms/noinstancefound');
            exit;
        } elseif ($safinstances[0]->active == 0) {
            print $safinstances[0]->offlinemessage;
            if (empty($safinstances[0]->offlinemessage)) {
                print "This site is offline.";
                header('Location: ' . Sydney_Tools_Paths::getRootUrlCdn() . '/install/instance/index.php/referrer/PluginAuth/checkRightsPublicms');
            }
            exit;
        }

        // get page data
        $d = $this->request->getParams();
        if ($d['module'] == 'publicms' && $d['controller'] == 'index' && $d['action'] == 'view') {
            $nodes = new Pagstructure();
            if (!isset($d['page']) || !preg_match("/^[0-9]{1,100}$/", $d['page'])) {
                $nodeId = $nodes->getHomeId($this->safinstancesId);
            } else {
                $nodeId = $d['page'];
            }
            $node = $nodes->fetchAll(" id = '" . $nodeId . "' AND safinstances_id = '" . $this->safinstancesId . "' ");
            if (count($node) == 1) {
                $authorizedGroupId = $node[0]->usersgroups_id;
                if (!self::isContentAccessible($authorizedGroupId, $this->userNamespace->user['member_of_groups'])) {
                    $this->redirecting('default', 'login', 'index', 'code04');
                }
            } else {
                print "Node $nodeId not found! (FATAL ERROR 542 in Sydney_Controller_Plugin_Auth::checkRightsPublicms)";
                header('Location: ' . Sydney_Tools_Paths::getRootUrlCdn() . '/install/instance/index.php/referrer/PluginAuth/checkRightsPublicms/nodenotfound');
                exit;
            }
        }
    }

    /**
     *
     * @param unknown_type $nodeUsergroupId
     * @param unknown_type $userMemberOfGroups
     */
    public static function isContentAccessible($nodeUserGroupId, $userMemberOfGroups)
    {
        // authorize all access if the group is none or guest
        if ($nodeUserGroupId != 0 && $nodeUserGroupId != 1) {
            if (isset($userMemberOfGroups) && is_array($userMemberOfGroups)
                && in_array($nodeUserGroupId, $userMemberOfGroups)
            ) {
                // is authorized
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * Redirect to a specific module/controller/action
     * @param $module
     * @param $controller
     * @param $action
     * @return void
     */
    protected function redirecting($module, $controller, $action, $code = '')
    {
        $this->request->setModuleName('default');
        $this->request->setControllerName('login');
        $this->request->setActionName('index');
        $this->request->setParams(array('errormessage' => 'Vous tentez d\'accéder une page protégée, veuillez vous connecter! <!-- ' . $code . ' -->'));
    }

    /**
     * Returns the group of the user and put the user's data in the session
     * @param $login
     * @return string
     */
    private function getGroupName($login)
    {
        if (!isset($this->userNamespace->user)) {
            $users = new Users();
            $select = $users->select()->setIntegrityCheck(false)
                ->from($users, array(
                    'users_id'         => 'users.id',
                    'login'            => 'users.login',
                    'usersgroups_name' => 'usersgroups.name',
                    'usersgroups_id'   => 'usersgroups.id',
                    'fname'            => 'users.fname',
                    'lname'            => 'users.lname',
                    'email'            => 'users.email',
                    'usersgroups_id'   => 'users.usersgroups_id'
                ))
                ->where(' users.login LIKE ?', $login)
                ->join('usersgroups', 'users.usersgroups_id = usersgroups.id');
            $rows = $users->fetchAll($select);
            $row = $rows->current();

            // save the last login time
            $usrDB = new Users();
            $urow = $usrDB->fetchRow("id = '" . $row->users_id . "'");
            $urow->lastlogindate = Sydney_Tools::getMySQLFormatedDate();
            $urow->save();

            $this->userNamespace->user = $row->toArray();
            // define all the groups this user is part of
            $groupsDB = new Usersgroups();
            $this->userNamespace->user['member_of_groups'] = $groupsDB->getParentsIds($this->userNamespace->user['usersgroups_id']);

            $this->userNamespace->lock();

            return $row->usersgroups_name;
        } else {
            return $this->userNamespace->user['usersgroups_name'];
        }

    }
}

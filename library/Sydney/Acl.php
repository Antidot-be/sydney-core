<?php
include_once('Zend/Acl/Role.php');
include_once('Zend/Acl/Resource.php');
include_once('Zend/Auth.php');
include_once('Zend/Acl.php');

/**
 *
 * @package AntidotLibrary
 * @subpackage Acl
 */
class Sydney_Acl extends Zend_Acl
{
    private $safinstancesId = 0;
    private $modulesDB;
    private $rolesDB;
    private $roles;
    private $debug = false;

    /**
     * Constructs the access control list
     * @param Zend_Auth $auth
     */
    public function __construct(Zend_Auth $auth, $safinstancesId)
    {
        $this->safinstancesId = $safinstancesId;
        $this->modulesDB = new Safmodules();
        $this->rolesDB = new Usersgroups();
        $this->roles = $this->rolesDB->fetchAlltoFlatArray();
        //unset($this->roles[0]);
        // Add Resources
        $this->_addResources();
        // Add Roles
        $this->_addRoles();
        // Assign Access Rules
        $this->_allowAccess();
    }

    /**
     * Sets additional modules and roles
     * @param Array $a Set of modules and roles
     */
    public function addCustomModules($a)
    {
        foreach ($a as $module => $role) {
            $this->add(new Zend_Acl_Resource($module));
            $this->allow($role, $module);
        }
    }

    /**
     *
     */
    protected function _addResources()
    {
        foreach ($this->_getModules() as $module) {
            $this->add(new Zend_Acl_Resource($module));
        }
    }

    /**
     *
     */
    protected function _addRoles()
    {
        foreach ($this->_getRolesStructure() as $role => $parentRole) {
            $this->addRole(new Zend_Acl_Role($role), $parentRole);
        }
    }

    /**
     *
     */
    protected function _allowAccess()
    {
        foreach ($this->_getRolesToModules() as $role => $modules) {
            foreach ($modules as $module) {
                $this->allow($role, $module);
            }
        }
    }

    /**
     *
     * @return Array
     */
    protected function _getModules()
    {
        $m = $this->modulesDB->getAvailableModules($this->safinstancesId);
        if ($this->debug) {
            Zend_Debug::dump($m);
        }

        return $m;
    }

    /**
     *
     * @return Array
     */
    protected function _getRolesStructure()
    {
        $rolesStructure = array();
        foreach ($this->roles as $r) {
            $v = $this->roles[($r['parent_id'])]['name'];
            if ($r['parent_id'] == 1 && $r['id'] == 1) {
                $v = null;
            }
            $rolesStructure[($r['name'])] = $v;
        }
        if ($this->debug) {
            Zend_Debug::dump($rolesStructure);
        }

        return $rolesStructure;
    }

    /**
     *
     */
    protected function _getRolesToModules()
    {
        $rolesToModules = array();
        $modules = $this->modulesDB->getAvailableModules($this->safinstancesId, false);
        foreach ($this->roles as $r) {
            $rolesToModules[($r['name'])] = array();
            foreach ($modules as $m) {
                if ($m['usersgroups_id'] == $r['id']) {
                    $rolesToModules[($r['name'])][] = $m['name'];
                }
            }
        }
        if ($this->debug) {
            Zend_Debug::dump($rolesToModules);
        }

        return $rolesToModules;
    }

}

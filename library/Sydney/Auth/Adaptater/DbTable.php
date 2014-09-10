<?php

/**
 * This abstract class should be the parent of every controller of the sydney admin interface.
 * It takes care of some usefull global variables (for the view and the controller).
 *
 * @author Arnaud Selvais
 * @package SydneyLibrary
 * @subpackage Controller
 */
class Sydney_Auth_Adaptater_DbTable extends Zend_Auth_Adapter_DbTable
{
    /**
     * @todo Change the $dbAdapter for making it automatic according to the config, here we are stuck with MySQL
     * @param Zend_Controller_Request_Http $request
     * @param bool $encryptedPass
     * @return bool|Sydney_Auth_Adaptater_DbTable
     */
    public static function getAuthAdapter(Zend_Controller_Request_Http $request, $encryptedPass = false)
    {

        // Load cookie informations
        $identity = Sydney_Http_Cookie::getIdentity($request);
        $credential = Sydney_Http_Cookie::getCredential($request);

        // Load params
        $params = $request->getParams();

        // Auth with identity and credential loaded from cookie
        if (empty($identity) && empty($credential) && empty($params['username']) && empty($params['password'])) { // IF no username and no password then return false

            return false;
        } elseif (!empty($identity) && !empty($credential) && empty($params['username']) && empty($params['password'])) { // IF identity loaded from cookie then set as params
            $params['username'] = $identity;
            $params['password'] = $credential;
        }

        $where2 = " 1 = 2 ";
        $username = strtolower(addslashes($params['username']));
        $password = addslashes($params['password']);

        // get the user if any
        $uDB = new Users();
        $users = $uDB->fetchAll(" LOWER(login) LIKE '" . $username . "' ");

        // one user found
        if (count($users) == 1) {
            if ($users[0]->safinstances_id == Sydney_Tools::getSafinstancesId()) {
                $where2 = " 1 = 1 ";
            } else {
                $corDB = new SafinstancesUsers();
                $cors = $corDB->fetchAll(" safinstances_id = " . Sydney_Tools::getSafinstancesId() . " AND users_id = " . $users[0]->id . " ");
                if (count($cors) > 0) {
                    $where2 = " 1 = 1 ";
                }
            }
            $username = $users[0]->login;
        }
        $config = Zend_Registry::get('config');
        $dbAdapter = new Zend_Db_Adapter_Pdo_Mysql($config->db->params);

        if ($encryptedPass === false) {
            $authAdapter = new Sydney_Auth_Adaptater_DbTable($dbAdapter, 'users', 'login', 'password', 'MD5(?) AND valid = 1 AND active = 1 AND (TIMESTAMPADD(SECOND,timeValidityPassword,lastpwdchanges) > now() OR timeValidityPassword = 0) AND ' . $where2);
        } else {

            $authAdapter = new Sydney_Auth_Adaptater_DbTable($dbAdapter, 'users', 'login', 'password', '? AND valid = 1 AND active = 1 AND (TIMESTAMPADD(SECOND,timeValidityPassword,lastpwdchanges) > now() OR timeValidityPassword = 0) AND ' . $where2);
        }

        // Store username and pass to cookie
        if ($params['rememberme'] == "1") {
            Sydney_Http_Cookie::setAuthCookie($username, $password, 7);
        }

        $authAdapter->setIdentity($username)->setCredential($password);

        return $authAdapter;
    }

    /**
     * authenticate() - defined by Zend_Auth_Adapter_Interface.  This method is called to
     * attempt an authentication.  Previous to this call, this adapter would have already
     * been configured with all necessary information to successfully connect to a database
     * table and attempt to find a record matching the provided identity.
     *
     * @throws Zend_Auth_Adapter_Exception if answering the authentication query is impossible
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $authresult = parent::authenticate();

        if ($authresult->isValid()) {
            // TODO Check if password isn't too old
        }

        return $authresult;
    }

}

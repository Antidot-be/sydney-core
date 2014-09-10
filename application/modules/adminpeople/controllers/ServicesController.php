<?php

/**
 * Controller
 */
class Adminpeople_ServicesController extends Sydney_Controller_Action
{
    /**
     * Defines the views types the actions should bring back
     * @var array
     */
    public $contexts = array(
        'delete'                 => array('json'),
        'processuser'            => array('json'),
        'processcompany'         => array('json'),
        'linkcompanytouser'      => array('json'),
        'linkcompaniestouser'    => array('json'),
        'userswebsitepermisions' => array('json'),
        'removeavatar'           => array('json'),
        'changeavatar'           => array('json'),
        'getcompanies'           => array('json'),
        'updatecompanies'        => array('json'),
        'deletecompanies'        => array('json'),
        'displaypeople'          => array('json', 'csv')
    );

    /**
     * @author GDE
     * @since 07/08/2013
     * @project #52-Ajouts exports CSV
     * @var bool
     */
    private $isCsv = false;

    /**
     * Controller initialization
     */
    public function init()
    {
        parent::init();
        $this->getResponse()->setHeader("Cache-Control", "no-cache, must-revalidate");

        // Init the Context Switch Action helper
        $contextSwitch = $this->_helper->contextSwitch();

        // GDE - #52-Ajouts exports CSV - 07/08/2013
        if ($this->getRequest()->getParam('format') == 'csv') {
            // Init
            $this->setIsCsv(true);
            // Excel format context
            $excelConfig =
                array(
                    'csv' => array(
                        'suffix'  => 'csv',
                        'headers' => array(
                            'Content-type' => 'text/csv',
                        )
                    )
                );
            // Add the new context
            $contextSwitch->setContexts($excelConfig);

            // Set the new context to the reports action
            //$contextSwitch->addActionContext('export', 'excel');

            // choose common view
            $this->_helper->viewRenderer('log');
        }

        // Initializes the action helper
        $contextSwitch->initContext();
        $this->_helper->layout->disableLayout();

        // get the request data
        $r = $this->getRequest();
        $this->jsonstr = false;
        if (isset($r->jsonstr)) {
            $this->jsonstr = Zend_Json::decode($r->jsonstr);
        }

        $this->_isService = true;
        parent::init();
        $this->getResponse()->setHeader("Cache-Control", "no-cache, must-revalidate");
    }

    public function indexAction()
    {

    }

    public function removeavatarAction()
    {
        $this->view->ResultSet = array(
            'message'  => 'No avatar updated !',
            'status'   => 0,
            'showtime' => 3,
            'modal'    => true
        );
        $r = $this->getRequest();

        if (isset($r->userid) && preg_match('/^[0-9]{1,10}$/', $r->userid)) {
            if (UsersOp::changeAvatar($r->userid)) {
                $this->view->ResultSet = array(
                    'message'  => 'OK success!',
                    'status'   => 1,
                    'showtime' => 2,
                    'modal'    => false,
                    'id'       => $r->userid
                );
            }
        }
    }

    public function changeavatarAction()
    {
        $this->view->ResultSet = array(
            'message'  => 'No avatar updated !',
            'status'   => 0,
            'showtime' => 3,
            'modal'    => true
        );
        $r = $this->getRequest();

        if (isset($r->userid) && preg_match('/^[0-9]{1,10}$/', $r->userid)) {
            if (UsersOp::changeAvatar($r->userid, $r->avatarid)) {
                $this->view->ResultSet = array(
                    'message'  => 'OK success!',
                    'status'   => 1,
                    'showtime' => 2,
                    'modal'    => false,
                    'id'       => $r->userid
                );
            }
        }
    }

    /**
     * Delete a user
     * @todo IMPORTANT SECURITY! Check that all the data linked to the user are not deleted (like images the user whould have uploaded)
     * @return void
     */
    public function deleteAction()
    {
        $this->view->ResultSet = array(
            'message'  => 'Error! You can not delete this user...',
            'status'   => 0,
            'showtime' => 5,
            'modal'    => true
        );
        $r = $this->getRequest();

        if (isset($r->id) && preg_match('/^[0-9]{1,10}$/', $r->id)) {
            $udb = new Users();
            $where = " id = '" . $r->id . "' AND safinstances_id = '" . $this->safinstancesId . "' AND id != '" . $this->usersId . "' ";
            $uu = $udb->fetchAll($where);
            if (count($uu) == 1) {
                $uu[0]->delete();
                $this->view->ResultSet = array(
                    'message'  => 'OK success!',
                    'status'   => 1,
                    'showtime' => 2,
                    'modal'    => false,
                    'id'       => $r->id
                );
            }
        }
    }

    /**
     * Saves the data from the "permission access to website form"
     * @return void
     */
    public function userswebsitepermisionsAction()
    {
        $this->view->ResultSet = array();
        $this->view->message = 'Error... ';
        $this->view->status = 0;
        $form = new UsersWebsitePermisionsForm();
        $data = $this->getRequest()->getPost();
        if ($form->isValid($data)) {
            if (isset($data['id']) && preg_match('/^[0-9]{1,10}$/', $data['id'])) {
                // update the user
                $usersDB = new Users();
                $users = $usersDB->find($data['id']);
                if (count($users) == 1) {
                    $users[0]->safinstances_id = $data['saf_id'];
                    $uid = $users[0]->id;
                    $users[0]->save();

                    // update correspondances
                    $c1DB = new SafinstancesUsers();
                    $c1DB->delete("users_id = '" . $uid . "' ");
                    $c1DB->setUsersLinkedTo($uid, $data['SafinstancesUsers']);

                    $this->view->message = 'Data saved';
                    $this->view->status = 1;
                    $this->view->modal = false;
                }
            }
        } else {
            $this->view->ResultSet = array(
                'errors' => $form->getMessages(),
                'entry'  => $data
            );
            $this->view->message = 'Errors in the form...';
            $this->view->status = 0;
            $this->view->timeout = 2;
            $this->view->modal = false;
        }

    }

    /**
     * Process edit/create and save a user with the user's form
     *
     * @todo IMPORTANT SECURITY check we are editing a user we have the rights to edit (according to the ID passed)
     * @return void
     */
    public function processuserAction()
    {
        $isCalledByPublicModule = $this->view->moduleName == 'publicms' ? true : false;
        $data = $this->getRequest()->getPost();

        $oUser = new Users();
        if (!$this->view->status = $oUser->save($data, $isCalledByPublicModule)) {
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
            $this->view->message = Sydney_Messages::getInstance()->getMessages();
            $this->view->ResultSet = array(
                'errors' => Sydney_Messages::getInstance()->getMessages(),
                'entry'  => $oUser->get()->toArray()
            );
        }

        $this->view->showtime = 3;
        $this->view->modal = false;
    }


    /**
     * Displays the file list in an HTML format
     * URL example : http://<url>/Adminfiles/Services/displayfiles/format/json
     *
     * @return void
     */
    public function displaypeopleAction()
    {
        $r = $this->getRequest();
        $ts = 1;
        if (isset($r->vmode)) {
            $this->view->vmode = $r->vmode;
            if ($r->vmode == 'list') {
                $ts = 3;
            }
        }

        $this->view->embeded = 'no';
        $this->view->context = 'default';

        if (isset($r->embeded)) {
            $this->view->embeded = $r->embeded;
        }
        if (isset($r->context)) {
            $this->view->context = $r->context;
        }

        $this->view->files = array();

        $fltr = new Zend_Filter_Digits();
        $desc = $fltr->filter($r->desc);
        $order = $fltr->filter($r->order);
        $count = $fltr->filter($r->count);
        $offset = $fltr->filter($r->offset);
        $filter = $fltr->filter($r->filter);
        $fcompany = $fltr->filter($r->fcompany);
        $fgroup = $fltr->filter($r->fgroup);
        $fstatus = $fltr->filter($r->fstatus);

        $fltr = new Zend_Filter_Alnum();
        $searchstr = $fltr->filter($r->searchstr);

        // GDE - #52-Ajouts exports CSV - 07/08/2013
        // On enregistre les filtres afin de pouvoir les utiliser lors de l'export
        $registry = new Zend_Session_Namespace('registry-people');
        if (!$this->isCsv()) {
            $registry->peopleFilterCompany = $fcompany;
            $registry->peopleFilterGroup = $fgroup;
            $registry->peopleFilterStatus = $fstatus;
            $registry->searchstr = $searchstr;
        } else {
            $fcompany = $registry->peopleFilterCompany;
            $fgroup = $registry->peopleFilterGroup;
            $fstatus = $registry->peopleFilterStatus;
            $searchstr = $registry->searchstr;
        }

        if (isset($r->embeded)) {
            $this->view->embeded = $r->embeded;
        }
        if (isset($r->context)) {
            $this->view->context = $r->context;
        }

        // define filters
        $filters = '';
        if (isset($searchstr)) {
            $filters .= " AND ( (LOWER( CONCAT(users.fname, ' ', users.lname)) LIKE '%" . addslashes(strtolower($searchstr)) . "%') OR login like '%" . addslashes(strtolower($searchstr)) . "%' ) ";
        }
        if (isset($r->fgroup) && trim($fgroup) != '') {
            $filters .= " AND users.usersgroups_id = $fgroup ";
        }
        if (isset($r->fstatus) && trim($fstatus) != '') {
            $filters .= " AND users.active = $fstatus ";
        }
        if (isset($r->fcompany)) {
            if ($r->fcompany != '') {
                $filters .= " AND companies.id = '" . addslashes($r->fcompany) . "' ";
            }
        }

        // GDE - #52-Ajouts exports CSV - 07/08/2013
        // On d�sactive la limitation du nombre de r�sultats pour l'export csv
        if ($this->isCsv()) {
            $count = 0;
        }
        $sql = UsersOp::getSqlUserList($this->usersData, array(
            $filters,
            $order,
            $count,
            $offset,
            $fcompany,
            $fgroup,
            $searchstr
        ));
        $sqlCount = UsersOp::getSqlUserList($this->usersData, array(
            $filters,
            $order,
            $count,
            $offset,
            $fcompany,
            $fgroup,
            $searchstr
        ), true);

        $this->view->users_id = $this->usersId;
        $this->view->people = $this->_db->fetchAll($sql); //$where, $order, $count, $offset

        // 	sets the number of pages
        if (!$this->isCsv()) {
            $resultCount = $this->_db->fetchAll($sqlCount);
            $this->view->nbpages = ceil($resultCount[0]['cnt'] / $count);
        }
    }

    /**
     * lists the companies
     */
    public function getcompaniesAction()
    {
        $this->_initDataTableRequest();
        $sDB = new Companies();
        $where = ' id IN (' . implode(',', $sDB->getLinkedSafinstancesIds($this->safinstancesId)) . ') ';
        $this->view->ResultSet = $sDB->fetchdatatoYUI($where, $this->sort . ' ' . $this->dir, $this->results, $this->startIndex, $this->hidefields);
    }

    /**
     * update a field
     */
    public function updatecompaniesAction()
    {
        $sDB = new Companies();
        $this->view->result = $sDB->updateOneField($this->getRequest()->getPost());
    }

    public function deletecompaniesAction()
    {
        $r = $this->getRequest();
        $sDB = new Companies();
        $ar = $sDB->deleteRowForGrid($r->id, $this->safinstancesId);
        foreach ($ar as $k => $v) {
            $this->view->$k = $v;
        }
    }

    /**
     * @author GDE
     * @since 07/08/2013
     * @project #52-Ajouts exports CSV
     * @param bool $isCsv
     */
    private function setIsCsv($isCsv)
    {
        $this->isCsv = $isCsv;
    }

    /**
     * Suis-je en mode export csv?
     * @author GDE
     * @since 07/08/2013
     * @project #52-Ajouts exports CSV
     * @return boolean
     */
    private function isCsv()
    {
        return $this->isCsv;
    }

}

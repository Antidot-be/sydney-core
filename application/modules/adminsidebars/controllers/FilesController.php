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
class Adminsidebars_FilesController extends Sydney_Controller_Action
{
    /*	public function init()
        {
            $reg = Zend_Registry::getInstance();
            $config = $reg->get('config');
            $this->view->cdn = $config->general->cdn;
        } */
    /**
     *
     */
    public function indexAction()
    {
        $sql = 'SELECT SUM(fweight) AS totalWeight FROM filfiles WHERE safinstances_id = ' . $this->safinstancesId;
        $ttw = $this->_db->fetchAll($sql);
        $this->view->totalWeight = $ttw[0]['totalWeight'];
        $this->view->totalWeightAllowed = $this->_config->files->totalWeightAllowed * 1024 * 1024 * 1024;
        $this->view->weightPercent = round($this->view->totalWeight / $this->view->totalWeightAllowed, 2) * 100;

        $dbfiles = new Filfiles();
        $where = 'safinstances_id = ' . $this->safinstancesId;
        $order = 'datecreated DESC';
        // $order=null;

        $this->view->lastuploaded = $dbfiles->fetchAll($where, $order, 10, 0);

    }

    public function editAction()
    {
    }

    public function uploadAction()
    {
    }
}

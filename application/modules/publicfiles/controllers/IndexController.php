<?php

/**
 *
 * @author arnaud
 * @todo TODO Big security hole here! as files are accecible even if we should not have the right to access it :)
 *
 */
class Publicfiles_IndexController extends Sydney_Controller_Actionpublic
{
    public $contexts = array(
        'viewfolercontent' => array('json'),
    );

    public function init()
    {
        parent::init();
        $this->getResponse()->setHeader("Cache-Control", "no-cache, must-revalidate");
        $this->_helper->contextSwitch()->initContext();
        $this->view->ajaxurl = '/publicfiles/index/';
        $this->loadInstanceViewHelpers();
    }

    /**
     *
     * @return void
     */
    public function indexAction()
    {
    }

    /**
     *
     * @todo TODO check the security here
     * @return void
     */
    public function viewfolercontentAction()
    {
        $this->view->ajaxurl .= 'viewfolercontent/';

        $ftype = false;
        $folderIds = false;
        $filesIds = array();
        $this->view->vmode = 'list';
        $r = $this->getRequest();

        if (isset($r->vmode)) {
            $this->view->vmode = $r->vmode;
        }
        if (isset($r->paperexecpt)) {
            $this->view->paperexecpt = $r->paperexecpt;
        }
        if (isset($r->ajsrt)) {
            $this->view->ajsrt = $r->ajsrt;
        }

        if (isset($r->fids)) {
            $folderIds = preg_split('/,/', $r->fids);
            foreach ($folderIds as $fid) {
                if (preg_match('/^[0-9]{1,90}$/', $fid)) {
                    $corDB = new FilfoldersFilfiles();
                    $tempArray = array_merge($filesIds, $corDB->getFilfilesLinkedTo($fid));
                    $filesIds = $tempArray;
                }
            }
            $filsDB = new Filfiles();
            if (sizeof($filesIds) > 0) {
                $files = $filsDB->fetchAll("id IN (" . implode(',', $filesIds) . ") ", null, "datecreated DESC");
            } else {
                $files = array();
            }
            if (isset($r->format) && $r->format == 'json') {
                $this->view->files = $files->toArray();
            } else {
                $this->view->files = $files;
            }
        }
    }

    /**
     *
     * @return void
     */
    public function lastupdatesAction()
    {
        $this->view->ajaxurl .= 'lastupdates/';

        $r = $this->getRequest();
        $nbr = 10;
        $noviewswitch = 'Y';
        $vmode = 'list';
        $flm = 1;
        $ftyp = array_merge(Sydney_Medias_Utils::$filters[4], Sydney_Medias_Utils::$filters[5]);
        $way = 'DESC';
        $dorder = 'datecreated';
        $this->view->ajsrt = 'date';

        if (isset($r->nbr) && preg_match('/^[0-9]{1,3}$/', $r->nbr)) {
            $nbr = $r->nbr;
            $this->view->ajaxurl .= 'nbr/' . $nbr . '/';
        }
        if (isset($r->noviewswitch)) {
            $noviewswitch = $r->noviewswitch;
            $this->view->ajaxurl .= 'noviewswitch/' . $noviewswitch . '/';
        }
        if (isset($r->vmode)) {
            $vmode = $r->vmode;
            $this->view->ajaxurl .= 'vmode/' . $vmode . '/';
        }
        if (isset($r->flm) && isset(Sydney_Medias_Utils::$filters[$r->flm])) {
            $ftyp = Sydney_Medias_Utils::$filters[$r->flm];
            $this->view->ajaxurl .= 'ftyp/' . $ftyp . '/';
        }
        if (isset($r->ajsrt)) {
            if ($r->ajsrt == 'date') {
                $dorder = 'datecreated';
            }
            if ($r->ajsrt == 'label') {
                $dorder = 'label';
            }
            if ($r->ajsrt == 'size') {
                $dorder = 'fweight';
            }
            $this->view->ajsrt = $dorder;
        }
        if (isset($r->way)) {
            if ($r->way == 'DESC') {
                $way = '';
            }
        }
        $this->view->ajaxurl .= 'way/' . $way . '/';

        $this->view->noviewswitch = $noviewswitch;
        $this->view->vmode = $vmode;
        $sql = "	safinstances_id = '" . $this->safinstancesId . "'
					AND type IN ('" . implode("','", $ftyp) . "') ";
        $filsDB = new Filfiles();
        $this->view->files = $filsDB->fetchAll($sql, trim($dorder . ' ' . $way), $nbr, 0);
        $this->render('viewfolercontent');
    }

}

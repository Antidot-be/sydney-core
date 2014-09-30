<?php
include_once(Sydney_Tools_Paths::getCorePath() . '/application/modules/adminfiles/controllers/FileController.php');

/**
 * Controller Adminfiles Files
 */

/**
 * Displays the file to the stout (or upload it to the stdout depending on the file).
 * The file is defined according to it's ID.
 *
 * @package Adminfiles
 * @subpackage Controller
 * @author Arnaud Selvais
 * @since 13/08/09
 * @copyright Antidot Inc. / S.A.
 *
 * @todo check the permission of the file here
 */
class Publicms_FileController extends Sydney_Controller_Actionpublic // Adminfiles_FileController
{


    public function init()
    {
        parent::init();
        $this->loadInstanceViewHelpers();
    }

    /**
     * List of the folders (tags) in an structured array (arborescence)
     * @return void
     */
    public function folderslistAction()
    {
        $r = $this->getRequest();
        $pid = 0;
        // id of the root we want to display the kids or none for all
        if (isset($r->parentid)) {
            $pid = $r->parentid;
        }
        $flo = new Filfolders();
        $this->view->list = $flo->getMultiArrayRelations($pid, $this->safinstancesId);
    }

    /**
     *
     * @return void
     */
    public function filelistAction()
    {
        $this->view->ajaxurl = '/publicms/file/filelist/';
        $request = $this->getRequest();
        $way = 'DESC';
        $srchstr = '';
        $dorder = 'datecreated';

        if (isset($request->srchstr) && $request->srchstr != '') {
            $srchstr = trim($request->srchstr);
        }
        $this->view->srchstr = $srchstr;

        $this->view->showOverlay = $request->getParam('overlay', true);
        $this->view->ajsrt = 'label';
        if (isset($request->ajsrt)) {
            // $this->view->ajsrt = $r->ajsrt;
            // $this->view->ajaxurl .= 'ajsrt/'.$this->view->ajsrt.'/';
            if ($request->ajsrt == 'date') {
                $dorder = 'filfiles.datecreated';
            }
            if ($request->ajsrt == 'label') {
                $dorder = 'filfiles.label';
            }
            if ($request->ajsrt == 'size') {
                $dorder = 'filfiles.fweight';
            }
            if ($request->ajsrt == 'user') {
                $dorder = 'u.fname,u.lname';
            }
            $this->view->ajsrt = $request->ajsrt;
        }
        if (isset($request->way)) {
            if ($request->way == 'DESC') {
                $way = '';
            }
        }
        $this->view->ajaxurl .= 'way/' . $way . '/';

        $this->view->flist = array();
        $sql = '';
        $fltr = new Zend_Filter_Digits();
        if (isset($request->id)) {
            $this->view->ajaxurl .= 'id/' . $request->id . '/';
            $folderid = $fltr->filter($request->id);
            $sql = "SELECT
						filfiles.id,
						filfiles.label,
						filfiles.desc,
						filfiles.keywords,
						filfiles.datecreated,
						filfiles.filename,
						filfiles.path,
						filfiles.type,
						u.fname,
						u.lname,
						filfiles.fweight
					FROM
						filfiles,
						filfolders,
						filfolders_filfiles,
						users as u
					WHERE ";
            if ($srchstr != '') {
                $sql .= ' filfiles.label LIKE ' . ($this->_db->quote('%' . $srchstr . '%')) . ' AND ';
            }
            $sql .= "
						filfiles.users_id = u.id AND
						filfolders_filfiles.filfiles_id = filfiles.id AND
						filfolders_filfiles.filfolders_id = filfolders.id AND
						filfolders.id = '" . $folderid . "' AND
						filfiles.safinstances_id = '" . $this->safinstancesId . "'
					GROUP BY filfiles.id
					ORDER BY
						" . $dorder . " $way
					";

        } elseif ($request->mode == 'filids' && isset($request->ids)) {

            $params = $request->params;
            if (is_string($params) && !empty($params)) {
                $params = unserialize(urldecode($params));
            }

            $ids = array();
            $this->view->ajaxurl .= 'mode/' . $request->mode . '/ids/' . $request->ids . '/params/' . urlencode(serialize($params)) . '/';
            if (isset($params) && key_exists('type', $params) && $params['type'] == "categories") {
                // Load the files id based on their category
                $linkedFiles = new FilfoldersFilfiles();
                foreach (preg_split('/,/', $request->ids) AS $category) {
                    $ids[] = $linkedFiles->getFilfilesLinkedTo($category);
                }
            } else {
                foreach (explode(',', $request->ids) as $id) {
                    $ids[] = $fltr->filter($id);
                }
            }
            $oFile = new Filfiles();
            $this->view->flist = $oFile->getFileInfosByIdList($ids, explode(',', $dorder . ($way ? ' ' . $way : '')));
        }
        if (isset($request->viewl)) {
            $this->view->viewl = $r->viewl;
            $this->view->ajaxurl .= 'viewl/' . $r->viewl . '/';
        }
        if (isset($request->noviewswitch)) {
            $this->view->noviewswitch = $request->noviewswitch;
            $this->view->ajaxurl .= 'noviewswitch/' . $request->noviewswitch . '/';
        }

        if ($sql != '') {
            $this->view->flist = $this->_db->fetchAll($sql);
        }

        /**
         * Params for lightboxview
         * AS : 20 Aug. 2013
         */
        // If vtype is 'lightbox' we display the lightbox view
        if (isset($request->vtype) && $request->vtype == 'lightbox') {
            $allParams = $request->getParams();
            if (!$allParams['nbrCols']) {
                $allParams['nbrCols'] = 6;
            }
            if (!isset($allParams['thumbWidth'])) {
                $allParams['thumbWidth'] = 200;
            }
            if (!isset($allParams['fullimgWidth'])) {
                $allParams['fullimgWidth'] = 600;
            }
            if (!isset($allParams['uselightbox'])) {
                $allParams['uselightbox'] = true;
            }
            if (!isset($allParams['lfrom'])) {
                $allParams['lfrom'] = 0;
            }
            if (!isset($allParams['loffset'])) {
                $allParams['loffset'] = ($allParams['nbrCols'] * 3);
            }
            if (!isset($allParams['searchtool'])) {
                $allParams['searchtool'] = true;
            }
            if (!isset($allParams['downloadable'])) {
                $allParams['downloadable'] = true;
            }
            if (isset($allParams['addClass'])) {
                unset($allParams['addClass']);
            }
            $this->view->allParams = $allParams;
            $this->render('lightbox');
        }
    }

    /**
     * View for lightbox thumbnails and highdef images ;)
     *
     */
    public function lightboxAction()
    {
    }

    /**
     *
     */
    protected function initFileHeaders()
    {
        $this->getResponse()->setHeader("Cache-Control", "no-cache, must-revalidate");
        $this->_helper->contextSwitch()->initContext();
        $r = $this->getRequest();
        if (isset($r->layout) && $r->layout == 'none') {
        } else {
            $this->_helper->layout->disableLayout();
        }
    }

    /**
     * Show the images from dynamic path with the following dimensions : $request->dw, $request->dh
     * Or if not notified, $dimensionWidth = 500
     *
     *  Example : /publicms/file/showimg/dw/400/id/5951/fn/5951.png
     * where ts is the thumb size mode
     * amd id is the ID of the file to get
     *
     * @todo TODO check the access rights to the images here!
     */
    public function showimgAction()
    {
        $this->initFileHeaders();
        $filter = new Zend_Filter_Digits();
        $dimensionWidth = 500;
        $dimensionHeight = null;
        $request = $this->getRequest();

        if (isset($request->id) && preg_match('/^[0-9]{1,30}$/', $request->id)) {
            $fileId = $filter->filter($request->id);
            if (isset($request->dw)) {
                $dimensionWidth = $filter->filter($request->dw);
            }
            if (isset($request->dh)) {
                $dimensionHeight = $filter->filter($request->dh);
            }
            $fileModel = new Filfiles();
            $where = 'id = ' . $fileId . ' AND safinstances_id = ' . $this->safinstancesId;
            $result = $fileModel->fetchAll($where);
            if (count($result) == 1) {
                $file = $result[0];
                $fileType = $file->type;
                $fullpath = Sydney_Tools_Paths::getAppdataPath() . '/adminfiles/' . $fileType . '/' . $file->filename;
                $fileTypeInstance = Sydney_Medias_Filetypesfactory::createfiletype($fullpath);
                if (!$fileTypeInstance->showImg($dimensionWidth, $dimensionHeight)) {

                }
            } else {

            }
        } else {

        }
        $this->render('index');
    }


    /**
     * Download the row file
     *
     */
    public function getrfileAction()
    {
        $this->initFileHeaders();
        $request = $this->getRequest();
        if (isset($request->id) && preg_match('/^[0-9]{1,30}$/', $request->id)) {
            $id = $request->id;
            $fileModel = new Filfiles();
            $where = 'id = ' . $id . ' AND safinstances_id = ' . $this->safinstancesId;
            $files = $fileModel->fetchAll($where);
            if (count($files) == 1) {
                $file = $files[0];

                //Définition dynamique du fullpath
                $fileType = $file->type;
                $fullpath = Sydney_Tools_Paths::getAppdataPath() . '/adminfiles/' . $fileType . '/' . $file->filename;
                $fileTypeInstance = Sydney_Medias_Filetypesfactory::createfiletype($fullpath);
                ob_end_clean();
                if (isset($request->download) && $request->download == 'yes') {
                    $cntdt = true;
                } else {
                    $cntdt = false;
                }
                $fileTypeInstance->getRawFile(false, $cntdt);
            }
        }
        $this->render('index');
    }


    /**
     * Show small images from uploaded pictures based on it's id and type size
     *
     * Type size :
     * 2 = 64x64 pixel
     * 3 = 32x32 pixel
     * 4 = 16x16 pixel
     *
     * Example : /publicms/file/thumb/id/1/ts/2
     * where ts is the thumb size mode
     * amd id is the ID of the file to get
     */
    public function thumbAction()
    {
        $this->initFileHeaders();
        $request = $this->getRequest();

        if (isset($request->id) && preg_match('/^[0-9]{1,30}$/', $request->id)) {
            if (isset($request->ts) && preg_match('/^[0-9]{1,30}$/', $request->ts)) {
                $typeSize = $request->ts;
            } else {
                $typeSize = 1;
            }
            $elementId = $request->id;
            $fileModel = new Filfiles();
            $where = 'id = ' . $elementId . ' AND safinstances_id = ' . $this->safinstancesId;
            $result = $fileModel->fetchAll($where);
            if (count($result) == 1) {
                $file = $result[0];

                $fileType = $file->type;
                $fullpath = Sydney_Tools_Paths::getAppdataPath() . '/adminfiles/' . $fileType . '/' . $file->filename;
                $fileTypeInstance = Sydney_Medias_Filetypesfactory::createfiletype($fullpath);

                // defines the thumb size
                if ($typeSize == 2) {
                    $fileTypeInstance->thumbSize = array(64, 64);
                }
                if ($typeSize == 3) {
                    $fileTypeInstance->thumbSize = array(32, 32);
                }
                if ($typeSize == 4) {
                    $fileTypeInstance->thumbSize = array(16, 16);
                }

                if (!$fileTypeInstance->showThumb()) {
                    print 'Image can not be processed';
                }
            } else {
                print 'You do not have access to this information';
            }
        } else {
            print 'Something is missing...';
        }
        $this->render('index');
    }
}

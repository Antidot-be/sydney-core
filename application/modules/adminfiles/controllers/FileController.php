<?php
include_once("Sydney/Medias/Filetypesfactory.php");
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
 */
class Adminfiles_FileController extends Sydney_Controller_Action
{
    /**
     * Controller initialization
     */
    public function init()
    {
        $this->_isService = true;
        parent::init();
        $this->getResponse()->setHeader("Cache-Control", "no-cache, must-revalidate");
        $this->_helper->contextSwitch()->initContext();
        $refactor = $this->getRequest();
        if (isset($refactor->layout) && $refactor->layout == 'none') {
        } else {
            $this->_helper->layout->disableLayout();
        }
    }

    /**
     *
     */
    public function indexAction()
    {
        echo 'nothing here...';
    }

    /**
     *
     * Show small images from uploaded pictures based on it's id and type size
     *
     * Type size :
     * 2 = 64x64 pixel
     * 3 = 32x32 pixel
     * 4 = 16x16 pixel
     *
     * Example: /adminfiles/file/thumb/id/1/ts/2
     * where ts is the thumb size mode
     * amd id is the ID of the file to get
     */
    public function thumbAction()
    {
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

                //Définition dynamique du fullpath
                $fullpath = Sydney_Tools_Paths::getAppdataPath() . '/adminfiles/' . $file->filename;
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

                // Process
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

    /**
     * Show the images from dynamic path with the following dimensions : $request->dw, $request->dh
     * Or if not notified, $dimensionWidth = 500
     *
     * Example : /adminfiles/file/thumb/id/1/ts/2
     * where ts is the thumb size mode
     * amd id is the ID of the file to get
     */
    public function showimgAction()
    {
        $filter = new Zend_Filter_Digits();
        $dimensionWidth = 500;
        $dimensionHeight = null;
        $request = $this->getRequest();
        if (isset($request->id) && preg_match('/^[0-9]{1,30}$/', $request->id)) {
            $elementId = $filter->filter($request->id);
            if (isset($request->dw)) {
                $dimensionWidth = $filter->filter($request->dw);
            }
            if (isset($request->dh)) {
                $dimensionHeight = $filter->filter($request->dh);
            }
            $fileModel = new Filfiles();
            $where = 'id = ' . $elementId . ' AND safinstances_id = ' . $this->safinstancesId;
            $ro = $fileModel->fetchAll($where);
            if (count($ro) == 1) {
                $file = $ro[0];

                //Définition dynamique du fullpath
                $fullpath = Sydney_Tools_Paths::getAppdataPath() . '/adminfiles/' . $file->filename;
                $fileTypeInstance = Sydney_Medias_Filetypesfactory::createfiletype($fullpath);
                if (isset($request->pdfpg) && $request->pdfpg > 1) {
                    $fileTypeInstance->pageid = intval($request->pdfpg) - 1;
                }
                if (!$fileTypeInstance->showImg($dimensionWidth, $dimensionHeight)) {
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

    /**
     * Function is probably not used yet 23/06/14
     *
     * @TODO find where this method is used
     */
    public function getfileAction()
    {
        $request = $this->getRequest();
        if (isset($request->id) && preg_match('/^[0-9]{1,30}$/', $request->id)) {
            $elementId = $request->id;
            $fileModel = new Filfiles();
            $where = 'id = ' . $elementId . ' AND safinstances_id = ' . $this->safinstancesId;
            $result = $fileModel->fetchAll($where);
            if (count($result) == 1) {
                $file = $result[0];

                //Définition dynamique du fullpath
                $fullpath = Sydney_Tools_Paths::getAppdataPath() . '/adminfiles/' . $file->filename;
                $fileTypeInstance = Sydney_Medias_Filetypesfactory::createfiletype($fullpath);
                if (!$fileTypeInstance->showImg()) {
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

    /**
     * Download the row file
     */
    public function getrfileAction()
    {
        $request = $this->getRequest();
        if (isset($request->id) && preg_match('/^[0-9]{1,30}$/', $request->id)) {
            $id = $request->id;
            $fileModel = new Filfiles();
            $where = 'id = ' . $id . ' AND safinstances_id = ' . $this->safinstancesId;
            $files = $fileModel->fetchAll($where);
            if (count($files) == 1) {
                $file = $files[0];

                //Définition dynamique du fullpath
                $fullpath = Sydney_Tools_Paths::getAppdataPath() . '/adminfiles/' . $file->filename;
                $fileTypeInstance = Sydney_Medias_Filetypesfactory::createfiletype($fullpath);

                ob_end_flush();
                $filename = false;
                $forceDownload = true;
                $fileTypeInstance->getRawFile($filename, $forceDownload);
            }
        }
        $this->render('index');
    }

    /**
     * Displays the content of a zip file (id passed as arg)
     */
    public function showzipcontentAction()
    {
        $request = $this->getRequest();
        if (isset($request->id) && preg_match('/^[0-9]{1,30}$/', $request->id)) {
            $id = $request->id;
            $fileModel = new Filfiles();
            $where = "id = " . $id . " AND safinstances_id = '" . $this->safinstancesId . "' AND type = 'ZIP' ";
            $files = $fileModel->fetchAll($where);
            $this->view->ziplist = array();
            if (count($files) == 1) {
                $file = $files[0];

                //Définition dynamique du fullpath
                $fullpath = Sydney_Tools_Paths::getAppdataPath() . '/adminfiles/' . $file->filename;
                $fileTypeInstance = Sydney_Medias_Filetypesfactory::createfiletype($fullpath);

                $this->view->ziplist = $fileTypeInstance->getZipContent();
            }
        }
    }

}

<?php

class Adminimageeditor_ServicesController extends Sydney_Controller_Action
{
    public $contexts = array(
        'saveimage' => array('json')
    );

    /**
     * (non-PHPdoc)
     * @see core/library/Sydney/Controller/Sydney_Controller_Action::init()
     */
    public function init()
    {
        parent::init();
        $this->getResponse()->setHeader("Cache-Control", "no-cache, must-revalidate");
        $this->_helper->contextSwitch()->initContext();
        $this->_helper->layout->disableLayout();
    }

    /**
     *
     */
    public function indexAction()
    {

    }

    /**
     *
     */
    public function revertAction()
    {
        $image = $this->_getImageFromRequest();
        if ($image) {

            //Définition dynamique du fullpath
            $webinstanceName = $this->_config->general->webinstance;
            $imageType = $image->type;
            $fullpath = __DIR__ . '/../../../../../webinstances/' . $webinstanceName . '/appdata/adminfiles/' . $imageType . '/' . $image->filename;

            $imageTypeInstance = Sydney_Medias_Filetypesfactory::createfiletype($fullpath);
            if (!$imageTypeInstance->getRawEditorCache(true)) {
                print 'Image can not be processed';
            }
        }
        $this->render('index');
    }

    /**
     *
     */
    public function showimgAction()
    {
        $image = $this->_getImageFromRequest();
        if ($image) {
            //Définition dynamique du fullpath
            $webinstanceName = $this->_config->general->webinstance;
            $imageType = $image->type;
            $fullpath = __DIR__ . '/../../../../../webinstances/' . $webinstanceName . '/appdata/adminfiles/' . $imageType . '/' . $image->filename;

            $imageTypeInstance = Sydney_Medias_Filetypesfactory::createfiletype($fullpath);
            if (!$imageTypeInstance->getRawEditorCache()) {
                print 'Image can not be processed';
            }
        }
        $this->render('index');
    }

    /**
     *
     */
    public function saveimageAction()
    {
        $image = $this->_getImageFromRequest();
        if ($image) {

            //Définition dynamique du fullpath
            $webinstanceName = $this->_config->general->webinstance;
            $imageType = $image->type;
            $path = __DIR__ . '/../../../../../webinstances/' . $webinstanceName . '/appdata/adminfiles/' . $imageType . '/';
            $fullpath = $path . $image->filename;

            $imageTypeInstance = Sydney_Medias_Filetypesfactory::createfiletype($fullpath);
            $im = $imageTypeInstance->_getTempEditorFullPath();
            $bn = preg_split('/\./', basename($fullpath));
            $filename = $bn[0] . '-' . mktime() . '.' . $bn[1];

            rename($im[1], $path . $filename);
            $fweight = filesize($path . $filename);

            $fil = new Filfiles();

            $fil->registerFileToDb($path, $filename, $fweight, strtoupper($bn[1]), $this->usersId, $this->safinstancesId);
            $this->view->message = 'Image saved to ' . $filename;

        } else {
            $this->view->message = 'Could not save the image...';
        }
    }

    /**
     *
     */
    public function cropimageAction()
    {
        $re = $this->getRequest();
        $image = $this->_getImageFromRequest();
        if ($image) {

            //Définition dynamique du fullpath
            $webinstanceName = $this->_config->general->webinstance;
            $imageType = $image->type;
            $fullpath = __DIR__ . '/../../../../../webinstances/' . $webinstanceName . '/appdata/adminfiles/' . $imageType . '/' . $image->filename;

            $imageTypeInstance = Sydney_Medias_Filetypesfactory::createfiletype($fullpath);
            if (!$imageTypeInstance->crop($re->getParams())) {
                print 'Image can not be processed';
            }
        }
        $this->render('index');
    }

    /**
     *
     */
    public function rotateAction()
    {

        $re = $this->getRequest();
        $image = $this->_getImageFromRequest();

        if ($image) {
            //Définition dynamique du fullpath
            $webinstanceName = $this->_config->general->webinstance;
            $imageType = $image->type;

            $fullpath = __DIR__ . '/../../../../../webinstances/' . $webinstanceName . '/appdata/adminfiles/' . $imageType . '/' . $image->filename;
            $ft = Sydney_Medias_Filetypesfactory::createfiletype($fullpath);

            if (!$ft->rotate($re->val)) {
                print 'Image can not be processed';
            }
        }
        $this->render('index');
    }

    /**
     *
     */
    public function flipAction()
    {
        $re = $this->getRequest();
        $image = $this->_getImageFromRequest();
        if ($image) {

            //Définition dynamique du fullpath
            $webinstanceName = $this->_config->general->webinstance;
            $imageType = $image->type;
            $fullpath = __DIR__ . '/../../../../../webinstances/' . $webinstanceName . '/appdata/adminfiles/' . $imageType . '/' . $image->filename;

            $imageTypeInstance = Sydney_Medias_Filetypesfactory::createfiletype($fullpath);
            if (!$imageTypeInstance->flip($re->val)) {
                print 'Image can not be processed';
            }
        }
        $this->render('index');
    }

    /**
     *
     */
    public function reflectionAction()
    {
        $re = $this->getRequest();
        $image = $this->_getImageFromRequest();
        if ($image) {

            //Définition dynamique du fullpath
            $webinstanceName = $this->_config->general->webinstance;
            $imageType = $image->type;
            $fullpath = __DIR__ . '/../../../../../webinstances/' . $webinstanceName . '/appdata/adminfiles/' . $imageType . '/' . $image->filename;

            $imageTypeInstance = Sydney_Medias_Filetypesfactory::createfiletype($fullpath);
            if (!$imageTypeInstance->reflectionEffect()) {
                print 'Image can not be processed';
            }
        }
        $this->render('index');
    }

    /**
     *
     */
    public function contrastAction()
    {
        $re = $this->getRequest();
        $image = $this->_getImageFromRequest();
        if ($image) {

            //Définition dynamique du fullpath
            $webinstanceName = $this->_config->general->webinstance;
            $imageType = $image->type;
            $fullpath = __DIR__ . '/../../../../../webinstances/' . $webinstanceName . '/appdata/adminfiles/' . $imageType . '/' . $image->filename;

            $imageTypeInstance = Sydney_Medias_Filetypesfactory::createfiletype($fullpath);
            if (!$imageTypeInstance->contrast()) {
                print 'Image can not be processed';
            }
        }
        $this->render('index');
    }

    /**
     *
     */
    public function sharpenAction()
    {
        $re = $this->getRequest();
        $image = $this->_getImageFromRequest();
        if ($image) {

            //Définition dynamique du fullpath
            $webinstanceName = $this->_config->general->webinstance;
            $imageType = $image->type;
            $fullpath = __DIR__ . '/../../../../../webinstances/' . $webinstanceName . '/appdata/adminfiles/' . $imageType . '/' . $image->filename;

            $imageTypeInstance = Sydney_Medias_Filetypesfactory::createfiletype($fullpath);
            if (!$imageTypeInstance->sharpen()) {
                print 'Image can not be processed';
            }
        }
        $this->render('index');
    }

    /**
     *
     */
    public function blacknwhiteAction()
    {
        $re = $this->getRequest();
        $image = $this->_getImageFromRequest();
        if ($image) {

            //Définition dynamique du fullpath
            $webinstanceName = $this->_config->general->webinstance;
            $imageType = $image->type;
            $fullpath = __DIR__ . '/../../../../../webinstances/' . $webinstanceName . '/appdata/adminfiles/' . $imageType . '/' . $image->filename;
            $imageTypeInstance = Sydney_Medias_Filetypesfactory::createfiletype($fullpath);
            if (!$imageTypeInstance->blacknwhite()) {
                print 'Image can not be processed';
            }
        }
        $this->render('index');
    }

    /**
     *
     */
    public function scaleAction()
    {
        $re = $this->getRequest();
        $image = $this->_getImageFromRequest();
        if ($image) {

            //Définition dynamique du fullpath
            $webinstanceName = $this->_config->general->webinstance;
            $imageType = $image->type;
            $fullpath = __DIR__ . '/../../../../../webinstances/' . $webinstanceName . '/appdata/adminfiles/' . $imageType . '/' . $image->filename;

            $imageTypeInstance = Sydney_Medias_Filetypesfactory::createfiletype($fullpath);
            if (!$imageTypeInstance->scale($re->val)) {
                print 'Image can not be processed';
            }
        }
        $this->render('index');
    }

    /**
     *
     */
    protected function _getImageFromRequest()
    {
        $fltr = new Zend_Filter_Digits();
        $re = $this->getRequest();
        if (isset($re->id) && preg_match('/^[0-9]{1,30}$/', $re->id)) {
            $elid = $fltr->filter($re->id);
            $mdb = new Filfiles();
            $where = 'id = ' . $elid . ' AND safinstances_id = ' . $this->safinstancesId;
            $r = $mdb->fetchAll($where);
            if (count($r) == 1) {
                return $r[0];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}

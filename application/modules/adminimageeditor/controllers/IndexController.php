<?php

class Adminimageeditor_IndexController extends Sydney_Controller_Action
{
    /**
     * (non-PHPdoc)
     * @see Sydney_Controller_Action::init()
     */
    public function init()
    {
        parent::init();
        $this->setSubtitle('Image Editor');
        $this->setSideBar('index', 'default');
        $this->layout->langswitch = false;
        $this->layout->search = false;

        $this->view->headLink()->appendStylesheet($this->view->cdnurl . '/sydneyassets/styles/imageeditor.css');
        $this->view->headLink()->appendStylesheet('/sydneyassets/yui/build/resize/assets/skins/sam/resize.css');
        $this->view->headLink()->appendStylesheet('/sydneyassets/yui/build/imagecropper/assets/skins/sam/imagecropper.css');
        $this->view->headScript()->appendFile($this->view->cdnurl . '/sydneyassets/scripts/public/jquery.imageeditor.js', 'text/javascript');
        $this->view->headScript()->appendFile('/sydneyassets/yui/build/resize/resize-min.js', 'text/javascript');
        $this->view->headScript()->appendFile('/sydneyassets/yui/build/imagecropper/imagecropper-min.js', 'text/javascript');

    }

    /**
     *
     */
    public function indexAction()
    {
        $this->setSubtitle2('Edit');
        $r = $this->getRequest();
        if (isset($r->id) && preg_match('/^[0-9]{1,50}$/', $r->id)) {
            $this->view->id = $r->id;
        }
        // else $this->view->id = 4941;
    }

}

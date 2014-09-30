<?php
/**
 * Controller
 */

/**
 * Controllers for structure edition
 *
 * @package Adminpages
 * @subpackage Controller
 * @author Arnaud Selvais <arnaud@antidot.com>
 * @since Mar 5, 2009
 * @copyright Antidot Inc. / S.A.
 */
class Adminpages_PagesController extends Sydney_Controller_Action
{
    /**
     * Init of the helpers for this controller. We are calling the parent init() first
     * @return
     */
    public function init()
    {
        parent::init();
        /*
         * @change GDE - 05/2014 - Content Translation
         * Load translation
         */
        $this->view->pageTranslate = new Translate_Content_Content();
    }

    /**
     *
     */
    public function indexAction()
    {
        $this->setSubtitle('Structure & Pages');
        $this->setSideBar('index', 'pages');
        $this->layout->langswitch = true;
        $this->layout->search = true;
    }

    /**
     * displays the page content in edition mode
     */
    public function editAction()
    {
        // [AS] add CKEditor (moved from the global as it was using a lot of ressource)
        $this->view->headScript()->appendFile(Sydney_Tools::getRootUrlCdn() . '/sydneyassets/jslibs/ckeditor/ckeditor.js', 'text/javascript');
        $this->view->headScript()->appendFile(Sydney_Tools::getRootUrlCdn() . '/sydneyassets/jslibs/ckeditor/adapters/jquery.js', 'text/javascript');

        $r = $this->getRequest();

        $elid = (isset($r->id) && preg_match('/^[0-9]{1,50}$/', $r->id)) ? $r->id : 0;
        $emodule = (isset($r->emodule)) ? $r->emodule : 'pages';

        $this->view->emodule = $emodule;
        $this->view->pagstructure_id = $elid;
        $this->view->customHelpers = $this->_registry->get('customhelpers');

        switch ($emodule) {
            case 'pages':
                $nodes = new Pagstructure();
                $where = 'id = ' . $elid . ' AND safinstances_id = ' . $this->safinstancesId;
                $this->view->node = $nodes->fetchRow($where);
                $this->view->pagid = $elid;
                $this->view->moduleName = 'adminpages';

                // set layout and titles properties
                $this->setSubtitle2($this->view->node->label);
                $this->setSubtitle('Edit page');
                $this->setSideBar('edit', 'pages');
                $this->layout->langswitch = true;
                $this->layout->search = true;

                // get the div content
                $cnt = new Pagdivspage();
                $this->view->contentDivs = $cnt->getDivs($this->view->node->id, false);

                // Affichage d'un design spécifique
                $layout = new Sydney_Layout_Layout();
                /* If layout if empty we will take the one in the config */

                if(!$this->view->node->layout){
                    $this->view->node->layout = $this->_config->general->layout;
                }
                $layout->setName($this->view->node->layout);
                if ($layout->loadZones()->hasZones()) {
                    $this->view->layout = $layout;
                    $this->view->preview = $layout->calculatePreview()->getPreview();
                    $this->view->zones = $layout->getZones();

                    $this->render('editzones');
                }
                break;
        }
    }

    /**
     *
     */
    public function sharecontentAction()
    {
        $this->view->r = $this->getRequest()->getParams();
    }

}

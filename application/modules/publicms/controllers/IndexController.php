<?php
/**
 * Controller Publicms Index
 */

/**
 * This will display the content from the CMS for the public part of the website
 *
 * @package Publicms
 * @subpackage Controller
 * @author Arnaud Selvais
 * @since
 * @copyright Antidot Inc. / S.A.
 */
class Publicms_IndexController extends Sydney_Controller_Actionpublic
{
    /**
     * Init of the helpers for this controller. We are calling the parent init() first
     * @return
     */
    public function init()
    {
        parent::init();
        $this->loadInstanceViewHelpers();
    }

    /**
     * Redirects to the right action
     */
    public function indexAction()
    {
        $this->redirect('/publicms/index/view/');
    }

    /**
     * Shows the content of a page
     *
     * @tood: add a param in the config to force the load of the next child or not react this way (dispay the empty node)
     *
     * Example with params:
     * /publicms/index/view/page/2831,4681,9121,9131,9141,9151,9161,9171,9181,9271,9221,9231,9241,9251,9261,9191,9201,9211,4311,9011/notthisdivs/66401,66431,66311,66271,66881/showtitles/Y/layout/antidotcaprint/
     *
     * Request Params:
     *        $r->format            The format we need the data to be returned
     *        $r->layout            If 'no' we do not use a layout; or if defined we use the layout name passed as val
     *        $r->heading            ???
     *        $r->notthisdivs        IDs of divs we don't want to show up (separated by a comma)
     *        $r->showtitles        Should we add the page title in case of multiple page ids
     *
     */
    public function viewAction()
    {

        $r = $this->getRequest();
        $this->view->requestParams = $r->getParams();

        if (!empty($r->format)) {
            $this->_helper->contextSwitch()->initContext($r->format);
        }
        $heading = 0;
        $dontRetreiveIds = array();
        $showtitles = false;
        $layout = '';

        if ($r->layout == 'no') {
            $this->_helper->layout->disableLayout();
        }
        if ($r->layout) {
            $layout = $r->layout;
        }
        if ($r->heading) {
            $heading = $r->heading;
        }
        if ($r->notthisdivs) {
            $dontRetreiveIds = explode(',', $r->notthisdivs);
        }
        if ($r->showtitles == 'Y') {
            $showtitles = true;
        }

        $this->view->heading = $heading;
        $currentPage = $this->_getPageId();
        $nodes = $this->structure;
        if (isset($this->structure->stringNodes[$currentPage])) {
            $thisnode =& $this->structure->stringNodes[$currentPage];
        }
        // push the current node in the view to access params in the layout
        $this->view->thisnode = $thisnode;
        $this->_checkUrlLabel();
        if (isset($thisnode['htmltitle']) && !empty($thisnode['htmltitle'])) {
            $this->view->headTitle($this->view->escape($thisnode['htmltitle']), Zend_View_Helper_Placeholder_Container_Abstract::SET);
        } else {
            $this->view->headTitle($thisnode['label']);
        }
        $this->_manageMetaTags();

        if ($r->layout == 'no') {
            if (isset($this->getRequest()->page)) {
                $cnt = new Pagdivspage();
                $cntDivs = $cnt->getDivs($this->getRequest()->page);
                $this->view->contentDivs = $cntDivs;
            }
        } elseif (!isset($thisnode) || ($thisnode['status'] != 'published')) {
            $this->forward('index', 'index', 'error');
        } else {
            $this->view->rootid = $currentPage;

            // Si la page est une redirection
            if (isset($thisnode) && $thisnode['redirecttoid'] > 0) {
                $currentPage = $thisnode['redirecttoid'];
            }

            $this->view->pagid = $currentPage;
            $where = 'safinstances_id = ' . $this->safinstancesId . ' AND id = ' . $currentPage;
            $this->view->nodes = $nodes->fetchAll($where);

            $this->view->contentDivs = array();
            if (count($this->view->nodes) > 0) {
                foreach ($this->view->nodes as $node) {
                    $cnt = new Pagdivspage();
                    $cntDivs = $cnt->getDivs($node->id, false, $dontRetreiveIds, $showtitles, array(
                        '<h1>',
                        '</h1>'
                    ), $this->structure);
                    $this->view->contentDivs = array_merge($this->view->contentDivs, $cntDivs);
                }
            }
        }

        $this->manageLayoutZones();


        // set a custom layout if any
        $this->_setCustomLayout($currentPage, $layout);
    }


    /**
     * On gère ici les zones pour le design
     * @since 12/11/2013
     */
    private function manageLayoutZones()
    {
        $layoutObject = new Sydney_Layout_Layout();
        // On prend en compte les redirection
        $layoutName = (!empty($this->view->thisnode['redirecttoid'])) ? $this->structure->stringNodes[$this->view->thisnode['redirecttoid']]['layout'] : $this->view->thisnode['layout'];
        $layoutObject->setName($layoutName);

        if ($layoutObject->loadZones()->hasZones()) {

            $data = array();
            $this->view->hasZone = true;
            $this->view->saveContentDivs = $this->view->contentDivs; // On enregistre l'ancien contentDivs
            foreach ($layoutObject->getZones() as $zoneName => $zone) {

                $this->filterContentDivByZone($zoneName);
                /* On rend "partiellement" les zones et on rï¿½cupï¿½re le contenu
                 * gï¿½nï¿½rer pour aprï¿½s l'injecter dans le template
                */
                $data[$zoneName] .= $this->view->render('index/view.phtml', null, true);
            }
            // On "injecte" les donnï¿½es gï¿½nï¿½rï¿½es dans le template
            $this->_helper->layout->assign('zones', $data);
            $this->_helper->viewRenderer->setNoRender();
        }
    }

    /**
     * Filtre le content div par zone
     * @since 12/02/2014
     * @param $zoneName
     */
    private function filterContentDivByZone($zoneName)
    {
        $tempContentDiv = array();
        foreach ($this->view->saveContentDivs as $div) {

            if ($div['zone'] == $zoneName) {
                $tempContentDiv[] = $div;
            }
        }
        $this->view->contentDivs = $tempContentDiv;
    }


    /**
     * Cette mï¿½thode va vï¿½rifier le label de l'url (slug)
     * Si le label courant est diffï¿½rent du label supposï¿½, on redirige vers le label supposï¿½
     * @author JTO
     * @since 19/02/2014
     */
    private function _checkUrlLabel()
    {
        $currentUrlLabel = trim($this->getRequest()->slug);
        $realUrlLabel = trim($this->view->thisnode['url']);
        $id = (int) $this->view->thisnode['id'];

        if (!empty($currentUrlLabel) && $this->view->thisnode) {
            // Cas 1 - Nouvelle url
            if (!empty($realUrlLabel)) {
                if ($realUrlLabel != $currentUrlLabel) {
                    $url = Sydney_Tools_Friendlyurls::getFriendlyUrl($id, $realUrlLabel, 'page', new Zend_View_Helper_Url());
                    $this->redirect($url, array('code' => 301));
                }
            } else {
                // On est dans l'ancien systï¿½me d'url ou le titrede la page = le label url
                // On doit donc calculer le label de l'url SUPPOSE et le confronter
                // au label reï¿½u dans l'url
                // Si diffï¿½rent on fait une redirection vers la VRAI url
                $supposedUrlLabel = Sydney_Tools_Friendlyurls::getUrlLabel($this->view->thisnode['label']);
                if ($supposedUrlLabel != $currentUrlLabel) {
                    $url = Sydney_Tools_Friendlyurls::getFriendlyUrl($id, $supposedUrlLabel, 'page', new Zend_View_Helper_Url());
                    $this->redirect($url, array('code' => 301));
                }
            }
        }
    }

    /**
     * Displays the sitemap, meaning the structure of the website in a hierarchy
     * @return void
     */
    public function sitemapAction()
    {
        $langCode = $this->getCurrentLangCode(true);
        $structure = $this->_getStructure();

        if (is_array($langCode)) {
            foreach ($structure as $k => $v) {
                if ($v['id'] == $langCode[1]) {
                    $structureAll = $v['kids'];
                    break;
                }
            }
        } else {
            $structureAll = $structure;
        }

        $this->view->sitemap = $structureAll;
    }

    /**
     *
     * @return array
     */
    protected function _getStructure()
    {
        return $this->view->structure;
    }

    /**
     * Displays the whole website content in one page
     * @todo TODO check if this can be a problem on a security level
     * @return void
     */
    public function viewallAction()
    {
        $ret = '';
        $this->hnbr = 0;
        foreach ($this->_getStructure() AS $page) {
            $ret .= $this->returnKids($page['kids'], true);
        }
        $this->view->content = $ret;
    }

    /**
     *
     * @param unknown_type $kidspages
     */
    private function returnKids($kidspages, $withContent = false)
    {
        $dt = '';
        if (sizeof($kidspages) > 0) {
            $this->hnbr++;
            foreach ($kidspages AS $page) {
                $dt .= '<h' . $this->hnbr . '>' . $page['label'] . '</h' . $this->hnbr . '>' . "\n";
                $dt .= $this->view->action('view', 'index', 'publicms', array(
                    'page'    => $page['id'],
                    'heading' => $this->hnbr
                ));
                $dt .= $this->returnKids($page['kids']);
            }
            $this->hnbr--;
        }

        return $dt;
    }

    /**
     * Returns the ID of the home page for this SAF instance
     * @return int
     */
    protected function _getPageId()
    {
        $nodes = new Pagstructure();
        if (isset($this->getRequest()->slug)) {
            return $nodes->getIdBySlug($this->getRequest()->slug, $this->safinstancesId);
        } else {
            return $nodes->getHomeId($this->safinstancesId);
        }
    }

}

<?php
require_once('Zend/View/Helper/Abstract.php');

/**
 * Helper showing the structure of a site in edit mode.
 * This is used in the module page -> structure editor
 *
 * @package SydneyLibrary
 * @subpackage ViewHelper
 * @todo Implement the translation method here
 */
class Sydney_View_Helper_StructureEditor extends Zend_View_Helper_Abstract
{
    /**
     * @var String HTML string to be returned
     */
    private $toReturn;

    /**
     * @var string HTML with all actions for each nodes
     */
    private $strAction;

    private $groups = array();
    /**
     * @var int The current level in the tree
     */
    private $level = 0;

    /**
     * @var array content the list of page with the number of content draft
     */
    private $listDraftContentByPage = array();

    /**
     *
     */
    private $context = 'default'; // actually 2 context: default and ckeditor

    /**
     *
     * Enter description here ...
     * @var unknown_type
     */
    private $hasNodeRestored = false;

    /**
     * Helper main function
     * @return String HTML to be inserted in the view
     * @param Array $structureArray [optional] Structure in an array form
     */
    public function StructureEditor($structureArray = array())
    {
        $oPageDivs = new Pagdivspage();
        $this->listDraftContentByPage = $oPageDivs->getDivsDraft();

        $grpDB = new Usersgroups();
        $this->groups = $grpDB->fetchLabelstoFlatArray();
        $this->addNodes($structureArray);

        return $this->toReturn;
    }

    /**
     * Add an array of nodes
     * @return void
     * @param Array $nodes [optional]
     */
    private function addNodes($nodes = array(), $sub = false, $parentDbId = 0, $firstCall = true)
    {
        if (!$sub) {
            $this->toReturn .= $this->getTabs() . '<ul id="sitemap" class="tree ui-sortable" dbid="' . $parentDbId . '">';
        } else {
            $this->toReturn .= $this->getTabs() . '<ul class="" dbid="' . $parentDbId . '">';
        }

        foreach ($nodes as $node) {

            if (isset($node['pagorder'])) {
                $pagorder = $node['pagorder'];
            } else {
                $pagorder = 0;
            }

            if ($node['status'] == 'restored') {
                $this->hasNodeRestored = true;
            }
            $liAddClass = '';
            if (($this->listDraftContentByPage[$node['id']] > 0)) {
                $liAddClass .= 'draft ';
                $alertContentDraft = ' , Draft: ' . $this->listDraftContentByPage[$node['id']];
            }
            $this->toReturn .=
                '<li class="test ' . $liAddClass . ' ' . ($node['ishome'] == 1 ? 'selected' : '') . '" id="' . $node['id'] . '" data="{addClass: \'' . $node['status'] . ' permspicto ' . $this->groups[($node['usersgroups_id'])] . '\',url: \'/adminpages/pages/edit/id/' . $node['id'] . '\',
                noLink:' . ($this->isRedirected($node) ? 'true' : 'false') . '}" id="structure_' . $node['id'] . '" dbid="' . $node['id'] . '" dborder="' . $pagorder . '">'
                . $node['label'] .
                ' <div class="dynatree-title-detail">(Status: ' . $node['status'] .
                ' , View: ' . (int) $node['stats']['views'] . $alertContentDraft;

            $this->toReturn .= ')' . $this->getDataNodeAsHtml($node) . $this->getIshomepageHtml($node['ishome']) . $this->getIsRedirected($node);
            $this->toReturn .= '</div>';

            // prefix the structure content by action
            $this->strAction .= $this->getActionsHtml($node['id'], $node['ishome'], $node['status'], $node);

            if (count($node['kids']) > 0) {
                $this->toReturn .= $this->addNodes($node['kids'], true, $node['id'], false);
            }

            if (!$sub && $this->hasNodeRestored && $node['status'] != 'restored') {
                $this->hasNodeRestored = false;
            }

        } // END Foreach

        $this->toReturn .= '</ul>';

        if ($firstCall) {
            $this->toReturn .= $this->strAction;
        }
    }

    /**
     *
     */
    private function isRedirected($node)
    {
        return (!empty($node['redirecttoid']) && $node['redirecttoid'] != 0);
    }

    /**
     * @param $node
     * @return string
     */
    private function getIsRedirected($node)
    {
        if ($this->isRedirected($node)) {
            return ' <img src="' . $this->view->cdn . '/sydneyassets/images/icons/redirect.png" alt="Page redirected" title="Page redirected" />';
        }
    }

    /**
     * Returns the HTML to mark a node as being the home page
     * @param int $isit
     * @return string
     */
    private function getIshomepageHtml($isit = 0)
    {
        return ($isit == 1) ? '<span class="capsule">Homepage</span>' : '';
    }

    /**
     * Returns end of line and tabs for proper HTML indentation
     * @return String
     */
    private function getTabs()
    {
        $toret = '';
        for ($i = 0; $i <= $this->level; $i++) {
            $toret .= "\t";
        }

        return "\n" . $toret;
    }

    /**
     *
     */
    public function setContext($value)
    {
        if ($value == '') {
            $value = 'default';
        }
        $this->context = $value;
    }

    /**
     *
     */
    public function getContext()
    {
        return $this->context;
    }

    private function getDataNodeAsHtml($node)
    {
        $data = '<div class="tooltip-infos" style="display: none">';
        if (is_array($node['stats']) && count($node['stats']) > 1) {
            $data .= '<b>Views:</b> ' . $node['stats']['views']
                . '<br/><b>Unique:</b> ' . $node['stats']['unique']
                . '<br/><b>Time on page:</b> ' . $node['stats']['timeonpage']
                . '<br/><b>Bounces:</b> ' . $node['stats']['bounces']
                . ' % <br/><b>Exits:</b> ' . $node['stats']['exits']
                . ' % <br/>';
        } else {
            $data .= 'No stats, yet <br/>';
        }
        $data .= '
                <b>Last publication:</b> ' . Sydney_Tools::getDateDashboard($node['datemodified']) . ' <b>by</b> ' . $node['who_modified']
            . '<br/>
                <b>Last update content:</b> ' . Sydney_Tools::getDateDashboard($node['date_lastupdate_content']) . ' <b>by</b> ' . $node['who_lastupdate_content'] . '<br/>
	</div>';

        return $data;
    }

    /**
     * Returns the HTML for the possible action for a node
     *
     *         (
     * [id] => 4031
     * [label] => Welcome in Sydney docs
     * [isCollapsed] =>
     * [status] => published
     * [datemodified] => 2010-11-24 06:38:37
     * [date_lastupdate_content] => 2010-11-24 06:38:37
     * [who_modified] => Gilles Demaret
     * [who_lastupdate_content] => Gilles Demaret
     * [ishome] => 1
     * [iscachable] =>
     * [cachetime] => 0
     * [menusid] => Array
     * (
     * )
     *
     * [redirecttoid] => 0
     * [usersgroups_id] => 2
     * [pagorder] => 0
     * [kids] => Array
     * (
     * )
     *
     * [stats] => Array
     * (
     * )
     *
     * )
     *
     *
     * @return String HTML string
     * @param int $dbid [optional] DB ID of the node
     * @param int $ishome [optional]
     */
    private function getActionsHtml($dbid = 0, $ishome = 0, $status = 'draft', $node = '')
    {
        $hidit = ($ishome == 1) ? ' invisible' : '';
        $btnStatus = ($status != 'published') ? 'publish' : 'unpublish';
        $toret = '<div id="adminpageaction-' . $dbid . '" style="display:none; text-align:right;" class="adminpages_action_container">';

        $toret .= '
        <div class="actionsContainer">
            <span class="actions">
                <a class="button ' . $btnStatus . '" id="btn_publish_' . $dbid . '" dbid="' . $dbid . '">
                    ' . ucfirst($btnStatus) . '
                </a>
                <a class="button" href="/adminpages/index/create/parentid/' . $dbid . '">
                    <img src="' . Sydney_Tools::getRootUrlCdn() . '/sydneyassets/images/ui/button/icon_add.png"/> Add a sub-page
                </a>
                <a class="button" href="/adminpages/index/editproperties/id/' . $dbid . '">
                    Properties
                </a>
                <a class="button duplicate" dbid="' . $dbid . '" href="#">
                    Duplicate
                </a>
                <a class="button warning' . $hidit . ' deletenodea" dbid="' . $dbid . '" href="#">
                    Delete
                </a>
            </span>
	    </div>';

        $toret .= '</div>';

        return $toret;
    }

}

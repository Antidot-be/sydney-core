<?php
require_once('Zend/View/Helper/Abstract.php');

/**
 * Helper showing the structure of a site in edit mode.
 * This is used in the module page -> structure editor
 *
 * @package SydneyLibrary
 * @subpackage ViewHelper
 * @author Arnaud Selvais
 * @since 31/05/09
 * @todo Implement the translation method here
 */
class Sydney_View_Helper_RecyclebinEditor extends Zend_View_Helper_Abstract
{
    /**
     * @var String HTML string to be returned
     */
    private $toReturn;
    /**
     * @var Array Containing the statuses. The key is the CSS class, the value is the label
     */
    private $statuses = array(
        'draft'     => 'Draft',
        'revised'   => 'Revised',
        'published' => 'Published'
    );
    private $groups = array();
    /**
     * @var int The current level in the tree
     */
    private $level = 0;

    /**
     *
     */
    private $context = 'default'; // actually 2 context: default and ckeditor

    /**
     * Helper main function
     * @return String HTML to be inserted in the view
     * @param Array $structureArray [optional] Structure in an array form
     */
    public function RecyclebinEditor($structureArray = array())
    {
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
    private function addNodes($nodes = array(), $sub = false, $parentdbid = 0)
    {
        if (!$sub) {
            $this->toReturn .= $this->getTabs() . '<ul id="sitemap" class="tree ui-sortable recyclebin" dbid="' . $parentdbid . '">';
        } else {
            $this->toReturn .= $this->getTabs() . '<ul class="" dbid="' . $parentdbid . '">';
        }

        foreach ($nodes as $node) {
            if (count($node['kids']) > 0) {
                $collapsedCss = ' collapsed';
            } else {
                $collapsedCss = '';
            }

            if (isset($node['pagorder'])) {
                $pagorder = $node['pagorder'];
            } else {
                $pagorder = 0;
            }
            $this->toReturn .=
                $this->getTabs() . '<li class="liRowRecyclebin" id="recyclebin_' . $node['id'] . '" dbid="' . $node['id'] . '" dborder="' . $pagorder . '">'
                . $this->getTabs() . "\t" . '<div class="row">'
                . $this->getTabs() . "\t" . '<a class="bullet' . $collapsedCss . '" href="#">•</a>'
                . $this->getTabs() . "\t" . '<label><a>' . $node['label'] . '</a></label>'

                . $this->getTabs() . "\t" . '<span class="status ' . $node['status'] . '"></span>'
                . $this->getTabs() . "\t" . $this->getActionsHtml($node['id'], $node['ishome'], $node['status'])

                . $this->getTabs() . "\t" . '</div>';
            if (count($node['kids']) > 0) {
                $this->toReturn .= $this->addNodes($node['kids'], true, $node['id']);
            }
            $this->toReturn .= $this->getTabs() . '</li>';
        }
        $this->toReturn .= $this->getTabs() . '</ul>';
    }

    /**
     * Returns the HTML to mark a node as being the home page
     * @return String HTML string
     * @param boolean $isit [optional]
     */
    private function getIshomepageHtml($isit = 0)
    {
        if ($isit == 1) {
            return '<span class="capsule">Homepage</span>';
        } else {
            return '';
        }
    }

    /**
     * Returns the HTML for the possible action for a node
     * @return String HTML string
     * @param int $dbid [optional] DB ID of the node
     * @param int $ishome [optional]
     */
    private function getActionsHtml($dbid = 0, $ishome = 0, $status = 'draft')
    {
        $toreturn = '';
        $toreturn .= '<div class="actionsContainer">';
        if ($this->getContext() == 'default') {
            $toreturn .= '<span class="actions">';
            $toreturn .= '<a dbid="' . $dbid . '" class="button restorenodea" href="/adminpages/recyclebin/restore/format/json">Restore</a>';
            $toreturn .= '<a class="button warning deleterestorenodea' . $hidit . ' deletenodea" dbid="' . $dbid . '" href="#">Delete</a>';
            $toreturn .= '</span>';
        }
        $toreturn .= '</div>';

        return $toreturn;
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

}

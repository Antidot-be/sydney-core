<?php
require_once('Zend/View/Helper/Abstract.php');

/**
 * Helper showing the structure of a site in a drop down box
 *
 * @package SydneyLibrary
 * @subpackage ViewHelper
 * @author Arnaud Selvais
 * @since 08/06/09
 * @todo Implement the translation method here
 */
class Sydney_View_Helper_StructureDropdown extends Zend_View_Helper_Abstract
{
    /**
     * @var String HTML string to be returned
     */
    private $toReturn;
    private $level = 0;
    private $html = '';
    private $selectedId;
    private $structureArray;

    /**
     * Helper main function
     * @return String HTML to be inserted in the view
     * @param Array $structureArray [optional] Structure in an array form
     * @param int $selectedId ID of the selected node
     */
    public function StructureDropdown()
    {
        $this->resetValue();

        return $this;
    }

    function resetValue()
    {
        $this->toReturn = '<option value="" selected="selected">None</option><option value="">-----------</option>';
        $this->setStructureArray(null);
        $this->setSelectedId(null);
    }

    public function setStructureArray($structureArray)
    {
        $this->structureArray = $structureArray;

        return $this;
    }

    public function setSelectedId($selectedId)
    {
        $this->selectedId = $selectedId;

        return $this;
    }

    public function render()
    {
        $this->html = $this->buildStructureDropdown($this->structureArray, $this->selectedId);

        return $this->html;
    }

    public function __toString()
    {
        return $this->render();
    }

    public function buildStructureDropdown($structureArray = array(), $selectedId = 0)
    {
        $lvl = '';
        if ($this->level > 0) {
            for ($i = 0; $i < $this->level; $i++) {
                $lvl .= '&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            $lvl .= '∟&nbsp;';
        }
        foreach ($structureArray as $node) {
            if ($selectedId == $node['id']) {
                $sel = ' selected="selected"';
            } else {
                $sel = '';
            }
            $this->toReturn .= '<option value="' . $node['id'] . '"' . $sel . '>' . $lvl . $node['label'] . '</option>' . "\n";
            if (count($node['kids']) > 0) {
                $this->level++;
                $this->buildStructureDropdown($node['kids'], $selectedId);
                $this->level--;
            }
        }

        return $this->toReturn;
    }

}

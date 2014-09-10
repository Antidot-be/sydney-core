<?php
require_once('Zend/View/Helper/Abstract.php');

/**
 * Helper showing the heading editor for page content editing
 *
 * @package Adminpages
 * @subpackage ViewHelper
 * @author Arnaud Selvais
 * @since 08/06/09
 * @todo Implement the translation method here
 */
class Adminpages_View_Helper_EditorFreeText extends Zend_View_Helper_Abstract
{
    /**
     * Helper main function
     * @return String HTML to be inserted in the view
     */
    public function EditorFreeText()
    {
        $toReturn = '<div class="editor freetext"><p class="sydney_editor_p">';
        // $toReturn .= '<input class="value" type="text" value="" style="width:100%; height:300px;" />';
        $toReturn .= '<textarea class="value" style="width:100%; height:300px;"></textarea>';
        $toReturn .= '</p>';

        $toReturn .= '<p class="buttons sydney_editor_p"><a class="button sydney_editor_a" href="save">Save as actual content</a> <a class="button sydney_editor_a" href="save-draft">Save as draft</a> <a class="button muted sydney_editor_a" href="cancel">Cancel</a></p></div>';

        return $toReturn;
    }
}

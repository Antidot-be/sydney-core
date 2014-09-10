<?php

/**
 * Insert a link "Back to top" in the page
 *
 * @author Frederic Arijs
 * @since  05-sept.-2011
 */
class Adminpages_View_Helper_EditorGototop extends Zend_View_Helper_Abstract
{
    public function EditorGototop()
    {
        $toReturn = '<div class="editor gototop">';
        $toReturn .= '<p class="sydney_editor_p" align="right"><a href="javascript:scroll(0,0);" class="backtotop sydney_editor_a">'
            . Zend_Registry::get('Zend_Translate')->_('gototop')
            . '</a></p>';

        $toReturn .= '<p class="buttons sydney_editor_p"><a class="button sydney_editor_a" href="save">
                        Save as actual content</a> <a class="button sydney_editor_a" href="save-draft">
                        Save as draft</a> <a class="button muted sydney_editor_a" href="cancel">
                        Cancel</a></p>';
        $toReturn .= '</div>';

        return $toReturn;
    }
}

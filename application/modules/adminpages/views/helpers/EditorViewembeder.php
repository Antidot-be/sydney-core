<?php

/**
 * Helper showing the heading editor for page content editing
 */
class Adminpages_View_Helper_EditorViewembeder extends Zend_View_Helper_Abstract
{
    /**
     * Helper main function
     * @return String HTML to be inserted in the view
     */
    public function EditorViewembeder()
    {
        $toReturn = '<div class="editor view-embedder-block" data-content-type="view-embedder-block">
					<p class="sydney_editor_p">' . Sydney_Tools_Localization::_('View path') . ' <input class="value sydney_editor_input" type="text" value="" style="width:300px;" /></p>
					<p class="buttons sydney_editor_p"><a class="button sydney_editor_a" href="save">' . Sydney_Tools_Localization::_('Save as actual content') . '</a> <a class="button sydney_editor_a" href="save-draft">' . Sydney_Tools_Localization::_('Save as draft') . '</a> <a class="button muted sydney_editor_a" href="cancel">' . Sydney_Tools_Localization::_('Cancel') . '</a></p>
				</div>';

        return $toReturn;
    }
}

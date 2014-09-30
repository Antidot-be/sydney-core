<?php

/**
 * Helper showing the heading editor for page content editing
 */
class Adminpages_View_Helper_EditorHeading extends Zend_View_Helper_Abstract
{
    /**
     * Helper main function
     * @return String HTML to be inserted in the view
     */
    public function EditorHeading()
    {
        $toReturn = '<div class="editor heading-block" data-content-type="heading-block">
					<p class="sydney_editor_p"><input class="value" type="text" value="" /></p>
					<p class="radiogroup sydney_editor_p">
						<label class="sydney_editor_label"><input class="sydney_editor_input" id="f_heading" name="level" type="radio" value="1"/> ' . Sydney_Tools_Localization::_('Heading') . '</label>
						<label class="sydney_editor_label"><input class="sydney_editor_input" id="f_subheading" name="level" type="radio" value="2"/> ' . Sydney_Tools_Localization::_('Sub heading') . '</label>
						<label class="sydney_editor_label"><input class="sydney_editor_input" id="f_minorheading" name="level" type="radio" value="3"/> ' . Sydney_Tools_Localization::_('Minor heading') . ' (3)</label>
						<label class="sydney_editor_label"><input class="sydney_editor_input" id="f_minorheading4" name="level" type="radio" value="4"/> ' . Sydney_Tools_Localization::_('Minor heading') . ' (4)</label>
						<label class="sydney_editor_label"><input class="sydney_editor_input" id="f_minorheading5" name="level" type="radio" value="5"/> ' . Sydney_Tools_Localization::_('Minor heading') . ' (5)</label>
						<label class="sydney_editor_label"><input class="sydney_editor_input" id="f_minorheading6" name="level" type="radio" value="6"/> ' . Sydney_Tools_Localization::_('Minor heading') . ' (6)</label>
					</p>
					<p class="buttons sydney_editor_p"><a class="button sydney_editor_a" href="save">Save as actual content</a> <a class="button sydney_editor_a" href="save-draft">' . Sydney_Tools_Localization::_('Save as draft') . '</a>  <a class="button muted sydney_editor_a" href="cancel">' . Sydney_Tools_Localization::_('Cancel') . '</a></p>
				</div>';

        return $toReturn;
    }
}

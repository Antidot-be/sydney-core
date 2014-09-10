<?php
require_once('Zend/View/Helper/Abstract.php');

/**
 * Helper showing the text HTML editor for editing page content
 *
 * @package Adminpages
 * @subpackage ViewHelper
 * @author Arnaud Selvais
 * @since 08/06/09
 * @todo Implement the translation method here
 */
class Adminpages_View_Helper_EditorText extends Zend_View_Helper_Abstract
{
    /**
     * Helper main function
     * @return String HTML to be inserted in the view
     */
    public function EditorText()
    {
        $pathToConfigStyles = Sydney_Tools::getLocalPath() . '/config/ckeditor.styles.ini';
        $pathToConfigJsTemplate = Sydney_Tools::getLocalPath() . '/config/ckeditor.jstemplate.ini';

        $toReturn = '<script>
                            var publicCss = "' . Zend_Registry::get("config")->ckeditor->css . '";'
            . '</script><!-- ' . $pathToConfigStyles . ' -->';

        if (file_exists($pathToConfigStyles) && $contentStyles = file_get_contents($pathToConfigStyles)) {
            $addStyleCss = '<script> var addStyleCss = ' . $contentStyles . ';</script>';
        } else {
            $addStyleCss = '<script> var addStyleCss = false;</script>';
        }

        if (file_exists($pathToConfigJsTemplate) && $contentTemplate = file_get_contents($pathToConfigJsTemplate)) {
            $toReturn .= '<script type="text/javascript">' . $contentTemplate . '</script>';
        }

        $toReturn .= $addStyleCss . '<div class="editor text sydneyeditor">
					<textarea class="texteditor">

					</textarea>
					<p class="buttons sydney_editor_p">
						<a class="button sydney_editor_a" href="save">' . Sydney_Tools_Localization::_('Save as actual content') . '</a> <a class="button sydney_editor_a" href="save-draft">' . Sydney_Tools_Localization::_('Save as draft') . '</a>
						<a class="button muted sydney_editor_a" href="cancel">' . Sydney_Tools_Localization::_('Cancel') . '</a>
					</p>
				</div>';

        return $toReturn;
    }
}

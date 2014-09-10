<?php
require_once('Zend/View/Helper/Abstract.php');

/**
 * Helper showing the text content
 *
 * @package Adminpages
 * @subpackage ViewHelper
 * @author Arnaud Selvais
 * @since 08/06/09
 * @todo Implement the translation method here
 */
class Adminpages_View_Helper_ContentText extends Zend_View_Helper_Abstract
{
    /**
     * Helper main function
     * @param $actionsHtml String HTML code showing the action buttons
     * @param $content String The content of this element
     * @param $dbId Int DB id of the object
     * @param $order Int order of this item in the DB
     * @param $params Array parameters (if any)
     * @return String HTML to be inserted in the view
     */
    public function ContentText($actionsHtml = '', $content = '', $dbId = 0, $order = 0, $params = array(), $moduleName = 'adminpages', $pagstructureId = 0, $sharedInIds = '')
    {
        $eventsInfo = SafactivitylogOp::getAuthorNLastEditorForContent($dbId, $moduleName);

        $toReturn = '<li class="' . $params['addClass'] . ' sydney_editor_li" editclass="text" dbid="' . $dbId . '" dborder="' . $order . '" pagstructureid="' . $pagstructureId . '" sharedinids="' . $sharedInIds . '">
		' . $actionsHtml . '
		<div class="content clearfix2">
				' . $content . '
		</div>
		<p class="lastUpdatedContent sydney_editor_p">' . $eventsInfo['firstEvent'] . '<br />' . $eventsInfo['lastEvent'] . '</p>
		</li>';

        return $toReturn;
    }
}

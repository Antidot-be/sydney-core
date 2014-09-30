<?php

/**
 * Helper showing the heading content
 */
class Adminpages_View_Helper_ContentHeading extends Zend_View_Helper_Abstract
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
    public function ContentHeading($actionsHtml = '', $content = '', $dbId = 0, $order = 0, $params = array('level' => 1), $moduleName = 'adminpages', $pagstructureId = 0, $sharedInIds = '')
    {

        $eventsInfo = SafactivitylogOp::getAuthorNLastEditorForContent($dbId, $moduleName);

        $toReturn = '
        <li
            class="' . $params['addClass'] . ' sydney_editor_li"
            type="h' . $params['level'] . '"
            editclass="heading"
            dbid="' . $dbId . '"
            dborder="' . $order . '"
            data-content-type="heading-block"
            pagstructureid="' . $pagstructureId . '"
            sharedinids="' . $sharedInIds . '">
		' . $actionsHtml . '
		<div class="content clearfix2">
			<h' . $params['level'] . ' class="sydney_editor_h' . $params['level'] . '">' . $content . '</h' . $params['level'] . '>
		</div>
		<p class="lastUpdatedContent sydney_editor_p">' . $eventsInfo['firstEvent'] . '<br />' . $eventsInfo['lastEvent'] . '</p>
		</li>';

        return $toReturn;
    }

}

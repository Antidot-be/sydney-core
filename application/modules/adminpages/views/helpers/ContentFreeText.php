<?php

/**
 * Helper showing the free text content
 */
class Adminpages_View_Helper_ContentFreeText extends Zend_View_Helper_Abstract
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
    public function ContentFreeText(
        $actionsHtml = '',
        $content = '',
        $dbId = 0,
        $order = 0,
        $params = array('language' => 'php'),
        $moduleName = 'adminpages',
        $pagstructureId = 0,
        $sharedInIds = ''
    )
    {
        $eventsInfo = SafactivitylogOp::getAuthorNLastEditorForContent($dbId, $moduleName);

        $secureContent = htmlspecialchars($content);
        $contentToDisplay = nl2br(htmlspecialchars(substr($content, 0, 200) . ' ...'));

        $toReturn = '<li class="' . $params['addClass'] . ' sydney_editor_li"
						type="' . $params['language'] . '"
                        data-content-type="plain-text-html-block"
						editclass="freetext"
						dbid="' . $dbId . '"
						dborder="' . $order . '"
						pagstructureid="' . $pagstructureId . '"
						sharedinids="' . $sharedInIds . '">
			' . $actionsHtml . '
			<div class="content">
				' . $contentToDisplay . '
			</div>
			<textarea id="textAreaFreeText" style="display:none;">' . $secureContent . '</textarea>
			<p class="lastUpdatedContent sydney_editor_p">' . $eventsInfo['firstEvent'] . '<br />' . $eventsInfo['lastEvent'] . '</p>
		</li>';

        return $toReturn;
    }
}

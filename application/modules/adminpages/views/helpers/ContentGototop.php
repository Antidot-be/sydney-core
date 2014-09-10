<?php

/**
 * Description of ContentBacktotop
 *
 * @author Frederic Arijs
 * @since  05-sept.-2011
 */
class Adminpages_View_Helper_ContentGototop extends Zend_View_Helper_Abstract
{
    public function ContentGototop($actionsHtml = '', $content = '', $dbId = 0,
                                   $order = 0, $params = array(),
                                   $moduleName = 'adminpages', $pageStructureId = 0, $sharedInIds = '')
    {
        $eventsInfo = SafactivitylogOp::getAuthorNLastEditorForContent($dbId, $moduleName);
        $toReturn = '	<li class="' . $params['addClass'] . ' sydney_editor_li" editclass="gototop" dbid="' . $dbId . '" dborder="' . $order . '" pagstructureid="' . $pageStructureId . '" sharedinids="' . $sharedInIds . '">';
        $toReturn .= $actionsHtml . '<div class="content"> ';
        $toReturn .= '<p class="sydney_editor_p" align="right"><a href="javascript:scroll(0,0);" class="backtotop sydney_editor_a">'
            . Zend_Registry::get('Zend_Translate')->_('gototop')
            . '</a></p>';
        $toReturn .= '</div>';
        $toReturn .= '<p class="lastUpdatedContent sydney_editor_p">' . $eventsInfo['firstEvent'] . '<br />' . $eventsInfo['lastEvent'] . '</p>
        </li>';

        return $toReturn;
    }
}

<?php

/**
 * Description of ContentGototop
 *
 * @author Frederic Arijs
 * @since  05-sept.-2011
 */
class Publicms_View_Helper_ContentGototop extends Zend_View_Helper_Abstract
{
    public function ContentGototop($actionsHtml = '', $content = '', $dbId = 0, $order = 0, $params = array(), $pagstructureId = 0)
    {
        $toReturn = '<div class="' . $params['addClass'] . '">';
        $toReturn .= $actionsHtml . '<div class="content"> ';
        $toReturn .= '<p align="right"><a href="javascript:scroll(0,0);" class="backtotop">'
            . Zend_Registry::get('Zend_Translate')->_('gototop')
            . '</a></p>';
        $toReturn .= '</div></div>';

        return $toReturn;
    }
}

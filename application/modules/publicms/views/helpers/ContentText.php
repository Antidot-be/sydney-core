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
class Publicms_View_Helper_ContentText extends Zend_View_Helper_Abstract
{
    /**
     * Helper main function
     * @param string $actionsHtml HTML code showing the action buttons
     * @param string $content The content of this element
     * @param int $dbId DB id of the object
     * @param int $order Order of this item in the DB
     * @param array $params Parameters (if any)
     * @param int $pagstructureId
     * @return string HTML to be inserted in the view
     */
    public function ContentText($actionsHtml = '', $content = '', $dbId = 0, $order = 0, $params = array(), $pagstructureId = 0)
    {
        $toReturn = '';
        if (!empty($content)) {
            $toReturn = $actionsHtml . ' ' . $content;
        }

        return $toReturn;
    }
}

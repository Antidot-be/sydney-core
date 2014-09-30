<?php

/**
 * Helper showing the freetext content
 */
class Publicms_View_Helper_ContentFreeText extends Zend_View_Helper_Abstract
{
    /**
     * Helper main function
     * @param string $actionsHtml HTML code showing the action buttons
     * @param string $content The content of this element
     * @param int $dbid DB id of the object
     * @param int $order Order of this item in the DB
     * @param array $params Parameters (if any)
     * @param string $moduleName
     * @param int $pagstructure_id
     * @return string HTML to be inserted in the view
     */
    public function ContentFreeText(
        $actionsHtml = '',
        $content = '',
        $dbid = 0,
        $order = 0,
        $params = array('language' => 'php'),
        $moduleName = 'adminpages',
        $pagstructureId = 0
    )
    {
        return $content;
    }
}

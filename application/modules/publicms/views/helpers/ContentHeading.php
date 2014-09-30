<?php

/**
 * Helper showing the heading content
 */
class Publicms_View_Helper_ContentHeading extends Zend_View_Helper_Abstract
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
    public function ContentHeading($actionsHtml = '', $content = '', $dbId = 0, $order = 0, $params = array('level' => 1), $pagstructureId = 0)
    {
        $toReturn = '';
        if (!empty($content)) {
            $level = $params['level'];

            $toReturn = <<<END
                $actionsHtml
                <h$level>$content</h$level>
END;
        }

        return $toReturn;
    }
}

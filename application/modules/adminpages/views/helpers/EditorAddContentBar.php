<?php

/**
 * Helper showing the add content bar with the possible actions
 */
class Adminpages_View_Helper_EditorAddContentBar extends Zend_View_Helper_Abstract
{

    public function __construct()
    {
    }

    /**
     * Helper main function
     * @return String HTML to be inserted in the view
     */
    public function EditorAddContentBar()
    {
        $toReturn = '<strong>Add content:</strong> ';
        $htmlContentTab = array();

        $registry = Zend_Registry::getInstance();
        foreach($registry->get('customhelpers') as $identifier => $customHelper){
            $htmlContentTab[] =  '<a class="sydney_editor_a" href="#" data-content-type="'.$identifier.'">' . $customHelper->getLabel() . '</a>';
        }

        $toReturn .= implode(' - ', $htmlContentTab);

        return $toReturn;
    }
}

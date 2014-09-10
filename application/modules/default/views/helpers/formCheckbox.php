<?php
require_once('Zend/View/Helper/Abstract.php');

/**
 * Helper showing the heading content
 *
 * @package Adminpages
 * @subpackage ViewHelper
 * @author Arnaud Selvais
 * @since 08/06/09
 * @todo Implement the translation method here
 */
class Default_View_Helper_formCheckbox extends Zend_View_Helper_Abstract
{
    /**
     *
     * Enter description here ...
     * @param unknown_type $option
     */
    public function formCheckbox($option)
    {
        return 'text';
    }
}

<?php
require_once('Zend/View/Helper/Abstract.php');

/**
 * Helper showing a basic menu
 *
 * @package Adminpages
 * @subpackage ViewHelper
 * @author Arnaud Selvais
 * @since 08/06/09
 * @todo implement content caching in this helper
 */
class Publicms_View_Helper_MenuBasic extends Zend_View_Helper_Abstract
{
    /**
     * returns the structure in an HTML form
     * The format of the array to pass to this helper is :
     * <code>
     * Array
     * (
     *     [96] => Array
     *      (
     *       [id] => 96
     *       [label] => Welcome
     *       [isCollapsed] =>
     *       [status] => draft
     *       [ishome] => 1
     *       [kids] => Array
     *           (
     *      [...]
     * </code>
     * @return String
     * @param object $aStrructure [optional]
     */
    public function MenuBasic($aStrructure = array(), $pageid = null)
    {
        $r = '<li>';
        foreach ($aStrructure as $e) {
            $r .= '
	        <ul><a href="/publicms/index/view/page/' . $e['id'] . '">' . $e['label'] . '</a></ul>
			';
        }
        $r .= '</li>';

        return $r;
    }

}

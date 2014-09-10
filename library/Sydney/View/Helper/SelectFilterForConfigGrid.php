<?php
require_once('Zend/View/Helper/Abstract.php');

/**
 * Helper showing a select box containing filters for a datatable
 *
 * @package Adminpages
 * @subpackage ViewHelper
 * @author Arnaud Selvais
 * @since
 */
class Sydney_View_Helper_SelectFilterForConfigGrid extends Zend_View_Helper_Abstract
{
    public function selectFilterForConfigGrid($model = 'Safmodules', $where = null, $showEmptyChoice = true)
    {
        $h = '';
        $selectedVal = '';
        if (isset($_POST) && isset($_POST['filteron']) && ($_POST['filteron'] == $model) && isset($_POST['filtervalue'])) {
            $selectedVal = $_POST['filtervalue'];
        }
        $sDB = new $model;
        $h .= '<form name="filterform" method="post">';
        $h .= '<input type="hidden" name="filteron" value="' . $model . '">';
        $h .= '<select name="filtervalue" onChange="$(this).parent().submit();">';
        if ($showEmptyChoice) {
            $h .= '<option value="">------------</option>';
        }
        foreach ($sDB->fetchAll($where) as $e) {
            if ($selectedVal == $e->id) {
                $s = ' selected ';
            } else {
                $s = ' ';
            }
            $h .= '<option value="' . $e->id . '"' . $s . '>' . $e->label . '</option>';
        }
        $h .= '</select></form>';

        return $h;

    }
}

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
class Sydney_View_Helper_SelectFilterForDataTable extends Zend_View_Helper_Abstract
{
    public function selectFilterForDataTable($datatable = 'Formlistvals', $filtermodel = 'Formlistvalsgroups', $where = null)
    {
        $sDB = new $filtermodel;
        $htr = '';
        $htr .= '<select name="' . $filtermodel . '" class="datatablefilter oDT-filter" onChange="' . $datatable . 'DataTable.oDT.applyFilters();">';
        $htr .= '<option value="">-----</option>';
        foreach ($sDB->fetchAll($where) as $e) {
            $htr .= '<option value="' . $e->id . '">' . $e->label . '</option>';
        }
        $htr .= '</select>';

        return $htr;
    }
}

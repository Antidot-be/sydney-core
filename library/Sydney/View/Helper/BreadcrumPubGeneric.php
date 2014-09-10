<?php
require_once('Zend/View/Helper/Abstract.php');

/**
 * Helper showing a basic menu
 *
 * @package Adminpages
 * @subpackage ViewHelper
 * @author Arnaud Selvais
 * @since
 */
class Sydney_View_Helper_BreadcrumPubGeneric extends Zend_View_Helper_Abstract
{
    public function breadcrumPubGeneric($breadCrumData, $sep = '', $fromelement = 1, $linkelements = true)
    {
        $breadcrumLabels = '';
        if (isset($breadCrumData) && is_array($breadCrumData)) {
            foreach ($breadCrumData as $v) {
                $i++;
                if ($i == 1) {
                    $rootpid = $v['id'];
                }
                if ($i >= $fromelement) {
                    $breadcrumLabels .= ' ';
                    if ($linkelements) {
                        $breadcrumLabels .= '<a href="' . $this->view->sydneyUrl($v['id'], $v['label']) . '">';
                    }
                    $breadcrumLabels .= $v['label'];
                    if ($linkelements) {
                        $breadcrumLabels .= '</a>';
                    }
                    if ($i < count($breadCrumData)) {
                        $breadcrumLabels .= $sep;
                    }
                }
            }
        }

        return $breadcrumLabels;
    }

}

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
class Sydney_View_Helper_MenuSydneypubGeneric extends Zend_View_Helper_Abstract
{
    private $gotthenode = false;
    private $currentpids = array();

    /**
     * Returns HTML to create a navigation menu.
     *
     * @param array $strructure Structure in an array
     * @param array $pageids BreadCrum data array
     * @param int $menuid ID of the menu to show (ex: filter on the footer menu)
     * @param boolean $subitems Should we integrate the sub items in the list
     * @param int $frompageid Take this id as the origin node
     */
    public function menuSydneypubGeneric($strructure = array(), $pageids = array(), $menuid = 1, $subitems = true, $frompageid = null)
    {
        $this->gotthenode = false;
        $this->currentpids = array();

        return $this->_looper($strructure, $pageids, $menuid, $subitems, $frompageid);
    }

    private function _looper($strructure = array(), $pageids = array(), $menuid = 1, $subitems = true, $frompageid = null)
    {
        $r = '';
        if (count($this->currentpids) == 0 && is_array($pageids)) {
            foreach ($pageids as $l) {
                $this->currentpids[] = $l['id'];
            }
        }
        if ($frompageid != null && $this->gotthenode == false) {
            $aStrructure = $this->getNodesPerId($strructure, $frompageid);
        } else {
            $aStrructure = $strructure;
        }

        if (is_array($aStrructure)) {
            foreach ($aStrructure as $e) {
                if (in_array($menuid, $e['menusid'])) {
                    // print $e['id'].' - '; print_r($this->currentpids);
                    if (in_array($e['id'], $this->currentpids)) {
                        $lef = 'selected';
                    } else {
                        $lef = '';
                    }
                    $r .= '
					<li class="' . $lef . '"><a href="/publicms/index/view/page/' . $e['id'] . '"><span>' . $e['label'] . '</span></a>';
                    if (is_array($e['kids']) && count($e['kids']) > 0 && $subitems) {
                        $r .= '<ul>';
                        $r .= $this->_looper($e['kids'], $pageids, $menuid, $subitems, $frompageid);
                        $r .= '</ul>';
                    }
                    $r .= '</li>';
                }
            }
        }

        return $r;
    }

    /**
     *
     * @param array $aStrructure
     * @param int $frompageid
     */
    public function getNodesPerId($aStrructure, $frompageid)
    {
        if (isset($aStrructure[$frompageid])) {
            $this->gotthenode = true;

            return $aStrructure[$frompageid]['kids'];
        } else {
            foreach ($aStrructure as $e) {
                return $this->getNodesPerId($e['kids'], $frompageid);
            }
        }
    }

}

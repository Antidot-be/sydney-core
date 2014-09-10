<?php
require_once('Zend/View/Helper/Abstract.php');

/**
 * Formats a date from a MySQL date time to a european date
 * @author arnaud
 *
 */
class Sydney_View_Helper_DateMySQL2date extends Zend_View_Helper_Abstract
{
    /**
     * Formats a date from a MySQL date time to a european date
     * @param String $d MySQL date
     */
    public function DateMySQL2date($d)
    {
        $dd = preg_split("/ /", $d);
        $d = preg_split("/-/", $dd[0]);

        return $d[2] . '/' . $d[1] . '/' . $d[0];
    }
}

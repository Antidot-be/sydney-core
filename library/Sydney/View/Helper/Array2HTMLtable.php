<?php
require_once('Zend/View/Helper/Abstract.php');

class Sydney_View_Helper_Array2HTMLtable extends Zend_View_Helper_Abstract
{
    /**
     * Converts an array in an HTML table
     * @param Array $a Array of data
     * @param Mixed $head Null or an array containing the header labels
     * @param array $totalcols Array of name of cols we would like to calculate a total on (if any)
     * @return String HTML table representation of the data
     */
    public function Array2HTMLtable($data = null, $head = null, $totalcols = array(), $avr = 0, $tableclass = '')
    {
        $warp1 = '<table class="' . $tableclass . '">';
        $header = '';
        $body = '';
        $warp2 = '</table>';
        $total = '';
        $totalvals = array();

        if ($data == null) {
            return 'Data is null...';
        }
        if (!is_array($data)) {
            try {
                $a = $a->toArray();
            } catch (Exception $e) {
                return 'Could not convert the data...';
            }
        } else {
            $a = $data;
        }
        if (count($a) == 0) {
            return 'No data...';
        }

        $i = 0;
        foreach ($a as $line) {
            $body .= '<tr>';
            if ($i == 0) {
                $header .= '<tr>';
            }
            if ($i + 1 == count($a)) {
                $total .= '<tr>';
            }
            $u = 0;
            foreach ($line as $k => $v) {
                if ($i == 0) {
                    if (is_array($head)) {
                        $header .= '<th>' . $head[$u] . '</th>';
                    } else {
                        $header .= '<th>' . $k . '</th>';
                    }
                    $u++;
                }
                if (count($totalcols) > 0 && in_array($k, $totalcols)) {
                    $totalvals[$k] += $v;
                    $body .= '<td style="text-align:right;">' . number_format($v, 2) . '</td>';
                } else {
                    $body .= '<td>' . $v . '</td>';
                }
                if ($i + 1 == count($a)) {
                    if (count($totalcols) > 0) {
                        if (in_array($k, $totalcols)) {
                            if ($avr > 0) {
                                $vvv = number_format($totalvals[$k], 2) . "<br>Avr. $avr: " . number_format($totalvals[$k] / $avr, 2);
                            } else {
                                $vvv = number_format($totalvals[$k], 2);
                            }
                            $total .= '<th style="text-align:right;">' . $vvv . '</th>';

                        } else {
                            $total .= '<th></th>';
                        }
                    }
                }

            }
            if ($i == 0) {
                $header .= '</tr>';
            }
            if ($i + 1 == count($a)) {
                $total .= '</tr>';
            }
            $body .= '</tr>';
            $i++;
        }
        if (count($totalcols) == 0) {
            $total = '';
        }

        return $warp1 . $header . $body . $total . $warp2;
    }
}

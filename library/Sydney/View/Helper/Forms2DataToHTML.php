<?php
require_once('Zend/View/Helper/Abstract.php');

/**
 * Formating the adminforms2 data in a nice html (needs css though)
 * @author Arnaud Selvais
 * @since 5 Aug. 2011
 */
class Sydney_View_Helper_Forms2DataToHTML extends Zend_View_Helper_Abstract
{
    /**
     * Formating the adminforms2 data in a nice html (needs css though)
     * @param array $formData
     */
    public function Forms2DataToHTML($formData)
    {
        $html = '';
        $html .= '<div class="ICsumData">';
        foreach ($formData as $form) {
            $html .= '<div class="ICsumH2"><h2>' . $form['label'] . '</h2>';
            foreach ($form['kids'] as $dspgr) {
                $html .= '<div class="ICsumH3">';
                $html .= '<h3>' . $dspgr['label'] . '</h3>';
                $html .= '<table>';
                foreach ($dspgr['kids'] as $field) {
                    $html .= '<tr><th>' . $field['label'] . '</th><td>' . nl2br($field['value']) . '</td></tr>';
                }
                $html .= '</table></div>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        return $html;
    }

}

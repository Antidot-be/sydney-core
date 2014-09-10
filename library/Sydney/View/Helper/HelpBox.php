<?php
require_once('Zend/View/Helper/Abstract.php');

/**
 * Helper showing the structure of a site in a drop down box
 *
 * @package SydneyLibrary
 * @subpackage ViewHelper
 * @author Arnaud Selvais
 * @since 08/06/09
 * @todo Implement the translation method here
 */
class Sydney_View_Helper_HelpBox extends Zend_View_Helper_Abstract
{

    public function HelpBox($helpUrl = '/adminhelp/index/index/')
    {
        $html = '			<div class="helpbox" helpUrl="' . $helpUrl . '">
				<div class="helpContentIn"></div>
				<p class="buttons"><a id="helpboxhide" class="button muted" href="#">Hide this message</a></p>
			</div>';

        return $html;
    }
}

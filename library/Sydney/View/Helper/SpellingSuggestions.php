<?php
require_once('Zend/View/Helper/Abstract.php');

class Sydney_View_Helper_SpellingSuggestions extends Zend_View_Helper_Abstract
{
    /**
     * Suggestion of words coming from the pspell dictionnary in french and english
     *
     * @param string $q Query string
     * @param string $jsfunc The JS function to launch and pass the query string to
     */
    public function spellingSuggestions($q, $jsfunc = 'sugg')
    {
        $toret = '';
        if ($q == '') {
            return ' ... No suggestion possible, the query is empty ... ';
        }
        if (function_exists(pspell_new)) {
            $ss = 0;
            foreach (array('en' => 'English', "fr" => 'French') as $k => $v) {
                $pspellLink = pspell_new($k);
                $suggs = pspell_suggest($pspellLink, $q);
                if (count($suggs) > 0) {
                    $ss++;
                    $toret .= "<b>In " . $v . "</b> : ";
                    foreach ($suggs as $sug) {
                        $toret .= '<a href="javascript:' . $jsfunc . '(\'' . addslashes($sug) . '\')">' . htmlentities($sug) . '</a> &nbsp; ';
                    }
                    $toret .= "<br>";
                }
            }
            if ($ss == 0) {
                $toret .= '... we could not find anything in our dictionnaries ...';
            }
        } else {
            return ' !!! ERROR: the pspell module is not installed !!! ';
        }

        return $toret;
    }
}

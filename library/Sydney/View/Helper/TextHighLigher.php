<?php
require_once('Zend/View/Helper/Abstract.php');

class Sydney_View_Helper_TextHighLigher extends Zend_View_Helper_Abstract
{
    /**
     * Highlight a part of text using a span with style.
     * This can be used to highlight a part of text after a search query.
     * @param $txt The full text
     * @param $prt The string to highlight
     * @param string $highst The class to add to the span (typically a yellow background defined in the css)
     * @return mixed
     */
    public function textHighLigher($txt, $prt, $highst = 'highlightyel')
    {
        return preg_replace('/(.*)(' . $prt . ')(.*)/i', '\1<span class="' . $highst . '">\2</span>\3', $txt);
    }
}

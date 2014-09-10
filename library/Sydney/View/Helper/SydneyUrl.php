<?php

/**
 * Description of SydneyUrl
 *
 * @author Frederic Arijs
 * @since 26-janv.-2011
 */
class Sydney_View_Helper_SydneyUrl extends Zend_View_Helper_Url
{

    /**
     * @param int $id
     * @param string $label
     * @param string $type
     * @return string
     */
    public function SydneyUrl($id, $label, $type = 'page')
    {
        return Sydney_Tools_Friendlyurls::getFriendlyUrl($id, $label, $type, $this);
    }
}

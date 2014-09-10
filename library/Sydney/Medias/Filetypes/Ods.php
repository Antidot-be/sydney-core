<?php

/**
 *
 * @author Arnaud Selvais
 * @since 13/08/09
 * @copyright Antidot Inc. / S.A.
 */
class Sydney_Medias_Filetypes_Ods extends Sydney_Medias_Filetypes_Abstract
{
    protected $deficon = 'ods_128.png';

    /**
     * Displays the thumbnail on the STDOUT
     * @return Boolean
     */
    public function showThumb()
    {
        return parent::showThumb();
    }

    /**
     * Returns the current image size in an array
     * @return array
     */
    public function getSize()
    {
        return false;
    }
}

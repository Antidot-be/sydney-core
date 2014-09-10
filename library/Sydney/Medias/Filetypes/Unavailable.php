<?php

/**
 *
 * @author Arnaud Selvais
 * @since 13/08/09
 * @copyright Antidot Inc. / S.A.
 */
class Sydney_Medias_Filetypes_Unavailable extends Sydney_Medias_Filetypes_Abstract
{
    protected $deficon = 'unavailable_128.png';

    public function showThumb()
    {
        return parent::showThumb();
    }

    public function getSize()
    {
        return parent::getSize();
    }

}

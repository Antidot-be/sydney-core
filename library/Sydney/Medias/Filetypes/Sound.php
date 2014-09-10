<?php

/**
 *
 * @author Arnaud Selvais
 * @since 13/08/09
 * @copyright Antidot Inc. / S.A.
 */
class Sydney_Medias_Filetypes_Sound extends Sydney_Medias_Filetypes_Abstract
{
    protected $deficon = 'audio_128.png';

    public function showThumb()
    {
        return parent::showThumb();
    }

    public function getSize()
    {
        return parent::getSize();
    }

    /**
     * Returns the information we could find on the file
     * @return Array
     */
    public function getFileinfo()
    {
        $toret = parent::getFileinfo();
        // get the ID3 data if it is an MP3
        if ($this->extension == 'MP3') {
            if (function_exists('id3_get_tag')) {
                try {
                    $id3sata = id3_get_tag($this->fullpath);
                    foreach ($id3sata as $key => $val) {
                        if ($key == 'genre') {
                            $toret[('mp3.' . $key)] = id3_get_genre_name($val);
                        } else {
                            $toret[('mp3.' . $key)] = $val;
                        }
                    }
                } catch (Exception $e) {
                }
            }
        }

        return $toret;
    }

}

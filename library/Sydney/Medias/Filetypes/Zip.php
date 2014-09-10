<?php

/**
 *
 * @author Arnaud Selvais
 * @since 13/08/09
 * @copyright Antidot Inc. / S.A.
 */
class Sydney_Medias_Filetypes_Zip extends Sydney_Medias_Filetypes_Abstract
{
    protected $deficon = 'zip_128.png';

    /**
     * (non-PHPdoc)
     * @see Sydney_Medias_Filetypes_Abstract::showThumb()
     */
    public function showThumb()
    {
        return parent::showThumb();
    }

    /**
     * (non-PHPdoc)
     * @see Sydney_Medias_Filetypes_Abstract::getSize()
     */
    public function getSize()
    {
        return parent::getSize();
    }

    /**
     * returns an array of the files found in the ZIP archive
     * @return array
     */
    public function getZipContent()
    {
        $out = array();
        $open = zip_open($this->fullpath);
        while ($zip = zip_read($open)) {
            $file = zip_entry_name($zip);
            if (!preg_match('/__MACOSX/', $file)) {
                $out[] = $file;
            }
        }
        zip_close($open);

        return $out;
    }

    /**
     *
     */
    public function unzipToDir($fullpath = null)
    {
        if ($fullpath != null && is_dir($fullpath)) {
            $open = zip_open($this->fullpath);
            while ($zipEntry = zip_read($open)) {
                $file = zip_entry_name($zipEntry);
                if (!preg_match('/__MACOSX/', $file)) {
                    if (strpos(zip_entry_name($zipEntry), DIRECTORY_SEPARATOR) !== false) {
                        $file = substr(zip_entry_name($zipEntry), strrpos(zip_entry_name($zipEntry), DIRECTORY_SEPARATOR) + 1);
                        if (strlen(trim($file)) > 0) {
                            @file_put_contents($fullpath . "/" . $file, zip_entry_read($zipEntry, zip_entry_filesize($zipEntry)));
                        }
                    } else {
                        file_put_contents($fullpath . '/' . $file, zip_entry_read($zipEntry, zip_entry_filesize($zipEntry)));
                    }
                }
            }
            zip_close($open);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Prints the raw data to the STDOUT
     * @return void
     */
    public function getRawFile($filename = false, $forceDownload = false, $extf = null, $automime = true)
    {
        if (!$filename) {
            $filename = $this->fullpath;
        }

        header('Content-Description: File Transfer');
        header('Content-type: application/zip');
        header('Content-Disposition: attachment; filename="' . $this->basename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        ob_end_flush();
        readfile($filename);
    }
}

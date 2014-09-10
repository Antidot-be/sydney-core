<?php

/**
 * Utilities for working on / with files
 *
 */
class Sydney_Tools_File extends Sydney_Tools
{
    /**
     *
     * Simply writes some content into a file
     *
     * @param string $content Content
     * @param string $file Path to file (and filename), if nothing here, the file will be written in the instance cache dir
     */
    public static function writetofile($content = '', $file = '')
    {
        if ($content != '') {
            try {
                if ($file == '') {
                    $file = self::getCachePath() . '/tmp_writetofile.txt';
                }
                $fh = fopen($file, 'w');
                fwrite($fh, $content);
                fclose($fh);

                return true;
            } catch (Exception $e) {
                return false;
            }
        }
    }

    /**
     * ZIPs files (from an array) into a zip file and return the data associated with the
     * created ZIP.
     *
     * @param string $filename ZIP fullpath and name (we want to create)
     * @param array $bagelems Array of files in a format like the one produced by Filfiles() class (we need the path and filename props).
     */
    public static function zipfiles($filename = './tempname.zip', $bagelems = array())
    {
        $fsp = preg_split('/\//', $filename);
        $filenamebase = $fsp[(count($fsp) - 1)];
        $toret = array(
            'log'          => array(),
            'filefullpath' => $filename,
            'filename'     => $filenamebase,
            'zipinfo'      => array()
        );
        $zip = new ZipArchive();
        if (count($bagelems) > 0) {
            if ($zip->open($filename, ZIPARCHIVE::CREATE) !== true) {
                $toret['log'][] = "cannot open $filename";
            } else {
                foreach ($bagelems as $me) {
                    $zip->addFile($me['path'] . '/' . $me['filename'], $me['filename']);
                }
                $toret['zipinfo']["numfiles"] = $zip->numFiles;
                $toret['zipinfo']["status"] = $zip->status;
            }
        } else {
            $toret['log'][] = 'Nothing in the file array';
        }
        $zip->close();
        $fp = fopen($filename, "r");
        $toret['fstat'] = array_slice(fstat($fp), 13);
        fclose($fp);

        return $toret;
    }

}

<?php

/**
 * This factory will create and return a file type
 *
 * @author Arnaud Selvais
 * @since 13/08/09
 * @copyright Antidot Inc. / S.A.
 */
class Sydney_Medias_Filetypesfactory
{

    static private $errors = array();

    /**
     *
     * @return Sydney_Medias_Filetypes_Abstract
     * @param string $fullpath
     * @param Zend_Db_Table_Row $fdb The optional row from the DB
     * @return bool
     */
    static public function createfiletype($fullpath, $fdb = null)
    {
        $pi = pathinfo($fullpath);
        $ext = strtoupper($pi['extension']);
        $ft = Sydney_Medias_Utils::getFileType($ext);
        if (!file_exists($fullpath)) {
            $ft = Sydney_Medias_Utils::getFileType('unavailable');
        }
        if ($ft) {
            try {
                $fullpath = preg_replace("/\/\//", "/", $fullpath);
                $clsname = 'Sydney_Medias_Filetypes_' . ucfirst($ft[0]);

                return new $clsname($fullpath, null, $fdb);
            } catch (Exception $e) {
                self::$errors[] = 'Type Class can not be instanciated. I guess it does not exist.';

                return false;
            }
        } else {
            self::$errors[] = 'The file type is not supported.';

            return false;
        }
    }
}

<?php

/**
 * Utilities for directories info and manipulation
 *
 */
class Sydney_Tools_Dir extends Sydney_Tools
{
    /**
     *
     * @param unknown_type $dirPath
     * @param unknown_type $showdirs
     */
    public static function getDirList($dirPath, $showdirs = false)
    {
        $farr = array();
        if ($handle = opendir($dirPath)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && $file != ".svn") {
                    if (!is_dir("$dirPath/$file") && !$showdirs) {
                        $farr[] = $file;
                    } else {
                        $farr[] = $file;
                    }
                }
            }
            closedir($handle);
        }

        return $farr;
    }

    /**
     * removes dir and all its content
     * @param unknown_type $dir
     */
    public static function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        self::rrmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

}

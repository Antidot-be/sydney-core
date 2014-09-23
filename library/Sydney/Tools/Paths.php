<?php

/**
 * Utilities for returning paths and URLs
 *
 */
class Sydney_Tools_Paths extends Sydney_Tools
{

    private static $corePath;

    private static $webInstancePath;

    /**
     * We initialize the core path
     * @param $path string
     */
    public static function setCorePath($path)
    {
        self::$corePath = $path;
    }

    /**
     * @return string
     */
    public static function getCorePath()
    {
        return self::$corePath;
    }

    /**
     *
     * @param $path string
     */
    public static function setWebInstancePath($path)
    {
        self::$webInstancePath = $path;
    }

    /**
     *
     * @return string
     */
    public static function getWebInstancePath()
    {
        return self::$webInstancePath;
    }

    /**
     *
     */
    public static function getRootUrlCdn()
    {
        return Zend_Registry::get("config")->general->cdn;
    }

    /**
     * Get the jslibs directory
     * @return String the path to the directory containing the jslibs directory
     */
    public static function getJslibsPath()
    {
        $jsLibsPath = self::getCorePath() . '/webinstances/sydney/html/sydneyassets';

        if (is_dir($jsLibsPath)) {
            return str_replace('/sydneyassets', '', $jsLibsPath);
        }

        return Zend_Registry::get("config")->admin->jslibspath;
    }

    /**
     * Get the cache path for the current webinstance
     * @return string the cache path
     * @throws Zend_Exception
     */
    public static function getCachePath()
    {
        return self::getWebInstancePath() . DIRECTORY_SEPARATOR . Zend_Registry::get("config")->general->cachepath;
    }

    /**
     * Get the log directory path for the current webinstance
     * @return string the log path
     * @throws Zend_Exception
     */
    public static function getLogPath()
    {
        return self::getWebInstancePath() . DIRECTORY_SEPARATOR . Zend_Registry::get("config")->general->logdirpath;
    }

    /**
     * Get the custommap path for the current webinstance
     * @return string the custommap path
     * @throws Zend_Exception
     */
    public static function getCustomapPath()
    {
        return self::getWebInstancePath() . DIRECTORY_SEPARATOR . Zend_Registry::get("config")->general->customappath;
    }

    /**
     * Get the appdata path for the current webinstance
     * @return string the appdata path
     * @throws Zend_Exception
     */
    public static function getAppdataPath()
    {
        return self::getWebInstancePath() . DIRECTORY_SEPARATOR . Zend_Registry::get("config")->general->appdatapath;
    }

    /**
     * Get the local path for the current webinstance
     * @return string the local path
     */
    public static function getLocalPath()
    {
        return self::getWebInstancePath();
    }

    /**
     * Get the website URL
     * @return String   protocol and $_SERVER (ex: http://www.antidot.com)
     */
    public static function getRootUrl()
    {
        $protocol = "http://";
        if ($_SERVER['SERVER_PORT'] != 80) {
            $protocol = "https://";
        }

        return $protocol . $_SERVER['SERVER_NAME'];
    }

    /**
     *
     * @param $idFile
     * @return string
     */
    public static function getUrlAvatar($idFile)
    {
        $avatar = '';
        $rowFile = null;
        if ($idFile > 0) {
            $avatar = '/adminfiles/file/thumb/id/' . $idFile . '/ts/1/fn/' . $idFile . '.png';

            $oFile = new Filfiles;
            $rowFile = $oFile->getFileInfosById($idFile);
        }

        if (empty($avatar) || !is_object($rowFile)) {
            $avatar = self::getUrlDefaultAvatar();
        }

        return $avatar;
    }

    /**
     *
     */
    public static function getUrlDefaultAvatar()
    {
        return Zend_Registry::getInstance()->get('config')->general->cdn . '/sydneyassets/images/image64.png';
    }
}

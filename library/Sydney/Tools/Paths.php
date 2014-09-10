<?php

/**
 * Utilities for returning paths and URLs
 *
 */
class Sydney_Tools_Paths extends Sydney_Tools
{
    /**
     *
     */
    public static function getRootUrlCdn()
    {
        return Zend_Registry::get("config")->general->cdn;
    }

    /**
     *
     */
    public static function getRootPath()
    {
        return Zend_Registry::get("config")->general->rootPath;
    }

    /**
     * Get the jslibs directory
     * @return String the path to the directory containing the jslibs directory
     * @change GDE - 19/08/2013 - jslibs est d�plac� dans l'instance sydney
     */
    public static function getJslibsPath()
    {
        // On v�rifie si "jslibs" existe bien dans l'instance "com.antidot.sydney"
        $jsLibsPath = Sydney_Tools::getRootPath() . '/core/webinstances/sydney/html/sydneyassets';

        if (is_dir($jsLibsPath)) {
            // !!! � l'origine, c'est le dossier parent qui est retourn� par  "Zend_Registry::get("config")->admin->jslibspath"
            return str_replace('/sydneyassets', '', $jsLibsPath);
        }

        return Zend_Registry::get("config")->admin->jslibspath;
    }

    /**
     *  Get the cache path for the current webinstance
     * @return String the cache path
     */
    public static function getCachePath($isCoreWebinstance = false)
    {
        return Zend_Registry::get("config")->general->rootPath . DIRECTORY_SEPARATOR .
        ($isCoreWebinstance ? 'core' . DIRECTORY_SEPARATOR : '')
        . 'webinstances' . DIRECTORY_SEPARATOR
        . Zend_Registry::get("config")->general->webinstance . DIRECTORY_SEPARATOR
        . Zend_Registry::get("config")->general->cachepath;
    }

    /**
     *  Get the log directory path for the current webinstance
     * @return String the log path
     */
    public static function getLogPath($isCoreWebinstance = false)
    {
        return Zend_Registry::get("config")->general->rootPath . DIRECTORY_SEPARATOR .
        ($isCoreWebinstance ? 'core' . DIRECTORY_SEPARATOR : '')
        . 'webinstances' . DIRECTORY_SEPARATOR
        . Zend_Registry::get("config")->general->webinstance . DIRECTORY_SEPARATOR
        . Zend_Registry::get("config")->general->logdirpath;
    }

    /**
     *  Get the custommap path for the current webinstance
     * @return String the custommap path
     */
    public static function getCustomapPath($isCoreWebinstance = false)
    {
        return Zend_Registry::get("config")->general->rootPath . DIRECTORY_SEPARATOR .
        ($isCoreWebinstance ? 'core' . DIRECTORY_SEPARATOR : '')
        . 'webinstances' . DIRECTORY_SEPARATOR
        . Zend_Registry::get("config")->general->webinstance . DIRECTORY_SEPARATOR
        . Zend_Registry::get("config")->general->customappath;
    }

    /**
     *  Get the appdata path for the current webinstance
     * @return String the appdata path
     */
    public static function getAppdataPath($isCoreWebinstance = false)
    {
        return Zend_Registry::get("config")->general->rootPath . DIRECTORY_SEPARATOR .
        ($isCoreWebinstance ? 'core' . DIRECTORY_SEPARATOR : '')
        . 'webinstances' . DIRECTORY_SEPARATOR
        . Zend_Registry::get("config")->general->webinstance . DIRECTORY_SEPARATOR
        . Zend_Registry::get("config")->general->appdatapath;
    }

    /**
     *  Get the local path for the current webinstance
     * @return String the local path
     */
    public static function getLocalPath($isCoreWebinstance = false)
    {
        return Zend_Registry::get("config")->general->rootPath . DIRECTORY_SEPARATOR .
        ($isCoreWebinstance ? 'core' . DIRECTORY_SEPARATOR : '')
        . 'webinstances' . DIRECTORY_SEPARATOR
        . Zend_Registry::get("config")->general->webinstance;
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
     */
    public static function getYuiCompressorPath()
    {
        return Zend_Registry::get("config")->yui->compressor->path;
    }

    /**
     *
     * @param unknown_type $idFile
     */
    public static function getUrlAvatar($idFile)
    {
        $avatar = '';
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

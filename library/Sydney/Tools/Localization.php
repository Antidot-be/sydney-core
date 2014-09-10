<?php

/**
 * Tools for localization and translations
 *
 */
class Sydney_Tools_Localization extends Sydney_Tools
{
    /**
     * @var array Available languages
     */
    static public $authLanguages = array('en', 'zh', 'fr', 'nl', 'de');

    static private $authContentLanguages = null;

    /**
     *
     * @param unknown_type $word
     */
    public static function _($word)
    {
        $tempTranslation = Zend_Registry::getInstance()->get('Zend_Translate')->_($word);
        if (empty($tempTranslation)) {
            return $word;
        }

        return $tempTranslation;
    }

    public static function isMultiLanguage()
    {
        if (isset(Zend_Registry::get('config')->general->isMultiLanguage)) {
            return Zend_Registry::get('config')->general->isMultiLanguage;
        }

        return false;
    }

    /*
     * Methods content languages
     */

    /**
     *
     * @return boolean
     */
    public static function isMultiLanguageContentActive()
    {
        if (isset(Zend_Registry::get('config')->general->content->languages->active)) {
            return Zend_Registry::get('config')->general->content->languages->active;
        }

        return false;
    }

    public static function getDefaultContentLanguage()
    {
        return Zend_Registry::get('config')->general->content->languages->default;
    }

    public static function getContentLanguages()
    {
        if (!is_array(self::$authContentLanguages)) {
            self::$authContentLanguages = explode(',', Zend_Registry::get('config')->general->content->languages->list);
        }

        return self::$authContentLanguages;
    }

    public static function getCurrentContentLanguage()
    {
        $settingsNms = new Zend_Session_Namespace('appSettings');

        return $settingsNms->ContentLanguage;
    }

    public function isTranslationMustBeUpdated()
    {
        $settingsNms = new Zend_Session_Namespace('appSettings');

        return (self::isMultiLanguageContentActive() && $settingsNms->ContentLanguage === Zend_Registry::get('config')->general->content->languages->default);
    }

}

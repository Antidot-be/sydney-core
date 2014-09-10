<?php

/** Zend_Locale */
require_once 'Zend/Locale.php';

/**
 * @category   Sydney
 * @package    Sydney_Translate
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Sydney_Content_Translator
{

    const DISABLE_NOTICE = true;

    private $translate;

    public function getTableName()
    {
        return $this::TABLE_NAME;
    }

    public function __construct()
    {
        Zend_Translate::setCache(Zend_Cache::factory(
            'Core',
            'File',
            array('lifetime' => 7200, 'automatic_serialization' => true),
            array(
                'cache_dir'    => Sydney_Tools_Paths::getCachePath(),
                /*'file_name_prefix' => 'zendcache'.$this->getTableName(),*/
                'file_locking' => false,
            )
        ));

        $this->setTranslate(new Zend_Translate(
            array(
                'adapter'        => 'Sydney_Translate_Adapter_Db',
                'content'        => $this::TABLE_NAME,
                'locale'         => Sydney_Tools_Localization::getCurrentContentLanguage(),
                'disableNotices' => $this::DISABLE_NOTICE,
            )
        ));

        $this->getTranslate()->addTranslation(
            array(
                'adapter'        => 'Sydney_Translate_Adapter_Db',
                'content'        => $this::TABLE_NAME,
                'locale'         => Sydney_Tools_Localization::getCurrentContentLanguage(),
                'disableNotices' => $this::DISABLE_NOTICE,
            )
        );

    }

    public function translate($sourceLabel, $translateLabel, $elementId, $elementField = '')
    {
        if (!$this->isTranslationToBeUpdated()) {
            return $translateLabel;
        } else {
            // Update $translation
            $tranlation = new TranslationData();
            $tranlation->save($translateLabel, $this, Sydney_Tools_Localization::getCurrentContentLanguage(), $elementId, $elementField);

            return empty($sourceLabel) ? $translateLabel : $sourceLabel;
        }
    }

    private function isTranslationToBeUpdated()
    {
        $settingsNms = new Zend_Session_Namespace('appSettings');

        return (Sydney_Tools_Localization::isMultiLanguageContentActive() && $settingsNms->ContentLanguage !== Zend_Registry::get('config')->general->content->languages->default);
    }

    public function _($id, $defaultValue, $suffix = '')
    {
        if (!Sydney_Tools_Localization::isMultiLanguageContentActive()) {
            return $defaultValue;
        }
        $keyLabel = $id . '_' . $this::TABLE_NAME . (empty($suffix) ? '' : '_' . $suffix);
        $translation = $this->getTranslate()->_($keyLabel);

        if ($keyLabel === $translation || Sydney_Tools_Localization::getCurrentContentLanguage() === Sydney_Tools_Localization::getDefaultContentLanguage()) {
            return $defaultValue;
        }

        return $translation;
    }

    /**
     * @param Zend_Translate $translate
     */
    public function setTranslate(Zend_Translate $translate)
    {
        $this->translate = $translate;
    }

    /**
     * @return Zend_Translate
     */
    public function getTranslate()
    {
        return $this->translate;
    }

}

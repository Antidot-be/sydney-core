<?php

/**
 * Plugin for tidying the HTML output of ZF
 * @author arnaud
 *
 */
class Sydney_Controller_Plugin_Friendlyurls extends Zend_Controller_Plugin_Abstract
{
    /**
     *
     * @param array $config
     */
    public static function setConfig(array $config)
    {
        self::$_tidyConfig = $config;
    }

    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Plugin_Abstract::dispatchLoopShutdown()
     */
    public function dispatchLoopShutdown()
    {
        $request = $this->getRequest();

        if ($request->getModuleName() == 'publicms' && $request->getControllerName() == 'index' && $request->getActionName() == 'view') {
            $response = $this->getResponse();
            $html = $this->srFiles($this->srImages($response->getBody()));
            $response->setBody((string) $html);
        }
    }

    /**
     * Replaces the urls of images embedded in the WYSIWYG editor
     * @param string $html
     */
    protected function srImages($html)
    {
        return preg_replace('/"\/publicms\/file\/showimg\/dw\/([0-9]{1,4})\/id\/([0-9]{1,10})\/fn\/[0-9]{0,10}\.(png|jpg|gif)"/i', '"/S$1I$2.$3"', $html);
    }

    /**
     *
     * @param string $html
     */
    protected function srFiles($html)
    {
        return preg_replace('/"\/publicms\/file\/getrfile\/id\/([0-9]{1,10})"/i', '"/FILE-$1"', $html);
    }

}

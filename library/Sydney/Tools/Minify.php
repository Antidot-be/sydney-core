<?php

/**
 * Utilities for minifying JS and concatanate...
 *
 */
class Sydney_Tools_Minify extends Sydney_Tools
{

    /**
     *
     * @param unknown_type $type
     * @param Zend_View $zview
     * @param unknown_type $useCompression
     * @param unknown_type $useConcatenation
     * @param unknown_type $ctrl
     */
    public static function concatScripts($type, Zend_View $zview, $useConcatenation = true)
    {
        $r = '';
        $path = Sydney_Tools_Paths::getCorePath() . '/webinstances/sydney/html';

        $path2 = Sydney_Tools_Paths::getJslibsPath();

        $arrayOrig['jsOrig'] = Zend_Registry::getInstance()->get('config')->admin->js->orig;
        $arrayOrig['cssOrig'] = Zend_Registry::getInstance()->get('config')->admin->css->orig;
        $arrayLibs['jsLibs'] = Zend_Registry::getInstance()->get('config')->admin->js->libs;
        $arrayLibs['cssLibs'] = Zend_Registry::getInstance()->get('config')->admin->css->libs;
        /**
         * CSS + JS
         */
        // adding the main files from the jslibs
        foreach ($arrayLibs[$type . 'Libs'] as $file) {

            if ($useConcatenation) {
                if (file_exists($path2 . $file)) {
                    $tcnt = file_get_contents($path2 . $file);
                    if ($type == 'css' && preg_match('#sydneyassets\/jslibs\/jquery#', $file)) {
                        $needleStr = "#url\(\"images\/([a-zA-Z0-9_-]+)\.(png|gif|jpg)\"#";
                        $replaceStr = "url(" . Sydney_Tools_Paths::getRootUrlCdn() . "\/sydneyassets\/jslibs\/jquery\/css\/smoothness\/images\/$1.$2";
                        $tcnt = preg_replace($needleStr, $replaceStr, $tcnt);
                    }
                    if ($type == 'css' && preg_match('/skins\/sam/', $file)) {
                        $pht = preg_replace("/^\/assets\/yui\/build\/([A-z]{1,50})\/assets\/skins\/sam\/([A-z-_]{1,50}(\.css))/", "/sydneyassets/yui/build/\\1/assets/skins/sam/", $file);
                        $tcnt = str_replace('../../../../assets/skins/sam/', Sydney_Tools::getRootUrlCdn() . '/sydneyassets/yui/build/assets/skins/sam/', $tcnt);
                        $tcnt = preg_replace("/url\(([A-z0-9_-]{1,50})\.(png)\)/", "url(" . $pht . "\\1.\\2)", $tcnt);
                    }
                    $r .= "/* =========== File: " . $path2 . $file . " ================ */ \n\n\n" . $tcnt . "\n\n\n\n";
                }
            } else {
                if ($type == 'css') {
                    $zview->headLink()->appendStylesheet($file);
                } else {
                    // GDE - 19/08/2013 - On va chercher "jslibs" sur le cdn (com.antidot.sydney)
                    $zview->headScript()->appendFile(Zend_Registry::getInstance()->get('config')->general->cdn . $file, 'text/javascript');
                }
            }
        }

        // adding the main files
        foreach ($arrayOrig[$type . 'Orig'] as $file) {
            if ($useConcatenation) {
                $tcnt = file_get_contents($path . $file);
                $r .= "/* =========== File: " . $path . $file . " ================ */ \n\n\n" . $tcnt . "\n\n\n\n";
            } else {
                if ($type == 'css') {
                    $zview->headLink()->appendStylesheet($file);
                } else {
                    $zview->headScript()->appendFile(Sydney_Tools::getRootUrlCdn() . $file, 'text/javascript');
                }
            }
        }
        // END Foreach

        /**
         * JS
         */
        if ($type == 'js') {
            // adding the UI files
            $t = self::getDirList($path . '/sydneyassets/scripts/ui/');
            foreach ($t as $file) {
                if ($useConcatenation) {
                    $r .= "/* =========== File: " . $path . '/sydneyassets/scripts/ui/' . $file . " ================ */ \n\n\n" . file_get_contents($path . '/sydneyassets/scripts/ui/' . $file) . "\n\n\n\n";
                } else {
                    $zview->headScript()->appendFile(Sydney_Tools::getRootUrlCdn() . '/sydneyassets/scripts/ui/' . $file, 'text/javascript');
                }
            }

            // add the launcher
            $launchFile = '/sydneyassets/scripts/zLauncher.js';
            if ($useConcatenation) {
                $r .= "/* =========== File: " . $path . $launchFile . " ================ */ \n\n\n" . file_get_contents($path . $launchFile) . "\n\n\n\n";
            } else {
                $zview->headScript()->appendFile(Sydney_Tools::getRootUrlCdn() . $launchFile, 'text/javascript');
            }

        }

        return $r;
    }

}

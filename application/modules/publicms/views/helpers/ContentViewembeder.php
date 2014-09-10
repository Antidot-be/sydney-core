<?php
require_once('Zend/View/Helper/Abstract.php');

/**
 * Helper showing an acion and its view within this helper (with it we can embeed a view in another one)
 *
 * @package Adminpages
 * @subpackage ViewHelper
 * @author Arnaud Selvais
 * @since 08/06/09
 * @todo Implement the translation method here
 */
class Publicms_View_Helper_ContentViewembeder extends Zend_View_Helper_Abstract
{
    /**
     * Helper main function
     * @param $actionsHtml String HTML code showing the action buttons
     * @param $content String The content of this element
     * @param $dbId Int DB id of the object
     * @param $order Int order of this item in the DB
     * @param $params Array parameters (if any)
     * @return String HTML to be inserted in the view
     */
    public function contentViewembeder($actionsHtml = '', $content = '', $dbId = 0, $order = 0, $params = array(), $pagstructureId = 0)
    {

        if (!empty($content)) {
            switch (Sydney_Tools_Sydneyglobals::getConf('helpers')->content->viewembedder->method) {
                case 'ajax':
                    return Sydney_View_Embedder_Content::ajaxContents($content);
                    break;
                case 'filegetcontents':
                    return file_get_contents(Sydney_Tools::getRootUrl() . '/' . $content . '/sydneylayout/no/');
                    break;
                case 'curlgetcontents' :
                    return Sydney_View_Embedder_Content::curlGetContents(Sydney_Tools::getRootUrl() . $content . '/sydneylayout/no/');
                    break;
                case 'action':
                default :
                    $i = 0;
                    $module = null;
                    $controller = null;
                    $action = null;
                    $oldv = null;
                    $params2 = array();
                    foreach (preg_split("/\//", $content) as $v) {
                        if ($i == 1) {
                            $module = $v;
                        } elseif ($i == 2) {
                            $controller = $v;
                        } elseif ($i == 3) {
                            $action = $v;
                        } elseif ($i > 3) {
                            if ($i % 2 == 0) {
                                $params2[$v] = null;
                                $oldv = $v;
                            }
                            if ($i % 2 == 1) {
                                $params2[$oldv] = $v;
                                $oldv = null;
                            }
                        }
                        $i++;
                    }

                    return $this->view->action($action, $controller, $module, array_merge($params2, $params));
                    break;
            }
        } // END - if content

        return '';
    }
}

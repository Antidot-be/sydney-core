<?php

/**
 * Helper showing the view embedder content
 */
class Adminpages_View_Helper_ContentViewembeder extends Zend_View_Helper_Abstract
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
    public function contentViewembeder($actionsHtml = '', $content = '', $dbId = 0, $order = 0, $params = array(), $moduleName = 'adminpages', $pageStructureId = 0, $sharedInIds = '')
    {
        $toReturn = '';
        if (!empty($content)) {
            switch (Sydney_Tools::getConf('helpers')->content->viewembedder->method) {
                case 'ajax':
                    $toReturn = Sydney_View_Embedder_Content::ajaxContents($content);
                    break;
                case 'filegetcontents':
                    $toReturn = file_get_contents(Sydney_Tools::getRootUrl() . '/' . $content . '/sydneylayout/no/');
                    break;
                case 'curlgetcontents' :
                    $toReturn = Sydney_View_Embedder_Content::curlGetContents(Sydney_Tools::getRootUrl() . $content . '/sydneylayout/no/');
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
                    $toReturn = $this->view->action($action, $controller, $module, $params2);
                    break;
            }
        } // END - if content

        return '
            <li
                class="' . $params['addClass'] . ' sydney_editor_li"
                type=""
                dbparams="' . $content . '"
                editclass="viewembeder"
                dbid="' . $dbId . '"
                dborder="' . $order . '"
                data-content-type="view-embedder-block"
                pagstructureid="' . $pageStructureId . '"
                sharedinids="' . $sharedInIds . '">' . $actionsHtml . '<div class="content">'
        . $toReturn
        . '</div></li>';
    }

}

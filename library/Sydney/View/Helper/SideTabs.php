<?php
require_once('Zend/View/Helper/Abstract.php');

class Sydney_View_Helper_SideTabs extends Zend_View_Helper_Abstract
{
    /**
     *
     * Helper for displaying tabs and buttons in a sidebar in sydney admin
     *
     * @param Array $m Array of actions and their labels to show in the tabs
     * @param String $exceptAction Label of an action where we should not show the add button (like an index -> dashboard for example)
     * @param String $cdn The CDN to be used
     * @param String $moduleName The name of the module
     * @param String $controllerName The name of the Controller
     * @param String $actionName The name of the current action (to highlight the tab)
     * @return String The resulting HTML
     */
    public function SideTabs($m, $exceptAction, $cdn, $moduleName, $controllerName, $actionName, $parameters = false)
    {
        $s = $actionName;

        if (!isset($m[$s])) {
            foreach ($m as $k => $v) {
                //if (preg_match('/'.$k.'/', $s)) {
                if ($k == $s || $k == substr($s, 4)) { // substr: 4 for prefix edit
                    $s = $k;
                    break;
                }
            }
        }
        $html = '';
        if ($exceptAction != '_ALL_') {
            if ($actionName != $exceptAction && isset($m[$actionName])) {
                $html .= '<div class="actions clearfix"><a class="bigbutton" href="/' . $moduleName . '/' . $controllerName . '/edit' . $actionName . '/?forModule=' . $_GET['forModule'] . '"><img src="' . $cdn . '/sydneyassets/images/ui/bigbutton/icon_add.png" /> Add ' . Sydney_Tools_Localization::_($m[$actionName]) . '</a></div>';
            } elseif ($actionName != $exceptAction) {
                $html .= '<div class="actions clearfix"><a href="/' . $moduleName . '/' . $controllerName . '/' . $s . '/" class="bigbutton muted">Back to ' . Sydney_Tools_Localization::_($m[$s]) . '</a></div>';
            }
        }
        $html .= '<hr /><ul id="localnav" class="clearfix">';
        foreach ($m as $k => $v) {
            if (preg_match('/^sep[0-9]{0,5}$/', $k)) {
                $html .= '</ul><div class="pod"><h2>' . Sydney_Tools_Localization::_($v) . '</h2></div><ul id="localnav" class="clearfix">';
            } else {
                $stro = array('', '');
                if ($s == $k) {
                    $stro = array('<strong>', '</strong>');
                }
                $html .= '<li>' . $stro[0] . '<a href="/' . $moduleName . '/' . $controllerName . '/' . $k . '">' . Sydney_Tools_Localization::_($v) . '</a>' . $stro[1] . '</li>';
            }
        }

        $html .= '</ul>';
        if ($parameters) {
            $html .= '<div class="pod"><h2></h2></div><ul  id="localnav" class="clearfix">';
            $html .= '<li><a href="/adminparameters/index/parameters?forModule=' . $moduleName . '">Parameters</a></li>';
            $html .= '</ul>';
        }

        return $html;
    }

}

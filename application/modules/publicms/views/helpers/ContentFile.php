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
class Publicms_View_Helper_ContentFile extends Zend_View_Helper_Abstract
{
    /**
     * Helper main function
     * @param $actionsHtml String HTML code showing the action buttons
     * @param $content String The content of this element
     * @param $dbid Int DB id of the object
     * @param $order Int order of this item in the DB
     * @param $params Array parameters (if any)
     * @return String HTML to be inserted in the view
     */
    public function contentFile($actionsHtml = '', $content = '', $dbid = 0, $order = 0, $params = array())
    {
        if (!empty($content)) {
            $action = 'filelist';
            $controller = 'file';
            $module = 'publicms';
            $params2 = array(
                'mode'         => 'filids',
                'layout'       => 'none',
                'viewl'        => 'list',
                'noviewswitch' => 'Y',
                'ids'          => $content,
                'params'       => $params
            );

            /* $toret = '<li dbparams="'.$content.'" type="" editclass="files" dbid="'.$dbid.'" dborder="'.$order.'">
            ' .$actionsHtml .'
            <div class="content">
                '.$this->view->action($action,$controller,$module,$params2).'
            </div>
            </li>';

            return $toret; */

            return $this->view->action($action, $controller, $module, $params2);
        }

        return '';
    }
}

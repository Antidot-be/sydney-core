<?php
require_once('Zend/View/Helper/Abstract.php');

/**
 * Helper showing the heading editor for page content editing
 *
 * @package Adminpages
 * @subpackage ViewHelper
 * @author Arnaud Selvais
 * @since 08/06/09
 * @todo Implement the translation method here
 */
class Adminpages_View_Helper_EditorActions extends Zend_View_Helper_Abstract
{
    /**
     * Helper main function
     *
     * @param Boolean $isDeletable
     * @param Boolean $isDraft
     * @param Integer $isOnline
     * @param Boolean $workflowEnabled
     * @param Boolean $isEditable
     * @param String $msgNotEditable
     * @param Array $params
     * @return String HTML to be inserted in the view
     */
    public function EditorActions($isDeletable = true, $isDraft = false, $isOnline = 0, $workflowEnabled = false, $isEditable = true, $accessRightsEnabled = false, $msgNotEditable = 'Not editable...', $params = array(), $isShared = false)
    {

        if (!$isEditable) {
            return '<div class="actions"><div class="notEditable">' . $msgNotEditable . '</div></div>';
        }
        $toReturn = '<div class="actions"><label>-</label><label class="labelEditAction" id="labelEditAction-' . $params['dbid'] . '">' . $params['label'] . '</label>';

        $toReturn .= '<a id="duplicatediv-' . $params['dbid'] . '" class="button publishdiv sydney_editor_a" href="#">Duplicate</a>';

        if ($accessRightsEnabled) {
            $toReturn .= '<a class="button orange sydney_editor_a" href="accessrightsstatus">Access Rights</a>';
        }

        if (!(false == $isDeletable && false == $isDraft)) {
            if ($isDraft) {
                $toReturn .= '<a class="button publishdiv sydney_editor_a" href="publishdiv">Save as actual content</a>';
            } else {
                $toReturn .= '<a class="button unpublishdiv sydney_editor_a" href="unpublishdiv">Save as draft</a>';
            }
            if (!$isDeletable) {
                $toReturn .= '<a class="button warning sydney_editor_a" href="rollback">Delete draft</a>';
            } else {
                if (!$isShared) {
                    $toReturn .= '<a class="button warning sydney_editor_a" href="delete">Delete</a>';
                }
            }
        }
        $toReturn .= '<a class="button sydney_editor_a" href="edit">Edit</a>';
        if ($workflowEnabled) {
            $toReturn .= '<a class="button orange sydney_editor_a" href="workflowstatus">Change status</a>';
        }

        if (!$isDraft) {
            if ($isOnline) {
                $toReturn .= '<a id="offlinediv-' . $params['dbid'] . '" class="button unpublishdiv sydney_editor_a" href="#">Unpublish</a>';
            } else {
                $toReturn .= '<a id="offlinediv-' . $params['dbid'] . '" class="button publishdiv sydney_editor_a" href="#">Publish</a>';
            }
        }
        $toReturn .= '</div>
					<div class="move"><a class="button sydney_editor_a" title="Move" href="#">
					<span class="sydney_editor_span">Move</span></a>
					</div>';

        return $toReturn;
    }

}

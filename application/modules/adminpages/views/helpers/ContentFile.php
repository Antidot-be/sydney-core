<?php

/**
 * Helper showing the file content
 */
class Adminpages_View_Helper_ContentFile extends Zend_View_Helper_Abstract
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
    public function ContentFile($actionsHtml = '', $content = '', $dbId = 0, $order = 0, $params = array('level' => 1), $moduleName = 'adminpages', $pagstructureId = 0, $sharedInIds = '')
    {
        $eventsInfo = SafactivitylogOp::getAuthorNLastEditorForContent($dbId, $moduleName);
        $module = 'publicms';
        $params2 = array(
            'mode'         => 'filids',
            'layout'       => 'none',
            'viewl'        => 'list',
            'noviewswitch' => 'Y',
            'ids'          => $content
        );

        $this->view->flist = array();
        $sql = '';
        $fltr = new Zend_Filter_Digits();

        if ($params2['mode'] == 'filids' && isset($params2['ids'])) {
            $ids = array();
            foreach (explode(',', $params2['ids']) as $id) {
                $ids[] = $fltr->filter($id);
            }
        }

        if (is_array($params) && isset($params['type']) && $params['type'] == "categories") {
            // Load the files id based on their category
            $linkedFiles = new FilfoldersFilfiles();
            $ids = array();
            foreach (preg_split('/,/', $content) AS $category) {
                $ids[] = $linkedFiles->getFilfilesLinkedTo($category);
            }
        }

        $this->view->viewl = 'list';
        $this->view->noviewswitch = 'Y';

        $oFile = new Filfiles();
        $params2['flist'] = $oFile->getFileInfosByIdList($ids);

        $toret = '<li
                    class="' . $params['addClass'] . ' sydney_editor_li"
                    dbparams="' . $content . '"
                    type=""
                    editclass="files"
                    dbid="' . $dbId . '"
                    dborder="' . $order . '"
                    data-content-type="file-block"
                    pagstructureid="' . $pagstructureId . '"
                    sharedinids="' . $sharedInIds . '">
		' . $actionsHtml . '
			<div class="content">
				' . $this->view->partial('file/filelist.phtml', $module, $params2) . '
			</div>
			<p class="lastUpdatedContent sydney_editor_p">' . $eventsInfo['firstEvent'] . '<br />' . $eventsInfo['lastEvent'] . '</p>
		</li>';

        return $toret;

    }
}

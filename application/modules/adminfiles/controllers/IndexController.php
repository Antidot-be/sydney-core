<?php
/**
 * Controller
 */

/**
 * Default controller
 *
 * @package Adminfiles
 * @subpackage Controller
 * @author Arnaud Selvais <arnaud@antidot.com>
 * @since Mar 5, 2009
 * @copyright Antidot Inc. / S.A.
 */
class Adminfiles_IndexController extends Sydney_Controller_Action
{
    public function init()
    {
        parent::init();
    }

    /**
     * Displays the list of files
     * @return void
     */
    public function indexAction()
    {
        $r = $this->getRequest();
        $this->layout->search = true;
        $id = (int) $r->id;

        if (isset($r->embed) && $r->embed == 'yes') {
            if (isset($r->context)) {
                $context = $r->context;
            } else {
                $context = 'default';
            }
            if (isset($r->filter)) {
                $filter = $r->filter;
            } else {
                $filter = 0;
            }
            if (isset($r->mode)) {
                $mode = $r->mode;
            } else {
                $mode = 'thumb';
            }
            $id = 0;

            if (substr($context, 0, 8) == 'ckeditor') {

                if (isset($_GET['source'])) {
                    $this->_helper->layout->setLayout('ckeditor-browser');
                } else {
                    $this->_helper->layout->setLayout('ckeditor');
                }

                // CKEditor params
                $this->view->ckeditor_context = ($filter == 1 || $filter == 6) ? 'image' : 'file';
                $this->view->CKEditorFuncNum = $r->CKEditorFuncNum;
                $this->view->langCode = $r->langCode;
                $this->view->CKEditor = $r->CKEditor;

            } else {
                $this->_helper->layout->disableLayout();
            }

            $this->view->embed = true;

        } else {
            $context = "default";
            $filter = 0;
            $mode = 'thumb';
        }

        //if (!$this->view->ckeditor_context) {

        if ($id > 0) {
            $this->setSubtitle('File view');
            $this->setSideBar('edit', 'files');
            /**
             * load file details
             */
            $this->view->addiScript = '<script>
						var oPaginator;
						$(function() {
							if ($("#fileEdit").length > 0) {
								$("#filesBrowse").hide();
								oPaginator = $("#fileEdit").filemanager( {
                                                                    \'embeded\' : \'' . $r->embed . '\',
                                                                    \'context\' : \'' . $context . '\',
                                                                    \'filter\' : \'' . $filter . '\',
                                                                    \'mode\' : \'' . $mode . '\',
                                                                    \'id\' : \'' . $id . '\'
								});
							}
						});
				</script>';
            // END - paginator*/
        } else {
            $this->setSubtitle('Thumbnail view');
            $this->setSideBar('index', 'files');
            /**
             * load paginator
             */
            $q = '';
            if (isset($r->q) && $r->q) {
                $q = addslashes($r->q);
            }
            $this->view->addiScript = '<script>
						var oPaginator;
						$(function() {
							if($("#filelisting").length > 0) oPaginator = $("#filelisting").filemanager( {
							\'embeded\' : \'' . $r->embed . '\',
							\'context\' : \'' . $context . '\',
							\'filter\' : \'' . $filter . '\',
							\'mode\' : \'' . $mode . '\',
							\'id\' : \'' . $id . '\',
							\'q\' : \'' . $q . '\',
						});
						$(".edefiles").css("background","#DDD");
						$(".contentEditor > li.editing").css("padding-top","5px");
					});
				</script>';
            // END - paginator*/
        }
        //}

    }

    public function datagridAction()
    {
        $this->setSubtitle('DataGrid view');
        $this->setSideBar('index', 'files');
    }

    /**
     * Displays the upload screen
     * @return void
     */
    public function uploadAction()
    {

        //$this->_helper->layout->disableLayout();
        $params = $this->getRequest()->getParams();

        // gets the categories for upload + tagging
        $catDB = new Filfolders();
        $this->view->categories = $catDB->getFoldersStructure();

        if ($params['calledBy'] != 'adminpeople') {
            $this->setSubtitle('Upload files');
            $this->setSideBar('upload', 'files');
        }

        if (count($_FILES['file']) > 0) {
            switch ($_FILES['file']['error']) {
                case UPLOAD_ERR_OK:
                    $fullpath = Sydney_Tools::getAppdataPath() . '/adminfiles/';
                    $filename = $_FILES['file']['name'];

                    $ndirn = substr($filename, -3);
                    $ndirn = preg_replace('/\./', '', $ndirn);
                    $nnd = $fullpath . '/' . strtoupper($ndirn);
                    $type = strtoupper($ndirn);

                    if (!is_dir($nnd)) {
                        mkdir($nnd);
                    }

                    if (!empty($_POST['fileupload-new-filename'])) {
                        $filename = $_POST['fileupload-new-filename'];
                    }

                    if (move_uploaded_file($_FILES['file']['tmp_name'], $nnd . '/' . $filename)) {
                        $fil = new Filfiles();
                        $fil->registerFileToDb($nnd, $filename, filesize($nnd . '/' . $filename), $type, $this->usersId, $this->safinstancesId, $this->getRequest());

                        $returnmsg = '"' . $filename . '", ' . Sydney_Tools::_('UPLOAD_ERR_OK');
                    } else {
                        $returnmsg = Sydney_Tools::_('UPLOAD_UNKNOW_ERROR');
                    }
                    break;
                case UPLOAD_ERR_INI_SIZE :
                    $returnmsg = Sydney_Tools::_('UPLOAD_ERR_INI_SIZE');
                    break;
                case UPLOAD_ERR_FORM_SIZE :
                    $returnmsg = Sydney_Tools::_('UPLOAD_ERR_FORM_SIZE');
                    break;
                case UPLOAD_ERR_PARTIAL :
                    $returnmsg = Sydney_Tools::_('UPLOAD_ERR_PARTIAL');
                    break;
                case UPLOAD_ERR_NO_FILE :
                    $returnmsg = Sydney_Tools::_('UPLOAD_ERR_NO_FILE');
                    break;
                case UPLOAD_ERR_NO_TMP_DIR :
                    $returnmsg = Sydney_Tools::_('UPLOAD_ERR_NO_TMP_DIR');
                    break;
                case UPLOAD_ERR_CANT_WRITE :
                    $returnmsg = Sydney_Tools::_('UPLOAD_ERR_CANT_WRITE');
                    break;
                case UPLOAD_ERR_EXTENSION  :
                    $returnmsg = Sydney_Tools::_('UPLOAD_ERR_EXTENSION');
                    break;
            }

            if (!empty($returnmsg)) {
                echo '<span class="warning">', $returnmsg, '</span>';
            }
        }
    }

    /**
     * Search results
     */
    public function searchAction()
    {
        $r = $this->getRequest();
        $this->redirect('/adminfiles/index/index/sydneylayout/no/q/' . urlencode($r->q));
    }

}

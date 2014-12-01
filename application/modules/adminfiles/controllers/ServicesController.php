<?php
/**
 * Controller Adminfiles Services
 */

/**
 *
 *
 * @package Adminfiles
 * @subpackage Controller
 * @author Arnaud Selvais
 * @since
 * @copyright Antidot Inc. / S.A.
 */
class Adminfiles_ServicesController extends Sydney_Controller_Action
{

    /**
     * Defines the views types the actions should bring back
     * @var array
     */
    public $contexts = array(
        'displayfiles'      => array('json'),
        'uploadfile'        => array('json'),
        'deletefile'        => array('json'),
        'getftypes'         => array('json'),
        'searchfile'        => array('json'),
        'savefileprops'     => array('json'),
        'gettags'           => array('json'),
        'updatestrorder'    => array('json'),
        'getfilfiles'       => array('json'),
        'updatefilfiles'    => array('json'),
        'multitagging'      => array('json'),
        'unziptofiles'      => array('json'),
        'addfolder'         => array('json'),
        'renamefolder'      => array('json'),
        'deletefolder'      => array('json'),
    );

    public function indexAction()
    {

    }

    /**
     * Controller initialization
     */
    public function init()
    {
        $this->_isService = true;
        parent::init();
        $this->getResponse()->setHeader("Cache-Control", "no-cache, must-revalidate");
        $this->_helper->contextSwitch()->initContext();
        $this->_helper->layout->disableLayout();
    }

    /**
     * Returns the file types and details
     * URL example : http://<url>/adminfiles/services/getftypes/format/json
     * @return void
     */
    public function getftypesAction()
    {
        if ($this->getRequest()->calledBy == 'adminpeople') {
            $this->view->ftypes = Sydney_Medias_Utils::$peopleftypes;
        } else {
            $this->view->ftypes = Sydney_Medias_Utils::$ftypes;
        }
    }

    /**
     * Returns the file types and details
     * URL example : http://<url>/adminfiles/services/getftypes/format/json
     * @return void
     */
    public function searchfileAction()
    {
        if (!empty($this->getRequest()->filename)) {
            $file = new Filfiles();
            $rowSetFiles = $file->fetchAll("filename = '"
                . Sydney_Medias_Utils::sanitizeFilename($this->getRequest()->filename)
                . "' AND safinstances_id = '" . Sydney_Tools::getSafinstancesId() . "'");
            if (count($rowSetFiles) == 1) {
                $this->view->file = $rowSetFiles[0]->id;
            } else {
                $this->view->file = 0;
            }
        } else {
            $this->view->file = 0;
        }
    }

    /**
     * Displays the file list in an HTML format
     * URL example : http://<url>/Adminfiles/Services/displayfiles/format/json
     *
     * @return void
     */
    public function displayfilesAction()
    {
        $r = $this->getRequest();
        $this->view->embeded = 'no';
        $this->view->context = 'default';
        if (isset($r->embeded)) {
            $this->view->embeded = $r->embeded;
        }
        if (isset($r->context)) {
            $this->view->context = $r->context;
        }

        $filterDigits = new Zend_Filter_Digits();
        $ts = 1;
        if (isset($r->vmode)) {
            $this->view->vmode = $r->vmode;
            if ($r->vmode == 'list') {
                $ts = 3;
            }
        }
        $desc = $filterDigits->filter($r->desc);
        $order = $filterDigits->filter($r->order);
        $count = $filterDigits->filter($r->count);
        $offset = $filterDigits->filter($r->offset);
        $filter = $filterDigits->filter($r->filter);

        // treating the tags ids for filter
        $tagsIds = array();
        if (isset($r->tags) && is_array($r->tags) && count($r->tags) > 0) {
            $tagsIds = $r->tags;
        }
        if (isset($r->q) && $r->q != '') {
            $q = addslashes($r->q);
        } else {
            $q = null;
        }
        // folders mode
        if (isset($r->folder) && $r->folder != '' && $r->folder != 'false') {
            $folder = $r->folder;
        } else {
            $folder = false;
        }

        $fileDb = new Filfiles();
        $filesToReturn = $fileDb->getFilesToDisplayInFM($ts, $desc, $order, $count, $offset, $filter, $tagsIds, $q, $this->safinstancesId, $folder);
        $this->view->nbpages = $filesToReturn['nbpages'];
        $this->view->files = $filesToReturn['files'];
        $this->view->ResultSet = '';
        $this->view->selected_files = !empty($r->selected_files)? explode(',', $r->selected_files) : array();
    }

    /**
     *
     */
    public function getfilfilesAction()
    {
        $this->_initDataTableRequest();
        $fileDb = new Filfiles();
        $this->view->ResultSet = $fileDb->fetchdatatoYUI("safinstances_id = '" . $this->safinstancesId . "' ", $this->sort . ' ' . $this->dir, $this->results, $this->startIndex, $this->hidefields);
    }

    /**
     * Updates one field of the table
     */
    public function updatefilfilesAction()
    {
        $fileDb = new Filfiles();
        $this->view->result = $fileDb->updateOneField($this->getRequest()->getPost());
    }

    /**
     * Returns the HTML for the file edition.
     * @return void
     */
    public function displayeditAction()
    {
        $filterDigits = new Zend_Filter_Digits();
        $this->view->safinstances_id = $this->safinstancesId;
        $r = $this->getRequest();
        $this->view->ResultSet = array(
            'status'  => 0,
            'message' => 'Undefined Error ' . $r->id
        );
        if (isset($r->id)) {
            $id = $filterDigits->filter($r->id);
            $fileDb = new Filfiles();
            $where = 'id = ' . $id . ' AND safinstances_id = ' . $this->safinstancesId;
            $files = $fileDb->fetchAll($where);
            $this->view->filfolders = $this->_getLinkedFolders($id, true);
            if (count($files) == 1) {
                $this->view->file = $files->current();
                $this->view->fullpath = Sydney_Tools_Paths::getAppdataPath() . '/adminfiles/' . $this->view->file->filename;
            }
        }
    }

    /**
     * Returns a resultset of tags proposal according to the requets sent.
     * This is used for autocompletion of the file tagging system
     * @return void
     */
    public function gettagsAction()
    {
        $r = $this->getRequest();
        $query = "SELECT id, label, parent_id
				FROM filfolders
				WHERE safinstances_id = '" . $this->safinstancesId . "'
				AND label LIKE '" . addslashes($r->mylabel) . "%'
				AND isnode = 0
				LIMIT 0 , 20";
        $tags = array();
        $parentsLabels = array();

        foreach ($this->_db->fetchAssoc($query) as $folder) {
            $folder['parentlabel'] = '';
            if ($folder['parent_id'] > 0) {
                if (isset($parentsLabels [($folder['parent_id'])])) {
                    $folder['parentlabel'] = $parentsLabels[($folder['parent_id'])];
                } else {
                    $folderDb = new Filfolders();
                    $findedFolder = $folderDb->find($folder['parent_id']);
                    $folder['parentlabel'] = $findedFolder[0]->label;
                }
            }
            $tags[] = array($folder['label'], $folder['parentlabel'], $folder['id']);
        }
        $this->view->ResultSet = array('Results' => $tags);
    }

    /**
     * Upload a file on the server for use with google Gears multi upload in chunks.
     * We return a string with the %age uploaded so far.
     * @return void
     * @todo make the resume possible
     * @todo check if the file we try to upload is already there
     * @todo check if the file we upload is valid
     */
    public function uploadfileAction()
    {
        $fullpath = Sydney_Tools_Paths::getAppdataPath() . '/adminfiles/';

        // Get a file name
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }

        // check if appdata and adminfiles exist and if not, create dir
        if (!is_dir(Sydney_Tools_Paths::getAppdataPath())) {
            mkdir(Sydney_Tools_Paths::getAppdataPath());
            chmod(Sydney_Tools_Paths::getAppdataPath(), 0777);
        }
        if (!is_dir($fullpath)) {
            mkdir($fullpath);
            chmod($fullpath, 0777);
        }

        $explodedFilename = explode('.', $fileName);
        $fileType = strtoupper($explodedFilename[count($explodedFilename) - 1]);
        $nnd = $fullpath;

        $maxFileAge = 5 * 3600; // Temp file age in seconds
        set_time_limit(5 * 60);// 5 minutes execution time

        $targetDir = $nnd;

        // Create target dir
        if (!file_exists($targetDir)) {
            @mkdir($targetDir);
        }

        // Clean the fileName for security reasons
        $fileName = Sydney_Medias_Utils::sanitizeFilename($fileName);

        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        // Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

        $cleanupTargetDir = true;
        // Remove old temp files
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
            }
            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$filePath}.part") {
                    continue;
                }
                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    @unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }

        // Open temp file
        if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }
        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }
            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        }
        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }
        @fclose($out);
        @fclose($in);
        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1) {
            // Strip the temp .part suffix off
            rename("{$filePath}.part", $filePath);
        }

        // Return Success JSON-RPC response
        if (file_exists($targetDir . $fileName)) {
            $fil = new Filfiles();
            $fileId = $fil->registerFileToDb($targetDir, $fileName, filesize($targetDir . $fileName), $fileType, $this->usersId, $this->safinstancesId, $this->getRequest());

            die('{"jsonrpc" : "2.0", "result" : null, "id" : ' . Zend_Json_Encoder::encode($fileId) . ' }');
        }

    }

    /**
     * Moves all chunks of files to the final directory and register it in the DB
     * @return void
     */
    public function moveallchunksAction()
    {
        $fullpath = Sydney_Tools_Paths::getAppdataPath() . '/adminfiles/';
        $fi = new Filfiles();
        $res = $fi->moveAllChuncks($fullpath, $this->usersId, $this->safinstancesId);
        $this->view->content = implode("<br>\n", $res);
        $this->render('index');
    }

    /**
     *
     */
    public function unziptofilesAction()
    {
        // get the zip
        $r = $this->getRequest();
        if (isset($r->id) && preg_match('/^[0-9]{1,30}$/', $r->id)) {
            $id = $r->id;
            $fdb = new Filfiles();
            $where = "id = " . $id . " AND safinstances_id = '" . $this->safinstancesId . "' AND type = 'ZIP' ";
            $files = $fdb->fetchAll($where);
            $categories = $fdb->getCategoriesLabels($id);
            $ftype = null;
            if (count($files) == 1) {
                $fdb = $files[0];
                $ftype = Sydney_Medias_Filetypesfactory::createfiletype($fdb->path . '/' . $fdb->filename, $fdb);
            }
            $fullpath = Sydney_Tools_Paths::getCachePath() . '/' . uniqid('unziptofiles_');
            $this->view->content = array();
            // create the temp dir
            mkdir($fullpath);
            mkdir($fullpath . '/chunks');
            // unzip the file

            if ($ftype->unzipToDir($fullpath . '/chunks')) {
                // move the files to DB
                $fi = new Filfiles();
                $res = $fi->moveAllChuncks($fullpath, $this->usersId, $this->safinstancesId, $chunks = 'chunks', $desc = 'from zipped file - ' . $fdb->filename, $returnType = 'both');
                $this->view->content = $res[1];

                // link unzipped files to a category if any
                if (is_array($res[0]) && count($res[0]) > 0 && is_array($categories) && count($categories) > 0) {
                    $linkDB = new FilfoldersFilfiles();
                    foreach ($res[0] as $fileId) {
                        foreach ($categories as $categoryId => $catlabel) {
                            $row = $linkDB->createRow();
                            $row->filfolders_id = $categoryId;
                            $row->filfiles_id = $fileId;
                            $row->save();
                        }
                    }
                }

                // remove temp dir
                Sydney_Tools_Dir::rrmdir($fullpath);
            }
        }
    }


    /**
     * Delete a file with the ID passed as param.
     * URL example : http://<url>/adminfile/services/deletefiles/format/json/id/5
     * @return the result in the format requested
     */
    public function deletefileAction()
    {
        $r = $this->getRequest();
        $this->view->ResultSet = array(
            'status'  => 0,
            'message' => 'Undefined Error ' . $r->id
        );
        if (isset($r->id)) {
            $id = $r->id;
            $fdb = new Filfiles();
            $where = 'id = ' . $id . ' AND safinstances_id = ' . $this->safinstancesId;
            $files = $fdb->fetchAll($where);
            if (count($files) == 1) {
                $f = $files[0];


                if (Sydney_Search_Files_Links::getInstance()->isLinked($id)) {
                    $this->view->ResultSet = array(
                        'status'  => 0,
                        'message' => 'The file has linked into content and could not be delete from the database.'
                    );
                    $this->view->ResultSet['showtime'] = 5;
                } elseif ($fdb->delete($where)) {
                    /*
                     * GDE : 27/08/2010
                     * Add trace of current action
                     */
                    Sydney_Db_Trace::add('trace.event.delete_file'
                        . ' [' . $f->filename . ']', // message
                        'adminfiles', // module
                        Sydney_Tools::getTableName($fdb), // module table name
                        'deletefile', // action
                        $id // id
                    );
                    // */
                    $fullpath = Sydney_Tools_Paths::getAppdataPath() . '/adminfiles/' . $f->filename;
                    if (unlink($fullpath)) {
                        $this->view->ResultSet = array(
                            'status'  => 1,
                            'message' => 'File ' . $f->label . ' deleted',
                            'modal'   => false
                        );
                    } else {
                        $this->view->ResultSet = array(
                            'status'  => 0,
                            'message' => 'The file could not be delete on the hard disk.'
                        );
                        $this->view->ResultSet['showtime'] = 5;
                    }
                } else {
                    $this->view->ResultSet = array(
                        'status'  => 0,
                        'message' => 'The file could not be delete from the database.'
                    );
                    $this->view->ResultSet['showtime'] = 5;
                }
            }
        }
    }

    /**
     * Saves the files properties.
     * This is called from jquery.fileproperties method save()
     * @return void
     */
    public function savefilepropsAction()
    {
        $message = 'No action taken';
        $fltr = new Zend_Filter_Digits();
        $r = $this->getRequest();
        $fid = 0;
        if (isset($r->fid)) {
            $fid = $fltr->filter($r->fid);
        }
        if ($fid > 0) {
            $fildb = new Filfiles();
            $where = "id = $fid AND safinstances_id = '" . $this->safinstancesId . "'";
            $rows = $fildb->fetchAll($where);
            if (count($rows) == 1) {
                if (isset($r->filename)) {
                    $rows[0]->label = $r->filename;
                }
                if (isset($r->desc)) {
                    $rows[0]->desc = $r->desc;
                }
                $rows[0]->istagged = 0;
                /** if (isset($r->istagged)) {
                 * if ($r->istagged == 'true' )$rows[0]->istagged = 1;
                 * } **/
                $rows[0]->save();
                $message = 'File params saved';

                Sydney_Db_Trace::add('trace.event.update_file'
                    . ' [' . $r->filename . ']', // message
                    'adminfiles', // module
                    Sydney_Tools::getTableName($fildb), // module table name
                    'updatefile', // action
                    $fid // id
                );
                // */

                // save the folders
                if (isset($r->filfolders) && $r->filfolders != '') {
                    $ffdata = array();
                    foreach (Zend_Json::decode($r->filfolders) as $fel) {
                        $ffdata[] = $fel['val'];
                    }
                    $fileFilesDb = new FilfoldersFilfiles();
                    $fileFilesDb->setFilfilesLinkedTo($fid, $ffdata);
                }
            }
        }
        $this->view->ResultSet = array('message' => $message);
    }

    /**
     * Register the file and location in the DB for quick search and indexing
     *
     * @return void
     * @param string $path Path to the file
     * @param string $fileName File name on the hard disk
     * @param int $fileWeight Weight in bytes
     * @param string $type 3 letters extension
     */
    private function _registerFileToDb($path, $fileName, $fileWeight, $type, $params = array())
    {
        $fil = new Filfiles();
        $fil->registerFileToDb($path, $fileName, $fileWeight, $type, $this->usersId, $this->safinstancesId, $params);
    }

    /**
     * Saves the file into folders and create the folders if they do not exist
     *
     * @param $tags String all the folders name separated by a comma
     * @param $fid The file ID
     * @return void
     */
    private function saveFolders($tags, $fid)
    {
        $cor = new FilfoldersFilfiles();
        $cor->delete("filfiles_id = '" . $fid . "'");

        foreach (preg_split('/,/', $tags) as $tag) {
            if (trim($tag) != '') {
                $folderLabel = ucfirst(strtolower(trim($tag)));
                $folderDb = new Filfolders();
                $where = "safinstances_id = '" . $this->safinstancesId . "' AND label LIKE '" . addslashes($folderLabel) . "' ";
                $rows = $folderDb->fetchAll($where);
                if (count($rows) == 1) {
                    $folderRow = $rows[0];
                } elseif (count($rows) == 0) {
                    // create the folder
                    $folderRow = $folderDb->createRow();
                    $folderRow->label = $folderLabel;
                    $folderRow->safinstances_id = $this->safinstancesId;
                    $folderRow->save();
                } else {
                    break;
                }
                // add data in the correspondance table
                $cor = new FilfoldersFilfiles();
                $rows2 = $cor->fetchAll("filfolders_id = '" . $folderRow->id . "' AND filfiles_id = '" . $fid . "' ");
                if (count($rows2) == 0) {
                    $corrE = $cor->createRow();
                    $corrE->filfolders_id = $folderRow->id;
                    $corrE->filfiles_id = $fid;
                    $corrE->save();
                }
            }
        }
    }

    /**
     * Returns the IDs of the files having the tags passed in arg (separated by a ,)
     * @param $tags String list of tags separated by a ,
     * @return Array
     */
    private function getFilesIdsForTags($tags)
    {
        $art = array();
        $tagsA = array();
        foreach (preg_split('/,/', $tags) as $tag) {
            if (trim($tag) != '') {
                $tagsA[] = ucfirst(strtolower(trim($tag)));
            }
        }
        $sql = "

		SELECT filfiles_id, COUNT(filfiles_id) AS nbr FROM filfolders_filfiles WHERE filfolders_id IN (
			SELECT id FROM filfolders WHERE safinstances_id = " . $this->safinstancesId . " AND label IN ('" . implode("','", $tagsA) . "')
		) GROUP BY filfiles_id
		";

        foreach ($this->_db->fetchAssoc($sql) as $e) {
            // match files IDs responding to all the criterions
            if ($e['nbr'] == count($tagsA)) {
                $art[] = $e['filfiles_id'];
            }
        }

        return $art;
    }

    public function uploadscreenAction()
    {
        $this->_helper->layout->setLayoutPath(Sydney_Tools_Paths::getCorePath() . '/webinstances/sydney/layouts');
        $this->_helper->layout->setLayout('layoutBlank');
    }

    /**
     * Updates the structure order for all posted nodes (for the current saf instance).
     * URL : /adminpages/services/updatestrorder/format/json
     * @return void
     */
    public function updatestrorderAction()
    {
        $msg = 'error! Generic';
        $status = 0;
        try {
            $data = Zend_Json::decode($this->getRequest()->jsondata);
            $i = 0;
            foreach ($data as $n) {
                $nodes = new Filfolders();
                $nodes->update(array(
                    'parent_id' => $n['parentid'],
                    'pagorder'  => $n['ndborder']
                ), 'id = ' . $n['dbid'] . ' AND safinstances_id=' . $this->safinstancesId);
                $i++;
            }
            $msg = 'Structure order updated. ' . $i . ' nodes affected.';
            $status = 1;
        } catch (Exception $e) {
            $msg = 'error! the order could not be saved. ' . $e->getMessage();
            $status = 0;
        }
        $this->view->ResultSet = array('message' => $msg, 'status' => $status);
    }

    /**
     * Sets multiple files in categorie(s)
     */
    public function multitaggingAction()
    {
        $status = 0;
        $msg = 'Could not assign files in categories';

        $r = $this->getRequest();
        if (isset($r->fileFilesIds) && isset($r->fileFoldersIds) && is_array($r->fileFilesIds) && is_array($r->fileFoldersIds) && count($r->fileFilesIds) > 0 && count($r->fileFoldersIds) > 0) {
            $lDB = new FilfoldersFilfiles();
            foreach ($r->fileFilesIds as $fileFilesId) {
                $lDB->delete("filfiles_id = '" . $fileFilesId . "' ");
                foreach ($r->filfolders_ids as $fileFoldersId) {
                    $lDB->insert(array(
                        'filfolders_id' => $fileFoldersId,
                        'filfiles_id'   => $fileFilesId
                    ));
                }
            }
            $msg = count($r->fileFoldersIds) . ' categories assigned to ' . count($r->fileFilesIds) . ' files.';
            $status = 1;
        }
        $this->view->ResultSet = array('message' => $msg, 'status' => $status);
    }


    /**
     * Sets text data in the index field for a file
     */
    public function setindexdataAction()
    {
        $r = $this->getRequest();
        if (isset($r->id) && preg_match('/^[0-9]{1,30}$/', $r->id)) {
            $id = $r->id;
            $fileDb = new Filfiles();
            $where = "id = " . $id . " AND safinstances_id = '" . $this->safinstancesId . "' ";
            $files = $fileDb->fetchAll($where);
            if (count($files) == 1) {
                $fileDb = $files[0];
                $fileDb->idxcontent = $r->idxdt;
                $fileDb->save();
            }
        }
    }

    /**
     * Adds a folder in the current location
     */
    public function addfolderAction()
    {
        $r = $this->getRequest();
        $this->view->filfoldersid = 0;
        if ($r->parentid == false) {
            $parentId = 0;
        } else {
            $parentId = $r->parentid;
        }
        if (isset($r->parentid) && preg_match('/^[0-9]{1,30}$/', $r->parentid) && isset($r->label) && $r->label != '') {
            $folderDb = new Filfolders();
            $this->view->filfoldersid = $folderDb->insert(array(
                'label'           => $r->label,
                'desc'            => '',
                'parent_id'       => $parentId,
                'safinstances_id' => $this->safinstancesId,
                'pagorder'        => 0
            ));
        }
    }

    /**
     * Adds a folder in the current location
     */
    public function renamefolderAction()
    {
        $r = $this->getRequest();
        if (isset($r->id) && preg_match('/^[0-9]{1,30}$/', $r->id) && isset($r->label) && $r->label != '') {
            $folderDb = new Filfolders();
            $this->view->filfoldersid = $folderDb->update(array(
                'label' => $r->label
            ), " id = '" . $r->id . "' AND safinstances_id = '" . $this->safinstancesId . "' ");
        }
    }

    /**
     *
     */
    public function deletefolderAction()
    {
        $r = $this->getRequest();
        if (isset($r->id) && preg_match('/^[0-9]{1,30}$/', $r->id)) {
            $folderDb = new Filfolders();
            $this->view->filfoldersid = $folderDb->delete(" id = '" . $r->id . "' AND safinstances_id = '" . $this->safinstancesId . "' ");
        }
    }

    /**
     * Returns the folders linked to a file
     * @param int $filfilesId
     * @param boolean $json Return data in a JSON string containing the ids and labels
     * @return string
     */
    protected function _getLinkedFolders($filfilesId, $json = true)
    {
        $elements = array();
        $lDB = new FilfoldersFilfiles();
        $fDB = new Filfolders();

        $fileFilesIds = $lDB->getFilfoldersLinkedTo($filfilesId);

        if (count($fileFilesIds) > 0) {
            $sql = "id IN (" . implode(',', $fileFilesIds) . ")  ";
            foreach ($fDB->fetchAll($sql) as $el) {
                $elements[] = array(
                    'label' => addslashes($el->label),
                    'val'   => $el->id
                );
            }
        }
        if ($json) {
            return preg_replace('/"/', "'", Zend_Json::encode($elements));
        } else {
            return $elements;
        }
    }


    public function internlinkbrowserAction()
    {
        $r = $this->getRequest();
        $this->layout->search = true;
        $id = (int) $r->id;

        $context = "default";
        $filter = 0;
        $mode = 'thumb';

        $this->_helper->layout->disableLayout();
        $this->view->embed = true;

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
                                    \'embeded\' : \'yes\',
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
    }

}

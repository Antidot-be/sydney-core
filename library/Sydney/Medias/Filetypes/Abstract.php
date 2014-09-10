<?php
include_once('Sydney/Medias/Filetypes/Interface.php');
include_once('Zend/Filter/Alnum.php');

/**
 *
 * @author Arnaud Selvais
 * @since 13/08/09
 * @copyright Antidot Inc. / S.A.
 */
abstract class Sydney_Medias_Filetypes_Abstract implements Sydney_Medias_Filetypes_Interface
{
    protected $_debug = false;
    protected $deficon = 'default_128.png';
    // index of the page to show if it is a multipage document
    public $pageid = 0;
    public $fullpath;
    public $dirname;
    public $basename;
    public $extension;
    public $filename;
    public $assetsPath = ''; // see constructor
    public $cachepath = '/tmp';
    public $cachetime = 500000000;
    /**
     * @var Zend_Db_Table_Row If not null, it should be the row of the data linked to the file from the DB
     */
    public $fdb = null;
    /**
     * @var array Defining the default thumbnail size
     */
    public $thumbSize = array(110, 110);

    /**
     * @param $fullpath
     * @param null $cachpath
     * @param null $fdb
     */
    public function __construct($fullpath, $cachpath = null, $fdb = null)
    {
        // AS : 01/01/11 : quick fix for problem path in the DB vs mac sym links impossible to create in /home
        $this->fullpath = preg_replace('/\/home\/www\/sydneyFramework\/trunk\/webinstances/', '/www/sydney/webinstances', $fullpath);
        $this->assetsPath = Sydney_Tools::getRootPath() . '/core/library/Sydney/Medias/Assets/';
        $this->fdb = $fdb;
        $this->setPath($fullpath, $cachpath);
    }

    /**
     * @param $fullpath
     * @param null $cachpath
     */
    public function setPath($fullpath, $cachpath = null)
    {
        $pi = pathinfo($fullpath);
        $this->dirname = $pi['dirname'];
        $this->basename = $pi['basename'];
        $this->extension = strtoupper($pi['extension']);
        $this->filename = $pi['filename'];

        // set the cache dir path if we have one in the config
        if ($cachpath == null) {
            //$t = Sydney_Tools::getCachePath();
            //if (isset($t))
            $this->cachepath = Sydney_Tools::getCachePath();
        } else {
            $this->cachepath = $cachpath;
        }
    }

    /**
     * Displays the thumbnail on the STDOUT
     * @return Boolean
     */
    public function showThumb()
    {
        $cachename = $this->assetsPath . $this->deficon . $this->thumbSize[0] . $this->thumbSize[1];
        $cacheimg = $this->cachepath . '/' . $this->_nameFilter($cachename);
        if (!$thumb = $this->_dataCacher($cachename)) {
            $source = imagecreatefrompng($this->assetsPath . $this->deficon);
            list($width, $height) = getimagesize($this->assetsPath . $this->deficon);
            $thumb = imagecreatetruecolor($this->thumbSize[0], $this->thumbSize[1]);
            imagecopyresampled($thumb, $source, 0, 0, 0, 0, $this->thumbSize[0], $this->thumbSize[1], $width, $height);
            $this->_dataCacher($cachename, $thumb);
            imagepng($thumb, $cacheimg);
        }
        header('Content-type: image/png');
        header("Content-Disposition: attachment; filename=\"" . $cachename . ".png\";");
        $this->getRawFile($cacheimg, false, 'PNG', true);
    }

    /**
     * @param int $dw
     * @param null $dh
     */
    public function showImg($dw = 500, $dh = null)
    {
        $cachename = $this->assetsPath . $this->deficon . $dw . $dh;
        $cacheimg = $this->cachepath . '/' . $this->_nameFilter($cachename);
        if (!$thumb = $this->_dataCacher($cachename)) {
            $filename = $this->assetsPath . $this->deficon;
            list($width, $height) = getimagesize($filename);
            if ($dh == null) {
                if ($dw <= $width) {
                    $newwidth = $dw;
                    $newheight = round($height * ($newwidth / $width));
                } else {
                    $newwidth = $width;
                    $newheight = $height;
                }
            } else {
                if ($dh <= $height) {
                    $newheight = $dh;
                    $newwidth = round($width * ($newheight / $height));
                } else {
                    $newwidth = $width;
                    $newheight = $height;
                }

            }
            $thumb = imagecreatetruecolor($newwidth, $newheight);
            $source = imagecreatefrompng($filename);
            imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
            $this->_dataCacher($cachename, $thumb);
            imagejpeg($thumb, $cacheimg, 80);

        }
        header('Content-type: image/jpeg');
        $this->getRawFile($cacheimg, false, 'JPG', true);
    }

    /**
     * Returns the current image size in an array
     * @return bool
     */
    public function getSize()
    {
        return false;
    }

    /**
     * Returns the information we could find on the file
     * @return array
     */
    public function getFileinfo()
    {
        $ftinf = Sydney_Medias_Utils::getFileType($this->extension);

        $toret = array();
        $toret['general.deficon'] = $this->deficon;

        $toret['general.fullpath'] = $this->fullpath;
        if (file_exists($this->fullpath)) {
            $toret['general.filesize'] = filesize($this->fullpath);
            $toret['general.filemtime'] = filemtime($this->fullpath);
            $toret['general.filetype'] = filetype($this->fullpath);
        } else {
            $toret['general.filesize'] = 0;
            $toret['general.filemtime'] = null;
            $toret['general.filetype'] = null;
        }
        $toret['general.dirname'] = $this->dirname;
        $toret['general.basename'] = $this->basename;
        $toret['general.extension'] = $this->extension;
        $toret['general.intdesc'] = $ftinf[1];
        $toret['general.filename'] = $this->filename;
        $toret['general.assetsPath'] = $this->assetsPath;

        return $toret;
    }

    /**
     * Prints the raw data to the STDOUT
     *
     * @param bool $filename
     * @param bool $forceDownload
     * @param null $extf
     * @param bool $automime
     * @param string $cntdt
     */
    public function getRawFile($filename = false, $forceDownload = false, $extf = null, $automime = true, $cntdt = 'inline')
    {
        $ext = $this->extension;

        if ($forceDownload) {
            $cntdt = 'attachment';
        }
        if (!$filename) {
            $ext = '';
        }

        if ($automime) {
            if ($extf == null) {
                header('Content-type: ' . Sydney_Medias_Utils::getMimeType($this->extension));
            } else {
                header('Content-type: ' . Sydney_Medias_Utils::getMimeType($extf));
            }
        }
        if ($extf != null) {
            header('Content-Disposition: ' . $cntdt . '; filename="' . $this->basename . '.' . $extf);
        } else {
            header('Content-Disposition: ' . $cntdt . '; filename="' . $this->basename . '.' . $ext);
        }
        if (!$filename) {
            $filename = $this->fullpath;
        }

        $handle = fopen($filename, "rb");
        $contents = stream_get_contents($handle);
        fclose($handle);
        print $contents;
    }

    /**
     * Method for cach management
     *
     * @param $cfname Name of the file containing the cached data
     * @param null $pngdata
     * @param bool $isBindata
     * @return bool|false|mixed|null False if the data is not in cache or the cached data
     */
    protected function _dataCacher($cfname, $pngdata = null, $isBindata = false)
    {
        $cfname = $this->_nameFilter($cfname);
        $this->_logToFile('_dataCacher $cfname ' . $cfname);
        $frontendOptions = array('lifetime'                => $this->cachetime,
                                 'automatic_serialization' => true
        );
        $backendOptions = array('cache_dir' => $this->cachepath);
        $cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);

        // $pngdata = $cache->load($cfname);
        $pngdata = file_exists($this->cachepath . '/' . $cfname);

        if (!$pngdata) {
            $this->_logToFile('_dataCacher not in cache');
            if ($pngdata !== null) {
                $cache->save($pngdata, $cfname);
                $this->_logToFile('_dataCacher cache saved');
            }

            return false;
        } else {
            $this->_logToFile('_dataCacher IS cached');

            return $this->cachepath . '/' . $cfname;
            // return $pngdata;
        }
    }

    /**
     *
     * @param $cfname
     * @return string
     */
    protected function _nameFilter($cfname)
    {
        $filter = new Zend_Filter_Alnum();
        $cfname = $filter->filter($cfname);

        return $cfname;
    }

    /**
     * Logs data to file for debug purpose
     * @param string $message
     * @param string $file
     * @return bool
     */
    protected function _logToFile($message = '', $file = 'Sydney_Medias_Filestypes.log')
    {
        if (!$this->_debug) {
            return false;
        }
        $c = date('Ymd H:i:s') . ';' . $this->filename . ';' . $this->extension . ';"' . $message . '"' . "\n";
        file_put_contents(Sydney_Tools_Paths::getLogPath() . '/' . $file, $c, FILE_APPEND);

        return true;
    }

}

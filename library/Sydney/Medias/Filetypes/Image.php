<?php

/**
 *
 * @author Arnaud Selvais
 * @since 13/08/09
 * @copyright Antidot Inc. / S.A.
 */
class Sydney_Medias_Filetypes_Image extends Sydney_Medias_Filetypes_Abstract
{
    protected $deficon = 'picture_128.png';

    /**
     * Displays the thumbnail on the STDOUT
     * @return Boolean
     */
    public function showThumb()
    {
        $cachename = $this->fullpath . $this->thumbSize[0] . $this->thumbSize[1];
        $cacheimg = $this->cachepath . '/' . $this->_nameFilter($cachename);
        if ($this->extension == 'PNG') {
            $cacheimg .= '.png';
        }
        if ($this->extension == 'JPG' || $this->extension == 'JPEG') {
            $cacheimg .= '.jpg';
        }

        if (!is_file($cacheimg)) {
            $this->_logToFile('File not in cache');
            $dw = $this->thumbSize[0];
            $dh = $this->thumbSize[1];
            $filename = $this->fullpath;
            list($width, $height) = getimagesize($filename);
            if ($width < $height) {
                $newwidth = $dw;
                $newheight = round($height * ($newwidth / $width));
            } else {
                $newheight = $dh;
                $newwidth = round($width * ($newheight / $height));
            }
            $thumb = imagecreatetruecolor($dw, $dh);
            $resize = true;
            if ($this->extension == 'PNG') {
                $source = imagecreatefrompng($filename);
            } elseif ($this->extension == 'JPG' || $this->extension == 'JPEG') {
                $source = imagecreatefromjpeg($filename);
            } elseif ($this->extension == 'GIF') {
                $source = imagecreatefromgif($filename);
            } else {
                $resize = false;
                $thumb = imagecreatefrompng($this->assetsPath . $this->deficon);
            }
            if ($resize) {
                if ($this->extension == 'PNG') {
                    // resize and keep transparency for PNG
                    $thumb = imagecreatetruecolor($dw, $dh);
                    imagealphablending($thumb, false);
                    imagesavealpha($thumb, true);
                    $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
                    imagefilledrectangle($thumb, 0, 0, $dw, $dh, $transparent);
                }
                imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
            }
            if ($this->extension == 'PNG') {
                imagepng($thumb, $cacheimg);
            } else {
                imagejpeg($thumb, $cacheimg, 90);
            }
        } else {
            $this->_logToFile('File IS cached');
        }
        if ($this->extension == 'PNG') {
            header('Content-type: image/png');
            $this->getRawFile($cacheimg, false, 'png', true);
        } else {
            header('Content-type: image/jpeg');
            $this->getRawFile($cacheimg, false, 'jpg', true);
        }
    }

    /**
     * Show an image with a width defined (param passed as arg)
     * @param int $dw
     * @param null $dh
     * @return bool|void True if success
     */
    public function showImg($dw = 500, $dh = null)
    {
        $cachename = $this->fullpath . $dw . $dh;
        $cacheimg = $this->cachepath . '/' . $this->_nameFilter($cachename);
        if ($this->extension == 'PNG') {
            $cacheimg .= '.png';
        }
        if ($this->extension == 'JPG' || $this->extension == 'JPEG') {
            $cacheimg .= '.jpg';
        }

        if (!is_file($cacheimg)) {
            $filename = $this->fullpath;
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
                if ($dh < $height) {
                    $newheight = $dh;
                    $newwidth = round($width * ($newheight / $height));
                } else {
                    $newwidth = $width;
                    $newheight = $height;
                }
            }
            $thumb = imagecreatetruecolor($newwidth, $newheight);
            $resize = true;
            if ($this->extension == 'PNG') {
                $source = imagecreatefrompng($filename);
            } elseif ($this->extension == 'JPG' || $this->extension == 'JPEG') {
                $source = imagecreatefromjpeg($filename);
            } elseif ($this->extension == 'GIF') {
                $source = imagecreatefromgif($filename);
            } else {
                $resize = false;
                $thumb = imagecreatefrompng($this->assetsPath . $this->deficon);
            }
            if ($resize) {
                if ($this->extension == 'PNG') {
                    // resize and keep transparency for PNG
                    $thumb = imagecreatetruecolor($newwidth, $newheight);
                    imagealphablending($thumb, false);
                    imagesavealpha($thumb, true);
                    $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
                    imagefilledrectangle($thumb, 0, 0, $newwidth, $newheight, $transparent);
                }
                imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
            }
            $this->_dataCacher($cachename, $thumb);
            if ($this->extension == 'PNG') {
                imagepng($thumb, $cacheimg);
            } else {
                imagejpeg($thumb, $cacheimg, 90);
            }
        }
        if ($this->extension == 'PNG') {
            header('Content-type: image/png');
            $this->getRawFile($cacheimg, false, 'png', true);
        } else {
            header('Content-type: image/jpeg');
            $this->getRawFile($cacheimg, false, 'jpg', true);
        }
    }

    /**
     * @param bool $revert
     * @return array
     */
    public function _getTempEditorFullPath($revert = false)
    {
        $cachename = 'imageeditortmp-' . sha1($this->fullpath . session_id());
        $cacheimg = $this->cachepath . '/' . $this->_nameFilter($cachename) . '.' . $this->extension;
        if ($revert && file_exists($cacheimg) && is_file($cacheimg)) {
            unlink($cacheimg);
        }
        if (file_exists($cacheimg) && is_file($cacheimg)) {
            $source = $cacheimg;
        } else {
            $source = $this->fullpath;
        }

        return array($source, $cacheimg);
    }

    /**
     * Crop the current image
     * @param array $params Containing the following
     * @return bool
     */
    public function crop($params = array())
    {
        $source = $this->_getTempEditorFullPath();
        $picture = new Imagick($source[0]);
        $picture->cropImage($params['width'], $params['height'], $params['left'], $params['top']);
        $picture->writeImage($source[1]);
        $this->getRawEditorCache();

        return true;
    }

    /**
     *
     * @param bool $revert
     */
    public function getRawEditorCache($revert = false)
    {
        $source = $this->_getTempEditorFullPath($revert);
        $handle = fopen($source[0], "rb");
        $contents = stream_get_contents($handle);
        fclose($handle);
        print $contents;
    }

    /**
     * Rotate 90 degrees left
     * @param int $val
     * @return bool
     */
    public function rotate($val = 90)
    {
        $source = $this->_getTempEditorFullPath();
        $picture = new Imagick($source[0]);

        $picture->rotateimage(new ImagickPixel(), $val);
        $picture->writeImage($source[1]);

        $this->getRawEditorCache();

        return true;
    }

    /**
     * scale
     * @param int $val
     * @return bool
     */
    public function scale($val = 10)
    {
        $source = $this->_getTempEditorFullPath();
        $picture = new Imagick($source[0]);
        $sz = getimagesize($source[0]);
        $sk = 100 + $val;
        // round( $sz['columns']*$sk/100 ), $sz['rows']
        $picture->scaleImage(round($sz[0] * $sk / 100), 0);
        $picture->writeImage($source[1]);
        $this->getRawEditorCache();

        return true;
    }

    /**
     *
     * @return bool
     */
    public function contrast()
    {
        $source = $this->_getTempEditorFullPath();
        $picture = new Imagick($source[0]);
        $picture->contrastImage(1);
        $picture->writeImage($source[1]);
        $this->getRawEditorCache();

        return true;
    }

    /**
     *
     * @return bool
     */
    public function sharpen()
    {
        $source = $this->_getTempEditorFullPath();
        $picture = new Imagick($source[0]);
        $picture->sharpenImage(1, 0.5);
        $picture->writeImage($source[1]);
        $this->getRawEditorCache();

        return true;
    }

    /**
     *
     * @return bool
     */
    public function blacknwhite()
    {
        $source = $this->_getTempEditorFullPath();
        $picture = new Imagick($source[0]);
        $picture->modulateImage(100, 0, 100);
        $picture->writeImage($source[1]);
        $this->getRawEditorCache();

        return true;
    }

    /**
     *
     * @return bool
     */
    public function reflectionEffect()
    {
        $source = $this->_getTempEditorFullPath();
        $im = new Imagick($source[0]);
        /* Thumbnail the image */
        $im->thumbnailImage(200, null);
        /* Create a border for the image */
        $im->borderImage(new ImagickPixel("white"), 1, 1);
        /* Clone the image and flip it */
        $reflection = $im->clone();
        $reflection->flipImage();
        /* Create gradient. It will be overlayd on the reflection */
        $gradient = new Imagick();
        /* Gradient needs to be large enough for the image and the borders */
        $gradient->newPseudoImage($reflection->getImageWidth() + 5, round($reflection->getImageHeight() / 2), "gradient:transparent-white");
        /* Composite the gradient on the reflection */
        $reflection->compositeImage($gradient, imagick::COMPOSITE_OVER, 0, 0);
        /* Add some opacity. Requires ImageMagick 6.2.9 or later */
        $reflection->setImageOpacity(0.2);
        /* Create an empty canvas */
        $canvas = new Imagick();
        /* Canvas needs to be large enough to hold the both images */
        $width = $im->getImageWidth() + 40;
        $height = ($im->getImageHeight() * 1.5) + 10;
        $canvas->newImage($width, $height, new ImagickPixel("white"));
        $canvas->setImageFormat("png");
        /* Composite the original image and the reflection on the canvas */
        $canvas->compositeImage($im, imagick::COMPOSITE_OVER, 20, 10);
        $canvas->compositeImage($reflection, imagick::COMPOSITE_OVER, 20, $im->getImageHeight() + 10);

        $canvas->writeImage($source[1]);
        $this->getRawEditorCache();

        return true;
    }

    /**
     * Flip
     * @param string $val
     * @return bool
     */
    public function flip($val = 'h')
    {
        $source = $this->_getTempEditorFullPath();
        $picture = new Imagick($source[0]);
        if ($val == 'h') {
            $picture->flipImage();
        }
        if ($val == 'v') {
            $picture->flopImage();
        }
        $picture->writeImage($source[1]);
        $this->getRawEditorCache();

        return true;
    }

    /**
     * Returns the current image size in an array
     * @return array
     */
    public function getSize()
    {
        $width = 0;
        $height = 0;
        if (file_exists($this->fullpath)) {
            list($width, $height) = getimagesize($this->fullpath);
        }

        return array($width, $height);
    }

    /**
     * Returns the information we could find on the file
     * @return Array
     */
    public function getFileinfo()
    {
        $toret = parent::getFileinfo();
        // add the image size to the array
        $is = $this->getSize();
        $toret['img.width'] = $is[0];
        $toret['img.height'] = $is[1];

        // get the exif data
        try {
            $ft = @exif_imagetype($this->fullpath);
            if ($ft != IMAGETYPE_GIF && $ft != IMAGETYPE_PNG) {
                try {
                    ini_set("display_errors", 0);
                    $exifh = exif_read_data($this->fullpath);
                    if ($exifh) {
                        $exif = exif_read_data($this->fullpath, 0, true);
                        foreach ($exif as $key => $section) {
                            foreach ($section as $name => $val) {
                                $toret[('img.' . $key . '.' . $name)] = $val;
                            }
                        }
                        unset($toret['img.EXIF.MakerNote']);
                        unset($toret['img.EXIF.UserComment']);
                    }
                } catch (Exception $e) {
                }
            }
        } catch (Exception $e) {

        }

        return $toret;
    }
}

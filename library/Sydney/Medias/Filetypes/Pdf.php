<?php

/**
 *
 * @author Arnaud Selvais
 * @since 13/08/09
 * @copyright Antidot Inc. / S.A.
 */
class Sydney_Medias_Filetypes_Pdf extends Sydney_Medias_Filetypes_Abstract
{
    protected $deficon = 'pdf_128.png';

    /**
     * Displays the thumbnail on the STDOUT
     *
     * @param bool $pdfLogo
     * @param bool $showPreview
     * @return bool
     */
    public function showThumb($pdfLogo = false, $showPreview = true)
    {
        if ($this->thumbSize[0] < 64) {
            $showPreview = false;
        }
        if ($showPreview) {
            $divba = 3;
            if ($this->thumbSize[0] >= 64) {
                $pdfLogo = true;
            }
            if ($this->thumbSize[0] >= 200) {
                $divba = 4;
            }
            $this->showImg($this->thumbSize[0], $pdfLogo, round($this->thumbSize[0] / $divba));
        } else {
            $cachename = $this->fullpath . $this->thumbSize[0] . $this->thumbSize[1] . $this->pageid . ".png";
            $cacheimg = $this->cachepath . '/' . $this->_nameFilter($cachename);

            if ($this->thumbSize[0] == 128 && class_exists('Imagick')) {
                try {
                    if (!$im = $this->_dataCacher($cachename)) {
                        $this->_logToFile('File not in cache');
                        $im = new imagick($this->fullpath . '[' . $this->pageid . ']');
                        $im->setSize(128, 128);
                        $im->setImageFormat("png");
                        $im->adaptiveResizeImage($this->thumbSize[0], $this->thumbSize[1]);
                        // add a border to the image
                        $color = new ImagickPixel();
                        $color->setColor("rgb(200,200,200)");
                        $im->borderImage($color, 1, 1);
                        // draw the PDF icon on top of the PDF preview
                        if ($pdfLogo) {
                            $pdflogo = new Imagick($this->assetsPath . $this->deficon);
                            //$pdflogo->setSize(10,10);
                            $pdflogo->adaptiveResizeImage(60, 60);
                            $im->compositeImage($pdflogo, Imagick::COMPOSITE_DEFAULT, 1, 1);
                        }
                        $this->_dataCacher($cachename, $im, true);
                        $im->writeImage($cacheimg);
                    } else {
                        $this->_logToFile('File IS cached');
                    }
                    header("Content-Type: image/png");
                    header("Content-Disposition: attachment; filename=\"" . $cachename . ".png\";");
                    $this->getRawFile($cacheimg, false, 'PNG', true);
                } catch (Exception $e) {
                    return parent::showThumb();
                }
            } else {
                return parent::showThumb();
            }
        }
    }

    /**
     * Returns the current image size in an array
     * @return array
     */
    public function getSize()
    {
        return false;
    }

    /**
     * @param int $dw
     * @param bool $pdfLogo
     * @param int $pdfLogoSize
     */
    public function showImg($dw = 500, $pdfLogo = false, $pdfLogoSize = 128)
    {
        $cachename = $this->fullpath . $dw . $this->pageid . ".png";
        $cacheimg = $this->cachepath . '/' . $this->_nameFilter($cachename);
        if (class_exists('Imagick')) {
            try {
                $im = $this->_dataCacher($cachename);
                if ($im === false) {
                    $this->_logToFile('File not in cache');
                    $im = new imagick($this->fullpath . '[' . $this->pageid . ']');
                    //$im->setSize($dw*2,$dw*2);
                    $im->setImageFormat("png");
                    $im->adaptiveResizeImage($dw, $dw, true);
                    // add a border to the image
                    $color = new ImagickPixel();
                    $color->setColor("rgb(200,200,200)");
                    $im->borderImage($color, 1, 1);
                    // draw the PDF icon on top of the PDF preview
                    if ($pdfLogo) {
                        $pdflogo = new Imagick($this->assetsPath . $this->deficon);
                        $pdflogo->adaptiveResizeImage($pdfLogoSize, $pdfLogoSize);
                        $im->compositeImage($pdflogo, Imagick::COMPOSITE_DEFAULT, 1, 1);
                    }
                    $this->_dataCacher($cachename, $im);
                    $im->writeImage($cacheimg);
                } else {
                    $this->_logToFile('File IS cached');
                }
                header("Content-Type: image/png");
                header("Content-Disposition: attachment; filename=\"" . $cachename . ".png\";");
                $this->getRawFile($cacheimg, false, 'PNG', true);
            } catch (Exception $e) {
                return parent::showImg($dw);
            }
        } else {
            return parent::showImg($dw);
        }
    }

}

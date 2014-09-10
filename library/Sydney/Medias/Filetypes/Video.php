<?php
include_once('Sydney/Medias/Filetypes/Abstract.php');

/**
 *
 * @author Arnaud Selvais
 * @since 13/08/09
 * @copyright Antidot Inc. / S.A.
 */
class Sydney_Medias_Filetypes_Video extends Sydney_Medias_Filetypes_Abstract
{
    protected $deficon = 'video_128.png';
    protected $fileinfos = null;

    /**
     *
     *
     */
    public function showThumb($display = true, $execute = true, $vidthumbn = true)
    {
        $cacheimg = $this->showSingleThumb(null, false, $execute, true);
        if ($display) {
            $this->getRawFile($cacheimg, false, null, true);
        } else {
            echo $cacheimg;
        }
    }

    /**
     *
     * @return void
     */
    public function showSingleThumb($frameNbr = null, $displayImage = true, $execute = true, $vidthumbn = true)
    {

        // todo
    }

    /**
     *
     *
     */
    public function getSize()
    {
        $movie = $this->getFileinfo();
        $width = $movie['video.framewidth'];
        $height = $movie['video.frameheight'];

        return array($width, $height);
    }

    /**
     *
     * @return
     * @param object $dw [optional]
     */
    public function showImg($dw = 500, $vidthumbn = true)
    {
        $cachename = $this->fullpath . $dw . 'mm';
        $cacheimgf = $this->cachepath . '/' . $this->_nameFilter($cachename) . '.png';
        $minfos = $this->getFileinfo();

        if ($dw == 500) {
            $mode = 'multi';
        } else {
            $mode = 'image';
        }

        // $frameNbr = 5;
        header('Content-type: image/png');
        //$this->showSingleImg(100, 14, true);
        // $movie = new ffmpeg_movie($this->fullpath, false);
        // $totalFrames = $movie->getframecount();
        $pos = array(
            array(0, 0, 0, 0),
            array(0, 0, 0, 0),
            array(110, 0, 0, 0),
            array(220, 0, 0, 0),
            array(330, 0, 0, 0),
            array(0, 110, 0, 0),
            array(110, 110, 0, 0),
            array(220, 110, 0, 0),
            array(330, 110, 0, 0),
            array(0, 220, 0, 0),
            array(110, 220, 0, 0),
            array(220, 220, 0, 0),
            array(330, 220, 0, 0),
            array(0, 330, 0, 0),
            array(110, 330, 0, 0),
            array(220, 330, 0, 0),
            array(330, 330, 0, 0),
            array(0, 440, 0, 0),
            array(110, 440, 0, 0),
            array(220, 440, 0, 0),
            array(330, 440, 0, 0),
            array(0, 550, 0, 0),
            array(110, 550, 0, 0),
            array(220, 550, 0, 0),
            array(330, 550, 0, 0),
            array(0, 660, 0, 0),
            array(110, 660, 0, 0),
            array(220, 660, 0, 0),
            array(330, 660, 0, 0),
        );

        // $totalFrames = round($minfos['video.duration']);
        // $totalFrames = $minfos['video.framecount'];
        $totalFrames = 100;
        $each = round($totalFrames / (count($pos) - 1));

        $imagesf = array();
        if ($mode == 'multi') {
            if (!is_file($cacheimgf)) {
                $thumb = imagecreatetruecolor(110 * 4, 110 * 7);
                $res = array();
                for ($i = 1; $i <= (count($pos) - 1); $i++) {
                    // $cacheimg = $this->showSingleThumb($each*$i, false, true, $vidthumbn);
                    $frm = $each * $i;
                    $cacheimg = $this->showSingleThumb($frm, false, true, true);

                    $imagesf[] = $cacheimg;
                    $res[$i] = imagecreatefrompng($cacheimg);
                    imagecopymerge($thumb, $res[$i], $pos[$i][0], $pos[$i][1], $pos[$i][2], $pos[$i][3], 150, 150, 100);
                }
                imagepng($thumb, $cacheimgf);
            }

            $this->getRawFile($cacheimgf, false, 'png', true);
            // sleep(1);
            foreach ($imagesf as $tmpf) {
                unlink($tmpf);
            }

        } else {
            $cacheimg = $this->showSingleImg($dw, null, false, true);
            $this->getRawFile($cacheimg, false, 'png', true);
        }

    }

    /**
     *
     * @return
     * @param object $dw [optional]
     */
    public function showSingleImg($dw = 500, $frameNbr = null, $displayImage = true, $vidthumbn = true)
    {
        // Todo
    }

    /**
     * Returns the information we could find on the file
     * @return Array
     */
    public function getFileinfo()
    {
        if ($this->fileinfos == null) {
            $toret = parent::getFileinfo();
            // get the exif data
            try {
                /*
                $cls=new ReflectionClass('ffmpeg_movie');
                print '<xmp>';
                foreach($cls->getMethods() as $n) print '$toret[\'video.'.$n->name.'\'] = $movie->'.$n->name."();\n";
                print '</xmp>';
                */
                $movie = new ffmpeg_movie($this->fullpath, false);

                $toret['video.duration'] = $movie->getduration();
                $toret['video.framecount'] = $movie->getframecount();
                $toret['video.framerate'] = $movie->getframerate();
                $toret['video.filename'] = $movie->getfilename();
                $toret['video.comment'] = $movie->getcomment();
                $toret['video.title'] = $movie->gettitle();
                $toret['video.author'] = $movie->getauthor();
                $toret['video.artist'] = $movie->getartist();
                $toret['video.copyright'] = $movie->getcopyright();
                $toret['video.album'] = $movie->getalbum();
                $toret['video.genre'] = $movie->getgenre();
                $toret['video.year'] = $movie->getyear();
                $toret['video.tracknumber'] = $movie->gettracknumber();
                $toret['video.framewidth'] = $movie->getframewidth();
                $toret['video.frameheight'] = $movie->getframeheight();
                $toret['video.framenumber'] = $movie->getframenumber();
                $toret['video.pixelformat'] = $movie->getpixelformat();
                $toret['video.bitrate'] = $movie->getbitrate();
                $toret['video.hasaudio'] = $movie->hasaudio();
                $toret['video.hasvideo'] = $movie->hasvideo();
                $toret['video.videocodec'] = $movie->getvideocodec();
                $toret['video.audiocodec'] = $movie->getaudiocodec();
                $toret['video.videostreamid'] = $movie->getvideostreamid();
                $toret['video.audiostreamid'] = $movie->getaudiostreamid();
                $toret['video.audiochannels'] = $movie->getaudiochannels();
                $toret['video.audiosamplerate'] = $movie->getaudiosamplerate();
                $toret['video.audiobitrate'] = $movie->getaudiobitrate();
                $toret['video.videobitrate'] = $movie->getvideobitrate();
                $toret['video.pixelaspectratio'] = $movie->getpixelaspectratio();
                foreach ($toret as $k => $v) {
                    if ($v == '') {
                        unset($toret[$k]);
                    }
                }

                //$frm = $movie->getFrame(1);
            } catch (Exception $e) {
            }
            $this->fileinfos = $toret;

            return $this->fileinfos;
        } else {
            return $this->fileinfos;
        }
    }
}

<?php

class Sydney_View_Embedder_Content
{

    private static $url;

    private static function setUrl($url)
    {
        self::$url = $url;

        if (self::$url{0} == '/') {
            self::$url = substr(self::$url, 1);
        }
    }

    public static function curlGetContents($url)
    {

        self::setUrl($url);

        // create curl resource
        $ch = curl_init();

        // set url
        curl_setopt($ch, CURLOPT_URL, self::$url);

        // Fix timeout
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // ICI on rajoute le cookie session_id
        session_write_close();
        curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id() . '; ');

        // $output contains the output string
        ob_start();
        $output = curl_exec($ch);
        ob_end_clean();

        // close curl resource to free up system resources
        curl_close($ch);

        session_start();

        return $output;
    }

    public static function ajaxContents($url)
    {
        self::setUrl($url);
        $divId = uniqid();

        return "<div id='" . $divId . "'></div><script>
			$.get('" . Sydney_Tools::getRootUrl() . "/" . self::$url . "/sydneylayout/no', function(data) {
			  $('#" . $divId . "').html(data);
			});
		</script>";
    }

    public static function fileGetContents($url)
    {
        self::setUrl($url);

        return file_get_contents(self::$url);
    }

    public static function run(Zend_Controller_Action $ctrl, $url)
    {
        self::setUrl($url);
        $ctrl->render('');
    }

}

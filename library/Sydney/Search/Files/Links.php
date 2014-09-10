<?php

class Sydney_Search_Files_Links
{

    /**
     * Links object provides storage for shared objects.
     * @var Sydney_Search_Files_Links
     */
    private static $_links = null;

    private $result = array();

    /**
     * Retrieves the default Sydney_Search_Files_Links instance.
     *
     * @return Sydney_Search_Files_Links
     */
    public static function getInstance()
    {
        if (self::$_links === null) {
            self::$_links = new Sydney_Search_Files_Links();
        }

        return self::$_links;
    }

    /**
     * Search if the file has linked to a content/resource of Sydney
     * @param $fileid
     * @return bool
     */
    public function isLinked($fileid)
    {
        if (!key_exists($fileid, $this->result) || !key_exists('isLinked', $this->result[$fileid])) {
            // search on content
            $this->result[$fileid]['isLinked'] = false;
            if (Sydney_Search_Files_Pages_Links::getInstance()->hasLinked($fileid)) {
                $this->result[$fileid]['isLinked'] = true;
            } else { // search on Users if file has used as avatar
                if (Sydney_Search_Files_Users_Links::getInstance()->hasLinked($fileid)) {
                    $this->result[$fileid]['isLinked'] = true;
                }
            }
        }

        return $this->result[$fileid]['isLinked'];
    }

    /**
     * Buils a list of content/resource where a link to the file are found
     * @param $fileid
     * @return array
     */
    public function getLinks($fileid)
    {
        Sydney_Search_Files_Pages_Links::getInstance()->buildLinks($fileid);
        Sydney_Search_Files_Users_Links::getInstance()->buildLinks($fileid);

        return Sydney_Search_Files_Result::get();
    }

}

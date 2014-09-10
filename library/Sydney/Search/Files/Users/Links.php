<?php

class Sydney_Search_Files_Users_Links extends Users implements Sydney_Search_Files_iLinks
{

    /**
     * Links object provides storage for shared objects.
     * @var Sydney_Search_Files_Links
     */
    private static $_links = null;

    private $result;

    /**
     * Retrieves the default Sydney_Search_Files_Links instance.
     *
     * @return Sydney_Search_Files_Links
     */
    public static function getInstance()
    {
        if (self::$_links === null) {
            self::$_links = new Sydney_Search_Files_Users_Links();
        }

        return self::$_links;
    }

    public function hasLinked($fileid)
    {
        if ($this->getLinkOnAvatar($fileid)->count() == 0) {
            return false;
        } else {
            return true;
        }
    }

    public function buildLinks($fileid)
    {
        $user = new Users();
        $rowSetUsers = $this->getLinkOnAvatar($fileid);
        foreach ($rowSetUsers as $rowUser) {
            $user->set($rowUser);

            Sydney_Search_Files_Result::init($user->getModule(), 'Avatar', $user->get()->id);
            Sydney_Search_Files_Result::add('label', $user->__toString());
            Sydney_Search_Files_Result::add('link', '/adminpeople/index/editindex/id/' . $user->get()->id);
        }
    }

    private function getLinkOnAvatar($fileid)
    {
        $selector = $this->select()
            ->where('avatar = ?', $fileid);

        return $this->fetchAll($selector);
    }
}

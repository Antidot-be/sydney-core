<?php

class Sydney_Search_Files_Pages_Links extends PagdivsCommonOp implements Sydney_Search_Files_iLinks
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
            self::$_links = new Sydney_Search_Files_Pages_Links();
        }

        return self::$_links;
    }

    public function hasLinked($fileid)
    {
        // search on content "text bloc(2)" - regex search
        // - /publicms/file/getrfile/id/$fileid
        // - /publicms/file/showimg/dw/400/id/$fileid/fn/$fileid.png
        if ($this->getLinkOnTextBlock($fileid)->count() == 0) {
            // search on content file(5)/image(3)/video(4)/flash(7)
            if ($this->getLinkOnContentFile($fileid)->count() == 0) {
                // search if file has linked to a folder
                $listLinkedFiles = $this->getLinkOnFolder($fileid);
                if (count($listLinkedFiles) > 0) {
                    // search if the folder has linked to content
                    foreach ($listLinkedFiles as $linkId) {
                        if ($this->getLinkOnContentFile($linkId)->count() > 0) {
                            return true;
                        }
                    }
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return true;
        }

        return false;
    }

    public function buildLinks($fileid)
    {
        // links in text bloc
        $rowSetContent = $this->getLinkOnTextBlock($fileid);
        foreach ($rowSetContent as $rowContent) {
            if ($myparent = $this->getParent($rowContent->id)) {
                Sydney_Search_Files_Result::init($myparent->getModule(), 'Text block', $myparent->get()->id);
                Sydney_Search_Files_Result::add('label', $myparent->__toString());
                Sydney_Search_Files_Result::add('link', '/adminpages/pages/edit/id/' . $myparent->get()->id . ($myparent->getModule() == 'adminnews' ? '/emodule/news' : ''));
            }
        }

        // links in content file
        $rowSetContent = $this->getLinkOnContentFile($fileid);
        foreach ($rowSetContent as $rowContent) {
            if ($myparent = $this->getParent($rowContent->id)) {
                Sydney_Search_Files_Result::init($myparent->getModule(), 'File', $myparent->get()->id);
                Sydney_Search_Files_Result::add('label', $myparent->__toString());
                Sydney_Search_Files_Result::add('link', '/adminpages/pages/edit/id/' . $myparent->get()->id . ($myparent->getModule() == 'adminnews' ? '/emodule/news' : ''));
            }
        }

        // links on categories
        $listLinkedFiles = $this->getLinkOnFolder($fileid);
        if (count($listLinkedFiles) > 0) {
            // search if the folder has linked to content
            foreach ($listLinkedFiles as $linkId) {
                $rowSetContent = $this->getLinkOnContentFile($linkId);
                foreach ($rowSetContent as $rowContent) {
                    if ($myparent = $this->getParent($rowContent->id)) {
                        Sydney_Search_Files_Result::init($myparent->getModule(), 'File by category', $myparent->get()->id);
                        Sydney_Search_Files_Result::add('label', $myparent->__toString());
                        Sydney_Search_Files_Result::add('link', '/adminpages/pages/edit/id/' . $myparent->get()->id . ($myparent->getModule() == 'adminnews' ? '/emodule/news' : ''));
                    }
                }
            }
        }
    }

    private function getLinkOnTextBlock($fileid)
    {
        // search on content "text bloc(2)" - regex search
        // - /publicms/file/getrfile/id/$fileid
        // - /publicms/file/showimg/dw/400/id/$fileid/fn/$fileid.png
        $selector = $this->select()
            ->where('content_type_label = "text-block"')
            ->where('( ( (content REGEXP "/publicms/file/getrfile/id/' . $fileid . '[^0-9]" > 0) OR content REGEXP "/publicms/file/showimg/dw/([0-9]{1,})/id/' . $fileid . '/fn" > 0)')
            ->orWhere(' (content REGEXP "/FILE-' . $fileid . '[^0-9]" > 0) ')
            ->orWhere('( (content_draft REGEXP "/publicms/file/getrfile/id/' . $fileid . '[^0-9]" > 0) OR content_draft REGEXP "/publicms/file/showimg/dw/([0-9]{1,})/id/' . $fileid . '/fn" > 0) )')
            ->orWhere(' (content_draft REGEXP "/FILE-' . $fileid . '[^0-9]" > 0) ');

        return $this->fetchAll($selector);
    }

    private function getLinkOnContentFile($fileid)
    {
        // search on content file(5)/image(3)/video(4)/flash(7)
        $selector = $this->select()
            ->where('content_type_label IN (?)', array('file-block'))
            ->where('( content REGEXP "(.*)(^|,)' . $fileid . '(,|$)(.*)" > 0')
            ->orWhere('content_draft REGEXP "(.*)(^|,)' . $fileid . '(,|$)(.*)" > 0 )');

        return $this->fetchAll($selector);
    }

    private function getLinkOnFolder($fileid)
    {
        // search if file has linked to a folder
        $linkedFiles = new FilfoldersFilfiles();

        return $linkedFiles->getFilfoldersLinkedTo($fileid);
    }

}

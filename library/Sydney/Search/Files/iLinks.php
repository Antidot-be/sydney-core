<?php

interface Sydney_Search_Files_iLinks
{
    public static function getInstance();

    public function hasLinked($fileid);

    public function buildLinks($fileid);
}

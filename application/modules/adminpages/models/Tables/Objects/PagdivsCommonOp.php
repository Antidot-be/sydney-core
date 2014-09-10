<?php

/**
 * Class containing method to get page contend (the divs we can find in a page).
 *
 * @package Admindb
 * @subpackage Model
 */
class PagdivsCommonOp extends Pagdivs
{

    /**
     * (non-PHPdoc)
     * @see core/application/models/PagdivsOp::loadParent()
     */
    protected function loadParent()
    {
    }

    public function getDivs()
    {
    }

    public function getParent($id = 0)
    {
        $page = new Pagdivspage();
        if ($parent = $page->getParent($id)) {
            return $parent;
        } else {
            $news = new Pagdivsnews();
            if ($parent = $news->getParent($id)) {
                return $parent;
            }
        }

        return null;
    }

}

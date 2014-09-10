<?php

/**
 * Class containing method to get page contend (the divs we can find in a page).
 *
 * @package Admindb
 * @subpackage Model
 */
class PagdivspageOp extends Pagdivs
{

    protected function loadParent()
    {
        if ($parent = $this->getParent($this->get()->id)) {
            $this->setParent($parent);
        }
    }

    public function getParent($id = 0)
    {
        if ($id == 0 && parent::getParent() !== null) {
            return parent::getParent();
        } elseif ($id > 0) {
            $pageDiv = new PagstructurePagdivs;
            $rowsetPageDiv = $pageDiv->fetchAll($pageDiv->select()->where("pagdivs_id = " . $id));

            if (count($rowsetPageDiv) > 0) {
                $page = new Pagstructure;
                $page->set($rowsetPageDiv->current()->pagstructure_id);
                if ($page->get()) {
                    return $page;
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * Returns the PAGDIVS elements contained in a page with the ID passed as param.
     *
     * @param integer $pageStructureId ID of the page you want retreive the divs from
     * @param boolean $onlyOnline If you only want the divs that have a status "online". Default = true.
     * @param array $dontRetreiveIds Array of div ids we don't want to retreive (or we want to hide)
     * @param bool $showTitles
     * @param array $titleTags
     *
     * @return Array result in an array form containing assotiative array rows
     */
    public function getDivs($pageStructureId = 44, $onlyOnline = true, $dontRetreiveIds = array(), $showTitles = false, $titleTags = array(
        '<h1>',
        '</h1>'
    ), $structure = false)
    {
        $pageStructureId = (int) $pageStructureId;

        $usersId = Sydney_Tools_User::getUserdata('users_id');
        if (!$usersId) {
            $usersId = 0;
        }

        $sql = "SELECT *, pagstructure_pagdivs.order as order_pagstructure_pagdiv
				FROM
					pagdivtypes, pagdivs, pagstructure_pagdivs
				WHERE
					pagdivtypes.id = pagdivs.pagdivtypes_id AND
					pagdivs.id = pagstructure_pagdivs.pagdivs_id AND
					pagstructure_pagdivs.pagstructure_id = '" . $pageStructureId . "'
				AND pagdivs.isDeleted = 0";

        if ($onlyOnline) {
            $sql .= ' AND pagdivs.online = 1';
        }
        if (count($dontRetreiveIds) > 0) {
            $sql .= ' AND pagdivs.id NOT IN (' . implode(',', $dontRetreiveIds) . ') ';
        }

        // $sql .= ' ORDER BY pagdivs.order';
        // 12 Jun 2013 - AS - use the order from the link table so we can have different orders in pages for 1 node
        $sql .= ' ORDER BY pagstructure_pagdivs.order';

        return $this->_postContentTreatment($this->checkIsEditable($this->_db->fetchAll($sql)), $showTitles, $titleTags, $structure);
    }

    /**
     *
     * @param $divs
     * @param bool $showTitles
     * @param array $titleTags
     * @return mixed
     */
    private function _postContentTreatment($divs, $showTitles = false, $structure = false)
    {
        $titleTags = array(
            '<div class="breadcrumbs margin-bottom-40"><div class="container"><h1 class="color-green pull-left">',
            '</h1></div></div>'
        );

        if (!$showTitles) {
            return $divs;
        } else {
            $oldPageStructureId = '';
            for ($i = 0; $i < count($divs); $i++) {
                $pageStructureId = $divs[$i]['pagstructure_id'];
                $breadCrum = $structure->getBreadCrumData(Sydney_Tools_Sydneyglobals::getSafinstancesId(), $divs[$i]['pagstructure_id']);

                $label = '';
                foreach ($breadCrum as $elm) {
                    $label = $elm['label'];
                }
                if ($oldPageStructureId == $pageStructureId) {
                    $label = '';
                }

                if ($label != '') {
                    $label = $titleTags[0] . $label . $titleTags[1];
                }

                $divs[$i]['content'] = $label . $divs[$i]['content'];

                $oldPageStructureId = $pageStructureId;
            }

            return $divs;
        }
    }

    /**
     *
     */
    public function getDivsDraft()
    {
        $selector = $this->select()->setIntegrityCheck(false)
            ->from('pagstructure', array('id'))
            ->columns('count(pagstructure.id) as  cnt')
            ->join('pagstructure_pagdivs', 'pagstructure.id = pagstructure_pagdivs.pagstructure_id', '')
            ->join('pagdivs', 'pagdivs.id = pagstructure_pagdivs.pagdivs_id', '')
            ->where('pagstructure.safinstances_id = ' . Sydney_Tools::getSafinstancesId())
            ->where('pagdivs.isDeleted = 0')
            ->where('pagdivs.content_draft != ""')
            ->group('pagstructure.id');

        $localArray = $this->fetchAll($selector)->toArray();
        foreach ($localArray as $row) {
            $returnArray[$row['id']] = $row['cnt'];
        }

        return $returnArray;
    }

    /**
     *
     * @param Integer $dbid the div id you want to put offline/online
     * @return Mixed On success a string with the new status (offline/online). false on error.
     */
    public static function duplicate($pageDivId = 0, $nodeId = 0, $order = -1)
    {
        // On construit le nouveau pagdiv (on en récupère l'id)
        $newId = Pagdivs::duplicate($pageDivId);

        $realOrder = null;
        /* Si c'est -1 c'est que "duplicate" est appelée sans passer de order
         * Par défaut order = 0 alors
         * Ces 2 cas ($order = -1|0) ne devrait théoriquement arriver que pour les pagstructur_pagdiv
         * qui n'ont pas d'order donc pour les anciens sites
        */
        if ($order == -1) {
            // Cas mauvais il ne devrait plus être possible de le faire :(
            // Possible lorsqu'on duplique un type de contenu (texte ou autre)
            // TODO
            $realOrder = 0;
        } elseif ($order == 0) {
            // on récupère l'order fictif (propre à la classe)
            $realOrder = self::getFictivePagDivsOrder();
        } else {
            $realOrder = $order;
        }

        // Link to pagstructure
        $linkContentToPage = new PagstructurePagdivs();
        $linkContentToPage->insert(array(
            'pagstructure_id' => $nodeId,
            'pagdivs_id'      => $newId,
            'order'           => $realOrder
        ));

        return $newId;
    }

    /**
     * JTO - #350 - Problème d'ordre avec de vieux sites
     * Attribut permettant de simuler un order dans les pags div
     * dans le cas ou il n'y en a pas en DB
     */
    protected static $fictivePageDivOrder;

    /**
     * JTO - #350 - Problème d'ordre avec de vieux sites
     * Lorsqu'on appele cette méthode, $fictivePagDivOrder est propre
     * à toute la classe
     */
    public static function getFictivePagDivsOrder()
    {
        return self::$fictivePageDivOrder++;
    }

    /**
     * JTO - #350 - Problème d'ordre avec de vieux sites
     * Remet à l'état initial le compteur d'ordre fictif
     * */
    public static function resetFictivePagDivsOrder()
    {
        self::$fictivePageDivOrder = 1;
    }

}

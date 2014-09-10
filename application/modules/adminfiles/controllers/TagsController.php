<?php
/**
 * Controller Adminfiles Tags
 */

/**
 * Management of the tags in a hierarchic way
 *
 * @package Adminfiles
 * @subpackage Controller
 * @author Arnaud Selvais
 * @since
 * @copyright Antidot Inc. / S.A.
 */
class Adminfiles_TagsController extends Sydney_Controller_Action
{
    /**
     *
     */
    public function indexAction()
    {
        $this->setSubtitle('Adminfiles');
        $this->setSideBar('index', 'default');
        $this->layout->langswitch = true;
        $this->layout->search = true;

        $flo = new Filfolders();
        $this->view->list = $flo->getMultiArrayRelations(0, $this->safinstancesId);
    }
}

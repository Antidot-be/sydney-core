<?php
require_once('Zend/View/Helper/Abstract.php');

/**
 * Helper showing the add content bar with the possible actions
 *
 * @package Adminpages
 * @subpackage ViewHelper
 * @author Arnaud Selvais
 * @since 08/06/09
 * @todo Implement the translation method here
 */
class Adminpages_View_Helper_EditorAddContentBar extends Zend_View_Helper_Abstract
{
    private $actionsArray = array();
    public $availableModule;

    /**
     *
     */
    public function __construct()
    {

        $userData = Sydney_Tools::getUserdata();
        $safModules = new Safmodules();
        //$avmodules[$e->name] = array($e->label, $show, $e->name, $e->image, 'pagesEdit',$e->id);
        $this->availableModule = $safModules->getAvailableAvModules($this->safinstances_id, $userData['member_of_groups'], true);

        // getting the types from the DB
        if (count($this->actionsArray) == 0) {
            $typeDb = new Pagdivtypes();
            $configHelpers = Zend_Registry::getInstance()->get('config')->helpers->content;
            foreach ($typeDb->fetchAll('online = 1') as $e) {
                if ($configHelpers->{$e->code}->enable !== 'Off') {
                    $this->actionsArray[] = array(
                        $e->code,
                        $e->label,
                        $e->developeronly,
                        $e->safmodules_id
                    );
                }
            }
        }
    }

    /**
     * Helper main function
     * @return String HTML to be inserted in the view
     */
    public function EditorAddContentBar()
    {
        $toReturn = '<strong>Add content:</strong> ';
        $htmlContentTab = array();
        for ($i = 0; $i < count($this->actionsArray); $i++) {
            $isDev = (bool) $this->view->isDeveloper;
            $contentOnlyForDev = (bool) $this->actionsArray[$i][2];

            if ($isDev || (!$isDev && !$contentOnlyForDev && $this->isContentAvailable($this->actionsArray[$i][3]))) {
                $htmlContentTab[] = '<a class="sydney_editor_a" href="' . $this->actionsArray[$i][0] . '">' . $this->actionsArray[$i][1] . '</a>';
            }
        }
        $toReturn .= implode(' - ', $htmlContentTab);

        return $toReturn;
    }

    /**
     * Look if the content type can be display
     * @param $moduleId
     * @return bool
     */
    private function isContentAvailable($moduleId)
    {

        if ($moduleId == null || $moduleId == 0) {
            return true;
        }

        // On va simplement parcourir les module disponible et voir si le module lié au contenu y est présent
        foreach ($this->availableModule as $module) {
            if ($module[5] == $moduleId) {
                return true;
            }
        }

        return false;
    }
}

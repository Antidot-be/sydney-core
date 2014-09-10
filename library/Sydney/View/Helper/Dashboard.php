<?php
require_once('Zend/View/Helper/Abstract.php');

/**
 * Helper showing the structure of a site in edit mode.
 * This is used in the module page -> structure editor
 *
 * @package SydneyLibrary
 * @subpackage ViewHelper
 * @author Arnaud Selvais
 * @since 31/05/09
 * @todo Implement the translation method here
 */
class Sydney_View_Helper_Dashboard extends Zend_View_Helper_Abstract
{
    /**
     * Helper main function
     * @return String HTML to be inserted in the view
     * @param Array $structureArray [optional] Structure in an array form
     */
    public function Dashboard($listactivities)
    {
        $html = '';
        foreach ($listactivities['time'] as $datetime => $activityListId) {
            $html .= '<div xmlns="http://www.w3.org/1999/xhtml" class="whitebox">
			<h2>' . $datetime . '</h2><ul class="journal">';
            foreach ($activityListId as $activityId) {
                $html .= '<li>
					' . Sydney_Tools::getTime($listactivities['datas'][$activityId]->timestamp) . ': ';

                switch ($listactivities['datas'][$activityId]->module . '-' . $listactivities['datas'][$activityId]->module_table . '-' . $listactivities['datas'][$activityId]->action) {
                    case 'adminfiles-filfiles-insert':
                    case 'adminfiles-filfiles-update':
                        $html .= '<a href="/adminfiles/index/index/id/' . $listactivities['datas'][$activityId]->module_ids . '">'
                            . $listactivities['datas'][$activityId]->cnt . ' ' . Sydney_Tools::_('trace.event.action.' . $listactivities['datas'][$activityId]->action) . '
					 			</a>';
                        break;
                    case 'adminpages-pagstructure-restore':
                    case 'adminpages-pagstructure-insert':
                    case 'adminpages-pagstructure-update':
                        $html .= '<a href="/' . $listactivities['datas'][$activityId]->module . '/index/edit/id/' . $listactivities['datas'][$activityId]->module_ids . '">'
                            . $listactivities['datas'][$activityId]->cnt . ' ' . Sydney_Tools::_('trace.event.action.' . $listactivities['datas'][$activityId]->action) . '
					 			</a>';
                        break;
                    case 'adminpages-pagdivs-insert':
                    case 'adminpages-pagdivs-update':
                        $html .= '<a href="/' . $listactivities['datas'][$activityId]->module . '/pages/edit/id/' . $listactivities['datas'][$activityId]->parent_id . '">'
                            . $listactivities['datas'][$activityId]->cnt . ' ' . Sydney_Tools::_('trace.event.action.' . $listactivities['datas'][$activityId]->action) . '
					 			</a>';
                        break;
                    case 'adminnews-nwsnews-insert':
                    case 'adminnews-nwsnews-update':
                        $html .= '<a href="/' . $listactivities['datas'][$activityId]->module . '/index/properties/id/' . $listactivities['datas'][$activityId]->module_ids . '">'
                            . $listactivities['datas'][$activityId]->cnt . ' ' . Sydney_Tools::_('trace.event.action.' . $listactivities['datas'][$activityId]->action) . '
					 			</a>';
                        break;
                    case 'adminnews-pagdivs-insert':
                    case 'adminnews-pagdivs-update':
                        $html .= '<a href="/adminpages/pages/edit/id/' . $listactivities['datas'][$activityId]->parent_id . '/emodule/news">'
                            . $listactivities['datas'][$activityId]->cnt . ' ' . Sydney_Tools::_('trace.event.action.' . $listactivities['datas'][$activityId]->action) . '
					 			</a>';
                        break;
                    default:
                        $html .= $listactivities['datas'][$activityId]->cnt . ' ' . Sydney_Tools::_('trace.event.action.' . $listactivities['datas'][$activityId]->action);
                        break;
                }

                $html .= ' <strong>by</strong> ' . $listactivities['datas'][$activityId]->fname . ' ' . $listactivities['datas'][$activityId]->lname . '.
				</li>';
            }

            $html .= '</ul></div>';
        }

        return $html;
    }

}

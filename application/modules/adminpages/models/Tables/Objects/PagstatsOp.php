<?php

/**
 * Description of PagstatsOp
 *
 * @author Frederic Arijs
 * @since 19-janv.-2011
 */
class PagstatsOp extends Sydney_Db_Table
{

    /*
     * current row
     */
    protected $row = null;

    public function init()
    {
        parent::init();
    }

    /**
     *
     * Enter description here ...
     * @param $nodeid
     */
    public function getStatsOfPage($pageId = 0)
    {
        if ($pageId != 0 && ($this->row === null || $this->row->pagstructure_id != $pageId)) {
            $this->loadStats($pageId);
        }

        return $this->row;
    }

    public function updatePagesInfos($pagesInfos, $structure)
    {
        $stats = array();

        foreach ($pagesInfos AS $page) {
            $pageId = 0;
            // Extract id from page path
            $pagepath = $page->getDimension(Zend_Gdata_Analytics_DataQuery::DIMENSION_PAGE_PATH)->__toString();

            if ($pagepath == '/' || preg_match('/\/publicms\/index\/view\/$/', $pagepath)) { // no id means "home page"
                $pageId = $this->_findHomepage($structure);
            } elseif (preg_match('/\/publicms\/index\/view\/page\/(\d+)/', $pagepath, $id)) {
                $pageId = intval($id[1]);
            } elseif (preg_match('/([^-]*)-([0-9]*)\.html/', $pagepath, $id)) {
                $pageId = intval($id[2]);
            }

            // Does this page still exist in current structure?
            if ($pageId != 0 && $this->_existsInStructure($pageId, $structure)) {
                if (isset($stats[$pageId])) {
                    $stats[$pageId]['views'] += (int) $page->getMetric(Zend_Gdata_Analytics_DataQuery::METRIC_PAGEVIEWS)->__toString();

                    $stats[$pageId]['unique'] += (int) $page->getMetric(Zend_Gdata_Analytics_DataQuery::METRIC_UNIQUE_PAGEVIEWS)->__toString();

                    $stats[$pageId]['timeonpage'] = (float) ($stats[$pageId]['timeonpage']
                            + (int) $page->getMetric(Zend_Gdata_Analytics_DataQuery::METRIC_TIME_ON_PAGE)->__toString()
                        )
                        / $stats[$pageId]['views'];
                    $stats[$pageId]['bounces'] = (float) ($stats[$pageId]['bounces']
                            + (float) $page->getMetric(Zend_Gdata_Analytics_DataQuery::METRIC_BOUNCES)->__toString()
                        )
                        / 2;
                    if (0 == ((int) $page->getMetric(Zend_Gdata_Analytics_DataQuery::METRIC_EXITS)->__toString())) {
                        $stats[$pageId]['exits'] = 0;
                    } else {
                        $stats[$pageId]['exits'] = (float) ($stats[$pageId]['exits']
                                + (100 / ($stats[$pageId]['views']
                                        * ((int) $page->getMetric(Zend_Gdata_Analytics_DataQuery::METRIC_EXITS)->__toString())
                                    )
                                )
                            ) / 2;
                    }
                } else {
                    if (0 == ((int) $page->getMetric(Zend_Gdata_Analytics_DataQuery::METRIC_EXITS)->__toString())) {
                        $exits = 0;
                    } else {
                        $exits = (float) (100 / (((int) $page->getMetric(Zend_Gdata_Analytics_DataQuery::METRIC_PAGEVIEWS)->__toString()) * ((int) $page->getMetric(Zend_Gdata_Analytics_DataQuery::METRIC_EXITS)->__toString())));
                    }

                    $stats[$pageId] = array(
                        'views'      => (int) $page->getMetric(Zend_Gdata_Analytics_DataQuery::METRIC_PAGEVIEWS)->__toString(),
                        'unique'     => (int) $page->getMetric(Zend_Gdata_Analytics_DataQuery::METRIC_UNIQUE_PAGEVIEWS)->__toString(),
                        'timeonpage' => (float) ((int) $page->getMetric(Zend_Gdata_Analytics_DataQuery::METRIC_TIME_ON_PAGE)->__toString() / (int) $page->getMetric(Zend_Gdata_Analytics_DataQuery::METRIC_PAGEVIEWS)->__toString()),
                        'bounces'    => (float) $page->getMetric(Zend_Gdata_Analytics_DataQuery::METRIC_BOUNCES)->__toString(),
                        'exits'      => $exits,
                    );
                }
            }
        }

        foreach ($stats AS $pageId => $pageInfos) {
            if (null !== $this->fetchRow('pagstructure_id = ' . $pageId)) {
                $this->update($pageInfos, 'pagstructure_id = ' . $pageId);
            } else {
                $this->insert(array_merge($pageInfos, array('pagstructure_id' => $pageId)));
            }
        }

        return true;

    }

    private function _existsInStructure($pageId, $structure)
    {
        $isInStructure = false;
        foreach ($structure AS $id => $page) {
            if ($id == $pageId) {
                $isInStructure = true;
            }

            if (isset($page['kids']) && !empty($page['kids'])) {
                $isInStructure = $this->_existsInStructure($pageId, $page['kids']);
                if ($isInStructure) {
                    break;
                } // we found it we can stop
            }
        }

        return $isInStructure;
    }

    private function _findHomepage($structure)
    {
        $homeId = 0;

        foreach ($structure AS $id => $page) {
            if ($page['ishome'] == '1') {
                $homeId = $id;
                break;
            } elseif (isset($page['kids']) && !empty($page['kids'])) {
                $homeId = $this->_findHomepage($page['kids']);
                if ($homeId != 0) {
                    break;
                }
            }
        }

        return $homeId;
    }

    public function loadStats($pageId)
    {
        $select = $this->_getBaseSelect();
        $select->where('pagstructure_id = ?', $pageId);

        $this->row = $this->fetchRow($select);

        if (null === $this->row) {
            return array();
        }

        return $this->row->toArray();
    }

    private function _getBaseSelect()
    {
        return $this->select()->from('pagstats');
    }
}

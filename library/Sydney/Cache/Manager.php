<?php

class Sydney_Cache_Manager
{
    /**
     *
     * @var Zend_Cache instance
     */
    protected $cache;
    protected $config;
    protected $cachePath;

    /**
     *
     * @return void
     */
    function __construct()
    {
        $this->cache = Zend_Registry::get('cache');
        $this->config = Zend_Registry::get('config');
        $this->cachePath = Sydney_Tools::getCachePath();
        // clean the old cache
        $this->cache->clean(Zend_Cache::CLEANING_MODE_OLD);
    }

    /**
     * Clean the cache for the structure and the page content
     * @param int $safinstancesId
     * @return Boolean
     */
    public function clearPageCache($safinstancesId)
    {
        try {
            PagstructureOp::cleanCache($safinstancesId);
            foreach (glob($this->cachePath . '/zend_cache---internal-metadatas---publicms_*') as $filename) {
                unlink($filename);
            }
            foreach (glob($this->cachePath . '/zend_cache---publicms_*') as $filename) {
                unlink($filename);
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     *
     * @return boolean
     */
    public function clearAllCache()
    {
        try {
            $this->cache->clean(Zend_Cache::CLEANING_MODE_ALL);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Getters
     */
    public function getCache()
    {
        return $this->cache;
    }
}

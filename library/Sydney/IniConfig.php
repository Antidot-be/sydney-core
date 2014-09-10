<?php

class Sydney_IniConfig
{
    /**
     * @var array
     */
    private $configFiles = array();

    /**
     * @var Zend_Config_Ini
     */
    private $config;

    /**
     * @var string
     */
    private $section;

    /**
     * @param $section
     */
    public function __construct($section)
    {
        $this->section = $section;
    }

    /**
     *
     * @param string $file
     */
    public function addConfigFile($file)
    {
        if (file_exists($file)) {
            $this->configFiles[] = $file;
        }
    }

    /**
     *
     * @return Zend_Config_Ini
     */
    public function getConfig()
    {
        $this->config = new Zend_Config_Ini(array_shift($this->configFiles), $this->section, true);
        foreach ($this->configFiles as $file) {
            $this->config->merge(new Zend_Config_Ini($file, $this->section, true));
        }

        return $this->config;
    }
}

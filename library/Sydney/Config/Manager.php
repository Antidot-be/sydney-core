<?php

/**
 * Class for managing the config files
 *
 * @author Arnaud Selvais
 * @since 13/05/11
 */
class Sydney_Config_Manager
{
    protected $rootPath = '/www/sydney';
    protected $rootInstancesPath = '/webinstances';
    protected $configFileName = 'config.default.ini';
    protected $configFilesPaths = array();

    /**
     *
     */
    public function __construct()
    {

    }

    /**
     * Returns all the possible config file parameters within all webinstances
     */
    public function getAllPossibleConfigParams()
    {
        if (count($this->configFilesPaths) == 0) {
            $this->configFilesPaths = $this->getAllConfigFilesLocation();
        }
        $cfg = array();
        foreach ($this->configFilesPaths as $k => $v) {
            foreach (parse_ini_file($v) as $kk => $vv) {
                if (!isset($cfg[$kk])) {
                    $cfg[$kk] = array(
                        'sites'    => array(),
                        'examples' => array(),
                        'desc'     => ''
                    );
                }

                $cfg[$kk]['sites'][] = $k;
                $cfg[$kk]['examples'][] = $vv;
            }
        }
        ksort($cfg);

        return $cfg;
    }

    /**
     * Returns an array with webinstance dir as keys and full config files path as value
     * @return Array
     */
    public function getAllConfigFilesLocation()
    {
        $e = array();
        $p1 = $this->rootPath . $this->rootInstancesPath;
        foreach (Sydney_Tools::getDirList($p1, true) as $d) {
            if (!preg_match('/^\./', $d)) {
                $fp = $p1 . '/' . $d . '/config/' . $this->configFileName;
                if (file_exists($fp)) {
                    $e[$d] = $fp;
                }
            }
        }

        return $e;
    }

}

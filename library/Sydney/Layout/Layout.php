<?php

class Sydney_Layout_Layout
{

    /**
     * @author JTO
     * @since 12/02/2014
     * @var string
     */
    private $name;

    /**
     * @author JTO
     * @since 12/02/2014
     * @var string
     */
    private $fileName;

    /**
     * @author JTO
     * @since 12/02/2014
     * @var string
     */
    private $path;

    /**
     * @author JTO
     * @since 12/02/2014
     * @var Sydney_Layout_Zone[]
     */
    private $zones = array();

    /**
     * @author JTO
     * @since 11/03/2014
     * @var string
     */
    private $preview;

    public function __construct()
    {
        $this->path = Sydney_Tools_Paths::getLocalPath() . '/layouts/';
    }

    /**
     * @author JTO
     * @since 11/03/2014
     * @return string
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * @author JTO
     * @since 12/02/2014
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @author JTO
     * @since 12/02/2014
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @author JTO
     * @since 12/02/2014
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = (string) $fileName;
        $this->calculateCleanLayoutName();
    }

    /**
     * @author JTO
     * @since 12/02/2014
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        $this->calculateFileName();
    }

    /**
     * @author JTO
     * @since 12/02/2014
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = (string) $path;
    }

    /**
     * @author JTO
     * @since 12/02/2014
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @author JTO
     * @since 12/02/2014
     * @return Sydney_Layout_Zone[]
     */
    public function getZones()
    {
        return $this->zones;
    }

    /**
     * @author JTO
     * @since 13/02/2014
     * @param string $zoneName
     * @return Sydney_Layout_Zone
     */
    public function getZone($zoneName)
    {
        return $this->zones[$zoneName];
    }

    /**
     * @author JTO
     * @since 12/02/2014
     * @return bool
     */
    public function hasZones()
    {
        return !empty($this->zones);
    }

    /**
     * @author JTO
     * @since 12/02/2014
     * @return $this
     */
    public function loadZones()
    {
        $fileToLoad = $this->getPath() . '/' . $this->getFileName();
        if (file_exists($fileToLoad)) {
            $data = file_get_contents($fileToLoad);

            preg_match_all('#\$this->layout\(\)\->zones\[\'([a-z]+)\'\] ?;#i', $data, $matches);

            foreach ($matches[1] as $zoneName) {
                $zone = new Sydney_Layout_Zone();
                $zone->setName($zoneName);
                $this->addZone($zone);
            }
        }

        return $this;
    }

    /**
     * @author JTO
     * @since 13/02/2014
     * @param string $zoneName
     * @return bool
     */
    public function zoneExists($zoneName)
    {

        foreach ($this->zones as $zone) {
            if ($zone->getName() == $zoneName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Permet de g�n�rer un preview html du layout
     * @author JTO
     * @since 11/03/2014
     */
    public function calculatePreview()
    {
        $previewFile = $this->getPath() . '/preview-' . $this->getName() . '.phtml';
        if (!$this->hasZones()) {
            $this->loadZones();
        }
        if (file_exists($previewFile)) {
            ob_start();
            extract($this->getZones());
            include $previewFile;
            $content = ob_get_clean();
            $this->preview = $content;
        } else {
            $this->preview = 'No preview ...';
        }

        return $this;
    }

    /**
     * @author JTO
     * @since 12/02/2014
     */
    private function calculateCleanLayoutName()
    {
        if (empty($this->name)) {
            $this->name = str_replace('.phtml', '', $this->fileName);
        }
    }

    /**
     * @author JTO
     * @since 12/02/2014
     */
    private function calculateFileName()
    {
        if (empty($this->fileName)) {
            $this->fileName = $this->name . '.phtml';
        }
    }

    /**
     * @author JTO
     * @since 12/02/2014
     * @param Sydney_Layout_Zone $zone
     */
    private function addZone(Sydney_Layout_Zone $zone)
    {
        $this->zones[$zone->getName()] = $zone;
    }
}

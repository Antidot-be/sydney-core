<?php

class Sydney_Layout_Zone
{
    /**
     * @author JTO
     * @since 12/02/2014
     * @var string
     */
    private $name;

    /**
     * @author JTO
     * @since 21/02/2014
     * @var string
     */
    private $color;

    public function __construct()
    {
    }

    /**
     * @author JTO
     * @since 12/02/2014
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        $this->calculateColor();
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
     * @since 21/02/2014
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * M�thode permettant de calculer une couleur sur base du nom de la zone
     * @author JTO
     * @since 21/02/2014
     */
    private function calculateColor()
    {

        $hash = strrev(md5($this->name));
        $h = '';
        $j = 0;
        for ($i = 0; $i < 32 && $j < 3; $i++) {
            if ($hash[$i] > 0 && $hash[$i] <= 9) {
                $h .= $hash[$i];
                $j++;
            }
        }
        $h = ($h % 360 == 0) ? $h : $h % 360;
        $this->color = 'hsl(' . $h . ', 50%, 50%)';
    }

    /**
     * @author JTO
     * @since 12/03/2014
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}

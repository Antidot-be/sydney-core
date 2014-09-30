<?php

class Sydney_View_Helper_ContentType
{
    /**
     * @var string
     */
    private $label;
    /**
     * @var string
     */
    private $publicViewHelperMethod;
    /**
     * @var string
     */
    private $privateViewHelperMethod;
    /**
     * @var string
     */
    private $editorHelperMethod;


    function __construct($label, $publicViewHelperMethod, $privateViewHelperMethod, $editorHelperMethod)
    {
        $this->label = $label;
        $this->publicViewHelperMethod = $publicViewHelperMethod;
        $this->privateViewHelperMethod = $privateViewHelperMethod;
        $this->editorHelperMethod = $editorHelperMethod;
    }

    /**
     * Getter of $editorHelperMethod
     * @return string
     */
    public function getEditorHelperMethod()
    {
        return $this->editorHelperMethod;
    }

    /**
     * Setter of $editorHelperMethod
     * @param string $editorHelperMethod
     */
    public function setEditorHelperMethod($editorHelperMethod)
    {
        $this->editorHelperMethod = (string)$editorHelperMethod;
    }

    /**
     * Getter of $label
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Setter of $label
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = (string)$label;
    }

    /**
     * Getter of $privateViewHelperMethod
     * @return string
     */
    public function getPrivateViewHelperMethod()
    {
        return $this->privateViewHelperMethod;
    }

    /**
     * Setter of $privateViewHelperMethod
     * @param string $privateViewHelperMethod
     */
    public function setPrivateViewHelperMethod($privateViewHelperMethod)
    {
        $this->privateViewHelperMethod = (string)$privateViewHelperMethod;
    }

    /**
     * Getter of $publicViewHelperMethod
     * @return string
     */
    public function getPublicViewHelperMethod()
    {
        return $this->publicViewHelperMethod;
    }

    /**
     * Setter of $publicViewHelperMethod
     * @param string $publicViewHelperMethod
     */
    public function setPublicViewHelperMethod($publicViewHelperMethod)
    {
        $this->publicViewHelperMethod = (string)$publicViewHelperMethod;
    }
}
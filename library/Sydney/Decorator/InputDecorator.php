<?php

class Sydney_Decorator_InputDecorator extends Zend_Form_Decorator_Abstract
{
    public function __construct($options = null)
    {
        $this->setOption('placement', self::PREPEND);
        parent::__construct($options);
    }

    public function render($content)
    {
        $element = $this->getElement();
        if (!$element instanceof Zend_Form_Element) {
            return $content;
        }
        if (null === $element->getView()) {
            return $content;
        }

        $output = '<dt id="' . $element->getName() . '-label">';
        $output .= $this->buildLabel();
        $output .= $this->buildDescription();
        $output .= "</dt>";

        switch ($this->getPlacement()) {
            case (self::APPEND):
                return $content . $this->getSeparator() . $output;
            case (self::PREPEND):
            default:
                return $output . $this->getSeparator() . $content;
        }

        return $output . $content;
    }

    private function buildLabel()
    {
        $element = $this->getElement();
        $label = $element->getLabel();
        if ($translator = $element->getTranslator()) {
            $label = $translator->translate($label);
        }

        return $element->getView()->formLabel($element->getName(), $label);
    }

    private function buildDescription()
    {
        $element = $this->getElement();
        $desc = $element->getDescription();

        if (empty($desc)) {
            return '';
        }

        if ($translator = $element->getTranslator()) {
            $desc = $translator->translate($desc);
        }

        return '<span class="subheading">' . $desc . '</span>';
    }
}

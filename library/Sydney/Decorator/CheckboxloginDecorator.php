<?php

class Sydney_Decorator_CheckboxloginDecorator extends Zend_Form_Decorator_Abstract
{
    /**
     * Builds the label part
     */
    public function buildLabel()
    {
        $element = $this->getElement();
        $label = $element->getLabel();
        if ($translator = $element->getTranslator()) {
            $label = $translator->translate($label);
        }

        if ($element->getMessages()) {
            $cls = $element->setAttrib('class', 'formelementerror');
        }

        if ($element->isRequired()) {
            $label .= '*';
        }

        //$label .= ':';
        return $element->getView()->formLabel($element->getName(), $label, array('style' => 'font-size: 13px;'));
    }

    /**
     * Builds the input
     */
    public function buildInput()
    {
        $element = $this->getElement();
        $helper = $element->helper;

        $element->setAttrib('style', 'float:left;margin: 8px 8px 0 0');

        return $element->getView()->$helper($element->getName() . $element->getAttrib('suffixId'), $element->getValue(), $element->getAttribs(), $element->options);
    }

    /**
     * Render the element
     */
    public function render($content)
    {
        $element = $this->getElement();
        if (!$element instanceof Zend_Form_Element) {
            return $content;
        }
        if (null === $element->getView()) {
            return $content;
        }

        $output = '<dt style="margin-top:15px;">' . $this->buildInput() . ' ' . $this->buildLabel() . '</dt>';

        switch ($placement) {
            case (self :: PREPEND) :
                return $output . $this->getSeparator() . $content;
            case (self :: APPEND) :
            default :
                return $content . $this->getSeparator() . $output;
        }
    }
}

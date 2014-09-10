<?php
/**
 * File generated by the Sydney_Admin_Generator
 */

/**
 * Form to manage the data from the safactions table
 * @package Admindb
 * @subpackage FormmodelGenerated
 */
class SafactionsFormOp extends Sydney_Form
{
    public function __construct($options = null)
    {
        parent :: __construct($options);
        $this->setAttrib('accept-charset', 'UTF-8');
        $this->setName('safactions');

        $id = new Zend_Form_Element_Hidden('id');

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setAttrib('id', 'submitbutton');

        $label = new Zend_Form_Element_Text('label');
        $label->setLabel('label');

        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('name');

        $description = new Zend_Form_Element_Text('description');
        $description->setLabel('description');

        $safcontrollersId = new Zend_Form_Element_Select('safcontrollers_id');
        $options = new Safcontrollers();
        $safcontrollersId->addMultiOption('', '----------');
        foreach ($options->fetchAlltoFlatArray() as $k => $v) {
            $safcontrollersId->addMultiOption($k, $v['mlabel']);
        }
        $safcontrollersId->setLabel('Controller');

        $this->addElements(array(
            $id,
            $label,
            $name,
            $description,
            $safcontrollersId
        ));
        $this->addElements(array($submit));
    }

}

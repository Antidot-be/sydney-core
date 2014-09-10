<?php
/**
 * File generated by the Sydney_Admin_Generator
 */

/**
 * Form to manage the data from the safmodules table
 * @package Admindb
 * @subpackage FormmodelGenerated
 */
class SafmodulesForm extends Sydney_Form
{
    public function __construct($options = null)
    {
        parent :: __construct($options);
        $this->setAttrib('accept-charset', 'UTF-8');
        $this->setName('safmodules');

        $id = new Zend_Form_Element_Hidden('id');

        $hash = new Zend_Form_Element_Hash('no_csrf_foo', array('salt' => '4s564evzaSD64sf'));

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setAttrib('id', 'submitbutton');

        $label = new Zend_Form_Element_Text('label');
        $label->setLabel('label');

        $image = new Zend_Form_Element_Textarea('image');
        $image->setLabel('image');

        $description = new Zend_Form_Element_Textarea('description');
        $description->setLabel('description');

        $usersgroupsId = new Zend_Form_Element_Select('usersgroups_id');
        $options = new Usersgroups();
        $usersgroupsId->addMultiOption('', '----------');
        foreach ($options->fetchAlltoFlatArray() as $k => $v) {
            $usersgroupsId->addMultiOption($k, $v['mlabel']);
        }

        $usersgroupsId->setLabel('usersgroups_id');

        $prefix = new Zend_Form_Element_Text('prefix');
        $prefix->setLabel('prefix');

        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('name');

        $showintab = new Zend_Form_Element_Text('showintab');
        $showintab->setLabel('showintab');

        $istechnical = new Zend_Form_Element_Text('istechnical');
        $istechnical->setLabel('istechnical');

        $order = new Zend_Form_Element_Text('order');
        $order->setLabel('order');

        $isalwaysactive = new Zend_Form_Element_Text('isalwaysactive');
        $isalwaysactive->setLabel('isalwaysactive');

        $isapplication = new Zend_Form_Element_Text('isapplication');
        $isapplication->setLabel('isapplication');

        $longdesc = new Zend_Form_Element_Text('longdesc');
        $longdesc->setLabel('longdesc');

        $fileFoldersId = new Zend_Form_Element_Select('filfolders_id');
        $options = new Filfolders();
        $fileFoldersId->addMultiOption('', '----------');
        foreach ($options->fetchAlltoFlatArray() as $k => $v) {
            $fileFoldersId->addMultiOption($k, $v['mlabel']);
        }

        $fileFoldersId->setLabel('filfolders_id');

        $safinstancesTypeId = new Zend_Form_Element_Select('safinstancestype_id');
        $options = new Safinstancestype();
        $safinstancesTypeId->addMultiOption('', '----------');
        foreach ($options->fetchAlltoFlatArray() as $k => $v) {
            $safinstancesTypeId->addMultiOption($k, $v['mlabel']);
        }

        $safinstancesTypeId->setLabel('safinstancestype_id');

        $safinstancesSafmodules = new SafinstanceslistForm('SafinstancesSafmodules');
        $safinstancesSafmodules->setLabel('SafinstancesSafmodules');

        $this->addElements(array(
            $id,
            $hash,
            $label,
            $image,
            $description,
            $usersgroupsId,
            $prefix,
            $name,
            $showintab,
            $istechnical,
            $order,
            $isalwaysactive,
            $isapplication,
            $longdesc,
            $fileFoldersId,
            $safinstancesTypeId,
            $safinstancesSafmodules
        ));
        $this->addElements(array($submit));
    }

}

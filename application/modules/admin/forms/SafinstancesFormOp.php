<?php
/**
 * File generated by the Sydney_Admin_Generator on the Oct 14, 2010 10:26:24 AM by arnaud@antidot.ca
 */

/**
 * Form to manage the data from the safinstances table
 * @package Admindb
 * @subpackage FormmodelGenerated
 */
class SafinstancesFormOp extends Sydney_Form
{
    public function __construct($options = null)
    {
        parent :: __construct($options);
        $this->setAttrib('accept-charset', 'UTF-8');

        $this->idb = new Zend_Form_Element_Hidden('id');
    }

    /**
     * Sets an ID in the hidden field id
     * @param String|Int $id
     */
    public function setRowId($id)
    {
        $this->idb->setValue($id);
    }

    /**
     *
     */
    public function getMainform()
    {
        $this->setName('safinstances');

        $label = new Zend_Form_Element_Text('label');
        $label->setLabel('label');
        $label->setRequired(true);

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save');
        $submit->setAttrib('id', 'submitbuttona');

        $domain = new Zend_Form_Element_Text('domain');
        $domain->setLabel('Main domain name');
        $domain->setRequired(true);

        $description = new Zend_Form_Element_Textarea('description');
        $description->setLabel('Description');

        $rootpath = new Zend_Form_Element_Text('rootpath');
        $rootpath->setLabel('Instance Directory name');
        $rootpath->setRequired(true);

        $languagesId = new Zend_Form_Element_Select('languages_id');
        $options = new Languages();
        foreach ($options->fetchAlltoFlatArray() as $k => $v) {
            $languagesId->addMultiOption($k, $v['mlabel']);
        }
        $languagesId->setLabel('Main language');

        $database = new Zend_Form_Element_Text('database');
        $database->setLabel('Specific Database');

        $secdomains = new Zend_Form_Element_Textarea('secdomains');
        $secdomains->setLabel('Secondary domains (space separated)');

        $creationdate = new Zend_Form_Element_Text('creationdate');
        $creationdate->setLabel('Creation date');

        $offlinedate = new Zend_Form_Element_Text('offlinedate');
        $offlinedate->setLabel('Offline date');

        $active = new Zend_Form_Element_Checkbox('active');
        $active->setLabel('Is Active?');

        $safinstancesTypeId = new Zend_Form_Element_Select('safinstancestype_id');
        $options = new Safinstancestype();
        $safinstancesTypeId->addMultiOption('', '----------');
        foreach ($options->fetchAlltoFlatArray() as $k => $v) {
            $safinstancesTypeId->addMultiOption($k, $v['mlabel']);
        }
        $safinstancesTypeId->setLabel('Instance type');

        $offlinemessage = new Zend_Form_Element_Textarea('offlinemessage');
        $offlinemessage->setLabel('Offline message');

        $this->addElements(array(
            $this->idb,
            $this->hashb,
            $label,
            $domain,
            $safinstancesTypeId,
            $description,
            $rootpath,
            $languagesId,
            $database,
            $secdomains,
            $creationdate,
            $offlinedate,
            $active,
            $offlinemessage
        ));
        $this->addElements(array($submit));

        return $this;
    }

    /**
     *
     */
    public function getmodulesForm()
    {
        $this->setName('safinstancesModules');
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setAttrib('id', 'submitbuttonc');

        $safinstancesSafmodules = new Zend_Form_Element_MultiCheckbox('SafinstancesSafmodules');
        $safinstancesSafmodules->setLabel('SafinstancesSafmodules');
        $options = new Safmodules();
        // AND isalwaysactive != 1
        foreach ($options->fetchAll("istechnical != 1 AND name LIKE 'admin%' ", 'label') as $k) {
            $safinstancesSafmodules->addMultiOption($k->id, $k->label);
        }

        $this->addElements(array(
            $this->idb,
            $this->hashb,
            $safinstancesSafmodules
        ));
        $this->addElements(array($submit));

        return $this;
    }

    /**
     *
     *
     */
    public function getmenusForm()
    {
        $this->setName('pagmenusSafinstances');
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setAttrib('id', 'submitbuttonc');
        $pagMenusSafinstances = new PagmenuslistForm('PagmenusSafinstances');
        $this->addElements(array(
            $this->idb,
            $this->hashb,
            $pagMenusSafinstances
        ));
        $this->addElements(array($submit));

        return $this;
    }

}

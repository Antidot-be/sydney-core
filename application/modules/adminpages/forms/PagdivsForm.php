<?php
/**
 * File generated by the Sydney_Admin_Generator
 */

/**
 * Form to manage the data from the pagdivs table
 * @package Admindb
 * @subpackage FormmodelGenerated
 */
class PagdivsForm extends Sydney_Form
{
    public function __construct($options = null)
    {
        parent :: __construct($options);
        $this->setAttrib('accept-charset', 'UTF-8');
        $this->setName('pagdivs');

        $id = new Zend_Form_Element_Hidden('id');

        $hash = new Zend_Form_Element_Hash('no_csrf_foo', array('salt' => '4s564evzaSD64sf'));

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setAttrib('id', 'submitbutton');

        $label = new Zend_Form_Element_Text('label');
        $label->setLabel('label');

        $params = new Zend_Form_Element_Text('params');
        $params->setLabel('params');

        $paramsDraft = new Zend_Form_Element_Textarea('params_draft');
        $paramsDraft->setLabel('params_draft');

        $content = new Zend_Form_Element_Text('content');
        $content->setLabel('content');

        $contentDraft = new Zend_Form_Element_Text('content_draft');
        $contentDraft->setLabel('content_draft');

        $pagDivTypesId = new Zend_Form_Element_Select('pagdivtypes_id');
        $options = new Pagdivtypes();
        $pagDivTypesId->addMultiOption('', '----------');
        foreach ($options->fetchAlltoFlatArray() as $k => $v) {
            $pagDivTypesId->addMultiOption($k, $v['mlabel']);
        }

        $pagDivTypesId->setLabel('pagdivtypes_id');

        $status = new Zend_Form_Element_Text('status');
        $status->setLabel('status');

        $dateCreated = new Zend_Form_Element_Text('datecreated');
        $dateCreated->setLabel('datecreated');

        $dateModified = new Zend_Form_Element_Text('datemodified');
        $dateModified->setLabel('datemodified');

        $order = new Zend_Form_Element_Text('order');
        $order->setLabel('order');

        $usersGroupsId = new Zend_Form_Element_Select('usersgroups_id');
        $options = new Usersgroups();
        $usersGroupsId->addMultiOption('', '----------');
        foreach ($options->fetchAlltoFlatArray() as $k => $v) {
            $usersGroupsId->addMultiOption($k, $v['mlabel']);
        }

        $usersGroupsId->setLabel('usersgroups_id');

        $isDeleted = new Zend_Form_Element_Text('isDeleted');
        $isDeleted->setLabel('isDeleted');

        $online = new Zend_Form_Element_Checkbox('online');
        $online->setLabel('online');

        $pagStructurePagDivs = new PagstructurelistForm('PagstructurePagdivs');
        $pagStructurePagDivs->setLabel('PagstructurePagdivs');

        $this->addElements(array(
            $id,
            $hash,
            $label,
            $params,
            $paramsDraft,
            $content,
            $contentDraft,
            $pagDivTypesId,
            $status,
            $dateCreated,
            $dateModified,
            $order,
            $usersGroupsId,
            $isDeleted,
            $online,
            $pagStructurePagDivs
        ));
        $this->addElements(array($submit));
    }

}

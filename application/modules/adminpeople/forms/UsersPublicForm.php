<?php
/**
 * File generated by the Sydney_Admin_Generator on the Mar 21, 2010 10:05:29 PM by arnaud@antidot.ca
 */

/**
 * Form to manage the data from the users table
 * @package Admindb
 * @subpackage FormmodelGenerated
 */
class UsersPublicForm extends Sydney_Form
{
    public $password;
    public $password2;

    public function __construct($options = null, $memberOfGroups = array(), $modeEdit = false)
    {
        parent :: __construct($options);

        // get request
        $request = UsersFormOp::getParams('request');

        $this->setAttrib('accept-charset', 'UTF-8');
        $this->setName('users');
        $this->setAction('/publicms/profile/processuser/format/json');
        $id = new Zend_Form_Element_Hidden('id');

        $module = new Zend_Form_Element_Hidden('module');
        $module->setValue($request->module);

        $controller = new Zend_Form_Element_Hidden('controller');
        $controller->setValue($request->controller);

        $login = new Zend_Form_Element_Text('login');
        $login->setLabel('E-mail');
        $login->addValidator(new Zend_Validate_EmailAddress());
        //$login->setDescription('Must be a valid e-mail address');

        if ($modeEdit) {
            $login->setAttrib('disabled', true);
            $login->setAttrib('readonly', true);
        } else {
            $login->setRequired();
        }
        /**
         * Password field
         * @var void
         */
        $password = new Zend_Form_Element_Password('password');
        $password->setLabel('password');

        $password->addValidator(new Zend_Validate_StringLength(array(
            'min' => 6,
            'max' => 12
        )));

        $password2 = new Zend_Form_Element_Password('password2');
        $password2->setLabel('password2');
        $this->password = $password;
        $this->password2 = $password2;

        if (!$modeEdit) {
            $password->setRequired();
            $password2->setRequired();
        }

        $fileName = new Zend_Form_Element_Text('fname');
        $fileName->setLabel('fname');
        $fileName->setRequired();

        $lastName = new Zend_Form_Element_Text('lname');
        $lastName->setLabel('lname');
        $lastName->setRequired();

        $submit = new Zend_Form_Element_Submit('submit', 'Save user');
        $submit->setAttrib('id', 'submitbuttonuser');

        // Init list of elements
        $elementsList[0] = array(
            $login,
            $password,
            $password2
        );
        $elementsList[1] = array(
            $fileName,
            $lastName,
            $submit,
            $id,
            $module,
            $controller
        );

        $this->addElements($elementsList[0]);
        $this->addElements($elementsList[1]);
    }

}

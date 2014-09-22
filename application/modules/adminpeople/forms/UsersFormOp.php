<?php
/**
 * File generated by the Sydney_Admin_Generator on the Mar 21, 2010 10:05:29 PM by arnaud@antidot.ca
 */

/**
 * Form to manage the data from the users table
 * @package Admindb
 * @subpackage FormmodelGenerated
 */
class UsersFormOp extends Sydney_Form
{
    public $password;
    public $password2;

    public function __construct($options = null, $memberOfGroups = array(), $modeEdit = false)
    {
        parent :: __construct($options);

        // get request
        $request = UsersFormOp::getParams('request');
        $postedDatas = UsersFormOp::getParams('data');

        $this->setAttrib('accept-charset', 'UTF-8');
        $this->setName('users');

        $id = new Zend_Form_Element_Hidden('id');

        // Url forward in case of form success
        $urlForwards = new Zend_Form_Element_Hidden('urlforwards');
        $urlForwards->setValue('/adminpeople/index/index/');

        $module = new Zend_Form_Element_Hidden('module');
        $module->setValue($request->module);

        $controller = new Zend_Form_Element_Hidden('controller');
        $controller->setValue($request->controller);

        $login = new Zend_Form_Element_Text('login');
        $login->setLabel('login');

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
        if (!$modeEdit) {
            $password->setRequired();
        }
        $password->addValidator(new Zend_Validate_StringLength(array(
            'min' => 6,
            'max' => 12
        )));

        $password2 = new Zend_Form_Element_Password('password2');
        $password2->setLabel('password2');
        $this->password = $password;
        $this->password2 = $password2;

        $fileName = new Zend_Form_Element_Text('fname');
        $fileName->setLabel('fname');
        $fileName->setRequired();

        $mdName = new Zend_Form_Element_Text('mdname');
        $mdName->setLabel('mdname');

        $lastName = new Zend_Form_Element_Text('lname');
        $lastName->setLabel('lname');
        $lastName->setRequired();

        $phone = new Zend_Form_Element_Text('phone');
        $phone->setLabel('phone');

        $cell = new Zend_Form_Element_Text('cell');
        $cell->setLabel('cell');

        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('email');
        $email->addValidator(new Zend_Validate_EmailAddress());

        $submit = new Zend_Form_Element_Submit('submit', 'Save user');
        $submit->setAttrib('id', 'submitbuttonuser');

        // Init list of elements
        $elementsList[0] = array(
            $id,
            $module,
            $controller,
            $urlForwards,
            $login,
            $password,
            $password2
        );
        $elementsList[1] = array(
            $fileName,
            $mdName,
            $lastName,
            $phone,
            $cell,
            $email,
            $submit
        );

        if (($request->module != 'publicms' && $request->controller != 'profile')
            && ($postedDatas['module'] != 'publicms' && $postedDatas['controller'] != 'profile')
        ) {

            $usersgroupsId = new Zend_Form_Element_Select('usersgroups_id');
            $options = new Usersgroups();
            foreach ($options->fetchAlltoFlatArray() as $k => $v) {
                if (in_array($k, $memberOfGroups)) {
                    $usersgroupsId->addMultiOption($k, $v['mlabel']);
                }
            }
            $usersgroupsId->setLabel('usersgroups_id');
            $usersgroupsId->setRequired();

            $valid = new Zend_Form_Element_Checkbox('valid');
            $valid->setLabel('valid');

            $active = new Zend_Form_Element_Checkbox('active');
            $active->setLabel('active');

            $elementsList[0] = array(
                $id,
                $module,
                $controller,
                $urlForwards,
                $login,
                $password,
                $password2,
                $usersgroupsId
            );
            $elementsList[1] = array(
                $fileName,
                $mdName,
                $lastName,
                $phone,
                $cell,
                $email,
                $valid,
                $active,
                $submit
            );

        }

        $this->addElements($elementsList[0]);
        $this->addElements($elementsList[1]);
    }

}
<?php
/**
 * File generated by the Sydney_Admin_Generator
 */

/**
 * Sub form to manage the many to many links contained in the pagstructure object
 */
class PagstructurelistForm extends Zend_Form_Element_MultiCheckbox
{
    public function init()
    {
        $options = new Pagstructure();
        foreach ($options->fetchAlltoFlatArray() as $k => $v) {
            if (isset($v['mlabel'])) {
                $this->addMultiOption($k, $v['mlabel']);
            }
        }
    }
}

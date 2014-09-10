<?php

/**
 *
 * @package SydneyLibrary
 * @subpackage Form
 */
class Sydney_Form extends Zend_Form
{
    protected $safinstancesid = null;
    protected $config;
    protected $registry;
    /*
     * Zend_Db_Table_Row object
     */
    private static $params = array();

    /**
     *  setRow
     * @param Zend_Db_Table_Row $rowObject
     */
    public static function setParams($params, $key = '')
    {
        if (!empty($key)) {
            self::$params[$key] = $params;
        } elseif (is_array($params)) {
            self::$params = $params;
        }
    }

    public static function getParams($key = '')
    {
        if (empty($key)) {
            return self::$params;
        } else {
            return self::$params[$key];
        }
    }

    /**
     * Constructor overriding the parent constructor
     *
     * @param mixed $options
     * @return void
     */
    public function __construct($options = null)
    {
        $this->registry = Zend_Registry :: getInstance();
        $this->config = $this->registry->get('config');

        $this->safinstancesid = $this->config->db->safinstances_id;

        $this->addElementPrefixPath('Antidot', 'Sydney/');
        parent::__construct($options);
        $this->setAttrib('accept-charset', 'UTF-8');
    }

    /**
     * Setup the translation, this is not used for the moment
     *
     * @return void
     * @todo See if we need to implement that to make translation work automatically
     */
    protected function _setupTranslation()
    {
        $registry = Zend_Registry :: getInstance();
        $translate = $registry->get('Zend_Translate');
        $this->setTranslator($translate);
    }
}

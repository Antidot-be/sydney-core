<?php
/**
 * File generated by the Sydney_Admin_Generator v2.0
 */

/**
 * Mapping of the SafinstancesSafmodules table in an object
 * @package Admindb
 * @subpackage ModelGenerated
 */
class SafinstancesSafmodules extends SafinstancesSafmodulesOp
{
    protected $_schema = 'sydney';
    protected $_name = 'safinstances_safmodules';
    protected $_referenceMap = array(
        'Safinstances' => array(
            'columns'       => 'safinstances_id',
            'refTableClass' => 'Safinstances',
            'refColumns'    => 'id'
        ),
        'Safmodules'   => array(
            'columns'       => 'safmodules_id',
            'refTableClass' => 'Safmodules',
            'refColumns'    => 'id'
        ),
    );
    protected $_primary = 'id';

    public $fieldsNames = array(
        'safinstances_id', // bigint()
        'safmodules_id', // bigint()
        'id', // bigint()
    );

    protected $_metadata = array(
        'safinstances_id' => array(
            'SCHEMA_NAME'      => null,
            'TABLE_NAME'       => "safinstances_safmodules",
            'COLUMN_NAME'      => "safinstances_id",
            'COLUMN_POSITION'  => 1,
            'DATA_TYPE'        => "bigint",
            'DEFAULT'          => null,
            'NULLABLE'         => false,
            'LENGTH'           => null,
            'SCALE'            => null,
            'PRECISION'        => null,
            'UNSIGNED'         => null,
            'PRIMARY'          => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY'         => false,
        ),
        'safmodules_id'   => array(
            'SCHEMA_NAME'      => null,
            'TABLE_NAME'       => "safinstances_safmodules",
            'COLUMN_NAME'      => "safmodules_id",
            'COLUMN_POSITION'  => 2,
            'DATA_TYPE'        => "bigint",
            'DEFAULT'          => null,
            'NULLABLE'         => false,
            'LENGTH'           => null,
            'SCALE'            => null,
            'PRECISION'        => null,
            'UNSIGNED'         => null,
            'PRIMARY'          => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY'         => false,
        ),
        'id'              => array(
            'SCHEMA_NAME'      => null,
            'TABLE_NAME'       => "safinstances_safmodules",
            'COLUMN_NAME'      => "id",
            'COLUMN_POSITION'  => 3,
            'DATA_TYPE'        => "bigint",
            'DEFAULT'          => null,
            'NULLABLE'         => false,
            'LENGTH'           => null,
            'SCALE'            => null,
            'PRECISION'        => null,
            'UNSIGNED'         => null,
            'PRIMARY'          => true,
            'PRIMARY_POSITION' => 1,
            'IDENTITY'         => true,
        ),
    );

    /**
     * Returns the list of IDs linked to a "Safinstances"
     *
     * @param Int $id ID of the safmodules
     * @return Array list of IDs
     */
    public function getSafinstancesLinkedTo($id)
    {
        if (preg_match("/[0-9]{1,9}/", $id)) {
            $toreturn = array();
            $rows = $this->fetchAll($this->select()->where('safmodules_id = ?', $id));
            foreach ($rows->toArray() as $v) {
                $toreturn[] = $v['safinstances_id'];
            }

            return $toreturn;
        } else {
            return array();
        }
    }

    /**
     * Sets the data linked to an id in the "Safinstances" table
     *
     * @param Int $id ID of the safmodules
     * @param Array list of IDs
     * @return Boolean true if success and false if there were an error
     */
    public function setSafinstancesLinkedTo($id, array $data)
    {
        if (preg_match("/[0-9]{1,9}/", $id) && is_array($data)) {
            $where = $this->getAdapter()->quoteInto('safinstances_id = ?', $id);
            $this->delete($where);
            foreach ($data as $v) {
                $this->insert(array(
                    'safinstances_id' => $id,
                    'safmodules_id'   => $v
                ));
            }
        } else {
            return false;
        }
    }

    /**
     * Returns the list of IDs linked to a "Safmodules"
     *
     * @param Int $id ID of the safinstances
     * @return Array list of IDs
     */
    public function getSafmodulesLinkedTo($id)
    {
        if (preg_match("/[0-9]{1,9}/", $id)) {
            $toreturn = array();
            $rows = $this->fetchAll($this->select()->where('safinstances_id = ?', $id));
            foreach ($rows->toArray() as $v) {
                $toreturn[] = $v['safmodules_id'];
            }

            return $toreturn;
        } else {
            return array();
        }
    }

    /**
     * Sets the data linked to an id in the "Safmodules" table
     *
     * @param Int $id ID of the safinstances
     * @param Array list of IDs
     * @return Boolean true if success and false if there were an error
     */
    public function setSafmodulesLinkedTo($id, array $data)
    {
        if (preg_match("/[0-9]{1,9}/", $id) && is_array($data)) {
            $where = $this->getAdapter()->quoteInto('safmodules_id = ?', $id);
            $this->delete($where);
            foreach ($data as $v) {
                $this->insert(array(
                    'safmodules_id'   => $id,
                    'safinstances_id' => $v
                ));
            }
        } else {
            return false;
        }
    }

    public function __construct($id = 0)
    {
        $this->_schema = Sydney_Tools_Sydneyglobals::getConf()->db->params->dbname;
        parent::__construct($id);
    }

}

<?php
include_once('Zend/Db/Table.php');

/**
 * This class extends the Zend_Db_Table and adds interesting methods
 * for data manipulation and the Sydney_Admin_Generator tool.
 * All the objects mapped from the DB should be an extension of this class.
 *
 * @package AntidotLibrary
 * @subpackage Db
 * @version $Id: Table.php 144 2008-08-06 03:58:19Z arnaud $
 * @author Arnaud Selvais <arnaud@antidot.com>
 * @since 01-Jun-08
 *
 * @todo Cache a part of the data for small tables
 */
class Sydney_Db_Table extends Zend_Db_Table
{
    protected $_registry;
    protected $_translate;
    protected $safinstancesId;
    protected $row = null; // row object

    /**
     * Initialize the class.
     * Add the log object here to log the actions on the DB.
     */
    public function init()
    {
        $this->_registry = Zend_Registry::getInstance();
        $this->_translate = Zend_Registry::get('Zend_Translate');
        $this->_db->exec("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");
        $this->safinstancesId = Zend_Registry::get('config')->db->safinstances_id;
    }

    public function __construct($id = 0)
    {
        parent::__construct();
        if (is_numeric($id) && $id > 0) {
            $rowset = $this->find($id);
            $this->row = $rowset[0];
        }
    }

    /**
     *
     */
    public function get($id = 0, $forceReload = false)
    {
        if ($forceReload || ($id != 0 && ($this->row === null || $this->row->id != $id))) {
            $this->load($id);
        }

        return $this->row;
    }

    /**
     *
     */
    public function getRow()
    {
        return $this->get();
    }

    /**
     *
     */
    public function set($value)
    {
        if (is_object($value) && get_class($value) == 'Zend_Db_Table_Row') {
            $this->row = $value;
        } else {
            $this->load($value);
        }

        return $this;
    }

    /**
     *
     */
    private function load($id)
    {
        $this->row = $this->find($id)->current();

        return $this;
    }

    public function __toString()
    {
        return $this->get()->label;
    }

    /**
     * Returns all the data as a flat array and add the guested label in the
     * 'mlabel' parameter.
     *
     * @param array $rowset
     * @param string $idkey colomn name where we ll find the PK
     * @return array Array of rowset flattened
     */
    public function rowset_to_flat_array($rowset, $idkey = 'id')
    {
        $posslabels = array(
            'name',
            'label',
            'login',
            'elementname',
            'classname',
            'custname',
            'labelen',
            'id'
        );
        $toreturn = array();
        foreach ($rowset as $c) {
            $toreturn[($c[$idkey])] = $c;
            foreach ($toreturn[($c[$idkey])] as $k => $v) {
                if (in_array($k, $posslabels)) {
                    $toreturn[($c[$idkey])]['mlabel'] = $v;
                }
            }
        }

        return $toreturn;
    }

    /**
     * Returns a flat array with keys as keys and label as value.
     *
     * @param int $limit Do not return more entries that this number (default is 300)
     * @param string $where
     * @return Array list of labels with the ID as key
     */
    public function fetchAlltoFlatArray($limit = 300, $where = ' 1=1 ')
    {
        return $this->rowset_to_flat_array($this->fetchAll($this->select()->limit($limit)->where($where))->toArray());
    }

    /**
     * returns a list of labels with the IDs as key
     *
     * @param int $limit Do not return more entries that this number (default is 300)
     * @return Array list of labels with the ID as key
     */
    public function fetchLabelstoFlatArray($limit = 300, $line = true)
    {
        if ($line) {
            $toreturn = array('' => '----------');
        } else {
            $toreturn = array();
        }
        foreach ($this->rowset_to_flat_array($this->fetchAll($this->select()->limit($limit))->toArray()) as $k => $v) {
            $toreturn[$k] = $v['mlabel'];
        }

        return $toreturn;
    }

    /**
     *
     */
    public function fetchLabelstoYUIarray($limit = 30, $fieldname = '', $query = '', $adcol = null)
    {
        $toreturn = array();
        foreach ($this->rowset_to_flat_array(
                     $this->fetchAll(
                         $this->select()->limit($limit)
                             ->where($fieldname . ' LIKE ?', $query . '%')
                     )->toArray()) as $k => $v) {
            if ($adcol == null) {
                $toreturn[] = array('id' => $k, 'name' => $v[$fieldname]);
            } else {
                $toreturn[] = array(
                    'id'   => $k,
                    'name' => $v[$fieldname],
                    $adcol => $v[$adcol]
                );
            }
        }

        return $toreturn;
    }

    /**
     *
     */
    public function fetchLabelstoYUIarrayFilter($limit = 30, $fieldname = '', $where = '', $adcol = null)
    {
        $toreturn = array();
        foreach ($this->rowset_to_flat_array(
                     $this->fetchAll(
                         $this->select()->limit($limit)
                             ->where($where)
                     )->toArray()) as $k => $v) {
            if ($adcol == null) {
                $toreturn[] = array('id' => $k, 'name' => $v[$fieldname]);
            } else {
                $toreturn[] = array(
                    'id'   => $k,
                    'name' => $v[$fieldname],
                    $adcol => $v[$adcol]
                );
            }
        }

        return $toreturn;
    }

    /**
     * Returns the data in a YUI formated array for use with the YUI dataTable widget
     *
     * @param string|array|Zend_Db_Table_Select $where OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array $order OPTIONAL An SQL ORDER clause.
     * @param int $count OPTIONAL An SQL LIMIT count.
     * @param int $offset OPTIONAL An SQL LIMIT offset.
     * @param string $hidefields OPTIONAL List of fields to hide separated by a comma.
     * @param string $group OPTIONAL An SQL GROUP clause
     * @param string $columns OPTIONAL An SQL COLUMNS clause
     * @return Array Formated array for YUI DataTable
     *
     * @todo TODO cache the count value
     */
    public function fetchdatatoYUI($where = null, $order = null, $count = null, $offset = null, $hidefields = null, $group = null, $columns = null)
    {
        $result = $dataTranslation = array();
        $msql = 'SELECT count(*) as cnt FROM ' . $this->_schema . '.' . $this->_name;
        if ($where != null) {
            $msql .= ' WHERE ' . $where;
        }
        $cnt = $this->_db->fetchAll($msql);
        $totalRecords = intval($cnt[0]['cnt']);
        $hidefieldsA = array();
        if ($hidefields != null) {
            $hidefieldsA = explode(',', $hidefields);
        }

        $metadatas = $this->getMetadata();

        // build result
        $select = $this->select();
        if ($where !== null) {
            $select = $select->where($where);
        }
        if ($order !== null) {
            $select = $select->order($order);
        }
        if ($count !== null || $offset !== null) {
            $select->limit($count, $offset);
        }
        if ($group !== null && $group !== '') {
            $select = $select->group($group);
        }
        if ($columns !== null && $columns !== '') {
            $select = $select->from($this->getTableName(true))->columns($columns);
        }
        foreach ($this->fetchAll($select)->toArray() as $d) {
            $dd = array();
            foreach ($d as $k => $v) {
                if ($hidefields == null || in_array($k, $hidefieldsA)) {

                    /**
                     * Translate ID field by label
                     * On va essayer de remplacer les "id link" par des "labels" compr�hensible par le
                     * commun des mortels
                     */
                    // check if field is an ID
                    if (!array_key_exists($k, $dataTranslation)) {

                        if (strtolower(substr($k, -2)) == 'id') {
                            $dataTranslation[$k]['hasChecked'] = true;
                            $dataTranslation[$k]['hasValue'] = false;

                            // Mapping 'field link' => 'ClassName'
                            $mappingClassName = array('tags_id' => 'Filfolders');
                            if (!($className = $mappingClassName[$k])) {
                                if (strtolower(substr($k, -3)) == '_id') {
                                    $className = ucfirst(substr($k, 0, -3));
                                } else {
                                    $className = ucfirst(substr($k, 0, -2));
                                }
                            }

                            // On v�rifie s'il existe une classe(Zend_Db_Table)
                            if (@class_exists($className)) {
                                // get list of datas
                                $fieldname = '';
                                $myobject = new $className;

                                if (is_array($myobject->fieldsNames)) {
                                    // check if field label or name exist
                                    if (in_array('label', $myobject->fieldsNames)) {
                                        $fieldname = 'label';
                                    } elseif (in_array('name', $myobject->fieldsNames)) {
                                        $fieldname = 'name';
                                    } elseif (in_array('Name', $myobject->fieldsNames)) {
                                        $fieldname = 'Name';
                                    } elseif (in_array('fname', $myobject->fieldsNames)) {
                                        $fieldname = array('fname', 'lname');
                                    }

                                    // Si un des champs suivants existent alors on remplace l'id :
                                    // label, name, Name, fname
                                    if (!empty($fieldname)) {
                                        $dataTranslation[$k]['hasValue'] = true;
                                        $fetchedResult = $myobject->fetchAll();
                                        //print_r($fetchedResult);exit;
                                        foreach ($fetchedResult as $row) {
                                            $myobject->set($row);
                                            try {
                                                $dataTranslation[$k]['list'][$row['id']] = $myobject->__toString();
                                            } catch (Exception $e) {
                                                $dataTranslation[$k]['list'][$row['id']] = $row[$fieldname];
                                            }
                                        }
                                    }
                                }

                            }
                        }
                    }
                    // END - check if field is ID
                    /*
                     * END - Translate ID field by label
                     **/

                    // translate
                    if (key_exists($k, $dataTranslation) && $dataTranslation[$k]['hasValue']) {
                        $dd[$k] = $dataTranslation[$k]['list'][$v];
                    } else {
                        //if (preg_match('/^[0-9]{1,100}$/', $v)) {
                        if ($metadatas[$k]['DATA_TYPE'] == 'int' || $metadatas[$k]['DATA_TYPE'] == 'bigint') {
                            $dd[$k] = intval($v);
                        } else {
                            $dd[$k] = $v;
                        }
                    }
                }
            }
            $result[] = $dd;
        }

        $srt = preg_split('/ /', $order);
        if (isset($srt[1])) {
            $dir = $srt[1];
        } else {
            $dir = 'ASC';
        }

        return array(
            'recordsReturned' => count($result),
            'totalRecords'    => $totalRecords,
            'startIndex'      => $offset,
            'sort'            => $srt[0],
            'dir'             => $dir,
            'pageSize'        => $pageSize,
            'totalRecords'    => $totalRecords,
            'Result'          => $result
        );
    }

    /**
     * (non-PHPdoc)
     * @see Zend_Db_Table_Abstract::insert()
     */
    public function insert(array $data)
    {
        $toreturn = parent::insert($data);

        if (!$toreturn) {
            $toreturn = $this->_db->lastInsertId();
        }

        $this->log('INSERT:entry with the id ' . $toreturn . ' inserted');

        return $toreturn;
    }

    /**
     * update data overloaded to log the action
     */
    public function update(array $data, $where)
    {
        $toreturn = parent::update($data, $where);
        if ($toreturn > 0) {
            $this->log('UPDATE:' . $toreturn . ' row(s) updated');
        } else {
            $this->log('UPDATE:no row(s) updated', Zend_Log::ERR);
        }

        return $toreturn;
    }

    /**
     * delete data overloaded to log the action
     */
    public function delete($where)
    {
        $toreturn = parent::delete($where);
        if ($toreturn > 0) {
            $this->log('DELETE:' . $toreturn . ' row(s) deleted');
        } else {
            $this->log('DELETE:no row(s) deleted', Zend_Log::ERR);
        }

        return $toreturn;
    }

    /**
     * Log data with the logger inisialized in the init() method
     */
    protected function log($entry, $type = Zend_Log::NOTICE)
    {
        if (!is_object($this->logger)) {
            $this->logger = new Sydney_Log();
            $this->logger->setEventItem('className', get_class($this));
            $this->logger->addFilterDatabase();
        }
        $this->logger->log($entry, $type);
    }

    /**
     * Updates one field, this is to be used with the YUI dataGrid.
     *
     * The array passed as argument must have the following structure:
     *
     * <code>
     * array(
     *    'action' => 'cellEdit',
     *    'column' => '$fieldName',
     *    'id' => '$id',
     *    'newValue' => '$newValue',
     *    'oldValue' => '$oldValue'
     * );
     * </code>
     * The returned array will have the following structure:
     * <code>
     * return array(
     *        'status'    => $status,
     *        'newValue'    => $val
     *    );
     * </code>
     * @todo TODO Add a security check on the safinstances_id level
     * @param array $d
     * @return array
     */
    public function updateOneField($d = array())
    {
        $status = 'undefined';
        $val = $d['oldValue'];
        if ($d['action'] == 'cellEdit' && in_array($d['column'], $this->fieldsNames) && preg_match('/^[0-9]{1,80}$/', $d['id'])) {
            $resu = $this->update(array(
                $d['column'] => $d['newValue']
            ), "id = " . $this->_db->quote($d['id']));
            if ($resu == 1) {
                $status = 'ok';
                $val = $d['newValue'];
            } else {
                $status = 'Error: 0 or more than 1 rows affected';
            }
        } else {
            $status = 'Error: invalid data';
        }

        return array(
            'status'   => $status,
            'newValue' => $val
        );
    }

    /**
     * get table name
     * @param bool|string $withDbName
     */
    public function getTableName($withDbName = false)
    {
        if (is_bool($withDbName) && $withDbName) {
            return $this->getDatabaseName() . '.' . $this->_name;
        } elseif (is_string($withDbName) && $withDbName != '') {
            return $withDbName . '.' . $this->_name;
        } else {
            return $this->_name;
        }
    }

    public function getSchema()
    {
        return $this->getDatabaseName();
    }

    public function getDatabaseName($classname = '')
    {
        if (!empty($classname)) {
            $obj = new $classname;

            return $obj->info('schema');
        }

        return $this->info('schema');
    }

    public function getMetadata()
    {
        return $this->_metadata;
    }

    /**
     *
     * Can be used to delete one or more rows from a datatable
     * and return an array with appropriate values to put in the view
     *
     * @param string $id List of ids separated by a column
     * @param int $safinstancesId
     */
    public function deleteRowForGrid($id, $safinstancesId = null)
    {
        $toret = array();
        $nbrRowsDeleted = 0;
        $rowsid = 0;
        if (isset($id) && preg_match('/^[0-9,]{1,50}$/', $id)) {
            if (in_array('safinstances_id', $this->fieldsNames) && $safinstancesId != null) {
                $control = ' AND safinstances_id = ' . $this->_db->quote($safinstancesId);
            } else {
                $control = '';
            }
            $nbrRowsDeleted = $this->delete("id IN (" . $id . ") " . $control);
            $rowsid = $id;
        }
        $toret['rowsDeleted'] = $rowsid;
        $toret['timeout'] = 2;
        $toret['modal'] = false;
        $toret['message'] = 'Rows deleted: ' . $nbrRowsDeleted;

        if ($nbrRowsDeleted == 0) {
            $toret['message'] = 'ERROR! Rows NOT deleted: ';
            $toret['status'] = 0;

            if (method_exists($this->info('name'), 'getGtpInterface')) {
                $gtp = $this->getGtpInterface();

                if (is_object($gtp)) {
                    if ($gtp->getProcess()->hasError()) {
                        $toret['message'] = 'GTP ERROR (' . $gtp->getProcess()->getError() . '): ' . $gtp->getProcess()->getErrorDescription();
                    }
                }
            }
        } else {
            $toret['status'] = 1;
        }

        return $toret;
    }

    /**
     * Convenience function for updating a m2m relationship
     * @param array $data
     * @param String $fX table name in X
     * @param String $fY table name in Y
     */
    public function updateM2M($data, $fX = '', $fY = '')
    {
        $toret = array();
        $nbrRowsUpdated = 0;
        $nbrRowsDeleted = 0;

        $fXl = $fX . '_id';
        $fYl = $fY . '_id';
        if ($fXl == $fYl) {
            $fYl .= '2';
        }

        foreach ($data as $daa) {
            foreach ($daa as $d => $v) {
                $e = explode('_', $d);
                $da = array(
                    $fXl => $e[0],
                    $fYl => $e[1]
                );

                $nbrRowsDeleted += $rDeleted = $this->delete($fXl . " = '" . $e[0] . "' AND " . $fYl . " = '" . $e[1] . "' ");

                if ($v) {
                    $nbrRowsUpdated += $rInserted = count($this->insert($da));
                    if ($rInserted) {
                        if (!$rDeleted) {
                            $toret['datas']['insert'][(int) $keyInsert++] = $da;
                        }
                    }
                } elseif ($rDeleted) {
                    $toret['datas']['delete'][(int) $keyDelete][$fXl] = $e[0];
                    $toret['datas']['delete'][(int) $keyDelete++][$fYl] = $e[1];
                }
            }
        }
        $toret['timeout'] = 2;
        $toret['modal'] = false;
        $toret['message'] = 'Rows updated: ' . $nbrRowsUpdated . ' ; Rows deleted: ' . $nbrRowsDeleted;
        if ($nbrRowsUpdated == 0) {
            $toret['status'] = 0;
        } else {
            $toret['status'] = 1;
        }

        return $toret;
    }

    /**
     *
     */
    public function getFieldLabel()
    {
        return array('label');
    }

    /**
     *
     */
    public function getFieldValue()
    {
        return array('id');
    }

    /**
     *
     * @param Zend_Db_Table_Row $row
     */
    public function getValue(Zend_Db_Table_Row $row, $lang = 'en')
    {
        $returnLabel = '';
        $labels = $this->getFieldValue();

        foreach ($labels as $label) {
            if (in_array($label . '_' . $lang, $this->fieldsNames)) {
                $label = $label . '_' . $lang;
            }
            $returnLabel .= $row->$label;
        }

        return $returnLabel;
    }

    /**
     *
     * @param Zend_Db_Table_Row $row
     */
    public function getLabel(Zend_Db_Table_Row $row, $lang = 'en')
    {
        $returnLabel = '';
        $labels = $this->getFieldLabel();

        foreach ($labels as $label) {
            if (in_array($label . '_' . $lang, $this->fieldsNames)) {
                $label = $label . '_' . $lang;
            }
            $returnLabel .= $row->$label . ' ';
        }

        return $returnLabel;
    }

    /**
     *
     * @param $filters
     */
    public function getFilterSQLfromTblFilters($filters)
    {
        $fltrs = array();
        $rfe = null;
        if (isset($filters)) {
            $rs = Zend_Json::decode($filters);
            if (is_array($rs)) {
                foreach ($rs as $fle) {
                    $fltrs[] = " " . strtolower($fle[0]) . "_id = '" . $fle[1] . "' ";
                }
            }
            $rfe = implode(' AND ', $fltrs);
        }

        return $rfe;
    }

    public function __toArray()
    {
        $datas = array();
        foreach ($this->fieldsNames as $field) {
            if (!empty($this->get()->$field)) {
                $datas[$field] = $this->get()->$field;
            }
        }

        return $datas;
    }

}

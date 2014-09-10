<?php

/**
 * Utilities for DB info
 *
 */
class Sydney_Tools_Db extends Sydney_Tools
{
    /**
     *
     * @param unknown_type $object
     */
    public static function getTableName($object)
    {

        if (is_object($object)) {
            if (get_class($object) == 'Zend_Db_Table_Row') {
                return $object->getTable()->info('name');
            } elseif (method_exists($object, 'info')) {
                $info = $object->info();

                return $info['name'];
            }
        }

        return Sydney_Tools::_('unknow_table');
    }

}

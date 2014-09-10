<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Translate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Csv.php 21661 2010-03-27 20:20:27Z thomas $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Locale */
require_once 'Zend/Locale.php';

/** Zend_Translate_Adapter */
require_once 'Zend/Translate/Adapter.php';

/**
 * @category   Zend
 * @package    Zend_Translate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Sydney_Translate_Adapter_Db extends Zend_Translate_Adapter
{
    private $_data = array();

    /*
     * Load translation data
     *
     * @param  string|array  $tableName  tableName OR tableName list (separate by ",") of the translation source
     * @param  string        $locale    Locale/Language to add data for, identical with locale identifier,
     *                                  see Zend_Locale for more information
     * @param  array         $option    OPTIONAL Options to use
     * @return array
     */
    protected function _loadTranslationData($tableName, $locale, array $options = array())
    {
        $this->_data = array();
        $locale = empty($locale) ? new Zend_Locale() : $locale;
        //$options     = $options + $this->_options;

        // On récupère les données
        $tranlation = new TranslationData();
        $tranlationDatas = $tranlation->getByTableName(Array($tableName), $locale);

        foreach ($tranlationDatas as $data) {

            if (!isset($data->label)) {
                continue;
            }

            $this->_data[$locale][$data->tbl_id . '_' . $data->tbl_name . (empty($data->tbl_field) ? '' : '_' . $data->tbl_field)] = $data->label;

        }

        return $this->_data;
    }

    public function toString()
    {
        return 'Db';
    }

}

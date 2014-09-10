<?php
/**
 * Container for the AdminGenerator class.
 *
 * @category default
 * @package default
 * @copyright Copyright &copy; 2008, Antidot s.a. Belgium / Antidot Inc. Canada
 * @version    $Id$
 */

/**
 * This is a basic class to generate the admin part from a DB structure.
 * We will get the DB structure, and generate the model, form and controller according to it.
 *
 * @package AntidotLibrary
 * @subpackage Admin
 * @author Arnaud Selvais <arnaud@antidot.com>
 * @since 28-May-08
 * @subpackage AdminGenerator.php
 *
 * @todo Add comments on tables and tooltips for fields form PHPmyadmin data
 */
class Sydney_Admin_Generator
{
    /**
     * @var Array Array containing the log of the class
     */
    protected static $log = array();
    /**
     * @var Zend_Log Zend_Log instance
     */
    protected static $logger;
    /**
     * @var Zend_Config_Ini Zend config Object
     */
    protected static $config;
    /**
     * @var Zend_Db Zend DB object
     */
    protected static $db;
    /**
     * @var string login name of the person instantiating this class
     */
    protected static $useridentity;
    protected static $generalModelPath = '';
    protected static $generalModelformPath = '';
    /**
     * @var string Path to the module we want to generate the file into
     */
    protected static $modulePath;
    /**
     * Array containing the DB structure in this format:
     * <code>
     * Array
     * (
     *     [companies] => Array
     *         (
     *             [id] => Array
     *                 (
     *                     [SCHEMA_NAME] =>
     *                     [TABLE_NAME] => companies
     *                     [COLUMN_NAME] => id
     *                     [COLUMN_POSITION] => 1
     *                     [DATA_TYPE] => bigint
     *                     [DEFAULT] =>
     *                     [NULLABLE] =>
     *                     [LENGTH] =>
     *                     [SCALE] =>
     *                     [PRECISION] =>
     *                     [UNSIGNED] =>
     *                     [PRIMARY] => 1
     *                     [PRIMARY_POSITION] => 1
     *                     [IDENTITY] => 1
     *                 )
     *            ...
     * </code>
     */
    protected static $dbstructure = array();
    /**
     * containing the many to many links.
     * Example:
     * <code>
     * Array
     * (
     *     [contracts] => Array
     *         (
     *             [0] => services
     *         )
     *
     *     [services] => Array
     *         (
     *             [0] => contracts
     *             [1] => flows
     *             [2] => tariffplans
     *         )
     *
     *     [form] => Array
     *         (
     *             [0] => formdisplaygroup
     *         )
     *  ...
     * </code>
     */
    protected static $many2many = array();
    protected static $todoTables = array();
    /**
     * Contains the one to many links.
     * Example:
     * <code>
     * Array
     * (
     *     [contracts] => Array
     *         (
     *             [0] => customers
     *             [1] => users
     *             [2] => tariffplans
     *             [3] => flows
     *         )
     *
     *     [customers] => Array
     *         (
     *             [0] => customertype
     *         )
     *
     *     [flows] => Array
     *         (
     *             [0] => customertype
     *         )
     * ...
     * </code>
     */
    protected static $one2many = array();
    /**
     * dependent table mapping
     */
    protected static $deptables = array();
    /**
     * Array containing the reference map for DB relationships
     */
    protected static $refmaps = array();
    protected static $moduleName;
    protected static $date;

    /**
     *
     */
    public function __construct($path, $moduleName, $generalModelPath = '', $generalModelformPath = '')
    {
        if ($generalModelPath != '') {
            self::$generalModelPath = $generalModelPath;
        }
        if ($generalModelformPath != '') {
            self::$generalModelformPath = $generalModelformPath;
        }
        $this->setModuleName($moduleName);
        $this->init();
        $this->setModulePath($path);
    }

    /**
     *
     */
    public function setTodoTables($todoTables)
    {
        if (is_array($todoTables)) {
            self::$todoTables = $todoTables;
        } else {
            self::$todoTables = array();
        }
    }

    /**
     * Launch the process
     */
    public function execute($database = '')
    {
        if (!empty($database)) {
            self::$config = Zend_Registry::get('config');
            self::$config->db->params->dbname = $database;
            self::$db = Zend_Db::factory(self::$config->db);
        }

        $this->setDbStructure();
        if (self::$modulePath != '') {
            Sydney_Admin_Generator_Model::generateModelObjects();
            Sydney_Admin_Generator_Formmodel::generateFormModelObjects();
            Sydney_Admin_Generator_Controller::generateControllersObjects();
            Sydney_Admin_Generator_View::generateViews();
            //$this->generateIndexController();
            $this->logResultInDb();
        } else {
            $this->log('Error: no module path defined');
        }

    }

    /**
     *
     */
    public function setModulePath($path)
    {
        self::$modulePath = $path;
    }

    public function setModuleName($moduleName)
    {
        self::$moduleName = $moduleName;
    }

    /**
     *
     */
    protected function setDbStructure()
    {
        foreach (self::$db->listTables() as $table) {
            if (preg_match("/_/", $table) && !preg_match("/^view_/", $table)) {
                $this->log('Found m-to-m in "' . $table . '" table ...', Zend_Log::INFO);
                $t = preg_split('/_/', $table);
                if (!isset(self::$many2many[($t[0])])) {
                    self::$many2many[($t[0])] = array();
                }
                if (!isset(self::$many2many[($t[1])])) {
                    self::$many2many[($t[1])] = array();
                }
                $temp = array();
                foreach (self::$db->describeTable($table) as $k => $v) {
                    if (preg_match('/_/', $k)) {
                        $o = preg_split('/_/', $k);
                        if ($o[0] != $t[0] && $o[0] != 'id') {
                            self::$many2many[($t[0])][] = $o[0];
                        }
                        if ($o[0] != $t[1] && $o[0] != 'id') {
                            self::$many2many[($t[1])][] = $o[0];
                        }
                    }
                }
            }
            $this->log('Describing "' . $table . '" table ...');

            if (in_array($table, self::$todoTables) || count(self::$todoTables) == 0) {
                self::$dbstructure[$table] = self::$db->describeTable($table);
            }

            foreach (self::$db->describeTable($table) as $k => $v) {
                if (preg_match('/_id$/', $k)) {
                    if (!isset(self::$one2many[$table])) {
                        self::$one2many[$table] = array();
                    }
                    $linked = substr($k, 0, -3);
                    if ($linked == 'parent') {
                        $linked = $table;
                    }
                    self::$one2many[$table][] = $linked;
                    if (!isset(self::$deptables[$linked])) {
                        self::$deptables[$linked] = array();
                    }

                    $o = preg_split('/_/', $table);
                    $tablem = ucfirst($o[0]);
                    if (isset($o[1])) {
                        $tablem .= ucfirst($o[1]);
                    }

                    self::$deptables[$linked][] = $tablem;

                    if (!isset(self::$refmaps[$table])) {
                        self::$refmaps[$table] = array();
                    }
                    self::$refmaps[$table][$linked] = array(
                        'columns' => $linked . '_id',
                        'refTableClass' => $linked,
                        'refColumns' => 'id'
                    );

                }
            }
        }
    }

    /**
     *
     */
    protected function init()
    {
        $auth = Sydney_Auth::getInstance();
        self::$useridentity = 'NotAuthenticated';
        if ($auth->hasIdentity()) {
            self::$useridentity = $auth->getIdentity();
        }

        self::$date = new Zend_Date();

        self::$logger = new Sydney_Log();
        self::$logger->setEventItem('className', get_class($this));
        $writers = self::$logger->getWriters();
        self::$log = $writers[0];

        self::$config = Zend_Registry::get('config');
        self::$db = Zend_Db::factory(self::$config->db);
    }

    /**
     *
     */
    public function getLog()
    {
        return self::$log;
    }

    /**
     *
     */
    static protected function log($entry, $type = Zend_Log::DEBUG)
    {
        self::$logger->log($entry, $type);
    }

    /**
     * Log the action in the General Application log in the DB
     */
    protected function logResultInDb()
    {
        self::$logger->addFilterDatabase();
        $this->log('Admin generated');
    }

    /**
     *
     */
    public function getHtmlLog($simple = true)
    {
        $registry = Zend_Registry::getInstance();
        $baseUrl = ''; //$registry->get('baseUrl');

        $nolog = array();
        if ($simple) {
            $nolog = array(
                'priority',
                'identity',
                'HTTP_REFERER',
                'REMOTE_ADDR',
                'REQUEST_METHOD',
                'REQUEST_TIME'
            );
        }
        $out = '';
        $head = '';
        $u = 0;
        foreach (self::$log->events as $e) {
            $out .= '<tr>';
            $u++;
            foreach ($e as $k => $i) {
                if (!in_array($k, $nolog)) {
                    if ($k == 'priorityName') {
                        $out .= '<td><img src="/jslibs/firebug/' . $i . '.png"></td>';
                    } else {
                        $out .= '<td>' . $i . '</td>';
                    }
                    if ($u == 1) {
                        if ($k == 'priorityName') {
                            $k = '&nbsp;';
                        }
                        $head .= '<th>' . $k . '</th>';
                    }
                }
            }
            $out .= '</tr>';
            $out .= "\n";
        }

        return '<center><table class=tableDefault>' . '<tr>' . $head . '</tr>' . $out . '</table></center>';
    }

    /**
     * Writes some content into a file
     */
    static protected function writeToFile($filename, $somecontent)
    {

        if ($handle = @fopen($filename, 'r')) {
            if (!$contents = fread($handle, (1024 * 500))) {
                self::log("Cannot read file content ($filename)", Zend_Log::ERR);
                fclose($handle);
                // return false;
            } else {
                if ($contents == $somecontent) {
                    //self::log("File content did not change ($filename)", Zend_Log::WARN);
                    fclose($handle);

                    return false;
                }
                fclose($handle);
            }
        } else {
            self::log("Cannot read file ($filename)", Zend_Log::ERR);
            fclose($handle);
            // return false;
        }
        /*------------------------------------*/

        if (!$handle = fopen($filename, 'w')) {
            self::log("Cannot open file ($filename)", Zend_Log::ERR);
            fclose($handle);

            return false;
        }
        if (fwrite($handle, $somecontent) === false) {
            self::log("Cannot write to file ($filename)", Zend_Log::ERR);

            return false;
        }
        self::log("Success, wrote to file ($filename)", Zend_Log::NOTICE);
        fclose($handle);
    }
}

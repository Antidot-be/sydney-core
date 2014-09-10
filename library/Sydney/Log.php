<?php
include_once('Zend/Log.php');
include_once('Zend/Log/Writer/Mock.php');
include_once('Zend/Log/Writer/Db.php');

/**
 * Extension of the Zend_Log class with interesting information we added.
 * Priority are:
 * EMERG = 0 = Emergency: system is unusable
 * ALERT = 1 = Alert: action must be taken immediately
 * CRIT = 2 = Critical: critical conditions
 * ERR = 3 = Error: error conditions
 * WARN = 4 = Warning: warning conditions
 * NOTICE = 5 = Notice: normal but significant condition
 * INFO = 6 = Informational: informational messages
 * DEBUG = 7 = Debug: debug messages
 *
 * @package AntidotLibrary
 * @subpackage Log
 */
class Sydney_Log extends Zend_Log
{

    /*
     * Level og debug
     * 0 => no log
     * 1 => only on table applog
     * 2 => table + firebug
     */
    private $debugLevel = 2;

    /**
     * Constructor, we added some interesting info to log
     */
    public function __construct(Zend_Log_Writer_Abstract $writer = null)
    {
        if ($this->debugLevel > 0) {
            $writer = new Zend_Log_Writer_Mock;
        } else {
            $writer = new Zend_Log_Writer_Null;
        }

        parent::__construct($writer);
        $this->initNewProps();

        if ($this->debugLevel >= 2) {
            $this->addFirebugWriter();
        }

    }

    /**
     * initialize the custom logger.
     * add usefull info for us.
     */
    private function initNewProps()
    {
        // add the identity
        $auth = Sydney_Auth::getInstance();
        $identity = null;

        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
        }

        $this->setEventItem('identity', $identity);

        if (isset($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
        } else {
            $referer = '';
        }

        $this->setEventItem('HTTP_REFERER', $referer);

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $remoteAddr = $_SERVER['REMOTE_ADDR'];
        } else {
            $remoteAddr = '127.0.0.1';
        }

        $this->setEventItem('REMOTE_ADDR', $remoteAddr);

        if (isset($_SERVER['REQUEST_METHOD'])) {
            $requestMethod = $_SERVER['REQUEST_METHOD'];
        } else {
            $requestMethod = 'none';
        }

        $this->setEventItem('REQUEST_METHOD', $requestMethod);
        $this->setEventItem('REQUEST_TIME', $_SERVER['REQUEST_TIME']);
    }

    /**
     * Returns the writers used for this log
     * @return Array array of writers
     */
    public function getWriters()
    {
        return $this->_writers;
    }

    /**
     * Add Writing to Firebug
     */
    public function addFirebugWriter()
    {
        $writer = new Zend_Log_Writer_Null;
        if ($this->debugLevel > 0) {
            $writer = new Zend_Log_Writer_Firebug();
        }
        $this->addWriter($writer);
    }

    /**
     * Add the the logging in a CSV file
     */
    public function addFilterCSV()
    {
        $writer = new Zend_Log_Writer_Null;
        if ($this->debugLevel > 0) {
            $registry = Zend_Registry::getInstance();
            $config = $registry->get('config');
            $writer = new Zend_Log_Writer_Stream($config->general->rootPath . DIRECTORY_SEPARATOR . 'webinstances' . DIRECTORY_SEPARATOR . $config->general->webinstance . DIRECTORY_SEPARATOR . $config->general->logdirpath . '/general.log.csv');
            $format = '"%timestamp%","%priorityName%","%priority%","%className%","%message%","%identity%","%HTTP_REFERER%","%REMOTE_ADDR%","%REQUEST_METHOD%","%REQUEST_TIME%"' . PHP_EOL;
            $formatter = new Zend_Log_Formatter_Simple($format);
            $writer->setFormatter($formatter);
        }
        $this->addWriter($writer);
    }

    /**
     * Add the Database log filter
     */
    public function addFilterDatabase()
    {
        $writer = new Zend_Log_Writer_Null;
        if ($this->debugLevel > 0) {
            $registry = Zend_Registry::getInstance();
            $db = $registry->get('db');
            $columnMapping = array(
                'timestamp'      => 'timestamp',
                'priorityName'   => 'priorityName',
                'priority'       => 'priority',
                'className'      => 'className',
                'message'        => 'message',
                'identity'       => 'identity',
                'HTTP_REFERER'   => 'HTTP_REFERER',
                'REMOTE_ADDR'    => 'REMOTE_ADDR',
                'REQUEST_METHOD' => 'REQUEST_METHOD',
                'REQUEST_TIME'   => 'REQUEST_TIME'
            );
            $writer = new Zend_Log_Writer_Db($db, 'applog', $columnMapping);
        }
        $this->addWriter($writer);
    }

    /**
     * Returns an HTML view of the log kept in memory
     *
     * @return string HTML view of the log kept in memory
     */
    public function __toString()
    {
        $registry = Zend_Registry::getInstance();
        $baseUrl = $registry->get('baseUrl');

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
        foreach ($this->_writers[0]->events as $e) {
            $out .= '<tr>';
            $u++;
            foreach ($e as $k => $i) {
                if (!in_array($k, $nolog)) {
                    if ($k == 'priorityName') {
                        $out .= '<td><img src="' . $baseUrl . '/js/firebug/' . $i . '.png"></td>';
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
}

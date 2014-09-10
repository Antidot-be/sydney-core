<?php

/**
 * Utilities for date and time manipulation
 *
 */
class Sydney_Tools_Datetime extends Sydney_Tools
{
    /**
     * Tools for manipulation of dates
     *
     * @param unknown_type $datetime
     */
    public static function getDateObject($datetime)
    {
        return new Zend_Date($datetime, false, new Zend_Locale(self::$date_locale));
    }

    /**
     *
     * @param unknown_type $datetime
     * @param unknown_type $format
     */
    private static function getFormatedDate($datetime, $format = '')
    {
        if ($datetime == 0) {
            return '--/--/----';
        } else {
            if ($format == '') {
                return self::getDateObject($datetime)->toString();
            } else {
                return self::getDateObject($datetime)->toString($format);
            }
        }
    }

    /**
     *
     * @param unknown_type $datetime1
     * @param unknown_type $datetime2
     */
    public static function isDateEqual($datetime1, $datetime2)
    {
        try {
            return (self::getDateObject($datetime2)->equals(self::getDateObject($datetime1)));
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     *
     * @param unknown_type $datetime1
     * @param unknown_type $datetime2
     */
    public static function isDateGreaterThan($datetime1, $datetime2)
    {
        try {
            return self::getDateObject($datetime2)->isLater(self::getDateObject($datetime1));
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     *
     * @param unknown_type $datetime1
     * @param unknown_type $datetime2
     */
    public static function isDateGreaterOrEqual($datetime1, $datetime2)
    {
        try {
            return (self::getDateObject($datetime2)->equals(self::getDateObject($datetime1)) || self::getDateObject($datetime2)->isLater(self:: getDateObject($datetime1)));
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     *
     * @param unknown_type $datetime1
     * @param unknown_type $datetime2
     */
    public static function isDateLowerThan($datetime1, $datetime2)
    {
        try {
            return self::getDateObject($datetime2)->isEarlier(self::getDateObject($datetime1));
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     *
     * @param unknown_type $datetime
     * @param unknown_type $datetimeRange1
     * @param unknown_type $datetimeRange2
     */
    public static function isDateInRange($datetime, $datetimeRange1, $datetimeRange2)
    {
        try {
            if (self::getDateObject($datetime)->equals(self::getDateObject($datetimeRange1), Zend_Date::DATES)
                || self::getDateObject($datetime)->equals(self::getDateObject($datetimeRange2), Zend_Date::DATES)
            ) {
                return true;
            } elseif (self::getDateObject($datetime)->isLater(self::getDateObject($datetimeRange1), Zend_Date::DATES)
                && self::getDateObject($datetime)->isEarlier(self::getDateObject($datetimeRange2), Zend_Date::DATES)
            ) {
                return true;
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    /**
     *
     * @param unknown_type $datetime
     * @param unknown_type $format
     */
    public static function getDate($datetime, $format = '')
    {
        return self::getFormatedDate($datetime, $format);
    }

    /**
     *
     * @param unknown_type $datetime
     */
    public static function getTime($datetime)
    {
        return self::getFormatedDate($datetime, Zend_Date::HOUR . "'h'" . Zend_Date::MINUTE);
    }

    /**
     *
     * Enter description here ...
     * @param string $datetime
     * @param int $nbrDays
     * @param Zend_Date $format
     *    Supported format tokens are:
     *    G - era, y - year, Y - ISO year, M - month, w - week of year, D - day of year, d - day of month
     *    E - day of week, e - number of weekday (1-7), h - hour 1-12, H - hour 0-23, m - minute, s - second
     *    A - milliseconds of day, z - timezone, Z - timezone offset, S - fractional second, a - period of day
     */
    public static function getDateAddDays($datetime, $nbrDays = 1, $format = '')
    {
        $date = self::getDateObject($datetime);
        $date->add($nbrDays, Zend_Date::DAY);
        if ($format == '') {
            return $date->toString();
        } else {
            return $date->toString($format);
        }
    }

    /**
     *
     * @param unknown_type $datetime
     */
    public static function getDateDashboard($datetime)
    {
        return self::getFormatedDate($datetime, Zend_Date::MONTH_NAME_SHORT . ' ' . Zend_Date::DAY . ', ' . Zend_Date::YEAR);
    }

    /**
     *
     * @param unknown_type $datetime
     */
    public static function getDateSideBar($datetime)
    {
        return self::getFormatedDate($datetime, Zend_Date::MONTH_NAME_SHORT . ' ' . Zend_Date::DAY . ', ' . Zend_Date::YEAR . " 'at' " . Zend_Date::HOUR . "'h'" . Zend_Date::MINUTE);
    }

    /**
     *
     * @param unknown_type $datetime
     */
    public static function getDateHour($datetime)
    {
        return self::getFormatedDate($datetime, Zend_Date::HOUR);
    }

    /**
     *
     * @param unknown_type $datetime
     */
    public static function getDateMinute($datetime)
    {
        return self::getFormatedDate($datetime, Zend_Date::MINUTE);
    }

    /**
     *
     * @param unknown_type $datetime
     */
    public static function getDateSeconds($datetime)
    {
        return self::getFormatedDate($datetime, Zend_Date::SECOND);
    }

    /**
     *
     * @param unknown_type $datetime
     */
    public static function getDateDay($datetime)
    {
        return self::getFormatedDate($datetime, Zend_Date::DAY);
    }

    /**
     *
     * @param unknown_type $datetime
     */
    public static function getDateMonth($datetime)
    {
        return self::getFormatedDate($datetime, Zend_Date::MONTH);
    }

    /**
     *
     * @param unknown_type $datetime
     */
    public static function getDateYear($datetime)
    {
        return self::getFormatedDate($datetime, Zend_Date::YEAR);
    }

    /**
     *
     */
    public static function getMySQLFormatedDate($withTime = true)
    {
        if ($withTime) {
            return $datetime = date('Y-m-d H:i:s');
        } else {
            return $datetime = date('Y-m-d');
        }
    }

    /**
     *
     * @param unknown_type $datetime
     */
    public static function getDateForFileNaming($datetime = null)
    {
        //if ( $datetime == null ) $datetime = new DateTime();
        //return self::getFormatedDate($datetime, 'yyyyMMdd-HHmmss');
        return $datetime = date('Ymd-His');
    }

    /**
     *
     * @param unknown_type $datetime
     */
    public static function EUtoUSDateFormat($datetime)
    {
        return self::getFormatedDate($datetime, 'yyyy-MM-dd HH:mm');
    }

    /**
     *
     * @param unknown_type $datetime
     */
    public static function UStoEUDateFormat($datetime)
    {
        if (empty($datetime)) {
            return null;
        }

        return self::getFormatedDate($datetime, 'dd/MM/yyyy HH:mm');
    }

    /**
     * Fonction qui permet de faire des addition de temps
     * exemple addTime("24:00:14","03:00:00") retournera "27:00:14"
     *
     * @param unknown_type $time1
     * @param unknown_type $time2
     */
    public static function addTime($time1, $time2)
    {
        list($hr1, $min1, $sec1) = explode(":", $time1);
        $UTime1 = mktime(1, $min1, $sec1, 01, 01, 1970);
        list($hr2, $min2, $sec2) = explode(":", $time2);
        $UTime2 = mktime(1, $min2, $sec2, 01, 01, 1970);
        $UTimeTotal = $UTime1 + $UTime2;
        $UTimeTotal = $UTimeTotal - 3600;
        $timeTotal = date("H:i:s", $UTimeTotal);
        list($hr3, $min3, $sec3) = explode(":", $timeTotal);
        $hrTotal = $hr1 + $hr2;
        if ($hr3 >= 1) {
            $hrTotal = $hrTotal + $hr3;
        }
        $timeTotal = $hrTotal . ":" . $min3 . ":" . $sec3;

        return $timeTotal;
    }

    /**
     *
     * @param unknown_type $datetime
     */
    public static function getSmallTimeFormat($datetime)
    {
        return self::getFormatedDate($datetime, 'HH:mm');
    }

    /**
     *
     * @param unknown_type $datetime
     */
    public static function getSmallDateFormat($datetime)
    {
        return self::getFormatedDate($datetime, 'dd/MM');
    }

    /**
     * returns a formated date from a MySQL date time
     * @param unknown_type $dd
     * @param unknown_type $format
     */
    public static function formatGetDateOnly($dd, $format = 'EU')
    {
        $d = preg_split('/ /', $dd);
        $e = preg_split('/-/', $d[0]);
        if ($format == 'EU') {
            return $e[2] . '/' . $e[1] . '/' . $e[0];
        } else {
            return $e[1] . '/' . $e[2] . '/' . $e[0];
        }
    }

}

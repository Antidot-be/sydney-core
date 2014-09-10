<?php

class Sydney_Auth_Bypass
{

    private static $prefix = "IjSml54KlsqO";
    private static $keywords = array(
        'login',
        'id',
        'lastlogindate',
        'password',
        'lname',
        'safinstances_id'
    );

    public function get($usersId)
    {
        $user = new Users();

        if ($rowset = $user->find($usersId)) {
            $row = $rowset->current();
            $string = '';
            foreach (self::$keywords as $field) {
                $string .= $row->$field;
            }

            return self::$prefix . sha1($string);
        }

        return false;
    }

    public function isValid($passid, $pass)
    {
        $user = new Users();
        if ($rowset = $user->find($passid)) {
            $row = $rowset->current();
            foreach (self::$keywords as $field) {
                $string .= $row->$field;
            }

            if (self::$prefix . sha1($string) === $pass) {
                return $row;
            }
        }

        return false;
    }

    /**
     * Mobile Validation Key
     */
    static public function isValidMobileKey($_time, $_key)
    {
        $_key1 = 'Tu6ehUya';
        $_key2 = 'c5CHuxUq';
        $_key3 = 'nuDrEk2c';
        $_id = substr($_key, 0, strpos($_key, '/'));

        $user = new Users();
        if ($rowset = $user->find($_id)) {
            $row = $rowset->current();
            $_keyBuilded =
                $_key1
                . Sydney_Tools_Datetime::getDateYear($_time)
                . Sydney_Tools_Datetime::getDateSeconds($_time)
                . $row->password
                . Sydney_Tools_Datetime::getDateDay($_time)
                . Sydney_Tools_Datetime::getDateMinute($_time)
                . $_key2
                . Sydney_Tools_Datetime::getDateMonth($_time)
                . $row->login
                . $_key3;
            $row = $rowset->current();
            if ($_id . '/' . sha1($_keyBuilded) === $_key) {
                return $row;
            }
        }

        return false;
    }

}

?>

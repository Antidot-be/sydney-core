<?php

class Sydney_Tools_Security extends Sydney_Tools
{

    /**
     *
     * @param unknown_type $length
     * @param unknown_type $useLower
     * @param unknown_type $useUpper
     * @param unknown_type $useNumber
     * @param unknown_type $useCustom
     */
    public static function generatePassword($length = 8, $useLower = true, $useUpper = true, $useNumber = true, $useCustom = '!#%-_()')
    {
        $upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $lower = "abcdefghijklmnopqrstuvwxyz";
        $number = "0123456789";

        $seedLength = 0;
        $seed = '';
        $password = '';

        if ($useUpper === true) {
            $seedLength += 26;
            $seed .= $upper;
        }
        if ($useLower === true) {
            $seedLength += 26;
            $seed .= $lower;
        }
        if ($useNumber === true) {
            $seedLength += 10;
            $seed .= $number;
        }
        if (!empty($useCustom)) {
            $seedLength += strlen($useCustom);
            $seed .= $useCustom;
        }
        for ($i = 1; $i <= $length; $i++) {
            $password .= $seed{rand(0, $seedLength - 1)};
        }

        return $password;
    }

}

<?php

class Sydney_Http_Cookie
{

    private static $prefixCredential = "IjSml54KlsqO";
    private static $prefixIdentity = "hLmlkZ6szpPsqF5dsIH";

    private static function getPrefix()
    {
        return self::$prefix;
    }

    private static function getPrefixCookieName()
    {
        try {
            $ua = new Zend_Http_UserAgent();

            return $ua->getDevice()->getType() . $ua->getDevice()->getBrowser();
        } catch (Exception $e) {
            return 'unknowdevice';
        }

    }

    private static function getIdentityCookieName()
    {
        return self::getPrefixCookieName() . sha1(Sydney_Tools::getSafinstancesId() . 'sydney_identity');
    }

    private static function getCredentialCookieName()
    {
        return self::getPrefixCookieName() . sha1(Sydney_Tools::getSafinstancesId() . 'sydney_credential');
    }

    private static function decodeValue($value, $prefix)
    {
        $value = str_replace($prefix, '', $value);
        $value = base64_decode($value);

        return $value;
    }

    private static function encodeValue($value, $prefix)
    {
        return $prefix . base64_encode($value);
    }

    public static function getIdentity(Zend_Controller_Request_Http $request)
    {
        return self::decodeValue($request->getCookie(self::getIdentityCookieName()), self::$prefixIdentity);
    }

    public static function getCredential(Zend_Controller_Request_Http $request)
    {
        return self::decodeValue($request->getCookie(self::getCredentialCookieName()), self::$prefixCredential);
    }

    public static function setAuthCookie($identity, $credential, $nbrDaysValidityTime = 7)
    {
        setcookie(self::getIdentityCookieName(), // name
            self::encodeValue($identity, self::$prefixIdentity), // value
            time() + (60 * 60 * 24 * $nbrDaysValidityTime), // expire (7 days in case if 60*60*24*7)
            '/', // path
            null, // domain
            false, // secure
            true // httponly
        );
        setcookie(self::getCredentialCookieName(), // name
            self::encodeValue($credential, self::$prefixCredential), // value
            time() + (60 * 60 * 24 * $nbrDaysValidityTime), // expire (7 days in case if 60*60*24*7)
            '/', // path
            null, // domain
            false, // secure
            true // httponly
        );

    }

    public static function cleanAuthCookie()
    {
        self::setAuthCookie(false, false);
    }

}

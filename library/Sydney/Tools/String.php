<?php

/**
 * Utilities for string manipulation
 *
 */
class Sydney_Tools_String extends Sydney_Tools
{

    /**
     *
     * @param unknown_type $string
     */
    public static function stripAccents($string)
    {
        $a = array(
            'À',
            'Á',
            'Â',
            'Ã',
            'Ä',
            'Å',
            'Æ',
            'Ç',
            'È',
            'É',
            'Ê',
            'Ë',
            'Ì',
            'Í',
            'Î',
            'Ï',
            'Ð',
            'Ñ',
            'Ò',
            'Ó',
            'Ô',
            'Õ',
            'Ö',
            'Ø',
            'Ù',
            'Ú',
            'Û',
            'Ü',
            'Ý',
            'ß',
            'à',
            'á',
            'â',
            'ã',
            'ä',
            'å',
            'æ',
            'ç',
            'è',
            'é',
            'ê',
            'ë',
            'ì',
            'í',
            'î',
            'ï',
            'ñ',
            'ò',
            'ó',
            'ô',
            'õ',
            'ö',
            'ø',
            'ù',
            'ú',
            'û',
            'ü',
            'ý',
            'ÿ',
            'Ā',
            'ā',
            'Ă',
            'ă',
            'Ą',
            'ą',
            'Ć',
            'ć',
            'Ĉ',
            'ĉ',
            'Ċ',
            'ċ',
            'Č',
            'č',
            'Ď',
            'ď',
            'Đ',
            'đ',
            'Ē',
            'ē',
            'Ĕ',
            'ĕ',
            'Ė',
            'ė',
            'Ę',
            'ę',
            'Ě',
            'ě',
            'Ĝ',
            'ĝ',
            'Ğ',
            'ğ',
            'Ġ',
            'ġ',
            'Ģ',
            'ģ',
            'Ĥ',
            'ĥ',
            'Ħ',
            'ħ',
            'Ĩ',
            'ĩ',
            'Ī',
            'ī',
            'Ĭ',
            'ĭ',
            'Į',
            'į',
            'İ',
            'ı',
            'Ĳ',
            'ĳ',
            'Ĵ',
            'ĵ',
            'Ķ',
            'ķ',
            'Ĺ',
            'ĺ',
            'Ļ',
            'ļ',
            'Ľ',
            'ľ',
            'Ŀ',
            'ŀ',
            'Ł',
            'ł',
            'Ń',
            'ń',
            'Ņ',
            'ņ',
            'Ň',
            'ň',
            'ŉ',
            'Ō',
            'ō',
            'Ŏ',
            'ŏ',
            'Ő',
            'ő',
            'Œ',
            'œ',
            'Ŕ',
            'ŕ',
            'Ŗ',
            'ŗ',
            'Ř',
            'ř',
            'Ś',
            'ś',
            'Ŝ',
            'ŝ',
            'Ş',
            'ş',
            'Š',
            'š',
            'Ţ',
            'ţ',
            'Ť',
            'ť',
            'Ŧ',
            'ŧ',
            'Ũ',
            'ũ',
            'Ū',
            'ū',
            'Ŭ',
            'ŭ',
            'Ů',
            'ů',
            'Ű',
            'ű',
            'Ų',
            'ų',
            'Ŵ',
            'ŵ',
            'Ŷ',
            'ŷ',
            'Ÿ',
            'Ź',
            'ź',
            'Ż',
            'ż',
            'Ž',
            'ž',
            'ſ',
            'ƒ',
            'Ơ',
            'ơ',
            'Ư',
            'ư',
            'Ǎ',
            'ǎ',
            'Ǐ',
            'ǐ',
            'Ǒ',
            'ǒ',
            'Ǔ',
            'ǔ',
            'Ǖ',
            'ǖ',
            'Ǘ',
            'ǘ',
            'Ǚ',
            'ǚ',
            'Ǜ',
            'ǜ',
            'Ǻ',
            'ǻ',
            'Ǽ',
            'ǽ',
            'Ǿ',
            'ǿ'
        );

        $b = array(
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'AE',
            'C',
            'E',
            'E',
            'E',
            'E',
            'I',
            'I',
            'I',
            'I',
            'D',
            'N',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'U',
            'U',
            'U',
            'U',
            'Y',
            's',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'ae',
            'c',
            'e',
            'e',
            'e',
            'e',
            'i',
            'i',
            'i',
            'i',
            'n',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'u',
            'u',
            'u',
            'u',
            'y',
            'y',
            'A',
            'a',
            'A',
            'a',
            'A',
            'a',
            'C',
            'c',
            'C',
            'c',
            'C',
            'c',
            'C',
            'c',
            'D',
            'd',
            'D',
            'd',
            'E',
            'e',
            'E',
            'e',
            'E',
            'e',
            'E',
            'e',
            'E',
            'e',
            'G',
            'g',
            'G',
            'g',
            'G',
            'g',
            'G',
            'g',
            'H',
            'h',
            'H',
            'h',
            'I',
            'i',
            'I',
            'i',
            'I',
            'i',
            'I',
            'i',
            'I',
            'i',
            'IJ',
            'ij',
            'J',
            'j',
            'K',
            'k',
            'L',
            'l',
            'L',
            'l',
            'L',
            'l',
            'L',
            'l',
            'l',
            'l',
            'N',
            'n',
            'N',
            'n',
            'N',
            'n',
            'n',
            'O',
            'o',
            'O',
            'o',
            'O',
            'o',
            'OE',
            'oe',
            'R',
            'r',
            'R',
            'r',
            'R',
            'r',
            'S',
            's',
            'S',
            's',
            'S',
            's',
            'S',
            's',
            'T',
            't',
            'T',
            't',
            'T',
            't',
            'U',
            'u',
            'U',
            'u',
            'U',
            'u',
            'U',
            'u',
            'U',
            'u',
            'U',
            'u',
            'W',
            'w',
            'Y',
            'y',
            'Y',
            'Z',
            'z',
            'Z',
            'z',
            'Z',
            'z',
            's',
            'f',
            'O',
            'o',
            'U',
            'u',
            'A',
            'a',
            'I',
            'i',
            'O',
            'o',
            'U',
            'u',
            'U',
            'u',
            'U',
            'u',
            'U',
            'u',
            'U',
            'u',
            'A',
            'a',
            'AE',
            'ae',
            'O',
            'o'
        );

        return str_replace($a, $b, $string);
    }

    /**
     *
     * @param unknown_type $content
     * @param unknown_type $aroundWord
     * @param unknown_type $maxlength
     */
    public static function getExtractShortDesc($content, $aroundWord = '', $maxlength = 300)
    {
        if (strlen($content) <= $maxlength) {
            return $content;
        } else {

            // list of separator
            $separator = array(
                ' ',
                ' + ',
                ' - ',
                '~',
                '^',
                ' and ',
                ' or ',
                ' not ',
                '*',
                '"'
            );
            $aroundWord = str_ireplace($separator, ' ', $aroundWord);

            $startExtractPos = 0;
            $content = strip_tags($content);
            $content = trim(str_replace(array(
                chr(13),
                chr(10),
                chr(9)
            ), '', $content));
            if (!empty($aroundWord)) {
                $posOfWord = stripos($content, trim($aroundWord));
                if ($posOfWord !== false) {
                    $offset = $posOfWord > 50 ? $posOfWord - 50 : 0;
                    $startExtractPos = stripos($content, ' ', $offset);
                    if ($startExtractPos !== false && $startExtractPos > 0) {
                        $content = '...' . substr($content, $startExtractPos);
                    }
                }
                if (strlen($content) <= $maxlength) {
                    return $content;
                }
            }

            return substr($content, 0, strrpos(substr($content, 0, $maxlength), ' ')) . '...';
        }
    }

    /**
     *
     * @param unknown_type $content
     * @param unknown_type $word
     */
    public static function highlightWord($content, $word)
    {
        // list of separator
        $separator = array(
            ' ',
            ' + ',
            ' - ',
            '~',
            '^',
            ' and ',
            ' or ',
            ' not ',
            '*',
            '"'
        );
        // explode content
        $mycontent = explode(" ", $content);
        // group 1
        $group1 = explode(' ', $word);
        // group 2
        $group2 = str_ireplace($separator, ' ', $word);
        $group2 = explode(' ', $group2);
        // parse content
        foreach ($mycontent as $key => &$currentWord) {

            foreach ($group1 as $wordpart) {
                if ($wordpart == $currentWord || $wordpart == Sydney_Tools::stripAccents($currentWord)) {
                    $currentWord = '<strong>' . $currentWord . '</strong>';
                    continue 2;
                } else {
                    $currentWord = str_ireplace($wordpart, '<strong>' . $wordpart . '</strong>', $currentWord);
                }
            }

            foreach ($group2 as $wordpart) {
                if ($wordpart == $currentWord || $wordpart == Sydney_Tools::stripAccents($currentWord)) {
                    $currentWord = '<strong>' . $currentWord . '</strong>';
                    continue 2;
                } else {
                    $currentWord = str_ireplace($wordpart, '<strong>' . $wordpart . '</strong>', $currentWord);
                }
            }
        }
        // create string
        $content = implode(' ', $mycontent);

        return $content;
    }

    /**
     *
     * @param unknown_type $text
     * @param unknown_type $maxLength
     * @param unknown_type $suffix
     */
    public static function truncate($text, $maxLength = 36, $suffix = '...')
    {
        if (strlen($text) > $maxLength) {
            $text = substr($text, 0, $maxLength);
            $spacePos = strrpos($text, ' ');
            if ($spacePos) {
                $text = substr($text, 0, $spacePos);
            }
            $text = $text . $suffix;
        }

        return $text;
    }
}

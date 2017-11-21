<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Utils Controller
 * @author dev@maarch.org
 * @ingroup core
 */

namespace Core\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;


class UtilsController
{
    public static function wd_remove_accents(array $aArgs = [])
    {
        if(empty($aArgs['charset'])){
            $aArgs['charset'] = 'utf-8';
        }

        $str = htmlentities($aArgs['string'], ENT_NOQUOTES, $aArgs['charset']);

        $str = preg_replace(
            '#\&([A-za-z])(?:uml|circ|tilde|acute|grave|cedil|ring)\;#',
            '\1',
            $str
        );
        $str = preg_replace(
            '#\&([A-za-z]{2})(?:lig)\;#',
            '\1',
            $str
        );
        $str = preg_replace(
            '#\&[^;]+\;#',
            '',
            $str
        );

        return $str;
    }

    /**
    * Cleans html string, replacing entities by utf-8 code
    *
    * @param  $var string  String to clean
    * @return Cleaned string
    */
    public function wash_html($var, $mode="UNICODE")
    {
        if($mode == "UNICODE")
        {
            $var = str_replace("<br/>","\\n",$var);
            $var = str_replace("<br />","\\n",$var);
            $var = str_replace("<br/>","\\n",$var);
            $var = str_replace("&nbsp;"," ",$var);
            $var = str_replace("&eacute;", "\u00e9",$var);
            $var = str_replace("&egrave;","\u00e8",$var);
            $var = str_replace("&ecirc;","\00ea",$var);
            $var = str_replace("&agrave;","\u00e0",$var);
            $var = str_replace("&acirc;","\u00e2",$var);
            $var = str_replace("&icirc;","\u00ee",$var);
            $var = str_replace("&ocirc;","\u00f4",$var);
            $var = str_replace("&ucirc;","\u00fb",$var);
            $var = str_replace("&acute;","\u0027",$var);
            $var = str_replace("&deg;","\u00b0",$var);
            $var = str_replace("&rsquo;", "\u2019",$var);
        }
        else if($mode == 'NO_ACCENT')
        {
            $var = str_replace("<br/>","\\n",$var);
            $var = str_replace("<br />","\\n",$var);
            $var = str_replace("<br/>","\\n",$var);
            $var = str_replace("&nbsp;"," ",$var);
            $var = str_replace("&eacute;", "e",$var);
            $var = str_replace("&egrave;","e",$var);
            $var = str_replace("&ecirc;","e",$var);
            $var = str_replace("&agrave;","a",$var);
            $var = str_replace("&acirc;","a",$var);
            $var = str_replace("&icirc;","i",$var);
            $var = str_replace("&ocirc;","o",$var);
            $var = str_replace("&ucirc;","u",$var);
            $var = str_replace("&acute;","",$var);
            $var = str_replace("&deg;","o",$var);
            $var = str_replace("&rsquo;", "'",$var);

            // AT LAST
            $var = str_replace("&", " et ",$var);
        }
        else
        {
            $var = str_replace("<br/>","\\n",$var);
            $var = str_replace("<br />","\\n",$var);
            $var = str_replace("<br/>","\\n",$var);
            $var = str_replace("&nbsp;"," ",$var);
            $var = str_replace("&eacute;", "é",$var);
            $var = str_replace("&egrave;","è",$var);
            $var = str_replace("&ecirc;","ê",$var);
            $var = str_replace("&agrave;","à",$var);
            $var = str_replace("&acirc;","â",$var);
            $var = str_replace("&icirc;","î",$var);
            $var = str_replace("&ocirc;","ô",$var);
            $var = str_replace("&ucirc;","û",$var);
            $var = str_replace("&acute;","",$var);
            $var = str_replace("&deg;","°",$var);
            $var = str_replace("&rsquo;", "'",$var);
        }
        return $var;
    }
}

<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Text Format Model Abstract
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Models;

class TextFormatModelAbstract
{
    public static function normalize(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['string']);
        ValidatorModel::stringType($aArgs, ['string']);

        $a = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
        $b = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr';

        $string = utf8_decode($aArgs['string']);
        $string = strtr($string, utf8_decode($a), $b);
        $string = strtolower($string);

        return utf8_encode($string);
    }
}

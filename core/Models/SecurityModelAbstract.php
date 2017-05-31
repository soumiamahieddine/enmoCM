<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Security Model Abstract
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Models;

class SecurityModelAbstract
{
    public static function getPasswordHash($password)
    {
        return hash('sha512', $password);
    }
}

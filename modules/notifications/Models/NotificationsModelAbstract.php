<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Notifications Model
* @author dev@maarch.org
* @ingroup core
*/

namespace Notifications\Models;

use Core\Models\DatabaseModel;

class NotificationsModelAbstract
{
    public static function getEnableNotifications()
    {
        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['notifications'],
            'where'     => ['is_enabled = ?'],
            'data'      => ['Y']
        ]);

        return $aReturn;
    }
}

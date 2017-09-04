<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Notifications Events Model
* @author dev@maarch.org
* @ingroup core
*/

namespace Notifications\Models;

use Core\Models\DatabaseModel;
use Core\Models\ValidatorModel;

class NotificationsEventsModelAbstract
{
    public static function create(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['notification_sid', 'table_name', 'record_id', 'user_id', 'event_info']);
        ValidatorModel::stringType($aArgs, ['table_name', 'record_id', 'user_id', 'event_info']);
        ValidatorModel::intval($aArgs, ['notification_sid']);

        $db = new \Database();
        $aArgs['event_date'] = $db->current_datetime();

        $aReturn = DatabaseModel::insert([
            'table'         => 'notif_event_stack',
            'columnsValues' => $aArgs
            ]);

        return $aReturn;
    }
}

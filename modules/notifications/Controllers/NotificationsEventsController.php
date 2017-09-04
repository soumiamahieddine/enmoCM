<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Notifications events Controller
* @author dev@maarch.org
* @ingroup core
*/

namespace Notifications\Controllers;

use Notifications\Models\NotificationsEventsModel;
use Notifications\Models\NotificationsModel;

class NotificationsEventsController
{
    public static function fill_event_stack($event_id, $table_name, $record_id, $user, $info)
    {
        if ($record_id == '') {
            return;
        }
        
        $aNotifications = NotificationsModel::getEnableNotifications();
        if (empty($aNotifications)) {
            return;
        }

        foreach ($aNotifications as $notification) {
            $event_ids = explode(',', $notification['event_id']);
            if ($event_id == $notification['event_id'] || self::wildcard_match($notification['event_id'], $event_id) || in_array($event_id, $event_ids)) {
                NotificationsEventsModel::create([
                    'notification_sid' => $notification['notification_sid'],
                    'table_name'       => $table_name,
                    'record_id'        => $record_id,
                    'user_id'          => $user,
                    'event_info'       => $info
                ]);
            }
        }
    }

    public function wildcard_match($pattern, $str)
    {
        $pattern = '/^' . str_replace(array('%', '\*', '\?', '\[', '\]'), array('.*', '.*', '.', '[', ']+'), preg_quote($pattern)) . '$/is';
        $result = preg_match($pattern, $str);
        return $result;
    }
}

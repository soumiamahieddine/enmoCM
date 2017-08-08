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
* @ingroup Module
*/

namespace Notifications\Models;

use Core\Models\DatabaseModel;
use Core\Models\ValidatorModel;

class NotificationModelAbstract 
{
    public static function get(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['select']);        
        $aNotifications = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['notifications']
        ]);

        return $aNotifications;
    }

    public static function getById(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['notificationId']);
        $aNotification = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['notifications'],
            'where'     => ['notification_id = ?'],
            'data'      => [$aArgs['notificationId']]
        ]);
        if (empty($aNotification[0])) {
            return [];
        }

        return $aNotification[0];
    }

    public static function delete(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['notification_sid']);
        ValidatorModel::intVal($aArgs, ['notification_sid']);
        DatabaseModel::delete([
            'table'     => 'notifications',
            'where'     => ['notification_sid = ?'],
            'data'      => [$aArgs['notification_sid']],
        ]);

        return true;
    }

    public static function create(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['notification_id', 'description', 'is_enabled', 'event_id', 'notification_mode', 'template_id', 'diffusion_type', 'diffusion_properties']);
        ValidatorModel::intVal($aArgs, ['template_id']);
        ValidatorModel::stringType($aArgs, ['notification_id','description','is_enabled','event_id','notification_mode',]);
               
        DatabaseModel::insert([
            'table'         => 'notifications',
            'columnsValues' => [
                'notification_id'   => $aArgs['notification_id'],
                'description'     => $aArgs['description'],
                'is_enabled' => $aArgs['is_enabled'],
                'event_id' => $aArgs['event_id'],
                'notification_mode' => $aArgs['notification_mode'],
                'template_id' => $aArgs['template_id'],
                'rss_url_template' => $aArgs['rss_url_template'],
                'diffusion_type' => $aArgs['diffusion_type'],
                'diffusion_properties' => $aArgs['diffusion_properties'],
                'attachfor_type' => $aArgs['attachfor_type'],
                'attachfor_properties' => $aArgs['attachfor_properties']
            ]
        ]);

        return true;

    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['notification_sid']);
        ValidatorModel::intVal($aArgs, ['notification_sid']);

        $where['notification_sid'] = $aArgs['notification_sid'];
        //unset($aArgs['notification_id']);
        unset($aArgs['notification_sid']);

        $aReturn = DatabaseModel::update([
            'table' => 'notifications',
            'set'   => $aArgs,
            'where' => ['notification_sid = ?'],
            'data'  => [$where['notification_sid']]
        ]);

        return $aReturn;
    }

}

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
use Status\models\StatusModel;
use Entities\Models\EntityModelAbstract;
use Core\Models\GroupModelAbstract;

class NotificationModelAbstract 
{
    public static function get(array $aArgs = [])
    {
        $aNotifications = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['notifications']
        ]);

        return $aNotifications;
    }

    public static function getById(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['notification_sid']);

        $aNotification = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['notifications'],
            'where'     => ['notification_sid = ?'],
            'data'      => [$aArgs['notification_sid']]
        ]);

        if (empty($aNotification[0])) {
            return [];
        }

        return $aNotification[0];
    }

    public static function getByNotificationId(array $aArgs = [])
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
                'notification_id'       => $aArgs['notification_id'],
                'description'           => $aArgs['description'],
                'is_enabled'            => $aArgs['is_enabled'],
                'event_id'              => $aArgs['event_id'],
                'notification_mode'     => $aArgs['notification_mode'],
                'template_id'           => $aArgs['template_id'],
                'rss_url_template'      => $aArgs['rss_url_template'],
                'diffusion_type'        => $aArgs['diffusion_type'],
                'diffusion_properties'  => $aArgs['diffusion_properties'],
                'attachfor_type'        => $aArgs['attachfor_type'],
                'attachfor_properties'  => $aArgs['attachfor_properties']
            ]
        ]);

        return true;
    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['notification_sid']);
        ValidatorModel::intVal($aArgs, ['notification_sid']);

        $notification_sid = $aArgs['notification_sid'];
        unset($aArgs['data']);
        unset($aArgs['notification_sid']);

        $aReturn = DatabaseModel::update([
            'table' => 'notifications',
            'set'   => $aArgs,
            'where' => ['notification_sid = ?'],
            'data'  => [$notification_sid]
        ]);

        return $aReturn;
    }

    public static function getEvent()
    {
        $tabEvent_Type = DatabaseModel::select([
            'select'    => ['id, label_action'],
            'table'     => ['actions']
        ]);

        //get event system
        // $customId = CoreConfigModel::getCustomId();

        // if (file_exists('custom/' .$customId. 'modules/notifications/xml/event_type.xml')) {
        //     $path = 'custom/' .$customId. 'modules/notifications/xml/event_type.xml';
        // } else {
            $path = 'modules/notifications/xml/event_type.xml';
        // }
        $xmlfile = simplexml_load_file($path);
        if ($xmlfile) {
            foreach ($xmlfile->event_type as $eventType) {

                $tabEvent_Type[] = array(
                    'id'          => (string) $eventType->id,
                    'label_action'       => (string) $eventType->label,
                );
            }
        }

        return $tabEvent_Type;
    }

    public static function getTemplate()
    {
        $tabTemplate = DatabaseModel::select([
            'select'    => ['template_id, template_label'],
            'table'     => ['templates'],
            'where'     => ['template_target = ?'],
            'data'      => ['notifications']
        ]);
        
        return $tabTemplate;
    }

    public static function getDiffusionType()
    {
        // // $customId = CoreConfigModel::getCustomId();

        // // if (file_exists('custom/' .$customId. 'modules/notifications/xml/event_type.xml')) {
        // //     $path = 'custom/' .$customId. 'modules/notifications/xml/event_type.xml';
        // // } else {
        //     $path = 'modules/notifications/xml/diffusion_type.xml';
        // // }
        // $xmlfile = simplexml_load_file($path);
        // if ($xmlfile) {
        //     foreach ($xmlfile->diffusion_type as $diffusionType) {
        //         $result = [];
        //         if((string)$diffusionType->select){
        //             if((string)$diffusionType->where){
        //               $result = DatabaseModel::select([
        //                 'select'    => [(string)$diffusionType->select],
        //                 'table'     => [(string)$diffusionType->from],
        //                 'where'     => [(string)$diffusionType->where],
        //                 'data'      => [(string)$diffusionType->data]
        //                 ]);  
        //             }else{
        //                 $result = DatabaseModel::select([
        //                 'select'    => [(string)$diffusionType->select],
        //                 'table'     => [(string)$diffusionType->from],
        //                 ]);
        //             }
        //         }

        //         $tabDiffusion_Type[] = array(
        //             'id'          => (string) $diffusionType->id,
        //             'label'       => constant((string)$diffusionType->label),
        //             'add_attachment'       => (string)$diffusionType->add_attachment,
        //             'script'        => (string)$diffusionType->script,
        //             'request'        => $result,
        //         );

                


        //     }
        // }
        // $result = DatabaseModel::select([
        //     'select'    => ['group_id as id, group_desc as label'],
        //     'table'     => ['usergroups'],
        //     'where'     => ['enabled = ?'],
        //     'data'  => ['Y']
        // ]);
        $tabDiffusion_Type[] = array(
            'id'          => 'group',
            'label'          => constant((string)'_GROUP'),
            'add_attachment' => 'true',
            //'request'       => $result,
        );
        // $result = DatabaseModel::select([
        //     'select'    => ['entity_id as id, entity_label as label'],
        //     'table'     => ['entities'],
        //     'where'     => ['enabled = ?'],
        //     'data'  => ['Y']
        // ]);
        $tabDiffusion_Type[] = array(
            'id'          => 'entity',
            'label'          => constant((string)'_ENTITY'),
            'add_attachment' => 'true',
            //'request'       => $result
        );
        // $result = DatabaseModel::select([
        //     'select'    => ['id, label_status as label'],
        //     'table'     => ['status']
        // ]);
        $tabDiffusion_Type[] = array(
            'id'          => 'dest_entity',
            'label'          => constant((string)'_DEST_ENTITY'),
            'add_attachment' => 'false',
            //'request'       => $result
        );
        $tabDiffusion_Type[] = array(
            'id'          => 'dest_user',
            'label'          => constant((string)'_DEST_USER'),
            'add_attachment' => 'false',
            //'request'       => $result
        );
        $tabDiffusion_Type[] = array(
            'id'          => 'dest_user_visa',
            'label'          => constant((string)'_DEST_USER_VISA'),
            'add_attachment' => 'true',
            //'request'       => $result
        );
        $tabDiffusion_Type[] = array(
            'id'          => 'dest_user_sign',
            'label'          => constant((string)'_DEST_USER_SIGN'),
            'add_attachment' => 'true',
            //'request'       => $result
        );
        // $result = DatabaseModel::select([
        //     'select'    => ["user_id as id, concat(firstname,' ',lastname) as label"],
        //     'table'     => ['users']
        // ]);
        $tabDiffusion_Type[] = array(
            'id'          => 'user',
            'label'          => constant((string)'_USER'),
            'add_attachment' => 'true',
            //'request'       => $result
        );
        $result = [];
        $tabDiffusion_Type[] = array(
            'id'          => 'note_dest_user',
            'label'          => constant((string)'_NOTE_DEST_USER'),
            'add_attachment' => 'false',
            //'request'       => $result
        );
        $tabDiffusion_Type[] = array(
            'id'          => 'note_copy_list',
            'label'          => constant((string)'_NOTE_COPY_LIST'),
            'add_attachment' => 'false',
            //'request'       => $result
        );
        $tabDiffusion_Type[] = array(
            'id'          => 'contact',
            'label'          => constant((string)'_CONTACT'),
            'add_attachment' => 'true',
            //'request'       => $result
        );


        return $tabDiffusion_Type;

    }

    public static function getDiffusionTypeGroups()
    {
        $groups = GroupModelAbstract::get();
        return $groups;
    }

    public static function getDiffusionTypesUsers()
    {
        $users = DatabaseModel::select([
            'select'    => ["user_id as id, concat(firstname,' ',lastname) as label"],
            'table'     => ['users']
        ]);
        return $users;
    }

    public static function getDiffusionTypeEntities()
    {
        $entities = EntityModelAbstract::get();
        return $entities;
    }

    public static function getDiffusionTypeStatus()
    {
        $status = StatusModel::getList();
        return $status;
    }

}

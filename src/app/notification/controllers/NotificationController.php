<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Notifications Controller
* @author dev@maarch.org
* @ingroup notifications
*/

namespace Notification\controllers;

use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Notification\models\NotificationModel;
use Core\Models\ServiceModel;
use Slim\Http\Request;
use Slim\Http\Response;

class NotificationController
{
    public function get(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_notif', 'userId' => $GLOBALS['userId'], 'location' => 'notifications', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        return $response->withJson(['notifications' => NotificationModel::get()]);
    }

    public function getBySid(Request $request, Response $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_notif', 'userId' => $GLOBALS['userId'], 'location' => 'notifications', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $notification = NotificationModel::getById(['notification_sid' => $aArgs['id'], 'select' => ['notification_sid', 'notification_id', 'description', 'is_enabled', 'event_id', 'notification_mode', 'template_id', 'diffusion_type','diffusion_properties', 'attachfor_type','attachfor_properties']]);

        $notification['diffusion_properties'] = explode(",", $notification['diffusion_properties']);
        
        foreach ($notification['diffusion_properties'] as $key => $value) {
            $notification['diffusion_properties'][$value] = $value;
            unset($notification['diffusion_properties'][$key]);
        }

        $notification['attachfor_properties'] = explode(",", $notification['attachfor_properties']);
        
        foreach ($notification['attachfor_properties'] as $key => $value) {
            $notification['attachfor_properties'][$value] = $value;
            unset($notification['attachfor_properties'][$key]);
        }
        
        if (empty($notification)) {
            return $response->withStatus(400)->withJson(['errors' => 'Notification not found']);
        }
        $data = [];

        $data['event']         = NotificationModel::getEvent();
        $data['template']      = NotificationModel::getTemplate();
        $data['diffusionType'] = NotificationModel::getDiffusionType();
        $data['groups']        = NotificationModel::getDiffusionTypeGroups();
        $data['users']         = NotificationModel::getDiffusionTypesUsers();
        $data['entities']      = NotificationModel::getDiffusionTypeEntities();
        $data['status']        = NotificationModel::getDiffusionTypeStatus();

        $notification['data'] = $data;

        return $response->withJson(['notification'=>$notification]);
    }

    public function create(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_notif', 'userId' => $GLOBALS['userId'], 'location' => 'notifications', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();
        if (empty($data['notification_id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Notification error : notification_id is empty']);
        }
        $notificationInDb = NotificationModel::getByNotificationId(['notificationId' => $data['notification_id'], 'select' => ['notification_sid']]);
        
        if (Validator::notEmpty()->validate($notificationInDb)) {
            return $response->withStatus(400)->withJson(['errors' => _NOTIFICATIONS_ERROR.' '._NOTIF_ALREADY_EXIST]);

        } elseif (!Validator::length(0, 254)->validate($data['description'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Description is too long']);

        } elseif (!Validator::length(0, 254)->validate($data['event_id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'event_id is too long']);

        } elseif (!Validator::length(0, 30)->validate($data['notification_mode'])) {
            return $response->withStatus(400)->withJson(['errors' => 'notification_mode is too long']);

        } elseif (Validator::intType()->notEmpty()->validate($data['template_id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'wrong format for template_id']);
        }

        if ($data['is_enabled'] == true) {
            $data['is_enabled'] = 'Y';
        } else {
            $data['is_enabled'] = 'N';
        }

        $data['notification_mode'] = 'EMAIL';
        
        if ($data['diffusion_properties']) {
            $data['diffusion_properties'] = implode(",", $data['diffusion_properties']);
        }
        
        if ($data['attachfor_properties']) {
            $data['attachfor_properties'] = implode(",", $data['attachfor_properties']);
        } else {
            $data['attachfor_properties'] = '';
        }

        if (NotificationModel::create($data)) {
            HistoryController::add([
                'tableName' => 'notifications',
                'recordId'  => $data['notification_id'],
                'eventType' => 'ADD',
                'eventId'   => 'notificationsadd',
                'info'      => _ADD_NOTIFICATIONS . ' : ' . $data['notification_id']
            ]);
            return $response->withJson(NotificationModel::getByNotificationId(['notificationId' => $data['notification_id']]));
        } else {
            return $response->withStatus(400)->withJson(['errors' => 'Notification Create Error']);
        }

    }

    public function update(Request $request, Response $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_notif', 'userId' => $GLOBALS['userId'], 'location' => 'notifications', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();

        $data['notification_sid']     = $aArgs['id'];
        $data['diffusion_properties'] = implode(",", $data['diffusion_properties']);
        
        $data['attachfor_properties'] = implode(",", $data['attachfor_properties']);

        NotificationModel::update($data);

        $notification = NotificationModel::getById(['notificationId' => $data['notification_id']]);

        HistoryController::add([
            'tableName' => 'notifications',
            'recordId'  => $data['notification_sid'],
            'eventType' => 'UP',
            'eventId'   => 'notificationsup',
            'info'      => _MODIFY_NOTIFICATIONS . ' : ' . $data['notification_sid']
        ]);

        return $response->withJson(['notification'=> $notification]);
    }

    public function delete(Request $request, Response $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_notif', 'userId' => $GLOBALS['userId'], 'location' => 'notifications', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        NotificationModel::delete(['notification_sid' => $aArgs['id']]);

        HistoryController::add([
                'tableName' => 'notifications',
                'recordId'  => $aArgs['id'],
                'eventType' => 'DEL',
                'eventId'   => 'notificationsdel',
                'info'      => _DELETE_NOTIFICATIONS . ' : ' . $aArgs['id']
            ]);


        return $response->withJson([
            'success' => _DELETED_NOTIFICATION,
            'notifications' => NotificationModel::get()
        ]);
    }

    public function initNotification(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_notif', 'userId' => $GLOBALS['userId'], 'location' => 'notifications', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $notification = [];
        $notification['diffusion_properties'] = [];
        $notification['attachfor_properties'] = [];
        $data = [];

        $data['event']         = NotificationModel::getEvent();
        $data['template']      = NotificationModel::getTemplate();
        $data['diffusionType'] = NotificationModel::getDiffusionType();
        $data['groups']        = NotificationModel::getDiffusionTypeGroups();
        $data['users']         = NotificationModel::getDiffusionTypesUsers();
        $data['entities']      = NotificationModel::getDiffusionTypeEntities();
        $data['status']        = NotificationModel::getDiffusionTypeStatus();

        $notification['data'] = $data;

        return $response->withJson(['notification'=>$notification]);
    }

}

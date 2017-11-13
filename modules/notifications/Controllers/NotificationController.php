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

namespace Notifications\Controllers;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Notifications\Models\NotificationModel;
use Core\Models\ServiceModel;
use Core\Controllers\HistoryController;


class NotificationController
{
    public function get(RequestInterface $request, ResponseInterface $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_notif', 'userId' => $_SESSION['user']['UserId'], 'location' => 'notifications', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $notification['notifications'] = NotificationModel::get(['select' => ['notification_sid', 'notification_id', 'description', 'is_enabled', 'event_id', 'notification_mode', 'template_id', 'diffusion_type']]);

        return $response->withJson($notification);
    }

    public function getById(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_notif', 'userId' => $_SESSION['user']['UserId'], 'location' => 'notifications', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $notification['notifications'] = NotificationModel::getByNotificationId(['notificationId' => $aArgs['id'], 'select' => ['notification_sid', 'notification_id', 'description', 'is_enabled', 'event_id', 'notification_mode', 'template_id', 'diffusion_type','diffusion_properties', 'attachfor_type','attachfor_properties']]);
        if (empty($notification['notifications'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Notification not found']);
        }

        return $response->withJson($notification);
    }
    

    public function create(RequestInterface $request, ResponseInterface $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_notif', 'userId' => $_SESSION['user']['UserId'], 'location' => 'notifications', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();
        if(empty($data['notification_id'])){
            return $response->withStatus(400)->withJson(['errors' => 'Notification error : notification_id is empty']);
        }
        $notificationInDb = NotificationModel::getByNotificationId(['notificationId' => $data['notification_id'], 'select' => ['notification_sid']]);
        
        if($data){
            if(is_int($notificationInDb['notification_sid'])) {
                 return $response->withStatus(400)->withJson(['errors' => _NOTIFICATIONS_ERROR.' '._NOTIF_ALREADY_EXIST]);               
            }elseif(strlen($data['description']) > 255) {
                return $response->withStatus(400)->withJson(['errors' => _NOTIFICATIONS_ERROR.' '._NOTIF_DESCRIPTION_TOO_LONG]);
            }elseif(strlen($data['event_id']) > 255 && is_string($data['event_id'])) {
                return $response->withStatus(400)->withJson(['errors' => _NOTIFICATIONS_ERROR.' '._NOTIF_EVENT_TOO_LONG]);
            }elseif(strlen($data['notification_mode']) > 30){
                return $response->withStatus(400)->withJson(['errors' => _NOTIFICATIONS_ERROR.' '._NOTIF_MODE_TOO_LONG]);
            }elseif(Validator::intType()->notEmpty()->validate($data['template_id'])){
                return $response->withStatus(400)->withJson(['errors' => _NOTIFICATIONS_ERROR.' '._NOTIF_TEMPLATE_NOT_A_INT]);
            }elseif(!is_string($data['diffusion_type'])){
                return $response->withStatus(400)->withJson(['errors' => _NOTIFICATIONS_ERROR.' '._NOTIF_DIFFUSION_IS_A_INT]);
            }

            if($data['is_enabled'] == true){
                $data['is_enabled'] = 'Y';
            }else{
                $data['is_enabled'] = 'N';
            }

            $data['notification_mode'] = 'EMAIL';
            
            if($data['diffusion_properties']){
                $data['diffusion_properties'] = implode(",", $data['diffusion_properties']);
            }
            
            if($data['attachfor_properties']){
                $data['attachfor_properties'] = implode(",", $data['attachfor_properties']);
            }else{
                $data['attachfor_properties'] = '';
            }

            if (NotificationModel::create($data)) {
                HistoryController::add([
                    'tableName' => 'notifications',
                    'recordId'  => $data['notification_id'],
                    'eventType' => 'ADD',
                    'eventId'   => 'notificationsadd',
                    'info'       => _ADD_NOTIFICATIONS . ' : ' . $data['notification_id']
                ]);
                return $response->withJson(NotificationModel::getByNotificationId(['notificationId' => $data['notification_id']]));
            } else {
                return $response->withStatus(400)->withJson(['errors' => 'Notification Create Error']);
            }

        }
    }

    public function update(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_notif', 'userId' => $_SESSION['user']['UserId'], 'location' => 'notifications', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();

        $data['notification_sid'] = $aArgs['id'];
        $data['diffusion_properties'] = implode(",", $data['diffusion_properties']);
        
        $data['attachfor_properties'] = implode(",", $data['attachfor_properties']);
        if (!empty($errors)) {
            return $response->withStatus(500)->withJson(['errors' => $errors]);
        }

        NotificationModel::update($data);

        $notification = NotificationModel::getById(['notificationId' => $data['notification_id']]);

        HistoryController::add([
            'tableName' => 'notifications',
            'recordId'  => $data['notification_sid'],
            'eventType' => 'UP',
            'eventId'   => 'notificationsup',
            'info'       => _MODIFY_NOTIFICATIONS . ' : ' . $data['notification_sid']
        ]);

        return $response->withJson(['notification'=> $notification]);
    }

    public function delete(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_notif', 'userId' => $_SESSION['user']['UserId'], 'location' => 'notifications', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        NotificationModel::delete(['notification_sid' => $aArgs['id']]);

        HistoryController::add([
                'tableName' => 'notifications',
                'recordId'  => $aArgs['id'],
                'eventType' => 'DEL',
                'eventId'   => 'notificationsdel',
                'info'       => _DELETE_NOTIFICATIONS . ' : ' . $aArgs['id']
            ]);


        return $response->withJson([
            'success' => _DELETED_NOTIFICATION,
            'notifications' => NotificationModel::get(['select' => ['notification_sid', 'notification_id', 'description', 'is_enabled', 'event_id', 'notification_mode', 'template_id', 'diffusion_type']])
        ]);
    }

    public function getNewNotificationForAdministration(RequestInterface $request, ResponseInterface $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_notif', 'userId' => $_SESSION['user']['UserId'], 'location' => 'notifications', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $notification = [];
        $notification['diffusion_properties'] = [];
        $notification['attachfor_properties'] = [];
        $data = [];

        $data['event'] = NotificationModel::getEvent();
        $data['template'] = NotificationModel::getTemplate();
        $data['diffusionType'] = NotificationModel::getDiffusionType();
        $data['groups'] = NotificationModel::getDiffusionTypeGroups();
        $data['users'] = NotificationModel::getDiffusionTypesUsers();
        $data['entities'] = NotificationModel::getDiffusionTypeEntities();
        $data['status'] = NotificationModel::getDiffusionTypeStatus();

        $notification['data'] = $data;

        return $response->withJson(['notification'=>$notification]);
    }

    public function getNotificationForAdministration(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_notif', 'userId' => $_SESSION['user']['UserId'], 'location' => 'notifications', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $notification = NotificationModel::getById(['notification_sid' => $aArgs['id'], 'select' => ['notification_sid', 'notification_id', 'description', 'is_enabled', 'event_id', 'notification_mode', 'template_id', 'diffusion_type','diffusion_properties', 'attachfor_type','attachfor_properties']]);

        $notification['diffusion_properties'] = explode(",",$notification['diffusion_properties']);
        
        foreach ($notification['diffusion_properties'] as $key => $value) {
            $notification['diffusion_properties'][$value] = $value;
            unset($notification['diffusion_properties'][$key]);
        }

        $notification['attachfor_properties'] = explode(",",$notification['attachfor_properties']);
        
        foreach ($notification['attachfor_properties'] as $key => $value) {
            $notification['attachfor_properties'][$value] = $value;
            unset($notification['attachfor_properties'][$key]);
        }
        
        if (empty($notification)) {
                return $response->withStatus(400)->withJson(['errors' => 'Notification not found']);
        }
        $data = [];

        $data['event'] = NotificationModel::getEvent();
        $data['template'] = NotificationModel::getTemplate();
        $data['diffusionType'] = NotificationModel::getDiffusionType();
        $data['groups'] = NotificationModel::getDiffusionTypeGroups();
        $data['users'] = NotificationModel::getDiffusionTypesUsers();
        $data['entities'] = NotificationModel::getDiffusionTypeEntities();
        $data['status'] = NotificationModel::getDiffusionTypeStatus();

        $notification['data'] = $data;
        

        return $response->withJson(['notification'=>$notification]);
    }
}
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
use Core\Models\LangModel;
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
        $notification['notifications'] = NotificationModel::getById(['notificationId' => $aArgs['id'], 'select' => ['notification_sid', 'notification_id', 'description', 'is_enabled', 'event_id', 'notification_mode', 'template_id', 'diffusion_type']]);
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
        $notificationInDb = NotificationModel::getById(['notificationId' => $data['notification_id'], 'select' => ['notification_sid']]);

        if($data){
            if(is_int($notificationInDb['notification_sid'])){
                 return $response->withStatus(400)->withJson(['errors' => 'Notification error : id already exist']);
            }elseif(strlen($data[is_enabled]) > 1 && $data[is_enabled]){
                return $response->withStatus(400)->withJson(['errors' => 'Notification error : bad value for is_enabled ']);
            }elseif(strlen($data[description]) > 255){
                return $response->withStatus(400)->withJson(['errors' => 'Notification error : description is too long ']);
            }elseif(strlen($data[event_id]) > 255 && is_string($data[event_id])){
                return $response->withStatus(400)->withJson(['errors' => 'Notification error : event_id is too long ']);
            }elseif(strlen($data[notification_mode]) > 30){
                return $response->withStatus(400)->withJson(['errors' => 'Notification error : notification_mode is too long ']);
            }elseif(Validator::intType()->notEmpty()->validate($data[template_id])){
                return $response->withStatus(400)->withJson(['errors' => 'Notification error : template_id not a int ']);
            }elseif(!is_string($data[rss_url_template])){
                return $response->withStatus(400)->withJson(['errors' => 'Notification error : rss_url_template is not in good format ']);
            }elseif(!is_string($data[diffusion_type])){
                return $response->withStatus(400)->withJson(['errors' => 'Notification error : diffusion_type is a int ']);
            }elseif(!is_string($data[diffusion_properties])){
                return $response->withStatus(400)->withJson(['errors' => 'Notification error : template_id note a int ']);
            }

            if (NotificationModel::create($data)) {
                HistoryController::add([
                'table_name' => 'notifications',
                'record_id'  => $data['notification_id'],
                'event_type' => 'ADD',
                'event_id'   => 'notificationsadd',
                'info'       => _ADD_NOTIFICATIONS . ' : ' . $data['notification_id']
                ]);
                return $response->withJson(NotificationModel::getById(['notificationId' => $data['notification_id']]));
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
        //$aArgs   = self::manageValue($request);
        //$errors  = $this->control($aArgs, 'update');

        if (!empty($errors)) {
            return $response->withStatus(500)->withJson(['errors' => $errors]);
        }

        NotificationModel::update($data);

            //var_dump($aArgs);
            $notification = NotificationModel::getById(['notificationId' => $data['notification_id']]);

            HistoryController::add([
                'table_name' => 'notifications',
                'record_id'  => $notification['notification_id'],
                'event_type' => 'UP',
                'event_id'   => 'notificationsup',
                'info'       => _MODIFY_NOTIFICATIONS . ' : ' . $notification['notification_id']
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
                'table_name' => 'notifications',
                'record_id'  => $aArgs['id'],
                'event_type' => 'DEL',
                'event_id'   => 'notificationsdel',
                'info'       => _DELETE_NOTIFICATIONS . ' : ' . $aArgs['id']
            ]);


        return $response->withJson(['success' => _DELETED_NOTIFICATION]);
    }
}
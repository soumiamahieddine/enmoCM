<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Notifications Schedule Controller
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
use SrcCore\models\CoreConfigModel;

class NotificationScheduleController
{
    public function get(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_notif', 'userId' => $GLOBALS['userId'], 'location' => 'notifications', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        return $response->withJson([
            'crontab'                => self::getCrontab(),
            'authorizedNotification' => self::getAuthorizedNotifications(),
        ]);
    }
    
    public function saveCrontabRest(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_notif', 'userId' => $GLOBALS['userId'], 'location' => 'notifications', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();
        if (!self::checkCrontab($data)) {
            return $response->withStatus(500)->withJson(['errors' => 'Problem with crontab']);
        }

        foreach ($data as $id => $cronValue) {
            foreach ($cronValue as $key => $value) {
                if (!Validator::notEmpty()->validate($value)) {
                    $errors[] = $key." is empty";
                }
                if ($key != "cmd" && $key != "state" && !Validator::intVal()->validate($value) && $value != "*") {
                    $errors[] = "wrong format for ".$key;
                }
            }
        }
        if (!empty($errors)) {
            return $response->withStatus(500)->withJson(['errors' => $errors]);
        }

        self::saveCrontab($data);

        return $response->withJson(true);
    }

    public static function saveCrontab($data)
    {
        $aCrontab = self::getCrontab(false);

        $file = [];
        foreach ($data as $id => $cronValue) {
            if ($cronValue['state'] == 'hidden') {
                $file[$id] = "{$aCrontab[$id]['m']}\t{$aCrontab[$id]['h']}\t{$aCrontab[$id]['dom']}\t{$aCrontab[$id]['mon']}\t{$aCrontab[$id]['dow']}\t{$aCrontab[$id]['cmd']}";
            } elseif ($cronValue['state'] != 'deleted') {
                $file[$id] = "{$cronValue['m']}\t{$cronValue['h']}\t{$cronValue['dom']}\t{$cronValue['mon']}\t{$cronValue['dow']}\t{$cronValue['cmd']}";
            }
        }

        $output = '';

        if (isset($file)) {
            foreach ($file as $l) {
                $output .= "$l\n";
            }
        }

        $output = preg_replace("!\n+$!", "\n", $output);
        file_put_contents('/tmp/crontab.plain', print_r($file, true));
        file_put_contents('/tmp/crontab.txt', $output);

        exec('crontab /tmp/crontab.txt');

        HistoryController::add([
            'tableName' => 'notifications',
            'recordId'  => $GLOBALS['userId'],
            'eventType' => 'UP',
            'eventId'   => 'notificationadd',
            'info'      => _NOTIFICATION_SCHEDULE_UPDATED
        ]);

	return true;
    }

    public static function getCrontab($getHiddenValue = true)
    {
        $crontab  = shell_exec('crontab -l');
        $lines    = explode("\n", $crontab);
        $data     = array();
        $customId = CoreConfigModel::getCustomId();
        $corePath = dirname(__FILE__, 5) . '/';

        foreach ($lines as $cronLine) {
            $cronLine = trim($cronLine);
            if (strpos($cronLine, '#') !== false) {
                $cronLine = substr($cronLine, 0, strpos($cronLine, '#'));
            }
            if (empty($cronLine)) {
                continue;
            }
            $cronLine = preg_replace('![ \t]+!', ' ', $cronLine);
            if ($cronLine[0] == '@') {
                list($time, $cmd) = explode(' ', $cronLine, 2);
            } else {
                list($m, $h, $dom, $mon, $dow, $cmd) = explode(' ', $cronLine, 6);
            }

            if ($customId <> '') {
                $pathToFolow = $corePath . 'custom/'.$customId . '/';
            } else {
                $pathToFolow = $corePath;
            }

            $state = "normal";
            if (strpos($cmd, $pathToFolow.'modules/notifications/batch/scripts/') !== 0 && $getHiddenValue) {
                $cmd   = "hidden";
                $state = 'hidden';
            }

            $data[] = array(
                'm'     => $m,
                'h'     => $h,
                'dom'   => $dom,
                'mon'   => $mon,
                'dow'   => $dow,
                'cmd'   => $cmd,
                'state' => $state
            );
        }
        return $data;
    }

    protected static function getAuthorizedNotifications()
    {
        $aNotification      = NotificationModel::getEnableNotifications(['select' => ['notification_sid', 'description']]);
        $notificationsArray = array();
        $customId           = CoreConfigModel::getCustomId();
        $corePath           = dirname(__FILE__, 5) . '/';

        foreach ($aNotification as $result) {
            $filename = "notification";
            if (isset($customId) && $customId<>"") {
                $filename.="_".str_replace(" ", "", $customId);
            }
            $filename.="_".$result['notification_sid'].".sh";

            if ($customId <> '') {
                $pathToFolow = $corePath . 'custom/'.$customId . '/';
            } else {
                $pathToFolow = $corePath;
            }

            $path = $pathToFolow.'modules/notifications/batch/scripts/'.$filename;

            if (file_exists($path)) {
                $notificationsArray[$path] = $result['description'];
            }
        }
        
        return $notificationsArray;
    }

    protected static function checkCrontab($crontabToSave)
    {
        $customId          = CoreConfigModel::getCustomId();
        $crontabBeforeSave = self::getCrontab();
        $corePath          = dirname(__FILE__, 5) . '/';
        foreach ($crontabToSave as $id => $cronValue) {
            if ($cronValue['state'] != "hidden" && $crontabBeforeSave[$id]['state'] == "hidden") {
                $returnValue = false;
                break;
            } elseif ($cronValue['state'] == "hidden" && $crontabBeforeSave[$id]['state'] != "hidden") {
                $returnValue = false;
                break;
            } elseif ($cronValue['state'] == "new" || $cronValue['state'] == "normal") {
                if ($customId <> '') {
                    $pathToFolow = $corePath . 'custom/'.$customId . '/';
                } else {
                    $pathToFolow = $corePath;
                }
                $returnValue = true;
                if (strpos($crontabToSave[$id]['cmd'], $pathToFolow.'modules/notifications/batch/scripts/') !== 0) {
                    $returnValue = false;
                    break;
                }
            } else {
                $returnValue = true;
            }
        }

        return $returnValue;
    }

    public function createScriptNotificationRest(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_notif', 'userId' => $GLOBALS['userId'], 'location' => 'notifications', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $errors = [];
        $data = $request->getParams();
        if (!Validator::intVal()->validate($data['notification_sid'])) {
            $errors[] = 'notification_sid is not a numeric';
        }
        if (!Validator::notEmpty()->validate($data['notification_sid']) ||
            !Validator::notEmpty()->validate($data['notification_id'])) {
            $errors[] = 'one of arguments is empty';
        }

        if (!empty($errors)) {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => $errors]);
        }

        $notification_sid = $data['notification_sid'];
        $notification_id  = $data['notification_id'];

        self::createScriptNotification($notification_sid, $notification_id);

        return $response->withJson(true);
    }

    public static function createScriptNotification($notification_sid, $notification_id){

        //Creer le script sh pour les notifications
        $filename = "notification";
        $customId = CoreConfigModel::getCustomId();
        if (isset($customId) && $customId<>"") {
            $filename.="_".str_replace(" ", "", $customId);
        }
        $filename.="_".$notification_sid.".sh";

        $corePath = dirname(__FILE__, 5) . '/';

        if (file_exists($corePath. 'custom/'.$customId .'/modules/notifications/batch/config/config.xml')) {
            $ConfigNotif = $corePath. 'custom/'. $customId .'/modules/notifications/batch/config/config.xml';
        } elseif (file_exists($corePath. 'custom/'. $customId .'/modules/notifications/batch/config/config_'.$customId.'.xml')) {
            $ConfigNotif = $corePath. 'custom/'. $customId .'/modules/notifications/batch/config/config_'.$customId.'.xml';
        } elseif (file_exists($corePath. 'modules/notifications/batch/config/config_'.$customId.'.xml')) {
            $ConfigNotif = $corePath. 'modules/notifications/batch/config/config_'.$customId.'.xml';
        } else {
            $ConfigNotif = $corePath. 'modules/notifications/batch/config/config.xml';
        }
        
        if ($customId <> '') {
            $pathToFolow = $corePath . 'custom/'.$customId . '/';
            if (!file_exists($pathToFolow.'modules/notifications/batch/scripts/')) {
                mkdir($pathToFolow.'modules/notifications/batch/scripts/', 0777, true);
            }
            $file_open = fopen($pathToFolow.'modules/notifications/batch/scripts/'.$filename, 'w+');
        } else {
            $pathToFolow = $corePath;
            $file_open = fopen($pathToFolow.'modules/notifications/batch/scripts/'.$filename, 'w+');
        }

        fwrite($file_open, '#!/bin/sh');
        fwrite($file_open, "\n");
        fwrite($file_open, 'path=\''.$corePath.'modules/notifications/batch/\'');
        fwrite($file_open, "\n");
        fwrite($file_open, 'cd $path');
        fwrite($file_open, "\n");
        if ($notification_id == 'BASKETS') {
            fwrite($file_open, 'php \'basket_event_stack.php\' -c '.$ConfigNotif.' -n '.$notification_id);
        } elseif ($notification_id == 'RELANCE1' || $notification_id == 'RELANCE2' || $notification_id == 'RET1' || $notification_id == 'RET2') {
            fwrite($file_open, 'php \'stack_letterbox_alerts.php\' -c '.$ConfigNotif);
            fwrite($file_open, "\n");
            fwrite($file_open, 'php \'process_event_stack.php\' -c '.$ConfigNotif.' -n '.$notification_id);
        } else {
            fwrite($file_open, 'php \'process_event_stack.php\' -c '.$ConfigNotif.' -n '.$notification_id);
        }
        fwrite($file_open, "\n");
        fwrite($file_open, 'cd $path');
        fwrite($file_open, "\n");
        fwrite($file_open, 'php \'process_email_stack.php\' -c '.$ConfigNotif);
        fwrite($file_open, "\n");
        fclose($file_open);
        shell_exec("chmod +x " . escapeshellarg($pathToFolow . "modules/notifications/batch/scripts/" . $filename));

        HistoryController::add([
            'tableName' => 'notifications',
            'recordId'  => $notification_id,
            'eventType' => 'ADD',
            'eventId'   => 'notificationadd',
            'info'      => _NOTIFICATION_SCRIPT_ADDED
        ]);

        return true;
    }

}

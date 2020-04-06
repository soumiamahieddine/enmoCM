<?php
/******************************************************************************
 BATCH BASKET EVENT STACK

 Processes events from table event_stack

 1 - Add events for each basket with notif enabled

 2 - Scan event

 3 - Prepare e-mail and add to e-mail stack

******************************************************************************/

/* begin */
// load the config and prepare to process
include 'load_basket_event_stack.php';
$state = 'LOAD_NOTIFICATIONS';
while ($state != 'END') {
    if (isset($logger)) {
        Bt_writeLog(['level' => 'INFO', 'message' => 'STATE:'.$state]);
    }
    switch ($state) {
        /**********************************************************************/
        /*                          LOAD_NOTIFICATIONS                        */
        /* Load notification defsidentified with notification id              */
        /**********************************************************************/
        case 'LOAD_NOTIFICATIONS':
            Bt_writeLog(['level' => 'INFO', 'message' => 'Loading configuration for notification id '.$notificationId]);
            
            $notification = \Notification\models\NotificationModel::getByNotificationId(['notificationId' => $notificationId, 'select' => ['*']]);
            if ($notification === false) {
                Bt_exitBatch(1, "Notification '".$notificationId."' not found");
            }
            if ($notification['is_enabled'] === 'N') {
                Bt_exitBatch(100, "Notification '".$notificationId."' is disabled");
            }
            //Attach Mode ?
            if (!empty($notification['attachfor_type']) || $notification['attachfor_type'] != null) {
                $attachMode = true;
                Bt_writeLog(['level' => 'INFO', 'message' => 'The document will be attached for each recipient']);
            } else {
                $attachMode = false;
            }
            $state = 'ADD_EVENTS';
            break;

        /**********************************************************************/
        /*                          LOAD_EVENTS                               */
        /* Checking if the stack has notifications to proceed                 */
        /**********************************************************************/
        case 'ADD_EVENTS':
            $db = new Database();

            $baskets = \Basket\models\BasketModel::get(['select' => ['basket_id', 'basket_clause'], 'where' => ['flag_notif = ?'], 'data' => ['Y']]);

            foreach ($baskets as $basket) {
                Bt_writeLog(['level' => 'INFO', 'message' => 'BASKET: '.$basket['basket_id'].' in progess ...']);
                $exceptUsers[$basket['basket_id']] = [];
                $groups = \Basket\models\GroupBasketModel::get(['select' => ['group_id'], 'where' => ['basket_id = ?'], 'data' => [$basket['basket_id']]]);
                $nbGroups = count($groups);

                $u = 1;
                foreach ($groups as $group) {
                    $groupInfo = \Group\models\GroupModel::getByGroupId(['groupId' => $group['group_id'], 'select' => ['id']]);
                    if ($notification['diffusion_type'] == 'group') {
                        $users = \Group\models\GroupModel::getUsersById(['select' => ['users.user_id', 'users.id'], 'id' => $groupInfo['id']]);
                    } else {
                        $users = \Group\models\GroupModel::getUsersById(['select' => ['users.user_id', 'users.id'], 'id' => 0]);
                    }

                    $countUsersToNotify = count($users);
                    Bt_writeLog(['level' => 'INFO', 'message' => 'GROUP: '.$group['group_id'].' ... '.$countUsersToNotify.' user(s) to notify']);
                    $z = 1;
                    foreach ($users as $userToNotify) {
                        $real_user_id = '';
                        $whereClause  = \SrcCore\controllers\PreparedClauseController::getPreparedClause(['clause' => $basket['basket_clause'], 'login' => $userToNotify['user_id']]);
                        $user_id      = $userToNotify['user_id'];
                        $redirectedBasket = \Basket\models\RedirectBasketModel::get([
                            'select'    => ['actual_user_id'],
                            'where'     => ['owner_user_id = ?', 'basket_id = ?', 'group_id = ?'],
                            'data'      => [$userToNotify['id'], $basket['basket_id'], $groupInfo['id']]
                        ]);
                        if (!empty($redirectedBasket)) {
                            $real_user_id = $user_id;
                            $user         = \User\models\UserModel::getById(['id' => $redirectedBasket[0]['actual_user_id'], 'select' => ['user_id']]);
                            $user_id      = $user['user_id'];
                        }

                        $resources = \Resource\models\ResModel::getOnView([
                            'select'    => ['res_id'],
                            'where'     => [$whereClause],
                            'data'      => []
                        ]);
                        if (!empty($resources)) {
                            $userNbDoc = count($resources);
                            Bt_writeLog(['level' => 'INFO', 'message' => $userNbDoc.' document(s) to process for '.$userToNotify['user_id']]);
                            $i = 1;
                            $info = 'Notification ['.$basket['basket_id'].'] pour '.$userToNotify['user_id'];
                            if (!empty($real_user_id)) {
                                $notificationEvents = \Notification\models\NotificationsEventsModel::get(['select' => ['record_id'], 'where' => ['event_info', '(user_id = ? OR user_id = ?)'], 'data' => [$info, $userToNotify['user_id'], $user_id]]);
                            } else {
                                $notificationEvents = \Notification\models\NotificationsEventsModel::get(['select' => ['record_id'], 'where' => ['event_info', 'user_id = ?'], 'data' => [$info, $userToNotify['user_id']]]);
                            }

                            $aValues = [];
                            foreach ($resources as $resource) {
                                echo 'DOCUMENT '.$i.'/'.$userNbDoc.' for USER '.$z.'/'.$countUsersToNotify.' and GROUP '.$u.'/'.$nbGroups."\n";
                                if (empty($aRecordId[$resource['res_id']])) {
                                    $aValues[] = [
                                        'res_letterbox',
                                        '500',
                                        $resource['res_id'],
                                        $user_id,
                                        $info
                                    ];
                                }
                                ++$i;
                            }
                            if (!empty($aValues)) {
                                \SrcCore\models\DatabaseModel::insertMultiple([
                                    'table'     => 'notif_event_stack',
                                    'columns'   => ['table_name', 'notification_sid', 'record_id', 'user_id', 'event_info'],
                                    'values'    => $aValues
                                ]);
                            }
                        }
                        ++$z;
                    }
                    ++$u;
                }
            }
            Bt_writeLog(['level' => 'INFO', 'message' => 'Scanning events for notification sid '.$notification['notification_sid']]);
            $events = \Notification\models\NotificationsEventsModel::getById(['select' => ['*'], 'notificationSid' => '500']);
            $totalEventsToProcess = count($events);
            $currentEvent = 0;
            if ($totalEventsToProcess === 0) {
                Bt_exitBatch(0, 'No event to process');
            }
            Bt_writeLog(['level' => 'INFO', 'message' => $totalEventsToProcess.' event(s) to scan']);
            $tmpNotifs = array();
            $state = 'SCAN_EVENT';
            break;

        /**********************************************************************/
        /*                  MERGE_EVENT                                       */
        /* Process event stack to get recipients                              */
        /**********************************************************************/
        case 'SCAN_EVENT':
            $i = 1;

            foreach ($events as $event) {
                preg_match_all('#\[(\w+)]#', $event['event_info'], $result);
                $basket_id = $result[1];
                Bt_writeLog(['level' => 'INFO', 'message' => 'scanning EVENT : '.$i.'/'.$totalEventsToProcess.' (BASKET => '.$basket_id[0].', DOCUMENT => '.$res_id.', RECIPIENT => '.$user_id.')']);

                // Diffusion type specific res_id
                $res_id = false;
                if ($event['table_name'] == $coll_table || $event['table_name'] == $coll_view) {
                    $res_id = $event['record_id'];
                } else {
                    $res_id = $diffusion_type_controler->getResId($notification, $event);
                }

                $event['res_id'] = $res_id;
                $user_id = $event['user_id'];

                if (!isset($tmpNotifs[$user_id])) {
                    $tmpNotifs[$user_id]['recipient'] = \User\models\UserModel::getByLogin(['select' => ['*'], 'login' => $user_id]);
                }
                preg_match_all('#\[(\w+)]#', $event['event_info'], $result);
                $basket_id = $result[1];
                $tmpNotifs[$user_id]['baskets'][$basket_id[0]]['events'][] = $event;

                ++$i;
            }
            $totalNotificationsToProcess = count($tmpNotifs);
            Bt_writeLog(['level' => 'INFO', 'message' => $totalNotificationsToProcess.' notifications to process']);

        /**********************************************************************/
        /*                      FILL_EMAIL_STACK                              */
        /* Merge template and fill notif_email_stack                          */
        /**********************************************************************/
            $logger->write('STATE:MERGE NOTIF', 'INFO');
            $i = 1;
            foreach ($tmpNotifs as $user_id => $tmpNotif) {
                foreach ($tmpNotif['baskets'] as $basketId => $basket_list) {
                    $baskets = \Basket\models\BasketModel::getByBasketId(['select' => ['basket_name'], 'basketId' => $basketId]);
                    $subject = $baskets['basket_name'];

                    // Merge template with data and style
                    Bt_writeLog(['level' => 'INFO', 'message' => 'generate e-mail '.$i.'/'.$totalNotificationsToProcess.' (TEMPLATE =>'.$notification['template_id'].', SUBJECT => '.$subject.', RECIPIENT => '.$user_id.', DOCUMENT(S) => '.count($basket_list['events'])]);

                    $params = array(
                        'recipient'    => $tmpNotif['recipient'],
                        'events'       => $basket_list['events'],
                        'notification' => $notification,
                        'maarchUrl'    => $maarchUrl,
                        'maarchApps'   => $maarchApps,
                        'coll_id'      => $coll_id,
                        'res_table'    => $coll_table,
                        'res_view'     => $coll_view,
                    );
                    $html = $templates_controler->merge($notification['template_id'], $params, 'content');

                    if (strlen($html) === 0) {
                        foreach ($tmpNotif['events'] as $event) {
                            $events_controler->commitEvent($event->event_stack_sid, 'FAILED: Error when merging template');
                        }
                        Bt_exitBatch(8, 'Could not merge template with the data');
                    }

                    // Prepare e-mail for stack
                    $sender = (string) $mailerParams->mailfrom;
                    $recipient_mail = $tmpNotif['recipient']->mail;

                    if (!empty($recipient_mail)) {
                        $html = str_replace("&#039;", "'", $html);
                        $html = pg_escape_string($html);
                        $html = str_replace('&amp;', '&', $html);
                        $html = str_replace('&', '#and#', $html);

                        // Attachments
                        $attachments = array();
                        if ($attachMode) {
                            $logger->write('Adding attachments', 'INFO');
                            foreach ($basket_list['events'] as $event) {
                                // Check if event is related to document in collection
                                if ($event->res_id != '') {
                                    $query = 'SELECT '
                                        .'ds.path_template ,'
                                        .'mlb.path, '
                                        .'mlb.filename '
                                        .'FROM '.$coll_view.' mlb LEFT JOIN docservers ds ON mlb.docserver_id = ds.docserver_id '
                                        .'WHERE mlb.res_id = ?';
                                    $stmt = Bt_doQuery($db, $query, array($event->res_id));
                                    $path_parts = $stmt->fetchObject();
                                    $path = $path_parts->path_template.str_replace('#', '/', $path_parts->path).$path_parts->filename;
                                    $path = str_replace('//', '/', $path);
                                    $path = str_replace('\\', '/', $path);
                                    $attachments[] = $path;
                                }
                            }
                            $logger->write(count($attachments).' attachment(s) added', 'INFO');
                        }
                    
                        if (in_array($user_id, $exceptUsers[$basketId])) {
                            $logger->write('Notification disabled for '.$user_id, 'WARNING');
                        } else {
                            $logger->write('... adding e-mail to email stack', 'INFO');
                            if ($_SESSION['config']['databasetype'] == 'ORACLE') {
                                $query = "DECLARE
                                        vString notif_email_stack.html_body%type;
                                        BEGIN
                                        vString := '".$html."';
                                        INSERT INTO "._NOTIF_EMAIL_STACK_TABLE_NAME."
                                        (sender, recipient, subject, html_body, charset, attachments, module) 
                                        VALUES (?, ?, ?, vString, ?, '".implode(',', $attachments)."', 'notifications');
                                        END;";
                                $arrayPDO = array($sender, $recipient_mail, $subject, $mailerParams->charset);
                            } else {
                                if (count($attachments) > 0) {
                                    $query = 'INSERT INTO '._NOTIF_EMAIL_STACK_TABLE_NAME
                                    .' (sender, recipient, subject, html_body, charset, attachments, module) '
                                    ."VALUES (?, ?, ?, ?, ?, '".implode(',', $attachments)."', 'notifications')";
                                } else {
                                    $query = 'INSERT INTO '._NOTIF_EMAIL_STACK_TABLE_NAME
                                    .' (sender, recipient, subject, html_body, charset, module) '
                                    ."VALUES (?, ?, ?, ?, ?, 'notifications')";
                                }
                                $arrayPDO = array($sender, $recipient_mail, $subject, $html, $mailerParams->charset);
                            }
    
                            $db->query($query, $arrayPDO);
                        }
                        foreach ($basket_list['events'] as $event) {
                            if (in_array($event->user_id, $exceptUsers[$basketId])) {
                                $events_controler->commitEvent($event->event_stack_sid, 'WARNING : Notification disabled for '.$event->user_id);
                            } else {
                                $events_controler->commitEvent($event->event_stack_sid, 'SUCCESS');
                            }
                        }
                    }
                }
                ++$i;
            }
            $state = 'END';
    }
}

//clean tmp directory
echo "clean tmp path ....\n";
array_map('unlink', glob($_SESSION['config']['tmppath'].'/*.html'));

$logger->write('End of process', 'INFO');
Bt_logInDataBase(
    $totalEventsToProcess,
    0,
    $totalNotificationsToProcess.' notification(s) processed without error'
);

exit($GLOBALS['exitCode']);

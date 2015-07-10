<?php

/*
*   Copyright 2008-2011 Maarch
*
*   This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with Maarch Framework. If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief Contains the docservers_controler Object
* (herits of the BaseObject class)
*
* @file
* @author Loïc Vinet - Maarch
* @date $date$
* @version $Revision$
* @ingroup core
*/

//Loads the required class
try {
    require_once 'modules/notifications/class/events.php';
    require_once 'modules/notifications/notifications_tables_definition.php';
    require_once 'core/class/ObjectControlerAbstract.php';
    require_once 'core/class/class_db_pdo.php';
} catch (Exception $e) {
    echo $e->getMessage() . ' // ';
}

/**
 * Class for controling docservers objects from database
 */
class events_controler
    extends ObjectControler
{
    public function getEventsByNotificationSid($notification_sid) 
    {
        $query = "SELECT * FROM " . _NOTIF_EVENT_STACK_TABLE_NAME
            . " WHERE exec_date is NULL "
            . " AND notification_sid = " . $notification_sid ;
        $dbConn = new dbquery();
        $dbConn->connect();
        $dbConn->query($query);
        $events = array();
        while ($eventRecordset = $dbConn->fetch_object()) {
            $events[] = $eventRecordset;
        }
        return $events;
    }
    
  
    function wildcard_match($pattern, $str)
    {
        $pattern = '/^' . str_replace(array('%', '\*', '\?', '\[', '\]'), array('.*', '.*', '.', '[', ']+'), preg_quote($pattern)) . '$/is';
        $result = preg_match($pattern, $str);
        return $result;
    }
    
    public function fill_event_stack($event_id, $table_name, $record_id, $user, $info) {
        if ($record_id == '') return;
        
        $query = "SELECT * "
            ."FROM " . _NOTIFICATIONS_TABLE_NAME 
            ." WHERE is_enabled = 'Y'";
        $dbConn = new Database();
        $stmt = $dbConn->query($query);
        if($stmt->rowCount() === 0) {
            return;
        }
        
        while($notification = $stmt->fetchObject()) {
            $event_ids = explode(',' , $notification->event_id);
            if($event_id == $notification->event_id
                || $this->wildcard_match($notification->event_id, $event_id)
                || in_array($event_id, $event_ids)) {
                $notifications[] = $notification;
            }
        }
        if (count($notifications) == 0) return;
        foreach ($notifications as $notification) {
            $dbConn->query(
                "INSERT INTO "
                    ._NOTIF_EVENT_STACK_TABLE_NAME." ("
                        ."notification_sid, "
                        ."table_name, "
                        ."record_id, "
                        ."user_id, "
                        ."event_info, "
                        ."event_date"
                    .") "
                ."VALUES(?, "
                    ."?, "
                    ."?, "
                    ."?, "
                    ."?, "
                    .$dbConn->current_datetime()
                .")",
                array(
                    $notification->notification_sid,
                    $table_name,
                    $record_id,
                    $user,
                    $info
                )
            );
        }
    }
    
    public function commitEvent($eventId, $result) {
        $dbConn = new dbquery();
        $dbConn->connect();
        $query = "UPDATE " . _NOTIF_EVENT_STACK_TABLE_NAME 
            . " SET exec_date = ".$dbConn->current_datetime().", exec_result = '".$result."'" 
            . " WHERE event_stack_sid = ".$eventId;
        $dbConn->query($query);
    }
    
    
    
}


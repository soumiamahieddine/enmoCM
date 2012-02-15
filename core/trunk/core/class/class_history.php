<?php
/*
*   Copyright 2008-2012 Maarch
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
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief   Contains all the function to manage the history table
*
*<ul>
* <li>Connexion logs and events history management</li>
*</ul>
* @file
* @author Claire Figueras <dev@maarch.org>
* @author Cyril Vazquez <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/

/**
* @brief   Contains all the function to manage the history table
*
* @ingroup core
*/
class history extends dbquery
{
    /**
    * Inserts a record in the history table
    *
    * @param  $where  string Table or view of the event
    * @param  $id integer Identifier of the event to add
    * @param  $how string Event type (Keyword)
    * @param  $what string Event description
    * @param  $databasetype string Type of the database (MYSQL, POSTGRESQL, etc...)
    * @param  $id_module string Identifier of the module concerned 
    * by the event (admin by default)
    */
    public function add(
        $table_name, 
        $record_id, 
        $event_type, 
        $event_id, 
        $info, 
        $databasetype, 
        $id_module = 'admin', 
        $isTech = false, 
        $result = _OK, 
        $level = _LEVEL_INFO, 
        $user = ''
    )
    {
        $remote_ip = $_SERVER['REMOTE_ADDR'];
        $info = $this->protect_string_db($info, $databasetype);
        $user = '';
        if (isset($_SESSION['user']['UserId'])) {
            $user = $_SESSION['user']['UserId'];
        }
        if (!$isTech) {
            $this->connect();
            $this->query(
                "INSERT INTO ".$_SESSION['tablename']['history']
                    . " (table_name, record_id , event_type , event_id, user_id"
                    . " , event_date , " . "info , id_module, remote_ip) "
                    . "VALUES ('" . $table_name . "', '" . $record_id . "', '"
                    . $event_type . "', '" . $event_id . "','" . $user 
                    . "', " . $this->current_datetime() . ", '" . $info . "', '" 
                    . $id_module . "' , '" . $remote_ip . "')"
                , false
                , true
            );
            $this->disconnect();
        } else {
            //write on a log
            echo $info;exit;
        }
        
        // If module Notifications is loaded, check if event has 
        //as associated template and add event to stack for notification
        $core = new core_tools();
        if ($core->is_module_loaded("notifications")) {
            
            // Get template association id
            $this->connect();
            $query = "SELECT system_id FROM " 
                   . $_SESSION['tablename']['temp_templates_association'] 
                   . " WHERE upper(what) = 'EVENT' "
                   . " AND value_field = '" . $event_id . "'"
                   . " AND maarch_module = 'notifications'";
            $this->query($query);
                    
            if ($this->nb_result() > 0) {
                while ($ta = $this->fetch_object()) {
                    $query = "INSERT INTO " . $_SESSION['tablename']['notif_event_stack'] 
                            . " (ta_sid, table_name, record_id, user_id, event_info"
                            . ", event_date)" 
                            . " VALUES(" . $ta->system_id . ", '" 
                            . $table_name . "', '" . $record_id . "', '" 
                            . $user . "', '" . $info . "', " 
                            . $this->current_datetime() . ")";
                    $this->query($query, false, true);
                }
            }
            //$this->disconnect();
        }
        
    }

    /**
    * Gets the label of an history keyword
    *
    * @param  $id  string Key word identifier
    * @return  string Label of the key word or empty string
    */
    public function get_label_history_keyword($id)
    {
        if (empty($id)) {
            return '';
        } else {
            for ($i=0; $i<count($_SESSION['history_keywords']);$i++) {
                if ($id == $_SESSION['history_keywords'][$i]['id']) {
                    return $_SESSION['history_keywords'][$i]['label'];
                }
            }
        }
        return '';
    }
}

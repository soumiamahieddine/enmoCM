<?php

/*
*    Copyright 2008-2011 Maarch
*
*  This file is part of Maarch Framework.
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
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief  Contains the controler of life_cycle object (create, save, modify, etc...)
* 
* 
* @file
* @author Luc KEULEYAN - BULL
* @author Laurent Giovannoni
* @date $date$
* @version $Revision$
* @ingroup life_cycle
*/

// To activate de debug mode of the class
$_ENV['DEBUG'] = false;

// Loads the required class
try {
    require_once ("modules/notifications/class/templates_association.php");
    require_once ("modules/notifications/notifications_tables_definition.php");
    require_once ("core/class/ObjectControlerAbstract.php");
    require_once ("core/class/ObjectControlerIF.php");
    require_once ("core/class/class_history.php");
} catch (Exception $e) {
    echo $e->getMessage() . ' // ';
}

/**
* @brief  Controler of the templates_association_controler object 
*
*<ul>
*  <li>Get an lc_policies object from an id</li>
*  <li>Save in the database a templates_association_controler</li>
*  <li>Manage the operation on the templates_association_controler related tables in the database (insert, select, update, delete)</li>
*</ul>
* @ingroup templates
*/
class templates_association_controler extends ObjectControler implements ObjectControlerIF
{

    /**
    * Returns an templates_assoc object based on a templates_assoc identifier
    *
    * @param  $ta_sid string  templates_assoc identifier
    * @return templates_assoc object with properties from the database or null
    */
    public function get($template_id) {
        
        $this->set_specific_id('system_id');
        $template = $this->advanced_get($template_id, _TEMPLATES_ASSOCIATION_TABLE_NAME);
        
        if (get_class($template) <> "templates_association") {
            return null;
        } else {
            //var_dump($policy);
            return $template;
        }
    }

    
    /**
    * Deletes in the database (lc_policies related tables) a given lc_policies (policy_id)
    *
    * @param  $policy object  policy object
    * @return array true if the deletion is complete, false otherwise
    */
    public function delete($template_association) 
    {
        $control = array();
        if (!isset($template_association) || empty($template_association)) {
            $control = array('status' => 'ko',
                             'value'  => '',
                             'error'  => _EVENT_EMPTY
                       );
            return $control;
        }
        
        $this->set_specific_id('system_id');
        if ($this->advanced_delete($template_association) == true) {
            if (isset($params['log_event_del'])
                && ($params['log_event_del'] == "true"
                    || $params['log_event_del'] == true)) {
                $history = new history();
                $history->add(
                    TEMPLATES_ASSOCIATON, $template_association->system_id, 'DEL', 'eventdel',_EVENT_DELETED . ' : '
                    . $template_association->system_id, $params['databasetype']
                );
            }
            $control = array('status' => 'ok',
                             'value'  => $template_association->system_id
                      );
        } else {
            $control = array('status' => 'ko',
                             'value'  => $template_association->system_id,
                             'error'  => $error
                          );
        }
        return $control;
    }
    
    
    /**
    * Save given object in database:
    * - make an update if object already exists,
    * - make an insert if new object.
    * @param object $policy
    * @param string mode up or add
    * @return array
    */
    public function save($template_association, $mode = "") 
    {
        
        $control = array();
        $this->set_foolish_ids(
            array(
                'notification_id'
            )
        );
        // If template_association not defined or empty, return an error
        if (!isset($template_association) || empty($template_association)) {
            $control = array('status' => 'ko',
                             'value'  => '',
                             'error'  => _EVENT_EMPTY
                       );
            return $control;
        }



        // If mode not up or add, return an error
        if (!isset($mode) || empty($mode)
            || ($mode <> 'add' && $mode <> 'up' )) {
            $control = array('status' => 'ko',
                             'value'  => '',
                             'error'  => _MODE . ' ' ._UNKNOWN
                        );
            return $control;
        }
        
        //$template_association = $this->isAStatus($template_association);
        $this->set_specific_id('system_id');
        $template_association->what = 'event';

        // Data checks
        $control = $this->control($template_association, $mode, $params);
        
        if ($control['status'] == 'ok') {
            $core = new core_tools();
            $_SESSION['service_tag'] = 'event_' . $mode;
            $core->execute_modules_services(
                $params['modules_services'], 'event_add_db', 'include'
            );

            if ($mode == 'up') {
                //Update existing status
                if ($this->update($template_association)) {
                    $control = array('status' => 'ok',
                                     'value'  => $template_association->system_id
                               );
                    //log
                    if ($params['log_status_up'] == 'true') {
                        $history = new history();
                        $history->add(
                            NOTIFICATIONS_TABLE, $template_association->system_id, 'UP','eventup',
                            _EVENT_MODIFIED . ' : ' . $template_association->system_id,
                            $params['databasetype']
                        );
                    }
                } else {
                    $control = array('status' => 'ko',
                                     'value'  => '',
                                     'error'  => _PB_WITH_EVENT_UPDATE
                                );
                }
            } else { //mode == add
                if ($this->insert($template_association)) {
                    $control = array('status' => 'ok',
                                     'value'  => $template_association->system_id);
                    //log
                    if ($params['log_event_add'] == 'true') {
                        $history = new history();
                        $history->add(
                            NOTIFICATIONS_TABLE, $template_association->system_id, 'ADD','eventadd',
                            _EVENT_ADDED . ' : ' . $template_association->system_id,
                            $params['databasetype']
                        );
                    }
                } else {
                    $control = array('status' => 'ko',
                                     'value'  => '',
                                     'error'  => _PB_WITH_STATUS
                                );
                }
            }
        }
        unset($_SESSION['service_tag']);
        return $control;
    }
       

    /**
    * Control the data of Status object
    *
    * @param  $status template_association object
    * @param  $mode Mode (add or up)
    * @param  $params More parameters,
    *                 array('modules_services' => $_SESSION['modules_services']
    *                                               type array,
    *                     'log_status_up'      => 'true' / 'false': log status
    *                                               modification,
    *                     'log_status_add'     => 'true' / 'false': log status
    *                                               addition,
    *                     'databasetype'       => Type of the database
    *                )
    * @return array (  'status' => 'ok' / 'ko',
    *                  'value'  => template_association identifier or empty in case of error,
    *                  'error'  => Error message, defined only in case of error
    *                  )
    */
    private function control($template_association, $mode, $params=array())
    {
        $error = "";
        $f = new functions();
       
        $template_association->notification_id = $f->protect_string_db(
            $f->wash($template_association->notification_id, 'no', _DESC, 'yes', 0, 50)
        );
        $template_association->description = $f->protect_string_db(
            $f->wash($template_association->description, 'no', _DESC, 'yes', 0, 50)
        );
        $template_association->diffusion_type = $f->protect_string_db(
            $f->wash($template_association->diffusion_type, 'no', _DIFFUSION_TYPE)
        );
        $template_association->diffusion_properties = $f->protect_string_db(
            $f->wash($template_association->diffusion_properties, 'no', _DIFFUSION_PROPERTIES, 'no')
        );
        $template_association->attachfor_type = $f->protect_string_db(
            $f->wash($template_association->attachfor_type, 'no', _ATTACHFOR_TYPE, 'no')
        );
        $template_association->attachfor_properties = $f->protect_string_db(
            $f->wash($template_association->attachfor_properties, 'no', _ATTACHFOR_PROPERTIES, 'no')
        );
        $template_association->maarch_module = 'notifications';

        $_SESSION['service_tag'] = 'event_check';
        $core = new core_tools();
        //$core->execute_modules_services(
        //    $params['modules_services'], 'status_check', 'include'
        //);

        $error .= $_SESSION['error'];
        //TODO:rewrite wash to return errors without html and not in the session
        $error = str_replace("<br />", "#", $error);
        $return = array();
        if (!empty($error)) {
                $return = array('status' => 'ko',
                                'value'  => $template_association->system_id,
                                'error'  => $error
                          );
        } else {
            $return = array('status' => 'ok',
                            'value'  => $template_association->system_id
                      );
        }
        unset($_SESSION['service_tag']);
               
        return $return;
    }
    
    private function insert($template_association)
    {
        return $this->advanced_insert($template_association);
    }

    /**
    * Updates a status in the database (status table) with a Status object
    *
    * @param  $status Status object
    * @return bool true if the update is complete, false otherwise
    */
    private function update($template_association)
    {
       //var_dump($template_association); exit();
       return $this->advanced_update($template_association);
    }
    
    
}

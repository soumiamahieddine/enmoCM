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
	//require_once 'modules/notifications/class/event';
	require_once 'modules/templates/class/templates_association.php';
	require_once 'modules/notifications/notifications_tables_definition.php';
    require_once 'core/class/class_request.php';  
    require_once 'core/class/ObjectControlerAbstract.php';
} catch (Exception $e) {
    echo $e->getMessage() . ' // ';
}

/**
 * Class for controling docservers objects from database
 */
class event_controler
    extends ObjectControler
{
    /**
     * Get event with given event_id.
     * Can return null if no corresponding object.
     * @param $id Id of event to get
     * @return event
     */
    public function get($event_id)
    {
		
        if (empty($event_id)) {
            return null;
        }

        self::set_specific_id('system_id');
      
        $event = self::advanced_get($event_id, _TEMPLATES_ASSOCIATION_TABLE_NAME);

        if (isset($event)) {
            return $event;
        } else {
            return null;
        }
    }
  

}


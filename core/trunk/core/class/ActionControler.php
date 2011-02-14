<?php
/*
*    Copyright 2008,2009,2010 Maarch
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
* @brief  Contains the controler of the Action Object (create, save, modify, etc...)
*
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/

// Loads the required class
try {
	require_once('core/class/class_db.php');
	require_once('core/class/Action.php');
	require_once('core/core_tables.php');
 // require_once('core/class/ObjectControlerIF.php');
    require_once('core/class/ObjectControlerAbstract.php');
    require_once('core/class/class_history.php');
} catch (Exception $e) {
	echo $e->getMessage() . ' // ';
}

/**
* @brief  Controler of the Action Object
*
*<ul>
*  <li>Get an action object from an id</li>
*  <li>Save in the database an action</li>
*  <li>Manage the operation on the action related tables in the database (insert, select, update, delete)</li>
*</ul>
* @ingroup core
*/
class ActionControler
{
	/**
	* Dbquery object used to connnect to the database
    */
	private static $db;

	/**
	* Actions table
    */
	public static $actions_table ;

	/**
	* Actions_groupbaskets_table table
    */
	public static $actions_groupbaskets_table ;

	/**
	* Opens a database connexion and values the tables variables
	*/
	public function connect()
	{
		$db = new dbquery();
		$db->connect();
		self::$actions_table = $_SESSION['tablename']['actions'];

		self::$db=$db;
	}

	/**
	* Close the database connexion
	*/
	public function disconnect()
	{
		self::$db->disconnect();
	}

	/**
	* Returns an Action Object based on a action identifier
	*
	* @param  $action_id string  Action identifier
	* @return Action object with properties from the database or null
	*/
	public function get($action_id)
	{
		if(empty($action_id))
			return null;

		self::connect();
		$query = "select * from ".self::$actions_table." where id = ".$action_id;

		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
		echo _NO_ACTION_WITH_ID.' '.$action_id.' // ';
		}

		if(self::$db->nb_result() > 0)
		{
			$action = new Action();
			$queryResult=self::$db->fetch_object();
			foreach($queryResult as $key => $value){
				$action->$key=$value;
			}
			self::disconnect();
			return $action;
		}
		else
		{
			self::disconnect();
			return null;
		}
	}


	/**
	* Saves in the database an Action object
	*
	* @param  $group Action object to be saved
	* @param  $mode string  Saving mode : add or up
	* @return bool true if the save is complete, false otherwise
	*/
	public function save($action, $mode)
	{
		if(!isset($action) )
			return false;

		if($mode == "up")
			return self::update($action);
		elseif($mode =="add")
			return self::insert($action);

		return false;
	}

	/**
	* Inserts in the database (actions table) an Action object
	*
	* @param  $action Action object
	* @return bool true if the insertion is complete, false otherwise
	*/
	private function insert($action)
	{
		if(!isset($action) )
			return false;

		self::connect();
		$prep_query = self::insert_prepare($action);

		$query="insert into ".self::$actions_table." ("
					.$prep_query['COLUMNS']
					.") values("
					.$prep_query['VALUES']
					.")";
		try{
			if($_ENV['DEBUG']){ echo $query.' // '; }
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_INSERT_ACTION." ".$action->toString().' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}

	/**
	* Updates a action in the database (action table) with a Action object
	*
	* @param  $action Action object
	* @return bool true if the update is complete, false otherwise
	*/
	private function update($action)
	{
		if(!isset($action) )
			return false;

		self::connect();
		$query="update ".self::$actions_table." set "
					.self::update_prepare($action)
					." where id=".$action->id;

		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_UPDATE_ACTION." ".$action->toString().' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}

	/**
	* Deletes in the database (actions table) a given action (action_id)
	*
	* @param  $action_id string  Action identifier
	* @return bool true if the deletion is complete, false otherwise
	*/
	public function delete($action_id)
	{
		if(!isset($action_id)|| empty($action_id) )
			return false;
		if(! self::actionExists($action_id))
			return false;

		self::connect();
		$query="delete from ".self::$actions_table." where id=".$action_id;

		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE_ACTION_ID." ".$action_id.' // ';
			$ok = false;
		}
		if($ok)
			self::cleanActionsGroupbasket($action_id);

		self::disconnect();

		return $ok;
	}

	/**
	* Cleans the actions_groupbasket table in the database from a given action (action_id)
	*
	* @param  $action_id string  Action identifier
	* @return bool true if the cleaning is complete, false otherwise
	*/
	public function cleanActionsGroupbasket($action_id)
	{
		if(!isset($action_id)|| empty($action_id) )
			return false;

		self::connect();
		$query="delete from ".self::$actions_groupbaskets_table."  where id_action=".$action_id;
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE_ACTION_ID." ".$action_id.' // ';
			$ok = false;
		}

		self::disconnect();
		return $ok;
	}

	/**
	* Asserts if a given action (action_id) exists in the database
	*
	* @param  $action_id String Action identifier
	* @return bool true if the action exists, false otherwise
	*/
	public function actionExists($action_id)
	{
		if(!isset($action_id) || empty($action_id))
			return false;

		self::connect();
		$query = "select id from ".self::$actions_table." where id = ".$action_id;

		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _UNKNOWN.' '._ACTION." ".$action_id.' // ';
		}

		if(self::$db->nb_result() > 0)
		{
			self::disconnect();
			return true;
		}
		self::disconnect();
		return false;
	}

	/**
	* Prepares the update query for a given Action object
	*
	* @param  $action Action object
	* @return String containing the fields and the values
	*/
	private function update_prepare($action)
	{
		$result=array();
		foreach($action->getArray() as $key => $value)
		{
			// For now all fields in the action table are strings or dates
			if(!empty($value))
			{
				$result[]=$key."='".functions::protect_string_db($value)."'";
			}
		}
		// Return created string minus last ", "
		return implode(",",$result);
	}

	/**
	* Prepares the insert query for a given Action object
	*
	* @param  $action Action object
	* @return Array containing the fields and the values
	*/
	private function insert_prepare($action)
	{
		$columns=array();
		$values=array();
		foreach($action->getArray() as $key => $value)
		{
			//For now all fields in the actions table are strings or dates
			if(!empty($value))
			{
				$columns[]=$key;
				$values[]="'".functions::protect_string_db($value)."'";
			}
		}
		return array('COLUMNS' => implode(",",$columns), 'VALUES' => implode(",",$values));
	}

}
?>

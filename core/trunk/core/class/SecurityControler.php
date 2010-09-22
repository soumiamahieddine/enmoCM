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
* @brief  Contains the controler of the Security Object 
* 
* 
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/


// To activate de debug mode of the class
$_ENV['DEBUG'] = false;
/*
define("_CODE_SEPARATOR","/");
define("_CODE_INCREMENT",1);
*/

// Loads the required class
try {
	require_once("core/core_tables.php");
	require_once("core/class/class_db.php");
	require_once("core/class/users_controler.php");
	require_once("core/class/session_security_controler.php");
	require_once("core/class/Security.php");
	if(!defined("_CLASSIFICATION_SCHEME_VIEW")) define("_CLASSIFICATION_SCHEME_VIEW","mr_classification_scheme_view");
//	require_once("apps/".$_SESSION['businessapps'][0]['appid']."/security_bitmask.php"); must be called in the controler
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

/**
* @brief  Controler of the Security Object 
*
*<ul>
*  <li>Get an security object from an id</li>
*  <li>Save in the database a security</li>
*  <li>Manage the operation on the security table in the database (insert, select, update, delete)</li>
*</ul>
* @ingroup core
*/
class SecurityControler
{
	/**
	* Dbquery object used to connnect to the database
    */
	private static $db;
	
	/**
	* Security table
    */
	private static $security_table;
	
	
	/**
	* Opens a database connexion and values the tables variables
	*/
	public function connect()
	{
		$db = new dbquery();
		$db->connect();
		
		self::$security_table = SECURITY_TABLE;
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
	* Returns an Security Object based on a security identifier
	*
	* @param  $security_id string  Security identifier
	* @return Security object with properties from the database or null
	*/
	public function get($security_id)
	{
		if(empty($security_id))
			return null;

		self::connect();

		$query = "select * from ".self::$security_table." where security_id = ".$security_id;
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _NO_ACCESS_WITH_ID.' '.$security_id.' // ';
		}
		
		if(self::$db->nb_result() > 0)
		{
			$access=new SecurityObj();
			$queryResult=self::$db->fetch_object();  
			foreach($queryResult as $key => $value){
				$access->$key=$value;
			}
			self::disconnect();
			return $access;
		}
		else
		{
			self::disconnect();
			return null;
		}
	}
	
	/**
	* Returns all security object for a given usergroup
	*
	* @param  $group_id string  Usergroup identifier
	* @return Array of security objects or null
	*/
	public function getAccessForGroup($group_id)
	{
		if(empty($group_id))
			return null;
			
		self::connect();
		// Querying database
		$query = "select * from ".self::$security_table." where group_id = '".$group_id."'";
		
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _NO_GROUP_WITH_ID.' '.$group_id.' // ';
		}
		
		$security = array();
		if(self::$db->nb_result() > 0)
		{
			while($queryResult = self::$db->fetch_object())
			{
				$access=new SecurityObj();
				foreach($queryResult as $key => $value){
					$access->$key=$value;
				}
				array_push($security, $access);
			}
		}
		self::disconnect();
		return $security;
	}
	
	/**
	* Saves in the database a security object 
	*
	* @param  $security Security object to be saved
	* @param  $mode string  Saving mode : add or up (add by default)
	* @return bool true if the save is complete, false otherwise
	*/
	public function save($security, $mode="add")
	{
		if(!isset($security))
			return false;
			
		if($mode == "up")
			return self::update($security); 
		elseif($mode == "add") 
			return self::insert($security);
			
		return false;
	}
	
	/**
	* Inserts in the database (security table) a Security object
	*
	* @param  $security Security object
	* @return bool true if the insertion is complete, false otherwise
	*/
	private function insert($security)
	{
		if(!isset($security))
			return false;
			
		self::connect();
		$prep_query = self::insert_prepare($security);

		$query="insert into ".self::$security_table." ("
					.$prep_query['COLUMNS']
					.") values("
					.$prep_query['VALUES']
					.")";
		try{
			if($_ENV['DEBUG']){ echo $query.' // '; }
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_INSERT_ACCESS." ".$security->toString().' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	/**
	* Updates a security in the database (security table) with a Security object
	*
	* @param  $security Security object
	* @return bool true if the update is complete, false otherwise
	*/
	private function update($security)
	{
		if(!isset($security))
			return false;
			
		self::connect();
		$query="update ".self::$security_table." set "
					.self::update_prepare($security)
					." where security_id=".$security->security_id; 
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_UPDATE_ACCESS." ".$security->toString().' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	/**
	* Deletes in the database (security table) a given security
	*
	* @param  $security_id string  Security identifier
	* @return bool true if the deletion is complete, false otherwise
	*/
	public function delete($security_id)
	{
		if(!isset($security_id)|| empty($security_id) )
			return false;
			
		self::connect();
		$query="delete from ".self::$security_table." where security_id=".$security_id;
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE_SECURITY_ID." ".$security_id.' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	/**
	* Deletes in the database (security table) all security of a given usergroup
	* 
	* @param  $group_id string  Usergroup identifier
	* @return bool true if the deletion is complete, false otherwise
	*/
	public function deleteForGroup($group_id)
	{
		if(!isset($group_id)|| empty($group_id) )
			return false;
			
		self::connect();
		$query="delete from ".self::$security_table." where group_id='".$group_id."'";
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE.' '._GROUP_ID." ".$group_id.' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	/**
	* Prepares the update query for a given Security object
	*
	* @param  $security Security object
	* @return String containing the fields and the values 
	*/
	private function update_prepare($security)
	{
		$result=array();
		foreach($security->getArray() as $key => $value)
		{
			// For now all fields in the usergroups table are strings or date excepts the security_id
			if(!empty($value))
			{
				if($key <> 'security_id')
					$result[]=$key."='".$value."'";
			}
		}
		// Return created string minus last ", "
		return implode(",",$result);
	} 
	
	/**
	* Prepares the insert query for a given Security object
	*
	* @param  $security Security object
	* @return Array containing the fields and the values 
	*/
	private function insert_prepare($security)
	{
		$columns=array();
		$values=array();
		foreach($security->getArray() as $key => $value)
		{
			// For now all fields in the usergroups table are strings or date excepts the security_id
			if(!empty($value))
			{
				if($key <> 'security_id')
				{
					$columns[]=$key;
					$values[]="'".$value."'";
				}
			}
		}
		return array('COLUMNS' => implode(",",$columns), 'VALUES' => implode(",",$values));
	}
	
	// TO DO : USE TO CHECK WHERE CLAUSE
	public function check_where_clause($coll_id, $target, $where_clause, $view, $user_id)
	{
		$res = array('RESULT' => false, 'TXT' => '');
		
		if(empty($coll_id) || empty($target) || empty($where))
		{
			$res['TXT'] = _ERROR_PARAMETERS_FUNCTION;
			return $res;
		}
		
		$where = " ".$where_clause;
		$where = str_replace("\\", "", $where);
		$where = self::process_security_where_clause($where, $user_id);
		if(str_replace(" ", "", $where) == "")
		{
			$where = "";
		}
		$where = str_replace("where", " ", $where);
		self::connect();
		
		if($target == 'ALL' || $target == 'DOC')
			$query = "select res_id from ".$view." where  ".$where;
		if($target == 'ALL' || $target == 'CLASS')
			$query = "select mr_aggregation_id from ".$view." where  ".$where;

		$ok = self::$db->query($query, true);
		if(!$ok )
		{
			$res['TXT'] = _SYNTAX_ERROR_WHERE_CLAUSE;
			return $res;
		}
		else
		{
			$res['TXT'] = _SYNTAX_OK;
			$res['RESULT'] = true;
		}
		self::disconnect();
		return $res;
	}
	
	/**
	* Process a where clause, using the process_where_clause methods of the modules, the core and the apps
	*
	* @param  $where_clause string Where clause to process
	* @param  $user_id string User identifier
	* @return string Proper where clause
	*/
	public function process_security_where_clause($where_clause, $user_id)
	{
		if(!empty($where_clause))
		{
			$where = ' where '.$where_clause;

			// Process with the core vars
			$where = self::process_where_clause($where, $user_id);
	
			// Process with the modules vars
			foreach(array_keys($_SESSION['modules_loaded']) as $key)
			{
				$path_module_tools = $_SESSION['modules_loaded'][$key]['path']."class".DIRECTORY_SEPARATOR."class_modules_tools.php";
				require_once($path_module_tools);
				$object = new $key;
				if(method_exists($object, 'process_where_clause'))
				{
					$where = $object->process_where_clause($where, $user_id);
				}
			}
			$where = preg_replace('/, ,/', ',', $where);
			$where = preg_replace('/\( ?,/', '(', $where);
			$where = preg_replace('/, ?\)/', ')', $where);

			// Process with the apps vars
			require_once('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_business_app_tools.php');
			$object = new business_app_tools();
			if(method_exists($object, 'process_where_clause'))
			{
				$where = $object->process_where_clause($where, $user_id);
			}
			return $where;
		}
		else
		{
			return '';
		}
	}

	/**
	* Process a where clause with the core specific vars
	*
	* @param  $where_clause string Where clause to process
	* @param  $user_id string User identifier
	* @return string Proper where clause
	*/
	public function process_where_clause($where_clause, $user_id)
	{
		$where = $where_clause;
		if(preg_match('/@user/', $where_clause))
		{
			$where = str_replace("@user","'".trim($user_id)."'", $where_clause);
		}
		return $where;
	}
	
	/**
	* Loads into session, the security parameters corresponding to the user groups.
	*
	* @param  $user_id string User Identifier
	*/
	public function load_security($user_id)
	{
		$tab['collections'] = array();
		$tab['security'] = array();

		self::connect();

		if($user_id == "superadmin")
		{
			for($i=0; $i<count($_SESSION['collections']);$i++)
			{
				$tab['security'][ $_SESSION['collections'][$i]['id']] = array();
				foreach(array_keys($_ENV['targets']) as $key)
				{
					$tab['security'][ $_SESSION['collections'][$i]['id']][$key] = array('table'  => $_SESSION['collections'][$i]['table'], 'label_coll'  => $_SESSION['collections'][$i]['label'],'view'  => $_SESSION['collections'][$i]['view'], 'where' =>" (1=1) ");			
				}
				array_push($tab['collections'], $_SESSION['collections'][$i]['id']);
			}
		}
		else
		{
			$groups = users_controler::getGroups($user_id);

			$access = array();
			for($i=0; $i<count($groups); $i++)
			{
				$tmp = self::getAccessForGroup($groups[$i]['GROUP_ID']);
				for($j=0; $j<count($tmp);$j++)
				{
					array_push($access, $tmp[$j]);
				}
			}
			for($i=0; $i<count($access); $i++)
			{
				// TO DO : vérifier les dates
				$start_date = $access[$i]->__get('mr_start_date');
				$stop_date = $access[$i]->__get('mr_stop_date');
				
				$target = $access[$i]->__get('where_target');
				$coll_id = $access[$i]->__get('coll_id');
				$where_clause = $access[$i]->__get('where_clause');
				$where_clause = self::process_security_where_clause($where_clause, $user_id);
				$where_clause = str_replace('where', '', $where_clause);
				
				$ind = self::get_ind_collection($coll_id);
				
				if(trim($where_clause) == "")
					$where = "-1";
				else
					$where =  "( ".$this->show_string($where_clause)." )";
					
				if( ! in_array($coll_id, $tab['collections'] ) )
				{
					$tab['security'][$coll_id] = array();
					
					if($target == 'ALL')
					{
						foreach(array_keys($_ENV['targets']) as $key)
						{
							$tab['security'][$coll_id][$key] = array('table'  => $_SESSION['collections'][$ind]['table'], 'label_coll'  => $_SESSION['collections'][$ind]['label'],'view'  => $_SESSION['collections'][$ind]['view'], 'where'  => $where);	
						}
					}
					else
					{
						$tab['security'][$coll_id][$target] = array('table'  => $_SESSION['collections'][$ind]['table'], 'label_coll'  => $_SESSION['collections'][$ind]['label'],'view'  => $_SESSION['collections'][$ind]['view'], 'where'  => $where);	
					}
					array_push($tab['collections'] ,$coll_id);	
				}
				else
				{
					if(isset($tab['security'][$coll_id][$target]) && count($tab['security'][$coll_id][$target]) > 0)
						$tab['security'][ $coll_id][$target]['where'] .= " or ".$where;
					elseif($target == 'ALL')
					{
						foreach(array_keys($_ENV['targets']) as $key)
						{
							if(isset($tab['security'][$coll_id][$key]) && count($tab['security'][$coll_id][$key]) > 0)
								$tab['security'][$coll_id][$key]['where'] .= " or ".$where;
							else
								$tab['security'][$coll_id][$key] = array('table'  => $_SESSION['collections'][$ind]['table'], 'label_coll'  => $_SESSION['collections'][$ind]['label'],'view'  => $_SESSION['collections'][$ind]['view'], 'where'  => $where);
						}
					}
					else
					{
						$tab['security'][$coll_id][$target] = array('table'  => $_SESSION['collections'][$ind]['table'], 'label_coll'  => $_SESSION['collections'][$ind]['label'],'view'  => $_SESSION['collections'][$ind]['view'], 'where'  => $where);	
					}
				}
			}
		}
		return $tab;
	}

	/**
	* Gets the indice of the collection in the  $_SESSION['collections'] array
	*
	* @param  $coll_id string  Collection identifier
	* @return integer Indice of the collection in the $_SESSION['collections'] or -1 if not found
	*/
	public function get_ind_collection($coll_id)
	{
		for($i=0;$i< count($_SESSION['collections']); $i++)
		{
			if(trim($_SESSION['collections'][$i]['id']) == trim($coll_id))
			{
				return $i;
			}
		}
		return -1;
	}


/**
	 * Give action bitmask for given $user_id over given
	 * object
	 * @param varchar(32) $user_id
	 * @param bigint $object_id
	 * @return bitmask
	 */
	public function getActions($user_id,$object_id, $object_type = 'aggregation')
	{
		// Select from security session table	
		$session_sec = session_security_controler::get($user_id);
		if($session_sec->__get('last_object_id') == $object_id)
			return $session_sec->__get('last_available_bitmask');
		else
			return setActions($user_id,$object_id, $object_type);
		/********
		 * FAKE *
		 ********/
		//return ADD_RECORD+CREATE_CLASS+CREATE_OTHER_AGREGATION+DATA_MODIFICATION+DELETE_CLASS+DELETE_OTHER_AGREGATION;
	}
	
	/**
	 * Update security session table with
	 * bitmask, according with given user 
	 * and aggregation.
	 * Return computed bitmask
	 * @param varchar(32) $user_id
	 * @param bigint $object_id
	 * @return bitmask
	 */
	public function setActions($user_id,$object_id, $object_type)
	{

		if($user_id == 'superadmin')
		{
			return MAX_BITMASK;
		}
		// Compute action bitmask 
		$full_bitmask = 0;
		$groups = users_controler::getGroups($user_id);
		//print_r($groups);
		
		$full_where = "";
		for($i=0; $i<count($groups); $i++)
		{
			$access = self::getAccessForGroup($groups[$i]['GROUP_ID']);
			//var_dump($access);
			for($j=0; $j<count($access);$j++)
			{			
				$target = $access[$j]->__get('where_target');
				$coll_id = $access[$j]->__get('coll_id');
				$where_clause = $access[$j]->__get('where_clause');
				$where_clause = self::process_security_where_clause($where_clause, $user_id);
				$where_clause = str_replace('where', '', $where_clause);
				$bitmask = $access[$j]->__get('rights_bitmask');
				
				$ind = self::get_ind_collection($coll_id);
				if(trim($where_clause) == "")
					$where = "-1";
				else
					$where =  "( ".$this->show_string($where_clause)." )";
				
				//echo 'target : '.$target.', coll_id : '.$coll_id.', where : '.$where.', bitmask : '.decbin($bitmask).'';
				$query = '';
				if($object_type == 'aggregation' && ($target == 'CLASS' || $target == 'ALL'))
				{
					$query = "select mr_aggregation_id from "._CLASSIFICATION_SCHEME_VIEW." where (".$where.') and mr_aggregation_id = '.$object_id;
				}
				elseif($object_type == 'classification_scheme' && ($target == 'CLASS' || $target == 'ALL'))
				{
					$query = "select mr_classification_scheme_id from "._CLASSIFICATION_SCHEME_VIEW." where (".$where.') and mr_classification_scheme_id = '.$object_id;
				}			
				else if($object_type == 'doc' && ($target == 'DOC' || $target == 'ALL'))
				{
					$query = "select res_id from ".$_SESSION['collections'][$ind]['view']." where (".$where.') and res_id = '.$object_id;
				}
				//echo $query;
				self::connect();
				if(!empty($query))
					self::$db->query($query);
					
				if(self::$db->nb_result() > 0)
				{
					if($bitmask > 0)
					{
						$full_bitmask = set_right($full_bitmask, $bitmask);
					}
					
					if(!empty($full_where))
						$full_where .= " and (".$where.") ";
					else
						$full_where .= $where;
				}
				self::disconnect();
			}
		}
		
		// Update security session table
		$session_security = new session_security();
		$session_security->setArray(array('user_id' => functions::protect_string_db($user_id), 'session_begin_date' => date("Y-m-d H:i"), 'full_where_clause' => functions::protect_string_db($full_where), 'last_available_bitmask' => $full_bitmask, 'last_object_id' => functions::protect_string_db($object_id))); // TO DO : calculate the session_end_date
		
		session_security_controler::save($session_security);
		
		return $full_bitmask;
	}
}
?>

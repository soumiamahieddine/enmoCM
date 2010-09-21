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
* @brief  Contains the docserver Object (herits of the BaseObject class)
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
/*
define("_CODE_SEPARATOR","/");
define("_CODE_INCREMENT",1);
*/

// Loads the required class
try {
	require_once("modules/life_cycle/class/docservers.php");
	require_once ("modules/life_cycle/life_cycle_tables_definition.php");
	require_once ("core/class/ObjectControlerAbstract.php");
	require_once ("core/class/ObjectControlerIF.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

define ("_DEBUG", false);
define ("_ADVANCED_DEBUG",false);

/**
 * Class for controling docservers objects from database
 */
class docservers_controler extends ObjectControler implements ObjectControlerIF {
	
	/**
	 * Save given object in database: 
	 * - make an update if object already exists,
	 * - make an insert if new object.
	 * Return updated object.
	 * @param docservers $docservers
	 * @return boolean
	 */
	public function save($docserver){
		if (!isset ($docserver))
			return false;

		self :: set_foolish_ids(array('docserver_id'));
		self :: set_specific_id('docserver_id');
		if(self::docserversExists($docserver->docserver_id)){
			// Update existing docservers
			return self::update($docserver);
		} else {
			// Insert new docservers
			return self::insert($docserver);
		}
	}

	/**
	* Inserts in the database (docservers table) a docserver object
	*
	* @param  $docserver docserver object
	* @return bool true if the insertion is complete, false otherwise
	*/
	private function insert($docserver){
		// Giving automatised values
		$docserver->enabled="Y";
		$docserver->creation_date=request::current_datetime();
		
		// Inserting object
		$result = self::advanced_insert($docserver);
		return $result;
	}
	
	/**
	* Updates in the database (docserver table) a docservers object
	*
	* @param  $docserver docserver object
	* @return bool true if the update is complete, false otherwise
	*/
	private function update($docserver) {
		return self::advanced_update($docserver);
	}


///////////////////////////////////////////////    GET BLOCK
	
	/**
	 * Get docservers with given id.
	 * Can return null if no corresponding object.
	 * @param $id Id of docservers to get
	 * @return docservers 
	 */
	public function get($id) {
		return self::advanced_get($id,_DOCSERVERS_TABLE_NAME);
	}

///////////////////////////////////////////////////// DELETE BLOCK
	/**
	 * Delete given docserver from database.
	 * @param docservers $docservers
	 */
	public function delete($docserver){
		// Deletion of given docservers
		$result = self::advanced_delete($docserver);
		return $result;
	}

/**
	* Disables a given docservers
	* 
	* @param  $docserver docservers object 
	* @return bool true if the disabling is complete, false otherwise 
	*/
	public function disable($docserver) {
		self :: set_foolish_ids(array('docserver_id'));
		self::set_specific_id('docserver_id');
		return self::advanced_disable($docserver_location);
	}

/**
	* Enables a given docserver
	* 
	* @param  $docserver docservers object  
	* @return bool true if the enabling is complete, false otherwise 
	*/
	public function enable($docserver) {
		self :: set_foolish_ids(array('docserver_id'));
		self::set_specific_id('docserver_id');
		return self::advanced_enable($docserver);
	}

//////////////////////////////////////////////   OTHER PRIVATE BLOCK
	public function docserversExists($docserver_id){
		if(!isset($docserver_id) || empty($docserver_id))
			return false;
		self::$db=new dbquery();
		self::$db->connect();
		
		//LKE = BULL ===== SPEC FONC : ==== Cycles de vie : docservers (ID1)
		// Ajout du contrôle pour vérifier l'existence de la combinaison "docserver_id"
		$query = "select docserver_id from "._DOCSERVERS_TABLE_NAME." where docserver_id = '".$docserver_id."'";
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _UNKNOWN._DOCSERVER." ".$docserver_id.' // ';
		}
		
		if(self::$db->nb_result() > 0){
			self::$db->disconnect();
			return true;
		}
		self::$db->disconnect();
		return false;
	}
}

?>

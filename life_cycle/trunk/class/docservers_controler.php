<?php

try {
	require_once("modules/life_cycle/class/ObjectControlerIF.php");
	require_once("modules/life_cycle/class/ClassifiedObjectControlerAbstract.php");
	require_once("modules/life_cycle/class/docservers.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

define ("_DEBUG", false);
define ("_ADVANCED_DEBUG",false);

/**
 * Class for controling docservers objects from database
 * data, and vice-versa.
 * @author boulio
 *
 */
class docservers_controler extends ClassifiedObjectControler implements ObjectControlerIF {
	
	/**
	 * Save given object in database: 
	 * - make an update if object already exists,
	 * - make an insert if new object.
	 * Return updated object.
	 * @param docservers $docservers
	 * @return boolean
	 */
	public function save($docservers){
		if(self::docserversExists($docservers->docserver_id)){
			// Update existing docservers
			return self::update($docservers);
		} else {
			// Insert new docservers
			return self::insert($docservers);
		}
	}

///////////////////////////////////////////////////////   INSERT BLOCK	
	/**
	 * Add given docservers to database.
	 * @param docservers $docservers
	 */
	private function insert($docservers){
		// Giving automatised values
		$docservers->enabled="Y";
		$docservers->creation_date=request::current_datetime();
		
		// Inserting object
		$result = self::advanced_insert($docservers);
		return $result;
	}

///////////////////////////////////////////////////////   UPDATE BLOCK
	/**
	 * Update given docservers informations in database.
	 * @param docservers $docservers
	 */
	private function update($docservers){
		// Updating automatised values of given object
		
		// Update given docservers in database
		$result = self::advanced_update($docservers);
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
	 * Delete given docservers from database.
	 * @param docservers $docservers
	 */
	public function delete($docservers){
		// Deletion of given docservers
		$result = self::advanced_delete($docservers);
		return $result;
	}

///////////////////////////////////////////////////// DISABLE BLOCK
	/**
	 * Disable given docservers from database.
	 * @param docservers $docservers
	 */
	public function disable($docservers){
		// Disable of given docservers
		$result = self::advanced_disable($docservers);
		return $result;
	}

///////////////////////////////////////////////////// ENABLE BLOCK
	/**
	 * Disable given docservers from database.
	 * @param docservers $docservers
	 */
	public function enable($docservers){
		// Disable of given docservers
		$result = self::advanced_enable($docservers);
		return $result;
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

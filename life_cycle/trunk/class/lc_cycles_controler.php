<?php

try {
	require_once("modules/life_cycle/class/ObjectControlerIF.php");
	require_once("modules/life_cycle/class/ClassifiedObjectControlerAbstract.php");
	require_once("modules/life_cycle/class/lc_cycles.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

define ("_DEBUG", false);
define ("_ADVANCED_DEBUG",false);

/**
 * Class for controling lc_cycles objects from database
 * data, and vice-versa.
 * @author boulio
 *
 */
class lc_cycles_controler extends ClassifiedObjectControler implements ObjectControlerIF {
	
	/**
	 * Save given object in database: 
	 * - make an update if object already exists,
	 * - make an insert if new object.
	 * Return updated object.
	 * @param lc_cycles $lc_cycles
	 * @return boolean
	 */
	public function save($lc_cycles){

	if(self::cyclesExists($lc_cycles->cycle_id,$lc_cycles->policy_id)){
			// Update existing lc_cycles
			return self::update($lc_cycles);
		} else {
			// Insert new lc_cycles
			return self::insert($lc_cycles);
		}
	}

///////////////////////////////////////////////////////   INSERT BLOCK	
	/**
	 * Add given lc_cycles to database.
	 * @param lc_cycles $lc_cycles
	 */
	private function insert($lc_cycles){
		// Giving automatised values
		
		// Inserting object
		$result = self::advanced_insert($lc_cycles);
		return $result;
	}

///////////////////////////////////////////////////////   UPDATE BLOCK
	/**
	 * Update given lc_cycles informations in database.
	 * @param lc_cycles $lc_cycles
	 */
	private function update($lc_cycles){
		// Updating automatised values of given object
		
		// Update given lc_cycles in database
		$result = self::advanced_update($lc_cycles);
	}

///////////////////////////////////////////////    GET BLOCK
	
	/**
	 * Get lc_cycles with given id.
	 * Can return null if no corresponding object.
	 * @param $id Id of lc_cycles to get
	 * @return lc_cycles 
	 */
	public function get($id) {
		return self::advanced_get($id,_LC_CYCLES_TABLE_NAME);
	}

///////////////////////////////////////////////////// DELETE BLOCK
	/**
	 * Delete given lc_cycles from database.
	 * @param lc_cycles $lc_cycles
	 */
	public function delete($lc_cycles){
	
		if(self::RattachementExists($lc_cycles->cycle_id)){
			// Deletion of given lc_cycles IMPOSSIBLE
			echo "ERROR DELETE";
		} else {
			// Deletion of given lc_cycles
			$result = self::advanced_delete($lc_cycles);
		}
		return $result;
	}

///////////////////////////////////////////////////// DISABLE BLOCK
	/**
	 * Disable given lc_cycles from database.
	 * @param lc_cycles $lc_cycles
	 */
	public function disable($lc_cycles){
		// Disable of given lc_cycles
		$result = self::advanced_disable($lc_cycles);
		return $result;
	}

///////////////////////////////////////////////////// ENABLE BLOCK
	/**
	 * Disable given lc_cycles from database.
	 * @param lc_cycles $lc_cycles
	 */
	public function enable($lc_cycles){
		// Disable of given lc_cycles
		$result = self::advanced_enable($lc_cycles);
		return $result;
	}

//////////////////////////////////////////////   OTHER PRIVATE BLOCK
	public function cyclesExists($cycle_id, $policy_id){
		if(!isset($cycle_id) || empty($cycle_id))
			return false;
			
		if(!isset($policy_id) || empty($policy_id))
			return false;
			
		self::$db=new dbquery();
		self::$db->connect();
		
		//LKE = BULL ===== SPEC FONC : ==== Cycles de vie : lc_cycles (ID1)
		// Ajout du contr�le pour v�rifier l'existence de la combinaison "cycle_id" & "policy_id"
		$query = "select cycle_id from "._LC_CYCLES_TABLE_NAME." where cycle_id = '".$cycle_id."' and policy_id = '".$policy_id."'";
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _UNKNOWN._DOCSERVER." ".$cycle_id.' // ';
		}
		
		if(self::$db->nb_result() > 0){
			self::$db->disconnect();
			return true;
		}
		self::$db->disconnect();
		return false;
	}
	
	
	public function RattachementExists($cycle_id){
		if(!isset($cycle_id) || empty($cycle_id))
			return false;
		self::$db=new dbquery();
		self::$db->connect();
		
		//LKE = BULL ===== SPEC FONC : ==== Cycles de vie : lc_cycles (ID2)
		// lors de la suppression d'un cycle d'archivage, v�rifier que ce dernier n'ait pas d'objets rattach�s (lc_stack) 
		//$query1 = "select cycle_id from "._LC_STACK_TABLE_NAME." where cycle_id = '".$cycle_id."'";
		
		// lors de la suppression d'un cycle d'archivage, v�rifier que ce dernier n'ait pas d'objets rattach�s (lc_cycle_steps) 
		$query2 = "select cycle_id from "._LC_CYCLE_STEPS_TABLE_NAME." where cycle_id = '".$cycle_id."'";

		// lors de la suppression d'un cycle d'archivage, v�rifier que ce dernier n'ait pas d'objets rattach�s (res_x) 
		//$query3 = "select cycle_id from "._LC_RES_X_TABLE_NAME." where cycle_id = '".$cycle_id."'";	

		// lors de la suppression d'un cycle d'archivage, v�rifier que ce dernier n'ait pas d'objets rattach�s (adr_x) 
		//$query4 = "select cycle_id from "._LC_ADR_X_TABLE_NAME." where cycle_id = '".$cycle_id."'";		
		
		try{
			if (self::$db->query($query2)) 
			{
				/*if (!self::$db->query($query2)) 
				{
					if (!self::$db->query($query3)) 
					{
						if (!self::$db->query($query4)) 
						{
							self::$db->disconnect();
							return false;
						}
						else{
							self::$db->disconnect();
							return true;
						}
					}
					else{
						self::$db->disconnect();
						return true;
					}
				}
				else{
					self::$db->disconnect();
					return true;
				}*/
				echo $query2;
				self::$db->disconnect();
				return False;
			}
			else{
				echo $query2;
				self::$db->disconnect();
				return True;
			}
			
		} catch (Exception $e){
			echo _UNKNOWN._DOCSERVER." ".$cycle_id.' // ';
		}
	}
	
	
	public function getAllId($can_be_disabled = false){
		self::$db=new dbquery();
		self::$db->connect();
		$query = "select cycle_id from "._LC_CYCLES_TABLE_NAME." ";
		if(!$can_be_disabled)
			$query .= " where enabled = 'Y'";
		try{
			if($_ENV['DEBUG'])
				echo $query.' // ';
			self::$db->query($query);
		} catch (Exception $e){
			echo _NO_DOCSERVER_LOCATION.' // ';
		}
		if(self::$db->nb_result() > 0){
			$result = array();
			$cptId = 0;
			while($queryResult = self::$db->fetch_object()){
				$result[$cptId] = $queryResult->cycle_id;
				$cptId++;
			}
			self::$db->disconnect();
			return $result;
		} else {
			self::$db->disconnect();
			return null;
		}
	}
	
	/**
	* Check the where clause syntax
	*
	* @param  $where_clause string The where clause to check
	* @return bool true if the syntax is correct, false otherwise
	*/
	public function where_test($where_clause) {
		$res = true;
		self::$db=new dbquery();
		self::$db->connect();
		if(!empty($where_clause)) {
			$res = self::$db->query("select count(*) from res_x where ".$where_clause, true);
		}
		if(!$res) {
			$res = false;
		}
		self::$db->disconnect();
		return $res;
	}
	
	/**
	* Check the where clause syntax
	*
	* @param  $where_clause string The where clause to check
	* @return bool true if the syntax is correct, false otherwise
	*/
	public function where_test_secure($where_clause) {
		$string = $where_clause;
		$search1="'drop|insert|delete|update'";
		preg_match($search1, $string, $out);
		$count=count($out[0]);
		if($count == 1) {
			$find1 = true;
		}
		else {
			$find1 = false;
		}
		return $find1;
	}
}

?>

<?php
/**
* modules tools Class for notes
*
*  Contains all the functions to load modules tables for notes
*
* @package  maarch
* @version 3.0
* @since 10/2005
* @license GPL v3
* @author  Claire Figueras  <dev@maarch.org>
*
*/



// Loads the required class
try {
    require_once("core/class/class_db.php");
    require_once("modules/notes/notes_tables.php");
    require_once("modules/entities/entities_tables.php");
    require_once ("modules/notes/class/class_modules_tools.php");
    require_once "modules/entities/class/EntityControler.php";
} catch (Exception $e){
    echo $e->getMessage().' // ';
}

class notes extends dbquery
{

	/**
    * Dbquery object used to connnect to the database
    */
    private static $db;
    
    /**
    * Entity object
    */
    public static $ent;
    
     /**
    * Notes table
    */
    public static $notes_table ;

    /**
    * Notes_entities table
    */
    public static $notes_entities_table ;
    
    /**
    * Entities table
    */
    public static $entities_table ;
    
     /**
    * Opens a database connexion and values the tables variables
    */
    public function connect()
    {
        $db = new dbquery();
        $db->connect();
        self::$notes_table = NOTES_TABLE;
        self::$notes_entities_table = NOTE_ENTITIES_TABLE;
        self::$entities_table = 'entities';

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
	* Build Maarch module tables into sessions vars with a xml configuration
	* file
	*/
	public function build_modules_tables()
	{
		if (file_exists(
		    $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
		    . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . "modules"
		    . DIRECTORY_SEPARATOR . "notes" . DIRECTORY_SEPARATOR . "xml"
		    . DIRECTORY_SEPARATOR . "config.xml"
		)
		) {
			$path = $_SESSION['config']['corepath'] . 'custom'
			      . DIRECTORY_SEPARATOR . $_SESSION['custom_override_id']
			      . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR
			      . "notes" . DIRECTORY_SEPARATOR . "xml" . DIRECTORY_SEPARATOR
			      . "config.xml";
		} else {
			$path = "modules" . DIRECTORY_SEPARATOR . "notes"
			      . DIRECTORY_SEPARATOR . "xml" . DIRECTORY_SEPARATOR
			      . "config.xml";
		}
		$xmlconfig = simplexml_load_file($path);
		foreach ($xmlconfig->TABLENAME as $tableName) {
			$_SESSION['tablename']['not_notes'] = (string) $tableName->not_notes;
			$_SESSION['tablename']['note_entities'] = (string) $tableName->note_entities;
		}
		$hist = $xmlconfig->HISTORY;
		$_SESSION['history']['noteadd'] = (string) $hist->noteadd;
		$_SESSION['history']['noteup'] = (string) $hist->noteup;
		$_SESSION['history']['notedel'] = (string) $hist->notedel;
	}
	
	/**
	 * Function to get all the entities 
	 * 
	 */
/*
	public function getentities()
	{
		$entitiesOrg = array();
		require_once 'modules/entities/class/EntityControler.php';
		$entityControler = new EntityControler();
		$entitiesOrg = $entityControler->getAllEntities();
		 return $entitiesOrg;
	}
*/
	
	/**
	 * 
	 * 
	 * 
	 */
	 public function insertEntities($id)
	 {
		 //echo "RES_ID : ".$id;
	 } 
	
	
	/**
	 * Function to get which user can see a note
	 * @id note identifier
	 */
	public function getNotesEntities($id)
	{
		self::connect();
		$ent = new EntityControler();
		
		
		$query = "select entity_id, entity_label from ".self::$notes_entities_table." , ".self::$entities_table
		." WHERE item_id LIKE entity_id and note_id = " .$id;
		
		
		try{
            if($_ENV['DEBUG'])
                echo $query.' // ';
            self::$db->query($query);
        } catch (Exception $e){}
        

        $entitiesList = array();
        $entitiesChosen = array();
        $entitiesList = $ent->getAllEntities();
        

        while($res = self::$db->fetch_object())
        {
			array_push($entitiesChosen, $ent->get($res->entity_id));
        }
        
        //self::disconnect();
		return $entitiesChosen;
	}
	
	/*
	 * Function to get the entities
	 * 
	 */ 
	
	 public function process_where_clause($whereClause, $userId)
    {
        if (! preg_match('/@/', $whereClause)) {
            return $whereClause;
        }
        $where = $whereClause;
        $tmpArr = array();
        // We must create a new object because the object connexion can already
        // be used
        $db = new dbquery();
        $db->connect();
        require_once 'modules' . DIRECTORY_SEPARATOR . 'entities'
            . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
            . 'class_manage_entities.php';
        $obj = new entity();
        if (preg_match('/@my_entities/', $where)) {
            $entities = '';
            $db->query(
            	"select entity_id from " . ENT_USERS_ENTITIES
                . " where user_id = '" . $this->protect_string_db(
                    trim($userId)
                ) . "'"
            );
            while ($res = $db->fetch_object()) {
                $entities .= "'"  . $res->entity_id . "', ";
            }

            $entities = preg_replace('/, $/', '', $entities);

            if ($entities == '' && $userId == 'superadmin') {
                $entities = $this->empty_list();
            }
            $where = str_replace("@my_entities", $entities, $where);
        }
        if (preg_match('/@all_entities/', $where)) {
            $entities = '';
            $db->query(
            	"select entity_id from " . ENT_ENTITIES . " where enabled ='Y'"
            );
            while ($res = $db->fetch_object()) {
                $entities .= "'" . $res->entity_id . "', ";
            }
            $entities = preg_replace("|, $|", '', $entities);
            $where = str_replace("@all_entities", $entities, $where);
        }
        if (preg_match('/@my_primary_entity/', $where)) {
            $primEntity = '';
            if (isset($_SESSION['user']['UserId'])
                && $userId == $_SESSION['user']['UserId']
                && isset($_SESSION['user']['primary_entity']['id'])
            ) {
                $primEntity = "'" . $_SESSION['user']['primary_entity']['id']
                            . "'";
            } else {
                $db->query(
                	"select entity_id from " . ENT_USERS_ENTITIES
                    . " where user_id = '" . $this->protect_string_db(
                        trim($userId)
                    ) . "' and primary_entity = 'Y'"
                );
                //$db->show();
                $res = $db->fetch_object();
                if (isset($res->entity_id)) {
                    $primEntity = "'" . $res->entity_id . "'";
                }
            }
            if ($primEntity == '' && $userId == 'superadmin') {
                $primEntity = $this->empty_list();
            }
            $where = str_replace("@my_primary_entity", $primEntity, $where);
            //echo "<br>".$where."<br>";
        }
        $total = preg_match_all(
        	"|@subentities\[('[^\]]*')\]|", $where, $tmpArr, PREG_PATTERN_ORDER
        );
        if ($total > 0) {
            //$this->show_array( $tmpArr);
            for ($i = 0; $i < $total; $i ++) {
                $entitiesArr = array();
                $tmp = str_replace("'", '', $tmpArr[1][$i]);
                if (preg_match('/,/', $tmp)) {
                    $entitiesArr = preg_split('/,/', $tmp);
                } else {
                    array_push($entitiesArr, $tmp);
                }

                $children = array();
                for ($j = 0; $j < count($entitiesArr); $j ++) {
                    $tabChildren = array();
                    $arr = $obj->getTabChildrenId(
                        $tabChildren, $entitiesArr[$j]
                    );
                    $children = array_merge($children, $arr);
                }
                $entities = '';
                for ($j = 0; $j < count($children); $j ++) {
                    //$entities .= "'".$children[$j]."', ";
                    $entities .= $children[$j] .  ", ";
                }
                $entities = preg_replace("|, $|", '', $entities);
                if ($entities == '' && $userId == 'superadmin') {
                    $entities = $this->empty_list();
                }
                $where = preg_replace(
                	"|@subentities\['[^\]]*'\]|", $entities, $where, 1
                );
            }
        }
        $total = preg_match_all(
        	"|@immediate_children\[('[^\]]*')\]|", $where, $tmpArr,
            PREG_PATTERN_ORDER
        );
        if ($total > 0) {
            //$this->show_array($tmpArr);
            for ($i = 0; $i < $total; $i ++) {
                $entitiesArr = array();
                $tmp = str_replace("'", '', $tmpArr[1][$i]);
                if (preg_match('/,/' , $tmp)) {
                    $entitiesArr = preg_split('/,/', $tmp);
                } else {
                    array_push($entitiesArr, $tmp);
                }

                $children = array();
                for ($j = 0; $j < count($entitiesArr); $j ++) {
                    $tabChildren = array();
                    $arr = $obj->getTabChildrenId(
                        $tabChildren, $entitiesArr[$j], '', true
                    );
                    $children = array_merge($children, $arr);
                }
                //print_r($children);
                $entities = '';
                for ($j = 0; $j < count($children); $j ++) {
                    //$entities .= "'".$children[$j]."', ";
                    $entities .= $children[$j] . ", ";
                }
                $entities = preg_replace("|, $|", '', $entities);
                if ($entities == '' && $userId == 'superadmin') {
                    $entities = $this->empty_list();
                }

                $where = preg_replace(
                	"|@immediate_children\['[^\]]*'\]|", $entities, $where, 1
                );
            }
        }

        $total = preg_match_all(
        	"|@sisters_entities\[('[^\]]*')\]|", $where, $tmpArr,
            PREG_PATTERN_ORDER
        );
        if ($total > 0) {
            //$this->show_array( $tmpArr);
            for ($i = 0; $i < $total; $i ++) {
                $tmp = str_replace("'", '', $tmpArr[1][$i]);
                $tmp = trim($tmp);
                $entities = $obj->getTabSisterEntityId($tmp);
                $sisters = '';
                for ($j = 0; $j < count($entities); $j ++) {
                    $sisters .= $entities[$j].", ";
                }
                $sisters = preg_replace("|, $|", '', $sisters);
                if ($sisters == '' && $userId == 'superadmin') {
                    $sisters = $this->empty_list();
                }
                $where = preg_replace(
                	"|@sisters_entities\['[^\]]*'\]|", $sisters, $where, 1
                );
            }
        }
        $total = preg_match_all(
        	"|@parent_entity\[('[^\]]*')\]|", $where, $tmpArr,
            PREG_PATTERN_ORDER
        );
        if ($total > 0) {
            //$this->show_array( $tmpArr);
            for ($i = 0; $i < $total; $i ++) {
                $tmp = str_replace("'", '', $tmpArr[1][$i]);
                $tmp = trim($tmp);
                $entity = $obj->getParentEntityId($tmp);
                $entity = "'" . $entity . "'";
                if ($entity == '' && $userId == 'superadmin') {
                    $entity = $this->empty_list();
                }
                $where = preg_replace(
                	"|@parent_entity\['[^\]]*'\]|", $entity, $where, 1
                );
            }
        }
        $where = str_replace("or DESTINATION in ()", "", $where);
        //echo $where;exit;
        return $where;
    }
}


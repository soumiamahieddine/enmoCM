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
	
}


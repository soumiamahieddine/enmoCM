<?php
/*
*    Copyright 2008,2012 Maarch
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
* Module : Tags
* 
* This module is used to store ressources with any keywords
* V: 1.0
*
* @file
* @author Loic Vinet
* @date $date$
* @version $Revision$
*/


// To activate de debug mode of the class
$_ENV['DEBUG'] = false;
/*
define("_CODE_SEPARATOR","/");
define("_CODE_INCREMENT",1);
*/

// Loads the required class
try {
    require_once("core/class/class_db.php");
    require_once("core/class/class_history.php");
    require_once("modules/tags/class/Tag.php");
    require_once("modules/tags/tags_tables_definition.php");

} catch (Exception $e){
    echo $e->getMessage().' // ';
}

/**
* @brief  Controler of the Tag Object
* @ingroup core
*/
class tag_controler
    extends ObjectControler
{
    /**
     * Get event with given event_id.
     * Can return null if no corresponding object.
     * @param $id Id of event to get
     * @return event
     */
    
    
    public function get_all_tags()
    {
		/*
		 * Return a complete list of tags in Maarch
		 */
		  
		$return = array();
       

		$db = new dbquery();
		$db->connect();
        $db->query(
        	'select distinct tag_label, coll_id from '._TAG_TABLE_NAME.' order by tag_label asc ');
  
        self::set_specific_id('tag_label');
      
	  	if($db->nb_result() > 0){
	  		while($tag=$db->fetch_object()){
	  			$tougue['tag_label'] = $tag->tag_label;
				$tougue['coll_id'] = $tag->coll_id;
	  			array_push($return, $tougue);
			}
			return $return;
	  	}
        return false;
    }
    
    
    public function get_by_label($tag_label, $coll_id = 'letterbox_coll')
    {
		/*
		 * Searching a tag by label
		 * @If tag exists, return this value, else, return false
		 */
		
        if (empty($tag_label) || empty($coll_id) ) {
           
            return null;
        }

		$db = new dbquery();
		$db->connect();
        $db->query(
        	'select tag_label, coll_id from '._TAG_TABLE_NAME.' where tag_label=\''.$tag_label.'\' and'.
        		' coll_id = \''.$coll_id.'\'');
  
        self::set_specific_id('tag_label');
      
        $tag=$db->fetch_object();

        if (isset($tag)) {
            return $tag;
        } else {
            return null;
        }
    }
    
    public function get($tag_label, $coll_id, $res_id)
    {
		/*
		 * Standard Get Object, not used at this time
		 */
        if (empty($tag_label) || empty($coll_id) || empty($res_id)) {
            return null;
        }

        self::set_specific_id('tag_label');
      
        $tag = self::advanced_get($tag_label, _TAG_TABLE_NAME);

        if (isset($tag)) {
            return $tag;
        } else {
            return null;
        }
    }
	
	
  
	public function get_by_res($res_id,$coll_id)
    {
		/*
		 * Searching tags by a ressources
		 * @Return : list of tags for one ressource
		 */
		$db = new dbquery();
		$db->connect();
        $db->query(
        	"select tag_label from " ._TAG_TABLE_NAME
            . " where res_id = '" . $res_id . "' and coll_id = '".$coll_id."' "
        );
		//$db->show();
        
		
		$return = array();
		while ($res = $db->fetch_object()){
			array_push($return, $res->tag_label);
		}
        if ($return) return $return;
		else return false;
    }
  
  
  
  	public function delete_this_tag($res_id,$coll_id,$tag_label)
    {
		/*
		 * Deleting a tag for a ressource
		 */
		$db = new dbquery();
		$db->connect();
        $db->query(
        	"select tag_label from " ._TAG_TABLE_NAME
            . " where res_id = '" . $res_id . "' and coll_id = '".$coll_id."' and tag_label = '".$tag_label."' "
        );
		if ($db->nb_result()>0){
			//Lancement de la suppression de l'occurence
			$fin =$db->query(
	        	"delete from " ._TAG_TABLE_NAME
	            . " where res_id = '" . $res_id . "' and coll_id = '".$coll_id."' and tag_label = '".$tag_label."' "
      	    );
			if ($fin){ 
				$hist = new history();
				$hist->add(
					_TAG_TABLE_NAME, $tag_label, "DEL", 'tagdel', _TAG_DELETED.' : "'.
					substr($db->protect_string_db($tag_label), 0, 254) .'"',
					$_SESSION['config']['databasetype'], 'tags'
				);
				return true; }
		}
		return fasle;
		
		//$db->show();
    }
	
	public function countdocs($tag_label, $coll_id){
		/*
		 * Count ressources for one tag : used by tags administration
		 */
		 
		$db = new dbquery();
		$db->connect();
        $db->query(
	        	"select count(res_id) as bump from " ._TAG_TABLE_NAME
	            . " where tag_label = '" . $tag_label . "' and coll_id = '".$coll_id."' ".
	            " and res_id <> 0"
        );
		
		$result = $db->fetch_object();
		$return = 0; 
		
		if ($result)
		{
			$return = $result->bump; 
		}
		
		return $return;
	}
	
	
	/*
	 * Searching a list of ressources by label
	 * @Return : an Array with label's ressources or 0
	 */
	public function getresarray_byLabel($tag_label, $coll_id){
		$array = array();
		
		$db = new dbquery();
		$db->connect();
        $db->query(
	        	"select res_id as bump from " ._TAG_TABLE_NAME
	            . " where tag_label = '" . $tag_label . "' and coll_id = '".$coll_id."' ".
	            " and res_id <> 0"
        );
        //$db->show();
		
		while ($result = $db->fetch_object())
		{
			array_push($array, $result->bump);
		}
		
		if ($array)
		{
			return $array; 
		}
		
		return false;
	}
	
	
	public function delete_tags($res_id,$coll_id)
    {
		/*
		 * Searching a tag by label
		 * @If tag exists, return this value, else, return false
		 */
		$db = new dbquery();
		$db->connect();
        $db->query(
	        	"delete from " ._TAG_TABLE_NAME
	            . " where res_id = '" . $res_id . "' and coll_id = '".$coll_id."' "
        );
        $hist = new history();
		$hist->add(
			'res_view_letterbox', $res_id, "DEL", 'tagdel', _ALL_TAG_DELETED_FOR_RES_ID.' : "'.
			substr($db->protect_string_db($res_id), 0, 254) .'"',
			$_SESSION['config']['databasetype'], 'tags'
		);
		//$db->show();
    }
    
    
	public function delete($tag_label,$coll_id)
    {
		/*
		 * Deleting  [REALLY] a tag for a ressource
		 */
		$db = new dbquery();
		$db->connect();
        $del = $db->query(
	        	"delete from " ._TAG_TABLE_NAME
	            . " where tag_label = '" . $tag_label . "' and coll_id = '".$coll_id."' "
        );
		if ($del){
			$hist = new history();
			$hist->add(
				_TAG_TABLE_NAME, $tag_label, "DEL", 'tagdel', _TAG_DELETED.' : "'.
				substr($db->protect_string_db($tag_label), 0, 254) .'"',
				$_SESSION['config']['databasetype'], 'tags'
			);
			return true; 
		}
		return false;
		//$db->show();
    }
	
	
	public function store($tag_label, $mode='up', $params){
		/*
		 * Store into the database a tag for a ressource
		 */
		$db = new dbquery();
		
		if ($mode=='add'){
			$new_tag_label = $params[0];
			$coll_id = $params[1];
			$this->insert_tag_label($new_tag_label, $coll_id);	
			
			$hist = new history();
			$hist->add(
				_TAG_TABLE_NAME, $new_tag_label, "ADD", 'tagadd', _TAG_ADDED.' : "'.
				substr($db->protect_string_db($new_tag_label), 0, 254) .'"',
				$_SESSION['config']['databasetype'], 'tags'
			);
			return true;
		}
		elseif($mode=='up'){
			
			$new_tag_label = $params[0];
			$coll_id = $params[1];
			$this->update_tag_label($new_tag_label, $tag_label, $coll_id);	
			$hist = new history();
			$hist->add(
				_TAG_TABLE_NAME, $new_tag_label, "ADD", 'tagup', _TAG_ADDED.' : "'.
				substr($db->protect_string_db($new_tag_label), 0, 254) .'"',
				$_SESSION['config']['databasetype'], 'tags'
			);
			return true;
		}
		else
		{
			return false;	
		}
	}
	
	public function update_tag_label($new_tag_label, $old_taglabel, $coll_id)
    {
		/*
		 * Update in the memory [Session] the tag value for one ressource
		 */
    	$new_tag_label = $this->control_label($new_tag_label);
		
		$db = new dbquery();
		$db->connect();
        $db->query(
        	"select tag_label from " ._TAG_TABLE_NAME
            . " where  coll_id = '".$coll_id."' and tag_label = '".$new_tag_label."'  "
        );
        if ($db->nb_result() == 0)
		{
	        $db->query(
	        	"update " ._TAG_TABLE_NAME
	            . " set tag_label = '".$new_tag_label."' where coll_id = '".$coll_id."' and tag_label = '".$old_taglabel."'  "
	        );
	        $hist = new history();
			$hist->add(
				_TAG_TABLE_NAME, $new_tag_label, "UP", 'tagup', _TAG_UPDATED.' : "'.
				substr($db->protect_string_db($new_tag_label), 0, 254) .'"',
				$_SESSION['config']['databasetype'], 'tags'
			);
		}
		else
		{
			$_SESSION['error'] = _TAG_ALREADY_EXISTS;
			
		}
    }
	
	public function insert_tag_label($new_tag_label, $coll_id)
    {
		/*
		 * Add in the memory [Session] the tag value for one ressource
		 */
		$db = new dbquery();
		$db->connect();
		
		//Primo, test de l'existance du mot clé en base.
		$db->query(
        	"select tag_label from " ._TAG_TABLE_NAME
            . " where  coll_id = '".$coll_id."' and tag_label = '".$new_tag_label."'  "
        );
		//$db->show();exit();
		if ($db->nb_result() == 0)
		{
			 $db->query(
        	"insert into " ._TAG_TABLE_NAME
            . " values ('".$new_tag_label."', '".$coll_id."', 0)"
      		  );
      		 $hist = new history();
			 $hist->add(
				_TAG_TABLE_NAME, $new_tag_label, "ADD", 'tagadd', _TAG_ADDED.' : "'.
				substr($db->protect_string_db($new_tag_label), 0, 254) .'"',
				$_SESSION['config']['databasetype'], 'tags'
		 	 );
		}
		
    }
	
	
	
	public function add_this_tag($res_id,$coll_id,$tag_label)
    {
		/*
		 * Adding  [REALLY] a tag for a ressource
		 */
    	
		$tag_label = $this->control_label($tag_label);
		
		$db = new dbquery();
		$db->connect();
        $db->query(
        	"select tag_label from " ._TAG_TABLE_NAME
            . " where res_id = '" . $res_id . "' and coll_id = '".$coll_id."' and tag_label = '".$tag_label."'  "
        );
		if ($db->nb_result()==0){
			//Lancement de la suppression de l'occurence
			$fin =$db->query(
	        	"insert into " ._TAG_TABLE_NAME
	            . " (tag_label, res_id, coll_id) values ('".$tag_label."', '" . $res_id . "','".$coll_id."')  "
      	    );
			if ($fin){ 
				
				$hist = new history();
				$hist->add(
					'res_view_letterbox', $res_id, "ADD", 'tagadd', _TAG_ADDED.' : "'.
					substr($db->protect_string_db($tag_label), 0, 254) .'"',
					$_SESSION['config']['databasetype'], 'tags'
				);
				return true; }
		}
		return fasle;
		
		//$db->show();
        
    }
	
	public function load_sessiontag($res_id,$coll_id)
	{
			$_SESSION['tagsuser'] = array();	
			$_SESSION['tagsuser'] =	$this->get_by_res($res_id, $coll_id);	
	}
	
	
	public function add_this_tags_in_session($tag_label)
    { 
    
		$ready = true;
		if ($_SESSION['tagsuser'])
		{
			//$_SESSION['taguser'] = array();	
			
			foreach($_SESSION['tagsuser'] as $this_tag){
				if ($this_tag == $tag_label){
					$ready = false;
				}	
			}	
	
		}
		
		if ($ready == true){
					
					
			if 	(!$_SESSION['tagsuser'])
			{
				$_SESSION['tagsuser'] = array();
			}
				
			array_push($_SESSION['tagsuser'], $tag_label);
			return true;
		}
		return false;
	
    }
    
    public function remove_this_tags_in_session($tag_label)
    { //remplir le formulaire de session
	
		if ($_SESSION['tagsuser'])
		{
			$ready = false;
			foreach($_SESSION['tagsuser'] as $this_tag){
				if ($this_tag == $tag_label){
					$ready = true;
				}	
			}
			
			if ($ready == true){
				
				unset($_SESSION['tagsuser'][array_search($tag_label, $_SESSION['tagsuser'])]);
				return true;
			}
			return false;
		}
		else
		{
			return false;
		}    
    }
	
	public function update_restag($res_id,$coll_id,$tag_array)
    {
  		$core_tools = new core_tools();	
  		if ($core_tools->test_service('add_tag_to_res', 'tags',false) == 1)
		{
			$this->delete_tags($res_id, $coll_id);
			foreach($tag_array as $this_taglabel)
			{
				$this->add_this_tag($res_id,$coll_id,$this_taglabel);
			}
		}
	}
	
	
	private function control_label($label){
		$label  = str_replace('\r', '', $label);
		$label  = str_replace('\n', '', $label);
		$label  = str_replace('\'', ' ', $label);
		$label  = str_replace('"', ' ', $label);
		$label  = str_replace('\\', ' ', $label);
		
		
		//On découpe la chaine composée de virgules
		$tabrr = array( CHR(13) => ",", CHR(10) => "," ); 
		$label = strtr($label,$tabrr); 
		
		return $label;
	}
  
}

?>

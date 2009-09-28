<?php
/**
* Chrono number Class
*
* Contains all the specific functions of chrono number
*
* @package  Maarch LetterBox 3.0
* @version 3.0
* @since 06/2007
* @license GPL
* @author  Loïc Vinet  <dev@maarch.org>
*
*/

//class AdminActions extends dbquery
class chrono
{
	/**
	* Return an array with all structure readed in chorno.xml
	*
	* @param string $xml_file add or up (a supprimer)
	*/
	function get_structure($id_chrono)
	{
		$globality = array();
		$parameters_tab = array();
		$chrono_tab = array();
		
		
		$chrono_config = simplexml_load_file($_SESSION['config']['businessapppath']."xml".$_SESSION['slash_env']."chrono.xml");
		if($chrono_config)
		{
			foreach($chrono_config ->CHRONO as $CHRONO)
			{
				if($CHRONO->id ==  $id_chrono)
				{
					$chrono_id = (string) $CHRONO->id;
					$separator = (string) $CHRONO->separator;
					array_push($parameters_tab, array("ID"=> $chrono_id , "SEPARATOR"=>$separator));
					
					foreach($CHRONO->ELEMENT as $ELEMENT)
					{
						$type = $ELEMENT->type;
						$value = (string) $ELEMENT->value;
						array_push($chrono_tab, array("TYPE"=> $type , "VALUE"=>$value));
					}
				}
			}
			array_push($globality, array("PARAMETERS"=>$parameters_tab, "ELEMENTS"=>$chrono_tab));
			
			return $globality;
		}	
		else
		{
			echo "chrono::get_structure error";
		}
		
	}

	function convert_date_field($chrono_array)
	{
		//$new_chrono_array = array();
	
		for($i = 0;$i <= count($chrono_array); $i++)
		{
				if ($chrono_array[$i]['TYPE'] == "date")
				{
					if ($chrono_array[$i]['VALUE'] == "year")
					{
						$chrono_array[$i]['VALUE'] = date('Y');
					}
					elseif ($chrono_array[$i]['VALUE'] == "month")
					{
						$chrono_array[$i]['VALUE'] = date('m');
					}
					elseif ($chrono_array[$i]['VALUE'] == "day")
					{
						$chrono_array[$i]['VALUE'] = date('d');
					}
					elseif ($chrono_array[$i]['VALUE'] == "full_date")
					{
						$chrono_array[$i]['VALUE'] = date('dmY');
					}
				}
		}
		return $chrono_array;
		
	}
	
	
	
	function convert_maarch_var($chrono_array, $php_var)
	{
		
		for($i = 0;$i <= count($chrono_array); $i++)
		{
				if ($chrono_array[$i]['TYPE'] == "maarch_var")
				{
					if ($chrono_array[$i]['VALUE'] == "arbox_id")
					{
						$chrono_array[$i]['VALUE'] = $php_var['arbox_id'];
					}
					elseif ($chrono_array[$i]['VALUE'] == "entity_id")
					{
						$chrono_array[$i]['VALUE'] = $php_var['entity_id'];;
					}
					elseif ($chrono_array[$i]['VALUE'] == "type_id")
					{
						$chrono_array[$i]['VALUE'] = $php_var['type_id'];;
					}
				}
		}
		return $chrono_array;
		
	}
	
	
	function convert_maarch_forms($chrono_array, $forms)
	{
		
		
		for($i = 0;$i <= count($chrono_array); $i++)
		{
			if($chrono_array[$i]['TYPE'] == "maarch_form")
			{ 
					foreach ($forms as $key => $value)
					{	
						if ($chrono_array[$i]['VALUE'] == $key)
						{
							$chrono_array[$i]['VALUE'] = $value;
						}
					}

			}
		}
		return $chrono_array;
	}
		
	
	function convert_maarch_functions($chrono_array, $php_var = 'false')
	{
		for($i = 0;$i <= count($chrono_array); $i++)
		{
				
				if ($chrono_array[$i]['TYPE'] == "maarch_functions")
				{
					if ($chrono_array[$i]['VALUE'] == "chr_global")
					{
						$chrono_array[$i]['VALUE'] = $this->execute_chrono_for_this_year();
					}
					elseif ($chrono_array[$i]['VALUE'] == "chr_by_entity")
					{
						$chrono_array[$i]['VALUE'] = $this->execute_chrono_by_entity($php_var['entity_id']);
					}
					elseif ($chrono_array[$i]['VALUE'] == "chr_by_category")
					{
						$chrono_array[$i]['VALUE'] = $this->execute_chrono_by_category($php_var['category_id']);
					}
					elseif ($chrono_array[$i]['VALUE'] == "category_char")
					{
						$chrono_array[$i]['VALUE'] = $this->execute_category_char($php_var);
					}
					
			}
		}
		return $chrono_array;
		
	}
	
	
	function execute_chrono_for_this_year()
	{
		require_once($_SESSION['pathtocoreclass']."class_db.php");
		$db = new dbquery();
		$db->connect();
		
		//Get the crono key for this year
		$db->query("SELECT param_value_int from ".$_SESSION['tablename']['param']." where id = 'chrono_global_".date('Y')."' ");
		if ($db->nb_result() == 0)
		{
				$chrono = $this->create_new_chrono_global($db);
		}
		else
		{
				$fetch = $db->fetch_object();
				$chrono = $fetch->param_value_int; 
		}
		$this->update_chrono_for_this_year($chrono, $db);
		return $chrono;
	}
	
	
	function execute_chrono_by_entity($entity)
	{
		require_once($_SESSION['pathtocoreclass']."class_db.php");
		$db = new dbquery();
		$db->connect();
		
		//Get the crono key for this year  
		$db->query("SELECT param_value_int from ".$_SESSION['tablename']['param']." where id = 'chrono_".$entity."_".date('Y')."' ");
		if ($db->nb_result() == 0)
		{
				$chrono = $this->create_new_chrono_for_entity($db, $entity);
		}
		else
		{
				$fetch = $db->fetch_object();
				$chrono = $fetch->param_value_int; 
		}
		$this->update_chrono_for_entity($chrono, $db, $entity);
		return $chrono;
	}
	
	function execute_chrono_by_category($category)
	{
		require_once($_SESSION['pathtocoreclass']."class_db.php");
		$db = new dbquery();
		$db->connect();
		
		//Get the crono key for this year  
		$db->query("SELECT param_value_int from ".$_SESSION['tablename']['param']." where id = 'chrono_".$category."_".date('Y')."' ");
		if ($db->nb_result() == 0)
		{
				$chrono = $this->create_new_chrono_for_category($db, $category);
		}
		else
		{
				$fetch = $db->fetch_object();
				$chrono = $fetch->param_value_int; 
		}
		$this->update_chrono_for_category($chrono, $db, $category);
		return $chrono;
	}
	
	private function execute_category_char($php_var)
	{
		if (!$php_var['category_id'])
		{
			return "category::php_var error";
		}
		else
		{
			if($php_var['category_id'] == "incoming")
			{
				return "E";
			}
			elseif($php_var['category_id'] == "outcoming")
			{
				return "S";
			}
			else
			{
				return '';
			}
		}
	}
	
	//For global chrono
	private function update_chrono_for_this_year($actual_chrono, $db)
	{
		$actual_chrono++;
		$db->query("UPDATE ".$_SESSION['tablename']['param']." SET param_value_int = '".$actual_chrono."'  WHERE id = 'chrono_global_".date('Y')."' " );
	}
	
	private function create_new_chrono_global($db)
	{
		$db->query("INSERT INTO ".$_SESSION['tablename']['param']." (id, param_value_int) VALUES ('chrono_global_".date('Y')."', '1')" );
		return 1;
	}
	
	
	
	//For specific chrono =>category
	private function update_chrono_for_category($actual_chrono, $db, $category)
	{
		$actual_chrono++;
		$db->query("UPDATE ".$_SESSION['tablename']['param']." SET param_value_int = '".$actual_chrono."'  WHERE id = 'chrono_".$category."_".date('Y')."' " );
	}
	private function create_new_chrono_for_category($db, $category)
	{
		$db->query("INSERT INTO ".$_SESSION['tablename']['param']." (id, param_value_int) VALUES ('chrono_".$category."_".date('Y')."', '1')" );
		return 1;
	}
	
	
	//For specific chrono =>entity
	private function update_chrono_for_entity($actual_chrono, $db, $entity)
	{
		$actual_chrono++;
		$db->query("UPDATE ".$_SESSION['tablename']['param']." SET param_value_int = '".$actual_chrono."'  WHERE id = 'chrono_".$entity."_".date('Y')."' " );
	}
	private function create_new_chrono_for_entity($db, $entity)
	{
		$db->query("INSERT INTO ".$_SESSION['tablename']['param']." (id, param_value_int) VALUES ('chrono_".$entity."_".date('Y')."', '1')" );
		return 1;
	}
	
	function generate_chrono($chrono_id, $php_var = 'false', $form= 'false')
	{
		
		$tmp = $this->get_structure($chrono_id);
		$elements = $tmp[0]['ELEMENTS'];
		$parameters = $tmp[0]['PARAMETERS'];		
	
	
		//Launch any conversion needed for value in the chrono array
		$elements = $this->convert_date_field($elements); //For type date
		$elements = $this->convert_maarch_var($elements, $php_var); //For php var in maarch
		$elements = $this->convert_maarch_functions($elements, $php_var); 	
		$elements = $this->convert_maarch_forms($elements, $form); //For values used in forms
		
		
		
		//Generate chrono string
		$string = $this->convert_in_string($elements, $parameters);
		return $string;
	}
	
	
	function convert_in_string($elements, $parameters)
	{
		
		$separator = $parameters[0]['SEPARATOR']; 
		
		$this_string = '';
		//Explode each elements of this array
		foreach($elements as $array)
		{
			$this_string .= $separator;
			$this_string .= $array['VALUE'];
		}
		
		//$this_string = substr($this_string, 1);
		return $this_string; 
		
	}
	

}

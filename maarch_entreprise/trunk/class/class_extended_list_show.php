<?php
class extended_list_show extends functions
{
	//Public variables
	public $actual_line_css;
	public $the_start;
	public $the_link;
	public $the_template;
	public $hide_standard_list;
	public $disabled_field;
	public $countTd;
	public $line_css;
	public $params = array();
	public $list_key;
	public $actions_link = array();
	
	//Load value from db with $result tab
	public function tmplt_load_value($actual_string, $theline, $result)
	{
		$my_explode= explode ("|", $actual_string);
		if (!$my_explode[1])
		{
			return _WRONG_PARAM_FOR_LOAD_VALUE;
		}
		else
		{
			$to_share = $my_explode[1];
			for($stand= 0; $stand <= count($result[$theline]); $stand++ )
			{
				if($result[$theline][$stand]['column'] == $to_share)
				{
						return $result[$theline][$stand]['value'];
				}
			}
		}
	}

	
	//Load css defined in $actual_string
	private function tmplt_load_css($actual_string)
	{
		$my_explode= explode ("|", $actual_string);

		if (!$my_explode[1])
		{
			return _WRONG_PARAM_FOR_LOAD_VALUE;
		}
		else
		{
			return $my_explode[1];
		}
	}


	//Load image from apps defined in $actual_string
	private function tmplt_load_img($actual_string)
	{
		$my_explode= explode ("|", $actual_string);

		if (!$my_explode[1])
		{
			return _WRONG_PARAM_FOR_LOAD_VALUE;
		}
		else
		{
			return $_SESSION['config']['businessappurl']."static.php?filename=".$my_explode[1];
		}
	}


	//Load radio form if this parameters is loaded in list_show and list_show_with_template
	private function tmplt_load_external_script($actual_string, $theline, $result, $key)
	{

		$my_explode= explode ("|", $actual_string);
		if (count($my_explode) <> 3)
		{

			return  _WRONG_PARAM_FOR_LOAD_VALUE;
		}

		$module_id = $my_explode[1];
		$file_name = $my_explode[2];

		include('modules'.DIRECTORY_SEPARATOR.$module_id.DIRECTORY_SEPARATOR.$file_name);
		//return $external;
	}


	//Load function order from templated list
	private function tmplt_order_link($actual_string)
	{
		$my_explode= explode ("|", $actual_string);

		if (count($my_explode) <> 3)
		{

			return  _WRONG_PARAM_FOR_LOAD_VALUE;
		}
		else
		{
			$my_link = $this->the_link."&amp;listlistreinit=true&amp;start=".$this->the_start."&amp;order=".$my_explode[2]."&amp;order_field=".$my_explode[1];
			return $my_link;
		}
	}


	//Generate link to view the document
	private function url_docview($actual_string, $theline, $result, $key)
	{
		$return = $_SESSION['config']['businessappurl']."index.php?display=true&dir=indexing_searching&page=view&id=".$result[$theline][0][$key];
		return $return;
	}


	//Generate link to view detail page
	private function tmplt_url_docdetail($actual_string, $theline, $result, $key)
	{
		$return = $_SESSION['config']['businessappurl']."index.php?page=".$this->params['details_destination']."&amp;id=".$result[$theline][0][$key];
		return $return;
	}

	
	//Load radio form if this parameters is loaded in list_show and list_show_with_template
	public function tmplt_func_bool_radio_form($actual_string, $theline, $result, $key)
	{
		if ($this->params['bool_radio_form'] == true)
		{
			if ($this->disabled_field == true)
			{
				$return = '<img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=cadenas_rouge.png" alt="'._DOC_LOCKED.'" border="0"/>';
			}
			else
			{
				$return = '<input type="radio"  class="check" name="field" value="'.$result[$theline][0]['value'].'" class="check" />';
			}
			return $return;
		}
	}


	//Load check form if this parameters is loaded in list_show and list_show_with_template
	private function tmplt_func_bool_check_form($actual_string, $theline, $result, $key)
	{
		if ($this->params['bool_check_form'] == true)
		{
			if ($this->disabled_field == true)
			{
				$return = '<img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=cadenas_rouge.png" alt="'._DOC_LOCKED.'" border="0"/>';
			}
			else
			{
				$return = '<input type="checkbox"  name="field[]" value="'.$result[$theline][0]['value'].'" class="check" />';
			}
						
			return $return;
		}
	}

	//Load order icon and link if this parameters is loaded in list_show
	private function tmplt_func_bool_sort($actual_string, $theline, $result, $key)
	{
		if ($this->params['bool_sort'] == true)
		{
			$my_explode= explode ("|", $actual_string);
		
			if (!isset($my_explode[1]))
			{
				return  _WRONG_PARAM_FOR_LOAD_VALUE;
			}
			else
			{
				$col_id = $my_explode[1];
				
				if ($this->params['bool_list_is_ajax'] === true)
				{
					$return = '<a href="#" onclick="goToLink(\''.$this->the_link.'&amp;display=true'.$this->the_template.'&amp;start='.$this->start.'&amp;order=desc&amp;order_field='.$col_id.'&amp;listreinit=true\', \''.$this->params['div_list_ajax'].'\');" title="'. _DESC_SORT.'"><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=tri_down.gif" border="0" alt="'._DESC_SORT.'" /></a>';
					$return .= '<a href="#" onclick="goToLink(\''.$this->the_link.'&amp;display=true'.$this->the_template.'&amp;start='.$this->start.'&amp;order=asc&amp;order_field='.$col_id.'&amp;listreinit=true\', \''.$this->params['div_list_ajax'].'\');" title="'._ASC_SORT.'"> <img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=tri_up.gif" border="0" alt="'._ASC_SORT.'" /></a>';
				}
				else
				{
					$return = '<a href="'.$this->the_link.'&amp;start='.$this->start.$this->the_template.'&amp;order=desc&amp;order_field='.$col_id.'&amp;listreinit=true" title="'. _DESC_SORT.'"><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=tri_down.gif" border="0" alt="'._DESC_SORT.'" /></a>';
					$return .= '<a href="'.$this->the_link.'&amp;start='.$this->start.$this->the_template.'&amp;order=asc&amp;order_field='.$col_id.'&amp;listreinit=true" title="'._ASC_SORT.'"> <img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=tri_up.gif" border="0" alt="'._ASC_SORT.'" /></a>';
				}
							
				return $return;
			}
		}
	}
	
	//Load check form if this parameters is loaded in list_show and list_show_with_template
	private function tmplt_func_click_form($actual_string, $theline, $result, $key)
	{
		if($this->params['bool_do_action'] && !empty($this->params['id_action']) && (count($this->params['do_actions_arr']) == 0 ||  $this->params['do_actions_arr'][$theline] == true) )
		{
			$return = '//onclick="valid_form( \'page\', \''.$result[$theline][0]['value'].'\', \''.$this->params['id_action'].'\');"';
			return $return;
		}
	}


	//Load view_doc if this parameters is loaded in list_show and list_show_with_template
	private function tmplt_func_bool_detail_doc($actual_string, $theline, $result, $key)
	{
		if ($this->params['bool_details'] == true)
		{
			if($this->params['bool_details_popup'] == true)
			{
				$return = '<a href="#" OnClick="javascript:window.open(\''.$_SESSION['config']['businessappurl'].'index.php?page='.$this->params['details_destination'].'&amp;id='.$result[$theline][0][$key].'\');" title="'._DETAILS.'">';
			}
			else
			{
				$return = '<a href="#" OnClick="javascript:window.top.location=\''.$_SESSION['config']['businessappurl'].'index.php?page='.$this->params['details_destination'].'&amp;id='.$result[$theline][0][$key].'\';return false;" title="'. _DETAILS.'">';
			}
			
			$return .= "<img src='".$_SESSION['config']['businessappurl']."static.php?filename=picto_infos.gif'  alt='"._DETAILS."'   border='0' /></a>";

			return $return;
		}
	}

	//Load view_doc if this parameters is loaded in list_show and list_show_with_template
	private function tmplt_func_bool_detail_cases($actual_string, $theline, $result, $key)
	{
		if ($this->params['bool_details'] == true)
		{
			if($this->params['bool_details_popup'] == true)
			{
								
				$return = '<a href="#" OnClick="javascript:window.open(\''.$_SESSION['config']['businessappurl']."index.php?page=details_cases&module=cases&amp;id=".$result[$theline][0]['case_id'].'\');" title="'._DETAILS_CASES.'">';
			}
			else
			{
				$return = '<a href="#" OnClick="javascript:window.top.location=\''.$_SESSION['config']['businessappurl']."index.php?page=details_cases&module=cases&amp;id=".$result[$theline][0]['case_id'].'\';return false;" title="'. _DETAILS_CASES.'">';
			}
			$return  .= "<img src='".$_SESSION['config']['businessappurl']."static.php?filename=picto_infos.gif'  alt='"._DETAILS_CASES."'  border='0' /></a>";

			return $return;
		}
	}


	//Load check form if this parameters is loaded in list_show and list_show_with_template
	private function tmplt_func_bool_view_doc($actual_string, $theline, $result, $key)
	{
		if ($this->params['bool_view_document'] == true)
		{

			$return = "<a href='".$_SESSION['config']['businessappurl']."index.php?display=true&dir=indexing_searching&page=view&id=".$result[$theline][0][$key]."' target=\"_blank\" title='"._VIEW_DOC."'>
                            <img src='".$_SESSION['config']['businessappurl']."static.php?filename=picto_dld.gif' alt='"._VIEW_DOC."' border='0'/></a>";
			return $return;
		}
	}


	//Load check form if this parameters is loaded in list_show and list_show_with_template
	private function tmplt_include_by_module($actual_string, $theline, $result, $key, $string_to_module)
	{
		$my_explode= explode ("|", $actual_string);
		
		if (count($my_explode) <> 2)
		{
			return  _WRONG_PARAM_FOR_LOAD_VALUE;
		}
		else
		{
			$core_tools = new core_tools();
			$module_id = $my_explode[1];
			
			if($core_tools->is_module_loaded($module_id) == true)
			{
				$temp = $string_to_module;
				preg_match_all('/##(.*?)##/', $temp, $out);

				for($i=0;$i<count($out[0]);$i++)
				{
					$remplacement = $this->load_var_sys($out[1][$i], $theline,$result, $key);
					$temp = str_replace($out[0][$i],$remplacement,$temp);
				}
				$string_to_module = $temp;

				return $string_to_module;
			}
			else
			{
				return '';
			}
		}
	}
	
	//Reload last css parameter defined for the result list
	private function tmplt_css_line_reload($actual_string)
	{
		return $this->actual_line_css;
	}

	//Load constant from lang file
	private function tmplt_define_lang($actual_string)
	{
		$my_explode= explode ("|", $actual_string);

		if (!$my_explode[1])
		{
			return _WRONG_PARAM_FOR_LOAD_VALUE;
		}
		else
		{
			return constant($my_explode[1]);
		}
	}

	//Load css for actual line. For the next line, arg1 is swith by arg2
	private  function tmplt_css_line($actual_string)
	{
		$my_explode= explode ("|", $actual_string);

		if (!$my_explode[1])
		{
			return _WRONG_PARAM_FOR_LOAD_VALUE;
		}
		else
		{
			if(count($my_explode) == 2 )
			{
				return $my_explode[1];
			}
			elseif(count($my_explode) == 3 )
			{
				if ($this->actual_line_css == '')
				{
					$this->actual_line_css = $my_explode[1];
					return $this->actual_line_css;
				}
				elseif ($this->actual_line_css == $my_explode[1])
				{
					$this->actual_line_css = $my_explode[2];
					return $this->actual_line_css;
				}
				elseif ($this->actual_line_css == $my_explode[2])
				{
					$this->actual_line_css = $my_explode[1];
					return $this->actual_line_css;
				}
				else
				{
					return _WRONG_PARAM_FOR_LOAD_VALUE;
				}
			}
			else
			{
				return _WRONG_PARAM_FOR_LOAD_VALUE;
			}
		}
	}

	//Display actions icon/label
	private function tmplt_func_display_action($actual_string, $theline, $result, $key)
	{
		$my_explode= explode ("|", $actual_string);
		
		if (!isset($my_explode[1]) || strlen ($my_explode[1]) < 0 )
		{
			return _WRONG_PARAM_FOR_LOAD_VALUE;
		}
		else
		{
			$action_id = $my_explode[1];
			
			$disabled_action = $this->line_is_disabled($result, $theline, $this->actions_link[$action_id]['disabled_field'], $this->actions_link[$action_id]['disabled_rule']);
			
			if (!$disabled_action)
			{
				$href = $this->build_link($result, $theline,  $this->actions_link[$action_id]['href']);
				
				$action_href = '';
				
				//Begin of href link
				if(isset($this->actions_link[$action_id]['bool_popup']) && $this->actions_link[$action_id]['bool_popup'] === true) //If open link in popup widow
				{
					//Init
					$popup_name = $popup_arguments = '';
					
					if(isset($this->actions_link[$action_id]['popup_name'])) 
					{
						$popup_name = $this->actions_link[$action_id]['popup_name'];
					}
					
					if(isset($this->actions_link[$action_id]['popup_arguments'])) 
					{
						$popup_arguments = $this->actions_link[$action_id]['popup_arguments'];
					}
					
					$action_href .= '<a href="javascript://" onclick="openpopup(\''.$href.'\', \''.$popup_name.'\', \''.$popup_arguments.'\');" title="'.$this->actions_link[$action_id]['tooltip'].'>"';
				}
				else
				{
					$action_href .= '<a href="'. $href.'" title="'.$this->actions_link[$action_id]['tooltip'].'" ';
				}
				
				//Javascript alert box
				if(isset($this->actions_link[$action_id]['bool_alert']) && $this->actions_link[$action_id]['bool_alert'] === true) 
				{
					foreach(array_keys($result[$theline]) as $value)
					{
						if($result[$theline][$value]['column'] == $this->actions_link[$action_id]['alert_field'])
						{
							$str_alert = addslashes($result[$theline][$value]['value']);
							break;
						}
					}
					$str_alert_del = str_replace("@alert_field", $str_alert, $this->actions_link[$action_id]['alert_text']);
					$action_href .= 'onclick="return(confirm(\''.$str_alert_del.'\'));" ';
				}
				
				//Style
				if(isset($this->actions_link[$action_id]['class'])) { $action_href .= ' class="'.$this->actions_link[$action_id]['class'].'">';}else {$action_href .= '>';} //End of link
				
				//Image
				if(isset($this->actions_link[$action_id]['icon'])) { $action_href .= '<img src="'.$this->actions_link[$action_id]['icon'].'" alt="'.$this->actions_link[$action_id]['tooltip'].'" border="0"/>'; }
				
				//Label
				if(isset($this->actions_link[$action_id]['label'])) { $action_href .= '&nbsp;'.$this->actions_link[$action_id]['label'];}
				
				$action_href .= '</a>';
			}
			else
			{
				$action_href = '&nbsp;';
			}
		}
		return $action_href;
	}
	
	//Display Add button
	private function tmplt_func_display_add_button($actual_string, $theline, $result, $key)
	{
        $return = '<a href="'.$this->params['add_button_link'].'"><span>'.$this->params['add_button_label'].'</span></a>';
		return $return;
	}
	
	//Load string ans search all function defined in this string
	private function load_var_sys($actual_string, $theline, $result = array(), $key = 'empty' , $include_by_module= '')
	{		
		##load_value|arg1##: load value in the db; arg1= column's value identifier
		if (preg_match("/^load_value\|/", $actual_string))
		//elseif($actual_string == "load_value")
		{
			$my_var = $this->tmplt_load_value($actual_string, $theline, $result);
		}
		##load_css|arg1## : load css style - arg1= name of this class
		elseif (preg_match("/^load_css\|/", $actual_string))
		{
			$my_var = $this->tmplt_load_css($actual_string);
		}
		##css_line|coll|nonecoll## : load css style for line arg1,arg2 : switch beetwin style on line one or line two
		elseif (preg_match("/^css_line_reload$/", $actual_string))
		{
			$my_var = $this->tmplt_css_line_reload($actual_string);
		}
		##css_line|coll|nonecoll## : load css style for line arg1,arg2 : switch beetwin style on line one or line two
		elseif (preg_match("/^css_line\|/", $actual_string))
		{
			$my_var = $this->tmplt_css_line($actual_string);
		}
		##load_img|arg1## : show loaded image; arg1= name of img file
		elseif (preg_match("/^load_img\|/", $actual_string))
		{
			$my_var = $this->tmplt_load_img($actual_string);
		}
		##order_link|arg1|arg2## : reload list and change order;  arg1=type; arg2=sort
		elseif (preg_match("/^order_link\|/", $actual_string))
		{
			$my_var = $this->tmplt_order_link($actual_string);
		}
		##url_docview## : view the file
		elseif (preg_match("/^url_docview$/", $actual_string))
		{
			$my_var = $this->url_docview($actual_string, $theline, $result, $key);
		}
		##define_lang|arg1## : define constant by the lang file; arg1 = constant of lang.php
		elseif (preg_match("/^define_lang\|/", $actual_string))
		{
			$my_var = $this->tmplt_define_lang($actual_string);
		}
		##url_docdetail## : load page detail for this file
		elseif (preg_match("/^url_docdetail$/", $actual_string))
		{
			$my_var = $this->tmplt_url_docdetail($actual_string, $theline, $result, $key);
		}
		##func_bool_radio_form## : Activate parameters in class list show
		elseif (preg_match("/^func_bool_radio_form$/", $actual_string))
		{
			$my_var = $this->tmplt_func_bool_radio_form($actual_string, $theline, $result, $key);
		}
		##func_bool_check_form## : Activate parameters in class list show
		elseif (preg_match("/^func_bool_check_form$/", $actual_string))
		{
			$my_var = $this->tmplt_func_bool_check_form($actual_string, $theline, $result, $key);
		}
		##func_bool_view_doc## : Activate parameters in class list show
		elseif (preg_match("/^func_bool_view_doc$/", $actual_string))
		{
			$my_var = $this->tmplt_func_bool_view_doc($actual_string, $theline, $result, $key);
		}
		##func_bool_detail_doc## : Activate parameters in class list show
		elseif (preg_match("/^func_bool_detail_doc$/", $actual_string))
		{
			$my_var = $this->tmplt_func_bool_detail_doc($actual_string, $theline, $result, $key);
		}
		elseif (preg_match("/^func_click_form$/", $actual_string))
		{
			$my_var = $this->tmplt_func_click_form($actual_string, $theline, $result, $key);
		}
		elseif (preg_match("/^func_include_by_module\|/", $actual_string))
		{
			$my_var = $this->tmplt_include_by_module($actual_string, $theline, $result, $key,$include_by_module);
		}
		elseif (preg_match("/^func_load_external_script\|/", $actual_string))
		{
			$my_var = $this->tmplt_load_external_script($actual_string, $theline, $result, $key,$include_by_module);
		}
		elseif (preg_match("/^func_bool_detail_case$/", $actual_string))
		{
			$my_var = $this->tmplt_func_bool_detail_cases($actual_string, $theline, $result, $key,$include_by_module);
		}
		elseif (preg_match("/^func_display_action\|/", $actual_string))
		{
			$my_var = $this->tmplt_func_display_action($actual_string, $theline, $result, $key);
		}
		elseif (preg_match("/^func_display_add_button$/", $actual_string))
		{
			$my_var = $this->tmplt_func_display_add_button($actual_string, $theline, $result, $key);
		}
		elseif (preg_match("/^func_bool_sort\|/", $actual_string))
		{
			$my_var = $this->tmplt_func_bool_sort($actual_string, $theline, $result, $key);
		}
		else
		{
			$my_var = _WRONG_FUNCTION_OR_WRONG_PARAMETERS;
		}
		return $my_var;
	}


	//Get template and remove all comments
	private function get_template($this_file)
	{
		//Ouverture du fichier
		$list_trait = file_get_contents ($this_file);
		//Suppression des commantaires dans la page
		$list_trait = preg_replace("/(<!--.*?-->)/s","", $list_trait);

		return $list_trait;
	}
	
	
	//Disable line if disabled rule is true
	private function line_is_disabled($result, $theline, $disabled_field, $disabled_rule)
	{
		$disabled = false;
		$disabled_str = "";
		
		foreach(array_keys($result[$theline]) as $value)
		{
			if($result[$theline][$value]['column'] == $disabled_field)
			{
				//echo $result[$theline][$value]['column'].'/';
				$disabled_str = addslashes($result[$theline][$value]['value']);
				break;
			}
		}

		//Disabled fields
		if (!empty($disabled_str))
		{
			$str_disabled_field = str_replace("@disabled_field", "'".$disabled_str."'", $disabled_rule);
			$rule = "return($str_disabled_field);";
			
			//echo $rule."<br>\n";
			if(@eval($rule))
			{
				$disabled = true;
			}
			//var_dump($disabled);
		}
		
		return $disabled;
	}
	
	//Build link
	private function build_link($result, $theline, $actionHref)
	{
		//load href link for this action
		$href = $actionHref;
		
		//If you want to use different key for action link
		if (strpos($actionHref, "@key") !== false)
		{	
			$tab_key = array();
			
			//Position of the @key string
			$key_pos = strpos($actionHref, "@key");

			//Extract part of the url after @key= to the end
			$key_str = substr($actionHref, $key_pos + 5);

			//Length of the key
			$key_len = $key_pos + strpos($key_str, "$");
			
			//The exact string key
			$the_key = substr($key_str, 0, strpos($key_str, "$"));
			$tab_key = explode(";", $the_key);
			$tab_subkey = array();
			
			//Init all var
			$label = $val_key = $new_href ='';
			
			foreach ($tab_key as $value)
			{
				$tab_subkey = explode("|", $value);
				$label = $tab_subkey[0];
				$val_key = $tab_subkey[1];
				
				//Get the value
				foreach(array_keys($result[$theline]) as $value2)
				{
					if($result[$theline][$value2]['column'] == $val_key)
					{
						if($new_href == ''){
							$new_href.= $label.'='.$result[$theline][$value2]['value'];
						}
						else{
							$new_href.= '&amp;'.$label.'='.$result[$theline][$value2]['value'];
						}
					}
				}
			}
			
			//replace the key couple
			$href = substr_replace($actionHref, $new_href, $key_pos, $key_len);
			
			//echo $href; exit;
		}
		else
		{
			$href = $actionHref."&amp;id=".$result[$theline][0][$this->list_key]; 
		}
		return $href;
	}
	
	
	//Create table tag
	private function create_table ($table_style)
	{
		if(!empty($this->params['table_css'])){ $table_css = 'class="'.$this->params['table_css'].'"'; }
		
		$table = '<table border="0" '.$table_style.' cellspacing="0" '.$table_css.'>';

		return $table;
	}
	
	
	//Create header
	private function create_header($result, $listcolumn, $listshow, $ordercol)
	{
		$count_td = 0;
		$header = '<thead>';	
		$header .= '<tr>';

		//If the checkbox boolean
		if(isset($this->params['bool_check_form']) && $this->params['bool_check_form'] || 
			(isset($this->params['bool_radio_form']) && $this->params['bool_radio_form']) 
			)
		{
			$header .= '<th width="3%">&nbsp;</th>';
			$count_td ++;
		}

		//If the view document boolean
		if(isset($this->params['bool_view_document']) && $this->params['bool_view_document'])
		{
			$header .= '<th width="3%">&nbsp;</th>';
			$count_td ++;
		}

		//Print column header
		for($count_column = 0;$count_column < count($listcolumn);$count_column++)
		{
			if($listshow[$count_column] === true)
			{
				$header .= '<th width="'.$result[0][$count_column]['size'].'%" valign="'.$result[0][$count_column]['valign'].'" align="'.$result[0][$count_column]['label_align'].'">';
				$header .= '<span>'.$listcolumn[$count_column]; 
				
				//Show sort icon
				if($this->params['bool_sort'])
				{
					if( $ordercol[$count_column] !== false)
					{
						//If Ajax link, call javascript redirection function
						if($this->params['bool_list_is_ajax'] && !empty($this->params['div_list_ajax']))
						{
							$header .= '<br/><br/>';
							$header .= '<a href="#" onclick="goToLink(\''.$this->the_link.'&amp;display=true'.$this->the_template.'&amp;listreinit=true&amp;start='.$this->the_start.'&amp;order=desc&amp;order_field='.$ordercol[$count_column].'\', \''.$this->params['div_list_ajax'].'\');" title="'._DESC_SORT.'"><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=tri_down.gif" border="0" alt="'._DESC_SORT.'" /></a>';
							$header .= '<a href="#" onclick="goToLink(\''.$this->the_link.'&amp;display=true'.$this->the_template.'&amp;listreinit=true&amp;start='.$this->the_start.'&amp;order=asc&amp;order_field='.$ordercol[$count_column].'\', \''.$this->params['div_list_ajax'].'\');" title="'._ASC_SORT.'"> <img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=tri_up.gif" border="0" alt="'._ASC_SORT.'" /></a>';
						}
						else //Put normal href link
						{
							$header .= '<br/><br/>';
							$header .= '<a href="'.$this->the_link.'&amp;start='.$this->the_start.'&amp;listreinit=true'.$this->the_template.'&amp;order=desc&amp;order_field='.$ordercol[$count_column].'" title="'._DESC_SORT.'"><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=tri_down.gif" border="0" alt="'._DESC_SORT.'" /></a>';
							$header .= '<a href="'.$this->the_link.'&amp;start='.$this->the_start.'&amp;listreinit=true'.$this->the_template.'&amp;order=asc&amp;order_field='.$ordercol[$count_column].'" title="'._ASC_SORT.'"> <img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=tri_up.gif" border="0" alt="'._ASC_SORT.'" /></a>';				
						}
					}

				}
				$header .= '</span>';
				$header .= '</th>';
				$count_td ++;
			}
		}

		//Reserve space for action button 
		for($i = 0;$i < count($this->actions_link);$i++)
		{
			$header .= '<th width="4%" valign="bottom" >&nbsp; </th>';
			$count_td ++;
		}

		//Reserve space for details button
		if(isset($this->params['bool_details']) && $this->params['bool_details'])
		{
			$header .= '<th width="4%" valign="bottom" >&nbsp; </th>';
			$count_td ++;
		}
		
		$header .= '</tr>';
		$header .= '</thead>';
		$header .= '<tbody>';
		$this->countTd = $count_td;
		
		return $header;
	}
	
	//Load css for each line
	private function load_css_line()
	{
		if ($this->line_css == '')
		{
			$this->line_css =  'class="col"';
			
			return $this->line_css;
		}
		elseif ($this->line_css ==  'class="col"')
		{
			$this->line_css = '';
			return $this->line_css;
		}
	}

	//Create content for line
	private function create_content($result, $theline, $listcolumn, $key)
	{
		$content = '';
	    $content .= '<tr '.$this->load_css_line().'>';
		
		$disabled_field = false;
		
		//Disabled radio an checkbox
		if (isset($this->params['disabled_form_field']) && ((isset($this->params['bool_check_form']) && $this->params['bool_check_form']) 
		|| (isset($this->params['bool_radio_form']) && $this->params['bool_radio_form'])))
		{
			$disabled_field = $this->line_is_disabled($result, $theline, $this->params['disabled_form_field'], $this->params['disabled_form_rule']);
		}
		
		//Show checkBox
		if(isset($this->params['bool_check_form']) && $this->params['bool_check_form'])
		{
			 $content .= '<td width="3%"><div align="center">';

			if ($disabled_field)
			{
				$content .= '<img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=cadenas_rouge.png" alt="'._DOC_LOCKED.'" border="0"/>';
			}
			else
			{
				$content .= '<input type="checkbox" name="field[]" class="check" value="'.$result[$theline][0][$key].'" />';
			}
			
			$content .= '</div></td>';
			
		}	//OR Show radio button
		elseif(isset($this->params['bool_radio_form']) && $this->params['bool_radio_form'])
		{
			
			$content .= '<td width="3%"><div align="center">';
			
			if ($disabled_field)
			{
				$content .= '<img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=cadenas_rouge.png" alt="'._DOC_LOCKED.'" border="0"/>';
			}
			else
			{
				$content .= '<input type="radio" name="field" class="check" value="'.$result[$theline][0][$key].'" />';
			}
			
			$content .= '</div></td>';
		}
		
		//Show document icon
		if(isset($this->params['bool_view_document']) && $this->params['bool_view_document'])
		{
			$content .= '<td width="3%">';
	        $content .= '<div align="center">';
		    $content .= '<a href="'.$_SESSION['config']['businessappurl'].'index.php?display=true&amp;dir=indexing_searching&amp;page=view&amp;id='.$result[$theline][0][$key].'" target="_blank" title="'._VIEW_DOC.'">';
			$content .= '<img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=picto_dld.gif" alt="'._VIEW_DOC.'" border="0"/>';
			$content .= '</a>';
	        $content .= '</div>';
	        $content .= '</td>';
		}
		
		//Show the rows
		for($count_column = 0;$count_column < count($listcolumn);$count_column++)
        {
            if($result[$theline][$count_column]['show']==true)
            {
				$class ='';
				
				if (isset($result[$theline][$count_column]['class']) && !empty($result[$theline][$count_column]['class']))
				{ 
					$class ='class="'.$result[$theline][$count_column]['class'].'"';
				}
				
				//If action
				if(isset($this->params['id_action']) && !empty($this->params['id_action']) && $this->params['bool_do_action'] && $disabled_field === false)
				{
					$content .= '<td width="'.$result[$theline][$count_column]['size'].'%" align="'.$result[$theline][$count_column]['align'].'" onclick="valid_form( \'page\', \''.$result[$theline][0]['value'].'\', \''.$this->params['id_action'].'\');" '.$class.'>'.$this->show($this->extended_thisword($result[$theline][$count_column]['value'], $this->what)).'</td>';
				}//Open popup on line click
				elseif($this->params['bool_link_on_line'] && $disabled_field === false)
				{
					$content .= '<td width="'.$result[$theline][$count_column]['size'].'%" align="'.$result[$theline][$count_column]['align'].'" onclick="openpopup(\''.$this->params['link_on_line_destination'].'?id='.$result[$theline][0]['value'].'\');" '.$class.'>'.$this->show($this->extended_thisword($result[$theline][$count_column]['value'], $this->what)).'</td>';
				}
				else//Default
				{
					$content .= '<td width="'.$result[$theline][$count_column]['size'].'%" align="'.$result[$theline][$count_column]['align'].'" '.$class.'>'.$this->show($this->extended_thisword($result[$theline][$count_column]['value'], $this->what)).'</td>';
				}
			}
		}
		
		//Show action buttons
		for($i = 0;$i < count($this->actions_link);$i++)
		{
			$disabled_action = false;
			
			//Disabled action
			if (isset($this->actions_link[$i]['disabled_field']))
			{
				$disabled_action = $this->line_is_disabled($result, $theline, $this->actions_link[$i]['disabled_field'], $this->actions_link[$i]['disabled_rule']);
			}
			
			$content .= '<td width="4%" class="action" align="center" nowrap>';
				
			if (!$disabled_action)
			{
				if (isset($this->actions_link[$i]['switch']) && $this->actions_link[$i]['switch'])
				{
					//Disabled switch
					$disabled_switch = $this->line_is_disabled($result, $theline, $this->actions_link[$i]['disabled_switch_field'], $this->actions_link[$i]['disabled_switch_rule']);

					if (!$disabled_switch)
					{
						//Build href link on
						$href = $this->build_link($result, $theline, $this->actions_link[$i]['href_on']);
						$content .= '<a href="'.$href.'" title="'.$this->actions_link[$i]['tooltip_on'].'" ';
						
						//If javascript alert box
						$str_alert = '';
						if(isset($this->actions_link[$i]['bool_alert_on']) && $this->actions_link[$i]['bool_alert_on'] === true) 
						{
							foreach(array_keys($result[$theline]) as $value)
							{
								if($result[$theline][$value]['column'] == $this->actions_link[$i]['alert_field_on'])
								{
									$str_alert = addslashes($result[$theline][$value]['value']);
									break;
								}
							}
							$str_alert = str_replace("@alert_field", $str_alert, $this->actions_link[$i]['alert_text_on']);
							$content .= ' onclick="return(confirm(\''.$str_alert.'\'));" ';
						}
						
						//Style
						if(isset($this->actions_link[$i]['class_on']))	{ $content .= ' class="'.$this->actions_link[$i]['class_on'].'">';	} else { $content .= '>'; } //End of link
						
						//Image
						if(isset($this->actions_link[$i]['icon_on'])) { $content .= '<img src="'.$this->actions_link[$i]['icon_on'].'" alt="'.$this->actions_link[$i]['tooltip_on'].'" border="0"/>'; }
						
						//Label
						if(isset($this->actions_link[$i]['label_on'])) { $content .= '&nbsp;'.$this->actions_link[$i]['label_on']; }
					
					$content .= '</a>';
					}
					else
					{
						//Build href link off
						$href = $this->build_link($result, $theline, $this->actions_link[$i]['href_off']);
						$content .= '<a href="'.$href.'" title="'.$this->actions_link[$i]['tooltip_off'].'" ';
						
						//If javascript alert box
						$str_alert = '';
						if(isset($this->actions_link[$i]['bool_alert_off']) && $this->actions_link[$i]['bool_alert_off'] === true) 
						{
							foreach(array_keys($result[$theline]) as $value)
							{
								if($result[$theline][$value]['column'] == $this->actions_link[$i]['alert_field_off'])
								{
									$str_alert = addslashes($result[$theline][$value]['value']);
									break;
								}
							}
							$str_alert = str_replace("@alert_field", $str_alert, $this->actions_link[$i]['alert_text_off']);
							$content .= ' onclick="return(confirm(\''.$str_alert.'\'));" ';
						}
						
						//Style
						if(isset($this->actions_link[$i]['class_off']))	{ $content .= ' class="'.$this->actions_link[$i]['class_off'].'">';	} else { $content .= '>'; } //End of link
						
						//Image
						if(isset($this->actions_link[$i]['icon_off'])) { $content .= '<img src="'.$this->actions_link[$i]['icon_off'].'" alt="'.$this->actions_link[$i]['tooltip_off'].'" border="0"/>'; }
						
						//Label
						if(isset($this->actions_link[$i]['label_off'])) { $content .= '&nbsp;'.$this->actions_link[$i]['label_off']; }
						
						$content .= '</a>';
					}
				}
				else
				{
					//Build href link
					$href = $this->build_link($result, $theline, $this->actions_link[$i]['href']);
					
					//Begin of href link
					if(isset($this->actions_link[$i]['bool_popup']) && $this->actions_link[$i]['bool_popup'] === true) //If open link in popup widow
					{
						//Init
						$popup_name = $popup_arguments = '';
						
						if(isset($this->actions_link[$i]['popup_name'])) 
						{
							$popup_name =$this->actions_link[$i]['popup_name'];
						}
						
						if(isset($this->actions_link[$i]['popup_arguments'])) 
						{
							$popup_arguments = $this->actions_link[$i]['popup_arguments'];
						}
						
						$content .= '<a href="javascript://" onclick="openpopup(\''.$href.'\', \''.$popup_name.'\', \''.$popup_arguments.'\');" title="'.$this->actions_link[$i]['tooltip'].'"';
					}
					else
					{
						$content .= '<a href="'.$href.'" title="'.$this->actions_link[$i]['tooltip'].'" ';
					}
					
					//If javascript alert box
					$str_alert_del = '';
					if(isset($this->actions_link[$i]['bool_alert']) && $this->actions_link[$i]['bool_alert'] === true) 
					{
						foreach(array_keys($result[$theline]) as $value)
						{
							if($result[$theline][$value]['column'] == $this->actions_link[$i]['alert_field'])
							{
								$str_alert = addslashes($result[$theline][$value]['value']);
								break;
							}
						}
						$str_alert_del = str_replace("@alert_field", $str_alert, $this->actions_link[$i]['alert_text']);
						$content .= ' onclick="return(confirm(\''.$str_alert_del.'\'));" ';
					}
					
					//Style
					if(isset($this->actions_link[$i]['class']))	{ $content .= ' class="'.$this->actions_link[$i]['class'].'">';	} else { $content .= '>'; } //End of link
					
					//Image
					if(isset($this->actions_link[$i]['icon'])) { $content .= '<img src="'.$this->actions_link[$i]['icon'].'" alt="'.$this->actions_link[$i]['tooltip'].'" border="0"/>'; }
					
					//Label
					if(isset($this->actions_link[$i]['label'])) { $content .= '&nbsp;'.$this->actions_link[$i]['label']; }
					
					$content .= '</a>';
				}
			
			}
			else
			{
				$content .= '&nbsp;';
			}
			$content .= '</td>';
		}
		
		//Show details icon
		if(isset($this->params['bool_details']) && $this->params['bool_details'])
        {
            $content .= '<td width="4%"  align="center">';
			$content .= '<div align="right">';
			
			if(isset($this->params['bool_details_popup']) && $this->params['bool_details_popup'])
			{
				$content .= '<a href="#" OnClick="javascript:window.open(\''.$_SESSION['config']['businessappurl'].'index.php?page=details&amp;dir=indexing_searching&amp;id='.$result[$theline][0][$key].'\');" title="'._DETAILS.'">';
			}
			else
			{
				$content .= '<a href="#" OnClick="javascript:window.top.location=\''.$_SESSION['config']['businessappurl'].'index.php?page=details&amp;dir=indexing_searching&amp;id='.$result[$theline][0][$key].'\'; return false;" title="'._DETAILS.'">';
			}
			
			$content .= '<img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=picto_infos.gif"  alt="'._DETAILS.'" width="25" height="25" border="0" /></a>';
			$content .= '</div>';
            $content .= '</td>';
        }
	
		$content .= '</tr>';
		
		return  $content;
	}
	
	//Create footer
	private function create_footer()
	{
		$footer = '';
	
		$footer .= '</tbody>';
		$footer .= '</table>';
		
		return $footer;
	}
	
	/*
	* @param array : Result of a request
	* @param string : The key used for id in the list
	* @param array : Parameters of the list
		(
		[module] => string : Name of the module where the page is
		[page_name] => string : The calling page
		[page_title] => string : Title of the page to be displayed over the list
		[bool_big_page_title] => boolean : size of the title. If false small size
		[page_picto] => string : Image to be displayed near title
		[bool_show_listletters] => boolean : If true : show list letters, search on the elements of the list by alphabetical filter
		[listletters_what_search] => string : Search field in alphabetical filter
		[bool_show_searchbox] => boolean : If true : show search box, search on the elements of the list possible
		[searchbox_autoCompletionArray] => array : Array of autocompletion values in search box
		[bool_view_document] => boolean : Boolean to view document or not 
		[bool_details] => boolean : Boolean to show the detail page link or not 
		[bool_details_popup] => boolean : Boolean to show the detail page on popup mode (new window) 
		[bool_sort] => boolean : Boolean to show the order icons or not
		[table_css] => string : Css used in the list
		[height] => string : Height of the list
		[lines_to_show]  => string : Number of rows to show in the list
		[bool_legend] => boolean : Show the legend icon and tips
		[legend_title] => string : The title of the legend (in box legend))
		[legend] => string : The legend
		[bool_doc_convert] => boolean : Add convert doc combo
		[bool_template] => boolean : Show template in list
		[template_list] => array : List of templates
		[actual_template] => string : Selected template
		[bool_list_is_ajax] => boolean : True if the list is generated using ajax
		[div_list_ajax] => string : The name of the Div where the list is showed with ajax			
		[bool_check_form] => boolean : Add checkbox to select row (checkbox name : field[])
		[bool_radio_form] => boolean : Add radio button to select row 
		[bool_buttons_form] => boolean : Add buttons to list
		[buttons_form] => array : Array of buttons
		[name_form] => string : Name of the form 
		[class_form] => string : Class of the form 
		[method_form] => string : Method of the select form 
		[action_form] => string : Action of the select form
		[disabled_form_field] => string : Name of the database field used to disabled checkbox or radio input of the list
		[disabled_form_rule] => string : Rule to verify to disabled form element (must return boolean) Use generic @disabled_field parameter
		[hidden_fields_form] => array : Hidden fields in the form
		[bool_do_action] => boolean : If action on list .False by default
		[id_action] => string : Id of the action
		[actions_combo] => array : List of the elements of the actions combo list
		[bool_link_on_line] => boolean : If the click on the line open link. False by default
		[link_on_line_destination] => string : Link to open, completed with the key of the current line($link_on_line_destination?id=key) 
		[filter_list] => array : filters avalaible for the list
		[filter_div]  => string : The name of the Div where the list is filtered with ajax
		
	* @param array : List of actions icon /label
			(
			[disabled_field] => string : Used in disabled rule
			[disabled_rule] => string : To disabled action link (must return boolean). Use generic @disabled_field parameter
			[switch] => bolean : Action icon type switch (on/off)
				//If switch = true
					[disabled_switch_field] => string : Used in disabled switch action
					[disabled_switch_rule] => string : Rule to disabled switch action (must return boolean). Use generic @disabled_field parameter
					[href_on] => string : Link for action on
					[class_on] => string : Css style for action on
					[tooltip_on] => string : Tooltip for action on
					[icon_on] => string : Icon for action on
					[icon_on_alt] => string : Alt text for image on
					[label_on] => string :  Label on
					[bool_alert_on] => boolean : If alert before action on
					[alert_text_on] => string : Text displayed in the alert box. Can use generic @alert_field parameter
					[alert_field_on] => string : Field used in alert box 
					[href_off] => string : Link for action off
					[class_off] => string : Css style for action off
					[tooltip_off] => string : Tooltip for action off
					[icon_off] => string : Icon for action off
					[icon_off_alt] => string : Alt text for image off
					[label_off] => string : Label off
					[bool_alert_off] => boolean :  If alert before action off
					[alert_text_off] => string : Text displayed in the alert box. Can use generic @alert_field parameter
					[alert_field_off] => string : Field used in alert box 
				
				//Else if switch = false (default value)
					[href] => string : Link for action
					[tooltip] => string : Tooltip for action
					[class] => string : Css style for action link
					[icon] => string : Icon for action link
					[icon_alt] => string : Alt text for image
					[label] => string : Label of the link
					[bool_alert] => boolean : If alert before action
					[alert_text] => string : Text displayed in the alert box. Can use generic @alert_field parameter
					[alert_field] => string : Field used in alert box 
					[bool_popup] => boolean : Open popup on action
					[popup_name] => string : Popup name
					[popup_arguments] => string : Popup argument (width, height...)
			)
		)
	*/
	public function extended_list($result, $key, $parameters = array(), $actions=array() )
	{
		//Default values
		if (!isset($parameters['bool_big_page_title'])){ $parameters['bool_big_page_title'] = true; }
		if (!isset($parameters['bool_show_listletters'])){ $parameters['bool_show_listletters'] = false; }
		if (!isset($parameters['name_form'])){ $parameters['name_form']= 'list_form'; }
		if (!isset($parameters['method_form'])){ $parameters['method_form']= 'GET'; }
		if (!isset($parameters['action_form'])){ $parameters['action_form']= '#'; }
		if (!isset($parameters['class_form'])){ $parameters['class_form']= 'forms'; }
		if (!isset($parameters['lines_to_show'])){ $parameters['lines_to_show']=  $_SESSION['config']['nblinetoshow']; }
		if (!isset($parameters['bool_check_form'])){ $parameters['bool_check_form']= false; }
		if (!isset($parameters['bool_radio_form'])){ $parameters['bool_radio_form']= false; }
		if (!isset($parameters['bool_buttons_form'])){ $parameters['bool_buttons_form']= false; }
		if (!isset($parameters['bool_do_action'])){ $parameters['bool_do_action'] = false; }
		if (!isset($parameters['bool_link_on_line'])){ $parameters['bool_link_on_line']= false; }
		if (!isset($parameters['show_searchbox'])){ $parameters['show_searchbox']= false; }
		if (!isset($parameters['bool_add_button'])){ $parameters['bool_add_button']= false; }
		if (!isset($parameters['bool_view_document'])){ $parameters['bool_view_document']= false; }
		if (!isset($parameters['bool_details'])){ $parameters['bool_details']= false; }
		if (!isset($parameters['bool_details_popup'])){ $parameters['bool_details_popup']= false; }
		if (!isset($parameters['bool_sort'])){ $parameters['bool_sort']= false; }
		if (!isset($parameters['bool_legend'])){ $parameters['bool_legend']= false; }
		if (!isset($parameters['bool_page_in_module'])){ $parameters['bool_page_in_module']= true; }
		if (!isset($parameters['bool_list_is_ajax'])){ $parameters['bool_list_is_ajax']= false; }
		if (!isset($parameters['div_list_ajax'])){ $parameters['div_list_ajax'] = ''; }
		if (!isset($parameters['table_css'])){ $parameters['table_css'] = 'listing spec'; }
		if (!isset($parameters['actions_json'])){ $parameters['actions_json'] = '{}'; }
		if (!isset($parameters['bool_show_searchbox'])){ $parameters['bool_show_searchbox'] = true; }
		if (!isset($parameters['bool_doc_convert'])){ $parameters['bool_doc_convert'] = false; }
		if (!isset($parameters['bool_template'])){ $parameters['bool_template'] = false; }
		if (!isset($parameters['searchbox_autoCompletionArray'])){ $parameters['searchbox_autoCompletionArray'] =''; }
		if (!isset($parameters['template_list'])){ $parameters['template_list'] = array(); }
		if (!isset($parameters['bool_filter'])){ $parameters['bool_filter'] = false; }
		if (!isset($parameters['filter_list'])){ $parameters['filter_list'] = array(); }
		if (!isset($parameters['click_line_text'])){ $parameters['click_line_text'] = _CLICK_LINE_TO_MAKE_ACTION; }
		
		
		$core_tools = new core_tools();
		$core_tools->load_lang();
		
		$this->params = $parameters;
		$this->hide_standard_list = $hide_standard_list;
		$this->actions_link = $actions;
		$this->line_css = 'class="col"';
		$this->list_key = $key;
		
		 /*To keep value for extended simples script =>*/ $_SESSION['extended_template']['id_default_action'] = $this->params['id_action'];
		
		//$this->show_array($this->params);
		
		//show the document list in result of the search
		$link="";
		$what = "";
		$count_td = 0;
		
		//$listvalue = array();
		$listcolumn = array();
		$listshow = array();
		$listformat = array();
		$ordercol = array();
	
		// put in tab the different label of the column
		for ($j=0;$j<count($result[0]);$j++)
		{
			array_push($listcolumn,$result[0][$j]["label"]);
			array_push($listshow,$result[0][$j]["show"]);
			array_push($ordercol,$result[0][$j]["order"]);
		}
		
		//$this->show_array($result);
		//$this->show_array($listcolumn);
		//$this->show_array($listshow);
		//$this->show_array($ordercol);
		//$this->show_array($result);
		$func = new functions();
		
		if (isset($parameters['page_parameters']))
		{
			$pos = strpos($parameters['page_parameters'], '&');
			//if my page_parameters string have '&'
			if ($pos !== false)
			{
				//at the firt position
				if ($pos <> 0)
				{
					//And page is called by index page
					if ($parameters['bool_page_in_module'])
					{
						//Add '&' 
						$parameters['page_parameters'] = '&amp;'.$parameters['page_parameters'];
					}
				}
			}
			else //my my page_parameters string dont have '&' at all
			{
				//And page is called by index page
				if ($parameters['bool_page_in_module'])
				{
					//Add '&' 
					$parameters['page_parameters'] = '&amp;'.$parameters['page_parameters'];
				}
			}				
		}
		
		//Page page_name
		if (isset($parameters['page_name']))
		{
			//If page is called in a module by index page
			if ($parameters['bool_page_in_module'] && isset($parameters['module']))
			{
				$link = $_SESSION['config']['businessappurl'].'index.php?page='.$parameters['page_name']."&amp;module=".$parameters['module'].$parameters['page_parameters'];
			}
			elseif(isset($parameters['module']) && !$parameters['bool_page_in_module']) //Else if page is called inside the module
			{
				$link = $_SESSION['urltomodules'].$parameters['module']."/".$parameters['page_name'].".php?".$parameters['page_parameters'];
			}
			else 
			{
				$link = $_SESSION['config']['businessappurl'].'index.php?page='.$parameters['page_name'].$parameters['page_parameters'];
			}
		}
		else//Default link (anchor) to prevent error in link if no page_name or module name
		{
			$link = "#";
		}
		
		//String searched in list
		if(isset($parameters['listletters_what_search']))
		{
			$link.= '&amp;what_search='.$parameters['listletters_what_search'];
		}
		
		//What to search in list
		if(isset($_REQUEST['what']))
		{
			//$link .= "&amp;what=".strip_tags($_REQUEST['what']);
			
			$what = strip_tags($_REQUEST['what']);
		}	

		//Template
		if(isset($_REQUEST['template']))
		{
			$this->the_template = '&amp;template='.$_REQUEST['template'];
		}
		
		$this->the_link = $link;
		
		//Start value for list
		if(isset($_REQUEST['start']) && !empty($_REQUEST['start']))
		{
			//If list is listreinitialize, start to 0
			if(isset($_REQUEST['listreinit']) && !empty($_REQUEST['listreinit']))
			{
				$start = 0;
			}
			else
			{
				$start = strip_tags($_REQUEST['start']);
			}
		}
		else
		{
			$start = 0;
		}
		
		$this->the_start = $start;
		
		//Order direction value
		if(isset($_REQUEST['order']))
		{
			$orderby = strip_tags($_REQUEST['order']);	
		}
		else
		{
			$orderby = 'asc';
		}
		
		//Order database field value
		if(isset($_REQUEST['order_field']))
		{
			$orderfield = strip_tags($_REQUEST['order_field']);
		}
		else
		{
			$orderfield = '';
		}
		
		//Number of line to show in the list (config value)
		$nb_show = $parameters['lines_to_show'];
		
		//Total number of lines
		$nb_total = count($result);
		
		//Number of pages
		$nb_pages = ceil($nb_total/$nb_show);
		//echo 'NB total '.$nb_total.' / NB show: '.$nb_show.' / Pages: '.$nb_pages.' /';
		
		$end = $start + $nb_show;
		if($end > $nb_total)
		{
			$end = $nb_total;
		}
		
		//Doc converter		
		if($parameters['bool_doc_convert'])
		{
			if($core_tools->is_module_loaded("doc_converter"))
			{
				$_SESSION['doc_convert'] = array();
				require_once("modules".DIRECTORY_SEPARATOR."doc_converter".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
				$doc_converter = new doc_converter();
				$disp_dc = $doc_converter->convert_list($result, true);
			}
		}
		
		//List height table style
		$table_style= "";
		if(isset($parameters['height']))
        {
			$table_style= 'style="width:100%;"';
		}
		
		//Template
		if ($parameters['bool_template'])
		{
			$tdeto = $this->display_template_for_user($parameters['template_list'], $link, $parameters['actual_template']);
		}
		
		//template or not template
		$table = $head = $content = $footer = '';
		
		if($parameters['bool_template'] && isset($parameters['actual_template']) && !empty($parameters['actual_template']) && $parameters['actual_template'] <> 'none')
		{
			if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."template".DIRECTORY_SEPARATOR.$parameters['actual_template'].".html"))
			{
				$file = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."template".DIRECTORY_SEPARATOR.$parameters['actual_template'].".html";
			}
			else
			{
				$file = 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."template".DIRECTORY_SEPARATOR.$parameters['actual_template'].".html";
			}
			
			if(file_exists($file))
			{
				$list_trait = $this->get_template($file);
				$tmp = explode("#!#", $list_trait);
				
				//Exploding template to lunch funtion in load_var_sys()
				foreach($tmp as $ac_tmp)
				{
					if (substr($ac_tmp , 0, 5) == "TABLE")
					{
						$table = substr($ac_tmp, 5);
						$true_table = $table;
						//appel des fonctions de remplacement;
						preg_match_all('/##(.*?)##/', $true_table, $out);

						for($i=0;$i<count($out[0]);$i++)
						{
							$remplacement_table = $this->load_var_sys($out[1][$i], $theline, '', '', $including_table);
							$table = str_replace($out[0][$i],$remplacement_table,$true_table);
						}
					}
					elseif (substr($ac_tmp , 0, 4) == "HEAD")
					{
						$head = substr($ac_tmp, 4);
						$true_head = $head;
						preg_match_all('/##(.*?)##/', $true_head, $out);

						for($i=0;$i<count($out[0]);$i++)
						{
							$remplacement_head = $this->load_var_sys($out[1][$i], $theline, '', '', $including_head);
							$true_head = str_replace($out[0][$i],$remplacement_head,$true_head);
						}
						$head = $true_head;
					}
					elseif (substr($ac_tmp , 0, 6) == "RESULT")
					{
						$content = substr($ac_tmp, 6);
					}
					elseif (substr($ac_tmp , 0, 6) == "FOOTER")
					{
						$footer = substr($ac_tmp, 6);
					}
				}
				
				//To load including values template Use for case by exemple
				//##############################################################
				if($core_tools->is_module_loaded("cases") == true)
				{
					$case_file = "modules".DIRECTORY_SEPARATOR."cases".DIRECTORY_SEPARATOR."template_addon".DIRECTORY_SEPARATOR.$parameters['actual_template'].".html";
					if (file_exists($case_file))
					{
						$addon_list_trait = $this->get_template($case_file);
						$addon_tmp = explode("#!#", $addon_list_trait);
						foreach($addon_tmp as $including_file)
						{
							if (substr($including_file , 0, 5) == "TABLE")
								$including_table = substr($including_file, 5);
							if (substr($including_file , 0, 4) == "HEAD")
								$including_head = substr($including_file, 4);
							if (substr($including_file , 0, 6) == "RESULT")
								$including_result = substr($including_file, 6);
							if (substr($including_file , 0, 6) == "FOOTER")
								$including_footer = substr($including_file, 6);
						}
					}
				}
				//##############################################################
			}
			else
			{
				$parameters['bool_template'] = false;
			}
		}
		else
		{	
			$table = $this->create_table ($table_style);
			$head = $this->create_header($result, $listcolumn, $listshow, $ordercol);
			$footer = $this->create_footer();
		}
		
		// if they are more than 1 page we do pagination
		if($nb_pages > 1)
		{
			$next_start = 0;
			$page_dropdown_form = "<form name=\"newpage1\" method=\"get\" >";
			
			//If Ajax link, call javascript redirection function
			if($parameters['bool_list_is_ajax'] && !empty($parameters['div_list_ajax']))
			{
				$page_dropdown_form .= _GO_TO_PAGE." <select name=\"startpage\" onchange=\"goToLink('".$link."&amp;display=true".$this->the_template."&amp;start='+document.newpage1.startpage.value,'".$parameters['div_list_ajax']."');\">";
			}
			else //Put normal href link
			{
				$page_dropdown_form .= _GO_TO_PAGE." <select name=\"startpage\" onchange=\"window.location.href='".$link.$this->the_template."&amp;start='+document.newpage1.startpage.value;\">";
			}
			
			//Option content
			$lastpage = 0;
			for($i = 0;$i <> $nb_pages; $i++)
			{
				$the_line = $i + 1;
				if($start == $next_start)
				{	
					$page_dropdown_form .= "<option value=\"".$next_start."\" selected=\"selected\">".$the_line."</option>";
				
				}
				else
				{
					$page_dropdown_form .= "<option value=\"".$next_start."\">".$the_line."</option>";
				}
				
				$next_start = $next_start + $nb_show;
				$lastpage = $next_start;
			}
			$page_dropdown_form .= "</select></form>" ;
			
			$lastpage = $lastpage - $nb_show;
			$previous = "&nbsp;";
			$next = "";
			if($start > 0)
			{
				$start_prev = $start - $nb_show;
				//If Ajax link, call javascript redirection function
				if($parameters['bool_list_is_ajax'] && !empty($parameters['div_list_ajax']))
				{
					$previous = "&lt; <a href=\"#\" onclick=\"goToLink('".$link."&amp;display=true".$this->the_template."&amp;start=".$start_prev."','".$parameters['div_list_ajax']."');\">"._PREVIOUS."</a> ";
				}
				else //Put normal href link
				{
					$previous = "&lt; <a href=\"".$link.$this->the_template."&amp;start=".$start_prev."\">"._PREVIOUS."</a> ";
				}
			}
			
			if($start <> $lastpage)
			{
				$start_next = $start + $nb_show;
				
				//If Ajax link, call javascript redirection function
				if($parameters['bool_list_is_ajax'] && !empty($parameters['div_list_ajax']))
				{
					$next = " <a href=\"#\" onclick=\"goToLink('".$link."&amp;display=true".$this->the_template."&amp;start=".$start_next."','".$parameters['div_list_ajax']."');\">"._NEXT."</a> >";
				}
				else //Put normal href link
				{
					$next = " <a href=\"".$link.$this->the_template."&amp;start=".$start_next."\">"._NEXT."</a> >";
				}
			}
		
			//$navigation_link = '<div class="block" style="height:30px;" align="center" ><b><div class="list_previous">'.$previous."</div>".$page_dropdown_form."</div><div class='list_next' >".$next."</div></b></div>";
			$navigation_link = '<div class="block" style="height:30px;vertical" align="center" >';
			$navigation_link .= '<table width="100%" border="0"><tr>';
			$navigation_link .= '<td align="center" width="14%" nowrap><b>'.$previous.'</b></td>';
			$navigation_link .= '<td align="center" width="14%" nowrap><b>'.$next.'</b></td>';
			$navigation_link .= '<td width="10px">|</td>';
			$navigation_link .= '<td align="center" width="30%">'.$page_dropdown_form.'</td>';
			$navigation_link .= '<td width="10px">|</td>';
			$navigation_link .= '<td width="210px" align="center">'.$disp_dc.'</td>';
			$navigation_link .= '<td width="10px">|</td>';
			$navigation_link .= '<td align="right" nowrap>'.$tdeto.'</td>';
			$navigation_link .= '</tr></table>';
			$navigation_link .= '</div>';
		}
		else
		{
			if ($nb_total > 0)
			{
				$navigation_link = '<div class="block" style="height:30px;vertical" align="center" >';
				$navigation_link .= '<table width="100%" border="0"><tr>';
				$navigation_link .= '<td align="center" width="14%">&nbsp;</td>';
				$navigation_link .= '<td align="center" width="14%">&nbsp;</td>';
				$navigation_link .= '<td width="10px">&nbsp;</td>';
				$navigation_link .= '<td align="center" width="30%">&nbsp;</td>';
				$navigation_link .= '<td width="10px">|</td>';
				if($parameters['bool_doc_convert']) 
					$navigation_link .= '<td width="210px" align="center">'.$disp_dc.'</td>';
				else 
					$navigation_link .= '<td width="210px" align="center">&nbsp;</td>';
				$navigation_link .= '<td width="10px">|</td>';
				if ($parameters['bool_template'])
					$navigation_link .= '<td align="right" nowrap>'.$tdeto.'</td>'; 
				else 
					$navigation_link .= '<td align="right">&nbsp</td>';
				$navigation_link .= '</tr></table>';
				$navigation_link .= '</div>';
			}
		}

		//Listletter for alphabetical filter
		if($parameters['bool_show_listletters'])
		{
			//$this->extended_listletters($link, $parameters['bool_show_searchbox']);
			$this->extended_listletters($link, $parameters['bool_show_searchbox'],  $parameters['searchbox_autoCompletionArray']);
		}
		
		//Filter
		if($parameters['bool_filter'] && count($parameters['filter_list']) >0)
		{
			$filters = $this->display_filter($parameters['filter_list'], $parameters['actual_template']);
			echo $filters; 
		}
		
		//Ajax div
		if( $parameters['bool_list_is_ajax'] && isset($parameters['div_list_ajax']))
        {
		?>
			<div id="<?php echo $parameters['div_list_ajax'];?>">
		<?php
		}
		
		//Filter div
		if($parameters['bool_filter'] && count($parameters['filter_list']) >0 && isset($parameters['filter_div']))
		{
			?>
			<div id="<?php echo $parameters['filter_div'];?>">
			<?php
		}
		
		//Page picto
		if(isset($parameters['page_picto']))
		{
			$picto_path = '<img src="'.$parameters['page_picto'].'" alt="" class="title_img" /> ';
		}
		
		//Page title
		if(isset($parameters['page_title']))
		{			
			if($parameters['bool_big_page_title'])
			{
				echo '<h1>'.$picto_path.$parameters['page_title'].'</h1>';
			}
			else
			{
				echo '<b>'.$picto_path.$parameters['page_title'].'</b>';
			}
		}
		
		//If one line at least
		if (count($result) > 0)
		{		
			//Navigation  link
			echo $navigation_link; 
			
			$withForm = false;
			//c'est ici qu'il faut faire le test sur l'ensemble des paramètres qui ont besoin du formulaire
			if(
				($parameters['bool_check_form']) ||
				($parameters['bool_radio_form']) ||
				($parameters['bool_buttons_form']) ||
				($parameters['bool_do_action'])
			)
			{
				$withForm = true;
				$formName = $parameters['name_form'];
				$formAction = $parameters['action_form'];
				$formMethod = $parameters['method_form'];
				$formClass = $parameters['class_form'];
				?>
				<script type="text/javascript"> 
				//var arr_actions = <?php echo $parameters['actions_json'];?>;
				var arr_msg_error = {'confirm_title' : '<?php echo addslashes(_ACTION_CONFIRM);?>', 
											'validate' : '<?php echo addslashes(_VALIDATE);?>',
											'cancel' : '<?php echo addslashes(_CANCEL);?>',
											'choose_action' : '<?php echo addslashes(_CHOOSE_ACTION);?>', 
											'choose_one_doc' : '<?php echo addslashes(_CHOOSE_ONE_DOC);?>',
											'choose_one_object' : '<?php echo addslashes(_CHOOSE_ONE_OBJECT);?>'
							};
				valid_form=function(mode, res_id, id_action)
				{
					if(!isAlreadyClick)
					{
						var val = '';
						var action_id = '';
						var table = '';
						var coll_id = '';
						var module = '';
						var thisfrm = document.getElementById('<?php echo $formName; ?>');
						if(thisfrm)
						{
							for(var i=0; i < thisfrm.elements.length; i++)
							{
								
								if(thisfrm.elements[i].name == 'field' && thisfrm.elements[i].checked == true)
								{
									val += thisfrm.elements[i].value+',';
								}
								else if(thisfrm.elements[i].name == 'field[]' && thisfrm.elements[i].checked == true)
								{
									val += thisfrm.elements[i].value+',';
								}
								else if(thisfrm.elements[i].id == 'action')
								{
									action_id = thisfrm.elements[i].options[thisfrm.elements[i].selectedIndex].value;
								}
							/*	else if(form_elem[i].id == 'mode')
								{
									mode = form_elem[i].value;
								}*/
								else if(thisfrm.elements[i].id == 'table')
								{
									table = thisfrm.elements[i].value;
								}
								else if(thisfrm.elements[i].id == 'coll_id')
								{
									coll_id = thisfrm.elements[i].value;
								}
								else if(thisfrm.elements[i].id == 'module')
								{
									module = thisfrm.elements[i].value;
								}
							}
							
							val = val.substr(0, val.length -1);
							var val_frm = {'values' : val,  'action_id' : action_id, 'table' : table, 'coll_id' : coll_id, 'module' : module} 

							if(res_id && res_id != '')
							{
								val_frm['values'] = res_id;
							}
							if(id_action && id_action != '')
							{
								val_frm['action_id'] = id_action;
							}
							//alert(mode + "/" + val_frm['action_id'] + "/" +  val_frm['values'] + "/" +  val_frm['table'] + "/" +  val_frm['module'] + "/" +  val_frm['coll_id']);
							action_send_first_request('<?php $_SESSION['config']['businessappurl'];?>index.php?display=true&page=manage_action&module=core', mode,  val_frm['action_id'], val_frm['values'], val_frm['table'], val_frm['module'], val_frm['coll_id']);	
						}
						else
						{
							alert('Validation form error');
						}
						
						if (mode == 'mass')
						{
							isAlreadyClick = false;
						}
						else
						{
							isAlreadyClick = true;
						}
					}
				}
				<?php
				//Function to see if on box is checked
				if($parameters['bool_check_form'])
				{
				?>
					//Function to check if one checkbox is enabled
					function checkMyBox()
					{
						var thisfrm = document.getElementById('<?php echo $formName; ?>');
						var count = 0;
						
						for(var i=0; i < thisfrm.elements.length; i++)
						{
							if(thisfrm.elements[i].name == 'field' && thisfrm.elements[i].checked == true)
							{
								count++ ;
							}
							else if(thisfrm.elements[i].name == 'field[]' && thisfrm.elements[i].checked == true)
							{
								count++;
							}
						}
						
						if(count==0)
						{
							window.top.document.getElementById('main_error').innerHTML = arr_msg_error['choose_one_object'];
							return false;
						}
					}
				<?php
				}
				?>
				</script>
			<?php 
			}
				?>
				<script type="text/javascript">
				function openpopup(theURL, target, features) { window.open(theURL,target, features);}
				<?php

				if (
					($parameters['bool_list_is_ajax'] && !empty($parameters['div_list_ajax']))
				)
				{
					?>
					function getXhr(){
						var xhr = null; 
						if(window.XMLHttpRequest) // Firefox et autres
							xhr = new XMLHttpRequest();
						else if(window.ActiveXObject){ // Internet Explorer 
							try {
									xhr = new ActiveXObject("Msxml2.XMLHTTP");
							} catch (e){
								xhr = new ActiveXObject("Microsoft.XMLHTTP");
							}
						}
						else { // XMLHttpRequest non supporté par le navigateur 
							alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest..."); 
							xhr = false; 
						} 
						return xhr;
					}
					
					function goToLink(url,divId)
					{
						//alert(divId + ':' +url);
						var xmlHttp = getXhr();
						 // On ouvre la requete vers la page désirée
						 xmlHttp.open("GET", url, true);
						 xmlHttp.onreadystatechange = function(){
						 //alert(xmlHttp.readyState);
						 if (xmlHttp.readyState == 1)
						 {
							document.getElementById(divId).innerHTML = '<img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=loading.gif" border="0" alt="Loading..." />';
						 }
						 if ( xmlHttp.readyState == 4 )
						 {
							// j'affiche dans la DIV spécifiées le contenu retourné par le fichier
							 document.getElementById(divId).innerHTML = xmlHttp.responseText;
						 }
						}
						 // dans le cas du get
					   xmlHttp.send(null);
					}
					<?php
				}
					?>
				</script>
				<?php
				
			//Si affichage du formulaire
			if($withForm)
			{
				?>
				<form name="<?php echo $formName; ?>" id="<?php echo $formName; ?>" action="<?php echo $formAction; ?>" method="<?php echo $formMethod; ?>" class="<?php echo $formClass; ?>" >
				<?php
				if (count($parameters['hidden_fields_form']) >0)
				{
					for ($h =0; $h<count($parameters['hidden_fields_form']); $h++)
					{
						echo '<input type="hidden" id="'.$parameters['hidden_fields_form'][$h]['id'].'" name="'.$parameters['hidden_fields_form'][$h]['name'].'" value="'.$parameters['hidden_fields_form'][$h]['value'].'">'."\n";
					}
				}
			}
			
			//Begin if list height
			if(isset($parameters['height']))
			{
			?>
				<div style="height:<?php echo $parameters['height'];?>;overflow:auto;">
					<div style="height:97%;"><!-- Do not set width! -->
			<?php
			}
			
			//CONTENT!!!!
			$content_list = '';
			for($theline = $start; $theline < $end ; $theline++)
			{
				$this->disabled_field = $this->line_is_disabled ($result, $theline, $this->params['disabled_form_field'], $this->params['disabled_form_rule']);

				if($parameters['bool_template'] && isset($parameters['actual_template']) && !empty($parameters['actual_template']) && $parameters['actual_template'] <> 'none')
				{
					$true_content = $content;
					preg_match_all('/##(.*?)##/', $true_content, $out);
					for($i=0;$i<count($out[0]);$i++)
					{
						$remplacement = $this->load_var_sys($out[1][$i], $theline, $result, $key,$including_result);
						$true_content = str_replace($out[0][$i],$remplacement,$true_content);
					}
					$content_list .= $true_content;
				}
				else
				{
					$content_list .= $this->create_content($result, $theline, $listcolumn, $key);
				}
			}
			
			echo $table.$head.$content_list.$footer;
			
			//End if Height
			if(isset($parameters['height']))
			{
			?>
					</div>
				</div>
			<?php
			}
		
		}
		
		// Displays the text for click on line if needed
		if(count($result) > 0 &&  $parameters['bool_do_action']) 
		{
			echo "<em>".$parameters['click_line_text']."</em><br/>";
		}
		
		//Ajax div
		if($parameters['bool_list_is_ajax'] && isset($parameters['div_list_ajax']))
        {
		?>
			</div>
		<?php
		}
		
		//Filter div
		if($parameters['bool_filter'] && count($parameters['filter_list']) >0 && isset($parameters['filter_div']))
        {
		?>
			</div>
		<?php
		}
		
		
		//Display Add button
		if($parameters['bool_add_button'])
		{
		?>
			<br/><span class="add clearfix"><a href="<?php echo $parameters['add_button_link'];?>"><span><?php echo $parameters['add_button_label'];?></span></a></span>
		<?php
		}

		?>
		<br/>
		<?php
		
		//If form
		if($withForm)
		{
			?>
			<p align="center">
			<?php
			//Action list combo
			if( isset($parameters['actions_combo']) && count($parameters['actions_combo'])>0)
			{
				?>
				<b><?php echo _ACTIONS; ?> :</b>
				<select name="action" id="action">
					<option value=''><?php echo _CHOOSE_ACTION;?></option>
					<?php
					for($ind_act = 0; $ind_act < count($parameters['actions_combo']);$ind_act++)
					{
						?><option value="<?php echo $parameters['actions_combo'][$ind_act]['value'];?>"><?php echo $parameters['actions_combo'][$ind_act]['label'];?></option><?php
					}
					?>
				</select>
				<input class="button" type="button" name="send" id="send" value="<?php echo _VALIDATE; ?>" onclick="valid_form('mass');" />
				<?php
			}
			
			//Show action buttons
			if ($parameters['bool_buttons_form'])
			{
				for($i = 0;$i < count($parameters['buttons_form']);$i++)
				{
				?>
					<input class="button" type="<?php echo $parameters['buttons_form'][$i]['type']; ?>" 
							name="<?php echo $parameters['buttons_form'][$i]['name']; ?>" 
							id="<?php echo $parameters['buttons_form'][$i]['id']; ?>" 
							value="<?php echo $parameters['buttons_form'][$i]['label']; ?>" 
							<?php if( isset($parameters['buttons_form'][$i]['action']) && !empty($parameters['buttons_form'][$i]['action'])){ ?> onclick="javascript: <?php echo $parameters['buttons_form'][$i]['action']; ?>" <?php } ?> />
				<?php
				}
			}
			?>
			</p>
			</form>
			<?php
		}
		?>
		
		<div align="left">
		<?php
		//Doc converter
		if($parameters['bool_doc_convert'])
		{
			if($core_tools->is_module_loaded("doc_converter"))
			{
				$_SESSION['doc_convert'] = array();
				require_once("modules".DIRECTORY_SEPARATOR."doc_converter".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
				$doc_converter = new doc_converter();
				$doc_converter->convert_list($result);
			}
		}
		
		//Legend
		if($parameters['bool_legend'] && isset($parameters['legend']) && !empty($parameters['legend']))
		{
		?>
			<br/><em>
			<a href="#" class="legend"><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_legend.gif"  alt=" <?php echo _LEGEND; ?>" border="0" align="absmiddle" /><?php echo _LEGEND; ?>
			<br/><span><?php echo $parameters['legend_title']; ?><hr/><?php echo $parameters['legend']; ?></span>
			<a>
			</em>
		<?php
		}
		?>
		</div>
	<?php
	}
	
	/**
	* Mark with a color background the word you're searching in the detail of the row
	*
	* @param string $words  haystack word
	* @param string $need  needle string 
	* @param string $maxlen max length of the searched string
	* @return string $words with the needle highlighted 
	*/
	private function extended_thisword($words,$need, $maxlen = 70)
	{
		// mark with a color background the word you're searching in the detail of the row
		if(strlen($words) < $maxlen)
		{
			if (strlen($need) > 3)
			{
				$ar_need = explode(" ", $need);
				
				for($i = 0; $i < count($ar_need); $i++)
				{
					$save_ar_need = "";
					$pos = stripos($words, $ar_need[$i]);
					
					if($pos !== false)
					{
						$save_ar_need = substr($words, $pos, strlen($ar_need[$i]));
					}
					//$words = str_ireplace($ar_need[$i],"<span class=\"thisword\">".$ar_need[$i]."</span>",$words);
					$words = preg_replace("/(".$ar_need[$i].")/i","<span class=\"thisword\">".$save_ar_need."</span>",$words);
				}
			}
		}
		return $words;
	}
	
	/* *
	* show the alphabetical filter in list
	*
	* @param string $link url of the page where the function is used
	* @param boolean $show_searchbox to show the search input box
	* @param array $autoCompletionArray array of autocompletion values
	*/
	private function extended_listletters($link, $show_searchbox = true, $autoCompletionArray = array())
	{
		//$link = preg_replace("&amp;what=[A-Z]", "", $link);
		?>
		<div id="list_letter">
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="forms">
		  <tr>
			<td width="65%" height="30">
				<strong><?php echo _ALPHABETICAL_LIST; ?></strong> : 
                <?php for($i=ord('A'); $i <= ord('Z');$i++)
				{
					//If Ajax
					if($this->params['bool_list_is_ajax'] && !empty($this->params['div_list_ajax']))
					{
					?>
						<a href="javascript://" onclick="goToLink('<?php echo $link;?>&amp;display=true&amp;listreinit=true&amp;what=<?php echo chr($i);?>', '<?php  echo $this->params['div_list_ajax'];?>');"><?php echo chr($i);?></a> 
                    <?php	
					}
					else //Put normal href link
					{
					?>
						<a  href="<?php echo $link;?>&amp;listreinit=true&amp;what=<?php echo chr($i);?>"><?php echo chr($i);?></a> 
                    <?php					
					}	
				}
				
				//If Ajax
				if($this->params['bool_list_is_ajax'] && !empty($this->params['div_list_ajax']))
				{
				?>
					- <a  href="javascript://" onclick="goToLink('<?php echo $link;?>&amp;display=true&amp;listreinit=true&amp;what=', '<?php  echo $this->params['div_list_ajax'];?>');"><?php echo _ALL; ?></a>
			   <?php	
				}
				else //Put normal href link
				{
				?>
					- <a href="<?php echo $link;?>&amp;listreinit=true&amp;what="><?php echo _ALL; ?></a>
                <?php					
				}
				?>
				
			</td>
			<td width="35%" align="right">
			<?php
			if($show_searchbox)
			{
				?>
				<form method="post" name="frmletters" action="<?php echo $link;?>&amp;listreinit=true">
					<input name="what" id="what" type="text" size="15" value="" />
					<?php
					if(count($autoCompletionArray) > 0)
					{
						?>
						<div id="whatList" class="autocomplete"></div>
						<script type="text/javascript">
							initList('what', 'whatList', '<?php echo $autoCompletionArray['list_script_url'];?>', 'what', '<?php echo $autoCompletionArray['number_to_begin'];?>');
						</script>
						<?php
					}
					?>
					<input name="Submit" class="button" type="submit" value="<?php echo _SEARCH;?>"/>
				</form>
                <?php 
			}
			else
			{
				echo "&nbsp;&nbsp;";
			}
			?>
			</td>
		  </tr>
		</table>
		</div>
		<?php
	}
	
	
	/**
	* Create sql clause for order
	*
	* @param string $order sql argument for order (ASC or DESC)
	* @param string $field the field used in order argument
	*/
	public function extended_define_order($order, $field)
	{
		// configure the sql argument order by
		$orderby = "";

		if(isset($field)  && !empty($field))
		{
			if (!isset($order) || empty($order))
			{
				$order = 'asc';
			}
			//
			if ($order == 'asc' || $order == 'desc')
				$orderby = "order by ".$field." ".$order;
		}
		
		return $orderby;
	}
	
	
	/**
	* Create sql clause for alphabetical filter
	*
	* @param string $field the field used 
	* @param string $filter the string to be search
	*/
	public function extended_define_filter($field, $filter)
	{
		// configure the sql filter
		$sqlFilter = "";

		if(isset($filter) && !empty($filter))
		{
			$filter = $this->protect_string_db($filter);
			
			if(isset($field) && !empty($field))
			{
				if($_SESSION['config']['databasetype'] == "POSTGRESQL")
				{
					$sqlFilter = " (".$field." ilike '".strtolower($filter)."%' or ".$field." ilike '".strtoupper($filter)."%') ";
				}
				else
				{
					$sqlFilter = " (".$field." like '".strtolower($filter)."%' or ".$field." like '".strtoupper($filter)."%') ";
				}
			}
		}
		
		return $sqlFilter;
	}
	
	
	/**
	* Builds the basket actions parameters list 
	*
	* @param array $basket_array current_basket array
	* @param array $param_list parameters array used to display the result list
	*/
	public function extended_basket_to_actions_list($basket_array, $param_list)
	{
		//Liste des actions disponibles
		if(count($basket_array['actions']) > 0)
		{
			$param_list['actions_combo'] = array();
			
			for($i=0; $i<count($basket_array['actions']);$i++)
			{
				if($basket_array['actions'][$i]['MASS_USE'] == 'Y')
				{
					array_push($param_list['actions_combo'], array('value' => $basket_array['actions'][$i]['ID'], 'label' => addslashes($basket_array['actions'][$i]['LABEL'])));
				}
			}
		}
		
		//Si au moins une action, afficher les case à cocher
		if(count($param_list['actions_combo']) > 0)
		{
			$param_list['bool_check_form'] = true;
		}
		
		//Action par defaut en cliquant sur une ligne de la liste
		if(!empty($basket_array['default_action']))
		{
			$param_list['bool_do_action'] = true;
			$param_list['id_action'] = $basket_array['default_action'];
		}
		
		return $param_list;
	}
	
	/**
	* show obect to switch in another lists
	*
	*/
	private function display_template_for_user($template_list, $link, $actual_template='')
	{
		/* $template_list : list of template
		 *		 [name] : name of template file
		 * 		 [img] : html img to use for this template
		 * 		 [img_on] : html img to use if template is selected
		 * 		 [label] : label to show in alt tag or title tag
		 */
		 
		$tmpl = '';
		 
		if (count($template_list) > 0)
		{
			if (empty ($actual_template) || $actual_template == 'none')
			{
				$tmpl .= "<img src='".$_SESSION['config']['businessappurl']."static.php?filename=no_template_on.gif' alt='"._NO_TEMPLATE."' >";
			}
			else
			{
				//If Ajax
				if($this->params['bool_list_is_ajax'] && !empty($this->params['div_list_ajax']))
				{
					$tmpl .= '<a href="javascript://" onclick="goToLink(\''.$link.'&amp;display=true&amp;template=none\', \''.$this->params['div_list_ajax'].'\');"><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=no_template.gif" alt="'._ACCESS_LIST_STANDARD.'" ></a>';
				}
				else //Put normal href link
				{
					$tmpl .= "<a href='".$link."&amp;template=none'><img src='".$_SESSION['config']['businessappurl']."static.php?filename=no_template.gif' alt='"._ACCESS_LIST_STANDARD."' ></a>";
				}
			}
			
			foreach ($template_list as $temp)
			{	$img = '';
				if ($actual_template == $temp['name']) { $img = $temp['img_on'];} else { $img = $temp['img'];} 
				
				//If Ajax
				if($this->params['bool_list_is_ajax'] && !empty($this->params['div_list_ajax']))
				{
					$tmpl .= '<a href="javascript://" onclick="goToLink(\''.$link.'&amp;display=true&amp;template='.$temp['name'].'\', \''.$this->params['div_list_ajax'].'\');"><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename='.$img.'" alt="'.$temp['label'].'" title="'.$temp['label'].'"></a>';
				}
				else //Put normal href link
				{
					$tmpl .= "&nbsp;<a href='".$link."&amp;template=".$temp['name']."'> <img src='".$_SESSION['config']['businessappurl']."static.php?filename=".$img."' alt='".$temp['label']."' title='".$temp['label']."'></a>";
				}
			}
		}
		
		return $tmpl;
	}
	
	/**
	* Loads templates into session
	*/
	public function load_templates()
	{
		// Reads the templates.xml file
		// if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'templates.xml'))
		// {
			// $path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'templates.xml';
		// }
		// else
		// {
			// $path = 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'templates.xml';
		// }
		
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."list".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."templates.xml"))
		{
			$path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."list".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."templates.xml";
		}
		else
		{
			$path = "modules".DIRECTORY_SEPARATOR."list".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."templates.xml";
		}
		
		$xmltemplate = simplexml_load_file($path);
		$k = 0;
				
		// Browses the templates in that file  and loads $_SESSION['templates']
		foreach($xmltemplate->TEMPLATE as $TEMPLATE)
		{
			$_SESSION['templates'][$k]['id'] = (string) $TEMPLATE->id;
			
			$tmp = (string) $TEMPLATE->name;
			$tmp2 = $this->retrieve_constant_lang($tmp, 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php');
			if($tmp2 <> false)
			{
				$_SESSION['templates'][$k]['name'] = $tmp2;
			}
			else
			{
				$_SESSION['templates'][$k]['name'] = $tmp;
			}

			$tmp = (string) $TEMPLATE->comment;
			$tmp2 = $this->retrieve_constant_lang($tmp, 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php');
			if($tmp2 <> false)
			{
				$_SESSION['templates'][$k]['comment'] = $tmp2;
			}
			else
			{
				$_SESSION['templates'][$k]['comment'] = $tmp;
			}
			
			$_SESSION['templates'][$k]['img'] = (string) $TEMPLATE->img;
			$_SESSION['templates'][$k]['img_on'] = (string) $TEMPLATE->imgon;
			$_SESSION['templates'][$k]['templatepage'] = (string) $TEMPLATE->templatepage;
			$_SESSION['templates'][$k]['enabled'] = (string) $TEMPLATE->enabled;
			$l=0;
			foreach($TEMPLATE->WHEREAMIUSED as $WHEREAMIUSED)
			{
				$_SESSION['templates'][$k]['whereamiused'][$l]['page'] = (string) $WHEREAMIUSED->page;
				$l++;
			}
			$k++;
		}
	}
	/**
	* Load templates array for current page
	*
	* @param string $whereami name of the current page (same as in xml templates file)
	*/
	public function load_list_template($whereami)
	{
		$templates = array();
		
		for($i=0; $i<count($_SESSION['templates']);$i++)
		{
			if($_SESSION['templates'][$i]['enabled'] == 'true')
			{	
				for($k=0; $k < count($_SESSION['templates'][$i]['whereamiused']);$k++)
				{
					if($_SESSION['templates'][$i]['whereamiused'][$k]['page'] == $whereami)
					{
						$templates[$_SESSION['templates'][$i]['id']]['label'] = $_SESSION['templates'][$i]['name'];
						$templates[$_SESSION['templates'][$i]['id']]['name'] = $_SESSION['templates'][$i]['templatepage'];
						$templates[$_SESSION['templates'][$i]['id']]['img'] = $_SESSION['templates'][$i]['img'];
						$templates[$_SESSION['templates'][$i]['id']]['img_on']  = $_SESSION['templates'][$i]['img_on'];
					}
				}
			}
		}
		
		return $templates;
	}
	
	/**
	* Loads filters into session
	*/
	public function load_filters()
	{
		// Reads the filters.xml file
		// if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'filters.xml'))
		// {
			// $path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'filters.xml';
		// }
		// else
		// {
			// $path = 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'filters.xml';
		// }
		
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."list".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."filters.xml"))
		{
			$path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."list".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."filters.xml";
		}
		else
		{
			$path = "modules".DIRECTORY_SEPARATOR."list".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."filters.xml";
		}
		
		$xmltemplate = simplexml_load_file($path);
		$k = 0;
				
		// Browses the filters in that file  and loads $_SESSION['filters']
		foreach($xmltemplate->FILTER as $FILTER)
		{
			$_SESSION['filters'][$k]['id'] = (string) $FILTER->id;
			
			$tmp = (string) $FILTER->name;
			$tmp2 = $this->retrieve_constant_lang($tmp, 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php');
			if($tmp2 <> false)
			{
				$_SESSION['filters'][$k]['name'] = $tmp2;
			}
			else
			{
				$_SESSION['filters'][$k]['name'] = $tmp;
			}

			$tmp = (string) $FILTER->comment;
			$tmp2 = $this->retrieve_constant_lang($tmp, 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php');
			if($tmp2 <> false)
			{
				$_SESSION['filters'][$k]['comment'] = $tmp2;
			}
			else
			{
				$_SESSION['filters'][$k]['comment'] = $tmp;
			}
			
			$_SESSION['filters'][$k]['filterpage'] = (string) $FILTER->filterpage;
			$_SESSION['filters'][$k]['filterid'] = (string) $FILTER->filterid;
			$_SESSION['filters'][$k]['filtervarname'] = (string) $FILTER->filtervarname;
			$_SESSION['filters'][$k]['nature'] = (string) $FILTER->nature;
			$_SESSION['filters'][$k]['autocomplete'] = (string) $FILTER->autocomplete;
			$_SESSION['filters'][$k]['enabled'] = (string) $FILTER->enabled;
			
			$l=0;
			foreach($FILTER->WHEREAMIUSED as $WHEREAMIUSED)
			{
				$_SESSION['filters'][$k]['whereamiused'][$l]['page'] = (string) $WHEREAMIUSED->page;
				$l++;
			}
			$k++;
		}
	}
	
	/**
	* Load filters array for current page
	*
	* @param string $whereami name of the current page (same as in xml filters file)
	*/
	public function load_list_filter($whereami)
	{
		$filters = array();
		
		for($i=0; $i<count($_SESSION['filters']);$i++)
		{
			if($_SESSION['filters'][$i]['enabled'] == 'true')
			{	
				for($k=0; $k < count($_SESSION['filters'][$i]['whereamiused']);$k++)
				{
					if($_SESSION['filters'][$i]['whereamiused'][$k]['page'] == $whereami)
					{
						$filters[$_SESSION['filters'][$i]['id']]['id'] = $_SESSION['filters'][$i]['id'];
						$filters[$_SESSION['filters'][$i]['id']]['label'] = $_SESSION['filters'][$i]['name'];
						$filters[$_SESSION['filters'][$i]['id']]['filter_page'] = $_SESSION['filters'][$i]['filterpage'];
						$filters[$_SESSION['filters'][$i]['id']]['filter_id'] = $_SESSION['filters'][$i]['filterid'];
						$filters[$_SESSION['filters'][$i]['id']]['filter_var_name'] = $_SESSION['filters'][$i]['filtervarname'];
						$filters[$_SESSION['filters'][$i]['id']]['nature']  = $_SESSION['filters'][$i]['nature'];
					}
				}
			}
		}
		
		return $filters;
	}
	
	/**
	* show filter for user
	*
	*/
	public function display_filter($filters_list, $actual_template='')
	{
		$filt = '';
		$form = '';
		$js_var = '';
		$ajax_var = '';
		
		if (count($filters_list) > 0)
		{
			$filt .= "\n\n".'<div align="center"><form name="form_filter" action="#" method="post">'."\n";
			$filt .= _FILTER_BY.': '."\n";
			$filt .= '<script type="text/javascript">';
			
			$filterId = '';
				
			foreach ($filters_list as $temp)
			{
				$form .= '<input type="hidden" name="'.$temp['filter_id'].'" id="'.$temp['filter_id'].'" value ="">'."\n";
				$js_var .= ' var _'.$temp['filter_id'].' = document.getElementById(\''.$temp['filter_id'].'\').value;'."\n";
				$js_alert .= '_'.$temp['filter_id'].' + \'/\' + ';
				$ajax_var .= ' '.$temp['filter_var_name'].' : _'.$temp['filter_id'].', ';
				
				if ($temp['nature'] == 'list')
				{
					$form .= '<select name="'.$temp['id'].'" id="'.$temp['id'].'" onchange="change(this.options[this.selectedIndex].value, \''.$temp['filter_id'].'\', \''.$_SESSION['config']['businessappurl'].'index.php?display=true&page='.$temp['filter_page'].'\');">'."\n";
					$form .= '	<option value="">'.$temp['label'].'</option>'."\n";
					$filterId = $temp['id'];
					//include('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'filters_list_definition.php');
					include('modules'.DIRECTORY_SEPARATOR.'list'.DIRECTORY_SEPARATOR.'filters_list_definition.php');
					$form .= '</select>'."\n";
				}
				elseif($temp['nature'] == 'text')
				{
					$form .= '<input type="text" name="'.$temp['id'].'" id="'.$temp['id'].'" value="['.$temp['label'].']" onKeyPress="if(event.keyCode == 9) change_'.$temp['id'].'(this.value, \''.$_SESSION['config']['businessappurl'].'index.php?display=true&page='.$temp['filter_page'].'\');">'."\n";
				}
			}
			
			$filt .= "\n\n";
			$filt .= 'function change(valeur, id, path_script)'."\n";
			$filt .= '{'."\n";
			//$filt .= '		alert(valeur +\'/\' + id +\'/\' + path_script);'."\n";
			$filt .= ' var actual_control = document.getElementById(id);'."\n";
			$filt .= ' actual_control.value = valeur;'."\n";
			$filt .= $js_var;
			$filt .= ' var templateVal = \''.$_REQUEST['template'].'\'; '."\n";
			$filt .= ' var startVal = \''.$_REQUEST['start'].'\'; '."\n";
			$filt .= ' var orderVal = \''.$_REQUEST['order'].'\'; '."\n";
			$filt .= ' var order_fieldVal = \''.$_REQUEST['order_field'].'\'; '."\n";
			$filt .= ' if(path_script)'."\n";
			$filt .= ' {'."\n";
			$filt .= ' 	new Ajax.Request(path_script,'."\n";
			$filt .= ' 	{'."\n";
			$filt .= ' 		method:\'post\','."\n";
			$filt .= ' 		parameters: {'.$ajax_var.' start : startVal, order : orderVal, order_field : order_fieldVal, template : templateVal},'."\n";
			$filt .= '		onloading: function(answer)'."\n";
			$filt .= '		{ '."\n";
			$filt .= '			var div_to_fill = $(\''.$this->params['filter_div'].'\');'."\n";
			$filt .= '			div_to_fill.innerHTML = \'<img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=loading.gif" border="0" alt="Loading..." />\';'."\n";
			$filt .= '		},'."\n";
			$filt .= ' 		onSuccess: function(answer){'."\n";
			//$filt .= '		alert('.$js_alert.' \'|\');'."\n";
			$filt .= '			var div_to_fill = $(\''.$this->params['filter_div'].'\');'."\n";
			$filt .= '			div_to_fill.innerHTML = answer.responseText;'."\n";
			$filt .= '			evalMyScripts(\''.$this->params['filter_div'].'\');'."\n";
			$filt .= ' 		},'."\n";
			$filt .= ' 		onFailure: function(){ }'."\n";
			$filt .= ' 	});'."\n";
			$filt .= ' }'."\n";
			$filt .= '}'."\n";
			
			$filt .= '</script>';
			$form .= ' <input type="button" class="button" value="'._CLEAR_SEARCH.'" onclick="javascript:window.location.href=\''.$this->the_link.'&listlistreinit=true\';">';
		
			$filt .= $form;
			$filt .= '</form><div><br/>';
		}
		
		return $filt;
	}
}
?>

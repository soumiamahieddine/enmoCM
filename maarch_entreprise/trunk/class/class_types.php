<?php
/**
* Types Class
*
* Contains all the function to manage the doctypes
*
* @package  Maarch LetterBox 1.0
* @version 2.0
* @since 10/2005
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*
*/

/**
* Class types: Contains all the function to manage the doctypes
*
* @author  Claire Figueras  <dev@maarch.org>
* @license GPL
* @package Maarch LetterBox 1.0
* @version 1.1
*/
class types extends dbquery
{

	/**
	* Form to add, modify or propose a doc type
	*
	* @param string $mode val, up or prop
	* @param integer $id type identifier, empty by default
	*/
	public function formtype($mode,$id = "")
	{
		// form to add, modify or proposale a doc type
		$func = new functions();
		$core_tools = new core_tools();
		require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
		$sec = new security();
		$state = true;
		if(!isset($_SESSION['m_admin']['doctypes']))
		{
			$this->cleartypeinfos();
		}
		if($mode <> "prop" && $mode <> "add")
		{
			$this->connect();
			$this->query("select * from ".$_SESSION['tablename']['doctypes']." where type_id = ".$id."");
			if($this->nb_result() == 0)
			{
				$_SESSION['error'] = _DOCTYPE.' '._ALREADY_EXISTS;
				$state = false;
			}
			else
			{
				$_SESSION['m_admin']['doctypes'] = array();
				$line = $this->fetch_object();
				$_SESSION['m_admin']['doctypes']['TYPE_ID'] = $line->type_id;
				$_SESSION['m_admin']['doctypes']['COLL_ID'] = $line->coll_id;
				$_SESSION['m_admin']['doctypes']['COLL_LABEL'] = $_SESSION['m_admin']['doctypes']['COLL_ID'];
				for($i=0; $i< count($_SESSION['collections']); $i++)
				{
					if($_SESSION['collections'][$i]['id'] == $_SESSION['m_admin']['doctypes']['COLL_ID'])
					{
						$_SESSION['m_admin']['doctypes']['COLL_LABEL'] = $_SESSION['collections'][$i]['label'];
						break;
					}
				}
				$_SESSION['m_admin']['doctypes']['LABEL'] = $this->show_string($line->description);
				$_SESSION['m_admin']['doctypes']['SUB_FOLDER'] = $line->doctypes_second_level_id;
				$_SESSION['m_admin']['doctypes']['VALIDATE'] = $line->enabled;
				$_SESSION['m_admin']['doctypes']['TABLE'] = $line->coll_id;
				$_SESSION['m_admin']['doctypes']['ACTUAL_COLL_ID'] = $line->coll_id;
				$_SESSION['m_admin']['doctypes']['indexes'] = $this->get_indexes($line->type_id, $line->coll_id, 'minimal');
				$_SESSION['m_admin']['doctypes']['mandatory_indexes'] = $this->get_mandatory_indexes($line->type_id, $line->coll_id);

				$_SESSION['service_tag'] = 'doctype_up';
				$core_tools->execute_modules_services($_SESSION['modules_services'], 'doctype_up', "include");
				$core_tools->execute_app_services($_SESSION['app_services'], 'doctype_up', 'include');
			}
		}
		else // mode = add
		{
			$_SESSION['m_admin']['doctypes']['indexes'] = array();
			$_SESSION['m_admin']['doctypes']['mandatory_indexes'] = array();
			$_SESSION['service_tag'] = 'doctype_add';
			echo $core_tools->execute_modules_services($_SESSION['modules_services'], 'doctype_up', "include");
			$core_tools->execute_app_services($_SESSION['app_services'], 'doctype_up', 'include');
			$_SESSION['service_tag'] = '';
		}
		?>
		<h1><img src="<? echo $_SESSION['config']['img'];?>/manage_doctypes_b.gif" alt="" />
			<?
            if($mode == "up")
            {
                echo _DOCTYPE_MODIFICATION;
            }
            elseif($mode == "add")
            {
                echo _ADD_DOCTYPE;
            }
            ?>
        </h1>
		<div id="inner_content" class="clearfix">
			<?
			if($state == false)
			{
				echo "<br /><br /><br /><br />"._DOCTYPE.' '._UNKOWN."<br /><br /><br /><br />";
			}
			else
			{
				$array_coll = $sec->retrieve_insert_collections();
				?>
				<br/><br/>
					<form name="frmtype" id="frmtype" method="post" action="<? echo $_SESSION['config']['businessappurl'];?>index.php?page=types_up_db" class="forms">
					<input type="hidden" name="order" id="order" value="<?php echo $_REQUEST['order'];?>" />
					<input type="hidden" name="order_field" id="order_field" value="<?php echo $_REQUEST['order_field'];?>" />
					<input type="hidden" name="what" id="what" value="<?php echo $_REQUEST['what'];?>" />
					<input type="hidden" name="start" id="start" value="<?php echo $_REQUEST['start'];?>" />
				<div class="block">

						<input  type="hidden" name="mode" value="<? echo $mode; ?>" />
						<p>
							<label><? echo _ATTACH_SUBFOLDER;?> : </label>
							<select name="sous_dossier" id="sous_dossier" class="listext" onchange="">
								<option value=""><? echo _CHOOSE_SUBFOLDER;?></option>
								<?
								for($i=0; $i< count($_SESSION['sous_dossiers']); $i++)
								{
									?>
										<option value="<? echo $_SESSION['sous_dossiers'][$i]['ID']; ?>" <? if($_SESSION['sous_dossiers'][$i]['ID'] == $_SESSION['m_admin']['doctypes']['SUB_FOLDER']) { echo "selected=\"selected\"" ;}?>><? echo $_SESSION['sous_dossiers'][$i]['LABEL']; ?></option>
									<?
								}
								?>
							</select>
						</p>

						<p>
							<label for="collection"><?php  echo _COLLECTION;?> : </label>
                      		<!--<select name="collection" id="collection" onchange="get_opt_index('<?php echo 
                      		$_SESSION['config']['businessappurl'];?>admin/architecture/types/get_index.php', this.options[this.options.selectedIndex].value);">-->
                      		<select name="collection" id="collection" onchange="get_opt_index('<?php echo 
                      		$_SESSION['config']['businessappurl'];?>index.php?display=true&page=get_index', this.options[this.options.selectedIndex].value);">
                        		<option value="" ><?php  echo _CHOOSE_COLLECTION;?></option>
                            <?php  for($i=0; $i<count($array_coll);$i++)
							{
							?>
                            	<option value="<?php  echo $array_coll[$i]['id'];?>" <?php  if($_SESSION['m_admin']['doctypes']['COLL_ID'] == $array_coll[$i]['id']){ echo 'selected="selected"';}?> ><?php  echo $array_coll[$i]['label'];?></option>
                            <?php
							}
							?>
                       	 </select>
                   	 </p>
					<?php
					if($mode == "up")
					{
						?>
						<p>
							<label for="id"><? echo _ID;?> : </label>
	                        <input type="text" class="readonly" readonly="readonly" name="idbis" value="<?  echo $id;?>" />
	                        <input type="hidden" name="id" value="<? echo $id; ?>" />
	                    </p>
						<?
					}
					?>
					<p>
						<label for="label"><? echo _WORDING; ?> : </label>
						<input name="label" type="text" class="textbox" id="label" value="<? echo $func->show($_SESSION['m_admin']['doctypes']['LABEL']); ?>"/>
					</p>
					<?php
					$_SESSION['service_tag'] = 'frm_doctype';
					$core_tools->execute_app_services($_SESSION['app_services'], 'doctype_up', 'include');

					?>
					</div>
					<div class="block_end">&nbsp;</div>
					<br/>
					<?

					$core_tools->execute_modules_services($_SESSION['modules_services'], 'doctype_up', "include");
					$_SESSION['service_tag'] = '';
					?>

	                <div id="opt_index"></div>
					<p class="buttons">
						<?
						if($mode == "up")
						{
							?>
							<input class="button" type="submit" name="Submit" value="<? echo _MODIFY_DOCTYPE; ?>"/>
							<?
						}
						elseif($mode == "add")
						{
							?>
							<input type="submit" class="button"  name="Submit" value="<? echo _ADD_DOCTYPE; ?>" />
							<?
						}
						?>
						 <input type="button" class="button"  name="cancel" value="<?php echo _CANCEL; ?>" onclick="javascript:window.location.href='<? echo $_SESSION['config']['businessappurl'];?>index.php?page=types';"/>
					</p>
                </form>
                </div>
                <script type="text/javascript">
                var coll_list = $('collection');
               // get_opt_index('<?php echo $_SESSION['config']['businessappurl'];?>admin/architecture/types/get_index.php', coll_list.options[coll_list.options.selectedIndex].value);
                get_opt_index('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=get_index', coll_list.options[coll_list.options.selectedIndex].value);
                </script>
			<?
			}
			?>
		</div>
	<?
	}

	/**
	* Checks the formtype data
	*/
	private function typesinfo()
	{
		$core_tools = new core_tools();
		$func = new functions();
		if(!isset($_REQUEST['mode']))
		{
			$_SESSION['error'] = _UNKNOWN_PARAM."<br />";
		}

		if(isset($_REQUEST['label']) && !empty($_REQUEST['label']))
		{
			$_SESSION['m_admin']['doctypes']['LABEL'] = $func->wash($_REQUEST['label'], "no", _THE_WORDING, 'yes', 0, 255);
		}
		else
		{
			$_SESSION['error'] .= _WORDING.' '._IS_EMPTY;
		}

		$_SESSION['service_tag'] = "doctype_info";
		echo $core_tools->execute_modules_services($_SESSION['modules_services'], 'doctype_info', "include");
		$core_tools->execute_app_services($_SESSION['app_services'], 'doctype_up', 'include');
		$_SESSION['service_tag'] = '';
		if(!isset($_REQUEST['collection']) || empty($_REQUEST['collection']))
		{
			$_SESSION['error'] .= _COLLECTION. ' '._IS_MANDATORY.'.<br/>';
		}
		else
		{
			$_SESSION['m_admin']['doctypes']['COLL_ID'] = $_REQUEST['collection'];
			$_SESSION['m_admin']['doctypes']['indexes'] = array();
			$_SESSION['m_admin']['doctypes']['mandatory_indexes'] = array();
			for($i=0; $i<count($_REQUEST['fields']);$i++)
			{
				array_push($_SESSION['m_admin']['doctypes']['indexes'], $_REQUEST['fields'][$i]);
			}
			for($i=0; $i<count($_REQUEST['mandatory_fields']);$i++)
			{
				if(!in_array($_REQUEST['mandatory_fields'][$i], $_SESSION['m_admin']['doctypes']['indexes']))
				{
					$_SESSION['error'] .= _IF_CHECKS_MANDATORY_MUST_CHECK_USE;
				}
				array_push($_SESSION['m_admin']['doctypes']['mandatory_indexes'], $_REQUEST['mandatory_fields'][$i]);
			}
		}
		if(!isset($_REQUEST['sous_dossier']) || empty($_REQUEST['sous_dossier']))
		{
			$_SESSION['error'] .= _THE_SUBFOLDER. ' '._IS_MANDATORY.'.<br/>';
		}
		else
		{
			$_SESSION['m_admin']['doctypes']['SUB_FOLDER'] = $func->wash($_REQUEST['sous_dossier'], "no", _THE_SUBFOLDER);
			$this->connect();
			$this->query("select doctypes_first_level_id as id from ".$_SESSION['tablename']['doctypes_second_level']." where doctypes_second_level_id = ".$_REQUEST['sous_dossier']);
			$res = $this->fetch_object();
			$_SESSION['m_admin']['doctypes']['STRUCTURE'] = $res->id;
		}
		$_SESSION['m_admin']['doctypes']['order'] = $_REQUEST['order'];
		$_SESSION['m_admin']['doctypes']['order_field'] = $_REQUEST['order_field'];
		$_SESSION['m_admin']['doctypes']['what'] = $_REQUEST['what'];
		$_SESSION['m_admin']['doctypes']['start'] = $_REQUEST['start'];
	}

	/**
	* Modify, add or validate a doctype
	*/
	public function uptypes()
	{
		// modify, add or validate a doctype
		$core_tools = new core_tools();
		$this->typesinfo();
		$order = $_SESSION['m_admin']['doctypes']['order'];
		$order_field = $_SESSION['m_admin']['doctypes']['order_field'];
		$what = $_SESSION['m_admin']['doctypes']['what'];
		$start = $_SESSION['m_admin']['doctypes']['start'];
		if(!empty($_SESSION['error']))
		{
			if($_REQUEST['mode'] == "up")
			{
				if(!empty($_SESSION['m_admin']['doctypes']['TYPE_ID']))
				{
				?><script language="javascript" type="text/javascript">window.top.location.href='<? echo $_SESSION['config']['businessappurl']."index.php?page=types_up&id=".$_SESSION['m_admin']['doctypes']['TYPE_ID'];?>';</script>
                <?
					exit();
				}
				else
				{
				?>
               	 <script language="javascript" type="text/javascript">window.top.location.href='<? echo $_SESSION['config']['businessappurl']."index.php?page=types&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;?>';</script>
                <?
					exit();
				}
			}
			elseif($_REQUEST['mode'] == "add" )
			{
			?> <script language="javascript" type="text/javascript">window.top.location.href='<? echo $_SESSION['config']['businessappurl']."index.php?page=types_add";?>';</script>
                <?
				exit();
			}
		}
		else
		{
			$this->connect();
			if($_REQUEST['mode'] <> "prop" && $_REQUEST['mode'] <> "add")
			{
				$this->query("update ".$_SESSION['tablename']['doctypes']." set description = '".$this->protect_string_db($_SESSION['m_admin']['doctypes']['LABEL'])."' , doctypes_first_level_id = ".$_SESSION['m_admin']['doctypes']['STRUCTURE'].", doctypes_second_level_id = ".$_SESSION['m_admin']['doctypes']['SUB_FOLDER'].", enabled = 'Y', coll_id = '".$this->protect_string_db($_SESSION['m_admin']['doctypes']['COLL_ID'])."' where type_id = ".$_SESSION['m_admin']['doctypes']['TYPE_ID']."");
				//$this->show_array($_SESSION['m_admin']['doctypes']['indexes']);

				$this->query("delete from ".$_SESSION['tablename']['doctypes_indexes']." where coll_id = '".$this->protect_string_db($_SESSION['m_admin']['doctypes']['COLL_ID'])."' and type_id = ".$_SESSION['m_admin']['doctypes']['TYPE_ID']);
				//$this->show();


				for($i=0; $i<count($_SESSION['m_admin']['doctypes']['indexes']);$i++)
				{
					$mandatory = 'N';
					if(in_array($_SESSION['m_admin']['doctypes']['indexes'][$i], $_SESSION['m_admin']['doctypes']['mandatory_indexes'] ))
					{
						$mandatory = 'Y';
					}
					$this->query("insert into ".$_SESSION['tablename']['doctypes_indexes']." (coll_id, type_id, field_name, mandatory) values('".$this->protect_string_db($_SESSION['m_admin']['doctypes']['COLL_ID'])."', ".$_SESSION['m_admin']['doctypes']['TYPE_ID'].", '".$_SESSION['m_admin']['doctypes']['indexes'][$i]."', '".$mandatory."')");
				}

				$_SESSION['service_tag'] = "doctype_updatedb";
				$core_tools->execute_modules_services($_SESSION['modules_services'], 'doctype_load_db', "include");
				$core_tools->execute_app_services($_SESSION['app_services'], 'doctype_up', 'include');
				$_SESSION['service_tag'] = '';
				if($_REQUEST['mode'] == "up")
				{
					$_SESSION['error'] = _DOCTYPE_MODIFICATION;
					if($_SESSION['history']['doctypesup'] == "true")
					{
						require("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
						$users = new history();
						$users->add($_SESSION['tablename']['doctypes'], $_SESSION['m_admin']['doctypes']['TYPE_ID'],"UP",_DOCTYPE_MODIFICATION." : ".$_SESSION['m_admin']['doctypes']['LABEL'], $_SESSION['config']['databasetype']);
					}
				}

				$this->cleartypeinfos();
				?>
				<script language="javascript" type="text/javascript">window.top.location.href='<? echo $_SESSION['config']['businessappurl']."index.php?page=types&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;?>';</script>
                <?
				exit();
			}
			else
			{
				require("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
				$users = new history();
				if( $_REQUEST['mode'] == "add")
				{
					$tmp = $this->protect_string_db($_SESSION['m_admin']['doctypes']['LABEL']);

					$this->query("insert into ".$_SESSION['tablename']['doctypes']." (coll_id, description, doctypes_first_level_id, doctypes_second_level_id,  enabled ".$_SESSION['m_admin']['doctypes']['custom_query_insert_colums'].") VALUES ('".$_SESSION['m_admin']['doctypes']['COLL_ID']."', '".$tmp."',".$_SESSION['m_admin']['doctypes']['STRUCTURE'].",".$_SESSION['m_admin']['doctypes']['SUB_FOLDER'].", 'Y' ".$_SESSION['m_admin']['doctypes']['custom_query_insert_values'].")");
					//$this->show();

					//exit();
					$this->query("select type_id from ".$_SESSION['tablename']['doctypes']." where coll_id = '".$_SESSION['m_admin']['doctypes']['COLL_ID']."' and description = '".$tmp."' and doctypes_first_level_id = ".$_SESSION['m_admin']['doctypes']['STRUCTURE']." and doctypes_second_level_id = ".$_SESSION['m_admin']['doctypes']['SUB_FOLDER']);
					//$this->show();
					$res = $this->fetch_object();
					$_SESSION['m_admin']['doctypes']['TYPE_ID'] = $res->type_id;

					for($i=0; $i<count($_SESSION['m_admin']['doctypes']['indexes']);$i++)
					{
						$mandatory = 'N';
						if(in_array($_SESSION['m_admin']['doctypes']['indexes'][$i], $_SESSION['m_admin']['doctypes']['mandatory_indexes'] ))
						{
							$mandatory = 'Y';
						}
						$this->query("insert into ".$_SESSION['tablename']['doctypes_indexes']." (coll_id, type_id, field_name, mandatory) values('".$this->protect_string_db($_SESSION['m_admin']['doctypes']['COLL_ID'])."', ".$_SESSION['m_admin']['doctypes']['TYPE_ID'].", '".$_SESSION['m_admin']['doctypes']['indexes'][$i]."', '".$mandatory."')");
					}

					$_SESSION['service_tag'] = "doctype_insertdb";
					echo $core_tools->execute_modules_services($_SESSION['modules_services'], 'doctype_load_db', "include");
					$core_tools->execute_app_services($_SESSION['app_services'], 'doctype_up', 'include');
					$_SESSION['service_tag'] = '';

					if($_SESSION['history']['doctypesadd'] == "true")
					{
						$users->add($_SESSION['tablename']['doctypes'],$res->type_id,"ADD", _DOCTYPE_ADDED." : ".$_SESSION['m_admin']['doctypes']['LABEL'],$_SESSION['config']['databasetype']);
					}
				}
				$this->cleartypeinfos();
				//header("location: ".$url);
				?> <script language="javascript" type="text/javascript">window.top.location.href='<? echo $_SESSION['config']['businessappurl']."index.php?page=types&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;?>';</script>
                <?
				exit();
			}
		}
	}

	/**
	* Clear the session variable for the doctypes
	*/
	private function cleartypeinfos()
	{
		// clear the session variable for the doctypes
		unset($_SESSION['m_admin']);
	}


	/**
	* Return in an array all enabled doctypes for a given collection
	*
	* @param string $coll_id Collection identifier
	*/
	public function getArrayTypes($coll_id)
	{
		$types = array();
		if(empty($coll_id))
		{
			return $types;
		}

		$this->connect();
		$this->query("select type_id, description from ".$_SESSION['tablename']['doctypes']." where coll_id = '".$coll_id."' and enabled = 'Y'");
		while($res = $this->fetch_object())
		{
			array_push($types, array('ID' => $res->type_id, 'LABEL' => $this->show_string($res->description)));
		}
		return $types;
	}

	/**
	* Returns in an array all enabled doctypes for a given collection with the structure
	*
	* @param string $coll_id Collection identifier
	*/
	public function getArrayStructTypes($coll_id)
	{
		$this->connect();
		$level1 = array();
		$this->query("select d.type_id, d.description, d.doctypes_first_level_id, d.doctypes_second_level_id, dsl.doctypes_second_level_label, dfl.doctypes_first_level_label from ".$_SESSION['tablename']['doctypes']." d, ".$_SESSION['tablename']['doctypes_second_level']." dsl, ".$_SESSION['tablename']['doctypes_first_level']." dfl where coll_id = 'letterbox_coll' and d.enabled = 'Y' and d.doctypes_second_level_id = dsl.doctypes_second_level_id and d.doctypes_first_level_id = dfl.doctypes_first_level_id and dsl.enabled = 'Y' and dfl.enabled = 'Y' order by dfl.doctypes_first_level_label,dsl.doctypes_second_level_label, d.description ");
		$last_level1 = '';
		$nb_level1 = 0;
		$last_level2 = '';
		$nb_level2 = 0;
		while($res = $this->fetch_object())
		{
			//var_dump($res);
			if($last_level1 <> $res->doctypes_first_level_id)
			{
				array_push($level1, array('id' => $res->doctypes_first_level_id, 'label' => $this->show_string($res->doctypes_first_level_label), 'level2' => array(array('id' => $res->doctypes_second_level_id, 'label' => $this->show_string($res->doctypes_second_level_label), 'types' => array(array('id' => $res->type_id, 'label' => $this->show_string($res->description)))))));
				$last_level1 = $res->doctypes_first_level_id;
				$nb_level1 ++;
				$last_level2 = $res->doctypes_second_level_id;
				$nb_level2 = 1;
			}
			elseif($last_level2 <> $res->doctypes_second_level_id)
			{
				array_push($level1[$nb_level1 -1]['level2'], array('id' => $res->doctypes_second_level_id, 'label' => $this->show_string($res->doctypes_second_level_label), 'types' => array(array('id' => $res->type_id, 'label' => $this->show_string($res->description)))));
				$last_level2 = $res->doctypes_second_level_id;
				$nb_level2 ++;
			}
			else
			{
			//	echo 'test '.$nb_level2;
				array_push($level1[$nb_level1 -1]['level2'][$nb_level2 -1]['types'], array('id' => $res->type_id, 'label' => $this->show_string($res->description)));
			}
			//$this->show_array($level1);
		}
		return $level1;
	}

	/**
	* Returns in an array all indexes possible for a given collection
	*
	* @param string $coll_id Collection identifier
	* @return array $indexes[$i]
	* 					['column'] : database field of the index
	* 					['label'] : Index label
	* 					['type'] : Index type ('date', 'string', 'integer' or 'float')
	* 					['img'] : url to the image index
	*/
	public function get_all_indexes($coll_id)
	{
		require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_security.php');
		$sec = new security();
		$ind_coll = $sec->get_ind_collection($coll_id);
		$xmlfile = simplexml_load_file('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR.$_SESSION['collections'][$ind_coll]['index_file']);
		$path_lang = 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php';
		$indexes = array();
		foreach($xmlfile->INDEX as $item)
		{

			$tmp = (string) $item->label;
			//echo $tmp;
			$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
			if($tmp2 <> false)
			{
				$label = $tmp2;
			}
			else
			{
				$label = $tmp;
			}
			$img = (STRING) $item->img;
			if(isset($item->default_value) && !empty($item->default_value))
			{
				$tmp = (string) $item->default_value;
				$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
				if($tmp2 <> false)
				{
					$default = $tmp2;
				}
				else
				{
					$default= $tmp;
				}
			}
			else
			{
				$default = false;
			}
			if(isset($item->values_list))
			{
				$values = array();
				$list = $item->values_list ;
				foreach($list->value as $val)
				{
					$tmp = (string) $val->label;
					$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
					if($tmp2 <> false)
					{
						$label_val = $tmp2;
					}
					else
					{
						$label_val = $tmp;
					}
					array_push($values, array('id' => (string) $val->id, 'label' => $label_val));
				}
				$arr_tmp = array('column' => (STRING) $item->column, 'label' => $label, 'type' => (STRING) $item->type, 'img' => $_SESSION['config']['businessappurl'].'img/'.$img, 'type_field' => 'select', 'values' => $values, 'default_value' => $default);
			}
			else
			{
				$arr_tmp = array('column' => (STRING) $item->column, 'label' => $label, 'type' => (STRING) $item->type, 'img' => $_SESSION['config']['businessappurl'].'img/'.$img, 'type_field' => 'input', 'default_value' => $default);
			}
			array_push($indexes, $arr_tmp);
		}
		return $indexes;
	}

	/**
	* Returns in an array all indexes for a doctype
	*
	* @param string $type_id Document type identifier
	* @param string $coll_id Collection identifier
	* @param string $mode Mode 'full' or 'minimal', 'full' by default
	* @return array array of the indexes, depends on the chosen mode :
	*  		1) mode = 'full' : $indexes[field_name] :  the key is the field name in the database
	* 										['label'] : Index label
	* 										['type'] : Index type ('date', 'string', 'integer' or 'float')
	* 										['img'] : url to the image index
	* 		2) mode = 'minimal' : $indexes[$i] = field name in the database
	*/
	public function get_indexes($type_id, $coll_id, $mode= 'full')
	{
		$fields = array();
		$this->connect();
		$this->query("select field_name from ".$_SESSION['tablename']['doctypes_indexes']." where coll_id = '".$coll_id."' and type_id = ".$type_id);
		//$this->show();

		while($res = $this->fetch_object())
		{
			array_push($fields,$res->field_name );
		}
		if($mode == 'minimal')
		{
			return $fields;
		}

		$indexes = array();
		require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_security.php');
		$sec = new security();
		$ind_coll = $sec->get_ind_collection($coll_id);
		$xmlfile = simplexml_load_file('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR.$_SESSION['collections'][$ind_coll]['index_file']);
		$path_lang = 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php';
		foreach($xmlfile->INDEX as $item)
		{
			$tmp = (string) $item->label;
			$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang );
			if($tmp2 <> false)
			{
				$label = $tmp2;
			}
			else
			{
				$label = $tmp;
			}
			$col = (STRING) $item->column;
			$img = (STRING) $item->img;
			if(isset($item->default_value) && !empty($item->default_value))
			{
				$tmp = (string) $item->default_value;
				$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
				if($tmp2 <> false)
				{
					$default = $tmp2;
				}
				else
				{
					$default= $tmp;
				}
			}
			else
			{
				$default = false;
			}
			if(in_array($col, $fields))
			{
				if(isset($item->values_list))
				{
					$values = array();
					$list = $item->values_list ;
					foreach($list->value as $val)
					{
						$tmp = (string) $val->label;
						$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
						if($tmp2 <> false)
						{
							$label_val = $tmp2;
						}
						else
						{
							$label_val = $tmp;
						}
						array_push($values, array('id' => (string) $val->id, 'label' => $label_val));
					}
					$indexes[$col] = array( 'label' => $label, 'type' => (STRING) $item->type, 'img' => $_SESSION['config']['businessappurl'].'img/'.$img, 'type_field' => 'select', 'values' => $values, 'default_value' => $default);
				}
				else
				{
					$indexes[$col] = array( 'label' => $label, 'type' => (STRING) $item->type, 'img' => $_SESSION['config']['businessappurl'].'img/'.$img, 'type_field' => 'input', 'default_value' => $default);
				}
			}
		}
		return $indexes;
	}

	/**
	* Returns in an array all manadatory indexes possible for a given type
	*
	* @param string $type_id Document type identifier
	* @param string $coll_id Collection identifier
	* @return array Array of the manadatory indexes, $indexes[$i] = field name in the db
	*/
	public function get_mandatory_indexes($type_id, $coll_id)
	{
		$fields = array();
		$this->connect();
		$this->query("select field_name from ".$_SESSION['tablename']['doctypes_indexes']." where coll_id = '".$coll_id."' and type_id = ".$type_id." and mandatory = 'Y'");

		while($res = $this->fetch_object())
		{
			array_push($fields,$res->field_name );
		}
		return $fields;
	}

	/**
	* Checks validity of indexes
	*
	* @param string $type_id Document type identifier
	* @param string $coll_id Collection identifier
	* @param array $values Values to check
	* @return bool true if checks is ok, false if an error occurs
	*/
	public function check_indexes($type_id, $coll_id, $values)
	{
		require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_security.php');
		$sec = new security();
		$ind_coll = $sec->get_ind_collection($coll_id);
		$indexes = $this->get_indexes($type_id, $coll_id);
		$mandatory_indexes = $this->get_mandatory_indexes($type_id, $coll_id);

		// Checks the manadatory indexes
		for($i=0; $i<count($mandatory_indexes);$i++)
		{
			if( empty($values[$mandatory_indexes[$i]]) )  // && ($values[$i]['VALUE'] == 0 && $_ENV['categories'][$cat_id][$values[$i]['ID']]['type_form'] <> 'integer')
			{
				$_SESSION['error'] = $indexes[$mandatory_indexes[$i]]['label'].' <br/>'._IS_EMPTY.'<br/>';
				return false;
			}
		}

		// Checks type indexes
		$date_pattern = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";
		foreach(array_keys($values)as $key)
		{
			if(!empty($_SESSION['error']))
			{
				return false;
			}
			if($indexes[$key]['type'] == 'date' && !empty($values[$key]))
			{
				if(preg_match( $date_pattern,$values[$key])== 0)
				{
					$_SESSION['error'] .= $indexes[$key]['label']." <br/>"._WRONG_FORMAT.".<br/>";
					return false;
				}
			}
			else if($indexes[$key]['type'] == 'string'  && !empty($values[$key]))
			{
				$field_value = $this->wash($values[$key],"no",$indexes[$key]['label']);
			}
			else if($indexes[$key]['type'] == 'float'  && !empty($values[$key])) // && $values[$key] >= 0
			{
				$field_value = $this->wash($values[$key],"float",$indexes[$key]['label']);
			}
			else if($indexes[$key]['type'] == 'integer'  && !empty($values[$key])) // && $values[$key] >= 0
			{
				$field_value = $this->wash($values[$key],"num",$indexes[$key]['label']);
			}

			if(isset($indexes[$key]['values']) && count($indexes[$key]['values']) > 0)
			{
				$found = false;
				for($i=0; $i < count($indexes[$key]['values']); $i++)
				{
					if($values[$key] == $indexes[$key]['values'][$i]['id'])
					{
						$found = true;
						break;
					}
				}
				if(!$found)
				{
					$_SESSION['error'] .= 	$indexes[$key]['label']." <br/>: "._ITEM_NOT_IN_LIST.".<br/>";
					return false;
				}
			}
		}
		return true;
	}


	/**
	* Returns a string to use in an sql update query
	*
	* @param string $type_id Document type identifier
	* @param string $coll_id Collection identifier
	* @param array $values Values to update
	* @return string Part of the update sql query
	*/
	public function get_sql_update($type_id, $coll_id, $values)
	{
		$indexes = $this->get_indexes($type_id, $coll_id);

		$req = '';
		foreach(array_keys($values)as $key)
		{
			if($indexes[$key]['type'] == 'date' && !empty($values[$key]))
			{
				$req .= ", ".$key." = '".$this->format_date_db($values[$key])."'";
			}
			else if($indexes[$key]['type'] == 'string' && !empty($values[$key]))
			{
				$req .= ", ".$key." = '".$this->protect_string_db($values[$key])."'";
			}
			else if($indexes[$key]['type'] == 'float' && !empty($values[$key]))
			{
				$req .= ", ".$key." = ".$values[$key]."";
			}
			else if($indexes[$key]['type'] == 'integer' && !empty($values[$key]))
			{
				$req .= ", ".$key." = ".$values[$key]."";
			}
		}
		return $req;
	}

	/**
	* Returns an array used to insert data in the database
	*
	* @param string $type_id Document type identifier
	* @param string $coll_id Collection identifier
	* @param array $values Values to update
	* @param array $data Return array
	* @return array
	*/
	public function fill_data_array($type_id, $coll_id, $values, $data = array())
	{
		$indexes = $this->get_indexes($type_id, $coll_id);

		foreach(array_keys($values)as $key)
		{
			if($indexes[$key]['type'] == 'date' && !empty($values[$key]))
			{
				array_push($data, array('column' => $key, 'value' => $this->format_date_db($values[$key]), 'type' => "date"));
			}
			else if($indexes[$key]['type'] == 'string' && !empty($values[$key]))
			{
				array_push($data, array('column' => $key, 'value' => $this->protect_string_db($values[$key]), 'type' => "string"));
			}
			else if($indexes[$key]['type'] == 'float' && !empty($values[$key]))
			{
				array_push($data, array('column' => $key, 'value' => $values[$key], 'type' => "float"));
			}
			else if($indexes[$key]['type'] == 'integer' && !empty($values[$key]))
			{
				array_push($data, array('column' => $key, 'value' => $values[$key], 'type' => "integer"));
			}
		}
		return $data;
	}

	/**
	* Inits in the database the indexes for a given res id to null
	*
	* @param string $coll_id Collection identifier
	* @param string $res_id Resource identifier
	*/
	public function inits_opt_indexes($coll_id, $res_id)
	{
		require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_security.php');
		$sec = new security();
		$table = $sec->retrieve_table_from_coll($coll_id);

		$indexes = $this->get_all_indexes( $coll_id);
		$query = "update ".$table." set ";
		for($i=0; $i<count($indexes);$i++)
		{
			$query .= $indexes[$i]['column']." = NULL, ";
		}
		$query = preg_replace('/, $/', ' where res_id = '.$res_id, $query);

		$this->connect();
		$this->query($query);
	}

	/**
	* Makes the search checks for a given index, and builds the where query and json
	*
	* @param array $indexes Array of the possible indexes (used to check)
	* @param string $field_name Field name, index identifier
	* @param string $val Value to check
	* @return array ['json_txt'] : json used in the search
	*  				['where'] : where query
	*/
	public function search_checks($indexes, $field_name, $val )
	{
		$where_request = '';
		$json_txt = '';
		$date_pattern = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";
		for($j=0; $j<count($indexes);$j++)
		{
			$column = $indexes[$j]['column'] ;
			if(preg_match('/^doc_/', $field_name))
			{
				$column = 'doc_'.$column;
			}
			if($indexes[$j]['column'] == $field_name || 'doc_'.$indexes[$j]['column'] == $field_name) // type == 'string'
			{
				if(!empty($val))
				{
					$json_txt .= " '".$field_name."' : ['".addslashes(trim($val))."'],";
					if($_SESSION['config']['databasetype'] == "POSTGRESQL")
					{
						$where_request .= " ".$column." ilike '%".$this->protect_string_db($val)."%' and ";
					}
					else
					{
						$where_request .= " ".$column." like '%".$this->protect_string_db($val)."%' and ";
					}
				}
				break;
			}
			else if(($indexes[$j]['column'].'_from' == $field_name || $indexes[$j]['column'].'_to' == $field_name || 'doc_'.$indexes[$j]['column'].'_from' == $field_name ||  'doc_'.$indexes[$j]['column'].'_to' == $field_name) && !empty($val))
			{ // type == 'date'
				if( preg_match($date_pattern,$val)==false )
				{
					$_SESSION['error'] .= _WRONG_DATE_FORMAT.' : '.$val;
				}
				else
				{
					$where_request .= " (".$column." >= '".$this->format_date_db($val)."') and ";
					$json_txt .= " '".$field_name."' : ['".trim($val)."'],";
				}
				break;
			}
			else if($indexes[$j]['column'].'min' == $field_name || $indexes[$j]['column'].'max' == $field_name || 'doc_'.$indexes[$j]['column'].'min' == $field_name || 'doc_'.$indexes[$j]['column'].'max' == $field_name)
			{
				if($indexes[$j]['type'] == 'integer' || $indexes[$j]['type'] == 'float')
				{
					if($indexes[$j]['type'] == 'integer')
					{
						$val_check = $func->wash($val,"num",$indexes[$j]['label'],"no");
					}
					else
					{
						$val_check = $func->wash($val,"float",$indexes[$j]['label'],"no");
					}
					if(empty($_SESSION['error']))
					{
						$where_request .= " (".$column." >= ".$val_check.") and ";
						$json_txt .= " '".$field_name."' : ['".$val_check."'],";
					}
				}
				break;
			}
		}
		return array('json_txt' => $json_txt, 'where' => $where_request);
	}
}
?>

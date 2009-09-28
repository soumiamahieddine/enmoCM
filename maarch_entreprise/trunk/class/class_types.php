<?
/**
* Types Class
*
* Contains all the function to manage the doctypes
*
* @package  Maarch LetterBox 1.0
* @version 2.0
* @since 10/2005
* @license GPL
* @author  Nicolas Gualtieri
* @author  Claire Figueras  <dev@maarch.org>
*
*/

/**
* Class types: Contains all the function to manage the doctypes
*
* @author  Nicolas Gualtieri
* @author  Claire Figueras  <dev@maarch.org>
* @license GPL
* @package Maarch LetterBox 1.0
* @version 1.1
*/
class types extends dbquery
{
	/**
	* Redefinition of the types object constructor : configure the sql argument order by
	*/
	function __construct()
	{
		parent::__construct();
		// configure the sql argument order by
		if(isset($_GET['start']))
		{
			$this->the_start = strip_tags($_GET['start']);
		}
		else
		{
			$this->the_start = 0;
		}

		if(isset($_GET['order']))
		{
			$this->orderby = strip_tags($_GET['order']);
		}
		else
		{
			$this->orderby = "labelasc";
		}

		$this->sqlorderby = "";

		if($this->orderby == "labelasc")
		{
			$this->sqlorderby = "order by description asc";
		}

		if($this->orderby == "labeldesc")
		{
			$this->sqlorderby = "order by description desc";
		}

		if($this->orderby == "idasc")
		{
			$this->sqlorderby = "order by type_id asc";
		}

		if($this->orderby == "iddesc")
		{
			$this->sqlorderby = "order by type_id desc";
		}

		if($this->orderby == "statusasc")
		{
			$this->sqlorderby = "order by enabled asc";
		}

		if($this->orderby == "statusdesc")
		{
			$this->sqlorderby = "order by enabled desc";
		}

		if($this->orderby == "userasc")
		{
			$this->sqlorderby = "order by custom_t2 asc";
		}

		if($this->orderby == "userdesc")
		{
			$this->sqlorderby = "order by user_id desc";
		}
	}

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
		require_once($_SESSION['pathtocoreclass']."class_security.php");
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
				$_SESSION['m_admin']['doctypes']['custom_t1'] = $this->show_string($line->custom_t1);
				$_SESSION['m_admin']['doctypes']['custom_t2'] = $this->show_string($line->custom_t2);
				$_SESSION['m_admin']['doctypes']['custom_t3'] = $this->show_string($line->custom_t3);
				$_SESSION['m_admin']['doctypes']['custom_t4'] = $this->show_string($line->custom_t4);
				$_SESSION['m_admin']['doctypes']['custom_t5'] = $this->show_string($line->custom_t5);
				$_SESSION['m_admin']['doctypes']['custom_t6'] = $this->show_string($line->custom_t6);
				$_SESSION['m_admin']['doctypes']['custom_t7'] = $this->show_string($line->custom_t7);
				$_SESSION['m_admin']['doctypes']['custom_t8'] = $this->show_string($line->custom_t8);
				$_SESSION['m_admin']['doctypes']['custom_t9'] = $this->show_string($line->custom_t9);
				$_SESSION['m_admin']['doctypes']['custom_t10'] = $this->show_string($line->custom_t10);
				$_SESSION['m_admin']['doctypes']['custom_t11'] = $this->show_string($line->custom_t11);
				$_SESSION['m_admin']['doctypes']['custom_t12'] = $this->show_string($line->custom_t12);
				$_SESSION['m_admin']['doctypes']['custom_t13'] = $this->show_string($line->custom_t13);
				$_SESSION['m_admin']['doctypes']['custom_t14'] = $this->show_string($line->custom_t14);
				$_SESSION['m_admin']['doctypes']['custom_t15'] = $this->show_string($line->custom_t15);
				$_SESSION['m_admin']['doctypes']['custom_d1'] = $line->custom_d1;
				$_SESSION['m_admin']['doctypes']['custom_d2'] = $line->custom_d2;
				$_SESSION['m_admin']['doctypes']['custom_d3'] = $line->custom_d3;
				$_SESSION['m_admin']['doctypes']['custom_d4'] = $line->custom_d4;
				$_SESSION['m_admin']['doctypes']['custom_d5'] = $line->custom_d5;
				$_SESSION['m_admin']['doctypes']['custom_d6'] = $line->custom_d6;
				$_SESSION['m_admin']['doctypes']['custom_d7'] = $line->custom_d7;
				$_SESSION['m_admin']['doctypes']['custom_d8'] = $line->custom_d8;
				$_SESSION['m_admin']['doctypes']['custom_d9'] = $line->custom_d9;
				$_SESSION['m_admin']['doctypes']['custom_d10'] = $line->custom_d10;
				$_SESSION['m_admin']['doctypes']['custom_n1'] = $line->custom_n1;
				$_SESSION['m_admin']['doctypes']['custom_n2'] = $line->custom_n2;
				$_SESSION['m_admin']['doctypes']['custom_n3'] = $line->custom_n3;
				$_SESSION['m_admin']['doctypes']['custom_n4'] = $line->custom_n4;
				$_SESSION['m_admin']['doctypes']['custom_n5'] = $line->custom_n5;
				$_SESSION['m_admin']['doctypes']['custom_f1'] = $line->custom_f1;
				$_SESSION['m_admin']['doctypes']['custom_f2'] = $line->custom_f2;
				$_SESSION['m_admin']['doctypes']['custom_f3'] = $line->custom_f3;
				$_SESSION['m_admin']['doctypes']['custom_f4'] = $line->custom_f4;
				$_SESSION['m_admin']['doctypes']['custom_f5'] = $line->custom_f5;

				$_SESSION['service_tag'] = 'doctype_up';
				$core_tools->execute_modules_services($_SESSION['modules_services'], 'doctype_up', "include");
				$core_tools->execute_app_services($_SESSION['app_services'], 'doctype_up', 'include');
			}
		}
		else // mode = add
		{
			$_SESSION['service_tag'] = 'doctype_add';
			echo $core_tools->execute_modules_services($_SESSION['modules_services'], 'doctype_up', "include");
			$core_tools->execute_app_services($_SESSION['app_services'], 'doctype_up', 'include');
			$_SESSION['service_tag'] = '';
		}
		?>
		<!--<script language="javascript">
			function change_coll(totaldoc)
			{
				var eleselect = window.top.document.getElementById('sous_dossier');
				//window.alert(eleselect.value);
				var eleframe1 = window.top.document.getElementById('choose_coll');
				eleframe1.src = '<?=$_SESSION['config']['businessappurl']?>admin/architecture/types/choose_coll.php?subfolder='+eleselect.value+'&totaldoc='+totaldoc;
			}
		</script>-->
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
				<div class="block">
				<form name="frmtype" id="frmtype" method="post" action="<? echo $_SESSION['config']['businessappurl'];?>index.php?page=types_up_db" class="forms">
					<input  type="hidden" name="mode" value="<? echo $mode; ?>" />
					<?
					/*if(!$core_tools->is_module_loaded("folder"))
					{
						?>
		              <!--  <p>
		                 	<iframe name="choose_coll" id="choose_coll" scrolling="no" width="100%" height="20" src="<? echo $_SESSION['config']['businessappurl'].'admin/architecture/types/choose_coll.php';?>" frameborder="0"></iframe>
		                </p>-->
						<p>
							<label><? echo _ATTACH_SUBFOLDER;?> : </label>
							<select name="sous_dossier" id="sous_dossier" class="listext">
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
						<?
					}
					else
					{
			            if($mode == "up")
			            {
							$this->query("select doctypes_first_level_id from ".$_SESSION['tablename']['doctypes_second_level']." where doctypes_second_level_id = ".$_SESSION['m_admin']['doctypes']['SUB_FOLDER']);
							$line = $this->fetch_object();
							$this->query("select doctypes_first_level_label from ".$_SESSION['tablename']['doctypes_first_level']." where doctypes_first_level_id = ".$line->doctypes_first_level_id);
							$line3 = $this->fetch_object();
							$_SESSION['m_admin']['doctypes']['STRUCT_LABEL'] = $this->show_string($line3->doctypes_first_level_label);
							if(isset($_SESSION['m_admin']['doctypes']['TYPE_ID']) && !empty($_SESSION['m_admin']['doctypes']['TYPE_ID']))
							{
								$table_view = $sec->retrieve_view_from_coll_id($_SESSION['m_admin']['doctypes']['COLL_ID']);
								$this->query("select count(*) as total_doc from ".$table_view." where type_id = ".$_SESSION['m_admin']['doctypes']['TYPE_ID']);
								//$this->show();
								$line = $this->fetch_object();
								$total_doc = $line->total_doc;
							}
						} change_coll('<? echo $total_doc;?>');*/
						?>
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
					<!--	<p>
		                 	<iframe name="choose_coll" id="choose_coll" scrolling="no" width="100%" height="50" src="<? echo $_SESSION['config']['businessappurl'].'admin/architecture/types/choose_coll.php';?>" frameborder="0"></iframe>
		                </p>-->
						<?
					//}?>
					<p>
						<label for="collection"><?php  echo _COLLECTION;?> : </label>
                      	<select name="collection" id="collection" onchange="">
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
	                <div align="center">
					<?php // To DO : index dynamiques ?>
					<!--	<iframe name="choose_index" id="choose_index" scrolling="auto" width="100%" height="350" src="<? echo $_SESSION['config']['businessappurl'].'admin/architecture/types/choose_index.php';?>" frameborder="0"></iframe>-->

					<p class="buttons">
						<?
						if($mode == "up")
						{
							?>
							<input class="button" type="submit" name="Submit" value="<? echo _MODIFY_DOCTYPE; ?>"/> <!--onclick="window.frames['choose_index'].document.forms[0].submit();" -->
							<?
						}
						elseif($mode == "add")
						{
							?>
							<input type="submit" class="button"  name="Submit" value="<? echo _ADD_DOCTYPE; ?>" /><!--onclick="window.frames['choose_index'].document.forms[0].submit();"-->
							<?
						}
						?>
						 <input type="button" class="button"  name="cancel" value="<?php echo _CANCEL; ?>" onclick="javascript:window.location.href='<? echo $_SESSION['config']['businessappurl'];?>index.php?page=types';"/>
					</p>
                </form>
                </div>
			<?
			}
			?>
		</div>
	<?
	}

	/**
	* Clean the type info
	*/
	private function typesinfo()
	{
		// clean the type info
		$core_tools = new core_tools();
		$func = new functions();
		//$func->show_array($_REQUEST);
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
			$_SESSION['error'] .= _THE_COLLECTION. ' '._IS_MANDATORY.'.<br/>';
		}
		else
		{
			$_SESSION['m_admin']['doctypes']['COLL_ID'] = $_REQUEST['collection'];
		/*	$coll_id = $_SESSION['m_admin']['doctypes']['COLL_ID'];

			for($i=0;$i<count($_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']]);$i++)
			{

				if($_REQUEST["field_".$_SESSION['index'][$coll_id][$i]['COLUMN']] == "Y")
				{
					if($_REQUEST["mandatory_".$_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN']] == "Y")
					{
						$_SESSION['m_admin']['doctypes'][$_SESSION['index'][$i]['COLUMN']] = "1100000000";
						$_SESSION['m_admin']['doctypes']['custom_query_insert_colums'] .= $_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN'].", ";
						$_SESSION['m_admin']['doctypes']['custom_query_insert_values'] .= "'1100000000', ";
						$_SESSION['m_admin']['doctypes']['custom_query_update'] .= $_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN']." = "."'1100000000', ";
					}
					else
					{
						$_SESSION['m_admin']['doctypes'][$_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN']] = "1000000000";
						$_SESSION['m_admin']['doctypes']['custom_query_insert_colums'] .= $_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN'].", ";
						$_SESSION['m_admin']['doctypes']['custom_query_insert_values'] .= "'1000000000', ";
						$_SESSION['m_admin']['doctypes']['custom_query_update'] .= $_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN']." = "."'1000000000', ";
					}
				}
				elseif($_REQUEST["field_".$_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN']] == "")
				{
					$_SESSION['m_admin']['doctypes'][$_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN']] = "0000000000";
					$_SESSION['m_admin']['doctypes']['custom_query_insert_colums'] .= $_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN'].", ";
					$_SESSION['m_admin']['doctypes']['custom_query_insert_values'] .= "'0000000000', ";
					$_SESSION['m_admin']['doctypes']['custom_query_update'] .= $_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN']." = '0000000000', ";
				}
			}

			if(trim($_SESSION['m_admin']['doctypes']['custom_query_insert_colums']) <> "")
			{
				$_SESSION['m_admin']['doctypes']['custom_query_insert_colums'] = ", ".$_SESSION['m_admin']['doctypes']['custom_query_insert_colums'];
				$_SESSION['m_admin']['doctypes']['custom_query_insert_colums'] = substr($_SESSION['m_admin']['doctypes']['custom_query_insert_colums'],0,strlen($_SESSION['m_admin']['doctypes']['custom_query_insert_colums'])-2);
			}
			if($_SESSION['m_admin']['doctypes']['custom_query_insert_values'] <> "")
			{
				$_SESSION['m_admin']['doctypes']['custom_query_insert_values'] = ", ".$_SESSION['m_admin']['doctypes']['custom_query_insert_values'];
				$_SESSION['m_admin']['doctypes']['custom_query_insert_values'] = substr($_SESSION['m_admin']['doctypes']['custom_query_insert_values'],0,strlen($_SESSION['m_admin']['doctypes']['custom_query_insert_values'])-2);
			}
			if($_SESSION['m_admin']['doctypes']['custom_query_update'] <> "")
			{
				$_SESSION['m_admin']['doctypes']['custom_query_update'] = ", ".$_SESSION['m_admin']['doctypes']['custom_query_update'];
				$_SESSION['m_admin']['doctypes']['custom_query_update'] = substr($_SESSION['m_admin']['doctypes']['custom_query_update'],0,strlen($_SESSION['m_admin']['doctypes']['custom_query_update'])-2);
			}*/
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
	}

	/**
	* Modify, add or validate a doctype
	*/
	public function uptypes()
	{
		// modify, add or validate a doctype
		$core_tools = new core_tools();
		$this->typesinfo();
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
               	 <script language="javascript" type="text/javascript">window.top.location.href='<? echo $_SESSION['config']['businessappurl']."index.php?page=types";?>';</script>
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
				$tmp = $this->protect_string_db($_SESSION['m_admin']['doctypes']['LABEL']);
				$this->query("update ".$_SESSION['tablename']['doctypes']." set description = '".$tmp."' , doctypes_first_level_id = ".$_SESSION['m_admin']['doctypes']['STRUCTURE'].", doctypes_second_level_id = ".$_SESSION['m_admin']['doctypes']['SUB_FOLDER'].", enabled = 'Y', coll_id = '".$_SESSION['m_admin']['doctypes']['COLL_ID']."' ".$_SESSION['m_admin']['doctypes']['custom_query_update']." where TYPE_ID = '".$_SESSION['m_admin']['doctypes']['TYPE_ID']."'");

				$_SESSION['service_tag'] = "doctype_updatedb";
				$core_tools->execute_modules_services($_SESSION['modules_services'], 'doctype_load_db', "include");
				$core_tools->execute_app_services($_SESSION['app_services'], 'doctype_up', 'include');
				$_SESSION['service_tag'] = '';
				if($_REQUEST['mode'] == "up")
				{
					$_SESSION['error'] = _DOCTYPE_MODIFICATION;
					if($_SESSION['history']['doctypesup'] == "true")
					{
						require($_SESSION['pathtocoreclass']."class_history.php");
						$users = new history();
						$users->add($_SESSION['tablename']['doctypes'], $_SESSION['m_admin']['doctypes']['TYPE_ID'],"UP",_DOCTYPE_MODIFICATION." : ".$_SESSION['m_admin']['doctypes']['LABEL'], $_SESSION['config']['databasetype']);
					}
				}
				$this->cleartypeinfos();
				?>
				<script language="javascript" type="text/javascript">window.top.location.href='<? echo $_SESSION['config']['businessappurl']."index.php?page=types";?>';</script>
                <?
				exit();
			}
			else
			{
				require($_SESSION['pathtocoreclass']."class_history.php");
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
				/*	if($core_tools->is_module_loaded("basket"))
					{
						$this->query("insert into ".$_SESSION['tablename']['mlb_doctype_ext']." (type_id, process_delay, delay1, delay2) values (".$res->type_id.", ".$_SESSION['m_admin']['doctypes']['process_delay'].", ".$_SESSION['m_admin']['doctypes']['delay1'].", ".$_SESSION['m_admin']['doctypes']['delay2'].")");
					}*/
					$_SESSION['service_tag'] = "doctype_insertdb";
					echo $core_tools->execute_modules_services($_SESSION['modules_services'], 'doctype_load_db', "include");
					$core_tools->execute_app_services($_SESSION['app_services'], 'doctype_up', 'include');
					$_SESSION['service_tag'] = '';

					if($_SESSION['history']['doctypesadd'] == "true")
					{
						$users->add($_SESSION['tablename']['doctypes'],$res->type_id,"ADD", _DOCTYPE_ADDED." : ".$_SESSION['m_admin']['doctypes']['LABEL'],$_SESSION['config']['databasetype']);
					}
					//$url = "index.php?page=types";
				}
				$this->cleartypeinfos();
				//header("location: ".$url);
				?> <script language="javascript" type="text/javascript">window.top.location.href='<? echo $_SESSION['config']['businessappurl']."index.php?page=types";?>';</script>
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
	* Delete a doc type
	*
	* @param integer $id doctype indentifier
	*/
/*	public function deltypes($id)
	{
		// delete a doc type
		$core_tools = new core_tools();
		$this->connect();
		$this->query("select description from ".$_SESSION['tablename']['doctypes']." where type_id = ".$id."");
		if($this->nb_result() == 0)
		{
			$_SESSION['error'] = _DOCTYPE.' '._UNKNOWN;
			header("location: ".$_SESSION['config']['businessappurl']."index.php?page=types");
			exit();
		}
		else
		{
			$info = $this->fetch_object();
			$this->query("delete from ".$_SESSION['tablename']['doctypes']." where type_id = ".$id."");


			$_SESSION['service_tag'] = "doctype_delete";
			$_SESSION['m_admin']['doctypes']['TYPE_ID'] = $id;
			$core_tools->execute_modules_services($_SESSION['modules_services'], 'doctype_del', "include");
			$core_tools->execute_app_services($_SESSION['app_services'], 'doctype_del', 'include');
			$_SESSION['service_tag'] = '';

			require($_SESSION['pathtocoreclass']."class_history.php");
			$users = new history();
			$users->add($_SESSION['tablename']['doctypes'], $id,"DEL",_DOCTYPE_DELETION." : ".$info->DESCRIPTION, $_SESSION['config']['databasetype']);
			$_SESSION['error'] = _DELETED_DOCTYPE;
			header("location: ".$_SESSION['config']['businessappurl']."index.php?page=types");
			exit();
		}
	}*/

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
}
?>

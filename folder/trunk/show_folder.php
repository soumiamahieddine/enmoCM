<?php
/**
* File : show_folder.php
*
* Show the details of a folder
*
* @package  Maarch v3
* @version 1.0
* @since 10/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
$core_tools = new core_tools();
if(!$core_tools->is_module_loaded("folder"))
{
	echo "Folder module missing !<br/>Please install this module.";
	exit();
}
// $core_tools->test_service('salary_sheet', 'folder');
/****************Management of the location bar  ************/
$sec = new security();
$init = false;
if($_REQUEST['reinit'] == "true")
{
	$init = true;
}
$level = "";
if($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)
{
	$level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=show_folder&module=folder&folder_id='.$_REQUEST['id'];
$page_label = _SHOW_FOLDER;
$page_id = "fold_show_folder";
$core_tools->manage_location_bar($page_path, $page_label,$page_id, $init, $level);
/***********************************************************/
require_once("modules".DIRECTORY_SEPARATOR."folder".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
require_once("apps/".$_SESSION['businessapps'][0]['appid']."/class".DIRECTORY_SEPARATOR."class_list_show.php");
$folder_object = new folder();
$request= new request;
$func = new functions();
require_once("core/class/class_history.php");
$users = new history();
$users->connect();
$status = '';
$_SESSION['current_foldertype'] = '';
$_SESSION['origin'] = "show_folder";
$date_pattern = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";

$view = '';
$update_right = $core_tools->test_service('modify_folder', 'folder', false);
$delete_right = $core_tools->test_service('delete_folder', 'folder', false);

//update folder index
if(isset($_POST['update_folder']))
{
	$folder_object->update_folder($_REQUEST, $_REQUEST['id']);
}
//delete the folder
if(isset($_POST['delete_folder']))
{
	$folder_object->delete_folder($_REQUEST['id'], $_REQUEST['foldertype_id']);
	?>
		<script language="javascript" type="text/javascript">window.top.location.href='<?php  echo $_SESSION['config']['businessappurl'].'index.php?page=search_adv_folder&module=folder';?>';</script>
    <?php
	exit();
}

?>
<div id="details_div" style="display:none;">
<h1><img src="<?php  echo $_SESSION['config']['img'];?>/manage_structures.gif" alt="<?php _FOLDER;?>" width="35px" height="30px"/> <?php  echo _SHOW_FOLDER;?></h1>
<div id="inner_content">
	<?php
	if($_REQUEST['id']<>"")
	{
		?>
		<div class="viewfolder">
		<?php
		if($_REQUEST['id'] <> "")
		{
			$folder_object->load_folder($_REQUEST['id'],$_SESSION['tablename']['fold_folders']);
			$status = $folder_object->get_field('status');
			$_SESSION['current_foldertype_coll_id'] = $folder_object->get_field('coll_id');
			$view = $sec->retrieve_view_from_coll_id($_SESSION['current_foldertype_coll_id']);
			if($status == 'DEL')
			{
				echo _NO_FOLDER_FOUND.".";
			}
			else
			{
				$folder_array = array();
				$folder_array = $folder_object->get_folder_info();

				$_SESSION['current_folder_id'] = $folder_array['system_id'];
				$id = $_SESSION['current_folder_id'];
				$folder_object->modify_default_folder_in_db($_SESSION['current_folder_id'], $_SESSION['user']['UserId'], $_SESSION['tablename']['users']);

				if($_SESSION['history']['folderview'] == true)
				{
					$users->add($_SESSION['tablename']['fold_folders'], $id ,"VIEW", _VIEW_FOLDER." ".strtolower(_NUM).$folder_array['folder_id'], $_SESSION['config']['databasetype'], 'folder');
				}
			}
		}
	}
		?>
		<div class="block">
			<h4><a href="#" onclick="history.go(-1);" class="back"></a></h4>
		</div>
		<div class=blank_space>&nbsp;</div>
		<dl id="tabricator1">
			<dt><?php  echo _FOLDER_DETAILLED_PROPERTIES;?></dt>
			<dd>
			<form method="post" name="index_folder" id="index_folder" action="index.php?page=show_folder&module=folder&id=<?php  echo $_SESSION['current_folder_id'] ?>">
				<h2><span class="date"><b><?php  echo _FOLDER_DETAILLED_PROPERTIES;?></b></span></h2>
				<br/>
				<table cellpadding="2" cellspacing="2" border="0" class="block forms details" width="100%">
					<tr>
						<th align="left" class="picto" >&nbsp;</th>
						<th ><?php  echo _FOLDERID_LONG; ?> :</th>
						<td ><input type="text" class="readonly" readonly="readonly" value="<?php  echo $folder_array['folder_id'] ; ?>" size="40" id="folder_id" name="folder_id" /></td>
						<th align="left" class="picto" >&nbsp;</th>
						<th ><?php  echo _FOLDERNAME; ?> :</th>
						<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $folder_array['folder_name']; ?>" id="folder_name" name="folder_name" /></td>
					</tr>
					<tr>
						<th align="left" class="picto" >&nbsp;</th>
						<th ><?php  echo _FOLDERTYPE; ?> :</th>
						<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $folder_array['foldertype_label']; ?>" id="foldertype"  name="foldertype" />
						<input type="hidden" name="foldertype_id" id="foldertype_id" value="<?php  echo $folder_array['foldertype_id']; ?>" />
						</td>
						<th align="left" class="picto" >&nbsp;</th>
						<th ><?php  echo _STATUS; ?> :</th>
						<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $folder_array['status']; ?>" id="status" name="status" /></td>
					</tr>
				</table>
				<?php if(count($folder_array['index']) > 0)
				{
					?>
					<br/>
						<h2>
			            <span class="date">
			            	<b><?php  echo _OPT_INDEXES;?></b>
			            </span>
			        	</h2>
			        	<br/>
			        <table cellpadding="2" cellspacing="2" border="0" class="block forms details" width="100%">
					<?php
							$i=0;
							foreach(array_keys($folder_array['index']) as $key)
							{
								if($i%2 != 1 || $i==0) // pair
								{
									?>
									<tr class="col">
									<?php
								}?>
								<th align="left" class="picto" >
									<?php
									if(isset($indexes[$key]['img']))
									{
										?>
										<img alt="<?php echo $folder_array['index'][$key]['label'];?>" title="<?php echo $folder_array['index'][$key]['label'];?>" src="<?php echo $folder_array['index'][$key]['img'];?>"  /></a>
										<?php
									}
									?>&nbsp;
								</th>
								<th align="left" >
									<?php echo $folder_array['index'][$key]['label'];?> :
								</th>
								<td>
									<?php
									if($update_right)
									{
										$value = '';
										if(!empty($folder_array['index'][$key]['show_value']))
										{
											$value = $folder_array['index'][$key]['show_value'];
										}
										elseif($folder_array['index'][$key]['default_value'])
										{
											$value = $folder_array['index'][$key]['default_value'];
										}
										if($folder_array['index'][$key]['type_field'] == 'input')
										{
											if($folder_array['index'][$key]['type'] == 'date')
											{
												?>
												<input type="text" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $value; ?>" size="40"  title="<?php echo $value; ?>" alt="<?php echo $value; ?>" onclick="showCalender(this);" />
												<?php
											}
											else
											{
												?>
												<input type="text" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $key;?>" size="40"  title="<?php echo $key;?>" alt="<?php echo $key;?>" />
												<?php
											}
										}
										else
										{
											?>
											<select name="<?php echo $key;?>" id="<?php echo $key;?>" >
												<option value=""><?php echo _CHOOSE;?>...</option>
												<?php for($i=0; $i<count($folder_array['index'][$key]['values']);$i++)
												{?>
													<option value="<?php echo $folder_array['index'][$key]['values'][$i]['id'];?>" <?php if($folder_array['index'][$key]['values'][$i]['id'] == $folder_array['index'][$key]['value'] || $folder_array['index'][$key]['values'][$i]['id'] == $value){ echo 'selected="selected"';}?>><?php echo $folder_array['index'][$key]['values'][$i]['label'];?></option>
													<?php
												}?>
											</select>
												<?php
										}

									}
									else
									{
									?>
										<input type="text" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $folder_array['index'][$key]['show_value'];?>" size="40"  title="<?php  echo $folder_array['index'][$key]['show_value']; ?>" alt="<?php  echo $folder_array['index'][$key]['show_value']; ?>" readonly="readonly" class="readonly" />
										<?php
									}
								?>
								</td>
								<?php
								if($i%2 == 1 && $i!=0) // impair
								{?>
									</tr>
									<?php
								}
								else
								{
									if($i+1 == count($folder_array['index']))
									{
										echo '<td  colspan="2">&nbsp;</td></tr>';
									}
								}
								$i++;
						}
				?></table><?php
				 } ?>
				<br/>
				<h2>
			        <span class="date"><b><?php  echo _FOLDER_PROPERTIES;?></b></span>
			    </h2>
				<br/>
				<table cellpadding="2" cellspacing="2" border="0" class="block forms details" width="100%">
					<tr>
						<th align="left" class="picto" >&nbsp;</th>
						<th ><?php  echo _TYPIST; ?> :</th>
						<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $folder_array['typist']; ?>" name="typîst" id="typist" /></td>
						<th align="left" class="picto" >&nbsp;</th>
						<th ><?php  echo _CREATION_DATE; ?> :</th>
						<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $folder_array['creation_date']; ?>" id="creation_date" name="creation_date"  /></td>
					</tr>
					<tr>
						<th align="left" class="picto" >&nbsp;</th>
						<th ><?php  echo _SYSTEM_ID; ?> :</th>
						<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $folder_array['system_id']; ?>" name="system_id" id="system_id" /></td>
						<th align="left" class="picto" >&nbsp;</th>
						<th ><?php  echo _MODIFICATION_DATE; ?> :</th>
						<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $folder_array['last_modified_date']; ?>" id="modification_date" name="modification_date"  /></td>
					</tr>
				</table>
				<br/>
				<p class="buttons" align="center">
					<?php if($update_right && count($folder_array['index']) > 0)
					{
						?><input type="submit" class="button" name="update_folder" id="update_folder" value="<?php echo _UPDATE_FOLDER;?>" /><?php
					}?>
					<?php if($delete_right)
						{?>
						<input type="submit" class="button"  value="<?php  echo _DELETE_FOLDER;?>" name="delete_folder" onclick="return(confirm('<?php  echo _REALLY_DELETE.' '._THIS_FOLDER.'?\n\r\n\r'._WARNING.' '._ALL_DOCS_AND_SUFOLDERS_WILL_BE_DELETED; ?>'));" />
						<?php } ?>
				</p>
				</form>
			</dd>
			<dt><?php  echo _ARCHIVED_DOC;?></dt>
			<dd>
				<table width="100%" border="0">
					<tr>
						<td valign="top">
							<div align="left">
								<?php
								if(trim($_SESSION['current_folder_id'])<>'' && !empty($view))
								{
									$select2 = array();
									$select2[$view]= array();
									 $tab2 = array();
									 array_push($select2[$view],"res_id", "type_label");
									$tab2=$request->select($select2,"folders_system_id = '".$_SESSION['current_folder_id']."' and status <> 'DEL'"," order by type_label ",$_SESSION['config']['databasetype'],"500",false);
										for ($cpt_folder_2=0;$cpt_folder_2<count($tab2);$cpt_folder_2++)
										{
											for ($cpt_folder_j_2=0;$cpt_folder_j_2<count($tab2[$cpt_folder_2]);$cpt_folder_j_2++)
											{
												foreach(array_keys($tab2[$cpt_folder_2][$cpt_folder_j_2]) as $value)
												{
													if($tab2[$cpt_folder_2][$cpt_folder_j_2][$value]=='res_id')
													{
														$tab2[$cpt_folder_2][$cpt_folder_j_2]['res_id']=$tab2[$cpt_folder_2][$cpt_folder_j_2]['value'];
														$tab2[$cpt_folder_2][$cpt_folder_j_2]["label"]=_GED_NUM;
														$tab2[$cpt_folder_2][$cpt_folder_j_2]["size"]="10";
														$tab2[$cpt_folder_2][$cpt_folder_j_2]["label_align"]="left";
														$tab2[$cpt_folder_2][$cpt_folder_j_2]["align"]="right";
														$tab2[$cpt_folder_2][$cpt_folder_j_2]["valign"]="bottom";
														$tab2[$cpt_folder_2][$cpt_folder_j_2]["show"]=false;
													}
													if($tab2[$cpt_folder_2][$cpt_folder_j_2][$value]=="type_label")
													{
														$tab2[$cpt_folder_2][$cpt_folder_j_2]["value"]=$request->show_string($tab[$cpt_folder_2][$cpt_folder_j_2]["value"]);
														$tab2[$cpt_folder_2][$cpt_folder_j_2]["label"]=_TYPE;
														$tab2[$cpt_folder_2][$cpt_folder_j_2]["size"]="40";
														$tab2[$cpt_folder_2][$cpt_folder_j_2]["label_align"]="left";
														$tab2[$cpt_folder_2][$cpt_folder_j_2]["align"]="left";
														$tab2[$cpt_folder_2][$cpt_folder_j_2]["valign"]="bottom";
														$tab2[$cpt_folder_2][$cpt_folder_j_2]["show"]=true;
													}
												}
											}
										}
										$_SESSION['FILLING_RES']['PARAM']['RESULT']=array();
										$_SESSION['FILLING_RES']['PARAM']['RESULT']=$tab2;
										$_SESSION['FILLING_RES']['PARAM']['NB_TOTAL']=$cpt_folder_2;
										$_SESSION['FILLING_RES']['PARAM']['TITLE']= $cpt_folder_2." "._FOUND_DOC;
										$_SESSION['FILLING_RES']['PARAM']['WHAT']='res_id';
										$_SESSION['FILLING_RES']['PARAM']['NAME']="filling_res";
										$_SESSION['FILLING_RES']['PARAM']['KEY']='res_id';
										$details_page = $sec->get_script_from_coll($folder_array['coll_id'], 'script_details');
										$_SESSION['FILLING_RES']['PARAM']['DETAIL_DESTINATION']=$details_page."";
										$_SESSION['FILLING_RES']['PARAM']['BOOL_VIEW_DOCUMENT']=true;
										$_SESSION['FILLING_RES']['PARAM']['BOOL_RADIO_FORM']=false;
										$_SESSION['FILLING_RES']['PARAM']['METHOD']="";
										$_SESSION['FILLING_RES']['PARAM']['ACTION']="";
										$_SESSION['FILLING_RES']['PARAM']['BUTTON_LABEL']="";
										$_SESSION['FILLING_RES']['PARAM']['BOOL_DETAIL']=true;
										$_SESSION['FILLING_RES']['PARAM']['BOOL_ORDER']=false;
										$_SESSION['FILLING_RES']['PARAM']['BOOL_FRAME']=true;

										?>
										<iframe name="filling_res" id="filling_res" src="<?php  echo $_SESSION['urltomodules']."folder/filling_res.php";?>" frameborder="0" scrolling="auto" width="400px" height="580px"></iframe>
										<?php
									}
									else
									{
										echo "&nbsp;";
									}
									?>
								</div>
							</td>
							<td valign="top">
					            <table>
									<tr valign="top">
										<td>
										<iframe name="view_doc" id="view_doc" src="<?php  echo $_SESSION['urltomodules']."folder/list_doc.php";?>" frameborder="0" scrolling="no" width="570px" height="580px"></iframe>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
			<dt><?php  echo _FOLDER_HISTORY;?></dt>
			<dd>
				<?php  echo $core_tools->execute_modules_services($_SESSION['modules_services'], 'index.php?page=show_folder', "include","show_history_folder", "folder");?>
			</dd>
			<!--<dt><?php  echo _MISSING_DOC;?></dt>
			<dd>
				<?php  //echo $core_tools->execute_modules_services($_SESSION['modules_services'], 'index.php?page=show_folder', "include","show_missing_doc_in_folder", "folder");?>
			</dd>-->
		</dl>

</div>
</div>
</div>
<script type="text/javascript">
	var item  = $('details_div');
	var tabricator1 = new Tabricator('tabricator1', 'DT');
	if(item)
  	{
		item.style.display='block';
	}
</script>

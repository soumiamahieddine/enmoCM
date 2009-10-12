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
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
require_once($_SESSION['pathtocoreclass']."class_security.php");
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
require_once($_SESSION['pathtomodules']."folder".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
require_once($_SESSION['pathtomodules']."folder".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_folders_show.php");
require_once($_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR."class_list_show.php");
$folder_show=new folders_show();
$folder_object=new folder();
$request= new request;
$func = new functions();
require_once($_SESSION['pathtocoreclass']."class_history.php");
$users = new history();
$users->connect();
$status = '';
$_SESSION['current_foldertype'] = '';
$_SESSION['origin'] = "show_folder";
$date_pattern = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";

$view = '';
/*
if(trim($_REQUEST['field']) <> "")
{
	$_SESSION['FOLDER']['SEARCH']['FOLDER_ID'] = $_REQUEST['field'];
	$folder_object->load_folder1($_SESSION['FOLDER']['SEARCH']['FOLDER_ID'],$_SESSION['tablename']['fold_folders']);
	$folder_array = array();
	$folder_array = $folder_object->get_folder_info();
	$_SESSION['current_folder_id'] = $folder_object->get_field('folders_system_id');
	$_SESSION['current_folder_typeid'] = $folder_object->get_field('folders_typeid');
	$_SESSION['FOLDER']['SEARCH']['FOLDER_NUM'] = '';
	$_SESSION['FOLDER']['SEARCH']['CUSTOM_T1'] = '';
	$_SESSION['current_foldertype_coll_id'] = $folder_array['coll_id'];
	$view = $sec->retrieve_view_from_coll_id($folder_array['coll_id']);
}
if(isset($_SESSION['current_folder_id']) && !empty($_SESSION['current_folder_id']) && !isset($_SESSION['FOLDER']['SEARCH']['FOLDER_NUM']) && !isset($_SESSION['FOLDER']['SEARCH']['CUSTOM_T1']))
{
	//$folder_object->connect();
	//$folder_object->query("select folder_id from ".$_SESSION['tablename']['fold_folders']." where folders_system_id = ".$_SESSION['current_folder_id']);
	//$res = $folder_object->fetch_object();
	$_SESSION['FOLDER']['SEARCH']['FOLDER_ID'] = $_SESSION['current_folder_id'];

}*/


/*$select[$_SESSION['tablename']['fold_folders']] = array();
array_push($select[$_SESSION['tablename']['fold_folders']],"folders_system_id","folder_id","custom_t1","custom_t2","custom_d1", "custom_t10");
$tab=$request->select($select,$where," order by custom_t1",$_SESSION['config']['databasetype'],"10");

if(isset($_SESSION['FOLDER']['SEARCH']['FOLDER_NUM']) && !empty($_SESSION['FOLDER']['SEARCH']['FOLDER_NUM'] ))
{
	if($_SESSION['config']['databasetype']== "POSTGRESQL")
	{
		$where .= "folder_id ilike '".$func->protect_string_db($_SESSION['FOLDER']['SEARCH']['FOLDER_NUM'],$_SESSION['config']['databasetype'])."%' and status <> 'DEL'";
	}
	else
	{
		$where .= " folder_id like '".$func->protect_string_db($_SESSION['FOLDER']['SEARCH']['FOLDER_NUM'],$_SESSION['config']['databasetype'])."%' and status <> 'DEL' ";
	}
	$tab=$request->select($select,$where," order by custom_t1",$_SESSION['config']['databasetype'],"10");
	//$request->show();
	$flagsearch = true;
}
elseif(isset($_SESSION['FOLDER']['SEARCH']['CUSTOM_T1']) && !empty($_SESSION['FOLDER']['SEARCH']['CUSTOM_T1'] ))
{
	if($_SESSION['config']['databasetype'] == "POSTGRESQL")
	{
		$where .= " custom_t1 ilike '".$func->protect_string_db($_SESSION['FOLDER']['SEARCH']['CUSTOM_T1'],$_SESSION['config']['databasetype'])."%' and status <> 'DEL' and status <> 'IMP'";
	}
	else
	{
		$where .= " custom_t1 like '".$func->protect_string_db($_SESSION['FOLDER']['SEARCH']['CUSTOM_T1'],$_SESSION['config']['databasetype'])."%' and status <> 'DEL' and status <> 'IMP'";
	}
	$tab=$request->select($select,$where," order by custom_t1",$_SESSION['config']['databasetype'],"10");
	$flagsearch = true;

}*/
/*
if($flagsearch)
{
	for ($cpt_folder_1=0;$cpt_folder_1<count($tab);$cpt_folder_1++)
	{
		for ($cpt_folder_j_1=0;$cpt_folder_j_1<count($tab[$cpt_folder_1]);$cpt_folder_j_1++)
		{
			foreach(array_keys($tab[$cpt_folder_1][$cpt_folder_j_1]) as $value)
			{
				if($tab[$cpt_folder_1][$cpt_folder_j_1][$value]=="folder_id")
				{
					$tab[$cpt_folder_1][$cpt_folder_j_1]["folder_id"]=$tab[$cpt_folder_1][$cpt_folder_j_1]['value'];
					$tab[$cpt_folder_1][$cpt_folder_j_1]["label"]=_ID;
					$tab[$cpt_folder_1][$cpt_folder_j_1]["size"]="10";
					$tab[$cpt_folder_1][$cpt_folder_j_1]["label_align"]="left";
					$tab[$cpt_folder_1][$cpt_folder_j_1]["align"]="center";
					$tab[$cpt_folder_1][$cpt_folder_j_1]["valign"]="bottom";
					$tab[$cpt_folder_1][$cpt_folder_j_1]["show"]=true;
				}
				if($tab[$cpt_folder_1][$cpt_folder_j_1][$value]=="folders_system_id")
				{
					$tab[$cpt_folder_1][$cpt_folder_j_1]["folders_system_id"]=$tab[$cpt_folder_1][$cpt_folder_j_1]['value'];
					$tab[$cpt_folder_1][$cpt_folder_j_1]["label"]="folders_system_id";
					$tab[$cpt_folder_1][$cpt_folder_j_1]["size"]="4";
					$tab[$cpt_folder_1][$cpt_folder_j_1]["label_align"]="left";
					$tab[$cpt_folder_1][$cpt_folder_j_1]["align"]="center";
					$tab[$cpt_folder_1][$cpt_folder_j_1]["valign"]="bottom";
					$tab[$cpt_folder_1][$cpt_folder_j_1]["show"]=false;
				}
				if($tab[$cpt_folder_1][$cpt_folder_j_1][$value]=="custom_t1")
				{
					$tab[$cpt_folder_1][$cpt_folder_j_1]["value"]=$request->show_string($tab[$cpt_folder_1][$cpt_folder_j_1]["value"]);
					$tab[$cpt_folder_1][$cpt_folder_j_1]["label"]=_LASTNAME;
					$tab[$cpt_folder_1][$cpt_folder_j_1]["size"]="15";
					$tab[$cpt_folder_1][$cpt_folder_j_1]["label_align"]="left";
					$tab[$cpt_folder_1][$cpt_folder_j_1]["align"]="left";
					$tab[$cpt_folder_1][$cpt_folder_j_1]["valign"]="bottom";
					$tab[$cpt_folder_1][$cpt_folder_j_1]["show"]=true;
				}
				if($tab[$cpt_folder_1][$cpt_folder_j_1][$value]=="custom_t2")
				{
					$tab[$cpt_folder_1][$cpt_folder_j_1]["value"]=$request->show_string($tab[$cpt_folder_1][$cpt_folder_j_1]["value"]);
					$tab[$cpt_folder_1][$cpt_folder_j_1]["label"]=_FIRSTNAME;
					$tab[$cpt_folder_1][$cpt_folder_j_1]["size"]="15";
					$tab[$cpt_folder_1][$cpt_folder_j_1]["label_align"]="left";
					$tab[$cpt_folder_1][$cpt_folder_j_1]["align"]="left";
					$tab[$cpt_folder_1][$cpt_folder_j_1]["valign"]="bottom";
					$tab[$cpt_folder_1][$cpt_folder_j_1]["show"]=true;
				}
			}
		}
	}
	if($cpt_folder_1>1)
	{
		$show_list = "ok";
		//print_r($tab);
	}
	elseif($cpt_folder_1==0)
	{
		$notfound = "ok";
		//echo _NO_RESULTS;
	}
	else
	{
		$_SESSION['FOLDER']['SEARCH']['FOLDER_ID'] = $tab[0][0]["folders_system_id"];
	}

}*/

//get the var to update the folder
/*
if(trim($_REQUEST['mode'])=='up')
{
	$data = array();
	foreach(array_keys($_REQUEST) as $value)
	{
		if($value <> "submit" && !$folder_object->is_mandatory_field($value))
		{
			//echo $value." ".$_GET[$value]."<br/>";
			if($folder_object->is_mandatory($value))
			{
				if(empty($_REQUEST[$value]))
				{
					$_SESSION['error'] .= $folder_object->retrieve_index_label($value)." "._MANDATORY.".<br/>";
					$_SESSION['field_error'][$value] = true;
					//echo $_SESSION['error'];
				}
			}
			if(!empty($_REQUEST[$value]))
			{
				$data = $folder_object->user_exit($value, $data);
			}
		}
	}
	$where = "folders_system_id = '".$_SESSION['FOLDER']['SEARCH']['FOLDER_ID']."'";
	$folder_object->load_folder1($_SESSION['FOLDER']['SEARCH']['FOLDER_ID'], $_SESSION['tablename']['fold_folders']);
	$_SESSION['current_foldertype_coll_id'] =$folder_object->get_field('coll_id');
	$view = $sec->retrieve_view_from_coll_id($_SESSION['current_foldertype_coll_id']);

	$request->update($_SESSION['tablename']['fold_folders'], $data,$where, $_SESSION['config']['databasetpe']);
	if($_SESSION['history']['folderup'] == 'true')
	{
		$users->add($_SESSION['tablename']['fold_folders'],$folder_object->get_field('folders_system_id')  ,"UP", _FOLDER_INDEX_MODIF, $_SESSION['config']['databasetype'],'folder');
	}
}
else
{
	if($_REQUEST['up_folder_boolean'])
	{
		echo _MISSING_FIELDS.".";
	}

}*/
?>
<div id="details_div" style="display:block;">
<h1><img src="<?php  echo $_SESSION['config']['img'];?>/manage_structures.gif" alt="<?php _FOLDER;?>" width="35px" height="30px"/> <?php  echo _SHOW_FOLDER;?></h1>
<div id="inner_content">
	<?php
	if($_REQUEST['id']<>"")
	{
		?>
		<div class="viewfolder">
		<?php
		if($_REQUEST['id'] <> "") // && $show_list <> "ok" && $notfound <> "ok")
		{
			$folder_object->load_folder1($_REQUEST['id'],$_SESSION['tablename']['fold_folders']);
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
				//$lastname = '';
/*
				for($cpt_folder_3=0;$cpt_folder_3<count($folder_array['index']);$cpt_folder_3++)
				{
					if($folder_array['index'][$cpt_folder_3]['column'] == 'custom_t1')
					{
						$lastname = $folder_array['index'][$cpt_folder_3]['value'];
						break;
					}
				}
*/

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
				<h2><span class="date"><b><?php  echo _FOLDER_DETAILLED_PROPERTIES;?></b></span></h2>
				<br/>
				<table cellpadding="2" cellspacing="2" border="0" class="block forms details" width="100%">
					<tr>
						<th align="left" class="picto" >&nbsp;</th>
						<th ><?php  echo _FOLDERID_LONG; ?> :</th>
						<td colspan="4"><input type="text" class="readonly" readonly="readonly" value="<?php  echo $folder_array['folder_id'] ; ?>" size="40" id="folder_id" name="folder_id" /></td>

					</tr>
					<tr>
						<th align="left" class="picto" >&nbsp;</th>
						<th ><?php  echo _FOLDERTYPE; ?> :</th>
						<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $folder_array['foldertype_label']; ?>" id="foldertype"  name="foldertype" /></td>
						<th align="left" class="picto" >&nbsp;</th>
						<th ><?php  echo _STATUS; ?> :</th>
						<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $folder_array['status']; ?>" id="status" name="status" /></td>
					</tr>
				</table>
				<?php if(count($folder_array['index']) > 0)
				{?>
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
									if($folder_array['index'][$key]['type'] == 'date')
									{
										?>
										<input type="text" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $folder_array['index'][$key]['value'];?>" size="40"  title="<?php  echo $folder_array['index'][$key]['value']; ?>" alt="<?php  echo $folder_array['index'][$key]['value']; ?>" onclick="showCalender(this);" />
										<?php
									}
									else
									{
										?>
										<input type="text" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $folder_array['index'][$key]['value'];?>" size="40"  title="<?php  echo $folder_array['index'][$key]['value']; ?>" alt="<?php  echo $folder_array['index'][$key]['value']; ?>" />
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
					//$func->show_array($folder_array['index']);
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
						<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $folder_array['last_modification_date']; ?>" id="modification_date" name="modification_date"  /></td>
					</tr>
				</table>
			</dd>
			<dt><?php  echo _ARCHIVED_DOC;?></dt>
			<dd>
				<?php
				if((!$flagsearch || $cpt_folder_3==1) && $status <> 'DEL')
				{
					?>
					<table width="100%" border="0">
						<tr>
							<td valign="top">
								<div align="left">
									<?php
									if(trim($_SESSION['current_folder_id'])<>'' && !empty($view))
									{
										$select2 = array();
										$select2[$view]= array();
										//$select2[$_SESSION['tablename']['doctypes']]= array();
									 	$tab2 = array();
									 	array_push($select2[$view],"res_id", "type_label");
										//array_push($select2[$_SESSION['tablename']['doctypes']],"description");
										//$tab2=$request->select($select2,"folders_system_id = ".$_SESSION['current_folder_id']." and status <> 'DEL'"," order by ".$_SESSION['tablename']['doctypes'].".description ",$_SESSION['config']['databasetype'],"500",true,$_SESSION['collection'][0]['table'],$_SESSION['tablename']['doctypes'],"type_id");
										$tab2=$request->select($select2,"folders_system_id = '".$_SESSION['current_folder_id']."' and status <> 'DEL'"," order by type_label ",$_SESSION['config']['databasetype'],"500",false);
										//$request->show();
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
										//$request->show_array($_SESSION['FILLING_RES']['PARAM']);
										//exit;
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
										<?php  //echo $core_tools->execute_app_services($_SESSION['app_services'], 'index.php?page=salary_sheet', "frame");?>
										 <?php  //echo $core_tools->execute_modules_services($_SESSION['modules_services'], 'index.php?page=salary_sheet', "frame");?>
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
		<?php
	}
	/*else
	{
		?><div>
		<?php  echo _PLEASE_SELECT_FOLDER;?>.</div>
		<?php
	}*/
	?>
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

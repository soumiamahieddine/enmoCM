<?php
/**
* File : search_adv_result.php
*
* Clean the advance search var and build the results
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 03/2007
* @license GPL
* @author  Loïc Vinet
*/
session_name('PeopleBox');
session_start();
//require_once("class/class_search.php");
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
require_once($_SESSION['config']['businessapppath']."class".$_SESSION['slash_env'].'class_list_show.php');
require_once($_SESSION['pathtomodules']."folder".$_SESSION['slash_env'].'class'.$_SESSION['slash_env'].'class_modules_tools.php');
require_once($_SESSION['pathtomodules']."indexing_searching".$_SESSION['slash_env'].'class'.$_SESSION['slash_env'].'class_modules_tools.php');
$folder = new folder();
$is = new indexing_searching();
$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$func = new functions();
/****************Management of the location bar  ************/
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
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=search_adv_folder_result&module=folder';
$page_label = _RESULTS;
$page_id = "folder_search_adv_result";
$core_tools->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
$fields = "";
$orderby = "";
// define the row of the start
if(isset($_REQUEST['start']))
{
	$start = $_REQUEST['start'];
}
else
{
	$start = 0;
}
$search_link = '';
$where_request = "";
if($_SESSION['config']['databasetype'] == "SQLSERVER")
{
	$_SESSION['date_pattern'] = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";
}
 else // MYSQL & POSTGRESQL
{
	$_SESSION['date_pattern'] = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";
}
$where_clause = '';
// FOLDER DATA
if(isset($_REQUEST['foldertype_id']) && !empty($_REQUEST['foldertype_id']))
{
	$_SESSION['folder_search']['foldertype_id'] = trim($_REQUEST['foldertype_id']);
	$where_request .= " ".$_SESSION['tablename']['fold_folders'].".foldertype_id = ".$func->protect_string_db($_SESSION['folder_search']['foldertype_id'])." and ";
}
else
{
	$_SESSION['folder_search']['foldertype_id'] = "";
}
if(isset($_REQUEST['folder_id']) && !empty($_REQUEST['folder_id']))
{
	$_SESSION['folder_search']['folder_id'] = trim($_REQUEST['folder_id']);
	if($_SESSION['config']['databasetype'] == "POSTGRESQL")
	{
		$where_request .= " ".$_SESSION['tablename']['fold_folders'].".folder_id ilike '".$func->protect_string_db($_SESSION['folder_search']['folder_id'],$_SESSION['config']['databasetype'])."%' and ";
	}
	else
	{
		$where_request .= " ".$_SESSION['tablename']['fold_folders'].".folder_id like '".$func->protect_string_db($_SESSION['folder_search']['folder_id'],$_SESSION['config']['databasetype'])."%' and ";
	}
}
else
{
	$_SESSION['folder_search']['folder_id'] = '';
}

// RESOURCE DATA
if(isset($_REQUEST['fold_custom_t5']) && !empty($_REQUEST['fold_custom_t5']))
{
	$_SESSION['folder_search']['custom_t5'] = trim($_REQUEST['fold_custom_t5']);
	if($_SESSION['config']['databasetype'] == "POSTGRESQL")
	{
		$where_request .= " ".$_SESSION['tablename']['fold_folders'].".custom_t5 ilike '%".$_SESSION['folder_search']['custom_t5']."%' and ";
	}
	else
	{
		$where_request .= " ".$_SESSION['tablename']['fold_folders'].".custom_t5 like '%".$_SESSION['folder_search']['custom_t5']."%' and ";
	}
}
else
{
	$_SESSION['folder_search']['custom_t5'] = '';
}
if(isset($_REQUEST['folder_name']) && !empty($_REQUEST['folder_name']))
{
	$_SESSION['folder_search']['folder_name'] = trim($_REQUEST['folder_name']);
	if($_SESSION['config']['databasetype'] == "POSTGRESQL")
	{
		$where_request .= "folder_name ilike '%".$_SESSION['folder_search']['folder_name']."' and ";
	}
	else
	{
		$where_request .= "folder_name like '%".$_SESSION['folder_search']['folder_name']."' and ";
	}
}
else
{
	$_SESSION['folder_search']['folder_name'] = '';
}
if(!empty($_REQUEST['start_archive_date']) && isset($_REQUEST['start_archive_date']))
{
	if( preg_match($_SESSION['date_pattern'],$_REQUEST['start_archive_date'])==false )
	{
		$_SESSION['error'] = _WRONG_DATE_FORMAT;
		$_SESSION['folder_search']['start_archive_date'] = '';
	}
	else
	{
		$_SESSION['folder_search']['start_archive_date'] = $func->format_date_db($_REQUEST['start_archive_date']);
		$where_request .= " (date(folders.creation_date) >= '".$_SESSION['folder_search']['start_archive_date']."') and ";
	}
}
$_SESSION['folder_search']['end_archive_date'] ='';
if(!empty($_REQUEST['end_archive_date']) && isset($_REQUEST['end_archive_date']))
{
	if( preg_match($_SESSION['date_pattern'],$_REQUEST['end_archive_date'])==false )
	{
		$_SESSION['error'] = _WRONG_DATE_FORMAT;
	}
	else
	{
		$_SESSION['folder_search']['end_archive_date'] = $func->format_date_db($_REQUEST['end_archive_date']);
		$where_request .= " (date(folders.creation_date) <= '".$_SESSION['folder_search']['end_archive_date']."') and ";
	}
}
else
{
	$_SESSION['folder_search']['end_archive_date'] = '';
}
foreach(array_keys($_REQUEST) as $value)
{

	if(preg_match('/^doc_/', $value))
	{
		if(!empty($_REQUEST[$value]))
		{
			$where_request = $is->user_exit($value, $where_request, false, true);
		}
		$_SESSION['folder_search'][$value] = $_REQUEST[$value];
	}
}
if(!empty($_SESSION['error']))
{
	$func->echo_error(_ADV_SEARCH_FOLDER_TITLE, "<br /><div class=\"error\">"._MUST_CORRECT_ERRORS." : <br /><br /><strong>".$_SESSION['error']."<br /><a href=\"".$_SESSION['config']['businessappurl']."index.php?page=search_adv_folder&module=folder\">"._CLICK_HERE_TO_CORRECT."</a></strong></div>", 'title', $_SESSION['urltomodules']."indexing_searching/img/picto_search_b.gif");
}
else
{
	$fields = substr($fields,0,strlen($fields)-1);
	$where_request = trim($where_request);
	//$where_request = preg_replace("/and$/", " ",$where_request);
}
if(empty($_SESSION['error']))
{
	$select = array();
	$select[$_SESSION['tablename']['fold_folders']]= array();
	$select[$_SESSION['tablename']['fold_foldertypes']] = array();
	//$select['people']= array();
	//$select['people_folders']= array();
	array_push($select[$_SESSION['tablename']['fold_folders']],"folders_system_id as res_id", "folder_id", "folder_name", "creation_date");
	//array_push($select['people'],"people_id", "first_name", "last_name");
	//array_push($select['people_folders'],"folder_id", "people_id");
	//array_push($select[$_SESSION['tablename']['fold_foldertypes']],"foldertype_label");
	if($_SESSION['config']['databasetype'] == "POSTGRESQL")
	{
		$where_request .= " ".$_SESSION['tablename']['fold_folders'].".foldertype_id = ".$_SESSION['tablename']['fold_foldertypes'].".foldertype_id ";
	}
	else
	{
		$where_request .= " ".$_SESSION['tablename']['fold_folders'].".foldertype_id = ".$_SESSION['tablename']['fold_foldertypes'].".foldertype_id ";
	}
	$where_request.= " and status <> 'ATT' and status <> 'REP' and status <> 'DEL' ";
	if(!empty($where_clause))
	{
		$where_request = '('.$where_request.") and (".$where_clause.')';
	}
	$list=new list_show();
	$order = '';
	if(isset($_REQUEST['order']) && !empty($_REQUEST['order']))
	{
		$order = trim($_REQUEST['order']);
	}
	$field = '';
	if(isset($_REQUEST['field']) && !empty($_REQUEST['field']))
	{
		$field = trim($_REQUEST['field']);
	}
	$orderstr = $list->define_order($order, $field);
	$request = new request();
	$tab=$request->select($select,$where_request,$orderstr,$_SESSION['config']['databasetype']);
	//$request->show();exit;
	$_SESSION['error_page'] = '';
	//build the tab with right forma for list_doc function
	if (count($tab) > 0)
	{
		for ($i=0;$i<count($tab);$i++)
		{
			for ($j=0;$j<count($tab[$i]);$j++)
			{
				foreach(array_keys($tab[$i][$j]) as $value)
				{
					if($tab[$i][$j][$value]=='res_id')
					{
						$tab[$i][$j]['res_id']=$tab[$i][$j]['value'];
						$tab[$i][$j]["label"]=_GED_NUM;
						$tab[$i][$j]["size"]="4";
						$tab[$i][$j]["label_align"]="left";
						$tab[$i][$j]["align"]="center";
						$tab[$i][$j]["valign"]="bottom";
						$tab[$i][$j]["show"]=false;
						$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
						$tab[$i][$j]["order"]='folders_system_id';
					}
					if ($tab[$i][$j][$value] == "folder_id")
					{
						$tab[$i][$j]["label"]=_FOLDERID;
						$tab[$i][$j]["size"]="4";
						$tab[$i][$j]["label_align"]="left";
						$tab[$i][$j]["align"]="center";
						$tab[$i][$j]["valign"]="bottom";
						$tab[$i][$j]["show"]=true;
						$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
						$tab[$i][$j]['value']=$tab[$i][$j]['value'];
						$tab[$i][$j]["order"]="folder_id";
					}
					if($tab[$i][$j][$value]=="folder_name")
					{
						$tab[$i][$j]["label"]=_FOLDERNAME;
						$tab[$i][$j]["size"]="15";
						$tab[$i][$j]["label_align"]="left";
						$tab[$i][$j]["align"]="left";
						$tab[$i][$j]["valign"]="bottom";
						$tab[$i][$j]["show"]=true;
						$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
						$tab[$i][$j]["order"]="folder_name";
					}
					if($tab[$i][$j][$value]=="creation_date")
					{
						$tab[$i][$j]["label"]=_FOLDERDATE;
						$tab[$i][$j]["value"] = $func->format_date($tab[$i][$j]["value"]);
						$tab[$i][$j]["size"]="15";
						$tab[$i][$j]["label_align"]="right";
						$tab[$i][$j]["align"]="right";
						$tab[$i][$j]["valign"]="bottom";
						$tab[$i][$j]["show"]=true;
						$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
						$tab[$i][$j]["order"]="creation_date";
					}
				}
			}
		}


		?>
        <h1><img src="<?php  echo $_SESSION['urltomodules']."indexing_searching/img/picto_search_b.gif";?>" alt="" /> <?php  echo _SEARCH_RESULTS." - ".count($tab)." "._FOUND_FOLDER;?></h1>
        <div id="inner_content"><?php

	$list->list_doc($tab,count($tab),'','folders_system_id','search_adv_folder_result','res_id','show_folder&module=folder',false,false,'','','',true,true,false, false,false,false,true,false,'', 'folder',false,'','','listing spec','','',true);
		?></div><?php
	}
	else
	{
		$func->echo_error(_ADV_SEARCH_FOLDER_TITLE,"<p class=\"error\"><img src=\"".$_SESSION['config']['img']."/noresult.gif\" /><br />"._NO_RESULTS."</p>", 'title',  $_SESSION['urltomodules']."indexing_searching/img/picto_search_b.gif");
	}
}
?>

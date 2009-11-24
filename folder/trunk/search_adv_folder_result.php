<?php

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR.'class_list_show.php');
require_once("modules".DIRECTORY_SEPARATOR."folder".DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_admin_foldertypes.php');

$core_tools = new core_tools();
$func = new functions();
$req = new request();
$foldertype = new foldertype();
$core_tools->load_lang();

//$core_tools->test_service('folder_search', 'folder');
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
$date_pattern = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";

$where_clause = '';
// FOLDER DATA

// Foldertype
if(isset($_REQUEST['foldertype_id']) && !empty($_REQUEST['foldertype_id']))
{
	$_SESSION['folder_search']['foldertype_id'] = trim($_REQUEST['foldertype_id']);
	$where_request .= " ".$_SESSION['tablename']['fold_folders'].".foldertype_id = ".$_SESSION['folder_search']['foldertype_id']." and ";
}
else
{
	$_SESSION['folder_search']['foldertype_id'] = "";
}

// Folder id
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

// Creation date
$_SESSION['folder_search']['creation_date_start'] = '';
if(!empty($_REQUEST['creation_date_start']) && isset($_REQUEST['creation_date_start']))
{
	if( preg_match($date_pattern,$_REQUEST['creation_date_start'])==false )
	{
		$_SESSION['error'] = _WRONG_DATE_FORMAT;

	}
	else
	{
		$_SESSION['folder_search']['creation_date_start'] = $func->format_date_db($_REQUEST['creation_date_start']);
		$where_request .= " (".$req->extract_date($_SESSION['tablename']['fold_folders'].'.creation_date')." >= '".$_SESSION['folder_search']['creation_date_start']."') and ";
	}
}
$_SESSION['folder_search']['creation_date_end'] ='';
if(!empty($_REQUEST['creation_date_end']) && isset($_REQUEST['creation_date_end']))
{
	if( preg_match($date_pattern,$_REQUEST['creation_date_end'])==false )
	{
		$_SESSION['error'] = _WRONG_DATE_FORMAT;
	}
	else
	{
		$_SESSION['folder_search']['creation_date_end'] = $func->format_date_db($_REQUEST['creation_date_end']);
		$where_request .= " (".$req->extract_date($_SESSION['tablename']['fold_folders'].'.creation_date')." <= '".$_SESSION['folder_search']['creation_date_end']."') and ";
	}
}

///////////// Optional indexes
if(isset($_SESSION['folder_search']['foldertype_id']) && !empty($_SESSION['folder_search']['foldertype_id']))
{
	$indexes = $foldertype->get_indexes($_SESSION['folder_search']['foldertype_id']) ;

	foreach( array_keys($indexes) as $key)
	{
		if(isset($_REQUEST[$key]) && !empty($_REQUEST[$key]))
		{
			$_SESSION['folder_search'][$key] = $_REQUEST[$key];
			$where_request .= $foldertype->search_checks($indexes, $key, $_REQUEST[$key]);
		}
		elseif(isset($_REQUEST[$key.'_from']) && !empty($_REQUEST[$key.'_from']))
		{
			$_SESSION['folder_search'][$key.'_from'] = $_REQUEST[$key.'_from'];
			$where_request .= $foldertype->search_checks($indexes, $key.'_from', $_REQUEST[$key.'_from']);
		}
		elseif( isset($_REQUEST[$key.'_to']) && !empty($_REQUEST[$key.'_to']))
		{
			$_SESSION['folder_search'][$key.'_to'] = $_REQUEST[$key.'_to'];
			$where_request .= $foldertype->search_checks($indexes, $key.'_to', $_REQUEST[$key.'_to']);
		}
		elseif( isset($_REQUEST[$key.'_max'])  && !empty($_REQUEST[$key.'_max']))
		{
			$_SESSION['folder_search'][$key.'_max'] = $_REQUEST[$key.'_max'];
			$where_request .= $foldertype->search_checks($indexes, $key.'_max', $_REQUEST[$key.'_max']);
		}
		elseif(isset($_REQUEST[$key.'_min']) && !empty($_REQUEST[$key.'_min']))
		{
			$_SESSION['folder_search'][$key.'_min'] = $_REQUEST[$key.'_min'];
			$where_request .= $foldertype->search_checks($indexes, $key.'_min', $_REQUEST[$key.'_min']);
		}
	}
}

if(!empty($_SESSION['error']))
{
	$func->echo_error(_ADV_SEARCH_FOLDER_TITLE, "<br /><div class=\"error\">"._MUST_CORRECT_ERRORS." : <br /><br /><strong>".$_SESSION['error']."<br /><a href=\"".$_SESSION['config']['businessappurl']."index.php?page=search_adv_folder&module=folder\">"._CLICK_HERE_TO_CORRECT."</a></strong></div>", 'title', $_SESSION['urltomodules']."folder/img/picto_search_b.gif");
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

	array_push($select[$_SESSION['tablename']['fold_folders']],"folders_system_id", "folder_id", "folder_name", "creation_date");

	$where_request .= " ".$_SESSION['tablename']['fold_folders'].".foldertype_id = ".$_SESSION['tablename']['fold_foldertypes'].".foldertype_id ";

	$where_request.= " and  status <> 'DEL' ";
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

	$tab=$req->select($select,$where_request,$orderstr,$_SESSION['config']['databasetype']);
	//$req->show();
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
					if($tab[$i][$j][$value]=='folders_system_id')
					{
						$tab[$i][$j]['folders_system_id']=$tab[$i][$j]['value'];
						$tab[$i][$j]["label"]='';
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
						$tab[$i][$j]["size"]="20";
						$tab[$i][$j]["label_align"]="left";
						$tab[$i][$j]["align"]="center";
						$tab[$i][$j]["valign"]="bottom";
						$tab[$i][$j]["show"]=true;
						$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
						$tab[$i][$j]['value']=$tab[$i][$j]['value'];
						$tab[$i][$j]["order"]="folder_id";
					}
					if ($tab[$i][$j][$value] == "folder_name")
					{
						$tab[$i][$j]["label"]=_FOLDERNAME;
						$tab[$i][$j]["size"]="20";
						$tab[$i][$j]["label_align"]="left";
						$tab[$i][$j]["align"]="center";
						$tab[$i][$j]["valign"]="bottom";
						$tab[$i][$j]["show"]=true;
						$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
						$tab[$i][$j]['value']=$tab[$i][$j]['value'];
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
        <h1><img src="<?php  echo $_SESSION['urltomodules']."folder/img/picto_search_b.gif";?>" alt="" /> <?php  echo _SEARCH_RESULTS." - ".count($tab)." "._FOUND_FOLDER;?></h1>
        <div id="inner_content"><?php

	$list->list_doc($tab,count($tab),'','folders_system_id','search_adv_folder_result','folders_system_id','show_folder&module=folder',false,false,'','','',true,true,false, false,false,false,true,false,'', 'folder',false,'','','listing spec','','',true);
		?></div><?php
	}
	else
	{
		$func->echo_error(_ADV_SEARCH_FOLDER_TITLE,"<p class=\"error\"><img src=\"".$_SESSION['config']['img']."/noresult.gif\" /><br />"._NO_RESULTS."</p>", 'title',  $_SESSION['urltomodules']."folder/img/picto_search_b.gif");
	}
}
?>

<?php
require_once($_SESSION['pathtocoreclass']."class_functions.php");
$admin = new core_tools();
$admin->test_admin('admin_foldertypes', 'folder');
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
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=foldertypes&module=folder';
$page_label = _FOLDERTYPES_LIST;
$page_id = "foldertypes";
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR."class_list_show.php");
$func = new functions();
$select[$_SESSION['tablename']['fold_foldertypes']] = array();
array_push($select[$_SESSION['tablename']['fold_foldertypes']],"foldertype_id","foldertype_label" );
$what = "";
$where ="";
if(isset($_REQUEST['what']))
{
	$what = $func->protect_string_db($_REQUEST['what']);
	$where = "foldertype_label like '".strtolower($what)."%' or foldertype_label like '".strtoupper($what)."%' ";
}
$list = new list_show();
$order = 'asc';
if(isset($_REQUEST['order']) && !empty($_REQUEST['order']))
{
	$order = trim($_REQUEST['order']);
}
$field = 'foldertype_label';
if(isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field']))
{
	$field = trim($_REQUEST['order_field']);
}

$orderstr = $list->define_order($order, $field);

$request= new request;
$tab = $request->select($select, $where, $orderstr, $_SESSION['config']['databasetype']);

for($i=0;$i<count($tab);$i++)
{
	for ($j=0;$j<count($tab[$i]);$j++)
	{
		foreach(array_keys($tab[$i][$j]) as $value)
		{

			if($tab[$i][$j][$value]=="foldertype_id")
			{
				$tab[$i][$j]["foldertype_id"]=$tab[$i][$j]['value'];
				$tab[$i][$j]["label"]= _ID;
				$tab[$i][$j]["size"]="20";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='foldertype_id';
			}
			if($tab[$i][$j][$value]=="foldertype_label")
			{
				$tab[$i][$j]["value"]=$request->show_string($tab[$i][$j]['value']);
				$tab[$i][$j]["foldertype_label"]=$tab[$i][$j]['value'];
				$tab[$i][$j]["label"]=_DESC;
				$tab[$i][$j]["size"]="45";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='foldertype_label';
			}
		}
	}
}
$page_name = "foldertypes";
$page_name_up = "foldertype_up";
$page_name_del = "foldertype_del";
$page_name_val= "";
$page_name_ban = "";
$page_name_add = "foldertype_add";
$label_add = _FOLDERTYPE_ADDITION;

$_SESSION['m_admin']['init'] = true;
$_SESSION['m_admin']['foldertype']['foldertypeId'] = "";
$_SESSION['m_admin']['foldertype']['desc'] = "";
$_SESSION['m_admin']['foldertype']['comment'] = "";
$_SESSION['m_admin']['foldertype']['doctypes'] = array();
$_SESSION['m_admin']['load_doctypes'] = true;
$title = _FOLDERTYPES_LIST." : ".$i." "._TYPES;
$autoCompletionArray = array();
$autoCompletionArray["list_script_url"] = $_SESSION['urltomodules']."folder/foldertype_list_by_name.php";
$autoCompletionArray["number_to_begin"] = 1;

$list->admin_list($tab, $i, $title, 'foldertype_id','foldertypes','folder','foldertype_id', true, $page_name_up, $page_name_val, $page_name_ban, $page_name_del, $page_name_add, $label_add, false, false, _ALL_FOLDERTYPES, _FOLDERTYPE, $_SESSION['urltomodules'].'folder/img/manage_foldertypes_b.gif', true, true, false, true, "", true, $autoCompletionArray);
?>

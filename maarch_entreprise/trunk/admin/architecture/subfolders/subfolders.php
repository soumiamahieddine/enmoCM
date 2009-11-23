<?php
/**
* File : sous_dossiers.php
*
* Subfolders list
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 06/2006x
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
//include('core/init.php');

$admin = new core_tools();
$admin->test_admin('admin_architecture', 'apps');
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
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=subfolders';
$page_label = _SUBFOLDER_LIST;
$page_id = "subfolders";
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['businessapps'][0]['appid'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
$func = new functions();
$select[$_SESSION['tablename']['doctypes_second_level']] = array();
array_push($select[$_SESSION['tablename']['doctypes_second_level']],"doctypes_second_level_id","doctypes_first_level_id","doctypes_second_level_label");
$what = "";
$where= " enabled = 'Y' ";
if(isset($_REQUEST['what']) && !empty($_REQUEST['what']))
{
	$what = $func->protect_string_db($_REQUEST['what']);
	if($_SESSION['config']['databasetype'] == "POSTGRESQL")
	{
		$where .= " and (doctypes_second_level_label ilike '".strtolower($what)."%' or doctypes_second_level_label ilike '".strtoupper($what)."%') ";
	}
	else
	{
		$where .= " and (doctypes_second_level_label like '".strtolower($what)."%' or doctypes_second_level_label like '".strtoupper($what)."%') ";
	}
}
$list = new list_show();
$order = 'asc';
if(isset($_REQUEST['order']) && !empty($_REQUEST['order']))
{
	$order = trim($_REQUEST['order']);
}
$field = 'doctypes_second_level_label';
if(isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field']))
{
	$field = trim($_REQUEST['order_field']);
}

$orderstr = $list->define_order($order, $field);

$request= new request;
$tab=$request->select($select,$where,$orderstr ,$_SESSION['config']['databasetype']);
for ($i=0;$i<count($tab);$i++)
{
	for ($j=0;$j<count($tab[$i]);$j++)
	{
		foreach(array_keys($tab[$i][$j]) as $value)
		{

			if($tab[$i][$j][$value]=="doctypes_second_level_id")
			{
				$tab[$i][$j]["doctypes_second_level_id"]=$tab[$i][$j]['value'];
				$tab[$i][$j]["label"]=_ID;
				$tab[$i][$j]["size"]="10";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='doctypes_second_level_id';
			}
			if($tab[$i][$j][$value]=="doctypes_first_level_id")
			{
				$tab[$i][$j]["doctypes_first_level_id"]=$tab[$i][$j]['value'];
				$tab[$i][$j]["label"]=_STRUCTURE;
				$tab[$i][$j]["size"]="10";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='doctypes_first_level_id';
			}
			if($tab[$i][$j][$value]=="doctypes_second_level_label")
			{
				$tab[$i][$j]['value']=$request->show_string($tab[$i][$j]['value']);
				$tab[$i][$j]["doctypes_second_level_label"]=$tab[$i][$j]['value'];
				$tab[$i][$j]["label"]=_DESC;
				$tab[$i][$j]["size"]="30";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='doctypes_second_level_label';
			}
		}
	}
}
$page_name = "subfolders";
$page_name_up = "subfolder_up";
$page_name_add = "subfolder_up";
$page_name_del = "subfolder_del";
$page_name_val = "";
$table_name = $_SESSION['tablename']['doctypes_second_level'];
$page_name_ban = "";
$label_add = _ADD_SUBFOLDER;
$_SESSION['m_admin']['structures'] = array();
$request->query("select * from ".$_SESSION['tablename']['doctypes_first_level']." where enabled = 'Y'");
while($res = $request->fetch_object())
{
	array_push($_SESSION['m_admin']['structures'], array('ID' => $res->doctypes_first_level_id, 'LABEL'=> $request->show_string($res->doctypes_first_level_label)));
}
$autoCompletionArray = array();
$autoCompletionArray["list_script_url"] = $_SESSION['config']['businessappurl']."index.php?display=true&page=subfolders_list_by_name";
$autoCompletionArray["number_to_begin"] = 1;

//$list->listletters('sous_dossiers','Tous les sous-dossiers',$_SESSION['lang']['txt_searching']." sous-dossier ",$_SESSION['lang']['txt_alphabetical_list']);
$list->admin_list($tab, $i, _SUBFOLDER_LIST.' : '.$i." ".strtolower(_SUBFOLDERS), 'doctypes_second_level_id','subfolders','subfolders','doctypes_second_level_id', true, $page_name_up, $page_name_val, $page_name_ban, $page_name_del, $page_name_add, $label_add, FALSE, TRUE, _ALL_SUBFOLDERS, _SUBFOLDER, $_SESSION['config']['img'].'/gerer_sous-dossiers_b.gif', false, true, false, true, "", true, $autoCompletionArray);
?>

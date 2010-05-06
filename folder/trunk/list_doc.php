<?php
/**
* File : result_folder.php
*
* Frame : show the folders corresponding to the search (folder select in indexing process)
*
* @package  Maarch PeopleBox 1.0
* @version 1.0
* @since 10/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
$core_tools = new core_tools();
$core_tools->load_lang();
$security = new security();
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
require_once("modules".DIRECTORY_SEPARATOR."folder".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
$func = new functions();
$core_tools->load_html();
//here we building the header
$core_tools->load_header('', true, false);
for($i=0; $i<count($_SESSION['user']['security']);$i++)
{
	if($_SESSION['user']['security'][$i]['coll_id'] == $_SESSION['current_foldertype_coll_id'])
	{
		$where_clause = $_SESSION['user']['security'][$i]['where'];
		break;
	}
}
?>
<body>
<?php
if(isset($_REQUEST['listid']) && $_REQUEST['listid'] <> "")
{
	$list_id = substr($_REQUEST['listid'], 0, strlen($_REQUEST['listid'])-1);
}
elseif(isset($_SESSION['where_list_doc']) && $_SESSION['where_list_doc'] <> "")
{
	$list_id = $_SESSION['where_list_doc'];
}
if(isset($list_id) && $list_id <> "")
{
	$table_view = $security->retrieve_view_from_coll_id($_SESSION['current_foldertype_coll_id']);

	$_SESSION['collection_id_choice'] = $_SESSION['current_foldertype_coll_id'];
	$details = $security->get_script_from_coll($_SESSION['collection_id_choice'], 'script_details');
	$details = preg_replace( '/.php/', '', $details);

	$select[$table_view]= array();
	array_push($select[$table_view],"res_id, type_label, creation_date");
	$where = "res_id in (".$list_id.") and (".$where_clause.")";
	$_SESSION['where_list_doc'] = $list_id;
	$request= new request;
	$tab=$request->select($select,$where," order by res_id ",$_SESSION['config']['databasetype'],"500",false,"","","",true,true);
	//$request->show();
	$folder_tmp = new folder();
	for ($i=0;$i<count($tab);$i++)
	{
		for ($j=0;$j<count($tab[$i]);$j++)
		{
			foreach(array_keys($tab[$i][$j]) as $value)
			{
				if($tab[$i][$j][$value]=="res_id")
				{
					$tab[$i][$j]["res_id"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_GED_NUM;
					$tab[$i][$j]["size"]="2";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
				if($tab[$i][$j][$value]=="type_label")
				{
					$tab[$i][$j]["res_id"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_TYPE;
					$tab[$i][$j]["size"]="2";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
				if($tab[$i][$j][$value]=="creation_date")
				{
					$tab[$i][$j]["creation_date"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]= _CREATION_DATE;
					$tab[$i][$j]["size"]="2";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
			}
		}
	}
	//$request->show_array($tab);
	$list=new list_show();
	$ind = count($tab);
	$list->list_doc($tab, $ind, $ind." "._FOUND_DOC, "res_id&listid=".$_REQUEST['listid'], "list_doc", "res_id", $details."&dir=indexing_searching", true, false, "", "", "", true, false, true, false, false, false, true, false, '', 'folder', false, '', '', 'listingsmall');
}
else
{
	//echo "_SELECT_SUBFOLDER";
}
$_SESSION['where_list_doc'] = "";
$core_tools->load_js();
?>
</body>
</html>

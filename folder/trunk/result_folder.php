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
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php"); 
$core_tools = new core_tools();
$core_tools->load_lang();
require_once($_SESSION['pathtomodules']."folder".$_SESSION['slash_env']."class".$_SESSION['slash_env']."class_modules_tools.php");
require_once($_SESSION['config']['businessapppath']."class".$_SESSION['slash_env']."class_list_show.php");
$func = new functions();
$core_tools->load_html();
//here we building the header
$core_tools->load_header();
?>
<body>
<?php 
if(isset($_SESSION['res_folder'])and !empty($_SESSION['res_folder']))
{
	$select[$_SESSION['tablename']['fold_folders']]= array();

	array_push($select[$_SESSION['tablename']['fold_folders']],"folders_system_id","folder_id","folder_name","custom_t2","custom_d1", "custom_t10");
	$select[$_SESSION['tablename']['fold_foldertypes']]= array();
	array_push($select[$_SESSION['tablename']['fold_foldertypes']],"foldertype_label");
	
	
	if($_SESSION['config']['databasetype'] == "POSTGRESQL")
	{
		$where = " ".$_SESSION['tablename']['fold_folders'].".foldertype_id = ".$_SESSION['tablename']['fold_foldertypes'].".foldertype_id ";
	}
	else
	{
		$where = " ".$_SESSION['tablename']['fold_folders'].".foldertype_id = ".$_SESSION['tablename']['fold_foldertypes'].".foldertype_id ";
	}
	if($_SESSION['res_folder'] == 'matricule' )
	{
		if($_SESSION['config']['databasetype'] == "POSTGRESQL")
		{
			$where .= "  and folder_id ilike '%".$func->protect_string_db($_SESSION['search_res_folder'],$_SESSION['config']['databasetype'])."%' and status <> 'DEL'";	
		}
		else
		{
			$where .= " and folder_id like '%".$func->protect_string_db($_SESSION['search_res_folder'],$_SESSION['config']['databasetype'])."%' and status <> 'DEL'";	
		}
	}
	if( $_SESSION['res_folder'] == 'nom' )
	{
		if($_SESSION['config']['databasetype'] == "POSTGRESQL")
		{
			$where .= "  and ".$_SESSION['tablename']['fold_folders'].".folder_name ilike '%".$func->protect_string_db($_SESSION['search_res_folder'],$_SESSION['config']['databasetype'])."%' and status <> 'DEL'";
		}
		else
		{
			$where .= "  and ".$_SESSION['tablename']['fold_folders'].".folder_name like '%".$func->protect_string_db($_SESSION['search_res_folder'],$_SESSION['config']['databasetype'])."%' and status <> 'DEL'";
		}
	}
	$request= new request;
	$tab=$request->select($select,$where," order by folder_name ",$_SESSION['config']['databasetype']);
	//$request->show();
	$folder_tmp = new folder();
	for ($i=0;$i<count($tab);$i++)
	{
		for ($j=0;$j<count($tab[$i]);$j++)
		{
			foreach(array_keys($tab[$i][$j]) as $value)
			{
				if($tab[$i][$j][$value]=="folders_system_id")
				{
					$tab[$i][$j]["folders_system_id"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_GED_NUM;
					$tab[$i][$j]["size"]="2";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=false;
				}
				if($tab[$i][$j][$value]=="folder_id")
				{
					$tab[$i][$j]['res_id']=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_FOLDER;
					$tab[$i][$j]["size"]="2";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
				if($tab[$i][$j][$value]=="folder_name")
				{
					$tab[$i][$j]["value"]=$request->show_string($tab[$i][$j]["value"]);
					$tab[$i][$j]["label"]=_LASTNAME;
					$tab[$i][$j]["size"]="2";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
				/*if($tab[$i][$j][$value]=="custom_t2")
				{	
					$tab[$i][$j]["value"]=$request->show_string($tab[$i][$j]["value"]);				
					$tab[$i][$j]["label"]=_FIRSTNAME;
					$tab[$i][$j]["size"]="2";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}*/
				if($tab[$i][$j][$value]=="custom_t10")
				{
					$tab[$i][$j]["custom_t10"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_ID." "._SOCIETY;
					$tab[$i][$j]["size"]="2";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=false;
				}
				if($tab[$i][$j][$value]=="foldertype_label")
				{	
					$tab[$i][$j]["value"]=$request->show_string($tab[$i][$j]["value"]);				
					$tab[$i][$j]["label"]=_FOLDERTYPE_LABEL;
					$tab[$i][$j]["size"]="3";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
			}
		}
	}
	for ($i=0;$i<count($tab);$i++)
	{
		for ($j=0;$j<count($tab[$i]);$j++)
		{
			foreach(array_keys($tab[$i][$j]) as $value)
			{	
				if($value == 'column' and $tab[$i][$j][$value]=="folders_system_id")
				{		
					$tmp = array();
					$id = $tab[$i][$j]['value'];
					
					$folder_tmp->load_folder1($id,$_SESSION['tablename']['fold_folders']);
					array_push($tab[$i], $tmp);
				}
			}
		}
	}
	//$request->show_array($tab);
	$list=new list_show();
	$ind = count($tab);
	$list->list_doc($tab,$ind,_SEARCH_RESULTS." : ".$ind." "._FOUND_FOLDERS,"folders_system_id","result_folder","folders_system_id","folder_detail",false,true,"get",$_SESSION['urltomodules']."folder/res_select_folder.php",_CHOOSE, false, false, true, false, false, false,  false, false, '', '', false, '', '', 'listingsmall');	
}
?>
</body>
</html>
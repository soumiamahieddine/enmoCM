<?php
/**
* File : folder_popup.php
*
* Show the missing docs in a folder (used in the stats)
*
* @package  Maarch PeopleBox 1.0
* @version 2.0
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
include('core/init.php');


require_once("core/class/class_functions.php");
require_once("core/class/class_db.php");
require_once("core/class/class_request.php");
require_once("core/class/class_core_tools.php");
$core_tools = new core_tools();
$core_tools->load_lang();

require_once("apps/".$_SESSION['businessapps'][0]['appid']."/class".DIRECTORY_SEPARATOR."class_list_show.php");
 $core_tools->load_html();
//here we building the header
$core_tools->load_header();
?>
<body>
<?php

$select = array();
$select[$_SESSION['collections'][0]['view']]= array();
//$select[$_SESSION['tablename']['fold_folders']]= array();
//$select[$_SESSION['tablename']['doctypes']]= array();

array_push($select[$_SESSION['collections'][0]['view']],"res_id",  'type_label');
//array_push($select[$_SESSION['tablename']['doctypes']], "description");

$where_request.= " folder_id = '".$_GET['id']."' AND status <> 'ATT' AND status <> 'REP' AND status <> 'DEL' ";

$request = new request();
$tab=$request->select($select,$where_request,"",$_SESSION['config']['databasetype']);

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
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["value_export"]=$tab[$i][$j]['value'];
				}

				if($tab[$i][$j][$value]=="type_label")
				{
					$tab[$i][$j]["label"]=_TYPE;
					$tab[$i][$j]["size"]="15";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["value_export"]=$tab[$i][$j]['value'];
				}
			}
		}
	}
}
	//$request->show_array($tab);

	$title = _FOLDER." ".$_GET['id']." : ".count($tab)." "._FOUND_DOC;

$list=new list_show();
?><div align="center"><?php
$list->list_doc($tab,$i,$title,'res_id','isearch_adv_result','res_id','',false,false,'','','',false,false,false,false,true);

?>
</div>
</body>
</html>

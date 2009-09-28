<?php
/*
*    Copyright 2008,2009 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
* Deprecated file : to adapt to the new basket modifications
*/
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
require_once($_SESSION['config']['businessapppath']."class".$_SESSION['slash_env']."class_list_show.php");
require_once($_SESSION['pathtocoreclass']."class_security.php");

$security = new security();
$core_tools = new core_tools();
$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header();
?>
<body>
<br/><br/>
<?php
if(!empty($_SESSION['current_basket']['view']))
{
	$table = $_SESSION['current_basket']['view'];
}
else
{
	$table = $_SESSION['current_basket']['table'];
}
$select[$table]= array();
$_SESSION['collection_id_choice'] = $_SESSION['current_basket']['coll_id'];
array_push($select[$table],"res_id","destination",'creation_date');
$where = $_SESSION['current_basket']['clause'];
$db = new db_query();
$db-> connexion();
$db->query("select distinct destination, entity_label from ".$_SESSION['tablename']['users']." ".$where);

$request= new request;
$tab=$request->select($select,$where,"",$_SESSION['config']['databasetype']);

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
				$tab[$i][$j]["size"]="4";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=false;
			}
			if($tab[$i][$j][$value]=="destination")
			{
				$tab[$i][$j]['res_id']=$tab[$i][$j]['value'];
				$tab[$i][$j]["label"]=_ENTITY;
				$tab[$i][$j]["size"]="15";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
			}
			if($tab[$i][$j][$value]=='res_id')
			{
				$tab[$i][$j]["label"]=_GED_NUM;
				$tab[$i][$j]["size"]="7";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
			}
			if($tab[$i][$j][$value]=='creation_date')
			{
				$tab[$i][$j]['creation_date']=$request->format_date_db($tab[$i][$j]['value']);
				$tab[$i][$j]['value']=$request->format_date_db($tab[$i][$j]['value']);
				$tab[$i][$j]["label"]=_SAVE_DATE;
				$tab[$i][$j]["size"]="20";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
			}
		}
	}
}

$title = _WAITING_QUAL_LIST." : ".$i." "._DOCS;
$list=new list_show();
$list->list_doc($tab,$i,$title,'res_id',"waiting_res_list",'res_id',"folder_detail",false,false,"get", $_SESSION['urltomodules']."indexing_searching/qualify_doc_main.php",_CHOOSE, false, false, true, false, false, false , true, false, '', '', false, '', '', 'listing spec', '', true);
if(count($tab) > 0)
{
	echo "<em>"._CLICK_LINE_BASKET1."</em>";
}
?>
</body>
</html>

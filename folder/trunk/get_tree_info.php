<?php
/*
*    Copyright 2008 - 2011 Maarch
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

/**
* @brief Returns in a json structure all allowed first branches of a tree for
* the current user (Ajax)
*
* @file
* @author  Claire Figueras  <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup apps
*/
require_once "core/class/class_security.php";
require_once "core/class/class_core_tools.php";
require_once "apps" . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
. DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR
. 'class_business_app_tools.php';
$appTools = new business_app_tools();
$core = new core_tools();
$core->load_lang();
$sec = new security();
$func = new functions();
$db = new dbquery();
$db->connect();
$dbTmp = new dbquery();
$dbTmp->connect();
$db1 = new dbquery();
$db1->connect();
$db2 = new dbquery();
$db2->connect();
$db3 = new dbquery();
$db3->connect();
$db4 = new dbquery();
$db4->connect();
$whereClause = $sec->get_where_clause_from_coll_id($_SESSION['collection_id_choice']);
?>
<script type="text/javascript">
	function hideshow(which){
		if (!document.getElementById)
			return
		if (which.style.display=="block")
			which.style.display="none"
		else
			which.style.display="block"
	}
</script>
<style type="text/css">li{cursor: pointer;}</style>
<?php
$db->connect();
$subject = $_REQUEST['project'];
$pattern = '/\([0-9]*\)/';
preg_match($pattern, substr($subject,3), $matches, PREG_OFFSET_CAPTURE);
$fold_id=str_replace("(", "", $matches[0][0]);
$fold_id=str_replace(")", "", $fold_id);
//print_r($fold_id);
//var_dump($matches[0]);die();

	$db->query(
		"select folders_system_id, folder_name, parent_id from folders WHERE folders_system_id not in (100) order by folder_id asc "
		);



$categories = array();

while($row = $db->fetch_array()) {
	$db2->query(
		"select count(*) as total from folders WHERE parent_id in ('".$row['folders_system_id']."')"
		);
	$row2 = $db2->fetch_array();
	//var_dump($row2);
	$db3->query(
		"select count(*) as total from res_view_letterbox WHERE folders_system_id in ('".$row['folders_system_id']."') AND (".$whereClause.") AND status NOT IN ('DEL')"
		);
	$row3 = $db3->fetch_array();
	//var_dump($row3);
	$db4->query(
		"select res_id, subject,doctypes_first_level_label,doctypes_second_level_label from res_view_letterbox WHERE folders_system_id in ('".$row['folders_system_id']."') AND (".$whereClause.") AND status NOT IN ('DEL')"
		);
	$id_docs='';
	$i=0;
	while($row4 = $db4->fetch_array()){
		if($i==0){
			$id_docs=$row4['res_id'].",".$row4['subject'].",".$row4['doctypes_first_level_label'].",".$row4['doctypes_second_level_label'];

		}else{
			$id_docs.=",".$row4['res_id'].",".$row4['subject'].",".$row4['doctypes_first_level_label'].",".$row4['doctypes_second_level_label'];

		}
		$i++;
	}
	//var_dump($id_docs);

	$categories[] = array(
		'parent_id' => $row['parent_id'],
		'folders_system_id' => $row['folders_system_id'],
		'nom_folder' => $row['folder_name'],
		'nb_child' => $row2['total'],
		'nb_doc' => $row3['total'],
		'id_doc' => $id_docs
		);
}
//print_r($categories);exit;
if($matches[0] != ''){
	echo afficher_arbo($fold_id, 0, $categories,$whereClause,$fold_id);
}else{
	echo afficher_arbo(0, 0, $categories,$whereClause,"");
}


function afficher_arbo($parent, $niveau, $array,$whereClause,$origin)
{
	$db4 = new dbquery();
	$db4->connect();
	$niveau_precedent = 0;
	$niveau_suiv=$niveau+1;

	if (!$niveau && !$niveau_precedent) $html .= "\n<ul id='positionsList'>\n";

	foreach ($array AS $noeud)
	{


		$which="document.getElementById(\"parent_".$noeud['folders_system_id']."_position_".$niveau_suiv."\")";
		$which_doc="document.getElementById(\"parent_".$noeud['folders_system_id']."_position_".$niveau."_doc\")";
		if ($parent == $noeud['parent_id'])
		{
			if ($niveau_precedent < $niveau){
				if($parent==0){
					$html .= "\n<ul style='display:block;' id='parent_".$parent."_position_".$niveau."'>\n";

				}else{
					$html .= "\n<ul style='display:none;' id='parent_".$parent."_position_".$niveau."'>\n";

				}
			} 
			$html .= "<li style='margin-left:20px;display:block;' id='parent_".$parent."_position_".$niveau."' ><span onclick='if (".$which.".style.display==\"block\"){".$which.".style.display=\"none\"}else{".$which.".style.display=\"block\"}'><img src=\"". $_SESSION['config']['businessappurl']. "static.php?filename=folder.gif\" class=\"mt_fclosed\" alt=\"\"></span><a style='color:black;' onclick='if (".$which_doc.".style.display==\"block\"){".$which_doc.".style.display=\"none\"}else{".$which_doc.".style.display=\"block\"}'>" . $noeud['nom_folder']." (".$noeud['nb_child']." sous-dossiers, ".$noeud['nb_doc']." document(s))</a>";
			$tab_doc=explode(',',$noeud['id_doc']);
			if($tab_doc[0]!=''){
				$i=0;
				$html.="<ul id='parent_".$noeud['folders_system_id']."_position_".$niveau."_doc' style='display:none;' >";
				while ($i<sizeof($tab_doc)) {
					$html .= "<li style='font-size: 10px;display:block;margin-left:25px;' id='parent_".$parent."_position_".$niveau."' ><a onclick='updateContent(\"index.php?dir=indexing_searching&page=little_details_invoices&display=true&value=" . $tab_doc[$i]."\", \"docView\");'><ul><li style='margin-left:30px;list-style-image:url(static.php?filename=page.gif);'>".$tab_doc[$i+2]."</li><li style='margin-left:40px;'>".$tab_doc[$i+3]."</li><li style='margin-left:50px;'>" . $tab_doc[$i]." - ".$tab_doc[$i+1]."</li></ul></a>";
					$i=$i+4;
				}
				$html.="</ul>";
			}

			$niveau_precedent = $niveau;
			
			$html .= afficher_arbo($noeud['folders_system_id'], ($niveau + 1), $array, $whereClause,'');
		}
		//var_dump($origin);
			if ($origin!='') {
				$db4->query(
					"select res_id, subject,doctypes_first_level_label,doctypes_second_level_label from res_view_letterbox WHERE folders_system_id in ('".$parent."') AND (".$whereClause.") AND status NOT IN ('DEL')"
					);
				$id_docs='';
				$z=0;
				while($row4 = $db4->fetch_array()){
					if($z==0){
						$id_docs_2=$row4['res_id'].",".$row4['subject'].",".$row4['doctypes_first_level_label'].",".$row4['doctypes_second_level_label'];

					}else{
						$id_docs_2.=",".$row4['res_id'].",".$row4['subject'].",".$row4['doctypes_first_level_label'].",".$row4['doctypes_second_level_label'];

					}
					$z++;
				}
				$tab_doc_2=explode(',',$id_docs_2);
				if($tab_doc_2[0]!=''){
					$z=0;
					$html.="<ul id='parent_0_position_".$niveau."_doc' style='display:block;' >";
					while ($z<sizeof($tab_doc_2)) {
						$html .= "<li style='font-size: 10px;display:block;margin-left:25px;' id='parent_0_position_".$niveau."' ><a onclick='updateContent(\"index.php?dir=indexing_searching&page=little_details_invoices&display=true&value=" . $tab_doc_2[$z]."\", \"docView\");'><ul><li style='margin-left:30px;list-style-image:url(static.php?filename=page.gif);'>".$tab_doc_2[$z+2]."</li><li style='margin-left:40px;'>".$tab_doc_2[$z+3]."</li><li style='margin-left:50px;'>" . $tab_doc_2[$z]." - ".$tab_doc_2[$z+1]."</li></ul></a>";
						$z=$z+4;
					}
					$html.="</ul>";
				}
				$origin='';
			}
	}

	if (($niveau_precedent == $niveau) && ($niveau_precedent != 0)) $html .= "</ul>\n</li>\n";
	else if ($niveau_precedent == $niveau) $html .= "</ul>\n";
	else $html .= "</li>\n";
	return $html;
}
echo $html;

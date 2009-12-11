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

/**
* @brief  Displays a document logs
*
* @file hist_doc.php
* @author Loic Vinet <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup indexing_searching_mlb
*/

$case_id = $result[$theline][0]['case_id'];


$db_external = new dbquery();
$db_external->connect();

$status_obj = new manage_status();
$status = $status_obj->get_not_searchable_status();
$sec = new security();
$func= new functions();
$request = new request();
$status_str = '';
for($i=0; $i<count($status);$i++)
{
	$status_str .=	"'".$status[$i]['ID']."',";
}
$status_str = preg_replace('/,$/', '', $status_str);
$where_request.= "  status not in (".$status_str.") ";
//$where_clause = $sec->get_where_clause_from_coll_id($_SESSION['collection_id_choice']);
$where_clause =" case_id = '".$case_id."' ";


if(!empty($where_request))
{
	if($_SESSION['searching']['where_clause_bis'] <> "")
	{
		$where_clause = "((".$where_clause.") or (".$_SESSION['searching']['where_clause_bis']."))";
	}
	$where_request = '('.$where_request.') and ('.$where_clause.')';
}
else
{
	if($_SESSION['searching']['where_clause_bis'] <> "")
	{
		$where_clause = "((".$where_clause.") or (".$_SESSION['searching']['where_clause_bis']."))";
	}
	$where_request = $where_clause;
}
$where_request = str_replace("()", "(1=-1)", $where_request);
$where_request = str_replace("and ()", "", $where_request);


if ($_SESSION['current_basket']['clause'] <> '' )
{
		$where_request .= " and (".$_SESSION['current_basket']['clause'].") ";
}

//$request = new request();
//$tab=$request->select($select,$where_request,$orderstr,$_SESSION['config']['databasetype']);
 
$db_external->query("select res_id, status, subject, dest_user, type_label, creation_date, entity_label, category_id, exp_user_id, category_id as category_img, process_limit_date, priority  from ".$_SESSION['collections'][0]['view']." where ".$where_request." order by priority,process_limit_date desc" );

if ($db_external->nb_result() >0)
{
	require_once("core/class/class_security.php");
	$security = new security();
	 $external = '<table border="0" style="font-size:9px; margin:0px;" width="100%"  cellspacing="0">';
	 while ($ext_result=$db_external->fetch_object())
	 {
					$res_status = $status_obj->get_status_data($ext_result->status);
		 
		 
					$my_action = 'onclick="valid_form( \'page\', \''.$ext_result->res_id.'\', \''.$_SESSION['extended_template']['id_default_action'].' \');"';
		 
					$right = $security->test_right_doc($_SESSION['collections'][0]['id'],$ext_result->res_id);
					if($right==false)
						$external .='<tr class="col"  style="color:#BBBBBB;" '.$my_action.'>';
					else
						$external .='<tr class="col"  onmouseover="document.body.style.cursor=\'pointer\';" onmouseout="document.body.style.cursor=\'auto\';" '.$my_action.'>';
					
					
					
					
					$external .='<td width="8%" >&nbsp;</td>';
					$external .='<td width="40px"><img src="'.$res_status['IMG_SRC'].'" alt = "'.$res_status['LABEL'].'" title = "'.$res_status['LABEL'].'"></td>';
					$external .='<td width="40px"><p><img src="'. get_img_cat($ext_result->category_id,$extension_icon).'" title="'.$_SESSION['mail_categories'][$ext_result->category_id].'" alt="'.$_SESSION['mail_categories'][$ext_result->category_id].'"></p></td>';
					$external .='<td width="40px" ><b><p align="center" title="'._GED_NUM.' : '.$ext_result->res_id.'" alt="'._GED_NUM.' : '.$ext_result->res_id.'">'.$func->cut_string($ext_result->res_id,50).'</td></b></p>';
					$external .='<td ><p title="'._SUBJECT.' : '.$request->show_string($ext_result->subject).'" alt="'.SUBJECT.' : '.$request->show_string($ext_result->subject).'">'.$func->cut_string($request->show_string($ext_result->subject),70).'</p></td>';
					//$external .='<td width="100px"><p>'.$ext_result->dest_user.'</td></p>';
					$external .='<td  ><p title="'._TYPE.' : '.$request->show_string($ext_result->type_label).'" alt="'._TYPE.' : '.$request->show_string($ext_result->type_label).'">('.$request->show_string($ext_result->type_label).')</p></td>';
					$external .='<td  ><p title="'._ENTITY.' : '.$request->show_string($ext_result->entity_label).'" alt="'._ENTITY.' : '.$request->show_string($ext_result->entity_label).'"><b>'.$request->show_string($ext_result->entity_label).'</b></p></td>';
					$external .='<td ><p title="'._PROCESS_LIMIT_DATE.' : '.$request->format_date_db($ext_result->process_limit_date,false).'" alt="'._PROCESS_LIMIT_DATE.' : '.$request->format_date_db($ext_result->process_limit_date,false).'">'.$request->format_date_db($ext_result->process_limit_date,false).'</p></td>';
					$external .='</a></tr>';
	 }
   	 $external .='</table>';
	
}

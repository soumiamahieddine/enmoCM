<?php
/*
*
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
* @brief   Displays the invoices list in the following baskets (default setting): NewInvoicesSup10000, RejectedInvoices, ValidatedInvoices
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
*/
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR.'class_contacts.php');
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_manage_status.php");

include_once('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'definition_mail_categories.php');
$status_obj = new manage_status();
$security = new security();
$core_tools = new core_tools();
$request = new request();
$contact = new contacts();
require_once("modules".DIRECTORY_SEPARATOR."basket".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
$bask = new basket();
if(!empty($_REQUEST['id']))
{
	$bask->load_current_basket(trim($_REQUEST['id']), 'frame');
}
if(!empty($_SESSION['current_basket']['view']))
{
	$table = $_SESSION['current_basket']['view'];
}
else
{
	$table = $_SESSION['current_basket']['table'];
}
$_SESSION['collection_id_choice'] = $_SESSION['current_basket']['coll_id'];
$select[$table]= array();
$where = $_SESSION['current_basket']['clause'];
array_push($select[$table],"res_id", "status", "category_id","category_id as category_img", "priority", "admission_date", "subject", "process_limit_date", "destination", "dest_user", "type_label");
$order = '';
if(isset($_REQUEST['order']) && !empty($_REQUEST['order']))
{
	$order = trim($_REQUEST['order']);
}
else
{
	$order = 'asc';
}
$order_field = '';
if(isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field']))
{
	$order_field = trim($_REQUEST['order_field']);
}
else
{
	$order_field = 'creation_date';
}
$list=new list_show();
$orderstr = $list->define_order($order, $order_field);
$bask->connect();
$do_actions_arr = array();
$tab=$request->select($select,$where,$orderstr,$_SESSION['config']['databasetype'], '1000', false, '', '', '', false);
//$request->show();

	//Manage of template list
	//###################

	//Defines template allowed for this list
	$template_list=array();
	array_push($template_list, array( "name"=>"document_list_extend", "img"=>"extend_list.gif", "label"=> _ACCESS_LIST_EXTEND));

	if(!$_REQUEST['template'])
		$template_to_use = $template_list[0]["name"];

	if(isset($_REQUEST['template']) && empty($_REQUEST['template']))
		$template_to_use = '';

	if($_REQUEST['template'])
		$template_to_use = $_REQUEST['template'];


	//For status icon
	$extension_icon = '';
	if($template_to_use <> '')
		$extension_icon = "_big";
	//###################


//$request->show();
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
				$tab[$i][$j]["size"]="4";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='res_id';
				$_SESSION['mlb_search_current_res_id'] = $tab[$i][$j]['value'];
			}
			if($tab[$i][$j][$value]=="admission_date")
			{
				$tab[$i][$j]["value"]=$core_tools->format_date_db($tab[$i][$j]["value"], false);
				$tab[$i][$j]["label"]=_ADMISSION_DATE;
				$tab[$i][$j]["size"]="10";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='admission_date';
			}
			if($tab[$i][$j][$value]=="process_limit_date")
			{
				$tab[$i][$j]["value"]=$core_tools->format_date_db($tab[$i][$j]["value"], false);
				$compareDate = "";
				if($tab[$i][$j]["value"] <> "" && ($statusCmp == "NEW" || $statusCmp == "COU" || $statusCmp == "VAL" || $statusCmp == "RET"))
				{
					$compareDate = $core_tools->compare_date($tab[$i][$j]["value"], date("d-m-Y"));
					if($compareDate == "date2")
					{
						$tab[$i][$j]["value"] = "<span style='color:red;'><b>".$tab[$i][$j]["value"]."<br><small>(".$core_tools->nbDaysBetween2Dates($tab[$i][$j]["value"], date("d-m-Y"))." "._DAYS.")<small></b></span>";
					}
					elseif($compareDate == "date1")
					{
						$tab[$i][$j]["value"] = $tab[$i][$j]["value"]."<br><small>(".$core_tools->nbDaysBetween2Dates(date("d-m-Y"), $tab[$i][$j]["value"])." "._DAYS.")<small>";
					}
					elseif($compareDate == "equal")
					{
						$tab[$i][$j]["value"] = "<span style='color:blue;'><b>".$tab[$i][$j]["value"]."<br><small>("._LAST_DAY.")<small></b></span>";
					}
				}
				$tab[$i][$j]["label"]=_PROCESS_LIMIT_DATE;
				$tab[$i][$j]["size"]="10";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='process_limit_date';
			}
			if($tab[$i][$j][$value]=="category_id")
			{
				$_SESSION['mlb_search_current_category_id'] = $tab[$i][$j]["value"];
				$tab[$i][$j]["value"] = $_SESSION['mail_categories'][$tab[$i][$j]["value"]];
				$tab[$i][$j]["label"]=_CATEGORY;
				$tab[$i][$j]["size"]="10";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='category_id';
			}
			if($tab[$i][$j][$value]=="priority")
			{
				$tab[$i][$j]["value"] = $_SESSION['mail_priorities'][$tab[$i][$j]["value"]];
				$tab[$i][$j]["label"]=_PRIORITY;
				$tab[$i][$j]["size"]="10";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='priority';
			}
			if($tab[$i][$j][$value]=="subject")
			{
				$tab[$i][$j]["value"] = $request->cut_string($request->show_string($tab[$i][$j]["value"]), 250);
				$tab[$i][$j]["label"]=_SUBJECT;
				$tab[$i][$j]["size"]="12";
				$tab[$i][$j]["label_align"]="right";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='subject';
			}
			if($tab[$i][$j][$value]=="type_label")
			{
				$tab[$i][$j]["value"] = $request->show_string($tab[$i][$j]["value"]);
				$tab[$i][$j]["label"]=_TYPE;
				$tab[$i][$j]["size"]="12";
				$tab[$i][$j]["label_align"]="right";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='type_label';
			}
			if($tab[$i][$j][$value]=="status")
			{
				$res_status = $status_obj->get_status_data($tab[$i][$j]['value'],$extension_icon);
				$statusCmp = $tab[$i][$j]['value'];
				$tab[$i][$j]['value'] = "<img src = '".$res_status['IMG_SRC']."' alt = '".$res_status['LABEL']."' title = '".$res_status['LABEL']."'>";
				$tab[$i][$j]["label"]=_STATUS;
				$tab[$i][$j]["size"]="4";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='status';
			}
			if($tab[$i][$j][$value]=="category_img")
			{
				$tab[$i][$j]["label"]=_CATEGORY;
				$tab[$i][$j]["size"]="10";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=false;
				$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
				$my_imgcat = get_img_cat($tab[$i][$j]['value'],$extension_icon);
				$tab[$i][$j]['value'] = "<img src = '".$my_imgcat."' alt = '' title = ''>";
				$tab[$i][$j]["value"] = $tab[$i][$j]['value'];
				$tab[$i][$j]["order"]="category_id";
			}
		}
	}
}




$i = count($tab);
$title = _RESULTS." : ".$i." "._FOUND_DOCS;
//$request->show_array($tab);
$_SESSION['origin'] = 'basket';
$_SESSION['collection_id_choice'] = $_SESSION['current_basket']['coll_id'];

$details = 'details&dir=indexing_searching';

$param_list = array('values' => $tab, 'title' => $title, 'key' => 'res_id', 'page_name' => 'view_baskets&module=basket&baskets='.$_SESSION['current_basket']['id'] ,
'what' => 'res_id', 'detail_destination' =>$details, 'details_page' => '', 'view_doc' => true,  'bool_details' => true, 'bool_order' => true,
'bool_frame' => false, 'module' => '', 'css' => 'listing spec',
 'hidden_fields' => '<input type="hidden" name="module" id="module" value="basket" /><input type="hidden" name="table" id="table" value="'.$_SESSION['current_basket']['table'].'"/>
 <input type="hidden" name="coll_id" id="coll_id" value="'.$_SESSION['current_basket']['coll_id'].'"/>', 'open_details_popup' => false, 'do_actions_arr' => $do_actions_arr, 'template' => true,
  'template_list'=> $template_list, 'actual_template'=>$template_to_use, 'bool_export'=>true );
$bask->basket_list_doc($param_list, $_SESSION['current_basket']['actions'],_CLICK_LINE_TO_PROCESS, true, $template_list, $template_to_use );
?>

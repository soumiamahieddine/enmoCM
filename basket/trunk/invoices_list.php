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
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['businessapps'][0]['appid'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
$security = new security();
$core_tools = new core_tools();
$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header();
$request= new request();
require_once("modules".DIRECTORY_SEPARATOR."basket".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");

$bask = new basket();
if(!empty($_REQUEST['id']))
{
	$bask->load_current_basket(trim($_REQUEST['id']), 'frame');
}

?>
<body>

<?php
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
array_push($select[$table],"res_id",'identifier',"doc_date","doc_custom_t4", "doc_custom_t3", "doc_custom_t10", "status");

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
$tab=$request->select($select,$where,$orderstr,$_SESSION['config']['databasetype'], '1000');
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
			}
			if($tab[$i][$j][$value]=="identifier")
			{
				$tab[$i][$j]['res_id']=$tab[$i][$j]['value'];
				$tab[$i][$j]["label"]=_ID;
				$tab[$i][$j]["size"]="10";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='identifier';
			}
			if($tab[$i][$j][$value]=="doc_date")
			{
				$tab[$i][$j]["value"] = $request->format_date($tab[$i][$j]["value"]);
				$tab[$i][$j]["label"]=_DATE;
				$tab[$i][$j]["size"]="10";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='doc_date';
			}
			if($tab[$i][$j][$value]=="doc_custom_t4")
			{
				$tab[$i][$j]["value"] = $request->show_string($tab[$i][$j]["value"]);
				$tab[$i][$j]["label"]=_CUSTOMER;
				$tab[$i][$j]["size"]="12";
				$tab[$i][$j]["label_align"]="right";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='doc_custom_t4';
			}
			if($tab[$i][$j][$value]=="doc_custom_t3")
			{
				$tab[$i][$j]["value"] = $request->show_string($tab[$i][$j]["value"]);
				$tab[$i][$j]["label"]=_COUNTRY;
				$tab[$i][$j]["size"]="10";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='doc_custom_t3';
			}
			if($tab[$i][$j][$value]=="doc_custom_t10")
			{
				$tab[$i][$j]["value"] = $tab[$i][$j]["value"];
				$tab[$i][$j]["label"]=_AMOUNT;
				$tab[$i][$j]["size"]="8";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="right";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='doc_custom_t10';
				$tab[$i][$j]["css_style"]= 'style="text-align:right;"';
			}
			if($tab[$i][$j][$value]=="status")
			{
				if($tab[$i][$j]['value'] == 'NEW') { $tab[$i][$j]['value'] = "<img src = '".$_SESSION['config']['businessappurl']."img/mail_new.gif' alt = '"._TO_PROCESS."' title = '"._TO_PROCESS."'>";} elseif($tab[$i][$j]['value'] == 'COU') { $tab[$i][$j]['value'] = "<img src = '".$_SESSION['config']['businessappurl']."img/mail.gif' alt = '"._IN_PROGRESS."' title = '"._IN_PROGRESS."'>";}elseif($tab[$i][$j]['value'] == 'END') { $tab[$i][$j]['value'] = "<img src = '".$_SESSION['config']['businessappurl']."img/mail_end.gif' alt = '"._CLOSED."' title = '"._CLOSED."'>";}else {$tab[$i][$j]['value'] =$request->show_string($tab[$i][$j]["value"]);}
				$tab[$i][$j]["label"]=_STATUS;
				$tab[$i][$j]["size"]="4";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='status';
			}
		}
	}
}
$i = count($tab);
$title = _RESULTS." : ".$i." "._FOUND_INVOICES;

$_SESSION['origin'] = 'basket';
	$_SESSION['collection_id_choice'] = $_SESSION['current_basket']['coll_id'];
	$tmp = preg_replace('/.php$/', '', $security->get_script_from_coll($_SESSION['current_basket']['coll_id'], 'script_details'));
	$details = $tmp.'&dir=indexing_searching';
$param_list = array('values' => $tab, 'title' => $title, 'key' => 'res_id', 'page_name' => 'invoices_list',
'what' => 'res_id', 'detail_destination' =>$details, 'details_page' => '', 'view_doc' => true,  'bool_details' => true, 'bool_order' => true,
'bool_frame' => true, 'module' => 'basket', 'css' => 'listing spec',
 'hidden_fields' => '<input type="hidden" name="table" id="table" value="'.$_SESSION['current_basket']['table'].'"/>
 <input type="hidden" name="module" id="module" value="basket" /><input type="hidden" name="coll_id" id="coll_id" value="'.$_SESSION['current_basket']['coll_id'].'"/>', 'open_details_popup' => false );

$bask->basket_list_doc($param_list, $_SESSION['current_basket']['actions'],_CLICK_LINE_TO_CHECK_INVOICE);
?>
</body>
</html>

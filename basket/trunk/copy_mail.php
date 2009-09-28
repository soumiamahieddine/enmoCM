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
* @brief   Basket page results : list of copy mails
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
*/
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
require_once($_SESSION['config']['businessapppath']."class".$_SESSION['slash_env']."class_list_show.php");
require_once($_SESSION['pathtocoreclass']."class_security.php");
require_once($_SESSION['pathtomodules']."basket".$_SESSION['slash_env']."class".$_SESSION['slash_env']."class_modules_tools.php");

$security = new security();
$core_tools = new core_tools();
$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header();
$connexion = new dbquery();
$connexion->connect();

$bask = new basket();
if(!empty($_REQUEST['id']))
{
	$bask->load_current_basket(trim($_REQUEST['id']), 'frame');
}
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

array_push($select[$table],"res_id", 'status', 'is_ingoing', "destination",'sender_corporate' ,'shipper_corporate','sender_society', 'sender_lastname',   'shipper_lastname', 'shipper_society',  'creation_date', 'priority', 'receiving_date', 'process_limit_date', 'mail_object', 'must_valid_answer');
$where = $_SESSION['current_basket']['clause'];
$request= new request;


$order = '';
if(isset($_REQUEST['order']) && !empty($_REQUEST['order']))
{
	$order = trim($_REQUEST['order']);
}
else
{
	$order = 'asc';
}
$field = '';
if(isset($_REQUEST['field']) && !empty($_REQUEST['field']))
{
	$field = trim($_REQUEST['field']);
}
else
{
	$field = 'process_limit_date, priority, res_id';
}
$list=new list_show();
$orderstr = $list->define_order($order, $field);

$tab=$request->select($select,$where,$orderstr,$_SESSION['config']['databasetype'], '500', false, '', '', '', false);
for ($i=0;$i<count($tab);$i++)
{
	for ($j=0;$j<count($tab[$i]);$j++)
	{
		foreach(array_keys($tab[$i][$j]) as $value)
		{

			if($tab[$i][$j][$value]=='sender_corporate')
			{
				$sender_corporate=$tab[$i][$j]['value'];
				$tab[$i][$j]["label"]=_SENDER;
				$tab[$i][$j]["size"]="5";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=false;
			}
			if($tab[$i][$j][$value]=='shipper_corporate')
			{
				$shipper_corporate=$tab[$i][$j]['value'];
				$tab[$i][$j]["label"]=_SENDER;
				$tab[$i][$j]["size"]="5";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=false;
			}
			if($tab[$i][$j][$value]=='is_ingoing')
			{
				$is_ingoing=$tab[$i][$j]['value'];
				if($tab[$i][$j]['value'] == 'Y') { $tab[$i][$j]['value'] = "<img src = '".$_SESSION['config']['businessappurl']."img/puce_prev.gif' title= '"._INGOING."' alt= '"._INGOING."'><img src = '".$_SESSION['config']['businessappurl']."img/nature_send_in.gif' alt= '"._INGOING."' title= '"._INGOING."'>";}
				else {$tab[$i][$j]['value'] = "<img src = '".$_SESSION['config']['businessappurl']."img/nature_send.gif' alt= '"._ONGOING."' title= '"._ONGOING."'><img src = '".$_SESSION['config']['businessappurl']."img/puce_next.gif' alt= '"._ONGOING."' title= '"._ONGOING."'>";}
				$tab[$i][$j]["label"]=_INVOICE_TYPE;
				$tab[$i][$j]["size"]="5";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]="is_ingoing";
			}
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
				$tab[$i][$j]["label"]=_ENTITY;
				$connexion->query("select entity_label from ".$_SESSION['tablename']['bask_entity']." where entity_id='".$tab[$i][$j]['value']."'");
				$line = $connexion->fetch_object();
				$tab[$i][$j]['value'] = $line->entity_label;
				$tab[$i][$j]["size"]="10";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]="destination";
			}
			if($tab[$i][$j][$value]=='res_id')
			{
				$tab[$i][$j]['res_id']=$tab[$i][$j]['value'];
				$tab[$i][$j]["label"]=_GED_NUM;
				$tab[$i][$j]["size"]="5";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]="res_id";
			}
			if($tab[$i][$j][$value]=='status')
			{
				if($tab[$i][$j]['value'] == 'NEW') { $tab[$i][$j]['value'] = "<img src = '".$_SESSION['config']['businessappurl']."img/mail_new.gif' alt = '"._TO_PROCESS."' title = '"._TO_PROCESS."'>";} elseif($tab[$i][$j]['value'] == 'COU') { $tab[$i][$j]['value'] = "<img src = '".$_SESSION['config']['businessappurl']."img/mail.gif' alt = '"._IN_PROGRESS."' title = '"._IN_PROGRESS."'>";}elseif($tab[$i][$j]['value'] == 'END') { $tab[$i][$j]['value'] = "<img src = '".$_SESSION['config']['businessappurl']."img/mail_end.gif' alt = '"._CLOSED."' title = '"._CLOSED."'>";}
				$tab[$i][$j]["label"]=_STATUS;
				$tab[$i][$j]["size"]="4";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]="res_id";
			}
			if($is_ingoing == 'Y')
			{
				if($sender_corporate == 'Y')
				{
					if($tab[$i][$j][$value]=='sender_society')
					{
						$tab[$i][$j]['value']="<b>".$tab[$i][$j]['value']."</b>";
						$tab[$i][$j]["label"]=_SENDER." / "._SHIPPER;
						$tab[$i][$j]["size"]="5";
						$tab[$i][$j]["label_align"]="left";
						$tab[$i][$j]["align"]="left";
						$tab[$i][$j]["valign"]="bottom";
						$tab[$i][$j]["show"]=true;
						$tab[$i][$j]["order"]="sender_society";
					}
				}
				elseif ($sender_corporate == 'N')
				{
					if($tab[$i][$j][$value]=='sender_lastname')
					{
						$tab[$i][$j]["label"]=_SENDER." / "._SHIPPER;
						$tab[$i][$j]["size"]="5";
						$tab[$i][$j]["label_align"]="left";
						$tab[$i][$j]["align"]="left";
						$tab[$i][$j]["valign"]="bottom";
						$tab[$i][$j]["show"]=true;
						$tab[$i][$j]["order"]="sender_lastname";
					}
				}
			}
			elseif($is_ingoing == 'N')
			{
				if($shipper_corporate == 'Y')
				{
					if($tab[$i][$j][$value]=='shipper_society')
					{
						$tab[$i][$j]['value']="<b>".$tab[$i][$j]['value']."</b>";
						$tab[$i][$j]["label"]=_SENDER." / "._SHIPPER;
						$tab[$i][$j]["size"]="5";
						$tab[$i][$j]["label_align"]="left";
						$tab[$i][$j]["align"]="left";
						$tab[$i][$j]["valign"]="bottom";
						$tab[$i][$j]["show"]=true;
						$tab[$i][$j]["order"]="shipper_society";
					}
				}
				elseif ($shipper_corporate == 'N')
				{
					if($tab[$i][$j][$value]=='shipper_lastname')
					{
						$tab[$i][$j]["label"]=_SENDER." / "._SHIPPER;
						$tab[$i][$j]["size"]="5";
						$tab[$i][$j]["label_align"]="left";
						$tab[$i][$j]["align"]="left";
						$tab[$i][$j]["valign"]="bottom";
						$tab[$i][$j]["show"]=true;
						$tab[$i][$j]["order"]="shipper_lastname";
					}
				}
			}
			if($tab[$i][$j][$value]=='mail_object')
			{
				$tab[$i][$j]["label"]=_MAIL_OBJECT;
				$tab[$i][$j]["size"]="9";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]="mail_object";
			}
			if($tab[$i][$j][$value]=='priority')
			{
				$tab[$i][$j]["label"]=_PRIORITY;
				if($tab[$i][$j]['value'] == '3') { $tab[$i][$j]['value'] = _LOW;} elseif($tab[$i][$j]['value'] == '2') { $tab[$i][$j]['value'] = _NORMAL;}  elseif($tab[$i][$j]['value'] == '1') { $tab[$i][$j]['value'] = _HIGH;}
				$tab[$i][$j]["size"]="5";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]="priority";
			}
			if($tab[$i][$j][$value]=='process_limit_date')
			{
				$tab[$i][$j]['process_limit_date']=$request->format_date_db($tab[$i][$j]['value']);
				$tab[$i][$j]['value']=$request->format_date_db($tab[$i][$j]['value']);
				$tab[$i][$j]["label"]=_CUSTOM_D2;
				$tab[$i][$j]["size"]="6";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]="process_limit_date";
			}
			if($tab[$i][$j][$value]=='must_valid_answer')
			{
				if($tab[$i][$j]['value'] == 'Y') { $tab[$i][$j]['value'] = "<img src = '".$_SESSION['config']['businessappurl']."img/picto_stat_enabled.gif' alt = '"._RESPONSE_VALID_BY_DGS."' title = '"._RESPONSE_VALID_BY_DGS."'>";} elseif($tab[$i][$j]['value'] == 'N') { $tab[$i][$j]['value'] = "<img src = '".$_SESSION['config']['businessappurl']."img/picto_stat_disabled.gif' alt = '"._RESPONSE_NOT_VALID_BY_DGS."' title = '"._RESPONSE_NOT_VALID_BY_DGS."'>";}elseif($tab[$i][$j]['value'] == '') { $tab[$i][$j]['value'] = "<img src = '".$_SESSION['config']['businessappurl']."img/picto_stat_disabled.gif' alt = '"._RESPONSE_NOT_VALID_BY_DGS."' title = '"._RESPONSE_NOT_VALID_BY_DGS."'>";}
				$tab[$i][$j]["label"]=_RESPONSE_VALID_BY_DGS;
				$tab[$i][$j]["size"]="9";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]="must_valid_answer";
			}
		}
	}
}

for($coll = 0; $coll < count($_SESSION['collections']); $coll++)
{
	if ($_SESSION['collections'][$coll]['id'] == $_SESSION['collection_id_choice'])
	{
		$script_detail = $_SESSION['collections'][$coll]['script_details'];
		$script_detail = str_replace('.php','',$script_detail);
	}
}
$_SESSION['origin'] = $_SESSION['config']['businessappurl'].'index.php?basket_id='.$_SESSION['current_basket']['id'];
$param_list = array('values' => $tab, 'title' =>  _COPY_LIST." : ".count($tab)." "._DOCS, 'key' => 'res_id', 'page_name' => 'copy_mail',
'what' => 'res_id', 'detail_destination' =>  "&module=indexing_searching&page=".$script_detail, 'details_page' => '', 'view_doc' => true,  'bool_details' => true,
 'bool_order' => true, 'bool_frame' => true, 'module' => '', 'css' => 'listing spec',
 'hidden_fields' => '<input type="hidden" value="mass" name="mode" id="mode" /><input type="hidden" name="table" id="table" value="'.$_SESSION['current_basket']['table'].'"/>
 <input type="hidden" name="module" id="module" value="basket" /><input type="hidden" name="coll_id" id="coll_id" value="'.$_SESSION['current_basket']['coll_id'].'"/>' );

$bask->basket_list_doc($param_list, $_SESSION['current_basket']['actions'], _CLICK_LINE_TO_VIEW);
?>
</body>
</html>

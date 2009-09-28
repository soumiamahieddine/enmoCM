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
* @brief   Basket page results
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
$security = new security();
$core_tools = new core_tools();
$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header();
$connexion = new dbquery();
$connexion->connect();
require_once($_SESSION['pathtomodules']."basket".$_SESSION['slash_env']."class".$_SESSION['slash_env']."class_modules_tools.php");
if(!empty($_REQUEST['id']))
{
	$bask = new basket();
	$bask->load_current_basket(trim($_REQUEST['id']), 'frame');
}
?>
<body>
<?php
$db = new dbquery();
$db-> connect();
$db->query("select distinct destination, entity_label from ".$_SESSION['current_basket']['view']." where ".$_SESSION['current_basket']['clause']." order by entity_label");
$authorised_entities = array();
while ($result= $db->fetch_object())
{
	array_push($authorised_entities, array("DESTINATION"=>$result->destination, "ENTITY_LABEL"=>$result->entity_label));
}
if(!$_REQUEST['start'] || $_REQUEST['start'] =='')
{
	if($_POST['entity'] <> '')
	{
		$_SESSION['tmpbasket']['service'] = $_POST['entity'];
	}
	else
	{
		$_SESSION['history']['service']= '';
	}

	if($_POST['status'] <> '')
	{
		$_SESSION['tmpbasket']['status'] = $_POST['status'];
	}
	else
	{
		$_SESSION['tmpbasket']['status']= '';
	}
}
$where_bsk = "";
$where_bsk_status = "";
$where_bsk_entity = "";
if(isset($_SESSION['tmpbasket']['service']) && !empty($_SESSION['tmpbasket']['service']))
{
	if($_SESSION['tmpbasket']['service'] <> "all")
	{
		$where_bsk_entity .= " and DESTINATION = '".$_SESSION['tmpbasket']['service']."' ";;
	}
	else
	{
		$where_bsk_entity .= "";
	}
}
if(isset($_SESSION['tmpbasket']['status']) && !empty($_SESSION['tmpbasket']['status']))
{
	if($_SESSION['tmpbasket']['status'] == "all")
	{
		$where_bsk_status .= " and (STATUS = 'NEW' or STATUS ='COU') ";
	}
	elseif($_SESSION['tmpbasket']['status']== "NEW")
	{
		$where_bsk_status .= " and STATUS ='NEW' ";
	}
	elseif($_SESSION['tmpbasket']['status'] == "COU")
	{
		$where_bsk_status .= " and STATUS = 'COU' ";
	}
	elseif($_SESSION['tmpbasket']['status'] == "RET")
	{
		$datenow=date("d-m-Y") ;
		$where_bsk_status .=" and ( process_limit_date <= '".$datenow."'  and STATUS <> 'END' ) ";
	}
	elseif($_SESSION['tmpbasket']['status'] == "DGS")
	{
		$where_bsk_status .= " and STATUS = 'DGS' ";
	}
	elseif($_SESSION['tmpbasket']['status'] == "RDG")
	{
		$where_bsk_status .= " and STATUS = 'RDG' ";
	}
	elseif($_SESSION['tmpbasket']['status'] == "VDG")
	{
		$where_bsk_status .= " and STATUS = 'VDG' ";
	}
}
$where_bsk = $where_bsk_status.$where_bsk_entity;
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
array_push($select[$table],"res_id", 'status', 'is_ingoing', "destination",'sender_corporate' ,'shipper_corporate','sender_society', 'sender_lastname',   'shipper_lastname', 'shipper_society',  'creation_date', 'priority', 'receiving_date', 'process_limit_date', 'mail_object');
$where = $_SESSION['current_basket']['clause'].$where_bsk;
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
$request= new request;
$tab=$request->select($select,$where,$orderstr,$_SESSION['config']['databasetype']);
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
				$tab[$i][$j]["size"]="5";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]="res_id";
			}
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
				else {$tab[$i][$j]['value'] =  "<img src = '".$_SESSION['config']['businessappurl']."img/nature_send.gif' alt= '"._ONGOING."' title= '"._ONGOING."'><img src = '".$_SESSION['config']['businessappurl']."img/puce_next.gif' alt= '"._ONGOING."' title= '"._ONGOING."'>";}
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
				$tab[$i][$j]['res_id']=$tab[$i][$j]['value'];
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
			if($tab[$i][$j][$value]=='status')
			{
				if($tab[$i][$j]['value'] == 'NEW') { $tab[$i][$j]['value'] = "<img src = '".$_SESSION['config']['businessappurl']."img/mail_new.gif' alt = '"._TO_PROCESS."' title = '"._TO_PROCESS."'>";} elseif($tab[$i][$j]['value'] == 'COU') { $tab[$i][$j]['value'] = "<img src = '".$_SESSION['config']['businessappurl']."img/mail.gif' alt = '"._IN_PROGRESS."' title = '"._IN_PROGRESS."'>";}elseif($tab[$i][$j]['value'] == 'END') { $tab[$i][$j]['value'] = "<img src = '".$_SESSION['config']['businessappurl']."img/mail_end.gif' alt = '"._CLOSED."' title = '"._CLOSED."'>";}elseif($tab[$i][$j]['value'] == 'DGS') { $tab[$i][$j]['value'] = "<img src = '".$_SESSION['config']['businessappurl']."img/dgs.png' alt = '"._DGS_ANSWER_VALIDATION_ASKED."' title = '"._DGS_ANSWER_VALIDATION_ASKED."'>";}elseif($tab[$i][$j]['value'] == 'VDG') { $tab[$i][$j]['value'] = "<img src = '".$_SESSION['config']['businessappurl']."img/vdg.png' alt = '"._ANSWER_VALIDATED_BY_DGS."' title = '"._ANSWER_VALIDATED_BY_DGS."'>";}elseif($tab[$i][$j]['value'] == 'RDG') { $tab[$i][$j]['value'] = "<img src = '".$_SESSION['config']['businessappurl']."img/rdg.png' alt = '"._ANSWER_REJECTED_BY_DGS."' title = '"._ANSWER_REJECTED_BY_DGS."'>";}
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
				$tab[$i][$j]["label"]=_PROCESS_LIMIT_DATE;
				$tab[$i][$j]["size"]="6";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]="process_limit_date";
			}
		}
	}
}
?>
<form name="hist_proprieties" method="post" action="auth_dep.php?start=0" class="forms enregistrement" >
	<table width="80%" border = "0">
		<tr>
			<td>
				<label class="bold"><?php echo _ENTITY; ?> : </label>
				<select name="entity" onchange="this.form.submit();" class="listext_big">
				<option value="all" selected="selected"><?php echo _ALL_ENTITIES; ?></option>
				<?php for($se = 0; $se < count($authorised_entities); $se++)
				{	?>
			        	<option value="<?php echo $authorised_entities[$se]['DESTINATION'];?>" <?php if ($_SESSION['tmpbasket']['service'] == $authorised_entities[$se]['DESTINATION']) { echo 'selected="selected"'; } ?> ><?php echo $authorised_entities[$se]['ENTITY_LABEL'];?></option>
				<?php } ?>
			    </select>
			</td>
			<td>
				<label class="bold"><?php echo _STATUS; ?> : </label>
				<select name="status" onchange="this.form.submit();" class="listext_big">
				<option value="all" <?php if ($_SESSION['tmpbasket']['status'] == "all") { echo 'selected="selected"'; } ?>><?php echo _TO_PROCESS." + "._IN_PROGRESS; ?></option>
				<option value="NEW" <?php if ($_SESSION['tmpbasket']['status'] == "NEW") { echo 'selected="selected"'; } ?>><?php echo _TO_PROCESS; ?></option>
				<option value="COU" <?php if ($_SESSION['tmpbasket']['status'] == "COU") { echo 'selected="selected"'; } ?>><?php echo _IN_PROGRESS; ?></option>
				<option value="RET" <?php if ($_SESSION['tmpbasket']['status'] == "RET") { echo 'selected="selected"'; } ?>><?php echo _LATE_PROCESS; ?></option>
				<option value="DGS" <?php if ($_SESSION['tmpbasket']['status'] == "DGS") { echo 'selected="selected"'; } ?>><?php echo _RESPONSE_VALID_BY_DGS; ?></option>
			    <option value="VDG" <?php if ($_SESSION['tmpbasket']['status'] == "VDG") { echo 'selected="selected"'; } ?>><?php echo _VALIDATED_ANSWERS; ?></option>
				<option value="RDG" <?php if ($_SESSION['tmpbasket']['status'] == "RDG") { echo 'selected="selected"'; } ?>><?php echo _REJECTED_ANSWERS; ?></option>
				</select>
			</td>
		</tr>
	</table>
</form>
<br/>
<?php
$title = _PROCESS_LIST." : ".$i." "._DOCS;
for($coll = 0; $coll < count($_SESSION['collections']); $coll++)
{
	if ($_SESSION['collections'][$coll]['id'] == $_SESSION['collection_id_choice'])
	{
		$script_detail = $_SESSION['collections'][$coll]['script_details'];
		$script_detail = str_replace('.php','',$script_detail);
	}
}
$_SESSION['origin'] = $_SESSION['config']['businessappurl'].'index.php?basket_id='.$_SESSION['current_basket']['id'];
$param_list = array('values' => $tab, 'title' =>  _PROCESS_LIST." : ".count($tab)." "._DOCS, 'key' => 'res_id', 'page_name' => 'auth_dep',
'what' => 'res_id', 'detail_destination' =>  "&module=indexing_searching&page=".$script_detail, 'details_page' => '', 'view_doc' => true,  'bool_details' => true,
 'bool_order' => true, 'bool_frame' => true, 'module' => '', 'css' => 'listing spec',
 'hidden_fields' => '<input type="hidden" value="mass" name="mode" id="mode" /><input type="hidden" name="table" id="table" value="'.$_SESSION['current_basket']['table'].'"/>
 <input type="hidden" name="module" id="module" value="basket" /><input type="hidden" name="coll_id" id="coll_id" value="'.$_SESSION['current_basket']['coll_id'].'"/>' );

$bask->basket_list_doc($param_list, $_SESSION['current_basket']['actions'], _CLICK_LINE_TO_VIEW);
?>
</body>
</html>

<?php
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
require_once("modules".DIRECTORY_SEPARATOR."basket".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_manage_status.php");
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR.'class_contacts.php');
include_once('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'definition_mail_categories.php');
$status_obj = new manage_status();
$security = new security();
$core_tools = new core_tools();
$core_tools->load_lang();
$request = new request();
$bask = new basket();
$contact = new contacts();
//$core_tools->show_array($_REQUEST);
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
array_push($select[$table],"res_id", "status", "category_id", "category_id as category_img", "contact_firstname", "contact_lastname", "contact_society", "user_lastname", "user_firstname", "priority", "creation_date", "admission_date", "subject", "process_limit_date", "entity_label", "dest_user", "type_label", "exp_user_id" );

if($core_tools->is_module_loaded("cases") == true)
{
	array_push($select[$table], "case_id", "case_label", "case_description");
}
$order = '';
if(isset($_REQUEST['order']) && !empty($_REQUEST['order']))
{
	$order = trim($_REQUEST['order']);
}
else
{
	$order = 'desc';
}
$order_field = '';
if(isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field']))
{
	$order_field = trim($_REQUEST['order_field']);
}
elseif($_REQUEST['template']<> 'group_case_for_basket')
{
    $order_field = 'priority, creation_date, res_id';
}
else
{
	$order_field = '';
}
$list=new list_show();
$orderstr = $list->define_order($order, $order_field);
$bask->connect();
$do_actions_arr = array();
$str = '';
$search = false;
if(isset($_REQUEST['entity_id']) && trim($_REQUEST['entity_id']) == "none")
{
	$_SESSION['auth_dep']['bask_chosen_entity'] = "";
}
if(isset($_REQUEST['category_id']) && trim($_REQUEST['category_id']) == "none")
{
	$_SESSION['auth_dep']['bask_chosen_category'] = "";
}
if(isset($_REQUEST['status_id']) && trim($_REQUEST['status_id']) == "none")
{
	$_SESSION['auth_dep']['bask_chosen_status'] = "";
}
if(isset($_REQUEST['entity_id']) && $_REQUEST['entity_id'] <> "none" && $_REQUEST['entity_id'] <> "")
{
	$_SESSION['auth_dep']['bask_chosen_entity'] = trim($_REQUEST['entity_id']);
}
if(isset($_REQUEST['category_id']) && $_REQUEST['category_id'] <> "none" && $_REQUEST['category_id'] <> "")
{
	$_SESSION['auth_dep']['bask_chosen_category'] = trim($_REQUEST['category_id']);
}
if( isset($_REQUEST['status_id']) && $_REQUEST['status_id'] <> "none" && $_REQUEST['status_id'] <> "")
{
	$_SESSION['auth_dep']['bask_chosen_status'] = trim($_REQUEST['status_id']);
}
if(isset($_REQUEST['contact_id']) && trim($_REQUEST['contact_id']) <> "")
{
	$contactTmp = str_replace(')', '', substr($_REQUEST['contact_id'], strrpos($_REQUEST['contact_id'],'(')+1));
	$find1 = strpos($contactTmp, ':');
	$find2 =  $find1 + 1;
	$contact_type = substr($contactTmp, 0, $find1);
	$contact_id = substr($contactTmp, $find2, strlen($contactTmp));
	if($contact_id <> "" && $contact_type <> "")
	{
		$_SESSION['auth_dep']['bask_chosen_contact'] = trim($_REQUEST['contact_id']);
	}
}
elseif( isset($_SESSION['auth_dep']['bask_chosen_contact'] ) && $_SESSION['auth_dep']['bask_chosen_contact'] <> "")
{
	$contactTmp = str_replace(')', '', substr($_SESSION['auth_dep']['bask_chosen_contact'], strrpos($_SESSION['auth_dep']['bask_chosen_contact'],'(')+1));
	$find1 = strpos($contactTmp, ':');
	$find2 =  $find1 + 1;
	$contact_type = substr($contactTmp, 0, $find1);
	$contact_id = substr($contactTmp, $find2, strlen($contactTmp));
}

$where = trim($_SESSION['current_basket']['clause']);
$where = str_replace("and status <> 'VAL'", " ", $where);
$where_concat = $where;

if(isset($_SESSION['auth_dep']['bask_chosen_entity']) && !empty($_SESSION['auth_dep']['bask_chosen_entity']))
{
	if(!empty($where))
	{
		$where_concat = "(".$where.") and destination = '".$bask->protect_string_db($_SESSION['auth_dep']['bask_chosen_entity'])."'";
	}
	$search = true;
}
if(isset($_SESSION['auth_dep']['bask_chosen_category']) && !empty($_SESSION['auth_dep']['bask_chosen_category']))
{
	if(!empty($where))
	{
		if($where_concat <> "")
		{
			$where_concat .= "and (".$where.") and category_id = '".$bask->protect_string_db($_SESSION['auth_dep']['bask_chosen_category'])."'";
		}
		else
		{
			$where_concat = "(".$where.") and category_id = '".$bask->protect_string_db($_SESSION['auth_dep']['bask_chosen_category'])."'";
		}
	}
	$search = true;
}
if(isset($_SESSION['auth_dep']['bask_chosen_contact']) && !empty($_SESSION['auth_dep']['bask_chosen_contact']))
{
	if($where_concat <> "")
	{
		if($contact_type == "user")
		{
			$where_concat .= " and (exp_user_id = '".$contact_id."' or dest_user_id = '".$contact_id."')";
		}
		elseif($contact_type == "contact")
		{
			$where_concat .= " and (exp_contact_id = '".$contact_id."' or dest_contact_id = '".$contact_id."')";
		}
	}
	else
	{
		if(!empty($where))
		{
			if($contact_type == "user")
			{
				$where_concat = "(".$where.") and (exp_user_id = '".$contact_id."' or dest_user_id = '".$contact_id."')";
			}
			elseif($contact_type == "contact")
			{
				$where_concat = "(".$where.") and (exp_contact_id = '".$contact_id."' or dest_contact_id = '".$contact_id."')";
			}
		}
	}
	$search = true;
}
if(isset($_SESSION['auth_dep']['bask_chosen_status']) && !empty($_SESSION['auth_dep']['bask_chosen_status']))
{
	if($_SESSION['auth_dep']['bask_chosen_status'] == 'late')
	{
		if($where_concat <> "")
		{
			$where_concat .= " and ( process_limit_date is not null and ".$request->current_datetime()." > ".$request->extract_date('process_limit_date')." )";
		}
		else
		{
			if(!empty($where))
			{
				$where_concat = "(".$where.") and (process_limit_date is not null and ".$request->current_datetime()." > ".$request->extract_date('process_limit_date').")";
			}
		}
	}
	else
	{
		if($where_concat <> "")
		{
			$where_concat .= " and status = '".$bask->protect_string_db($_SESSION['auth_dep']['bask_chosen_status'])."'";
		}
		else
		{
			if(!empty($where))
			{
				$where_concat = "(".$where.") and status = '".$bask->protect_string_db($_SESSION['auth_dep']['bask_chosen_status'])."'";
			}
		}
	}
	$search = true;
}

$where_concat = $_SESSION['searching']['where_request'] . $where_concat;

if(($_REQUEST['template']== 'group_case_for_basket') && ($core_tools->is_module_loaded('cases')))
{
    unset($select);
    $select = array();
    $select[$_SESSION['tablename']['cases']]= array();
    $select[$table]= array();
    array_push($select[$_SESSION['tablename']['cases']], "case_id", "case_label", "case_description", "case_typist", "case_creation_date");
    $where = " " . $_SESSION['tablename']['cases'] . ".case_id = " . $table . ".case_id and ";
    $tab = $request->select($select, $where . $where_concat, 'order by case_id desc', $_SESSION['config']['databasetype'], "default", false, "", "", "", true, false, true);
}
else
{
	$tab=$request->select($select, $where_concat, $orderstr, $_SESSION['config']['databasetype'], $_SESSION['config']['databasesearchlimit'], false, "", "", "", false, false, 'distinct');
}

//$request->show();
//$core_tools->show_array($tab);
//Manage of template list
//###################
//Defines template allowed for this list
$template_list=array();
array_push($template_list, array("name"=>"document_list_extend", "img"=>"extend_list.gif", "label"=> _ACCESS_LIST_EXTEND));
if($core_tools->is_module_loaded('cases'))
	array_push($template_list, array("name"=>"group_case_for_basket", "img"=>"case_list.gif", "label"=> _ACCESS_LIST_CASE));
if(!$_REQUEST['template'])
{
	$template_to_use = $template_list[0]["name"];
}
if(isset($_REQUEST['template']) && empty($_REQUEST['template']))
{
	$template_to_use = '';
}
if($_REQUEST['template'])
{
	$template_to_use = $_REQUEST['template'];
}
//For status icon
$extension_icon = '';
if($template_to_use <> '')
	$extension_icon = "_big";


//###################
//Specific View for group_case_template, we don' need to load the standard list_result_mlb
//#########################
if(($_REQUEST['template']== 'group_case_for_basket' )&& ($core_tools->is_module_loaded('cases')))
{
	include("modules".DIRECTORY_SEPARATOR."cases".DIRECTORY_SEPARATOR.'mlb_list_group_case_addon.php');
}
else
{
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
					$_SESSION['mlb_search_current_res_id'] = $tab[$i][$j]["value"];
                    $tmp = $bask->check_reserved_time($tab[$i][$j]['value'], $_SESSION['current_basket']['coll_id']);
                    array_push($do_actions_arr, $tmp);
				}
				if($tab[$i][$j][$value]=="creation_date")
				{
					$tab[$i][$j]["value"]=$core_tools->format_date_db($tab[$i][$j]["value"], false);
					$tab[$i][$j]["label"]=_CREATION_DATE;
					$tab[$i][$j]["size"]="10";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='creation_date';
				}
				/*if($tab[$i][$j][$value]=="admission_date")
				{
					$tab[$i][$j]["value"]=$core_tools->format_date_db($tab[$i][$j]["value"], false);
					$tab[$i][$j]["label"]=_ADMISSION_DATE;
					$tab[$i][$j]["size"]="10";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='admission_date';
				}*/
				if($tab[$i][$j][$value]=="process_limit_date")
				{
					$tab[$i][$j]["value"]=$core_tools->format_date_db($tab[$i][$j]["value"], false);
					$compareDate = "";
					if($tab[$i][$j]["value"] <> "" && ($statusCmp == "NEW" || $statusCmp == "COU" || $statusCmp == "VAL" || $statusCmp == "RET"))
					{
						$compareDate = $core_tools->compare_date($tab[$i][$j]["value"], date("d-m-Y"));
						if($compareDate == "date2")
						{
							$tab[$i][$j]["value"] = "<span style='color:red;'><b>".$tab[$i][$j]["value"]."<br/><small>(".$core_tools->nbDaysBetween2Dates($tab[$i][$j]["value"], date("d-m-Y"))." "._DAYS.")</small></b></span>";
						}
						elseif($compareDate == "date1")
						{
							$tab[$i][$j]["value"] = $tab[$i][$j]["value"]."<br/><small>(".$core_tools->nbDaysBetween2Dates(date("d-m-Y"), $tab[$i][$j]["value"])." "._DAYS.")</small>";
						}
						elseif($compareDate == "equal")
						{
							$tab[$i][$j]["value"] = "<span style='color:blue;'><b>".$tab[$i][$j]["value"]."<br/><small>("._LAST_DAY.")</small></b></span>";
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
				if($tab[$i][$j][$value]=="contact_firstname")
				{
					$contact_firstname = $tab[$i][$j]["value"];
					$tab[$i][$j]["show"]=false;
				}
				if($tab[$i][$j][$value]=="contact_lastname")
				{
					$contact_lastname = $tab[$i][$j]["value"];
					$tab[$i][$j]["show"]=false;
				}
				if($tab[$i][$j][$value]=="contact_society")
				{
					$contact_society = $tab[$i][$j]["value"];
					$tab[$i][$j]["show"]=false;
				}
				if($tab[$i][$j][$value]=="user_firstname")
				{
					$user_firstname = $tab[$i][$j]["value"];
					$tab[$i][$j]["show"]=false;
				}
				if($tab[$i][$j][$value]=="user_lastname")
				{
					$user_lastname = $tab[$i][$j]["value"];
					$tab[$i][$j]["show"]=false;
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
				if($tab[$i][$j][$value]=="exp_user_id")
				{
					$tab[$i][$j]["label"]=_CONTACT;
					$tab[$i][$j]["size"]="10";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=false;
					$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
					$tab[$i][$j]["value"] = $contact->get_contact_information_from_view($_SESSION['mlb_search_current_category_id'], $contact_lastname, $contact_firstname, $contact_society, $user_lastname, $user_firstname);
					$tab[$i][$j]["order"]=false;
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
				if($tab[$i][$j][$value]=="case_id" && $core_tools->is_module_loaded("cases") == true)
				{
					$tab[$i][$j]["label"]=_CASE_NUM;
					$tab[$i][$j]["size"]="10";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
					$tab[$i][$j]["value"] = "<a href='".$_SESSION['config']['businessappurl']."index.php?page=details_cases&module=cases&id=".$tab[$i][$j]['value']."'>".$tab[$i][$j]['value']."</a>";
					$tab[$i][$j]["order"]="case_id";
				}
				if($tab[$i][$j][$value]=="case_label" && $core_tools->is_module_loaded("cases") == true)
				{
					$tab[$i][$j]["label"]=_CASE_LABEL;
					$tab[$i][$j]["size"]="10";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
					$tab[$i][$j]["order"]="case_id";
				}
			}
		}
	}
}
if(count($tab) > 0)
{
    $chosenEntity = '';
    if (isset($_SESSION['auth_dep']['bask_chosen_entity'])) {
        $chosenEntity = $_SESSION['auth_dep']['bask_chosen_entity'];
    }
    $chosenCat = '';
    if (isset($_SESSION['auth_dep']['bask_chosen_category'])) {
        $chosenCat = $_SESSION['auth_dep']['bask_chosen_category'];
    }
    $chosenStatus = '';
    if (isset($_SESSION['auth_dep']['bask_chosen_status'])) {
        $chosenStatus = $_SESSION['auth_dep']['bask_chosen_status'];
    }
    $chosenContact = '';
    if (isset($_SESSION['auth_dep']['bask_chosen_contact'])) {
        $chosenStatus = $_SESSION['auth_dep']['bask_chosen_contact'];
    }
	$i = count($tab);
	$title = _RESULTS." : ".$i." "._FOUND_DOCS;
	$_SESSION['origin'] = 'basket';
	$_SESSION['collection_id_choice'] = $_SESSION['current_basket']['coll_id'];
	$details = 'details&dir=indexing_searching';
	$param_list = array(
		'values' => $tab,
		'title' => $title,
		'key' => 'res_id',
		'page_name' => 'view_baskets&module=basket&baskets='
	        . $_SESSION['current_basket']['id'] . '&entity_id='
	        . $chosenEntity . '&category_id=' . $chosenCat . '&status_id='
	        . $chosenStatus . '&contact_id=' . $chosenContact . '&order_field='
	        . $order_field . '&order=' . $order,
		'what' => 'res_id',
		'detail_destination' => $details,
		'details_page' => '',
		'view_doc' => true,
		'bool_details' => true,
		'bool_order' => true,
		'bool_frame' => false,
		'module' => '',
		'css' => 'listing spec',
		'hidden_fields' => '<input type="hidden" name="module" id="module" value="basket" />'
	        .'<input type="hidden" name="table" id="table" value="'
	        . $_SESSION['current_basket']['table'] . '"/><input type="hidden" name="coll_id" id="coll_id" value="'
	        . $_SESSION['current_basket']['coll_id'] . '"/>',
	    'open_details_popup' => false,
	    'do_actions_arr' => $do_actions_arr,
	    'template' => true,
		'template_list' => $template_list,
		'actual_template'=> $template_to_use,
		'bool_export' => true ,
		'mode_string' => true
	);
	echo $bask->basket_list_doc($param_list, $_SESSION['current_basket']['actions'], _CLICK_LINE_TO_PROCESS);
}


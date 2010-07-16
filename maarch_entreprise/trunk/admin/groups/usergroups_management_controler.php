<?php

$basket_loaded = false;
$entities_loaded = false;

if(core_tools::is_module_loaded('basket'))
	$basket_loaded = true;
if(core_tools::is_module_loaded('entities'))
	$entities_loaded = true;

$mode = 'add';
if(isset($_REQUEST['mode']) && !empty($_REQUEST['mode']))
{
	$mode = $_REQUEST['mode'];
}

$page_labels = array('add' => _ADDITION, 'up' => _MODIFICATION, 'list' => _GROUPS_LIST);
$page_ids = array('add' => 'group_add', 'up' => 'group_up', 'list' => 'groups_list');

try{
	require_once("apps/maarch_entreprise/class/UsergroupControler.php");
	require_once("apps/maarch_entreprise/class/UserControler.php");
	require_once("core/class/SecurityControler.php");
	require_once("core/class/class_security.php");
	if($mode == 'list')
	{
		require_once("core/class/class_request.php");
		require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
	}
	if($basket_loaded)
		require_once("modules/basket/class/BasketControler.php");
	if($mode == 'del' && $entities_loaded)
		require_once("modules/entities/class/EntityControler.php");

} catch (Exception $e){
	echo $e->getMessage();
}


function init_session()
{
	$_SESSION['m_admin']['groups'] = array();
	$_SESSION['m_admin']['groups']['GroupId'] = "";
	$_SESSION['m_admin']['groups']['desc'] = "";
	$_SESSION['m_admin']['groups']['security'] = array();
	$_SESSION['m_admin']['groups']['services'] = array();
	$_SESSION['m_admin']['init'] = false;
}

function transform_security_object_into_array($security)
{
	if(!isset($security))
	{
		return array();
	}
	
	$sec_id = $security->__get('security_id');
	$group_id = $security->__get('group_id');
	$comment = $security->__get('maarch_comment');
	$coll_id = $security->__get('coll_id');
	$where = $security->__get('where_clause');
	$target = $security->__get('where_target');
	$start_date = $security->__get('mr_start_date');
	$stop_date = $security->__get('mr_stop_date');
	$rights_bitmask = $security->__get('rights_bitmask');
	$sec = new security();
	$ind = $sec->get_ind_collection($coll_id);
	
	return array('SECURITY_ID' => $sec_id , 'GROUP_ID' => $group_id ,'COLL_ID' => $coll_id, 'IND_COLL_SESSION' => $ind, 'WHERE_CLAUSE' => $where, 'COMMENT' => $comment ,'WHERE_TARGET'=> $target, 'START_DATE' => $start_date, 'STOP_DATE' => $stop_date, 'RIGHTS_BITMASK' => $rights_bitmask);
}

function transform_array_of_security_object($array_sec)
{
	$res = array();
	for($i=0; $i<count($array_sec);$i++)
	{
		array_push($res, transform_security_object_into_array($array_sec[$i]));
	}
	return $res;
}


// passer le mode en param + id si mode up
// 

if(isset($_REQUEST['id']) && !empty($_REQUEST['id']))
{
	$group_id = $_REQUEST['id'];
}


if(isset($_REQUEST['group_submit']))
{
	if($mode == "add")
	{
		$_SESSION['m_admin']['groups']['GroupId'] = functions::wash($_REQUEST['group_id'], "alphanum", _THE_GROUP, 'yes', 0, 32);
	}

	if($mode == "up")
	{
		$_SESSION['m_admin']['groups']['GroupId'] = functions::wash($_REQUEST['group_id'], "alphanum", _THE_GROUP, 'yes', 0, 32);
	}
	if (isset($_POST['desc']) && !empty($_POST['desc']))
	{
		$_SESSION['m_admin']['groups']['desc'] = functions::wash($_REQUEST['desc'], "no", _GROUP_DESC, 'yes', 0, 255);
	}

	if (count($_SESSION['m_admin']['groups']['security']) < 1  && count($_REQUEST['services']) < 1)
	{
		functions::add_error(_THE_GROUP.' '._NO_SECURITY_AND_NO_SERVICES, "");
	}
	$_SESSION['m_admin']['groups']['order'] = $_REQUEST['order'];
	$_SESSION['m_admin']['groups']['order_field'] = $_REQUEST['order_field'];
	$_SESSION['m_admin']['groups']['what'] = $_REQUEST['what'];
	$_SESSION['m_admin']['groups']['start'] = $_REQUEST['start'];	
	
	if($mode == "add" && UsergroupControler::groupExists($_SESSION['m_admin']['groups']['GroupId']))
	{	
		$_SESSION['error'] = $_SESSION['m_admin']['groups']['GroupId']." "._ALREADY_EXISTS."<br />";
	}
	if(!empty($_SESSION['error']))
	{
		if($mode == "up")
		{
			if(!empty($_SESSION['m_admin']['groups']['GroupId']))
			{
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=usergroups_management_controler&mode=up&group_id=".$_SESSION['m_admin']['groups']['GroupId']."&admin=groups");
				exit;
			}
			else
			{
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=usergroups_management_controler&mode=list&admin=groups&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit;
			}
		}
		elseif($mode == "add")
		{
			$_SESSION['m_admin']['load_group'] = false;
			header("location: ".$_SESSION['config']['businessappurl']."index.php?page=usergroups_management_controler&mode=add&admin=groups");
			exit;
		}
	}
	else
	{
		$group_value = array('group_id' => functions::protect_string_db($_SESSION['m_admin']['groups']['GroupId']) , 'group_desc' => functions::protect_string_db($_SESSION['m_admin']['groups']['desc']), 'enabled' => 'Y');
		$usergroup = new Usergroup;
		$usergroup->setArray($group_value);
		
		UsergroupControler::save($usergroup, $mode);
	
		SecurityControler::deleteForGroup($_SESSION['m_admin']['groups']['GroupId']);
		for($i=0; $i < count($_SESSION['m_admin']['groups']['security'] ); $i++)
		{
			if($_SESSION['m_admin']['groups']['security'][$i] <> "")
			{
				$values = array('group_id' => $_SESSION['m_admin']['groups']['GroupId'],
								'coll_id' =>functions::protect_string_db($_SESSION['m_admin']['groups']['security'][$i]['COLL_ID']), 
								'where_clause' => functions::protect_string_db($_SESSION['m_admin']['groups']['security'][$i]['WHERE_CLAUSE']), 
								'maarch_comment' => functions::protect_string_db($_SESSION['m_admin']['groups']['security'][$i]['COMMENT']), 
								'where_target' => functions::protect_string_db($_SESSION['m_admin']['groups']['security'][$i]['WHERE_TARGET']));
								 
				$bitmask = '0';
				if(isset($_SESSION['m_admin']['groups']['security'][$i]['RIGHTS_BITMASK']) && !empty($_SESSION['m_admin']['groups']['security'][$i]['RIGHTS_BITMASK']))
				{
					$bitmask = (string) $_SESSION['m_admin']['groups']['security'][$i]['RIGHTS_BITMASK'];
				}
				$values['rights_bitmask'] = $bitmask;
				
				if(isset($_SESSION['m_admin']['groups']['security'][$i]['START_DATE']) && !empty($_SESSION['m_admin']['groups']['security'][$i]['START_DATE']))
				{
					$values['mr_start_date'] = functions::format_date_db($_SESSION['m_admin']['groups']['security'][$i]['START_DATE']);
				}
				if(isset($_SESSION['m_admin']['groups']['security'][$i]['STOP_DATE']) && !empty($_SESSION['m_admin']['groups']['security'][$i]['STOP_DATE']))
				{
					$values['mr_stop_date'] = functions::format_date_db($_SESSION['m_admin']['groups']['security'][$i]['STOP_DATE']);
				}
				
				$sec = new SecurityObj();
				$sec->setArray($values);
				SecurityControler::save($sec);
			}
		}
		UsergroupControler::deleteServicesForGroup($_SESSION['m_admin']['groups']['GroupId']);
		for($i=0; $i<count($_REQUEST['services']); $i++)
		{
			if(!empty($_REQUEST['services'][$i]))
			{
				UsergroupControler::insertServiceForGroup($_SESSION['m_admin']['groups']['GroupId'], $_REQUEST['services'][$i]);
			}
		}
						
		if($_SESSION['history']['usergroupsadd'] == "true" && $mode == "add")
		{
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$users = new history();
			$users->add($_SESSION['tablename']['usergroups'], $_SESSION['m_admin']['groups']['GroupId'],"ADD",_GROUP_ADDED." : ".$_SESSION['m_admin']['groups']['GroupId'], $_SESSION['config']['databasetype']);
		}
		elseif($_SESSION['history']['usergroupsup'] == "true" && $mode == "up")
		{
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$users = new history();
			$users->add($_SESSION['tablename']['usergroups'], $_SESSION['m_admin']['groups']['GroupId'],"UP",_GROUP_UPDATE." : ".$_SESSION['m_admin']['groups']['GroupId'], $_SESSION['config']['databasetype']);
		}
		unset($_SESSION['m_admin']);
		if($mode == "add")
		{
			$_SESSION['error'] =  _GROUP_ADDED;
		}
		else
		{
			$_SESSION['error'] = _GROUP_UPDATED;
			if(UsergroupControler::inGroup($_SESSION['user']['UserId'], $_SESSION['m_admin']['groups']['GroupId']) )
			{
				$_SESSION['user']['groups'] = array();
				$_SESSION['user']['security'] = array();
				//$sec->load_groups($_SESSION['user']['UserId']);
				$tmp = security::load_groups($_SESSION['user']['UserId']);
				$_SESSION['user']['groups'] = $tmp['groups'];
				$_SESSION['user']['primarygroup'] = $tmp['primarygroup'];

				$tmp = security::load_security($_SESSION['user']['UserId']);
				$_SESSION['user']['collections'] = $tmp['collections'];
				$_SESSION['user']['security'] = $tmp['security'];
			//	$sec->load_security();
				$_SESSION['user']['services'] = security::load_user_services($_SESSION['user']['UserId']);
			}
		}
						
		header("location: ".$_SESSION['config']['businessappurl']."index.php?page=usergroups_management_controler&mode=list&admin=groups&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
	}
	exit();	
}

$users = array();
$baskets = array();
$access = array();
$services = array();
$state = true;

if($mode == "up")
{
	$usergroup = UsergroupControler::get($group_id ); // ramène l'objet usergroup
	//$_SESSION['m_admin']['mode'] = "up";
	//if(empty($_SESSION['error']))
	//{		
	if(!isset($usergroup))
	{
		$state = false;
	}
	else
	{
		$_SESSION['m_admin']['groups']['GroupId'] = $usergroup->__get('group_id');
		$_SESSION['m_admin']['groups']['desc'] = $usergroup->__get('group_desc');

	//	if (! isset($_SESSION['m_admin']['load_security']) || $_SESSION['m_admin']['load_security'] == true)
		//{
			$access = SecurityControler::getAccessForGroup($group_id); // ramène le tableau des accès
			$_SESSION['m_admin']['groups']['security'] = transform_array_of_security_object($access);
			$_SESSION['m_admin']['load_security'] = false ;
	//	}
	//	if (! isset($_SESSION['m_admin']['load_services']) || $_SESSION['m_admin']['load_services'] == true)
	//	{
			$services = UsergroupControler::getServices($group_id);  // ramène le tableau des services
			$_SESSION['m_admin']['groups']['services'] = $services;
			$_SESSION['m_admin']['load_services'] = false ;
	//	}
		$users_id = UsergroupControler::getUsers($group_id ); //ramène le tableau des user_id appartenant au groupe
		$baskets_id = UsergroupControler::getBaskets($group_id ); //ramène le tableau des basket_id associées au groupe

		for($i=0; $i<count($users_id);$i++)
		{
			$tmp_user = UserControler::get($users_id[$i]);
			if(isset($tmp_user))
			{
				array_push($users, $tmp_user);
			}	
		}
		unset($tmp_user);
		
		if($basket_loaded)
		{
			for($i=0; $i<count($baskets_id);$i++)
			{
				$tmp_bask = BasketControler::get($baskets_id[$i]);
				if(isset($tmp_bask))
				{
					array_push($baskets, $tmp_bask);
				}
			}
		}

	}
}
elseif($mode == "add")
{
	//$_SESSION['m_admin']['mode'] = "add";
	if ($_SESSION['m_admin']['init']== true || !isset($_SESSION['m_admin']['init'] ))
	{
		init_session();
	}
}
elseif($mode == "list")
{
	$_SESSION['m_admin'] = array();
	init_session();
	
	$select[$_SESSION['tablename']['usergroups']] = array();
	array_push($select[$_SESSION['tablename']['usergroups']],"group_id","group_desc","enabled");
	$what = "";
	$where ="";
	if(isset($_REQUEST['what']) && !empty($_REQUEST['what']))
	{
		$what = functions::protect_string_db($_REQUEST['what']);
		if($_SESSION['config']['databasetype'] == "POSTGRESQL")
		{
			$where = "group_desc ilike '".strtolower($what)."%' or group_id ilike '".strtoupper($what)."%' ";
		}
		else
		{
			$where = "group_desc like '".strtolower($what)."%' or group_id like '".strtoupper($what)."%' ";
		}
	}

	$order = 'asc';
	if(isset($_REQUEST['order']) && !empty($_REQUEST['order']))
	{
		$order = trim($_REQUEST['order']);
	}
	$field = 'group_id';
	if(isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field']))
	{
		$field = trim($_REQUEST['order_field']);
	}

	$orderstr = list_show::define_order($order, $field);
	$request = new request();
	$tab=$request->select($select,$where,$orderstr,$_SESSION['config']['databasetype']);
	for ($i=0;$i<count($tab);$i++)
	{
		for ($j=0;$j<count($tab[$i]);$j++)
		{
			foreach(array_keys($tab[$i][$j]) as $value)
			{
				if($tab[$i][$j][$value]=="group_id")
				{
					$tab[$i][$j]["group_id"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]= _ID;
					$tab[$i][$j]["size"]="18";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='group_id';
				}
				if($tab[$i][$j][$value]=="group_desc")
				{
					$tab[$i][$j]['value']=functions::show_string($tab[$i][$j]['value']);
					$tab[$i][$j]["group_desc"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_DESC;
					$tab[$i][$j]["size"]="30";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='group_desc';
				}
				if($tab[$i][$j][$value]=="enabled")
				{
					$tab[$i][$j]["enabled"]= $tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_STATUS;
					$tab[$i][$j]["size"]="6";
					$tab[$i][$j]["label_align"]="center";
					$tab[$i][$j]["align"]="center";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='enabled';
				}
			}
		}
	}
	$page_name = "usergroups_management_controler&mode=list";
	$page_name_up = "usergroups_management_controler&mode=up";
	$page_name_del = "usergroups_management_controler&mode=del";
	$page_name_val= "usergroups_management_controler&mode=allow";
	$page_name_ban = "usergroups_management_controler&mode=ban";
	$page_name_add = "usergroups_management_controler&mode=add";
	$label_add = _GROUP_ADDITION;
	$_SESSION['m_admin']['load_security']  = true;
	$_SESSION['m_admin']['load_services'] = true;
	$_SESSION['m_admin']['init'] = true;
	$title = _GROUPS_LIST." : ".$i." "._GROUPS;
	$autoCompletionArray = array();
	$autoCompletionArray["list_script_url"] = $_SESSION['config']['businessappurl']."index.php?display=true&admin=groups&page=groups_list_by_name";
	$autoCompletionArray["number_to_begin"] = 1;
}
elseif((!isset($group_id) || empty($group_id) || ! UsergroupControler::groupExists($group_id)) &&($mode == "del" ||$mode == "ban" || $mode == "allow"))
{
	$_SESSION['error'] = _GROUP.' '._UNKNOWN;
}
elseif($mode == "ban")
{
	UsergroupControler::disable($group_id);
}
elseif($mode == "allow")
{
	UsergroupControler::enable($group_id);
}
elseif($mode == "del")
{
	UsergroupControler::delete($group_id);
	if($basket_loaded)
		BasketControler::cleanFullGroupbasket($group_id, 'group_id');
	if($entities_loaded)
		EntityControler::cleanGroupbasketRedirect($group_id, 'group_id');
}

if($mode == "ban" || $mode == "allow" || $mode == "del")
{
	?><script>window.top.location='<?php echo $_SESSION['config']['businessappurl']."index.php?page=usergroups_management_controler&mode=list&admin=groups&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;?>';</script>
	<?php
	exit;
}

if($mode == "add" || $mode == "up" || $mode == "list")
{
	 /****************Management of the location bar  ************/
	$init = false;
	if($_REQUEST['reinit'] == "true")
	{
		$init = true;
	}
	$level = "";
	if($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)
	{
		$level = $_REQUEST['level'];
	}
	$page_path = $_SESSION['config']['businessappurl'].'index.php?page=usergroups_management_controler&admin=groups&mode='.$mode;
	$page_label = $page_labels[$mode];
	$page_id = $page_ids[$mode];
	core_tools::manage_location_bar($page_path, $page_label, $page_id, $init, $level);
	/***********************************************************/

	include('usergroups_management.php');
}
?>

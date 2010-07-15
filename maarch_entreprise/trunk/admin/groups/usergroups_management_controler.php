<?php

$basket_loaded = false;
$core_tools = new core_tools();
if($core_tools->is_module_loaded('basket'))
{
	$basket_loaded = true;
}

try{
	require_once("apps/maarch_entreprise/class/UsergroupControler.php");
	require_once("apps/maarch_entreprise/class/UserControler.php");
	require_once("core/class/SecurityControler.php");
	require_once("core/class/class_security.php");
	if($basket_loaded)
	{
		require_once("modules/basket/class/BasketControler.php");
	}
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

if(isset($_REQUEST['group_id']) && !empty($_REQUEST['group_id']))
{
	$group_id = $_REQUEST['group_id'];
}

$mode = 'add';
if(isset($_REQUEST['mode']) && !empty($_REQUEST['mode']))
{
	$mode = $_REQUEST['mode'];
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
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=groups&admin=groups&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
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
						
		header("location: ".$_SESSION['config']['businessappurl']."index.php?page=groups&admin=groups&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
						
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
	//$_SESSION['m_admin']['mode'] = "up";
	//if(empty($_SESSION['error']))
	//{		

	$usergroup = UsergroupControler::get($group_id ); // ramène l'objet usergroup

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
			$access = SecurityControler::get_access_for_group($group_id); // ramène le tableau des accès
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
			array_push($users, UserControler::get($users_id[$i]));
		}
		
		if($basket_loaded)
		{
			for($i=0; $i<count($baskets_id);$i++)
			{
				array_push($baskets, BasketControler::get($baskets_id[$i]));
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
$page_label = _MODIFICATION;
$page_id = "usergroups_management_controler";
$core_tools->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/

include('usergroups_management.php');
?>

<?php

$entities_loaded = false;

if(core_tools::is_module_loaded('entities'))
	$entities_loaded = true;

$mode = 'add';
if(isset($_REQUEST['mode']) && !empty($_REQUEST['mode']))
{
	$mode = $_REQUEST['mode'];
}

$page_labels = array('add' => _ADDITION, 'up' => _MODIFICATION, 'list' => _USERS_LIST);
$page_ids = array('add' => 'user_add', 'up' => 'user_up', 'list' => 'users_list');

try{
	require_once("core/class/UsergroupControler.php");
	require_once("core/class/UserControler.php");
	if($mode == 'list')
	{
		require_once("core/class/class_request.php");
		require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
	}
	if($mode == 'del' && $entities_loaded)
		require_once("modules/entities/class/EntityControler.php");

} catch (Exception $e){
	echo $e->getMessage();
}

function init_session()
{
	$_SESSION['m_admin']['users'] = array();
	$_SESSION['m_admin']['users']['UserId'] = "";
	$_SESSION['m_admin']['users']['pass'] = "";
	$_SESSION['m_admin']['users']['FirstName'] = "";
	$_SESSION['m_admin']['users']['LastName'] = "";
	$_SESSION['m_admin']['users']['Phone'] = "";
	$_SESSION['m_admin']['users']['Mail'] = "";
	$_SESSION['m_admin']['users']['Department'] = "";
	$_SESSION['m_admin']['users']['Enabled'] = "";
	$_SESSION['m_admin']['users']['groups'] = array();
	$_SESSION['m_admin']['users']['nbbelonginggroups'] = 0;
	$_SESSION['m_admin']['init'] = false ;
	$_SESSION['m_admin']['load_group']  = true;
}

if(isset($_REQUEST['id']) && !empty($_REQUEST['id']))
{
	$user_id = $_REQUEST['id'];
}

if(isset($_REQUEST['user_submit']))
{
	if($mode == "add")
	{
		$_SESSION['m_admin']['users']['UserId'] = functions::wash($_REQUEST['user_id'], "no", _THE_ID, 'yes', 0, 32);
		if(isset($_SESSION['config']['userdefaultpassword']) && !empty($_SESSION['config']['userdefaultpassword']))
			$_SESSION['m_admin']['users']['pass'] = md5($_SESSION['config']['userdefaultpassword']);
		else
			$_SESSION['m_admin']['users']['pass'] = md5('maarch');
	}

	if($mode == "up")
		$_SESSION['m_admin']['users']['UserId'] = functions::wash($_REQUEST['user_id'], "no", _THE_ID, 'yes', 0, 32);

	$_SESSION['m_admin']['users']['order'] = $_REQUEST['order'];
	$_SESSION['m_admin']['users']['order_field'] = $_REQUEST['order_field'];
	$_SESSION['m_admin']['users']['what'] = $_REQUEST['what'];
	$_SESSION['m_admin']['users']['start'] = $_REQUEST['start'];

	$_SESSION['m_admin']['users']['FirstName'] = functions::wash($_REQUEST['FirstName'], "no", _THE_FIRSTNAME, 'yes', 0, 255);
	$_SESSION['m_admin']['users']['LastName'] = functions::wash($_REQUEST['LastName'], "no", _THE_LASTNAME, 'yes', 0, 255);

	if(isset($_REQUEST['Department']) && !empty($_REQUEST['Department']))
		$_SESSION['m_admin']['users']['Department']  = functions::wash($_REQUEST['Department'], "no", _DEPARTMENT, 'yes', 0, 50);

	if(isset($_REQUEST['Phone']) && !empty($_REQUEST['Phone']))
		$_SESSION['m_admin']['users']['Phone']  = functions::wash($_REQUEST['Phone'], "no", _PHONE, 'yes', 0, 15);
		
	if(isset($_REQUEST['LoginMode']) && !empty($_REQUEST['LoginMode']))
		$_SESSION['m_admin']['users']['LoginMode']  = functions::wash($_REQUEST['LoginMode'], "no", _LOGIN_MODE, 'yes', 0, 50);
		
	if(isset($_REQUEST['Mail']) && !empty($_REQUEST['Mail']))
		$_SESSION['m_admin']['users']['Mail']  = functions::wash($_REQUEST['Mail'], "mail", _MAIL, 'yes', 0, 255);
		

	if($_SESSION['m_admin']['users']['UserId'] <> "superadmin")
	{
		$primary_set = false;
		for($i=0; $i < count($_SESSION['m_admin']['users']['groups']);$i++)
		{
			if($_SESSION['m_admin']['users']['groups'][$i]['PRIMARY'] == 'Y')
			{
				$primary_set = true;
				break;
			}
		}
		if ($primary_set == false)
			$_SESSION['error'] = _PRIMARY_GROUP.' '._MANDATORY;
	}

	$_SESSION['service_tag'] = 'user_check';
	core_tools::execute_modules_services($_SESSION['modules_services'], 'user_check', "include");

	
	
	if($mode == "add" && UserControler::userExists($_SESSION['m_admin']['users']['UserId']))
	{	
		$_SESSION['error'] = $_SESSION['m_admin']['users']['UserId']." "._ALREADY_EXISTS."<br />";
	}
	if(!empty($_SESSION['error']))
	{
		if($mode == "up")
		{
			if(!empty($_SESSION['m_admin']['users']['UserId']))
			{
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=users_management_controler&mode=up&id=".$_SESSION['m_admin']['users']['UserId']."&admin=users");
				exit;
			}
			else
			{
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=users_management_controler&mode=list&admin=users&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit;
			}
		}
		elseif($mode == "add")
		{
			header("location: ".$_SESSION['config']['businessappurl']."index.php?page=users_management_controler&mode=add&admin=users");
			exit;
		}
	}
	else
	{
		$user_value = array('user_id' => functions::protect_string_db($_SESSION['m_admin']['users']['UserId']) , 'firstname' => functions::protect_string_db($_SESSION['m_admin']['users']['FirstName']), 'lastname' => functions::protect_string_db($_SESSION['m_admin']['users']['LastName']), 'enabled' => 'Y');
		
		if(isset($_SESSION['m_admin']['users']['pass']) && !empty($_SESSION['m_admin']['users']['pass']))
			$user_value['password'] = functions::protect_string_db($_SESSION['m_admin']['users']['pass']);
		if(isset($_SESSION['m_admin']['users']['Department']) && !empty($_SESSION['m_admin']['users']['Department']))
			$user_value['department'] = functions::protect_string_db($_SESSION['m_admin']['users']['Department']);
		if(isset($_SESSION['m_admin']['users']['Phone']) && !empty($_SESSION['m_admin']['users']['Phone']))
			$user_value['phone'] = functions::protect_string_db($_SESSION['m_admin']['users']['Phone']);
		if(isset($_SESSION['m_admin']['users']['LoginMode']) && !empty($_SESSION['m_admin']['users']['LoginMode']))
			$user_value['loginmode'] = functions::protect_string_db($_SESSION['m_admin']['users']['LoginMode']);
		if(isset($_SESSION['m_admin']['users']['Mail']) && !empty($_SESSION['m_admin']['users']['Mail']))
			$user_value['mail'] = functions::protect_string_db($_SESSION['m_admin']['users']['Mail']);
			
		$user = new User;
		$user->setArray($user_value);
		
		UserControler::save($user, $mode);
		
		UserControler::cleanUsergroupContent($_SESSION['m_admin']['users']['UserId']);
		UserControler::loadDbUsergroupContent($_SESSION['m_admin']['users']['UserId'], $_SESSION['m_admin']['users']['groups']);
		
		$_SESSION['service_tag'] = 'user_'.$mode;
		core_tools::execute_modules_services($_SESSION['modules_services'], 'users_add_db.php', "include");
		
		if($_SESSION['history']['usersadd'] == "true" && $mode == "add")
		{
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$hist = new history();
			$hist->add($_SESSION['tablename']['users'], $_SESSION['m_admin']['users']['UserId'],"ADD",_USER_ADDED." : ".$_SESSION['m_admin']['users']['UserId'], $_SESSION['config']['databasetype']);
		}
		elseif($_SESSION['history']['usersup'] == "true" && $mode == "up")
		{
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$hist = new history();
			$hist->add($_SESSION['tablename']['users'], $_SESSION['m_admin']['users']['UserId'],"UP",_USER_UPDATE." : ".$_SESSION['m_admin']['users']['UserId'], $_SESSION['config']['databasetype']);
		}
		unset($_SESSION['m_admin']);
		if($mode == "add")
		{
			$_SESSION['error'] =  _USER_ADDED;
		}
		else
		{
			$_SESSION['error'] = _USER_UPDATED;
		}
						
		header("location: ".$_SESSION['config']['businessappurl']."index.php?page=users_management_controler&mode=list&admin=users&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
	}
	exit();	
}

$state = true;

if($mode == "up")
{
	$user = UserControler::get($user_id );
	
	if(!isset($user))
	{
		$state = false;
	}
	else
	{
		$_SESSION['m_admin']['users']['UserId'] = $user->__get('user_id');
		$_SESSION['m_admin']['users']['FirstName'] = functions::show_string($user->__get('firstname'));
		$_SESSION['m_admin']['users']['LastName'] = functions::show_string($user->__get('lastname'));
		$_SESSION['m_admin']['users']['Phone'] = $user->__get('phone');
		$_SESSION['m_admin']['users']['Mail'] = $user->__get('mail');
		$_SESSION['m_admin']['users']['Department'] = functions::show_string($user->__get('department'));
		$_SESSION['m_admin']['users']['Enabled'] = $user->__get('enabled');
		$_SESSION['m_admin']['users']['Status'] = $user->__get('status');
		$_SESSION['m_admin']['users']['LoginMode'] = $user->__get('loginmode');

		if (($_SESSION['m_admin']['load_group'] == true || ! isset($_SESSION['m_admin']['load_group'] )) && $_SESSION['m_admin']['users']['UserId'] <> "superadmin")
		{
			$tmp_array = UserControler::getGroups($_SESSION['m_admin']['users']['UserId']);
			for($i=0; $i<count($tmp_array);$i++)
			{
				$group = UsergroupControler::get($tmp_array[$i]['GROUP_ID']);
				$tmp_array[$i]['LABEL'] = $group->__get('group_desc');
			}
			$_SESSION['m_admin']['users']['groups'] = $tmp_array;
			unset($tmp_array);
		}
		//session entities
		
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
	
	$select[$_SESSION['tablename']['users']] = array();
	array_push($select[$_SESSION['tablename']['users']],'user_id','lastname','firstname','enabled','status','mail' );
	$what = "";
	$where = " status <> 'DEL'";
	if(isset($_REQUEST['what']) && !empty($_REQUEST['what']))
	{
		$what = functions::protect_string_db($_REQUEST['what']);
		if($_SESSION['config']['databasetype'] == "POSTGRESQL")
		{
			$where .= "and( lastname ilike '".strtolower($what)."%' or lastname ilike '".strtoupper($what)."%' )";
		}
		else
		{
			$where .= "and( lastname like '".strtolower($what)."%' or lastname like '".strtoupper($what)."%' )";
		}
	}

	$order = 'asc';
	if(isset($_REQUEST['order']) && !empty($_REQUEST['order']))
	{
		$order = trim($_REQUEST['order']);
	}
	$field = 'lastname';
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
				if($tab[$i][$j][$value]=="user_id")
				{
					$tab[$i][$j]["user_id"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]= _ID;
					$tab[$i][$j]["size"]="10";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='user_id';
				}
				if($tab[$i][$j][$value]=="lastname")
				{
					$tab[$i][$j]['value']= functions::show_string($tab[$i][$j]['value']);
					$tab[$i][$j]["lastname"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_LASTNAME;
					$tab[$i][$j]["size"]="10";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='lastname';
				}
				if($tab[$i][$j][$value]=="firstname")
				{
					$tab[$i][$j]['value']= functions::show_string($tab[$i][$j]['value']);
					$tab[$i][$j]["firstname"]= $tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_FIRSTNAME;
					$tab[$i][$j]["size"]="10";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='firstname';
				}
				if($tab[$i][$j][$value]=="enabled")
				{
					$tab[$i][$j]["enabled"]= $tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_STATUS;
					$tab[$i][$j]["size"]="3";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="center";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='enabled';
				}
				if($tab[$i][$j][$value]=="mail")
				{
					$tab[$i][$j]["mail"] = $tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_MAIL;
					$tab[$i][$j]["size"]="10";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='mail';
				}
				if($tab[$i][$j][$value]=="status")
				{
					if($tab[$i][$j]['value'] == "ABS")
					{
						$tab[$i][$j]['value'] = "<em>("._MISSING.")</em>";
					}
					else
					{
						$tab[$i][$j]['value'] = '';
					}
					$tab[$i][$j]["status"] = $tab[$i][$j]['value'];
					$tab[$i][$j]["label"]='';
					$tab[$i][$j]["size"]="5";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='';
				}
			}
		}
	}

	$page_name = "users_management_controler&mode=list";
	$page_name_up = "users_management_controler&mode=up";
	$page_name_del = "users_management_controler&mode=del";
	$page_name_val= "users_management_controler&mode=allow";
	$page_name_ban = "users_management_controler&mode=ban";
	$page_name_add = "users_management_controler&mode=add";
	$label_add = _USER_ADDITION;
	$_SESSION['m_admin']['init'] = true;
	$title = _USERS_LIST." : ".$i." "._USERS;
	$autoCompletionArray = array();
	$autoCompletionArray["list_script_url"] = $_SESSION['config']['businessappurl']."index.php?display=true&admin=users&page=users_list_by_name";
	$autoCompletionArray["number_to_begin"] = 1;
}
elseif((!isset($user_id) || empty($user_id) || ! UserControler::userExists($user_id)) &&($mode == "del" ||$mode == "ban" || $mode == "allow"))
{
	$_SESSION['error'] = _USER.' '._UNKNOWN;
}
elseif($mode == "ban")
{
	UserControler::disable($user_id);
}
elseif($mode == "allow")
{
	UserControler::enable($user_id);
}
elseif($mode == "del")
{
	UserControler::delete($user_id);
	//to do : user_abs, listmodel
	// to do ? :user_entities,, listinstance
}

if($mode == "ban" || $mode == "allow" || $mode == "del")
{
	?><script>window.top.location='<?php echo $_SESSION['config']['businessappurl']."index.php?page=users_management_controler&mode=list&admin=users&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;?>';</script>
	<?php
	exit;
}


if($mode == "add" || $mode == "up" || $mode == "list")
{
	if($mode <> 'list')
	{
		$_SESSION['service_tag'] = 'user_init';
		core_tools::execute_modules_services($_SESSION['modules_services'], 'user_init', "include");
	}
	
	$_SESSION['m_admin']['nbgroups']  = UsergroupControler::getUsergroupsCount();
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
	$page_path = $_SESSION['config']['businessappurl'].'index.php?page=users_management_controler&admin=users&mode='.$mode;
	$page_label = $page_labels[$mode];
	$page_id = $page_ids[$mode];
	core_tools::manage_location_bar($page_path, $page_label, $page_id, $init, $level);
	/***********************************************************/

	include('users_management.php');
}
?>

<?php
$sessionName = "docservers_locations";
$pageName = "docservers_locations_management_controler";
$tableName = "docserver_locations";
$idName = "docserver_location_id";

$mode = 'add';
if(isset($_REQUEST['mode']) && !empty($_REQUEST['mode']))
{
	$mode = $_REQUEST['mode'];
}
$page_labels = array('add' => _ADDITION, 'up' => _MODIFICATION, 'list' => _DOCSERVERS_LOCATIONS_LIST);
$page_ids = array('add' => 'docservers_locations_add', 'up' => 'docservers_locations_up', 'list' => 'docservers_locations_list');
try{
	require_once("modules".DIRECTORY_SEPARATOR."life_cycle".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."DocserverLocationControler.php");
	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
	if($mode == 'list')
	{
		require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
		require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
	}
} catch (Exception $e){
	echo $e->getMessage();
}

function init_session()
{
	$sessionName = "docservers_locations";
	$_SESSION['m_admin'][$sessionName] = array();
	$_SESSION['m_admin']['init'] = false;
}

if(isset($_REQUEST['id']) && !empty($_REQUEST['id']))
{
	$idName = $_REQUEST['id'];
}

if(isset($_REQUEST['submit']))
{
	if($mode == "add" || $mode == "up")
	{
		$_SESSION['m_admin'][$sessionName][$idName] = functions::wash($_REQUEST['id'], "nick", _THE_DOCSERVER_LOCATION_ID, 'yes', 0, 32);
	}
	
	$_SESSION['m_admin'][$sessionName]['ipv4_filter'] = functions::wash($_REQUEST['ipv4_filter'], "no", _IPV4_FILTER, 'yes', 0, 1024);
	$_SESSION['m_admin'][$sessionName]['ipv6_filter'] = functions::wash($_REQUEST['ipv6_filter'], "no", _IPV6_FILTER, 'no', 0, 1024);
	
	if($mode == "add" && DocserverLocationControler::docserverLocationExists($_SESSION['m_admin'][$sessionName][$idName]))
	{	
		$_SESSION['error'] = $_SESSION['m_admin'][$sessionName][$idName]." "._ALREADY_EXISTS."<br />";
	}
	if(!empty($_SESSION['error']))
	{
		if($mode == "up")
		{
			if(!empty($_SESSION['m_admin'][$sessionName][$idName]))
			{
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=up&id=".$_SESSION['m_admin'][$sessionName][$idName]."&module=life_cycle");
				exit;
			}
			else
			{
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&module=life_cycle&mode=up&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what."&id=".$idName);
				exit;
			}
		}
		elseif($mode == "add")
		{
			$_SESSION['m_admin']['load_docserver_location'] = false;
			header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=add&module=life_cycle");
			exit;
		}
	}
	else
	{
		$docserver_location_value = array(
			'docserver_location_id' => functions::protect_string_db($_SESSION['m_admin'][$sessionName][$idName]), 
			'ipv4_filter' => functions::protect_string_db($_SESSION['m_admin'][$sessionName]['ipv4_filter']),
			'ipv6_filter' => functions::protect_string_db($_SESSION['m_admin'][$sessionName]['ipv6_filter']),  
			'enabled' => 'Y');
		$docserverLocation = new DocserverLocation;
		$docserverLocation->setArray($docserver_location_value);
		/*var_dump($_REQUEST);
		var_dump($docserverLocation);exit;*/
		DocserverLocationControler::save($docserverLocation, $mode);
		
		if($_SESSION['history']['docserverslocationsadd'] == "true" && $mode == "add")
		{
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add($_SESSION['tablename'][$tableName], $_SESSION['m_admin'][$sessionName][$idName],"ADD",_DOCSERVER_LOCATION_ADDED." : ".$_SESSION['m_admin'][$sessionName][$idName], $_SESSION['config']['databasetype']);
		}
		elseif($_SESSION['history']['docserverslocationsadd'] == "true" && $mode == "up")
		{
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add($_SESSION['tablename'][$tableName], $_SESSION['m_admin'][$sessionName][$idName],"UP",_DOCSERVER_LOCATION_UPDATED." : ".$_SESSION['m_admin'][$sessionName][$idName], $_SESSION['config']['databasetype']);
		}
		unset($_SESSION['m_admin']);
		if($mode == "add")
		{
			$_SESSION['error'] =  _DOCSERVER_LOCATION_ADDED;
		}
		else
		{
			$_SESSION['error'] = _DOCSERVER_LOCATION_UPDATED;
		}
		header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&module=life_cycle&mode=list&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
	}
	exit();	
}

$state = true;

if($mode == "up")
{
	$docserverLocation = DocserverLocationControler::get($idName);
	//var_dump($docserverLocation);
	if(!isset($docserverLocation))
	{
		$state = false;
	}
	else
	{
		$_SESSION['m_admin'][$sessionName] = $docserverLocation->getArray();
		if(empty($_SESSION['m_admin'][$sessionName]))
		{
			$state = false;
		}
	}
}
elseif($mode == "add")
{
	if ($_SESSION['m_admin']['init']== true || !isset($_SESSION['m_admin']['init'] ))
	{
		init_session();
	}
}
elseif($mode == "list")
{
	$_SESSION['m_admin'] = array();
	init_session();
	
	$select[$_SESSION['tablename'][$tableName]] = array();
	array_push($select[$_SESSION['tablename'][$tableName]], $idName, "ipv4_filter", "ipv6_filter", "enabled");
	$what = "";
	$where ="";
	if(isset($_REQUEST['what']) && !empty($_REQUEST['what']))
	{
		$what = functions::protect_string_db($_REQUEST['what']);
		if($_SESSION['config']['databasetype'] == "POSTGRESQL")
		{
			$where = $idName." ilike '".strtoupper($what)."%' ";
		}
		else
		{
			$where = $idName." like '".strtoupper($what)."%' ";
		}
	}
	$order = 'asc';
	if(isset($_REQUEST['order']) && !empty($_REQUEST['order']))
	{
		$order = trim($_REQUEST['order']);
	}
	$field = $idName;
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
				if($tab[$i][$j][$value]==$idName)
				{
					$tab[$i][$j][$idName]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]= _ID;
					$tab[$i][$j]["size"]="18";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]=$idName;
				}
				if($tab[$i][$j][$value]=="ipv4_filter")
				{
					$tab[$i][$j]["device_label"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]= _IPV4_FILTER;
					$tab[$i][$j]["size"]="18";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='ipv4_filter';
				}
				if($tab[$i][$j][$value]=="ipv6_filter")
				{
					$tab[$i][$j]["device_label"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]= _IPV6_FILTER;
					$tab[$i][$j]["size"]="18";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='ipv6_filter';
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
	$page_name = $pageName."&mode=list";
	$page_name_up = $pageName."&mode=up";
	$page_name_del = $pageName."&mode=del";
	$page_name_val= $pageName."&mode=allow";
	$page_name_ban = $pageName."&mode=ban";
	$page_name_add = $pageName."&mode=add";
	$label_add = _DOCSERVER_LOCATION_ADDITION;
	$_SESSION['m_admin']['init'] = true;
	$title = _DOCSERVERS_LOCATIONS_LIST." : ".$i." "._DOCSERVERS_LOCATIONS;
	$autoCompletionArray = array();
	$autoCompletionArray["list_script_url"] = $_SESSION['config']['businessappurl']."index.php?display=true&module=life_cycle&page=docservers_locations_list_by_id";
	$autoCompletionArray["number_to_begin"] = 1;
}
elseif((!isset($idName) || empty($idName) || ! DocserverLocationControler::docserverLocationExists($idName)) &&($mode == "del" ||$mode == "ban" || $mode == "allow"))
{
	$_SESSION['error'] = _DOCSERVER_LOCATION.' '._UNKNOWN;
}
elseif($mode == "ban")
{
	DocserverLocationControler::disable($idName);
}
elseif($mode == "allow")
{
	DocserverLocationControler::enable($idName);
}
elseif($mode == "del")
{
	DocserverLocationControler::delete($idName);
}
if($mode == "ban" || $mode == "allow" || $mode == "del")
{
	?><script>window.top.location='<?php echo $_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=list&module=life_cycle&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;?>';</script>
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
	$page_path = $_SESSION['config']['businessappurl']."index.php?page=".$pageName."&module=life_cycle&mode=".$mode;
	$page_label = $page_labels[$mode];
	$page_id = $page_ids[$mode];
	core_tools::manage_location_bar($page_path, $page_label, $page_id, $init, $level);
	/***********************************************************/
	include('docservers_locations_management.php');
}
?>


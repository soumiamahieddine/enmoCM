<?php
$sessionName = "docservers";
$pageName = "docservers_management_controler";
$tableName = "docservers";
$idName = "docserver_id";

$mode = 'add';
if(isset($_REQUEST['mode']) && !empty($_REQUEST['mode']))
{
	$mode = $_REQUEST['mode'];
}
$page_labels = array('add' => _ADDITION, 'up' => _MODIFICATION, 'list' => _DOCSERVERS_LIST);
$page_ids = array('add' => 'docserver_add', 'up' => 'docserver_up', 'list' => 'docservers_list');
try{
	require_once("modules".DIRECTORY_SEPARATOR."life_cycle".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."DocserverControler.php");
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
	$sessionName = "docservers";
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
		$_SESSION['m_admin'][$sessionName][$idName] = functions::wash($_REQUEST['id'], "nick", _THE_DOCSERVER_ID, 'yes', 0, 32);
	}
	if(isset($_REQUEST['size_limit_hidden']) && !empty($_REQUEST['size_limit_hidden']))
	{
		$_SESSION['m_admin'][$sessionName]['size_limit'] = functions::wash($_REQUEST['size_limit_hidden'], "no", _SIZE_LIMIT, 'yes', 0, 20);
	}
	$_SESSION['m_admin'][$sessionName]['device_type'] = functions::wash($_REQUEST['device_type'], "no", _DEVICE_TYPE, 'yes', 0, 255);
	$_SESSION['m_admin'][$sessionName]['device_label'] = functions::wash($_REQUEST['device_label'], "no", _DEVICE_LABEL, 'yes', 0, 255);
	$_SESSION['m_admin'][$sessionName]['is_readonly'] = functions::wash($_REQUEST['is_readonly'], "alphanum", _IS_READONLY, 'yes', 0, 1);
	$_SESSION['m_admin'][$sessionName]['path_template'] = functions::wash($_REQUEST['path_template'], "no", _PATH_TEMPLATE, 'yes', 0, 255);
	if(!is_dir($_SESSION['m_admin'][$sessionName]['path_template']))
	{
		$_SESSION['error'] .= _PATH_OF_DOCSERVER_UNAPPROACHABLE;
	}
	else
	{
		$Fnm = $_SESSION['m_admin'][$sessionName]['path_template']."test_docserver.txt";
		$isWriteable = true;
		if($inF = fopen($Fnm,"a"))
		{
			fwrite($inF,$texte);
			if(file_exists($Fnm))
			{
				unlink($Fnm);
			}
			else
			{
				$isWriteable = false;
			}
			fclose($inF);
		}
		else
		{
			$isWriteable = false;
		}
		if(!$isWriteable)
		{
			$_SESSION['error'] .= _THE_DOCSERVER_DOES_NOT_HAVE_THE_ADEQUATE_RIGHTS;
		}
	}
	$_SESSION['m_admin'][$sessionName]['priority'] = functions::wash($_REQUEST['priority'], "num", _PRIORITY, 'yes', 0, 6);
	$_SESSION['m_admin'][$sessionName]['oais_mode'] = functions::wash($_REQUEST['oais_mode'], "no", _OAIS_MODE, 'yes', 0, 32);
	$_SESSION['m_admin'][$sessionName]['sign_mode'] = functions::wash($_REQUEST['sign_mode'], "no", _SIGN_MODE, 'yes', 0, 32);
	$_SESSION['m_admin'][$sessionName]['compress_mode'] = functions::wash($_REQUEST['compress_mode'], "no", _COMPRESS_MODE, 'yes', 0, 32);
	$_SESSION['m_admin'][$sessionName]['docserver_locations_docserver_location_id'] = functions::wash($_REQUEST['docserver_locations_docserver_location_id'], "no", _DOCSERVER_LOCATION, 'yes', 0, 32);
	$_SESSION['m_admin'][$sessionName]['coll_id'] = functions::wash($_REQUEST['coll_id'], "no", _COLLECTION, 'yes', 0, 32);
	$_SESSION['m_admin'][$sessionName]['order'] = $_REQUEST['order'];
	$_SESSION['m_admin'][$sessionName]['order_field'] = $_REQUEST['order_field'];
	$_SESSION['m_admin'][$sessionName]['what'] = $_REQUEST['what'];
	$_SESSION['m_admin'][$sessionName]['start'] = $_REQUEST['start'];	
	
	if($mode == "add" && DocserverControler::docserverExists($_SESSION['m_admin'][$sessionName][$idName]))
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
			$_SESSION['m_admin']['load_docserver'] = false;
			header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=add&module=life_cycle");
			exit;
		}
	}
	else
	{
		$docserver_value = array(
			'docserver_id' => functions::protect_string_db($_SESSION['m_admin'][$sessionName][$idName]), 
			'device_type' => functions::protect_string_db($_SESSION['m_admin'][$sessionName]['device_type']), 
			'device_label' => functions::protect_string_db($_SESSION['m_admin'][$sessionName]['device_label']),
			'is_readonly' => functions::protect_string_db($_SESSION['m_admin'][$sessionName]['is_readonly']),
			'size_limit' => $_SESSION['m_admin'][$sessionName]['size_limit'],
			'path_template' => functions::protect_string_db($_SESSION['m_admin'][$sessionName]['path_template']),
			'coll_id' => functions::protect_string_db($_SESSION['m_admin'][$sessionName]['coll_id']),
			'priority' => $_SESSION['m_admin'][$sessionName]['priority'],
			'oais_mode' => functions::protect_string_db($_SESSION['m_admin'][$sessionName]['oais_mode']),
			'sign_mode' => functions::protect_string_db($_SESSION['m_admin'][$sessionName]['sign_mode']),
			'compress_mode' => functions::protect_string_db($_SESSION['m_admin'][$sessionName]['compress_mode']),
			'docserver_locations_docserver_location_id' => functions::protect_string_db($_SESSION['m_admin'][$sessionName]['docserver_locations_docserver_location_id']),
			'enabled' => 'Y');
		if($mode == "add")
			$docserver_value['creation_date'] = request::current_datetime();
		$docserver = new Docserver;
		$docserver->setArray($docserver_value);
		/*var_dump($_REQUEST);
		var_dump($docserver);exit;*/
		DocserverControler::save($docserver, $mode);
		
		if($_SESSION['history']['docserversadd'] == "true" && $mode == "add")
		{
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add($_SESSION['tablename'][$tableName], $_SESSION['m_admin'][$sessionName][$idName],"ADD",_DOCSERVER_ADDED." : ".$_SESSION['m_admin'][$sessionName][$idName], $_SESSION['config']['databasetype']);
		}
		elseif($_SESSION['history']['docserversadd'] == "true" && $mode == "up")
		{
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add($_SESSION['tablename'][$tableName], $_SESSION['m_admin'][$sessionName][$idName],"UP",_DOCSERVER_UPDATED." : ".$_SESSION['m_admin'][$sessionName][$idName], $_SESSION['config']['databasetype']);
		}
		unset($_SESSION['m_admin']);
		if($mode == "add")
		{
			$_SESSION['error'] =  _DOCSERVER_ADDED;
		}
		else
		{
			$_SESSION['error'] = _DOCSERVER_UPDATED;
		}
		header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&module=life_cycle&mode=list&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
	}
	exit();	
}

$state = true;

if($mode == "up")
{
	$docserver = DocserverControler::get($idName);
	$docserverLocationArray = array();
	$docserverLocationArray = DocserverLocationControler::getAllId();
	//var_dump($docserver);
	//var_dump($docserverLocationArray);
	if(!isset($docserver))
	{
		$state = false;
	}
	else
	{
		$_SESSION['m_admin'][$sessionName] = $docserver->getArray();
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
	array_push($select[$_SESSION['tablename'][$tableName]],$idName, "device_label", "path_template", "coll_id", "enabled");
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
				if($tab[$i][$j][$value]=="device_label")
				{
					$tab[$i][$j]["device_label"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]= _LABEL;
					$tab[$i][$j]["size"]="18";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='device_label';
				}
				if($tab[$i][$j][$value]=="path_template")
				{
					$tab[$i][$j]['value']=functions::show_string($tab[$i][$j]['value']);
					$tab[$i][$j]["path_template"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_PATH_TEMPLATE;
					$tab[$i][$j]["size"]="55";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='path_template';
				}
				if($tab[$i][$j][$value]=="coll_id")
				{
					$tab[$i][$j]['value']=functions::show_string($tab[$i][$j]['value']);
					$tab[$i][$j]["coll_id"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_COLL;
					$tab[$i][$j]["size"]="30";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='coll_id';
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
	$label_add = _DOCSERVER_ADDITION;
	$_SESSION['m_admin']['init'] = true;
	$title = _DOCSERVERS_LIST." : ".$i." "._DOCSERVERS;
	$autoCompletionArray = array();
	$autoCompletionArray["list_script_url"] = $_SESSION['config']['businessappurl']."index.php?display=true&module=life_cycle&page=docservers_list_by_id";
	$autoCompletionArray["number_to_begin"] = 1;
}
elseif((!isset($idName) || empty($idName) || ! DocserverControler::docserverExists($idName)) &&($mode == "del" ||$mode == "ban" || $mode == "allow"))
{
	$_SESSION['error'] = _DOCSERVER.' '._UNKNOWN;
}
elseif($mode == "ban")
{
	DocserverControler::disable($idName);
}
elseif($mode == "allow")
{
	DocserverControler::enable($idName);
}
elseif($mode == "del")
{
	DocserverControler::delete($idName);
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
	include('docservers_management.php');
}
?>


<?php
$sessionName = "cycles";
$pageName = "cycles_management_controler";
$tableName = "lc_cycle";
$idName = "cycle_id";

$mode = 'add';
if(isset($_REQUEST['mode']) && !empty($_REQUEST['mode']))
{
	$mode = $_REQUEST['mode'];
}
$page_labels = array('add' => _ADDITION, 'up' => _MODIFICATION, 'list' => _CYCLES_LIST);
$page_ids = array('add' => 'cycle_add', 'up' => 'cycle_up', 'list' => 'cycles_list');
try{
	require_once("modules".DIRECTORY_SEPARATOR."life_cycle".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."CycleControler.php");
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
	$sessionName = "cycles";
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
		$_SESSION['m_admin'][$sessionName][$idName] = functions::wash($_REQUEST['id'], "nick", _THE_CYCLE_ID, 'yes', 0, 32);
	}
	$_SESSION['m_admin'][$sessionName]['cycle_label'] = functions::wash($_REQUEST['cycle_label'], "no", _CYCLE_LABEL, 'yes', 0, 32);
	$_SESSION['m_admin'][$sessionName]['coll_id'] = functions::wash($_REQUEST['coll_id'], "no", _COLLECTION, 'yes', 0, 32);
	$_SESSION['m_admin'][$sessionName]['cycle_mode'] = functions::wash($_REQUEST['cycle_mode'], "no", _CYCLE_MODE, 'yes', 0, 32);
	$_SESSION['m_admin'][$sessionName]['where_clause'] = functions::wash($_REQUEST['where_clause'], "no", _WHERE_CLAUSE, 'yes', 0, 2048);
	$_SESSION['m_admin'][$sessionName]['is_must_complete'] = functions::wash($_REQUEST['is_must_complete'], "alphanum", _IS_MUST_COMPLETE, 'yes', 0, 1);
	$_SESSION['m_admin'][$sessionName]['preprocess_script'] = functions::wash($_REQUEST['preprocess_script'], "no", _PREPROCESS_SCRIPT, 'yes', 0, 255);
	$_SESSION['m_admin'][$sessionName]['postprocess_script'] = functions::wash($_REQUEST['postprocess_script'], "no", _POSTPROCESS_SCRIPT, 'yes', 0, 255);
	$_SESSION['m_admin'][$sessionName]['is_valid_by_user'] = functions::wash($_REQUEST['is_valid_by_user'], "alphanum", _IS_VALID_BY_USER, 'yes', 0, 1);
	$_SESSION['m_admin'][$sessionName]['users_user_id'] = functions::wash($_REQUEST['users_user_id'], "nick", _USER_ID, 'yes', 0, 32);
	
	$_SESSION['m_admin'][$sessionName]['order'] = $_REQUEST['order'];
	$_SESSION['m_admin'][$sessionName]['order_field'] = $_REQUEST['order_field'];
	$_SESSION['m_admin'][$sessionName]['what'] = $_REQUEST['what'];
	$_SESSION['m_admin'][$sessionName]['start'] = $_REQUEST['start'];	
	
	if($mode == "add" && CycleControler::cycleExists($_SESSION['m_admin'][$sessionName][$idName]))
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
			$_SESSION['m_admin']['load_cycle'] = false;
			header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=add&module=life_cycle");
			exit;
		}
	}
	else
	{
		$cycle_value = array(
			'cycle_id' => functions::protect_string_db($_SESSION['m_admin'][$sessionName][$idName]), 
			'cycle_label' => functions::protect_string_db($_SESSION['m_admin'][$sessionName]['cycle_label']), 
			'coll_id' => functions::protect_string_db($_SESSION['m_admin'][$sessionName]['coll_id']),
			'cycle_mode' => functions::protect_string_db($_SESSION['m_admin'][$sessionName]['cycle_mode']),
			'where_clause' => $_SESSION['m_admin'][$sessionName]['where_clause'],
			'is_must_complete' => functions::protect_string_db($_SESSION['m_admin'][$sessionName]['is_must_complete']),
			'preprocess_script' => functions::protect_string_db($_SESSION['m_admin'][$sessionName]['preprocess_script']),
			'postprocess_script' => $_SESSION['m_admin'][$sessionName]['postprocess_script'],
			'is_valid_by_user' => functions::protect_string_db($_SESSION['m_admin'][$sessionName]['is_valid_by_user']),
			'users_user_id' => functions::protect_string_db($_SESSION['m_admin'][$sessionName]['users_user_id']));
		
		$cycle = new cycle;
		$cycle->setArray($cycle_value);
		/*var_dump($_REQUEST);
		var_dump($cycle);exit;*/
		CycleControler::save($cycle, $mode);
		
		if($_SESSION['history']['lcadd'] == "true" && $mode == "add")
		{
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add($_SESSION['tablename'][$tableName], $_SESSION['m_admin'][$sessionName][$idName],"ADD",_CYCLE_ADDED." : ".$_SESSION['m_admin'][$sessionName][$idName], $_SESSION['config']['databasetype']);
		}
		elseif($_SESSION['history']['lcup'] == "true" && $mode == "up")
		{
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add($_SESSION['tablename'][$tableName], $_SESSION['m_admin'][$sessionName][$idName],"UP",_CYCLE_UPDATED." : ".$_SESSION['m_admin'][$sessionName][$idName], $_SESSION['config']['databasetype']);
		}
		unset($_SESSION['m_admin']);
		if($mode == "add")
		{
			$_SESSION['error'] =  _CYCLE_ADDED;
		}
		else
		{
			$_SESSION['error'] = _CYCLE_UPDATED;
		}
		header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&module=life_cycle&mode=list&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
	}
	exit();	
}

$state = true;

if($mode == "up")
{
	$cycle = CycleControler::get($idName);
	//var_dump($cycle);
	if(!isset($cycle))
	{
		$state = false;
	}
	else
	{
		$_SESSION['m_admin'][$sessionName] = $cycle->getArray();
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
	array_push($select[$_SESSION['tablename'][$tableName]], $idName, "cycle_label", "cycle_mode", "coll_id");
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
				if($tab[$i][$j][$value]=="cycle_label")
				{
					$tab[$i][$j]['value']=functions::show_string($tab[$i][$j]['value']);
					$tab[$i][$j]["path_template"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_CYCLE_LABEL;
					$tab[$i][$j]["size"]="55";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='cycle_label';
				}
				if($tab[$i][$j][$value]=="coll_id")
				{
					$tab[$i][$j]["device_label"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]= _COLL;
					$tab[$i][$j]["size"]="18";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='coll_id';
				}
				if($tab[$i][$j][$value]=="cycle_mode")
				{
					$tab[$i][$j]['value']=functions::show_string($tab[$i][$j]['value']);
					$tab[$i][$j]["path_template"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_CYCLE_MODE;
					$tab[$i][$j]["size"]="55";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='cycle_mode';
				}
			}
		}
	}
	$page_name = $pageName."&mode=list";
	$page_name_up = $pageName."&mode=up";
	$page_name_del = $pageName."&mode=del";
	$page_name_val= "";
	$page_name_ban = "";
	$page_name_add = $pageName."&mode=add";
	$label_add = _CYCLE_ADDITION;
	$_SESSION['m_admin']['init'] = true;
	$title = _CYCLES_LIST." : ".$i." "._CYCLES;
	$autoCompletionArray = array();
	$autoCompletionArray["list_script_url"] = $_SESSION['config']['businessappurl']."index.php?display=true&module=life_cycle&page=cycles_list_by_id";
	$autoCompletionArray["number_to_begin"] = 1;
}
elseif((!isset($idName) || empty($idName) || ! CycleControler::cycleExists($idName)) &&($mode == "del" ||$mode == "ban" || $mode == "allow"))
{
	$_SESSION['error'] = _CYCLE.' '._UNKNOWN;
}
elseif($mode == "ban")
{
	CycleControler::disable($idName);
}
elseif($mode == "allow")
{
	CycleControler::enable($idName);
}
elseif($mode == "del")
{
	CycleControler::delete($idName);
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
	include('cycles_management.php');
}
?>


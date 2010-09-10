<?php
core_tools::load_lang();

$mode = 'add';
if(isset($_REQUEST['mode']) && !empty($_REQUEST['mode']))
{
	$mode = $_REQUEST['mode'];
}

$page_labels = array('add' => _ADDITION, 'up' => _MODIFICATION, 'list' => _ACTION_LIST);
$page_ids = array('add' => 'action_add', 'up' => 'action_up', 'list' => 'action_list');

try{
	require_once("core/class/ActionControler.php");
	if($mode == 'list')
	{
		require_once("core/class/class_request.php");
		require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
	}
	
} catch (Exception $e){
	echo $e->getMessage();
}

function init_session()
{
	$_SESSION['m_admin']['action'] = array();
	$_SESSION['m_admin']['action']['ID'] = '';
	$_SESSION['m_admin']['action']['LABEL'] = '';
	$_SESSION['m_admin']['action']['ID_STATUS'] = '';
	$_SESSION['m_admin']['action']['ACTION_PAGE'] = '';
	$_SESSION['m_admin']['action']['HISTORY'] = 'Y';
}

if(isset($_REQUEST['id']) && !empty($_REQUEST['id']))
{
	$action_id = $_REQUEST['id'];
}

if(isset($_REQUEST['action_submit']))
{
	if($_REQUEST['mode'] == "up")
	{
		$_SESSION['m_admin']['action']['ID'] = functions::wash($action_id, "no", _ID." ");
	}
	$_SESSION['m_admin']['action']['LABEL'] = functions::wash($_REQUEST['label'], "no", _DESC." ", 'yes', 0, 255);
	if(empty($_REQUEST['action_page']))
	{
		$_SESSION['m_admin']['action']['ID_STATUS'] = functions::wash($_REQUEST['status'], "no", _STATUS." ",  'yes', 0, 10);
	}
	else
	{
		$_SESSION['m_admin']['action']['ID_STATUS'] = trim($_REQUEST['status']);
	}
	if(empty($_REQUEST['status']))
	{
		$_SESSION['m_admin']['action']['ACTION_PAGE'] = functions::wash($_REQUEST['action_page'], "no", _ACTION_PAGE." ", 'yes', 0, 255);
	}
	else
	{
		$_SESSION['m_admin']['action']['ACTION_PAGE'] = trim($_REQUEST['action_page']);
	}
	$_SESSION['m_admin']['action']['KEYWORD'] = '';
	$_SESSION['m_admin']['action']['FLAG_CREATE'] = 'N';
	if($_SESSION['m_admin']['action']['ACTION_PAGE'] <> "")
	{
		for($i=0; $i<count($_SESSION['actions_pages']);$i++)
		{
			if($_SESSION['m_admin']['action']['ACTION_PAGE'] == $_SESSION['actions_pages'][$i]['ID'])
			{
				$_SESSION['m_admin']['action']['KEYWORD'] = $_SESSION['actions_pages'][$i]['KEYWORD'];
				$_SESSION['m_admin']['action']['FLAG_CREATE'] = $_SESSION['actions_pages'][$i]['FLAG_CREATE'];
				break;
			}
		}
	}
	
	$_SESSION['m_admin']['action']['HISTORY'] = functions::wash($_REQUEST['history'], "no", _HISTORY." ");

	$_SESSION['m_admin']['action']['order'] = $_REQUEST['order'];
	$_SESSION['m_admin']['action']['order_field'] = $_REQUEST['order_field'];
	$_SESSION['m_admin']['action']['what'] = $_REQUEST['what'];
	$_SESSION['m_admin']['action']['start'] = $_REQUEST['start'];

	if($mode == "add" && ActionControler::actionExists($_SESSION['m_admin']['action']['ID']))
	{	
		$_SESSION['error'] = $_SESSION['m_admin']['action']['ID']." "._ALREADY_EXISTS."<br />";
	}
	if(!empty($_SESSION['error']))
	{
		if($mode == "up")
		{
			if(!empty($_SESSION['m_admin']['action']['ID']))
			{
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=action_management_controler&mode=up&id=".$_SESSION['m_admin']['action']['ID']."&admin=action");
				exit;
			}
			else
			{
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=action_management_controler&mode=list&admin=action&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit;
			}
		}
		elseif($mode == "add")
		{
			header("location: ".$_SESSION['config']['businessappurl']."index.php?page=action_management_controler&mode=add&admin=action");
			exit;
		}
	}
	else
	{
		$action_value = array('id' => functions::protect_string_db($_SESSION['m_admin']['action']['ID']) , 'label_action' => functions::protect_string_db($_SESSION['m_admin']['action']['LABEL']), 'keyword' => functions::protect_string_db($_SESSION['m_admin']['action']['KEYWORD']), 'create_id' => functions::protect_string_db($_SESSION['m_admin']['action']['FLAG_CREATE']), 'history' => functions::protect_string_db($_SESSION['m_admin']['action']['HISTORY']), 'action_page' => functions::protect_string_db($_SESSION['m_admin']['action']['ACTION_PAGE']), 'id_status' => functions::protect_string_db($_SESSION['m_admin']['action']['ID_STATUS']));
		
		$action = new Action;
		$action->setArray($action_value);
		
		ActionControler::save($action, $mode);
		
		if($_SESSION['history']['actionadd'] == "true" && $mode == "add")
		{
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$hist = new history();
			$hist->add($_SESSION['tablename']['actions'], $_SESSION['m_admin']['action']['ID'],"ADD",_ACTION_ADDED." : ".functions::protect_string_db($_SESSION['m_admin']['action']['ID']), $_SESSION['config']['databasetype']);
		}
		elseif($_SESSION['history']['actionup'] == "true" && $mode == "up")
		{
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$hist = new history();
			$hist->add($_SESSION['tablename']['actions'], $_SESSION['m_admin']['action']['ID'],"UP",_ACTION_MODIFIED." : ".functions::protect_string_db($_SESSION['m_admin']['action']['ID']), $_SESSION['config']['databasetype']);
		}
		unset($_SESSION['m_admin']);
		if($mode == "add")
		{
			$_SESSION['error'] =  _ACTION_ADDED;
		}
		else
		{
			$_SESSION['error'] = _ACTION_MODIFIED;
		}
						
		header("location: ".$_SESSION['config']['businessappurl']."index.php?page=action_management_controler&mode=list&admin=action&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
	}
	exit();	
}

$state = true;

if($mode == "up")
{
	$action = ActionControler::get($action_id );
	
	if(!isset($action))
	{
		$state = false;
	}
	else
	{
		$_SESSION['m_admin']['action']['ID'] = $action->__get('id');
		$_SESSION['m_admin']['action']['LABEL'] = functions::show_string($action->__get('label_action'));
		$_SESSION['m_admin']['action']['ID_STATUS'] = functions::show_string($action->__get('id_status'));
		$_SESSION['m_admin']['action']['IS_SYSTEM'] = functions::show_string($action->__get('is_system'));
		$_SESSION['m_admin']['action']['ACTION_PAGE'] = functions::show_string($action->__get('action_page'));
		$_SESSION['m_admin']['action']['HISTORY'] = functions::show_string($action->__get('history'));
		$_SESSION['m_admin']['action']['KEYWORD'] = functions::show_string($action->__get('keyword'));	
	}
}
elseif($mode == "add")
{
	if(!isset($_SESSION['m_admin']['action']))
	{
		init_session();
	}
}
elseif($mode == "list")
{
	$_SESSION['m_admin'] = array();
	init_session();
	
	$select[$_SESSION['tablename']['actions']] = array();
	array_push($select[$_SESSION['tablename']['actions']],"id", "label_action", 'is_system');
	$what = "";
	$where =" enabled = 'Y' ";
	if(isset($_REQUEST['what']) && !empty($_REQUEST['what']))
	{
		$what = functions::protect_string_db($_REQUEST['what']);
		if($_SESSION['config']['databasetype'] == "POSTGRESQL")
		{
			$where .= " and (label_action ilike '".functions::protect_string_db($what,$_SESSION['config']['databasetype'])."%'  ) ";
		}
		else
		{
			$where .= " and (label_action like '".functions::protect_string_db($what,$_SESSION['config']['databasetype'])."%'   ) ";
		}
	}
	$order = 'asc';
	if(isset($_REQUEST['order']) && !empty($_REQUEST['order']))
	{
		$order = trim($_REQUEST['order']);
	}
	$field = 'label_action';
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
				if($tab[$i][$j][$value]=="id")
				{
					$load = core_tools::is_action_defined($tab[$i][$j]['value']);

						$tab[$i][$j]["id"]=$tab[$i][$j]['value'];
						$tab[$i][$j]["label"]= _ID;
						$tab[$i][$j]["size"]="18";
						$tab[$i][$j]["label_align"]="left";
						$tab[$i][$j]["align"]="left";
						$tab[$i][$j]["valign"]="bottom";
						$tab[$i][$j]["show"]=true;
						$tab[$i][$j]["order"]='id';
				}
				if($tab[$i][$j][$value]=="label_action")
				{
					$tab[$i][$j]['value']=functions::show_string($tab[$i][$j]['value']);
					$tab[$i][$j]["label_action"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_DESC;
					$tab[$i][$j]["size"]="15";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='label_action';
				}
				if($tab[$i][$j][$value]=="is_system")
				{
					if($tab[$i][$j]['value'] == 'Y')
					{
						$tab[$i][$j]['value'] = _YES;
						array_push($tab[$i], array('column' => 'can_delete', 'value' => 'false', 'can_delete' => 'false',
						'label' => _DESC,'show' => false));
					}
					else
					{
						$tab[$i][$j]['value'] = _NO;
						array_push($tab[$i], array('column' => 'can_delete', 'value' => 'true', 'can_delete' => 'true',
						'label' => _DESC,'show' => false));
					}
					$tab[$i][$j]["is_system"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_IS_SYSTEM;
					$tab[$i][$j]["size"]="5";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='is_system';
				}
			}
		}
	}

	$page_name = "action_management_controler&mode=list";
	$page_name_up = "action_management_controler&mode=up";
	$page_name_del = "action_management_controler&mode=del";
	$page_name_val= "";
	$page_name_ban = "";
	$page_name_add = "action_management_controler&mode=add";
	$label_add = _ADD_ACTION;
	$_SESSION['m_admin']['init'] = true;
	$title = _ACTION_LIST." : ".count($tab)." "._ACTIONS;

	$autoCompletionArray = array();
	$autoCompletionArray["list_script_url"] = $_SESSION['config']['businessappurl']."index.php?display=true&admin=action&page=action_list_by_name";
	$autoCompletionArray["number_to_begin"] = 1;
}
elseif((!isset($action_id) || empty($action_id) || ! ActionControler::actionExists($action_id)) && $mode == "del" )
{
	$_SESSION['error'] = _ACTION.' '._UNKNOWN;
}
elseif($mode == "del")
{
	ActionControler::delete($action_id);
	$_SESSION['error'] = _ACTION_DELETED." ".$action_id;
	?><script type="text/javascript">window.top.location='<?php echo $_SESSION['config']['businessappurl']."index.php?page=action_management_controler&mode=list&admin=action&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;?>';</script>
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
	$page_path = $_SESSION['config']['businessappurl'].'index.php?page=action_management_controler&admin=action&mode='.$mode;
	$page_label = $page_labels[$mode];
	$page_id = $page_ids[$mode];
	core_tools::manage_location_bar($page_path, $page_label, $page_id, $init, $level);
	/***********************************************************/

	include('action_management.php');
}
?>

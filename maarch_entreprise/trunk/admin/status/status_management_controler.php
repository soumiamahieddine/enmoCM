<?php
core_tools::load_lang();

$mode = 'add';
if(isset($_REQUEST['mode']) && !empty($_REQUEST['mode']))
{
	$mode = $_REQUEST['mode'];
}

$page_labels = array('add' => _ADDITION, 'up' => _MODIFICATION, 'list' => _STATUS_LIST);
$page_ids = array('add' => 'status_add', 'up' => 'status_up', 'list' => 'status_list');

try{
	require_once("core/class/StatusControler.php");
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
	$_SESSION['m_admin']['status'] = array();
	$_SESSION['m_admin']['status']['ID'] = '';
	$_SESSION['m_admin']['status']['LABEL'] = '';
	$_SESSION['m_admin']['status']['IS_SYSTEM'] = 'N';
	$_SESSION['m_admin']['status']['IMG_FILENAME'] = '';
	$_SESSION['m_admin']['status']['MODULE'] = '';
	$_SESSION['m_admin']['status']['CAN_BE_SEARCHED'] = 'Y';
	$_SESSION['m_admin']['status']['CAN_BE_MODIFIED'] = 'Y';	
}

if(isset($_REQUEST['id']) && !empty($_REQUEST['id']))
{
	$status_id = $_REQUEST['id'];
}

if(isset($_REQUEST['status_submit']))
{
	$_SESSION['m_admin']['status']['ID'] = functions::wash($_REQUEST['status_id'], "no", _ID." ");
	$_SESSION['m_admin']['status']['LABEL'] = functions::wash($_REQUEST['label'], "no", _DESC." ", 'yes', 0, 50);
	$_SESSION['m_admin']['status']['IS_SYSTEM'] = functions::wash($_REQUEST['is_system'], "no", _IS_SYSTEM." ");
	$_SESSION['m_admin']['status']['IMG_FILENAME'] = '';
	$_SESSION['m_admin']['status']['MODULE'] = 'apps';

	$_SESSION['m_admin']['status']['CAN_BE_SEARCHED'] = functions::wash($_REQUEST['can_be_searched'], "no", CAN_BE_SEARCHED." ");
	$_SESSION['m_admin']['status']['CAN_BE_MODIFIED'] = functions::wash($_REQUEST['can_be_modified'], "no", _CAN_BE_MODIFIED." ");
	
	$_SESSION['m_admin']['status']['order'] = $_REQUEST['order'];
	$_SESSION['m_admin']['status']['order_field'] = $_REQUEST['order_field'];
	$_SESSION['m_admin']['status']['what'] = $_REQUEST['what'];
	$_SESSION['m_admin']['status']['start'] = $_REQUEST['start'];

	if($mode == "add" && StatusControler::statusExists($_SESSION['m_admin']['status']['ID']))
	{	
		$_SESSION['error'] = $_SESSION['m_admin']['status']['ID']." "._ALREADY_EXISTS."<br />";
	}
	if(!empty($_SESSION['error']))
	{
		if($mode == "up")
		{
			if(!empty($_SESSION['m_admin']['status']['ID']))
			{
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=status_management_controler&mode=up&id=".$_SESSION['m_admin']['status']['ID']."&admin=status");
				exit;
			}
			else
			{
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=status_management_controler&mode=list&admin=status&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit;
			}
		}
		elseif($mode == "add")
		{
			header("location: ".$_SESSION['config']['businessappurl']."index.php?page=status_management_controler&mode=add&admin=status");
			exit;
		}
	}
	else
	{
		$status_value = array('id' => functions::protect_string_db($_SESSION['m_admin']['status']['ID']) , 'label_status' => functions::protect_string_db($_SESSION['m_admin']['status']['LABEL']), 'is_system' => functions::protect_string_db($_SESSION['m_admin']['status']['IS_SYSTEM']),'maarch_module' => functions::protect_string_db($_SESSION['m_admin']['status']['MODULE']),'can_be_searched' => functions::protect_string_db($_SESSION['m_admin']['status']['CAN_BE_SEARCHED']), 'can_be_modified' => functions::protect_string_db($_SESSION['m_admin']['status']['CAN_BE_MODIFIED']));
		
		$status = new Status;
		$status->setArray($status_value);
		
		StatusControler::save($status, $mode);
		
		if($_SESSION['history']['statusadd'] == "true" && $mode == "add")
		{
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$hist = new history();
			$hist->add($_SESSION['tablename']['status'], $_SESSION['m_admin']['status']['ID'],"ADD",_STATUS_ADDED." : ".functions::protect_string_db($_SESSION['m_admin']['status']['ID']), $_SESSION['config']['databasetype']);
		}
		elseif($_SESSION['history']['statusup'] == "true" && $mode == "up")
		{
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$hist = new history();
			$hist->add($_SESSION['tablename']['status'], $_SESSION['m_admin']['status']['ID'],"UP",_STATUS_MODIFIED." : ".functions::protect_string_db($_SESSION['m_admin']['status']['ID']), $_SESSION['config']['databasetype']);
		}
		unset($_SESSION['m_admin']);
		if($mode == "add")
		{
			$_SESSION['error'] =  _STATUS_ADDED;
		}
		else
		{
			$_SESSION['error'] = _STATUS_MODIFIED;
		}
						
		header("location: ".$_SESSION['config']['businessappurl']."index.php?page=status_management_controler&mode=list&admin=status&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
	}
	exit();	
}

$state = true;

if($mode == "up")
{
	$status = StatusControler::get($status_id );
	
	if(!isset($status))
	{
		$state = false;
	}
	else
	{
		$_SESSION['m_admin']['status']['ID'] = $status->__get('id');
		$_SESSION['m_admin']['status']['LABEL'] = functions::show_string($status->__get('label_status'));
		$_SESSION['m_admin']['status']['IS_SYSTEM'] = functions::show_string($status->__get('is_system'));
		$_SESSION['m_admin']['status']['IMG_FILENAME'] =functions::show_string($status->__get('img_filename'));
		$_SESSION['m_admin']['status']['MODULE'] = functions::show_string($status->__get('maarch_module'));
		$_SESSION['m_admin']['status']['CAN_BE_SEARCHED'] = functions::show_string($status->__get('can_be_searched'));
		$_SESSION['m_admin']['status']['CAN_BE_MODIFIED'] = functions::show_string($status->__get('can_be_modified'));		
	}
}
elseif($mode == "add")
{
	if(!isset($_SESSION['m_admin']['status']))
	{
		init_session();
	}
}
elseif($mode == "list")
{
	$_SESSION['m_admin'] = array();
	init_session();
	
	$select[$_SESSION['tablename']['status']] = array();
	array_push($select[$_SESSION['tablename']['status']],"id", "label_status","is_system","can_be_searched");
	$what = "";
	$where = "";
	$what = functions::protect_string_db($_REQUEST['what']);
	if($_SESSION['config']['databasetype'] == "POSTGRESQL")
	{
		$where .= " (label_status ilike '".functions::protect_string_db($what,$_SESSION['config']['databasetype'])."%'  or id ilike '".functions::protect_string_db($what,$_SESSION['config']['databasetype'])."%' ) ";
	}
	else
	{
		$where .= " (label_status like '".functions::protect_string_db($what,$_SESSION['config']['databasetype'])."%'  or id like '".functions::protect_string_db($what,$_SESSION['config']['databasetype'])."%' ) ";
	}

	$order = 'asc';
	if(isset($_REQUEST['order']) && !empty($_REQUEST['order']))
	{
		$order = trim($_REQUEST['order']);
	}
	$field = 'label_status';
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
				$tab[$i][$j]["id"]=$tab[$i][$j]['value'];
				$tab[$i][$j]["label"]= _ID;
				$tab[$i][$j]["size"]="18";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='id';
			}
			if($tab[$i][$j][$value]=="label_status")
			{
				$tab[$i][$j]['value']=functions::show_string($tab[$i][$j]['value']);
				$tab[$i][$j]["label_status"]=$tab[$i][$j]['value'];
				$tab[$i][$j]["label"]=_DESC;
				$tab[$i][$j]["size"]="15";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='label_status';
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
			if($tab[$i][$j][$value]=="can_be_searched")
			{
				if($tab[$i][$j]['value'] == 'Y')
				{
					$tab[$i][$j]['value'] = _YES;
				}
				else
				{
					$tab[$i][$j]['value'] = _NO;
				}
				$tab[$i][$j]["can_be_searched"]= $tab[$i][$j]['value'];
				$tab[$i][$j]["label"]=_CAN_BE_SEARCHED;
				$tab[$i][$j]["size"]="5";
				$tab[$i][$j]["label_align"]="center";
				$tab[$i][$j]["align"]="center";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='can_be_searched';
			}
		}
		}
	}

	$page_name = "status_management_controler&mode=list";
	$page_name_up = "status_management_controler&mode=up";
	$page_name_del = "status_management_controler&mode=del";
	$page_name_val= "";
	$page_name_ban = "";
	$page_name_add = "status_management_controler&mode=add";
	$label_add = _ADD_STATUS;
	$_SESSION['m_admin']['init'] = true;
	$title = _STATUS_LIST." : ".$i." "._STATUS_PLUR;
	$autoCompletionArray = array();
	$autoCompletionArray["list_script_url"] = $_SESSION['config']['businessappurl']."index.php?display=true&admin=status&page=status_list_by_name";
	$autoCompletionArray["number_to_begin"] = 1;
}
elseif((!isset($status_id) || empty($status_id) || ! StatusControler::statusExists($status_id)) && $mode == "del" )
{
	$_SESSION['error'] = _STATUS.' '._UNKNOWN;
}
elseif($mode == "del")
{
	StatusControler::delete($status_id);
	$_SESSION['error'] = _STATUS_DELETED." ".$status_id;
	?><script type="text/javascript">window.top.location='<?php echo $_SESSION['config']['businessappurl']."index.php?page=status_management_controler&mode=list&admin=status&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;?>';</script>
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
	$page_path = $_SESSION['config']['businessappurl'].'index.php?page=status_management_controler&admin=status&mode='.$mode;
	$page_label = $page_labels[$mode];
	$page_id = $page_ids[$mode];
	core_tools::manage_location_bar($page_path, $page_label, $page_id, $init, $level);
	/***********************************************************/

	include('status_management.php');
}
?>

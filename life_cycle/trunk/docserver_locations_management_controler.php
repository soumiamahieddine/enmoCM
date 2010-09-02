<?php
//lgi +
$sessionName = "docserver_locations";
$pageName = "docserver_locations_management_controler";
$tableName = "docserver_locations";
$idName = "docserver_locations_id";

$mode = 'add';

/*echo "<pre>";
print_r($_REQUEST);
echo "</pre>";*/

core_tools::load_lang(); // NOTE : core_tools is not a static class

if(isset($_REQUEST['mode']) && !empty($_REQUEST['mode'])){
	$mode = $_REQUEST['mode'];
} else {
	$mode = 'list'; 
}

try{
	require_once("modules/life_cycle/class/docserver_locations_controler.php");
	require_once("core/class/class_request.php");
	// TODO : replace
	if($mode == 'list'){
		require_once("modules/life_cycle/lang/fr.php");
		require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
	}
} catch (Exception $e){
	echo $e->getMessage();
}

if(isset($_REQUEST['submit'])){
	// Action to do with db
	validate_cs_submit($mode);
} else {
	// Display to do
	if(isset($_REQUEST['id']) && !empty($_REQUEST['id']))
		$docserver_locations_id = $_REQUEST['id'];
	$state = true;
	switch ($mode) {
		case "up" :
			$state=display_up($docserver_locations_id); 
			location_bar_management($mode);
			break;
		case "add" :
			display_add(); 
			location_bar_management($mode);
			break;
		case "del" :
			display_del($docserver_locations_id); 
			break;
		case "list" :
			$docserver_locations_list=display_list(); 
			location_bar_management($mode);
			break;
		case "allow" :
			display_enable($docserver_locations_id); 
			location_bar_management($mode);
		case "ban" :
			display_disable($docserver_locations_id); 
			location_bar_management($mode);
	}
	include('docserver_locations_management.php');
}

// END of main block

/////// PRIVATE BLOCK

/**
 * Initialize session variables
 */
function init_session(){
	$sessionName = "docserver_locations";
	$_SESSION['m_admin'][$sessionName] = array();
}

/**
 * Management of the location bar  
 */
function location_bar_management($mode){
	$sessionName = "docserver_locations";
	$pageName = "docserver_locations_management_controler";
	$tableName = "docserver_locations";
	$idName = "docserver_locations_id";
	
	$page_labels = array('add' => _ADDITION, 'up' => _MODIFICATION, 'list' => _DOCSERVER_LOCATIONS_LIST);
	$page_ids = array('add' => 'docserver_add', 'up' => 'docserver_up', 'list' => 'docserver_locations_list');

	$init = false;
	if($_REQUEST['reinit'] == "true") 
		$init = true;

	$level = "";
	if($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1) 
		$level = $_REQUEST['level'];
	
	$page_path = $_SESSION['config']['businessappurl'].'index.php?page='.$pageName.'&module=life_cycle&mode='.$mode;
	$page_label = $page_labels[$mode];
	$page_id = $page_ids[$mode];
	$ct=new core_tools();
	$ct->manage_location_bar($page_path, $page_label, $page_id, $init, $level);

}

/**
 * Validate a submit (add or up),
 * up to saving object
 */
function validate_cs_submit($mode){
	$sessionName = "docserver_locations";
	$pageName = "docserver_locations_management_controler";
	$tableName = "docserver_locations";
	$idName = "docserver_locations_id";
	
	$f=new functions();

	$docserver_locations = new docserver_locations();
	//$f->show_array($_REQUEST);exit;
	if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
		// Update, so values exist
		$docserver_locations->docserver_locations_id=$f->protect_string_db($f->wash($_REQUEST['id'], "nick", _THE_DOCSERVER_LOCATION_ID." ", "yes", 0, 32));
	}
	$docserver_locations->ipv4=$f->protect_string_db($f->wash($_REQUEST['ipv4'], "no", _IPV4." ", 'yes', 0, 255));
	$docserver_locations->ipv6=$f->protect_string_db($f->wash($_REQUEST['ipv6'], "no", _IPV6." ", 'no', 0, 255));
	$docserver_locations->net_domain=$f->protect_string_db($f->wash($_REQUEST['net_domain'], "no", _NET_DOMAIN." ", 'no', 0, 32));
	$docserver_locations->mask=$f->protect_string_db($f->wash($_REQUEST['mask'], "no", _MASK." ", 'no', 0, 255));

	$status= array();
	$status['order']=$_REQUEST['order'];
	$status['order_field']=$_REQUEST['order_field'];
	$status['what']=$_REQUEST['what'];
	$status['start']=$_REQUEST['start'];
	
	if($mode == "add" && docserver_locations_controler::docserverLocationsExists($docserver_locations->docserver_locations_id)){	
		$_SESSION['error'] = $docserver_locations->docserver_locations_id." "._ALREADY_EXISTS."<br />";
	}
	
	if(!empty($_SESSION['error'])) {
		// Error management depending of mode
		put_in_session("status",$status);
		put_in_session("docserver_locations",$docserver_locations->getArray());
		
		switch ($mode) {
			case "up":
				if(!empty($_REQUEST['id'])) {
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=up&id=".$_REQUEST['id']."&module=life_cycle");
				} else {
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=list&module=life_cycle&order=".$status['order']."&order_field=".$status['order_field']."&start=".$status['start']."&what=".$status['what']);
				}
				exit;
			case "add":
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=add&module=life_cycle");
				exit;
		}
	} else {
		// Saving given object
		//$f->show_array($docserver_locations);
		$docserver_locations=docserver_locations_controler::save($docserver_locations);
		//history
		if($_SESSION['history']['docserver_locationsadd'] == "true" && $mode == "add"){
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add(_DOCSERVER_LOCATIONS_TABLE_NAME, $_REQUEST['id'], "ADD",_DOCSERVER_LOCATION_ADDED." : ".$_REQUEST['id'], $_SESSION['config']['databasetype']);
		} elseif($_SESSION['history']['docserver_locationsadd'] == "true" && $mode == "up"){
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add(_DOCSERVER_LOCATIONS_TABLE_NAME, $_REQUEST['id'], "UP",_DOCSERVER_LOCATION_UPDATED." : ".$_REQUEST['id'], $_SESSION['config']['databasetype']);
		}
		if($mode == "add")
			$_SESSION['error'] =  _DOCSERVER_LOCATION_ADDED;
		 else
			$_SESSION['error'] = _DOCSERVER_LOCATION_UPDATED;
		unset($_SESSION['m_admin']);
		header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=list&module=life_cycle&order=".$status['order']."&order_field=".$status['order_field']."&start=".$status['start']."&what=".$status['what']);
	}
}

/**
 * Initialize session parameters for update display
 * @param Long $docserver_locations_id
 */
function display_up($docserver_locations_id){
	$state=true;
	$docserver_locations = docserver_locations_controler::get($docserver_locations_id);
	if(empty($docserver_locations))
		$state = false; 
	else
		put_in_session("docserver_locations", $docserver_locations->getArray()); 
	
	return $state;
}

/**
 * Initialize session parameters for add display with given docserver
 */
function display_add(){
	$sessionName = "docserver_locations";
	if(!isset($_SESSION['m_admin'][$sessionName]))
		init_session();
}

/**
 * Initialize session parameters for list display
 */
function display_list(){
	$sessionName = "docserver_locations";
	$pageName = "docserver_locations_management_controler";
	$tableName = "docserver_locations";
	$idName = "docserver_locations_id";
	
	$_SESSION['m_admin'] = array();
	
	init_session();
	
	$select[_DOCSERVER_LOCATIONS_TABLE_NAME] = array();
	array_push($select[_DOCSERVER_LOCATIONS_TABLE_NAME], $idName, "ipv4", "ipv6", "net_domain", "enabled");
	$what = "";
	$where ="";
	if(isset($_REQUEST['what']) && !empty($_REQUEST['what'])){
		$what = functions::protect_string_db($_REQUEST['what']);
		if($_SESSION['config']['databasetype'] == "POSTGRESQL"){
			$where = $idName." ilike '".strtoupper($what)."%' ";
		} else {
			$where = $idName." like '".strtoupper($what)."%' ";
		}
	}

	// Checking order and order_field values
	$order = 'asc';
	if(isset($_REQUEST['order']) && !empty($_REQUEST['order'])){
		$order = trim($_REQUEST['order']);
	}
	$field = $idName;
	if(isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field'])){
		$field = trim($_REQUEST['order_field']);
	}
	$orderstr = list_show::define_order($order, $field);
	$request = new request();
	$tab=$request->select($select,$where,$orderstr,$_SESSION['config']['databasetype']);
	for ($i=0;$i<count($tab);$i++) {
		foreach($tab[$i] as &$item) {
			switch ($item['column']){
				case $idName:
					format_item($item,_ID,"18","left","left","bottom",true); break;
				case "ipv4":
					format_item($item,_IPV4,"15","left","left","bottom",true); break;
				case "ipv6":
					format_item($item,_IPV6,"15","left","left","bottom",true); break;
				case "net_domain":
					format_item($item,_NET_DOMAIN,"15","left","left","bottom",true); break;
				case "enabled":
					format_item($item,_ENABLED,"5","left","left","bottom",true); break;
			}
		}
			
	}
	/*
	 * TODO Pour éviter les actions suivantes, il y a 2 solutions :
	 * - La plus propre : créer un objet "PageList"
	 * - La plus locale : si cela ne sert que pour admin_list dans docserver_management.php,
	 *                    il est possible d'en construire directement la string et de la récupérer en return.
	 */  
	$result = array();
	$result['tab']=$tab;
	$result['what']=$what;
	$result['page_name'] = $pageName."&mode=list";
	$result['page_name_up'] = $pageName."&mode=up";
	$result['page_name_del'] = $pageName."&mode=del";
	$result['page_name_val']= $pageName."&mode=allow";
	$result['page_name_ban'] = $pageName."&mode=ban";
	$result['page_name_add'] = $pageName."&mode=add";
	$result['label_add'] = _DOCSERVER_LOCATION_ADDITION;
	$_SESSION['m_admin']['init'] = true;
	$result['title'] = _DOCSERVER_LOCATIONS_LIST." : ".count($tab)." "._DOCSERVER_LOCATIONS;
	$result['autoCompletionArray'] = array();
	$result['autoCompletionArray']["list_script_url"] = $_SESSION['config']['businessappurl']."index.php?display=true&module=life_cycle&page=docserver_locations_list_by_id";
	$result['autoCompletionArray']["number_to_begin"] = 1;
	return $result;
}

/**
 * Delete given docserver if exists and initialize session parameters
 * @param unknown_type $docserver_locations_id
 */
function display_del($docserver_locations_id){
	$docserver_locations = docserver_locations_controler::get($docserver_locations_id);
	if(isset($docserver_locations)){
		// Deletion
		docserver_locations_controler::delete($docserver_locations);
		$_SESSION['error'] = _DOCSERVER_LOCATION_DELETED." ".$docserver_locations_id;
		if($_SESSION['history']['docserver_locationsdel'] == "true"){
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add(_DOCSERVER_LOCATIONS_TABLE_NAME, $docserver_locations_id, "DEL", _DOCSERVER_LOCATION_DELETED." : ".$docserver_locations_id, $_SESSION['config']['databasetype']);
		}
		// NOTE: Why not calling display_list ?
		$pageName = "docserver_locations_management_controler";
		?><script>window.top.location='<?php echo $_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=list&module=life_cycle&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;?>';</script>
		<?php
		exit;
	} 
	else{
		// Error management
		$_SESSION['error'] = _DOCSERVER_LOCATION.' '._UNKNOWN;
	}
}

/**
 * allow given docserver if exists
 * @param unknown_type $docserver_locations_id
 */
function display_enable($docserver_locations_id){
	$docserver_locations = docserver_locations_controler::get($docserver_locations_id);
	if(isset($docserver_locations)){
		// Disable
		docserver_locations_controler::enable($docserver_locations);
		$_SESSION['error'] = _DOCSERVER_LOCATION_ENABLED." ".$docserver_locations_id;
		if($_SESSION['history']['docserver_locationsallow'] == "true"){
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add(_DOCSERVER_LOCATIONS_TABLE_NAME, $docserver_locations_id, "VAL",_DOCSERVER_LOCATION_ENABLED." : ".$docserver_locations_id, $_SESSION['config']['databasetype']);
		}
		// NOTE: Why not calling display_list ?
		$pageName = "docserver_locations_management_controler";
		?><script>window.top.location='<?php echo $_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=list&module=life_cycle&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;?>';</script>
		<?php
		exit;
	}
	else{
		// Error management
		$_SESSION['error'] = _DOCSERVER_LOCATION.' '._UNKNOWN;
	}
}

/**
 * ban given docserver if exists
 * @param unknown_type $docserver_locations_id
 */
function display_disable($docserver_locations_id){
	$docserver_locations = docserver_locations_controler::get($docserver_locations_id);
	if(isset($docserver_locations)){
		// Disable
		docserver_locations_controler::disable($docserver_locations);
		$_SESSION['error'] = _DOCSERVER_LOCATION_DISABLED." ".$docserver_locations_id;
		if($_SESSION['history']['docserver_locationsban'] == "true"){
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add(_DOCSERVER_LOCATIONS_TABLE_NAME, $docserver_locations_id, "BAN", _DOCSERVER_LOCATION_DISABLED." : ".$docserver_locations_id, $_SESSION['config']['databasetype']);
		}
		// NOTE: Why not calling display_list ?
		$pageName = "docserver_locations_management_controler";
		?><script>window.top.location='<?php echo $_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=list&module=life_cycle&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;?>';</script>
		<?php
		exit;
	} 
	else{
		// Error management
		$_SESSION['error'] = _DOCSERVER_LOCATION.' '._UNKNOWN;
	}
}

/**
 * Format given item with given values, according with HTML formating.
 * NOTE: given item needs to be an array with at least 2 keys: 
 * 'column' and 'value'.
 * NOTE: given item is modified consequently.  
 * @param $item
 * @param $label
 * @param $size
 * @param $label_align
 * @param $align
 * @param $valign
 * @param $show
 */
function format_item(&$item,$label,$size,$label_align,$align,$valign,$show){
	$item['value']=functions::show_string($item['value']);	
	$item[$item['column']]=$item['value'];
	$item["label"]=$label;
	$item["size"]=$size;
	$item["label_align"]=$label_align;
	$item["align"]=$align;
	$item["valign"]=$valign;
	$item["show"]=$show;
	$item["order"]=$item['column'];	
}

/**
 * Put given object in session, according with given type
 * NOTE: given object needs to be at least hashable
 * @param string $type
 * @param hashable $hashable
 */
function put_in_session($type,$hashable){
	foreach($hashable as $key=>$value){
		// echo "Key: $key Value: $value f:".functions::show_string($value)." // ";
		$_SESSION['m_admin'][$type][$key]=functions::show_string($value);
	}
}

?>

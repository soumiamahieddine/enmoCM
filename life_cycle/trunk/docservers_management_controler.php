<?php
//lgi +
$sessionName = "docservers";
$pageName = "docservers_management_controler";
$tableName = "docservers";
$idName = "docservers_id";

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
	require_once("modules/life_cycle/class/docservers_controler.php");
	require_once("core/class/class_request.php");
	// TODO : replace
	require_once("modules".DIRECTORY_SEPARATOR."life_cycle".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."DocserverLocationControler.php");
	if($mode == 'list'){
		require_once("modules/life_cycle/lang/fr.php");
		require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
	}
} catch (Exception $e){
	echo $e->getMessage();
}

if($mode == "up" || $mode =="add"){
	$docserverLocationsArray = array();
	$docserverLocationsArray = DocserverLocationControler::getAllId();
	$docserverTypesArray = array();
	$docserverTypesArray = DocserverLocationControler::getAllDocserverTypes();
}


if(isset($_REQUEST['submit'])){
	// Action to do with db
	validate_cs_submit($mode);
} else {
	// Display to do
	if(isset($_REQUEST['id']) && !empty($_REQUEST['id']))
		$docservers_id = $_REQUEST['id'];
	$state = true;
	switch ($mode) {
		case "up" :
			$state=display_up($docservers_id); 
			location_bar_management($mode);
			break;
		case "add" :
			display_add(); 
			location_bar_management($mode);
			break;
		case "del" :
			display_del($docservers_id); 
			break;
		case "list" :
			$docservers_list=display_list(); 
			location_bar_management($mode);
			break;
		case "allow" :
			display_enable($docservers_id); 
			location_bar_management($mode);
		case "ban" :
			display_disable($docservers_id); 
			location_bar_management($mode);
	}
	include('docservers_management.php');
}

// END of main block

/////// PRIVATE BLOCK

/**
 * Initialize session variables
 */
function init_session(){
	$sessionName = "docservers";
	$_SESSION['m_admin'][$sessionName] = array();
}

/**
 * Management of the location bar  
 */
function location_bar_management($mode){
	$sessionName = "docservers";
	$pageName = "docservers_management_controler";
	$tableName = "docservers";
	$idName = "docservers_id";
	
	$page_labels = array('add' => _ADDITION, 'up' => _MODIFICATION, 'list' => _DOCSERVERS_LIST);
	$page_ids = array('add' => 'docserver_add', 'up' => 'docserver_up', 'list' => 'docservers_list');

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
	$sessionName = "docservers";
	$pageName = "docservers_management_controler";
	$tableName = "docservers";
	$idName = "docservers_id";
	
	$f=new functions();

	$docservers = new docservers();
	//$f->show_array($_REQUEST);exit;
	if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
		// Update, so values exist
		$docservers->docservers_id=$f->protect_string_db($f->wash($_REQUEST['id'], "nick", _THE_DOCSERVER_ID." ", "yes", 0, 32));
	}
	$docservers->docserver_types_id=$f->protect_string_db($f->wash($_REQUEST['docserver_types_id'], "no", _DOCSERVER_TYPES." ", 'yes', 0, 32));
	$docservers->device_label=$f->protect_string_db($f->wash($_REQUEST['device_label'], "no", _DEVICE_LABEL." ", 'yes', 0, 255));
	$docservers->is_readonly=$f->protect_string_db($f->wash($_REQUEST['is_readonly'], "no", _IS_READONLY." ", 'yes', 0, 5));
	if($docservers->is_readonly == "false"){
		$docservers->is_readonly=false;
	} else {
		$docservers->is_readonly=true;
	}
	if(isset($_REQUEST['size_limit_hidden']) && !empty($_REQUEST['size_limit_hidden'])){
		$docservers->size_limit_number=$f->protect_string_db($f->wash($_REQUEST['size_limit_hidden'], "no", _SIZE_LIMIT." ", 'yes', 0, 255));
	}
	$docservers->path_template=$f->protect_string_db($f->wash($_REQUEST['path_template'], "no", _PATH_TEMPLATE." ", 'yes', 0, 255));
	if(!is_dir($docservers->path_template)){
		$_SESSION['error'] .= _PATH_OF_DOCSERVER_UNAPPROACHABLE."<br>";
	} else{
		$Fnm = $docservers->path_template."test_docserver.txt";
		$isWriteable = true;
		if($inF = fopen($Fnm,"a")){
			fwrite($inF,"test");
			if(file_exists($Fnm)){
				unlink($Fnm);
			} else{
				$isWriteable = false;
			}
			fclose($inF);
		} else{
			$isWriteable = false;
		}
		if(!$isWriteable){
			$_SESSION['error'] .= _THE_DOCSERVER_DOES_NOT_HAVE_THE_ADEQUATE_RIGHTS;
		}
	}

	$docservers->coll_id=$f->protect_string_db($f->wash($_REQUEST['coll_id'], "no", _COLLECTION." ", 'yes', 0, 32));
	$docservers->priority_number=$f->protect_string_db($f->wash($_REQUEST['priority_number'], "num", _PRIORITY." ", 'yes', 0, 6));
	$docservers->docserver_locations_id=$f->protect_string_db($f->wash($_REQUEST['docserver_locations_id'], "no", _DOCSERVER_LOCATIONS." ", 'yes', 0, 32));
	$docservers->adr_priority_number=$f->protect_string_db($f->wash($_REQUEST['adr_priority_number'], "num", _ADR_PRIORITY." ", 'yes', 0, 6));
	$status= array();
	$status['order']=$_REQUEST['order'];
	$status['order_field']=$_REQUEST['order_field'];
	$status['what']=$_REQUEST['what'];
	$status['start']=$_REQUEST['start'];
	
	if($mode == "add" && docservers_controler::docserverExists($docservers->docservers_id)){	
		$_SESSION['error'] = $docservers->docservers_id." "._ALREADY_EXISTS."<br />";
	}
	
	if(!empty($_SESSION['error'])) {
		// Error management depending of mode
		put_in_session("status",$status);
		put_in_session("docservers",$docservers->getArray());
		
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
		//$f->show_array($docservers);
		$docservers=docservers_controler::save($docservers);
		//history
		if($_SESSION['history']['docserversadd'] == "true" && $mode == "add"){
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add(_DOCSERVERS_TABLE_NAME, $_REQUEST['id'], "ADD",_DOCSERVER_ADDED." : ".$_REQUEST['id'], $_SESSION['config']['databasetype']);
		} elseif($_SESSION['history']['docserversadd'] == "true" && $mode == "up"){
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add(_DOCSERVERS_TABLE_NAME, $_REQUEST['id'], "UP",_DOCSERVER_UPDATED." : ".$_REQUEST['id'], $_SESSION['config']['databasetype']);
		}
		if($mode == "add")
			$_SESSION['error'] =  _DOCSERVER_ADDED;
		 else
			$_SESSION['error'] = _DOCSERVER_UPDATED;
		unset($_SESSION['m_admin']);
		header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=list&module=life_cycle&order=".$status['order']."&order_field=".$status['order_field']."&start=".$status['start']."&what=".$status['what']);
	}
}

/**
 * Initialize session parameters for update display
 * @param Long $docservers_id
 */
function display_up($docservers_id){
	$state=true;
	$docservers = docservers_controler::get($docservers_id);
	if(empty($docservers))
		$state = false; 
	else
		put_in_session("docservers", $docservers->getArray()); 
	
	return $state;
}

/**
 * Initialize session parameters for add display with given docserver
 */
function display_add(){
	$sessionName = "docservers";
	if(!isset($_SESSION['m_admin'][$sessionName]))
		init_session();
}

/**
 * Initialize session parameters for list display
 */
function display_list(){
	$sessionName = "docservers";
	$pageName = "docservers_management_controler";
	$tableName = "docservers";
	$idName = "docservers_id";
	
	$_SESSION['m_admin'] = array();
	
	init_session();
	
	$select[_DOCSERVERS_TABLE_NAME] = array();
	array_push($select[_DOCSERVERS_TABLE_NAME], $idName, "device_label", "docserver_types_id", "coll_id", "enabled");
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
				case "device_label":
					format_item($item,_DEVICE_LABEL,"15","left","left","bottom",true); break;
				case "docserver_types_id":
					format_item($item,_DOCSERVER_TYPE,"15","left","left","bottom",true); break;
				case "coll_id":
					format_item($item,_COLL_ID,"15","left","left","bottom",true); break;
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
	$result['label_add'] = _DOCSERVER_ADDITION;
	$_SESSION['m_admin']['init'] = true;
	$result['title'] = _DOCSERVERS_LIST." : ".count($tab)." "._DOCSERVERS;
	$result['autoCompletionArray'] = array();
	$result['autoCompletionArray']["list_script_url"] = $_SESSION['config']['businessappurl']."index.php?display=true&module=life_cycle&page=docservers_list_by_id";
	$result['autoCompletionArray']["number_to_begin"] = 1;
	return $result;
}

/**
 * Delete given docserver if exists and initialize session parameters
 * @param unknown_type $docservers_id
 */
function display_del($docservers_id){
	$docservers = docservers_controler::get($docservers_id);
	if(isset($docservers)){
		// Deletion
		docservers_controler::delete($docservers);
		$_SESSION['error'] = _DOCSERVER_DELETED." ".$docservers_id;
		if($_SESSION['history']['docserversdel'] == "true"){
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add(_DOCSERVERS_TABLE_NAME, $docservers_id, "DEL", _DOCSERVER_DELETED." : ".$docservers_id, $_SESSION['config']['databasetype']);
		}
		// NOTE: Why not calling display_list ?
		$pageName = "docservers_management_controler";
		?><script>window.top.location='<?php echo $_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=list&module=life_cycle&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;?>';</script>
		<?php
		exit;
	} 
	else{
		// Error management
		$_SESSION['error'] = _DOCSERVER.' '._UNKNOWN;
	}
}

/**
 * allow given docserver if exists
 * @param unknown_type $docservers_id
 */
function display_enable($docservers_id){
	$docservers = docservers_controler::get($docservers_id);
	if(isset($docservers)){
		// Disable
		docservers_controler::enable($docservers);
		$_SESSION['error'] = _DOCSERVER_ENABLED." ".$docservers_id;
		if($_SESSION['history']['docserversallow'] == "true"){
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add(_DOCSERVERS_TABLE_NAME, $docservers_id, "VAL",_DOCSERVER_ENABLED." : ".$docservers_id, $_SESSION['config']['databasetype']);
		}
		// NOTE: Why not calling display_list ?
		$pageName = "docservers_management_controler";
		?><script>window.top.location='<?php echo $_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=list&module=life_cycle&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;?>';</script>
		<?php
		exit;
	}
	else{
		// Error management
		$_SESSION['error'] = _DOCSERVER.' '._UNKNOWN;
	}
}

/**
 * ban given docserver if exists
 * @param unknown_type $docservers_id
 */
function display_disable($docservers_id){
	$docservers = docservers_controler::get($docservers_id);
	if(isset($docservers)){
		// Disable
		docservers_controler::disable($docservers);
		$_SESSION['error'] = _DOCSERVER_DISABLED." ".$docservers_id;
		if($_SESSION['history']['docserversban'] == "true"){
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add(_DOCSERVERS_TABLE_NAME, $docservers_id, "BAN", _DOCSERVER_DISABLED." : ".$docservers_id, $_SESSION['config']['databasetype']);
		}
		// NOTE: Why not calling display_list ?
		$pageName = "docservers_management_controler";
		?><script>window.top.location='<?php echo $_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=list&module=life_cycle&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;?>';</script>
		<?php
		exit;
	} 
	else{
		// Error management
		$_SESSION['error'] = _DOCSERVER.' '._UNKNOWN;
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

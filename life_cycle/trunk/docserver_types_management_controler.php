<?php
//lgi +
$sessionName = "docserver_types";
$pageName = "docserver_types_management_controler";
$tableName = "docserver_types";
$idName = "docserver_types_id";

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
	require_once("modules/life_cycle/class/docserver_types_controler.php");
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
		$docserver_types_id = $_REQUEST['id'];
	$state = true;
	switch ($mode) {
		case "up" :
			$state=display_up($docserver_types_id); 
			location_bar_management($mode);
			break;
		case "add" :
			display_add(); 
			location_bar_management($mode);
			break;
		case "del" :
			display_del($docserver_types_id); 
			break;
		case "list" :
			$docserver_types_list=display_list(); 
			location_bar_management($mode);
			break;
		case "allow" :
			display_enable($docserver_types_id); 
			location_bar_management($mode);
		case "ban" :
			display_disable($docserver_types_id); 
			location_bar_management($mode);
	}
	include('docserver_types_management.php');
}

// END of main block

/////// PRIVATE BLOCK

/**
 * Initialize session variables
 */
function init_session(){
	$sessionName = "docserver_types";
	$_SESSION['m_admin'][$sessionName] = array();
}

/**
 * Management of the location bar  
 */
function location_bar_management($mode){
	$sessionName = "docserver_types";
	$pageName = "docserver_types_management_controler";
	$tableName = "docserver_types";
	$idName = "docserver_types_id";
	
	$page_labels = array('add' => _ADDITION, 'up' => _MODIFICATION, 'list' => _DOCSERVER_TYPES_LIST);
	$page_ids = array('add' => 'docserver_add', 'up' => 'docserver_up', 'list' => 'docserver_types_list');

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
	$sessionName = "docserver_types";
	$pageName = "docserver_types_management_controler";
	$tableName = "docserver_types";
	$idName = "docserver_types_id";
	
	$f=new functions();

	$docserver_types = new docserver_types();
	//$f->show_array($_REQUEST);exit;
	if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
		// Update, so values exist
		$docserver_types->docserver_types_id=$f->protect_string_db($f->wash($_REQUEST['id'], "nick", _THE_DOCSERVER_TYPE_ID." ", "yes", 0, 32));
	}
	$docserver_types->dstype_label=$f->protect_string_db($f->wash($_REQUEST['dstype_label'], "no", _DSTYPE_LABEL." ", 'yes', 0, 255));
	$docserver_types->is_container=$f->protect_string_db($f->wash($_REQUEST['is_container'], "no", _IS_CONTAINER." ", 'yes', 0, '5'));
	if($docserver_types->is_container == "false"){
		$docserver_types->is_container=false;
	} else {
		$docserver_types->is_container=true;
	}
	$docserver_types->container_max_number=$f->protect_string_db($f->wash($_REQUEST['container_max_number'], "no", _CONTAINER_MAX_NUMBER." ", 'yes', 0, 6));
	$docserver_types->is_compressed=$f->protect_string_db($f->wash($_REQUEST['is_compressed'], "no", _IS_COMPRESSED." ", 'yes', 0, '5'));
	if($docserver_types->is_compressed == "false"){
		$docserver_types->is_compressed=false;
	} else {
		$docserver_types->is_compressed=true;
	}
	$docserver_types->compression_mode=$f->protect_string_db($f->wash($_REQUEST['compression_mode'], "no", _COMPRESSION_MODE." ", 'yes', 0, 32));
	$docserver_types->is_meta=$f->protect_string_db($f->wash($_REQUEST['is_meta'], "no", _IS_META." ", 'yes', 0, '5'));
	if($docserver_types->is_meta == "false"){
		$docserver_types->is_meta=false;
	} else {
		$docserver_types->is_meta=true;
	}
	$docserver_types->meta_template=$f->protect_string_db($f->wash($_REQUEST['meta_template'], "no", _META_TEMPLATE." ", 'yes', 0, 32));
	$docserver_types->is_logged=$f->protect_string_db($f->wash($_REQUEST['is_logged'], "no", _IS_LOGGED." ", 'yes', 0, '5'));
	if($docserver_types->is_logged == "false"){
		$docserver_types->is_logged=false;
	} else {
		$docserver_types->is_logged=true;
	}
	$docserver_types->log_template=$f->protect_string_db($f->wash($_REQUEST['log_template'], "no", _LOG_TEMPLATE." ", 'yes', 0, 32));
	$docserver_types->is_signed=$f->protect_string_db($f->wash($_REQUEST['is_signed'], "no", _IS_SIGNED." ", 'yes', 0, '5'));
	if($docserver_types->is_signed == "false"){
		$docserver_types->is_signed=false;
	} else {
		$docserver_types->is_signed=true;
	}
	$docserver_types->signature_mode=$f->protect_string_db($f->wash($_REQUEST['signature_mode'], "no", _SIGNATURE_MODE." ", 'yes', 0, 32));
	$status= array();
	$status['order']=$_REQUEST['order'];
	$status['order_field']=$_REQUEST['order_field'];
	$status['what']=$_REQUEST['what'];
	$status['start']=$_REQUEST['start'];
	
	if($mode == "add" && docserver_types_controler::docserverTypesExists($docserver_types->docserver_types_id)){	
		$_SESSION['error'] = $docserver_types->docserver_types_id." "._ALREADY_EXISTS."<br />";
	}
	
	if(!empty($_SESSION['error'])) {
		// Error management depending of mode
		put_in_session("status",$status);
		put_in_session("docserver_types",$docserver_types->getArray());
		
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
		//$f->show_array($docserver_types);
		$docserver_types=docserver_types_controler::save($docserver_types);
		//history
		if($_SESSION['history']['docserver_typesadd'] == "true" && $mode == "add"){
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add(_DOCSERVER_TYPES_TABLE_NAME, $_REQUEST['id'], "ADD",_DOCSERVER_TYPE_ADDED." : ".$_REQUEST['id'], $_SESSION['config']['databasetype']);
		} elseif($_SESSION['history']['docserver_typesadd'] == "true" && $mode == "up"){
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add(_DOCSERVER_TYPES_TABLE_NAME, $_REQUEST['id'], "UP",_DOCSERVER_TYPE_UPDATED." : ".$_REQUEST['id'], $_SESSION['config']['databasetype']);
		}
		if($mode == "add")
			$_SESSION['error'] =  _DOCSERVER_TYPE_ADDED;
		 else
			$_SESSION['error'] = _DOCSERVER_TYPE_UPDATED;
		unset($_SESSION['m_admin']);
		header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=list&module=life_cycle&order=".$status['order']."&order_field=".$status['order_field']."&start=".$status['start']."&what=".$status['what']);
	}
}

/**
 * Initialize session parameters for update display
 * @param Long $docserver_types_id
 */
function display_up($docserver_types_id){
	$state=true;
	$docserver_types = docserver_types_controler::get($docserver_types_id);
	if(empty($docserver_types))
		$state = false; 
	else
		put_in_session("docserver_types", $docserver_types->getArray()); 
	
	return $state;
}

/**
 * Initialize session parameters for add display with given docserver
 */
function display_add(){
	$sessionName = "docserver_types";
	if(!isset($_SESSION['m_admin'][$sessionName]))
		init_session();
}

/**
 * Initialize session parameters for list display
 */
function display_list(){
	$sessionName = "docserver_types";
	$pageName = "docserver_types_management_controler";
	$tableName = "docserver_types";
	$idName = "docserver_types_id";
	
	$_SESSION['m_admin'] = array();
	
	init_session();
	
	$select[_DOCSERVER_TYPES_TABLE_NAME] = array();
	array_push($select[_DOCSERVER_TYPES_TABLE_NAME], $idName, "dstype_label", "is_container", "is_compressed", "enabled");
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
				case "dstype_label":
					format_item($item,_DSTYPE_LABEL,"15","left","left","bottom",true); break;
				case "is_container":
					format_item($item,_IS_CONTAINER,"15","left","left","bottom",true); break;
				case "is_compressed":
					format_item($item,_IS_COMPRESSED,"15","left","left","bottom",true); break;
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
	$result['label_add'] = _DOCSERVER_TYPE_ADDITION;
	$_SESSION['m_admin']['init'] = true;
	$result['title'] = _DOCSERVER_TYPES_LIST." : ".count($tab)." "._DOCSERVER_TYPES;
	$result['autoCompletionArray'] = array();
	$result['autoCompletionArray']["list_script_url"] = $_SESSION['config']['businessappurl']."index.php?display=true&module=life_cycle&page=docserver_types_list_by_id";
	$result['autoCompletionArray']["number_to_begin"] = 1;
	return $result;
}

/**
 * Delete given docserver if exists and initialize session parameters
 * @param unknown_type $docserver_types_id
 */
function display_del($docserver_types_id){
	$docserver_types = docserver_types_controler::get($docserver_types_id);
	if(isset($docserver_types)){
		// Deletion
		docserver_types_controler::delete($docserver_types);
		$_SESSION['error'] = _DOCSERVER_TYPE_DELETED." ".$docserver_types_id;
		if($_SESSION['history']['docserver_typesdel'] == "true"){
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add(_DOCSERVER_TYPES_TABLE_NAME, $docserver_types_id, "DEL", _DOCSERVER_TYPE_DELETED." : ".$docserver_types_id, $_SESSION['config']['databasetype']);
		}
		// NOTE: Why not calling display_list ?
		$pageName = "docserver_types_management_controler";
		?><script>window.top.location='<?php echo $_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=list&module=life_cycle&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;?>';</script>
		<?php
		exit;
	} 
	else{
		// Error management
		$_SESSION['error'] = _DOCSERVER_TYPE.' '._UNKNOWN;
	}
}

/**
 * allow given docserver if exists
 * @param unknown_type $docserver_types_id
 */
function display_enable($docserver_types_id){
	$docserver_types = docserver_types_controler::get($docserver_types_id);
	if(isset($docserver_types)){
		// Disable
		docserver_types_controler::enable($docserver_types);
		$_SESSION['error'] = _DOCSERVER_TYPE_ENABLED." ".$docserver_types_id;
		if($_SESSION['history']['docserver_typesallow'] == "true"){
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add(_DOCSERVER_TYPES_TABLE_NAME, $docserver_types_id, "VAL",_DOCSERVER_TYPE_ENABLED." : ".$docserver_types_id, $_SESSION['config']['databasetype']);
		}
		// NOTE: Why not calling display_list ?
		$pageName = "docserver_types_management_controler";
		?><script>window.top.location='<?php echo $_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=list&module=life_cycle&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;?>';</script>
		<?php
		exit;
	}
	else{
		// Error management
		$_SESSION['error'] = _DOCSERVER_TYPE.' '._UNKNOWN;
	}
}

/**
 * ban given docserver if exists
 * @param unknown_type $docserver_types_id
 */
function display_disable($docserver_types_id){
	$docserver_types = docserver_types_controler::get($docserver_types_id);
	if(isset($docserver_types)){
		// Disable
		docserver_types_controler::disable($docserver_types);
		$_SESSION['error'] = _DOCSERVER_TYPE_DISABLED." ".$docserver_types_id;
		if($_SESSION['history']['docserver_typesban'] == "true"){
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add(_DOCSERVER_TYPES_TABLE_NAME, $docserver_types_id, "BAN", _DOCSERVER_TYPE_DISABLED." : ".$docserver_types_id, $_SESSION['config']['databasetype']);
		}
		// NOTE: Why not calling display_list ?
		$pageName = "docserver_types_management_controler";
		?><script>window.top.location='<?php echo $_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=list&module=life_cycle&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;?>';</script>
		<?php
		exit;
	} 
	else{
		// Error management
		$_SESSION['error'] = _DOCSERVER_TYPE.' '._UNKNOWN;
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

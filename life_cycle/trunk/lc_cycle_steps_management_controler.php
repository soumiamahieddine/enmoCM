<?php
/*
*    Copyright 2008,2009,2010 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief  Contains the life_cycle Object (herits of the BaseObject class)
* 
* 
* @file
* @author Laurent Giovannoni
* @author Sébastien Nana
* @date $date$
* @version $Revision$
* @ingroup life_cycle
*/

//lgi +
$sessionName = "lc_cycle_steps";
$pageName = "lc_cycle_steps_management_controler";
$tableName = "lc_cycle_steps";
$idName = "cycle_step_id";

$mode = 'add';

/*echo "<pre>";
print_r($_REQUEST);
echo "</pre>";*/

core_tools::load_lang(); // NOTE : core_tools is not a static class

if(isset($_REQUEST['mode']) && !empty($_REQUEST['mode'])) {
	$mode = $_REQUEST['mode'];
} else {
	$mode = 'list'; 
}

try {
	require_once("modules/life_cycle/class/lc_cycle_steps_controler.php");
	require_once("modules/life_cycle/class/lc_policies_controler.php");
	require_once("core/class/class_request.php");
	if($mode == 'list') {
		require_once("modules/life_cycle/lang/fr.php");
		require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
	}
} catch (Exception $e) {
	echo $e->getMessage();
}

if($mode == "up" || $mode =="add"){
	$policiesArray = array();
	$policiesArray = lc_policies_controler::getAllId();
}

if(isset($_REQUEST['submit'])) {
	// Action to do with db
	validate_cs_submit($mode);
} else {
	// Display to do
	if(isset($_REQUEST['id']) && !empty($_REQUEST['id']))
		$cycle_step_id = $_REQUEST['id'];
	$state = true;
	switch ($mode) {
		case "up" :
			$state=display_up($cycle_step_id); 
			location_bar_management($mode);
			break;
		case "add" :
			display_add(); 
			location_bar_management($mode);
			break;
		case "del" :			
			display_del($cycle_step_id); 
			break;
		case "list" :
			$lc_cycle_steps_list=display_list(); 
			location_bar_management($mode);
			break;
		case "allow" :
			display_enable($cycle_step_id); 
			location_bar_management($mode);
		case "ban" :
			display_disable($cycle_step_id); 
			location_bar_management($mode);
	}
	include('lc_cycle_steps_management.php');
}

/**
 * Initialize session variables
 */
function init_session() {
	$sessionName = "lc_cycle_steps";
	$_SESSION['m_admin'][$sessionName] = array();
}

/**
 * Management of the location bar  
 */
function location_bar_management($mode) {
	$sessionName = "lc_cycle_steps";
	$pageName = "lc_cycle_steps_management_controler";
	$tableName = "lc_cycle_steps";
	$idName = "cycle_step_id";
	
	$page_labels = array('add' => _ADDITION, 'up' => _MODIFICATION, 'list' => _LC_CYCLE_STEPS_LIST);
	$page_ids = array('add' => 'docserver_add', 'up' => 'docserver_up', 'list' => 'lc_cycle_steps_list');

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
function validate_cs_submit($mode) {
	$sessionName = "lc_cycle_steps";
	$pageName = "lc_cycle_steps_management_controler";
	$tableName = "lc_cycle_steps";
	$idName = "cycle_step_id";
	
	$f=new functions();

	$lc_cycle_steps = new lc_cycle_steps();
	//$f->show_array($_REQUEST);exit;
	if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
		// Update, so values exist
		$lc_cycle_steps->cycle_step_id=$f->protect_string_db($f->wash($_REQUEST['id'], "nick", _THE_LC_CYCLE_STEP_ID." ", "yes", 0, 32));
	}
	
	$lc_cycle_steps->policy_id=$f->protect_string_db($f->wash($_REQUEST['policy_id'], "no", _POLICY_ID." ", 'yes', 0, 32));
	$lc_cycle_steps->cycle_id=$f->protect_string_db($f->wash($_REQUEST['cycle_id'], "no", _LC_CYCLE_ID." ", 'yes', 0, 32));
	$lc_cycle_steps->docserver_type_id=$f->protect_string_db($f->wash($_REQUEST['docserver_type_id'], "no", _DOCSERVER_TYPE_ID." ", 'yes', 0, 32));	
	$lc_cycle_steps->cycle_step_desc=$f->protect_string_db($f->wash($_REQUEST['cycle_step_desc'], "no", _CYCLE_STEP_DESC." ", 'yes', 0, 255));
	$lc_cycle_steps->sequence_number=$f->protect_string_db($f->wash($_REQUEST['sequence_number'], "num", _SEQUENCE_NUMBER." ", 'yes', 0, 255));
	
	$lc_cycle_steps->is_allow_failure=$f->protect_string_db($f->wash($_REQUEST['is_allow_failure'], "no", _IS_ALLOW_FAILURE." ", 'yes', 0, '5'));
	if($lc_cycle_steps->is_allow_failure == "false") {
		$lc_cycle_steps->is_allow_failure=false;	
	} else {
		$lc_cycle_steps->is_allow_failure=true;
	}
	
	$lc_cycle_steps->is_must_complete=$f->protect_string_db($f->wash($_REQUEST['is_must_complete'], "no", _IS_MUST_COMPLETE." ", 'yes', 0, '5'));
	if($lc_cycle_steps->is_must_complete == "false") {
		$lc_cycle_steps->is_must_complete=false;	
	} else {
		$lc_cycle_steps->is_must_complete=true;
	}
	
	$lc_cycle_steps->step_operation=$f->protect_string_db($f->wash($_REQUEST['step_operation'], "no", _STEP_OPERATION." ", 'yes', 0, '5'));
	

	$status= array();
	$status['order']=$_REQUEST['order'];
	$status['order_field']=$_REQUEST['order_field'];
	$status['what']=$_REQUEST['what'];
	$status['start']=$_REQUEST['start'];
	
	
	if($mode == "add" && lc_cycle_steps_controler::cycleStepExists($lc_cycle_steps->cycle_step_id)) {	
		$_SESSION['error'] = $lc_cycle_steps->cycle_step_id." "._ALREADY_EXISTS."<br />";
	}
	
	if(!empty($_SESSION['error'])) {
		// Error management depending of mode
		put_in_session("status", $status);
		put_in_session("lc_cycle_steps", $lc_cycle_steps->getArray());
		
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
		//$f->show_array($lc_cycle_steps);
		$lc_cycle_steps=lc_cycle_steps_controler::save($lc_cycle_steps);
		//history
		if($_SESSION['history']['lc_cycle_stepsadd'] == "true" && $mode == "add") {
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add(_LC_CYCLE_STEPS_TABLE_NAME, $_REQUEST['id'], "ADD",_LC_CYCLE_STEP_ADDED." : ".$_REQUEST['id'], $_SESSION['config']['databasetype']);
		} elseif($_SESSION['history']['lc_cycle_stepsadd'] == "true" && $mode == "up") {
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add(_LC_CYCLE_STEPS_TABLE_NAME, $_REQUEST['id'], "UP",_LC_CYCLE_STEP_UPDATED." : ".$_REQUEST['id'], $_SESSION['config']['databasetype']);
		}
		if($mode == "add")
			$_SESSION['error'] =  _LC_CYCLE_STEP_ADDED;
		 else
			$_SESSION['error'] = _LC_CYCLE_STEP_UPDATED;
		unset($_SESSION['m_admin']);
		header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=list&module=life_cycle&order=".$status['order']."&order_field=".$status['order_field']."&start=".$status['start']."&what=".$status['what']);
	}
}

/**
 * Initialize session parameters for update display
 * @param Long $cycle_step_id
 */
function display_up($cycle_step_id) {
	$state=true;
	$lc_cycle_steps = lc_cycle_steps_controler::get($cycle_step_id);
	if(empty($lc_cycle_steps))
		$state = false; 
	else
		put_in_session("lc_cycle_steps", $lc_cycle_steps->getArray()); 
	
	return $state;
}

/**
 * Initialize session parameters for add display with given docserver
 */
function display_add() {
	$sessionName = "lc_cycle_steps";
	if(!isset($_SESSION['m_admin'][$sessionName]))
		init_session();
}

/**
 * Initialize session parameters for list display
 */
function display_list() {
	$sessionName = "lc_cycle_steps";
	$pageName = "lc_cycle_steps_management_controler";
	$tableName = "lc_cycle_steps";
	$idName = "cycle_step_id";
	
	$_SESSION['m_admin'] = array();
	
	init_session();
	
	$select[_LC_CYCLE_STEPS_TABLE_NAME] = array();
	array_push($select[_LC_CYCLE_STEPS_TABLE_NAME], $idName, "cycle_id", "cycle_step_desc","policy_id" , "sequence_number");
	$what = "";
	$where ="";
	if(isset($_REQUEST['what']) && !empty($_REQUEST['what'])) {
		$what = functions::protect_string_db($_REQUEST['what']);
		if($_SESSION['config']['databasetype'] == "POSTGRESQL") {
			$where = $idName." ilike '".strtoupper($what)."%' ";
		} else {
			$where = $idName." like '".strtoupper($what)."%' ";
		}
	}

	// Checking order and order_field values
	$order = 'asc';
	if(isset($_REQUEST['order']) && !empty($_REQUEST['order'])) {
		$order = trim($_REQUEST['order']);
	}
	$field = $idName;
	if(isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field'])) {
		$field = trim($_REQUEST['order_field']);
	}
	$orderstr = list_show::define_order($order, $field);
	$request = new request();
	$tab=$request->select($select,$where,$orderstr,$_SESSION['config']['databasetype']);
	//$request->show();
	for ($i=0;$i<count($tab);$i++) {
		foreach($tab[$i] as &$item) {
			switch ($item['column']) {
				case $idName:
					format_item($item,_ID,"15","left","left","bottom",true); break;
				case "cycle_step_desc":
					format_item($item,_CYCLE_STEP_DESC,"40","left","left","bottom",true); break;
					case "cycle_id":
					format_item($item,_CYCLE_ID,"15","left","left","bottom",true); break;
					case "policy_id":
					format_item($item,_POLICY_ID,"15","left","left","bottom",true); break;
				case "sequence_number":
					format_item($item,_SEQUENCE_NUMBER,"15","left","left","bottom",true); break;
			}
		}
	}
	$result = array();
	$result['tab']=$tab;
	$result['what']=$what;
	$result['page_name'] = $pageName."&mode=list";
	$result['page_name_up'] = $pageName."&mode=up";
	$result['page_name_del'] = $pageName."&mode=del";
	//$result['page_name_val']= $pageName."&mode=allow";
	//$result['page_name_ban'] = $pageName."&mode=ban";
	$result['page_name_add'] = $pageName."&mode=add";
	$result['label_add'] = _LC_CYCLE_STEP_ADDITION;
	$_SESSION['m_admin']['init'] = true;
	$result['title'] = _LC_CYCLE_STEPS_LIST." : ".count($tab)." "._LC_CYCLE_STEPS;
	$result['autoCompletionArray'] = array();
	$result['autoCompletionArray']["list_script_url"] = $_SESSION['config']['businessappurl']."index.php?display=true&module=life_cycle&page=lc_cycle_steps_list_by_id";
	$result['autoCompletionArray']["number_to_begin"] = 1;
	return $result;
}

/**
 * Delete given docserver if exists and initialize session parameters
 * @param unknown_type $cycle_step_id
 */
function display_del($cycle_step_id) {
	$lc_cycle_steps = lc_cycle_steps_controler::get($cycle_step_id);
	if(isset($lc_cycle_steps)) {
		// Deletion
		if(!lc_cycle_steps_controler::delete($cycle_step_id)) {
			$_SESSION['error'] = _YOU_CANNOT_DELETE." ".$cycle_step_id;
		} else {
			echo "ici"; exit;
			$_SESSION['error'] = _LC_CYCLE_STEP_DELETED." ".$cycle_step_id;
			if($_SESSION['history']['lc_cycle_stepsdel'] == "true") {
				require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
				$history = new history();
				$history->add(_LC_CYCLE_STEPS_TABLE_NAME, $cycle_step_id, "DEL", _LC_CYCLE_STEP_DELETED." : ".$cycle_step_id, $_SESSION['config']['databasetype']);
			}
		}
		$pageName = "lc_cycle_steps_management_controler";
		?><script>window.top.location='<?php echo $_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=list&module=life_cycle";?>';</script>
		<?php
		exit;
	} else {
		// Error management
		$_SESSION['error'] = _LC_CYCLE_STEP.' '._UNKNOWN;
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
function format_item(&$item,$label,$size,$label_align,$align,$valign,$show) {
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
function put_in_session($type,$hashable) {
	foreach($hashable as $key=>$value) {
		// echo "Key: $key Value: $value f:".functions::show_string($value)." // ";
		$_SESSION['m_admin'][$type][$key]=functions::show_string($value);
	}
}

?>

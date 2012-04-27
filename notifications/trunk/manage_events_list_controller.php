<?php
core_tools::load_lang();
$core_tools = new core_tools();
$core_tools->test_admin('admin_notif', 'notifications');

// Default mode is add
$mode = 'add';
if (isset($_REQUEST['mode']) && !empty($_REQUEST['mode'])) {
    $mode = $_REQUEST['mode'];
}



try{
    require_once 'core/class/ActionControler.php';
    require_once 'core/class/ObjectControlerAbstract.php';
    require_once 'core/class/ObjectControlerIF.php';
    require_once 'modules/templates/class/templates_controler.php' ;
//    require_once 'modules/notifications/class/notifications.php' ;
    require_once 'modules/notifications/class/templates_association_controler.php';
    require_once 'modules/notifications/class/diffusion_type_controler.php';
	//require_once 'modules/notifications/class/diffusion_content_controler.php';
    
    if ($mode == 'list') {
        require_once 'core/class/class_request.php' ;
        require_once 'apps' . DIRECTORY_SEPARATOR
                     . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR
                     . 'class' . DIRECTORY_SEPARATOR . 'class_list_show.php' ;
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

//Get list of aff availables actions
$al = new ActionControler();
$actions_list = $al->getAllActions();

//Get list of all diffusion types
$dt = new diffusion_type_controler();
$diffusion_types = $dt->getAllDiffusion();

//Get list of all diffusion contents
//$dt = new diffusion_content_controler();
//$diffusion_contents = $dt->getAllContents();

$tp = new templates_controler();
$templates_list = $tp->getAllTemplatesForSelect();
//Get list of all templates
if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
    $eventId = $_REQUEST['id'];
}


if (isset($_REQUEST['event_submit'])) {
    // Action to do with db
    validate_event_submit();

} else {
    // Display to do
    $state = true;
    switch ($mode) {
        case 'up' :
            $state = display_up($eventId);
         
            $_SESSION['service_tag'] = 'event_init';
            core_tools::execute_modules_services(
                $_SESSION['modules_services'], 'event_init', 'include'
            );
            location_bar_management($mode);
            break;
        case 'add' :
            display_add();
            $_SESSION['service_tag'] = 'event_init';
            core_tools::execute_modules_services(
                $_SESSION['modules_services'], 'event_init', 'include'
            );
            location_bar_management($mode);
            break;
        case 'del' :
            display_del($eventId);
            break;
        case 'list' :
            $eventsList = display_list();
            location_bar_management($mode);
           // print_r($statusList); exit();
            break;
    }
    include('manage_events_list.php');
}

/**
 * Management of the location bar
 */
function location_bar_management($mode)
{
    $pageLabels = array('add'  => _ADDITION,
                    'up'   => _MODIFICATION,
                    'list' => _MANAGE_EVENTS
               );
    $pageIds = array('add' => 'status_add',
                  'up' => 'status_up',
                  'list' => 'status_list'
            );
    $init = false;
    if (isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == 'true') {
        $init = true;
    }

    $level = '';
    if (isset($_REQUEST['level'])
        && ($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3
            || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)) {
        $level = $_REQUEST['level'];
    }

    $pagePath = $_SESSION['config']['businessappurl'] . 'index.php?page='
               . 'manage_events_controller&module=notifications&mode=' . $mode ;
    $pageLabel = $pageLabels[$mode];
    $pageId = $pageIds[$mode];
    $ct = new core_tools();
    $ct->manage_location_bar($pagePath, $pageLabel, $pageId, $init, $level);
}

/**
 * Initialize session parameters for update display
 * @param String $statusId
 */
function display_up($eventId)
{
	
    $eventCtrl = new templates_association_controler();
    $state = true;
    $event = $eventCtrl->get($eventId);

    if (empty($event)) {
        $state = false;
    } else {
		//var_dump($event);
        put_in_session('event', $event->getArray());
    }
    return $state;
}

/**
 * Initialize session parameters for add display
 */
function display_add()
{
    if (!isset($_SESSION['m_admin']['init'])) {
        init_session();
    }
}

/**
 * Initialize session parameters for list display
 */
function display_list() {
    $_SESSION['m_admin'] = array();
    $list = new list_show();
    $func = new functions();
    init_session();

    $select[TEMPLATES_ASSOCIATION] = array();
    array_push(
        $select[TEMPLATES_ASSOCIATION], 'system_id', 'template_id', 'notification_id', 'description'
    );
    $where = '';
    $what = '';
    if (isset($_REQUEST['what'])) {
        $what = $func->protect_string_db($_REQUEST['what']);
    }
    $where .= " (lower(description) like lower('"
				. $func->protect_string_db($what, $_SESSION['config']['databasetype'])
				. "%') or lower(description) like lower('"
				. $func->protect_string_db($what, $_SESSION['config']['databasetype'])
				. "%')) ";

    // Checking order and order_field values
    $order = 'asc';
    if (isset($_REQUEST['order']) && !empty($_REQUEST['order'])) {
        $order = trim($_REQUEST['order']);
    }

    $field = 'description';
    if (isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field'])) {
        $field = trim($_REQUEST['order_field']);
    }

    $orderstr = $list->define_order($order, $field);
    $request = new request();
    $tab = $request->select(
        $select, $where, $orderstr, $_SESSION['config']['databasetype']
    );
	//$request->show();
	
    for ($i=0;$i<count($tab);$i++) {
        foreach ($tab[$i] as &$item) {
            switch ($item['column']) {
                case 'system_id':
                    format_item(
                        $item, _ID, '18', 'left', 'left', 'bottom', true
                    );
                    break;
                case 'description':
                    format_item(
                        $item, _DESC, '55', 'left', 'left', 'bottom', true
                    );
                    break;
            }
        }
    }
    $_SESSION['m_admin']['init'] = true;
    $result = array(
        'tab'                 => $tab,
        'what'                => $what,
        'page_name'           => 'manage_events_list_controller&mode=list',
        'page_name_add'       => 'manage_events_list_controller&mode=add',
        'page_name_up'        => 'manage_events_list_controller&mode=up',
        'page_name_del'       => 'manage_events_list_controller&mode=del',
        'page_name_val'       => '',
        'page_name_ban'       => '',
        'label_add'           => _ADD_EVENT,
        'title'               => _EVENTS_LIST . ' : ' . $i,
        'autoCompletionArray' => array(
                                     'list_script_url'  =>
                                        $_SESSION['config']['businessappurl']
                                        . 'index.php?display=true&module=notifications'
                                        . '&page=manage_events_list_by_name',
                                     'number_to_begin'  => 1
                                 ),

    );
    return $result;
}

/**
 * Delete given status if exists and initialize session parameters
 * @param string $statusId
 */
function display_del($eventId) {
	
    $eventCtrl = new templates_association_controler();
    echo $eventId; 
    $event = $eventCtrl->get($eventId);
    if (isset($event)) {
        // Deletion
        $control = array();
        $params  = array( 'log_status_del' => $_SESSION['history']['eventdel'],
                         'databasetype' => $_SESSION['config']['databasetype']
                        );
        $control = $eventCtrl->delete($event, $params);
        if (!empty($control['error']) && $control['error'] <> 1) {
            $_SESSION['error'] = str_replace("#", "<br />", $control['error']);
        } else {
            $_SESSION['error'] = _EVENT_DELETED.' : '.$eventId;
        }
        ?><script type="text/javascript">window.top.location='<?php
            echo $_SESSION['config']['businessappurl']
                . 'index.php?page=manage_events_list_controller&mode=list&module='
                . 'notifications&order=' . $_REQUEST['order'] . '&order_field='
                . $_REQUEST['order_field'] . '&start=' . $_REQUEST['start']
                . '&what=' . $_REQUEST['what'];
        ?>';</script>
        <?php
        exit();
    } else {
        // Error management
        $_SESSION['error'] = _EVENT.' '._UNKNOWN;
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
 * @param $labelAlign
 * @param $align
 * @param $valign
 * @param $show
 */
function format_item(
    &$item, $label, $size, $labelAlign, $align, $valign, $show, $order = true
) {
    $func = new functions();
    $item['value'] = $func->show_string($item['value']);
    $item[$item['column']] = $item['value'];
    $item['label'] = $label;
    $item['size'] = $size;
    $item['label_align'] = $labelAlign;
    $item['align'] = $align;
    $item['valign'] = $valign;
    $item['show'] = $show;
    if ($order) {
        $item['order'] = $item['value'];
    } else {
        $item['order'] = '';
    }
}

/**
 * Validate a submit (add or up),
 * up to saving object
 */
function validate_event_submit() {
	$dType = new diffusion_type_controler();
	$diffType = array();
	$diffType = $dType->getAllDiffusion();
   
    $eventCtrl = new templates_association_controler();
    $pageName = 'manage_events_list_controller';

    $mode = $_REQUEST['mode'];
    $eventObj = new templates_association();
    
    if ($mode <> 'add'){
		$eventObj->system_id = $_REQUEST['system_id'];
	}
    $eventObj->notification_id = $_REQUEST['notification_id'];
	$eventObj->description = $_REQUEST['description'];
    $eventObj->value_field = $_REQUEST['value_field'];
    $eventObj->template_id = $_REQUEST['template_id'];
    $eventObj->diffusion_type = $_REQUEST['diffusion_type'];
    $eventObj->is_attached = $_REQUEST['is_attached'];
	
	foreach($diffType as $loadedType) 	{
		if ($loadedType->id == $eventObj->diffusion_type){
			if ($loadedType -> script <> '') {
				include($loadedType->script);
				$diffusion_properties_string = updatePropertiesSet($_REQUEST['diffusion_properties']);
				
			} else {
				$error .= 'System : Unable to load Require Script';
			}
		}	
	}		
			
	$eventObj->diffusion_properties = $diffusion_properties_string;
	
	
    $control = $eventCtrl->save($eventObj, $mode, $params);
    
    if (!empty($control['error']) && $control['error'] <> 1) {
        // Error management depending of mode
        $_SESSION['error'] = str_replace("#", "<br />", $control['error']);
        put_in_session('event', $event);
        put_in_session('event', $eventObj->getArray());

        switch ($mode) {
            case 'up':
                if (!empty($event->system_id)) {
                    header(
                        'location: ' . $_SESSION['config']['businessappurl']
                        . 'index.php?page=' . $pageName . '&mode=up&id='
                        . $event->event_id . '&module=notifications'
                    );
                } else {
                    header(
                        'location: ' . $_SESSION['config']['businessappurl']
                        . 'index.php?page=' . $pageName . '&mode=list&module='
                        .'notifications&order=' . $status['order'] . '&order_field='
                        . $status['order_field'] . '&start=' . $status['start']
                        . '&what=' . $status['what']
                    );
                }
                exit();
            case 'add':
                header(
                    'location: ' . $_SESSION['config']['businessappurl']
                    . 'index.php?page=' . $pageName . '&mode=add&module=notifications'
                );
                exit();
        }
    } else {
        if ($mode == 'add') {
            $_SESSION['error'] = _EVENT_ADDED;
        } else {
            $_SESSION['error'] = _EVENT_MODIFIED;
        }
        unset($_SESSION['m_admin']);
        header(
            'location: ' . $_SESSION['config']['businessappurl']
            . 'index.php?page=' . $pageName . '&mode=list&module=notifications&order='
            . $status['order'] . '&order_field=' . $status['order_field']
            . '&start=' . $status['start'] . '&what=' . $status['what']
        );
    }
   
}

function init_session()
{
    $_SESSION['m_admin']['event'] = array(
        'system_id'             	 => '',
		'notification_id'  			 	 => '',
        'description'  			 	 => '',
        'template_id'      	 => '',
        'diffusion_type'    		 => '',
        'diffusion_properties'  => '',
		//'diffusion_content'    		 => '',
        'is_attached' 			 => '',
        
    );
}

/**
 * Put given object in session, according with given type
 * NOTE: given object needs to be at least hashable
 * @param string $type
 * @param hashable $hashable
 */
function put_in_session($type, $hashable, $showString = true)
{
    $func = new functions();
    foreach ($hashable as $key=>$value) {
        if ($showString) {
            $_SESSION['m_admin'][$type][$key]=$func->show_string($value);
        } else {
            $_SESSION['m_admin'][$type][$key]=$value;
        }
    }
    //print_r($_SESSION['m_admin']);
}

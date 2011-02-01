<?php

$GLOBALS['basket_loaded'] = false;
$GLOBALS['entities_loaded'] = false;
$func = new functions();
if(core_tools::is_module_loaded('basket')){
    $GLOBALS['basket_loaded'] = true;
}
if(core_tools::is_module_loaded('entities')){
    $GLOBALS['entities_loaded'] = true;
}

$mode = 'add';
if(isset($_REQUEST['mode']) && !empty($_REQUEST['mode']))
    $mode = $_REQUEST['mode'];

try{
    require_once("core/class/usergroups_controler.php");
    require_once("core/class/users_controler.php");
    require_once("core/class/SecurityControler.php");
    require_once("core/class/class_security.php");
    if($mode == 'list'){
        require_once("core/class/class_request.php");
        require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
    }
    if($GLOBALS['basket_loaded']){
        require_once("modules/basket/class/BasketControler.php");
    }
    if($mode == 'del' && $GLOBALS['entities_loaded']){
        require_once("modules/entities/class/EntityControler.php");
    }

} catch (Exception $e){
    echo $e->getMessage();
}

core_tools::load_lang();

if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
    $group_id = $_REQUEST['id'];
}

if(isset($_REQUEST['group_submit'])){
    // Action to do with db
    validate_group_submit();

} else {
    // Display to do
    $users = array();
    $baskets = array();
    $access = array();
    $services = array();
    $state = true;
    switch ($mode) {
        case "up" :
            $res=display_up($group_id);
            $state = $res['state'];
            $users = $res['users'];
            $baskets = $res['baskets'];
            $access = $res['access'];
            $services = $res['services'];
            location_bar_management($mode);
            break;
        case "add" :
            display_add();
            location_bar_management($mode);
            break;
        case "del" :
            display_del($group_id);
            break;
        case "allow" :
            display_enable($group_id);
            break;
        case "ban" :
            display_disable($group_id);
            break;
        case "list" :
            $groups_list=display_list();
            location_bar_management($mode);
            break;
    }
    include('usergroups_management.php');
}

///////////// FUNCTIONS
/**
 * Management of the location bar
 */
function location_bar_management($mode){
    $page_labels = array('add' => _ADDITION, 'up' => _MODIFICATION, 'list' => _GROUPS_LIST);
    $page_ids = array('add' => 'group_add', 'up' => 'group_up', 'list' => 'groups_list');
    $init = false;
    if(isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == "true"){
        $init = true;
    }
    $level = "";
    if(isset($_REQUEST['level']) && ($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)){
        $level = $_REQUEST['level'];
    }
    $page_path = $_SESSION['config']['businessappurl'].'index.php?page=usergroups_management_controler&admin=groups&mode='.$mode;
    $page_label = $page_labels[$mode];
    $page_id = $page_ids[$mode];
    $ct=new core_tools();
    $ct->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
}


function init_session(){
    $_SESSION['m_admin']['groups'] = array();
    $_SESSION['m_admin']['groups']['group_id'] = "";
    $_SESSION['m_admin']['groups']['group_desc'] = "";
    $_SESSION['m_admin']['groups']['security'] = array();
    $_SESSION['m_admin']['groups']['services'] = array();
    $_SESSION['m_admin']['init'] = false;
    $_SESSION['m_admin']['load_security']  = true;
    $_SESSION['m_admin']['load_services'] = true;
}

function transform_security_object_into_array($security){
    if(!isset($security)){
        return array();
    }

    $sec_id = $security->__get('security_id');
    $group_id = $security->__get('group_id');
    $comment = $security->__get('maarch_comment');
    $coll_id = $security->__get('coll_id');
    $where = $security->__get('where_clause');
    $target = $security->__get('where_target');
    $start_date = $security->__get('mr_start_date');
    $stop_date = $security->__get('mr_stop_date');
    $rights_bitmask = $security->__get('rights_bitmask');
    $sec = new security();
    $ind = $sec->get_ind_collection($coll_id);

    return array('SECURITY_ID' => $sec_id , 'GROUP_ID' => $group_id ,'COLL_ID' => $coll_id, 'IND_COLL_SESSION' => $ind, 'WHERE_CLAUSE' => $where, 'COMMENT' => $comment ,'WHERE_TARGET'=> $target, 'START_DATE' => $start_date, 'STOP_DATE' => $stop_date, 'RIGHTS_BITMASK' => $rights_bitmask);
}

function transform_array_of_security_object($array_sec){
    $res = array();
    for($i=0; $i<count($array_sec);$i++){
        array_push($res, transform_security_object_into_array($array_sec[$i]));
    }
    return $res;
}

/**
 * Initialize session parameters for update display
 * @param Long $scheme_id
 */
function display_up($group_id){
    $users = array();
    $baskets = array();
    $access = array();
    $services = array();
    $state=true;
    $ugc = new usergroups_controler();
    $uc = new users_controler();
    $group = $ugc->get($group_id );
    $sec_ctrl = new SecurityControler();
    if(!isset($group)){
        $state = false;
    }
    else{
        put_in_session("groups",$group->getArray());
    }
    if (! isset($_SESSION['m_admin']['load_security']) || $_SESSION['m_admin']['load_security'] == true){
        $access = $sec_ctrl->getAccessForGroup($group_id); // Get security accesses in an array
        $_SESSION['m_admin']['groups']['security'] = transform_array_of_security_object($access);
        $_SESSION['m_admin']['load_security'] = false ;
    }
    if (! isset($_SESSION['m_admin']['load_services']) || $_SESSION['m_admin']['load_services'] == true){
        $services = $ugc->getServices($group_id);  // Get services array
        $_SESSION['m_admin']['groups']['services'] = $services;
        $_SESSION['m_admin']['load_services'] = false ;
    }
    $users_id = $ugc->getUsers($group_id ); //Get all user_id of all members of the group
    $baskets_id = $ugc->getBaskets($group_id ); // Get all basket_id linked to the group
    for($i=0; $i<count($users_id);$i++){
        $tmp_user = $uc ->get($users_id[$i]);
        if(isset($tmp_user)){
            array_push($users, $tmp_user);
        }
    }

    unset($tmp_user);

    if(isset($GLOBALS['basket_loaded']) && $GLOBALS['basket_loaded'] == true && count($baskets_id) > 0){
        for($i=0; $i<count($baskets_id);$i++){
            $tmp_bask = BasketControler::get($baskets_id[$i]);
            if(isset($tmp_bask)){
                $baskets[] = $tmp_bask;
            }
        }
    }
    $res['state'] = $state;
    $res['users'] = $users;
    $res['baskets'] = $baskets;
    $res['services'] = $services;
    $res['access'] = $access;
    return $res;
}

/**
 * Initialize session parameters for add display with given scheme
 */
function display_add(){
    if ($_SESSION['m_admin']['init']== true || !isset($_SESSION['m_admin']['init'] )){
        init_session();
    }
}

/**
 * Initialize session parameters for list display
 */
function display_list(){
    $_SESSION['m_admin'] = array();
    init_session();
    $func = new functions();
    $select[USERGROUPS_TABLE] = array();
    array_push($select[USERGROUPS_TABLE],'group_id','group_desc','enabled');
    $where = "";
    $what ="";
    if(isset($_REQUEST['what']) && !empty($_REQUEST['what'])){
        $what = $func->protect_string_db($_REQUEST['what']);
    }
    if($_SESSION['config']['databasetype'] == "POSTGRESQL"){
        $where = "group_desc ilike '".strtolower($what)."%' or group_id ilike '".strtoupper($what)."%' ";
    }
    else{
        $where = "group_desc like '".strtolower($what)."%' or group_id like '".strtoupper($what)."%' ";
    }
            // Checking order and order_field values
    $order = 'asc';
    if(isset($_REQUEST['order']) && !empty($_REQUEST['order'])){
        $order = trim($_REQUEST['order']);
    }

    $field = 'group_id';
    if(isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field'])){
        $field = trim($_REQUEST['order_field']);
    }
    $list = new list_show();
    $orderstr = $list->define_order($order, $field);
    $request = new request();
    $tab=$request->select($select,$where,$orderstr,$_SESSION['config']['databasetype']);
    for ($i=0;$i<count($tab);$i++) {
        foreach($tab[$i] as &$item) {
            switch ($item['column']){
                case "group_id":
                    format_item($item,_ID,"18","left","left","bottom",true); break;
                case "group_desc":
                    format_item($item,_DESC,"30","left","left","bottom",true); break;
                case "enabled":
                    format_item($item,_STATUS,"6","center","center","bottom",true); break;
            }
        }
    }

    /*
     * TODO Pour éviter les actions suivantes, il y a 2 solutions :
     * - La plus propre : créer un objet "PageList"
     * - La plus locale : si cela ne sert que pour admin_list dans classification_scheme_management.php,
     *                    il est possible d'en construire directement la string et de la récupérer en return.
     */
    $result = array();
    $result['tab']=$tab;
    $result['what']=$what;
    $result['page_name'] = "usergroups_management_controler&mode=list";
    $result['page_name_up'] = "usergroups_management_controler&mode=up";
    $result['page_name_del'] = "usergroups_management_controler&mode=del";
    $result['page_name_val']= "usergroups_management_controler&mode=allow";
    $result['page_name_ban'] = "usergroups_management_controler&mode=ban";
    $result['page_name_add'] = "usergroups_management_controler&mode=add";
    $result['label_add'] = _GROUP_ADDITION;
    $_SESSION['m_admin']['load_security']  = true;
    $_SESSION['m_admin']['load_services'] = true;
    $_SESSION['m_admin']['init'] = true;
    $result['title'] = _GROUPS_LIST." : ".$i." "._GROUPS;
    $result['autoCompletionArray'] = array();
    $result['autoCompletionArray']["list_script_url"] = $_SESSION['config']['businessappurl']."index.php?display=true&admin=groups&page=groups_list_by_name";
    $result['autoCompletionArray']["number_to_begin"] = 1;
    return $result;
}

/**
 * Delete given usergroup if exists and initialize session parameters
 * @param unknown_type $group_id
 */
function display_del($group_id){

    $ugc = new usergroups_controler();
    $group = $ugc->get($group_id);
    if(isset($group) && isset($group_id) && !empty($group_id)){
        $control = array();
        $params = array();
        if(isset($_SESSION['history']['usergroupsdel'])){
            $params['log_group_del'] = $_SESSION['history']['usergroupsdel'];
        }
        if(isset($_SESSION['config']['databasetype'])){
            $params['databasetype'] = $_SESSION['config']['databasetype'];
        }
        else{
            $params['databasetype'] = 'POSTGRESQL';
        }
        $control = $ugc->delete($group, $params);
        if($GLOBALS['basket_loaded']){
            $bc = new BasketControler();
            $bc->cleanFullGroupbasket($group_id, 'group_id');
        }
        if($GLOBALS['entities_loaded']){
            $ec = new EntityControler();
            $ec->cleanGroupbasketRedirect($group_id, 'group_id');
        }
        if(!empty($control['error']) && $control['error'] <> 1) {
            $_SESSION['error'] = str_replace("#", "<br />", $control['error']);
        } else {
            $_SESSION['error'] = _DELETED_GROUP.' : '.$group_id;
        }

        ?><script type="text/javascript">window.top.location='<?php echo $_SESSION['config']['businessappurl']."index.php?page=usergroups_management_controler&mode=list&admin=groups&order=".$_REQUEST['order']."&order_field=".$_REQUEST['order_field']."&start=".$_REQUEST['start']."&what=".$_REQUEST['what'];?>';</script>
        <?php
        exit();
    }
    else{
        // Error management
        $_SESSION['error'] = _GROUP.' '._UNKNOWN;
    }
}

/**
 * Enable given usergroup if exists and initialize session parameters
 * @param unknown_type $user_id
 */
function display_enable($group_id){

    $ugc = new usergroups_controler();
    $group = $ugc->get($group_id);
    if(isset($group)){
        $control = array();
        $params = array();
        if(isset($_SESSION['history']['usergroupsval'])){
            $params['log_group_enabled'] = $_SESSION['history']['usergroupsval'];
        }
        if(isset($_SESSION['config']['databasetype'])){
            $params['databasetype'] = $_SESSION['config']['databasetype'];
        }
        else{
            $params['databasetype'] = 'POSTGRESQL';
        }
        $control = $ugc->enable($group, $params);
         if(!empty($control['error']) && $control['error'] <> 1) {
            $_SESSION['error'] = str_replace("#", "<br />", $control['error']);
        } else {
            $_SESSION['error'] = _AUTORIZED_GROUP.' : '.$group_id;
        }
        exit();
        $url = $_SESSION['config']['businessappurl']."index.php?page=usergroups_management_controler&mode=list&admin=groups&order=".$_REQUEST['order']."&order_field=".$_REQUEST['order_field']."&start=".$_REQUEST['start']."&what=".$_REQUEST['what'];
        ?><script type="text/javascript">window.top.location='<?php echo $url;?>';</script>
        <?php
        exit();
    }
    else{
        // Error management
        $_SESSION['error'] = _GROUP.' '._UNKNOWN;
    }
}

/**
 * Disable given user if exists and initialize session parameters
 * @param unknown_type $user_id
 */
function display_disable($group_id){
    $ugc = new usergroups_controler();
    $group = $ugc->get($group_id);
    if(isset($group)){
        $control = array();
        $params = array();
        if(isset($_SESSION['history']['usergroupsban'])){
            $params['log_group_disabled'] = $_SESSION['history']['usergroupsban'];
        }
        if(isset($_SESSION['config']['databasetype'])){
            $params['databasetype'] = $_SESSION['config']['databasetype'];
        }
        else{
            $params['databasetype'] = 'POSTGRESQL';
        }

        $control = $ugc->disable($group, $params);
        if(!empty($control['error']) && $control['error'] <> 1) {
            $_SESSION['error'] = str_replace("#", "<br />", $control['error']);
        } else {
            $_SESSION['error'] = _SUSPENDED_GROUP.' : '.$group_id;
        }
        exit;
        $url = $_SESSION['config']['businessappurl']."index.php?page=usergroups_management_controler&mode=list&admin=groups&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;
        ?><script type="text/javascript">window.top.location='<?php echo $url;?>';</script>
        <?php
        exit();
    }
    else{
        // Error management
        $_SESSION['error'] = _GROUP.' '._UNKNOWN;
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
function format_item(&$item,$label,$size,$label_align,$align,$valign,$show,$order= true){
    $func = new functions();
    $item['value']=$func->show_string($item['value']);
    $item[$item['column']]=$item['value'];
    $item["label"]=$label;
    $item["size"]=$size;
    $item["label_align"]=$label_align;
    $item["align"]=$align;
    $item["valign"]=$valign;
    $item["show"]=$show;
    if($order){
        $item["order"]=$item['value'];
    }
    else{
        $item["order"]='';
    }
}

/**
 * Validate a submit (add or up),
 * up to saving object
 */
function validate_group_submit(){

    $ugc = new usergroups_controler();
    $pageName = "usergroups_management_controler";
    $group = new usergroups();
    $mode = $_REQUEST['mode'];

    $group->group_id=$_REQUEST['group_id'];
    if (isset($_REQUEST['desc']) && !empty($_REQUEST['desc'])){
        $group->group_desc=$_REQUEST['desc'];
    }

    $status= array();
    $status['order']=$_REQUEST['order'];
    $status['order_field']=$_REQUEST['order_field'];
    $status['what']=$_REQUEST['what'];
    $status['start']=$_REQUEST['start'];

    $control = array();
    $params = array('modules_services' => $_SESSION['modules_services'],
                    'log_group_up' => $_SESSION['history']['usergroupsup'],
                    'log_group_add' => $_SESSION['history']['usergroupsadd'],
                    'databasetype' => $_SESSION['config']['databasetype']
                    );

    $services = array();
    if(isset($_REQUEST['services']))
    {
        $services = $_REQUEST['services'];
    }
    if(isset($_SESSION['m_admin']['groups']['security']))
    {
        $control = $ugc->save($group, $_SESSION['m_admin']['groups']['security'], $services, $mode,$params);
    }

    if(!empty($control['error']) && $control['error'] <> 1) {
        // Error management depending of mode
        $_SESSION['error'] = str_replace("#", "<br />", $control['error']);
        put_in_session("status",$status);
        put_in_session("groups",$group->getArray());

         switch ($mode) {
            case "up":
                if(!empty($group->group_id)) {
                    header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=up&id=".$group->group_id."&admin=groups");
                } else {
                    header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=list&admin=groups&order=".$status['order']."&order_field=".$status['order_field']."&start=".$status['start']."&what=".$status['what']);
                }
                exit;
            case "add":
                $_SESSION['m_admin']['load_group'] = false;
                header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=add&admin=groups");
                exit;
        }
    } else {
        if($mode == "add"){
            $_SESSION['error'] = _GROUP_ADDED;
        }
         else{
            $_SESSION['error'] = _GROUP_UPDATED;
        }
        unset($_SESSION['m_admin']);
        header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=list&admin=groups&order=".$status['order']."&order_field=".$status['order_field']."&start=".$status['start']."&what=".$status['what']);
    }
}

/**
 * Put given object in session, according with given type
 * NOTE: given object needs to be at least hashable
 * @param string $type
 * @param hashable $hashable
 */
function put_in_session($type,$hashable, $show_string = true){
    $func = new functions();
    foreach($hashable as $key=>$value){
        if ($show_string){
            $_SESSION['m_admin'][$type][$key]=$func->show_string($value);
        }
        else{
            $_SESSION['m_admin'][$type][$key]=$value;
        }
    }
}
////////////////////////////////////////
?>

<?php
core_tools::load_lang();
$entities_loaded = false;

if(core_tools::is_module_loaded('entities'))
    $entities_loaded = true;

$mode = 'add';
if(isset($_REQUEST['mode']) && !empty($_REQUEST['mode']))
{
    $mode = $_REQUEST['mode'];
}


try{
    require_once("core/class/usergroups_controler.php");
    require_once("core/class/users_controler.php");
    if($mode == 'list')
    {
        require_once("core/class/class_request.php");
        require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
    }
    if($mode == 'del' && $entities_loaded)
        require_once("modules/entities/class/EntityControler.php");

} catch (Exception $e){
    echo $e->getMessage();
}

if(isset($_REQUEST['id']) && !empty($_REQUEST['id']))
{
    $user_id = $_REQUEST['id'];
}

if(isset($_REQUEST['user_submit'])){
    // Action to do with db
    validate_user_submit();

} else {
    // Display to do

    $state = true;
    switch ($mode) {
        case "up" :
            $state=display_up($user_id);
            $_SESSION['service_tag'] = 'user_init';
            core_tools::execute_modules_services($_SESSION['modules_services'], 'user_init', "include");
            $_SESSION['m_admin']['nbgroups']  = usergroups_controler::getUsergroupsCount();
            location_bar_management($mode);
            break;
        case "add" :
            display_add();
            $_SESSION['service_tag'] = 'user_init';
            core_tools::execute_modules_services($_SESSION['modules_services'], 'user_init', "include");
            $_SESSION['m_admin']['nbgroups']  = usergroups_controler::getUsergroupsCount();
            location_bar_management($mode);
            break;
        case "del" :
            display_del($user_id);
            break;
        case "allow" :
            display_enable($user_id);
            break;
        case "ban" :
            display_disable($user_id);
            break;
        case "list" :
            $users_list=display_list();
            $_SESSION['m_admin']['nbgroups']  = usergroups_controler::getUsergroupsCount();
            location_bar_management($mode);
            break;
    }
    include('users_management.php');
}

/////// PRIVATE BLOCK

/**
 * Management of the location bar
 */
function location_bar_management($mode)
{
    $page_labels = array('add' => _ADDITION, 'up' => _MODIFICATION, 'list' => _USERS_LIST);
    $page_ids = array('add' => 'user_add', 'up' => 'user_up', 'list' => 'users_list');
    $init = false;
    if(isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == "true")
        $init = true;

    $level = "";
    if(isset($_REQUEST['level']) && ($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1))
        $level = $_REQUEST['level'];

    $page_path = $_SESSION['config']['businessappurl'].'index.php?page=users_management_controler&admin=users&mode='.$mode;
    $page_label = $page_labels[$mode];
    $page_id = $page_ids[$mode];
    $ct=new core_tools();
    $ct->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
}

/**
 * Initialize session parameters for update display
 * @param Long $scheme_id
 */
function display_up($user_id){
    $state=true;
    $user = users_controler::get($user_id );
    if(empty($user))
        $state = false;
    else
        put_in_session("users",$user->getArray());

    if (($_SESSION['m_admin']['load_group'] == true || ! isset($_SESSION['m_admin']['load_group'] )) && $_SESSION['m_admin']['users']['user_id'] <> "superadmin")
    {
        $tmp_array = users_controler::getGroups($_SESSION['m_admin']['users']['user_id']);
        for($i=0; $i<count($tmp_array);$i++)
        {
            $group = usergroups_controler::get($tmp_array[$i]['GROUP_ID']);
            $tmp_array[$i]['LABEL'] = $group->__get('group_desc');
        }
        $_SESSION['m_admin']['users']['groups'] = $tmp_array;
        unset($tmp_array);
    }

    return $state;
}

/**
 * Initialize session parameters for add display with given scheme
 */
function display_add(){
    if(!isset($_SESSION['m_admin']['init']))
    {
        init_session();
    }

}

/**
 * Initialize session parameters for list display
 */
function display_list()
{
    $_SESSION['m_admin'] = array();

    init_session();

    $select[USERS_TABLE] = array();
    array_push($select[USERS_TABLE],'user_id','lastname','firstname','enabled','status','mail');
    $where = " status = 'OK' ";
    $what = '';
    if(isset($_REQUEST['what']))
    {
        $what = functions::protect_string_db($_REQUEST['what']);
    }
    if($_SESSION['config']['databasetype'] == "POSTGRESQL")
        $where .= " and ( lastname ilike '".strtolower($what)."%' or lastname ilike '".strtoupper($what)."%' )";
    else
        $where .= " and ( lastname like '".strtolower($what)."%' or lastname like '".strtoupper($what)."%' )";
            // Checking order and order_field values
    $order = 'asc';
    if(isset($_REQUEST['order']) && !empty($_REQUEST['order']))
        $order = trim($_REQUEST['order']);

    $field = 'lastname';
    if(isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field']))
        $field = trim($_REQUEST['order_field']);

    $orderstr = list_show::define_order($order, $field);
    $request = new request();
    $tab=$request->select($select,$where,$orderstr,$_SESSION['config']['databasetype']);
    for ($i=0;$i<count($tab);$i++) {
        foreach($tab[$i] as &$item) {
            switch ($item['column']){
                case "user_id":
                    format_item($item,_ID,"10","left","left","bottom",true); break;
                case "lastname":
                    format_item($item,_LASTNAME,"10","left","left","bottom",true); break;
                case "firstname":
                    format_item($item,_FIRSTNAME,"10","left","left","bottom",true); break;
                case "enabled":
                    format_item($item,_STATUS,"3","left","center","bottom",true); break;
                case "mail":
                    format_item($item,_MAIL,"10","left","left","bottom",true); break;
                case "status":
                    if($item['value'] == "ABS")
                        $item['value'] = "<em>("._MISSING.")</em>";
                    else
                        $item['value'] = '';
                    format_item($item,'',"5","left","left","bottom",true, false); break;
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
    $result['page_name'] = "users_management_controler&mode=list";
    $result['page_name_up'] = "users_management_controler&mode=up";
    $result['page_name_del'] = "users_management_controler&mode=del";
    $result['page_name_val']= "users_management_controler&mode=allow";
    $result['page_name_ban'] = "users_management_controler&mode=ban";
    $result['page_name_add'] = "users_management_controler&mode=add";
    $result['label_add'] = _USER_ADDITION;
    $_SESSION['m_admin']['init'] = true;
    $result['title'] = _USERS_LIST." : ".$i." "._USERS;
    $result['autoCompletionArray'] = array();
    $result['autoCompletionArray']["list_script_url"] = $_SESSION['config']['businessappurl']."index.php?display=true&admin=users&page=users_list_by_name";
    $result['autoCompletionArray']["number_to_begin"] = 1;
    return $result;
}

/**
 * Delete given user if exists and initialize session parameters
 * @param unknown_type $user_id
 */
function display_del($user_id)
{
    if(isset($user_id) && !empty($user_id))
    {
        // Deletion
        users_controler::delete($user_id);
        $_SESSION['error'] = _DELETED_USER.' : '.$user_id;
        // NOTE: Why not calling display_list ?
        ?><script type="text/javascript">window.top.location='<?php echo $_SESSION['config']['businessappurl']."index.php?page=users_management_controler&mode=list&admin=users&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;?>';</script>
        <?php
        exit;
    }
    else
    {
        // Error management
        $_SESSION['error'] = _USER.' '._UNKNOWN;
    }
}

/**
 * Enable given user if exists and initialize session parameters
 * @param unknown_type $user_id
 */
function display_enable($user_id)
{
    $user = users_controler::get($user_id);
    if(isset($user))
    {
        // Deletion
        users_controler::enable($user);
        $_SESSION['error'] = _AUTORIZED_USER.' : '.$user_id;
        // NOTE: Why not calling display_list ?
        ?><script type="text/javascript">window.top.location='<?php echo $_SESSION['config']['businessappurl']."index.php?page=users_management_controler&mode=list&admin=users&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;?>';</script>
        <?php
        exit;
    }
    else
    {
        // Error management
        $_SESSION['error'] = _USER.' '._UNKNOWN;
    }
}

/**
 * Disable given user if exists and initialize session parameters
 * @param unknown_type $user_id
 */
function display_disable($user_id)
{
    $user = users_controler::get($user_id);
    if(isset($user))
    {
        // Deletion
        users_controler::disable($user);
        $_SESSION['error'] = _SUSPENDED_USER.' : '.$user_id;
        // NOTE: Why not calling display_list ?
        ?><script type="text/javascript">window.top.location='<?php echo $_SESSION['config']['businessappurl']."index.php?page=users_management_controler&mode=list&admin=users&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;?>';</script>
        <?php
        exit;
    }
    else
    {
        // Error management
        $_SESSION['error'] = _USER.' '._UNKNOWN;
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
    $item['value']=functions::show_string($item['value']);
    $item[$item['column']]=$item['value'];
    $item["label"]=$label;
    $item["size"]=$size;
    $item["label_align"]=$label_align;
    $item["align"]=$align;
    $item["valign"]=$valign;
    $item["show"]=$show;
    if($order)
        $item["order"]=$item['value'];
    else
        $item["order"]='';
}

/**
 * Validate a submit (add or up),
 * up to saving object
 */
function validate_user_submit(){

    $user = new users();
    $mode = $_REQUEST['mode'];
    $user->user_id=functions::protect_string_db(functions::wash($_REQUEST['user_id'], "no", _THE_ID, 'yes', 0, 32));
    if($mode == "add")
    {
        if(isset($_SESSION['config']['userdefaultpassword']) && !empty($_SESSION['config']['userdefaultpassword']))
            $user->password = functions::protect_string_db(md5($_SESSION['config']['userdefaultpassword']));
        else
            $user->password = functions::protect_string_db(md5('maarch'));
    }

    $user->firstname = functions::protect_string_db(functions::wash($_REQUEST['FirstName'], "no", _THE_FIRSTNAME, 'yes', 0, 255));
    $user->lastname = functions::protect_string_db(functions::wash($_REQUEST['LastName'], "no", _THE_LASTNAME, 'yes', 0, 255));

    if(isset($_REQUEST['Department']) && !empty($_REQUEST['Department']))
        $user->department  = functions::protect_string_db(functions::wash($_REQUEST['Department'], "no", _DEPARTMENT, 'yes', 0, 50));

    if(isset($_REQUEST['Phone']) && !empty($_REQUEST['Phone']))
        $user->phone  = functions::protect_string_db(functions::wash($_REQUEST['Phone'], "no", _PHONE, 'yes', 0, 15));

    if(isset($_REQUEST['LoginMode']) && !empty($_REQUEST['LoginMode']))
        $user->loginmode  = functions::protect_string_db(functions::wash($_REQUEST['LoginMode'], "no", _LOGIN_MODE, 'yes', 0, 50));

    if(isset($_REQUEST['Mail']) && !empty($_REQUEST['Mail']))
        $user->mail  = functions::protect_string_db(functions::wash($_REQUEST['Mail'], "mail", _MAIL, 'yes', 0, 255));

    $status= array();
    $status['order']=$_REQUEST['order'];
    $status['order_field']=$_REQUEST['order_field'];
    $status['what']=$_REQUEST['what'];
    $status['start']=$_REQUEST['start'];

    put_in_session("status",$status);
    put_in_session("users",$user->getArray());

    if($_SESSION['m_admin']['users']['user_id'] <> "superadmin")
    {
        $primary_set = false;
        for($i=0; $i < count($_SESSION['m_admin']['users']['groups']);$i++)
        {
            if($_SESSION['m_admin']['users']['groups'][$i]['PRIMARY'] == 'Y')
            {
                $primary_set = true;
                break;
            }
        }
        if ($primary_set == false)
            $_SESSION['error'] = _PRIMARY_GROUP.' '._MANDATORY;
    }

    $_SESSION['service_tag'] = 'user_check';
    core_tools::execute_modules_services($_SESSION['modules_services'], 'user_check', "include");

    if($mode == "add" && users_controler::userExists($_SESSION['m_admin']['users']['user_id']))
    {
        $_SESSION['error'] = $_SESSION['m_admin']['users']['user_id']." "._ALREADY_EXISTS."<br />";
    }


    if(!empty($_SESSION['error']))
    {
        if($mode == "up")
        {
            if(!empty($_SESSION['m_admin']['users']['user_id']))
                header("location: ".$_SESSION['config']['businessappurl']."index.php?page=users_management_controler&mode=up&id=".$_SESSION['m_admin']['users']['user_id']."&admin=users");
            else
                header("location: ".$_SESSION['config']['businessappurl']."index.php?page=users_management_controler&mode=list&admin=users&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
        }
        elseif($mode == "add")
            header("location: ".$_SESSION['config']['businessappurl']."index.php?page=users_management_controler&mode=add&admin=users");

        exit();
    }
    else
    {
        users_controler::save($user);

        users_controler::cleanUsergroupContent($_SESSION['m_admin']['users']['user_id']);
        users_controler::loadDbUsergroupContent($_SESSION['m_admin']['users']['user_id'], $_SESSION['m_admin']['users']['groups']);

        $_SESSION['service_tag'] = 'user_'.$mode;

        core_tools::execute_modules_services($_SESSION['modules_services'], 'users_add_db', "include");

        if($_SESSION['history']['usersadd'] == "true" && $mode == "add")
        {
            require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
            $hist = new history();
            $hist->add($_SESSION['tablename']['users'], $_SESSION['m_admin']['users']['user_id'],"ADD",_USER_ADDED." : ".$_SESSION['m_admin']['users']['user_id'], $_SESSION['config']['databasetype']);
        }
        elseif($_SESSION['history']['usersup'] == "true" && $mode == "up")
        {
            require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
            $hist = new history();
            $hist->add($_SESSION['tablename']['users'], $_SESSION['m_admin']['users']['user_id'],"UP",_USER_UPDATE." : ".$_SESSION['m_admin']['users']['user_id'], $_SESSION['config']['databasetype']);
        }
        unset($_SESSION['m_admin']);
        if($mode == "add")
        {
            $_SESSION['error'] =  _USER_ADDED;
        }
        else
        {
            $_SESSION['error'] = _USER_UPDATED;
        }

        header("location: ".$_SESSION['config']['businessappurl']."index.php?page=users_management_controler&mode=list&admin=users&order=".$status['order']."&order_field=".$status['order_field']."&start=".$status['start']."&what=".$status['what']);
    }
}

function init_session()
{
    $_SESSION['m_admin']['users'] = array();
    $_SESSION['m_admin']['users']['groups'] = array();
    $_SESSION['m_admin']['users']['nbbelonginggroups'] = 0;
    $_SESSION['m_admin']['init'] = false ;
    $_SESSION['m_admin']['load_group']  = true;
}

/**
 * Put given object in session, according with given type
 * NOTE: given object needs to be at least hashable
 * @param string $type
 * @param hashable $hashable
 */
function put_in_session($type,$hashable, $show_string = true){
    foreach($hashable as $key=>$value){
        if ($show_string)
            $_SESSION['m_admin'][$type][$key]=functions::show_string($value);
        else
            $_SESSION['m_admin'][$type][$key]=$value;
    }
}
?>

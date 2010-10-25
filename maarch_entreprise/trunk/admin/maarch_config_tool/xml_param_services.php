<?php
$core_tools2 = new core_tools();
$core_tools2->test_admin('xml_param_services', 'apps');
 /****************Management of the location bar  ************/
$init = false;
if(isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == "true")
{
    $init = true;
}
$level = "";
if(isset($_REQUEST['level']) && $_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)
{
    $level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=modules_services_config&admin=maarch_config_tool';
$page_label = _XML_PARAM_SERVICE;
$page_id = "modules_services_config";
$core_tools2->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
include('modules_services_config.php');
?>
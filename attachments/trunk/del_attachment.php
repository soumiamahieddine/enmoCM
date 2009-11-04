<?php
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
require_once($_SESSION['pathtocoreclass']."class_security.php");

require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_resource.php");
$core_tools = new core_tools();
$core_tools->load_lang();

$func = new functions();

$db = new dbquery();
$db->connect();

$db->query("update ".$_SESSION['tablename']['attach_res_attachments']." set status = 'DEL' where res_id = ".$_REQUEST['id']);

if($_SESSION['history']['attachdel'] == "true")
{
	require_once($_SESSION['pathtocoreclass']."class_history.php");
	$users = new history();
	$users->add($_SESSION['tablename']['attach_res_attachments'], $_REQUEST['id'],"DEL", _ATTACH_DELETED." : ".$_REQUEST['id'], $_SESSION['config']['databasetype'],"attachments");

}

?>
<script language="javascript" type="text/javascript">
	var eleframe1 =  window.top.document.getElementById('list_attach');
	eleframe1.src = '<?php  echo $_SESSION['urltomodules']."attachments/";?>frame_list_attachments.php';
	//window.top.close();
</script>

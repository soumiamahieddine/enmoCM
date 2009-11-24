<?php

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_resource.php");
$core_tools = new core_tools();
$core_tools->load_lang();

$func = new functions();

$db = new dbquery();
$db->connect();

$db->query("update ".$_SESSION['tablename']['attach_res_attachments']." set status = 'DEL' where res_id = ".$_REQUEST['id']);

if($_SESSION['history']['attachdel'] == "true")
{
	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
	$users = new history();
	$users->add($_SESSION['tablename']['attach_res_attachments'], $_REQUEST['id'],"DEL", _ATTACH_DELETED." : ".$_REQUEST['id'], $_SESSION['config']['databasetype'],"attachments");

}

?>
<script language="javascript" type="text/javascript">
	var eleframe1 =  window.top.document.getElementById('list_attach');
	eleframe1.src = '<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=attachments&page=frame_list_attachments';
	//window.top.close();
</script>

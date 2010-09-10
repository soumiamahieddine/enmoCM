<?php

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_resource.php");
$core_tools = new core_tools();
$core_tools->load_lang();

$func = new functions();

$db = new dbquery();
$db->connect();

$db->query("UPDATE ".$_SESSION['tablename']['attach_res_attachments']." SET status = 'DEL' WHERE res_id = ".$_REQUEST['id']);

if($_SESSION['history']['attachdel'] == "true")
{
	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
	$users = new history();
	if(!isset($_SESSION['collection_id_choice']) || empty($_SESSION['collection_id_choice']))
	{
		$_SESSION['collection_id_choice'] = $_SESSION['user']['collections'][0];
	}
	$sec = new security();
	$view = $sec->retrieve_view_from_coll_id($_SESSION['collection_id_choice']);
	$db->query("SELECT res_id_master FROM ".$_SESSION['tablename']['attach_res_attachments']." WHERE res_id = ".$_REQUEST['id']);
	$res = $db->fetch_object();
	$res_id_master = $res->res_id_master;
	$users->add($view, $res_id_master,"DEL", _ATTACH_DELETED.' '._ON_DOC_NUM.$res_id_master."  (".$_REQUEST['id'].')', $_SESSION['config']['databasetype'],"attachments");
	$users->add($_SESSION['tablename']['attach_res_attachments'], $_REQUEST['id'],"DEL", _ATTACH_DELETED." : ".$_REQUEST['id'], $_SESSION['config']['databasetype'],"attachments");

}

?>
<script type="text/javascript">
	var eleframe1 =  window.top.document.getElementById('list_attach');
	eleframe1.src = '<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=attachments&page=frame_list_attachments';
	//window.top.close();
</script>

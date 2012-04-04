<?php

require_once "core/class/class_security.php";
require_once "core/class/class_request.php";
require_once "core/class/class_resource.php";
require_once "core/class/class_history.php";
require_once 'modules/attachments/attachments_tables.php';
$core = new core_tools();
$core->load_lang();

$func = new functions();

$db = new dbquery();
$db->connect();

$db->query(
	"UPDATE " . RES_ATTACHMENTS_TABLE . " SET status = 'DEL' WHERE res_id = "
    . $_REQUEST['id']
);

if ($_SESSION['history']['attachdel'] == "true") {
	$hist = new history();
	if (! isset($_SESSION['collection_id_choice'])
	    || empty($_SESSION['collection_id_choice'])
	) {
		$_SESSION['collection_id_choice'] = $_SESSION['user']['collections'][0];
	}
	$sec = new security();
	$view = $sec->retrieve_view_from_coll_id($_SESSION['collection_id_choice']);
	$db->query(
		"SELECT res_id_master FROM " . RES_ATTACHMENTS_TABLE
	    . " WHERE res_id = " . $_REQUEST['id']
	);
	$res = $db->fetch_object();
	$resIdMaster = $res->res_id_master;
	$hist->add(
	    $view, $resIdMaster, "DEL", 'attachdel', _ATTACH_DELETED . ' ' . _ON_DOC_NUM
	    . $resIdMaster . "  (" . $_REQUEST['id'] . ')',
	    $_SESSION['config']['databasetype'], "attachments"
	);
	$hist->add(
	    RES_ATTACHMENTS_TABLE, $_REQUEST['id'], "DEL", 'attachdel', _ATTACH_DELETED . " : "
	    . $_REQUEST['id'], $_SESSION['config']['databasetype'], "attachments"
	);
}
?>
<script type="text/javascript">
	var eleframe1 =  window.top.document.getElementById('list_attach');
	eleframe1.src = '<?php
echo $_SESSION['config']['businessappurl'];
?>index.php?display=true&module=attachments&page=frame_list_attachments&mode=normal';
	//window.top.close();
</script>

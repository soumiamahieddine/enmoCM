<?php
	require_once('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR 
        . 'class_db.php');
	
	$core = new core_tools();
	function showFrame($filename, $ds_path, $path){
		$frm_str = '';

		$frm_str .= '<div style="text-align:center;">';
		$frm_str .= '	<iframe src="'.$_SESSION['config']['businessappurl'].'index.php?display=true&module=visa&page=view_parapheur&filename='.$filename.'&path='.$ds_path.$path.'" name="viewframe" id="viewframe"  scrolling="auto" frameborder="0" width="1000" height="800"></iframe>';
		$frm_str .= '</div>';
		
		echo $frm_str;
	}
	$db = new dbquery();
	$db->connect();
	
    $db->query("select docserver_id, path, filename from res_view_letterbox where res_id = ".$_REQUEST['res_id']);
    $res = $db->fetch_object();
    $docserver_id = $res->docserver_id;
	
	
	$db->query("select path_template from ".$_SESSION['tablename']['docservers']." where docserver_id = '".$docserver_id."'");
    $res = $db->fetch_object();
    $docserver_path = $res->path_template;
	
	$str = "select filename, path from res_attachments where attachment_type = 'print_folder' and res_id_master= ".$_REQUEST['res_id']. " order by creation_date desc limit 1";
	$db->query($str);
	$res = $db->fetch_array();

	//echo "<pre>".print_r($res,true)."</pre>";
	
	/****************Management of the location bar  ************/
	$init = false;
	if (isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == 'true') {
		$init = true;
	}
	if (isset($_SESSION['indexation'] ) && $_SESSION['indexation'] == true) {
		$init = true;
	}
	$level = '';
	if (
		isset($_REQUEST['level'])
		&& (
			$_REQUEST['level'] == 2
			|| $_REQUEST['level'] == 3
			|| $_REQUEST['level'] == 4
			|| $_REQUEST['level'] == 1
		)
	) {
		$level = $_REQUEST['level'];
	}
	$page_path = $_SESSION['config']['businessappurl']
		. 'index.php?page=view_parapheur_controller&module=visa'
		. '&id=' . $_REQUEST['res_id'];
	$page_label = "Impression de dossier";
	$page_id = 'view_parapheur_controller';
	$core->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
?>
<h1><img src="<?php echo $_SESSION['config']['businessappurl'];
			?>static.php?module=fileplan&filename=manage_fileplan_b.gif" alt="" />
			<?php echo "Impression du dossier du document ". _NUM .$_REQUEST['res_id'] ;?></h1>
<div id="inner_content">
			<?php showFrame($res['filename'], $docserver_path, str_replace("#","/",$res['path'])); ?></div>
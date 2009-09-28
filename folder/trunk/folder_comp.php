<?php 
/**
* File : folder_comp.php
*
* Show the missing docs in a folder (used in the stats)
*
* @package  Maarch PeopleBox 1.0
* @version 2.0
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
session_name('PeopleBox'); 
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
 require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");

//require_once("class/class_search.php");
require_once($_SESSION['config']['businessapppath']."class".$_SESSION['slash_env']."class_list_show.php");
 require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
   $core_tools = new core_tools();
 if(!$core_tools->is_module_loaded("folder"))
 {
 	echo "Folder module missing !<br/>Please install this module.";
	exit();
 }
 require_once($_SESSION['pathtomodules']."folder".$_SESSION['slash_env']."class".$_SESSION['slash_env']."class_modules_tools.php");
 $core_tools->load_lang();
//here we loading the html
$core_tools->load_html();
//here we building the header
$core_tools->load_header();
$time = $core_tools->get_session_time_expire();
?>

<body id="validation_page" onLoad="setTimeout(window.close, <?php  echo $time;?>*60*1000);" >

<div align="center" width="95%">
<?php 
$id = "";
if(isset($_GET['id']) && !empty($_GET['id']))
{
	$id = $_GET['id'];
}

if(!empty($id))
{
	$missing_res = array();
	$folder = new folder();
	$folder->load_folder2($id, $_SESSION['tablename']['fold_folders']);
	$sys_id = $folder->get_field('folders_system_id');
	//$contrat = $folder->get_contract_type();
	//$missing_res = $folder->missing_res($_SESSION['ressources'][0]['tablename'], $_SESSION['tablename']['contracts_doctypes'], $_SESSION['tablename']['doctypes'], $sys_id, $contrat);		
	$foldertype_id = $folder->get_field('foldertype_id');
	$missing_res = $folder->missing_res($_SESSION['collections'][0]['table'], $_SESSION['tablename']['fold_foldertypes_doctypes'], $_SESSION['tablename']['doctypes'], $sys_id, $foldertype_id);	
	
	$tab = array();
	for($i=0; $i < count($missing_res); $i++)
	{
		$tmp2 = array('value'=>$missing_res[$i]['ID'], 'label' => _ID, 'size' => '20', 'label_align' => "center", 'align'=> "left", 'valign' => "bottom", 'show' => true, 'value_export'=>$missing_res[$i]['ID']);
		$tab[$i][0] = $tmp2;
		$tmp2 = array('value'=>$missing_res[$i]['LABEL'], 'label' => _DESC, 'size' => '30', 'label_align' => "center", 'align'=> "left", 'valign' => "bottom", 'show' => true, 'value_export'=>$missing_res[$i]['LABEL']);
		$tab[$i][1] = $tmp2;
	}

	
	$list=new list_show();
	$list->list_doc($tab,$i,_MISSING_DOC2." : ".$i." "._DOCS,'type_id',isearch_adv_result,'type_id',"",false,false,'','','',false,false,true, false, true);
}
else
{
	echo _NO_FOLDER_FOUND.".";
}
?>
<br/>

</div>
</body>
</html>
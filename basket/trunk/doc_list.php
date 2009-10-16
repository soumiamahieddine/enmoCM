<?php
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtomodules']."postindexing".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
require_once($_SESSION['pathtomodules']."basket".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
require_once($_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR."class_list_show.php");

$core_tools = new core_tools();
//here we loading the lang vars
$core_tools->test_user();

$func = new functions();

if(!$core_tools->is_module_loaded("postindexing"))
{
	echo "Postindexing module missing !<br/>Please install this module.";
	exit();
}

$db = new dbquery();
$db->connect();
$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header();

$postindexing = new postindexing();

$_SESSION['postindexing']['video_time'] = $postindexing->get_timestamp($_SESSION['tablename']['param'], $_SESSION['postindexing']['max_time_id']);
$_SESSION['postindexing']['max_batch'] = $postindexing->get_maxbatch($_SESSION['tablename']['param'], $_SESSION['postindexing']['max_files_id']);
$workbatch = $postindexing->get_workbatch($_SESSION['tablename']['param'], $_SESSION['postindexing']['workbatch_id']);

$timestamp = time();
$max_video_time = $timestamp + $_SESSION['postindexing']['video_time'];

$request= new request;

if(!empty($_SESSION['current_basket']['view']))
{
	$table = $_SESSION['current_basket']['view'];
}
else
{
	$table = $_SESSION['current_basket']['table'];
}

$select[$table]= array();

$where = " ((video_batch is null)
			or (video_batch is not null and video_user = '".$_SESSION['user']['UserId']."')
			or ((video_batch is not null and video_user <> '".$_SESSION['user']['UserId']."') and (video_batch is not null and video_time < ".$max_video_time.") )
			)
			and ".$_SESSION['current_basket']['clause'];

?>

<body >
<?php
if(isset($_REQUEST['action']) && !empty($_REQUEST['action']))
{
	$_SESSION['postindexing']['status'] = $_REQUEST['action'];
	$_SESSION['field_error'] = array();
	unset($_SESSION['postindexing']['resid_pointeur']);

	//If the user still got enough ressources
	$select[$table]= array();
	array_push($select[$table],"res_id");
	$where3 = " ((video_batch is not null and video_user = '".$_SESSION['user']['UserId']."' and video_time < ".$max_video_time.")
				or (video_batch is not null and video_time < ".$max_video_time.") )
				and (status = '".$_SESSION['postindexing']['status']."')";

	$tab1=$request->select($select,$where3,"",$_SESSION['config']['databasetype'], $_SESSION['postindexing']['max_batch'], false, "", "", "", true, true);

		//echo "COUNT: ".count($tab1);//DEBUG

	if ((count($tab1) < $_SESSION['postindexing']['max_batch']) )
	{
		//Number of ressources to add to complète the max batch files
		$max_batch = $_SESSION['postindexing']['max_batch'] - count($tab1) ;

		$_SESSION['postindexing']['nb_total'] = $_SESSION['postindexing']['max_batch'];

		$_SESSION['data'] = array();

		array_push($_SESSION['data'], array('column' => "status", 'value' => $_SESSION['postindexing']['status'], 'type' => "string"));
		array_push($_SESSION['data'], array('column' => "video_batch", 'value' => $workbatch, 'type' => "int"));
		array_push($_SESSION['data'], array('column' => "video_user", 'value' => $_SESSION['user']['UserId'], 'type' => "string"));
		array_push($_SESSION['data'], array('column' => "video_time", 'value' => $max_video_time, 'type' => "string"));

		$where2 = $where." LIMIT ".$max_batch;

		$request->update($table, $_SESSION['data'], $where2, $_SESSION['config']['databasetype']);

		$postindexing->update_workbatch($_SESSION['tablename']['param'], $_SESSION['postindexing']['workbatch_id'], $workbatch);

	}
	else
	{
		$_SESSION['postindexing']['nb_total'] = count($tab1);
	}
	unset($_SESSION['postindexing']['work']);
	unset($_SESSION['postindexing']['resid_pointeur']);
?>
	<script language="javascript" type="text/javascript">
		setTimeout(window.open('<?php echo $_SESSION['urltomodules'];?>postindexing/index_video.php','video'),2000);
		//top.reload();
	</script>
<?php
}

array_push($select[$table],"res_id","identifier","doc_date", "work_batch", "dest_user", "doc_custom_t4", "doc_custom_t3", "doc_custom_t10", "status", "type_id");

$tab=$request->select($select,$where,"",$_SESSION['config']['databasetype'], "500", false, "", "", "", true, true);

if (count($tab) > 0)
{

	?>
	<form name="frm_doc_list" id="frm_doc_list" method="post" action="#">
	<input type="hidden" name="action" value="<?php echo $_SESSION['current_basket']['actions'][0]['ID_STATUS'];?>" />
	<?php

	for ($i=0;$i<count($tab);$i++)
	{
		for ($j=0;$j<count($tab[$i]);$j++)
		{
			//echo "test : ".$tab[$i][$j]."<br/>";
			foreach(array_keys($tab[$i][$j]) as $value)
			{

				if($tab[$i][$j][$value]=="res_id")
				{
					$tab[$i][$j]["res_id"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_GED_NUM;
					$tab[$i][$j]["size"]="4";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='res_id';

				}
				if($tab[$i][$j][$value]=="identifier")
				{
					$tab[$i][$j]['identifier']=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_ID;
					$tab[$i][$j]["size"]="10";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='identifier';
				}
				if($tab[$i][$j][$value]=="doc_date")
				{
					$tab[$i][$j]["value"] = $core_tools->format_date_db($tab[$i][$j]["value"]);
					$tab[$i][$j]["label"]=_DATE;
					$tab[$i][$j]["size"]="10";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='doc_date';
				}
				if($tab[$i][$j][$value]=="doc_custom_t4")
				{
					$tab[$i][$j]["value"] = $request->show_string($tab[$i][$j]["value"]);
					$tab[$i][$j]["label"]=_CUSTOMER;
					$tab[$i][$j]["size"]="10";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='doc_custom_t4';
				}
				if($tab[$i][$j][$value]=="doc_custom_t3")
				{
					$tab[$i][$j]["value"] = $request->show_string($tab[$i][$j]["value"]);
					$tab[$i][$j]["label"]=_COUNTRY;
					$tab[$i][$j]["size"]="10";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='doc_custom_t3';
				}
				if($tab[$i][$j][$value]=="doc_custom_t10")
				{
					$tab[$i][$j]["value"] = $request->show_string($tab[$i][$j]["value"]);
					$tab[$i][$j]["label"]=_AMOUNT;
					$tab[$i][$j]["size"]="10";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='doc_custom_t10';
				}
				if($tab[$i][$j][$value]=="status")
				{
					$tab[$i][$j]["value"] = $request->show_string($tab[$i][$j]["value"]);
					$tab[$i][$j]["label"]=_STATUS;
					$tab[$i][$j]["size"]="4";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["order"]='status';

				}
			}
		}
	}

	for($coll = 0; $coll < count($_SESSION['collections']); $coll++)
	{
		if ($_SESSION['collections'][$coll]['id'] == $_SESSION['collection_id_choice'])
		{
			$script_detail = $_SESSION['collections'][$coll]['script_details'];
			$script_detail = str_replace('.php','',$script_detail);
		}
	}

	$param_list = array('values' => $tab,
						'title' =>  _PROCESS_LIST." : ".count($tab)." "._DOCS,
						'key' => 'res_id',
						'page_name' => 'doc_list',
						'what' => '',
						'detail_destination' =>  "index&module=indexing_searching&page=".$script_detail,
						'details_page' => '',
						'view_doc' => true,
						'bool_details' => true,
						'bool_order' => true,
						'bool_frame' => true,
						'module' => 'basket',
						'css' => 'listing spec'
						);

	$list = new list_show();
	$list->list_doc(
					$param_list['values'],
					count($param_list['values']),		//$nb_total total number of documents
					$param_list['title'],
					$param_list['what'],				//$what search expression
					$param_list['page_name'],
					$param_list['key'],
					$param_list['detail_destination'],
					$param_list['view_doc'],
					false,
					'',
					'' ,
					'',
					$param_list['bool_details'],
					$param_list['bool_order'],
					$param_list['bool_frame'],
					false,
					false,
					false ,
					true,
					false,
					'',
					$param_list['module'],
					false,
					'',
					'',
					$param_list['css'],
					'',
					false,
					true,
					$actions_list,
					'',
					'',
					'' ,
					$_SESSION['current_basket']['default_action']);
	//$list=new basket();
	//print_r($_SESSION['current_basket']['actions']);
	//$list->list_doc($tab,$i,$title,"res_id","list_invoices","res_id","details_invoices",true,false,"get",$_SESSION['config']['businessappurl']."index.php?page=invoices_list&module=basket",_CHOOSE, false, false, false, false,false,false,false,true,'', 'basket',false,  '', '', 'listing spec',  "", false, true);
	//$list->basket_list_doc($param_list, $_SESSION['current_basket']['actions'], _CLICK_LINE_TO_VIEW);
	?>
		<p class="buttons">
			<input class="button" name="imageField" type="submit" onclick="javascript:document.frm_doc_list.submit();" value="<?php echo _RESERVE_PACKAGE; ?>"  />
		</p>

		</form>
	<?php

}
else
{
?>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p class="error"><br /><?php echo _VIDEO_NO_RES;?></p>
<?php
}
?>
</body>
</html>

<?php  session_name('PeopleBox');
session_start();

require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");

$core_tools = new core_tools();
//here we loading the lang vars
$core_tools->load_lang();
$core_tools->test_service('manage_attachments', 'attachments');

require_once($_SESSION['pathtocoreclass']."class_db.php");
$func = new functions();

if(empty($_SESSION['collection_id_choice']))
{
	$_SESSION['collection_id_choice']= $_SESSION['user']['collections'][0];
}
$view_only = false;
if(isset($_REQUEST['view_only']))
{
	$view_only = true;
}
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR."class_list_show.php");
$func = new functions();

$select[$_SESSION['tablename']['attach_res_attachments']] = array();
array_push($select[$_SESSION['tablename']['attach_res_attachments']],"res_id","creation_date","title", "format");

$where = " res_id_master = ".$_SESSION['doc_id']." and coll_id ='".$_SESSION['collection_id_choice']."' and status <> 'DEL' ";
$request= new request;
$tab_attach=$request->select($select,$where,"",$_SESSION['config']['databasetype'], "500" );
//$request->show();
$ind_att1d = '';
for ($ind_att1=0;$ind_att1<count($tab_attach);$ind_att1++)
{
	$value_modify = false;
	for ($ind_att2=0;$ind_att2<count($tab_attach[$ind_att1]);$ind_att2++)
	{
		foreach(array_keys($tab_attach[$ind_att1][$ind_att2]) as $value)
		{
			if($tab_attach[$ind_att1][$ind_att2][$value]=="res_id")
			{
				$tab_attach[$ind_att1][$ind_att2]["res_id"]=$tab_attach[$ind_att1][$ind_att2]['value'];
				$tab_attach[$ind_att1][$ind_att2]["label"]= _ID;
				$tab_attach[$ind_att1][$ind_att2]["size"]="18";
				$tab_attach[$ind_att1][$ind_att2]["label_align"]="left";
				$tab_attach[$ind_att1][$ind_att2]["align"]="left";
				$tab_attach[$ind_att1][$ind_att2]["valign"]="bottom";
				$tab_attach[$ind_att1][$ind_att2]["show"]=false;
				$ind_att1d = $tab_attach[$ind_att1][$ind_att2]['value'];
			}
			if($tab_attach[$ind_att1][$ind_att2][$value]=="title")
			{
				$tab_attach[$ind_att1][$ind_att2]["title"]=$tab_attach[$ind_att1][$ind_att2]['value'];
				$tab_attach[$ind_att1][$ind_att2]["label"]= _TITLE;
				$tab_attach[$ind_att1][$ind_att2]["size"]="30";
				$tab_attach[$ind_att1][$ind_att2]["label_align"]="left";
				$tab_attach[$ind_att1][$ind_att2]["align"]="left";
				$tab_attach[$ind_att1][$ind_att2]["valign"]="bottom";
				$tab_attach[$ind_att1][$ind_att2]["show"]=true;
			}
			if($tab_attach[$ind_att1][$ind_att2][$value]=="creation_date")
			{
				$tab_attach[$ind_att1][$ind_att2]['value']=$request->format_date_db($tab_attach[$ind_att1][$ind_att2]['value'], true);
				$tab_attach[$ind_att1][$ind_att2]["creation_date"]=$tab_attach[$ind_att1][$ind_att2]['value'];
				$tab_attach[$ind_att1][$ind_att2]["label"]=_DATE;
				$tab_attach[$ind_att1][$ind_att2]["size"]="30";
				$tab_attach[$ind_att1][$ind_att2]["label_align"]="left";
				$tab_attach[$ind_att1][$ind_att2]["align"]="left";
				$tab_attach[$ind_att1][$ind_att2]["valign"]="bottom";
				$tab_attach[$ind_att1][$ind_att2]["show"]=true;
			}
			if($tab_attach[$ind_att1][$ind_att2][$value]=="format")
			{
				$tab_attach[$ind_att1][$ind_att2]['value']=$request->show_string($tab_attach[$ind_att1][$ind_att2]['value']);
				$tab_attach[$ind_att1][$ind_att2]["format"]=$tab_attach[$ind_att1][$ind_att2]['value'];
				$tab_attach[$ind_att1][$ind_att2]["label"]=_FORMAT;
				$tab_attach[$ind_att1][$ind_att2]["size"]="5";
				$tab_attach[$ind_att1][$ind_att2]["label_align"]="left";
				$tab_attach[$ind_att1][$ind_att2]["align"]="left";
				$tab_attach[$ind_att1][$ind_att2]["valign"]="bottom";
				$tab_attach[$ind_att1][$ind_att2]["show"]=false;

				if($tab_attach[$ind_att1][$ind_att2]['value'] == "maarch")
				{
					$value_modify = true;
				}

			}
		}
	}
	if(!$view_only)
	{
		$tmp = array('column' => 'modify_item', 'value'=>$value_modify, 'label' =>  _MODIFY, 'size' => '22', 'label_align' => "right", 'align'=> "center", 'valign' => "bottom", 'show' => false);
		array_push($tab_attach[$ind_att1], $tmp);

		$tmp2 = array('column' => 'delete_item','value'=>true, 'label' =>  _DELETE, 'size' => '22', 'label_align' => "right", 'align'=> "center", 'valign' => "bottom", 'show' => false);
		array_push($tab_attach[$ind_att1], $tmp2);
	}
}

//$request->show_array($tab_attach);
//here we loading the html
$core_tools->load_html();
//here we building the header
$core_tools->load_header();
?>
<body id="iframe">
 <?php
$list_attach = new list_show();

	$list_attach->list_simple($tab_attach, count($tab_attach), '','res_id','res_id', true, $_SESSION['urltomodules']."attachments/view_attachment.php",'listingsmall',$_SESSION['urltomodules']."templates/generate_attachment.php?mode=up",450,  500, $page_del = $_SESSION['urltomodules']."attachments/del_attachment.php");

?></body>
</html>
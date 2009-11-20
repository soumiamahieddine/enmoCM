<?php
include('core/init.php');


require_once("core/class/class_functions.php");
require_once("core/class/class_core_tools.php");

$core_tools = new core_tools();
//here we loading the lang vars
$core_tools->load_lang();
$core_tools->test_service('view_attachments', 'attachments');
require_once("core/class/class_db.php");
$func = new functions();

if(empty($_SESSION['collection_id_choice']))
{
	$_SESSION['collection_id_choice']= $_SESSION['user']['collections'][0];
}
require_once("core/class/class_request.php");
require_once("apps/".$_SESSION['businessapps'][0]['appid']."/class".DIRECTORY_SEPARATOR."class_list_show.php");
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
				$tab_attach[$ind_att1][$ind_att2]['value']=$request->show_string($tab_attach[$ind_att1][$ind_att2]['value']);
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
				$tab_attach[$ind_att1][$ind_att2]["size"]="30";
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
}
//$request->show_array($tab_attach);
//here we loading the html
//$core_tools->load_html();
//here we building the header
//$core_tools->load_header();
?>
<h2 onclick="change(100)" id="h2100" class="categorie">
	<img src="<?php  echo $_SESSION['config']['businessappurl'].$_SESSION['config']['img'];?>/plus.png" alt="" />&nbsp;<b><?php  echo _ATTACHMENTS;?> :</b>
	<span class="lb1-details">&nbsp;</span>
</h2>
<br>
<div class="desc" id="desc100" style="display:none">
	<div class="ref-unit">
   <?php
	$list_attach = new list_show();
	$list_attach->list_simple($tab_attach, count($tab_attach), '','res_id','res_id', true, $_SESSION['urltomodules']."attachments/view_attachment.php",'listing2', '',450,  500, '');
	?>
   </div>
</div>

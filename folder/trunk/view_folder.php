<?php
/**
* File : view_folder.php
*
* Search by folder and show folder data
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
include('core/init.php');

require_once("core/class/class_request.php");
require_once("apps/".$_SESSION['businessapps'][0]['appid']."/class".DIRECTORY_SEPARATOR."class_list_show.php");
require_once("core/class/class_core_tools.php");
$core_tools = new core_tools();
$lastname = '';
if(!$core_tools->is_module_loaded("folder"))
{
	echo "Folder module missing !<br/>Please install this module.";
	exit();
}
if(!$core_tools->is_module_loaded("indexing_searching"))
{
	echo "Indexing Searching module missing !<br/>Please install this module.";
	exit();
}
$core_tools->test_service('folder_search', 'folder');
/****************Management of the location bar  ************/
$init = false;
if($_REQUEST['reinit'] == "true")
{
	$init = true;
}
$level = "";
if($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)
{
	$level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=view_folder&module=folder';
$page_label = _SEARCH_FOLDER;
$page_id = "fold_view_folder";
$core_tools->manage_location_bar($page_path, $page_label,$page_id, $init, $level);
/***********************************************************/
require_once("modules/folder".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
require_once("modules/indexing_searching".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
require_once("modules/folder".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_folders_show.php");
require_once("core/class/class_history.php");
require_once("core/class/class_security.php");
$security = new security();
$users = new history();
$func = new functions();
$connexion = new dbquery();
$connexion->connect();
$folder_show=new folders_show();
$folder_object=new folder();
$is = new indexing_searching();
$_SESSION["unique_res_id"] = "";
$_SESSION['origin'] = 'view_folder';
$_SESSION['current_foldertype'] = '';
if($database_type == "SQLSERVER")
{
	$_SESSION['date_pattern'] = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";
}
else // MYSQL & POSTGRESQL
{
	 $_SESSION['date_pattern'] = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";
}
if(isset($_REQUEST['coll_id']) && !empty($_REQUEST['coll_id']))
{
	$_SESSION['collection_id_choice'] = trim($_REQUEST['coll_id']);
	$_SESSION['collection_choice'] = $security->retrieve_table_from_coll($_SESSION['collection_id_choice']);
}
if(trim($_REQUEST['field']) <> "")
{
	if($_GET['origin'] == "res_type")
	{
		$_SESSION["unique_res_id"] = $_REQUEST['field'];
	}
	else
	{
		if($_REQUEST['morethantwodocs'] <> "ok")
		{
			$_SESSION['FOLDER']['SEARCH']['FOLDER_ID'] = $_REQUEST['field'];
			$_SESSION['FOLDER']['SEARCH']['FOLDER_NUM'] = '';
			$_SESSION['FOLDER']['SEARCH']['CUSTOM_T1'] = '';
		}
		else
		{
			$_SESSION["unique_res_id"] = $_REQUEST['field'];
		}
	}
}
elseif(isset($_SESSION['current_folder_id']) && $_SESSION['current_folder_id']<> " " && !empty($_SESSION['current_folder_id']) && (!isset($_SESSION['FOLDER']['SEARCH']['FOLDER_NUM']) || empty($_SESSION['FOLDER']['SEARCH']['FOLDER_NUM'])) && (!isset($_SESSION['FOLDER']['SEARCH']['FOLDER_CUSTOM_T1']) || empty($_SESSION['FOLDER']['SEARCH']['FOLDER_CUSTOM_T1']))&& empty($_SESSION['FOLDER']['SEARCH']['FOLDER_ID'] ) && $_SESSION['FOLDER']['SEARCH']['FOLDER_ID'] <> " " )
{
	//$folder_object->connect();
	//$folder_object->query("select folder_id from ".$_SESSION['tablename']['fold_folders']." where ".$_SESSION['tablename']['fold_folders'].".folders_system_id = ".$_SESSION['current_folder_id']." and ".$_SESSION['tablename']['fold_folders'].".status <> 'IMP' and ".$_SESSION['tablename']['fold_folders'].".status <> 'DEL'");
	//$folder_object->show();
	//$res = $folder_object->fetch_object();
	$_SESSION['FOLDER']['SEARCH']['FOLDER_ID'] = $_SESSION['current_folder_id'];
}
if(trim($_GET['type_id'])<>"")
{
	$_SESSION['FOLDER']['SEARCH']['TYPE_ID'] = $_GET['type_id'];
}
if(isset($_REQUEST['res_id']) && !empty($_REQUEST['res_id']))
{
	$_SESSION["unique_res_id"] = trim($_REQUEST['res_id']);
}
$_SESSION['FOLDER_ID_BACK'] = $_SESSION['FOLDER']['SEARCH']['FOLDER_ID'];
//$docserver = new docserver($_SESSION['tablename']['docservers'],'fr');
$request= new request;
$select[$_SESSION['tablename']['fold_folders']] = array();
array_push($select[$_SESSION['tablename']['fold_folders']],"folders_system_id","folder_id","custom_t1","custom_t2","custom_d1", "custom_t10");
$select[$_SESSION['tablename']['society']]= array();
array_push($select[$_SESSION['tablename']['society']],"society_label");
if($_SESSION['config']['databasetype'] == "POSTGRESQL")
{
	$where = " ".$_SESSION['tablename']['fold_folders'].".custom_t10 = ".$_SESSION['tablename']['society'].".society_sysinfo_id  and ".$_SESSION['tablename']['fold_folders'].".status <> 'DEL' ";
}
else
{
	$where = " ".$_SESSION['tablename']['fold_folders'].".custom_t10 = ".$_SESSION['tablename']['society'].".society_sysinfo_id  and ".$_SESSION['tablename']['fold_folders'].".status <> 'DEL' ";
}
if(trim($_SESSION['FOLDER']['SEARCH']['FOLDER_NUM'])<>"")
{
	if($_SESSION['config']['databasetype'] == "POSTGRESQL")
	{
		$where .= " and ".$_SESSION['tablename']['fold_folders'].".folder_id ilike '%".$func->protect_string_db($_SESSION['FOLDER']['SEARCH']['FOLDER_NUM'],$_SESSION['config']['databasetype'])."%' ";
	}
	else
	{
		$where .= " and ".$_SESSION['tablename']['fold_folders'].".folder_id like '%".$func->protect_string_db($_SESSION['FOLDER']['SEARCH']['FOLDER_NUM'],$_SESSION['config']['databasetype'])."%' ";
	}
}
if($_SESSION['FOLDER']['SEARCH']['CUSTOM_T1']<>"")
{
	if($_SESSION['config']['databasetype'] == "POSTGRESQL")
	{
		$where .= " and custom_t1 ilike '".$func->protect_string_db($_SESSION['FOLDER']['SEARCH']['CUSTOM_T1'],$_SESSION['config']['databasetype'])."%' ";
	}
	else
	{
		$where .= "  and custom_t1 like '".$func->protect_string_db($_SESSION['FOLDER']['SEARCH']['CUSTOM_T1'],$_SESSION['config']['databasetype'])."%' ";
	}
}
if(trim($_SESSION['FOLDER']['SEARCH']['FOLDER_NUM'])<>"" || $_SESSION['FOLDER']['SEARCH']['CUSTOM_T1']<>"")
{
$tab=$request->select($select,$where ," order by custom_t1 ",$_SESSION['config']['databasetype'],"10");
	//$request->show();
 	//$_SESSION['current_folder_id'] = $_REQUEST['folder_id'];
	for ($i=0;$i<count($tab);$i++)
	{
		for ($j=0;$j<count($tab[$i]);$j++)
		{
			foreach(array_keys($tab[$i][$j]) as $value)
			{
				if($tab[$i][$j][$value]=="folder_id")
				{
					$tab[$i][$j]["folder_id"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_MATRICULE;
					$tab[$i][$j]["size"]="10";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="center";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
				if($tab[$i][$j][$value]=="folders_system_id")
				{
					$tab[$i][$j]["folders_system_id"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]="folders_system_id";
					$tab[$i][$j]["size"]="4";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="center";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=false;
				}
				if($tab[$i][$j][$value]=="custom_t1")
				{
					$tab[$i][$j]["value"]=$request->show_string($tab[$i][$j]["value"]);
					$tab[$i][$j]["label"]=_LASTNAME;
					$tab[$i][$j]["size"]="15";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
				if($tab[$i][$j][$value]=="custom_t2")
				{
					$tab[$i][$j]["value"]=$request->show_string($tab[$i][$j]["value"]);
					$tab[$i][$j]["label"]=_FIRSTNAME;
					$tab[$i][$j]["size"]="15";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
				if($tab[$i][$j][$value]=="custom_t10")
				{
					$tab[$i][$j]["custom_t10"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_ID." "._SOCIETY;
					$tab[$i][$j]["size"]="2";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=false;
				}
				if($tab[$i][$j][$value]=="society_label")
				{
					$tab[$i][$j]["value"]=$request->show_string($tab[$i][$j]["value"]);
					$tab[$i][$j]["label"]=_SOCIETY;
					$tab[$i][$j]["size"]="2";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
			}
		}
	}
	//$request->show_array($tab);
	$show_list = false;
	if(count($tab) > 1)
	{
		$show_list = true;
	}
	else
	{
		$_SESSION['FOLDER']['SEARCH']['FOLDER_ID'] = $tab[0][0]["folders_system_id"];
	}
	$_SESSION['FOLDER']['SEARCH']['CUSTOM_T1'] = '';
	$_SESSION['FOLDER']['SEARCH']['FOLDER_NUM'] = '';
}
//get the var to update the folder
if($_REQUEST['folder_index'] == "true")
{
	$data = array();
	foreach(array_keys($_REQUEST) as $value)
	{
		if($value <> "submit" && !$folder_object->is_mandatory_field($value))
		{
			//echo $value." ".$_GET[$value]."<br/>";
			if($folder_object->is_mandatory($value))
			{
				if(empty($_REQUEST[$value]))
				{
					$_SESSION['error'] .= $folder_object->retrieve_index_label($value)." "._MANDATORY.".<br/>";
					$_SESSION['field_error'][$value] = true;
					//echo $_SESSION['error'];
				}
			}
			if(!empty($_REQUEST[$value]))
			{
				$data = $folder_object->user_exit($value, $data);
			}
		}
	}
	//print_r($data);
	$folder_object->load_folder($_SESSION['FOLDER']['SEARCH']['FOLDER_ID'], $_SESSION['tablename']['fold_folders']);
	$status = $folder_object->get_field('status');
	if(isset($_REQUEST['custom_t4']) && !empty($_REQUEST['custom_t4']))
	{
		$tmp_contrat = $folder_object->get_field('custom_t4', true);
		$users->query('select contract_label from '.$_SESSION['tablename']['contracts']." where contract_id = ".$tmp_contrat);
		$res = $users->fetch_object();
		$old_contract = $res->contract_label;
		$contrat_label = '';
		$users->query("select contract_label as label from ".$_SESSION['tablename']['contracts']." where contract_id = ".$_REQUEST['custom_t4']);
		$res = $users->fetch_object();
		$contrat_label = $res->label;
		if($tmp_contrat <> $_REQUEST['custom_t4'])
		{
			$users->add($_SESSION['tablename']['fold_folders'],$folder_object->get_field('folders_system_id')  ,"UP", _MODIF_CONTRACT." : ".$contrat_label, $_SESSION['config']['databasetype'],'folder');
			$users->add($_SESSION['tablename']['fold_folders'],$folder_object->get_field('folders_system_id')  ,"UP", _MODIF_CONTRACT." : ".$old_contrat." -> ".$contrat_label, $_SESSION['config']['databasetype'], 'folder');
		}
	}
	$where = "folders_system_id = ".$_SESSION['FOLDER']['SEARCH']['FOLDER_ID']."";
	$request->update($_SESSION['tablename']['fold_folders'], $data,$where, $_SESSION['config']['databasetpe']);
	if($_SESSION['history']['folderup'] == 'true')
	{
		$users->add($_SESSION['tablename']['fold_folders'],$tmp_id ,"UP", _FOLDER_INDEX_MODIF, $_SESSION['config']['databasetype'],'folder');
	}
}
else
{
	if($_REQUEST['up_folder_boolean'])
	{
		echo _MISSING_FIELDS.".";
	}
}
if(isset($_REQUEST['submit_index_doc']))
{
	$db_type = new dbquery();
	$db_type->connect();
	$db_res = new dbquery();
	$db_res->connect();
	$db_res->query("select type_id  from ".$_SESSION['collection_choice']." where res_id = ".$_SESSION["unique_res_id"]."");
	$res_type_id = $db_res->fetch_array();
	$type_id = $res_type_id['type_id'];
	$_SESSION['type'] = $type_id;
	$db_type->query("select * from ".$_SESSION['tablename']['doctypes']." where type_id = ".$type_id);
	$res_type = $db_type->fetch_array();
	$type_id = $res_type['type_id'];
	/*$is_master_type = $res_type['is_master'];
	if($is_master_type == "Y")
	{
		$is_master_type = true;
	}
	else
	{
		$is_master_type = false;
	}*/
	$indexing = new indexing_searching();
	$indexing->update_doc($_REQUEST, "POST", $_SESSION["unique_res_id"], $_SESSION['collection_choice']);
}
//delete the doctype
if(isset($_REQUEST['delete_doc']) && !empty($_REQUEST['coll_id']))
{
	$db_type = new dbquery();
	$db_type->connect();
	$db_res = new dbquery();
	$db_res->connect();
	$db_res->query("select type_id  from ".$_SESSION['collection_choice']." where res_id = ".$_SESSION["unique_res_id"]."");
	$res_type_id = $db_res->fetch_array();
	$type_id = $res_type_id['type_id'];
	$_SESSION['type'] = $type_id;
	$db_type->query("select * from ".$_SESSION['tablename']['doctypes']." where type_id = ".$type_id);
	$res_type = $db_type->fetch_array();
	$type_id = $res_type['type_id'];
	/*$is_master_type = $res_type['is_master'];
	if($is_master_type == "Y")
	{
		$is_master_type = true;
	}
	else
	{
		$is_master_type = false;
	}*/
	$indexing = new indexing_searching();
	$indexing->delete_doc( $_SESSION["unique_res_id"], $_SESSION['collection_choice'];
	?>
	<script language="javascript" type="text/javascript">window.top.location.href='<?php  echo $_SESSION['config']['businessappurl'].'index.php?page=view_folder&module=folder&coll_id='.$_REQUEST['coll_id'].'&type_id='.$_SESSION['type'];?>';</script>
    <?php
	exit();
}
?>
<h1><img src="<?php  echo $_SESSION['urltomodules']."folder/img/view_folder_b.gif";?>" alt="" /> <?php  echo strtoupper(_SEARCH_FOLDER);?></h1>
<div id="inner_content">
<table width="100%" border="0">
	<tr>
		<td valign="top" ><div id="folder_tree" >
			<?php  $folder_show->construct_tree();?>
            </div>
		</td>
		<td valign="top">
            <div id="folder_search2">
                <div id="form1">
                    <table border="0" width="100%">
                        <tr>
                            <td width="45%"><iframe name="choose_foldertype2" id="choose_foldertype2" src="<?php  echo $_SESSION['urltomodules']."folder/choose_foldertype2.php";?>" frameborder="0" width="300" height="60"></iframe></td>
                            <td><iframe name="search_folder" id="search_folder" src="<?php  echo $_SESSION['urltomodules']."folder/search_folder.php";?>" frameborder="0" width="100%" height="80" scrolling="no"></iframe></td>
                        </tr>
                    </table>
                </div>
                <div <?php  if(isset($_SESSION['FOLDER']['SEARCH']['FOLDER_ID']) && !empty($_SESSION['FOLDER']['SEARCH']['FOLDER_ID'])){ echo 'id="form2"'; }?>>
					<?php
                    if($show_list)
                    {
                        $list=new list_show();
                        $list->list_doc($tab,$i,_SEARCH_RESULTS." : ".$i." "._FOUND_FOLDERS,"folder_id","view_folder","folder_id","folder_detail",false,true,"post","index.php?page=view_folder&amp;module=folder",_CHOOSE, false, false, false, false, false, false, false);
                        echo '<br/>';
                    }
                    else
                    {
                        if($_SESSION['FOLDER']['SEARCH']['FOLDER_ID']<>"")
                        {

							$folder_object->load_folder($_SESSION['FOLDER']['SEARCH']['FOLDER_ID'],$_SESSION['tablename']['fold_folders']);
                            $status = $folder_object->get_field('status');
                            if($status == 'DEL')
                            {
                                echo "</br><p class=\"error\"><img src=\"".$_SESSION['config']['businessappurl']."img/noresult.gif\" /><br />"._NO_FOLDER_FOUND.".</p>";
                            }
                            else
                            {
                                $folder_array = array();
                                $folder_array = $folder_object->get_folder_info();
                                $_SESSION['current_folder_id'] = $folder_array['system_id'];
                                $folder_object->modify_default_folder_in_db($_SESSION['current_folder_id'], $_SESSION['user']['UserId'], $_SESSION['tablename']['users']);
                                for($i=0; $i<count($folder_array['index']);$i++)
                                {
                                    if($folder_array['index'][$i]['column'] == 'custom_t1')
                                    {
                                        $lastname = $folder_array['index'][$i]['value'];
                                        break;
                                    }
                                }
                                if($lastname <>"")
                                {
									 if($_SESSION['history']['folderview'] == "true")
									{
										$users->add($_SESSION['tablename']['fold_folders'], $_SESSION['current_folder_id'] ,"VIEW", _VIEW_FOLDER." ".strtolower(_NUM).$folder_array['folder_id'], $_SESSION['config']['databasetype'],'folder');
									}
                                    $folder_show->view_folder_info_details($folder_array,"index.php?page=view_folder&amp;module=folder");
                                    echo "<hr/>";
                                }
                                else
                                {
                                    echo "</br><p class=\"error\"><img src=\"".$_SESSION['config']['businessappurl']."img/noresult.gif\" /><br />"._NO_FOLDER_FOUND.".</p>";
                                }
                            }
                        }
                        if($_SESSION['current_folder_id'] <> "" && isset($_SESSION['FOLDER']['SEARCH']['TYPE_ID']) && !empty($_SESSION['FOLDER']['SEARCH']['TYPE_ID']))
                        {
                        	?>
                            <h2><?php  echo _INFOS_PIECE;?> :</h2>
                        	<?php
                        }
                    }
                    if($lastname<>"" && isset($_SESSION['FOLDER']['SEARCH']['TYPE_ID'])&& !empty($_SESSION['FOLDER']['SEARCH']['TYPE_ID']))
                    {

                        if(!isset($_SESSION['unique_res_id']) || empty($_SESSION['unique_res_id']))
                        {
                            $view = $security->retrieve_view_from_table($_SESSION['collection_choice']);
                            $select2[$view]= array();
							array_push($select2[$view],"res_id","type_label","creation_date");
							$tab = $request->select($select2, "folders_system_id = ".$_SESSION['current_folder_id']." and type_id = ".$_SESSION['FOLDER']['SEARCH']['TYPE_ID']." and status <> 'DEL' ", "", $_SESSION['config']['databasetype'],"10");
                        }
                        else
                        {
							$view = $security->retrieve_view_from_table($_SESSION['collection_choice']);
                            $select2[$view]= array();
							//echo 'test '.$_SESSION['unique_res_id']." ".$view;
							array_push($select2[$view],"res_id","type_label","creation_date");
							$tab = $request->select($select2, " res_id = ".$_SESSION['unique_res_id'], "", $_SESSION['config']['databasetype'],"10");
                        }
                        for ($i=0;$i<count($tab);$i++)
                        {
                            for ($j=0;$j<count($tab[$i]);$j++)
                            {
                                foreach(array_keys($tab[$i][$j]) as $value)
                                {
                                    if($tab[$i][$j][$value]=="res_id")
                                    {
                                        $tab[$i][$j]["res_id"]=$tab[$i][$j]["value"];
                                        $tab[$i][$j]["label"]=_GED_NUM;
                                        $tab[$i][$j]["size"]="30";
                                        $tab[$i][$j]["label_align"]="left";
                                        $tab[$i][$j]["align"]="center";
                                        $tab[$i][$j]["valign"]="bottom";
                                        $tab[$i][$j]["show"]=true;
                                    }
                                    if($tab[$i][$j][$value]=="type_label")
                                    {
                                        $tab[$i][$j]["value"]=$tab[$i][$j]["value"];
                                        $tab[$i][$j]["label"]=_PIECE_TYPE;
                                        $tab[$i][$j]["size"]="30";
                                        $tab[$i][$j]["label_align"]="left";
                                        $tab[$i][$j]["align"]="left";
                                        $tab[$i][$j]["valign"]="bottom";
                                        $tab[$i][$j]["show"]=true;
                                    }
                                    if($tab[$i][$j][$value]=="creation_date")
                                    {
                                         $tab[$i][$j]["value"] = $this->format_date_db($tab[$i][$j]["value"], "/");
                                        $tab[$i][$j]["label"]=_SAVE_DATE;
                                        $tab[$i][$j]["size"]="30";
                                        $tab[$i][$j]["label_align"]="left";
                                        $tab[$i][$j]["align"]="left";
                                        $tab[$i][$j]["valign"]="bottom";
                                        $tab[$i][$j]["show"]=true;
                                    }
                                }
                            }
                        }

                        if(count($tab) == 1)
                        {
                            $_SESSION['unique_res_id'] = $tab[0][0]['value'];
                            if($lastname<>"" && isset($_SESSION['FOLDER']['SEARCH']['TYPE_ID'])&& !empty($_SESSION['FOLDER']['SEARCH']['TYPE_ID']))
                            {
                                $type_id = $_SESSION['FOLDER']['SEARCH']['TYPE_ID'];
                                if($type_id <> "0" && $type_id <> "")
                                {
                                    $connexion->query("select * from ".$_SESSION['tablename']['doctypes']." where type_id = ".$type_id);
                                    $res = $connexion->fetch_array();
                                    $desc = str_replace("\\","",$res['description']);
                                    $type_id = $res['type_id'];
                                /*    $is_master = $res['is_master'];
                                    if($is_master == "Y")
                                    {
                                        $doctypes_second_level_id = $res['doctypes_second_level_id'];
                                        $_SESSION['multidoc'] = true;
                                    }*/
                                    $indexing_searching = new indexing_searching();
                                    $indexing_searching->retrieve_index($res);
                                    //$func->show_array($_SESSION['index_to_use']);
                                    ?>
                                    <form method="post" name="index_doc" action="index.php?page=view_folder&amp;module=folder&amp;id=<?php  echo $_SESSION['unique_res_id'];?>" class="forms">
                                    <p>&nbsp;</p>
                                    <p>
                                        <label><?php  echo _PIECE_TYPE;?> :</label>
                                        <input type="text" readonly="readonly" class="readonly" value="<?php  echo $desc; ?>" />
                                    </p>
                                    <?php
                                    $db = new dbquery();
                                    $db->connect();
                                  /*  if($is_master == "Y")
                                    {
                                        $array_doctypes = array();
                                        $array_doctypes_exists = array();
                                        $connexion->query("select * from ".$_SESSION['tablename']['doctypes']." where doctypes_second_level_id = ".$doctypes_second_level_id);
                                        while($res = $connexion->fetch_object())
                                        {
                                            array_push($array_doctypes, array("TYPE_ID" => $res->type_id, "DESCRIPTION" => $connexion->show_string($res->description)));
                                        }
                                        //$func->show_array($array_doctypes);
                                        $connexion->query("select * from ".$_SESSION['collection_choice']." where folders_system_id = ".$_SESSION['current_folder_id']." and envelop_id = ".$_SESSION['FOLDER']['SEARCH']['TYPE_ID']." and status <> 'DEL'");
                                        $count_doc_exist = $connexion->nb_result();
                                        if($count_doc_exist > 0)
                                        {
                                            while($res2 = $connexion->fetch_object())
                                            {
                                                array_push($array_doctypes_exists, array("TYPE_ID" => $res2->type_id, "RES_ID" => $res2->res_id));
                                                if($res2->relation <> "")
                                                {
                                                    $_SESSION['masterdoctype_res_id'] = $res2->relation;
                                                    $_SESSION['masterdoctype_exists'] = true;
                                                }
                                            }
                                            //$func->show_array($array_doctypes_exists);
                                            echo "<b>"._DOC_ALREADY_PRESENT."</b><br/><br/>";
                                            for($i=0;$i<count($array_doctypes_exists);$i++)
                                            {
                                                if($array_doctypes_exists[$i]['TYPE_ID'] <> $type_id)
                                                {
                                                    $connexion->query("select * from ".$_SESSION['tablename']['doctypes']." where type_id = ".$array_doctypes_exists[$i]['TYPE_ID']);
                                                    $res_doctypes = $connexion->fetch_array();
                                                    $desc_exists = $connexion->show_string($res_doctypes['description']);
                                                    ?>
                                                    <p>
                                                        <label>
                                                            <a onclick="javascript:window.open('<?php  echo $_SESSION['urltomodules'];?>indexing_searching/view.php?id=<?php  echo $array_doctypes_exists[$i]['RES_ID'];?>','_blank');"><?php  echo $desc_exists;?></a> :
                                                        </label>
                                                        <!--<input type="checkbox" name="docexists" checked disabled/>&nbsp;--><img src="img/picto_dld.gif" onclick="javascript:window.open('<?php  echo $_SESSION['urltomodules'];?>indexing_searching/view.php?id=<?php  echo $array_doctypes_exists[$i]['RES_ID'];?>','_blank');"/>
                                                    </p>
                                                    <?php
                                                }
                                            }
                                        }
                                        $array_doctypes_not_indexed = array();
                                        $cpt_find_element = 0;
                                        for($z=0;$z<count($array_doctypes);$z++)
                                        {
                                            //echo "<br/>".$array_doctypes[$z]["TYPE_ID"]."<br/>";
                                            $find_element = false;
                                            for($h=0;$h<=count($array_doctypes_exists);$h++)
                                            {
                                                if($array_doctypes[$z]["TYPE_ID"] == $array_doctypes_exists[$h]['TYPE_ID'])
                                                {
                                                    $find_element = true;
                                                }
                                            }
                                            if(!$find_element)
                                            {
                                                $array_doctypes_not_indexed[$cpt_find_element] = $array_doctypes[$z];
                                                $cpt_find_element ++;
                                            }
                                        }
                                        //$func->show_array($array_doctypes_not_indexed);
                                        //echo "test ".count($array_doctypes_not_indexed);
                                        if((count($array_doctypes_not_indexed) > 1 && count($array_doctypes_not_indexed) <> "") && $is_master == "Y")
                                        {
                                            echo "<b>"._DOC_TO_ADD_FOR." ".$desc."</b><br/><br/>";
                                            for($i=0;$i<=count($array_doctypes_not_indexed);$i++)
                                            {
                                                if($array_doctypes_not_indexed[$i]['TYPE_ID'] <> $type_id && $array_doctypes_not_indexed[$i]['TYPE_ID'] <> "")
                                                {
                                                    ?>
                                                    <p>
                                                        <label>
                                                            <?php  echo $connexion->show_string($array_doctypes_not_indexed[$i]['DESCRIPTION']);?> :
                                                        </label>
                                                        <input type="checkbox" disabled="disabled" name="doctype_<?php  echo $array_doctypes_not_indexed[$i]['TYPE_ID'];?>"/>
                                                    </p>
                                                    <?php
                                                }
                                            }
                                        }
                                        elseif((count($array_doctypes_not_indexed) < 2 && count($array_doctypes_not_indexed) <> "")  && $is_master == "Y")
                                        {
                                            echo "<i style='color:#FF0000'>".$desc." "._COMPLETE."</i><br/>";
                                            $is_folder_complete = true;
                                        }
                                    }*/
                                /*    if(!$is_folder_complete && $is_master == "Y")
                                    {
										echo "<b>"._INDEX."</b><br/><br/>";
										for($i=0;$i<=count($_SESSION['index_to_use']);$i++)
										{
											if($_SESSION['index_to_use'][$i]['label'] <> "")
											{
												if($_SESSION['masterdoctype_res_id'] <> "")
												{
													$connexion->query("select ".$_SESSION['index_to_use'][$i]['column']." from ".$_SESSION['collection_choice']." where res_id = ".$_SESSION['masterdoctype_res_id']);
													$res_mastertype = $connexion->fetch_array();
													$_SESSION['indexing'][$_SESSION['index_to_use'][$i]['column']] = $res_mastertype[$_SESSION['index_to_use'][$i]['column']];
													if($_SESSION['index_to_use'][$i]['date'])
													{
														$_SESSION['indexing'][$_SESSION['index_to_use'][$i]['column']] = $func->format_date_db($_SESSION['indexing'][$_SESSION['index_to_use'][$i]['column']], false);
													}
												}
												if((isset($_SESSION['index_to_use'][$i]['foreign_key']) && !empty($_SESSION['index_to_use'][$i]['foreign_key']) && isset($_SESSION['index_to_use'][$i]['foreign_label']) && !empty($_SESSION['index_to_use'][$i]['foreign_label']) && isset($_SESSION['index_to_use'][$i]['tablename']) && !empty($_SESSION['index_to_use'][$i]['tablename'])) || (isset($_SESSION['index_to_use'][$i]['values']) && count($_SESSION['index_to_use'][$i]['values']) > 0))
												{
													?>
												<p>
													<label>
													<?php
													if($_SESSION['index_to_use'][$i]['mandatory'])
													{
														echo "<b>".$_SESSION['index_to_use'][$i]['label']."</b> : ";
													}
													else
													{
														echo $_SESSION['index_to_use'][$i]['label']." : ";
													}
													?>
												</label>
												<select name="doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>" id="doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>">
												<option value=""><?php  echo _CHOOSE;?></option>
												<?php
												if(isset($_SESSION['index_to_use'][$i]['values']) && count($_SESSION['index_to_use'][$i]['values']) > 0)
												{
													for($k=0; $k < count($_SESSION['index_to_use'][$i]['values']); $k++)
													{
													?>
														<option value="<?php  echo $_SESSION['index_to_use'][$i]['values'][$k]['label'];?>" <?php  if($_SESSION['indexing'][$_SESSION['index_to_use'][$i]['column']] == $_SESSION['index_to_use'][$i]['values'][$k]['label']){ echo 'selected="selected"'; } ?>><?php  echo $_SESSION['index_to_use'][$i]['values'][$k]['label'];?></option>
													<?php
													}
												}
												else
												{
													$query = "select ".$_SESSION['index_to_use'][$i]['foreign_key'].", ".$_SESSION['index_to_use'][$i]['foreign_label']." from ".$_SESSION['index_to_use'][$i]['tablename'];
													if(isset($_SESSION['index_to_use'][$i]['where']) && !empty($_SESSION['index_to_use'][$i]['where']))
													{
														$query .= " where ".$_SESSION['index_to_use'][$i]['where'];
													}
													if(isset($_SESSION['index_to_use'][$i]['order']) && !empty($_SESSION['index_to_use'][$i]['order']))
													{
														$query .= ' '.$_SESSION['index_to_use'][$i]['order'];
													}
													$db->query($query);
													while($res = $db->fetch_object())
													{
														?>
														<option value="<?php  echo $res->$_SESSION['index_to_use'][$i]['foreign_key'];?>" <?php  if($_SESSION['indexing'][$_SESSION['index_to_use'][$i]['column']] == $res->$_SESSION['index_to_use'][$i]['foreign_key']){ echo 'selected="selected"'; } ?>><?php  echo $db->show_string($res->$_SESSION['index_to_use'][$i]['foreign_label']);?></option>
														<?php
													}
												}
												?>
												</select>
											</p>
											<?php
											}
											else
											{
											?>
											<p>
												<span>
													<?php
													if($_SESSION['index_to_use'][$i]['mandatory'])
													{
														echo "<b>".$_SESSION['index_to_use'][$i]['label']."</b> : ";
													}
													else
													{
														echo $_SESSION['index_to_use'][$i]['label']." : ";
													}
													?>
												</span>
												<?php
												if($_SESSION['index_to_use'][$i]['date'])
												{

												?>
												<input type="text" name="doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>" id="doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>" value="<?php  echo $_SESSION['indexing'][$_SESSION['index_to_use'][$i]['column']];?>" <?php  if($_SESSION['field_error'][$_SESSION['index_to_use'][$i]['column']]){?>style="background-color:#FF0000"<?php  }?>
<?php  if($_SESSION['index_to_use'][$i]['date']){?> onclick="showCalender(this);"<?php  }?>
												/>
												<?php
												if($_SESSION['index_to_use'][$i]['mandatory'])
												{
													?>
													<input type="hidden" name="mandatory_doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>" id="mandatory_doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>" value="true" />
													<?php
												}
												if($_SESSION['index_to_use'][$i]['date'])
												{
												?>
													&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												<?php
												}
												?>
											</p>
											<?php
												}
											}
										}
									}
									else if($is_master == "N")
									{*/
										//echo "<b>"._INDEX."</b><br/><br/>";
										for($i=0;$i<=count($_SESSION['index_to_use']);$i++)
										{
											if($_SESSION['index_to_use'][$i]['label'] <> "")
											{
												$connexion->query("select ".$_SESSION['index_to_use'][$i]['column']." from ".$_SESSION['collection_choice']." where res_id = ".$_SESSION['unique_res_id']);
												$res_mastertype = $connexion->fetch_array();
												$_SESSION['indexing'][$_SESSION['index_to_use'][$i]['column']] = $res_mastertype[$_SESSION['index_to_use'][$i]['column']];
												if($_SESSION['index_to_use'][$i]['date'])
												{
													$_SESSION['indexing'][$_SESSION['index_to_use'][$i]['column']] = $func->format_date_db($_SESSION['indexing'][$_SESSION['index_to_use'][$i]['column']], false);
												}
												if((isset($_SESSION['index_to_use'][$i]['foreign_key']) && !empty($_SESSION['index_to_use'][$i]['foreign_key']) && isset($_SESSION['index_to_use'][$i]['foreign_label']) && !empty($_SESSION['index_to_use'][$i]['foreign_label']) && isset($_SESSION['index_to_use'][$i]['tablename']) && !empty($_SESSION['index_to_use'][$i]['tablename'])) || (isset($_SESSION['index_to_use'][$i]['values']) && count($_SESSION['index_to_use'][$i]['values']) > 0))
												{
													?>
													<p>
														<label>
															<?php
															if($_SESSION['index_to_use'][$i]['mandatory'])
															{
																echo "<b>".$_SESSION['index_to_use'][$i]['label']."</b> : ";
															}
															else
															{
																echo $_SESSION['index_to_use'][$i]['label']." : ";
															}
															?>
														</label>
														<select name="doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>" id="doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>">
														<option value=""><?php  echo _CHOOSE;?></option>
														<?php
														if(isset($_SESSION['index_to_use'][$i]['values']) && count($_SESSION['index_to_use'][$i]['values']) > 0)
														{
															for($k=0; $k < count($_SESSION['index_to_use'][$i]['values']); $k++)
															{
															?>
																<option value="<?php  echo $_SESSION['index_to_use'][$i]['values'][$k]['label'];?>" <?php  if($_SESSION['indexing'][$_SESSION['index_to_use'][$i]['column']] == $_SESSION['index_to_use'][$i]['values'][$k]['label']){ echo 'selected="selected"'; } ?>><?php  echo $_SESSION['index_to_use'][$i]['values'][$k]['label'];?></option>
															<?php
															}
														}
														else
														{
															$query = "select ".$_SESSION['index_to_use'][$i]['foreign_key'].", ".$_SESSION['index_to_use'][$i]['foreign_label']." from ".$_SESSION['index_to_use'][$i]['tablename'];
															if(isset($_SESSION['index_to_use'][$i]['where']) && !empty($_SESSION['index_to_use'][$i]['where']))
															{
																$query .= " where ".$_SESSION['index_to_use'][$i]['where'];
															}
															if(isset($_SESSION['index_to_use'][$i]['order']) && !empty($_SESSION['index_to_use'][$i]['order']))
															{
																$query .= ' '.$_SESSION['index_to_use'][$i]['order'];
															}
															$db->query($query);
															while($res = $db->fetch_object())
															{
																?>
																<option value="<?php  echo $res->$_SESSION['index_to_use'][$i]['foreign_key'];?>" <?php  if($_SESSION['indexing'][$_SESSION['index_to_use'][$i]['column']] == $res->$_SESSION['index_to_use'][$i]['foreign_key']){ echo 'selected="selected"'; } ?>><?php  echo $db->show_string($res->$_SESSION['index_to_use'][$i]['foreign_label']);?></option>
																<?php
															}
														}
														?>
														</select>
													</p>
												<?php
												}
												else
												{
													?>
													<p>
														<span>
															<?php
															if($_SESSION['index_to_use'][$i]['mandatory'])
															{
																echo "<b>".$_SESSION['index_to_use'][$i]['label']."</b> : ";
															}
															else
															{
																echo $_SESSION['index_to_use'][$i]['label']." : ";
															}
															?>
														</span>
														<?php
														if($_SESSION['index_to_use'][$i]['date'])
														{
															/*?>
															<img src="<?php  echo $_SESSION['config']['businessappurl'];?>img/calendar.jpg" alt="" name="for_doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>" id='for_doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>' onclick='showCalender(this)' /> <?php  */?>
															<input type="text" name="doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>" id="doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>" value="<?php  echo $func->format_date_db($_SESSION['indexing'][$_SESSION['index_to_use'][$i]['column']], false);?>" <?php  if($_SESSION['field_error'][$_SESSION['index_to_use'][$i]['column']]){?>style="background-color:#FF0000"<?php  }?> onclick='showCalender(this)' />
															<?php
														}
														else
														{
															?>
															<input type="text" name="doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>" id="doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>" value="<?php  echo $_SESSION['indexing'][$_SESSION['index_to_use'][$i]['column']];?>" <?php  if($_SESSION['field_error'][$_SESSION['index_to_use'][$i]['column']]){?>style="background-color:#FF0000"<?php  }?> />
															<?php
														}
														?>

														<?php
														if($_SESSION['index_to_use'][$i]['mandatory'])
														{
															?>
															<input type="hidden" name="mandatory_doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>" id="mandatory_doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>" value="true" />
															<?php
														}
														if($_SESSION['index_to_use'][$i]['date'])
														{
														?>
															&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
														<?php
														}
														?>
													</p>
													<?php
												}
											}
										}
									//}
								/*	elseif($is_folder_complete && $is_master == "Y")
									{
										echo "<b>"._INDEX."</b><br/><br/>";
										for($i=0;$i<=count($_SESSION['index_to_use']);$i++)
										{
											if($_SESSION['index_to_use'][$i]['label'] <> "")
											{
												if($_SESSION['masterdoctype_res_id'] <> "")
												{
													$connexion->query("select ".$_SESSION['index_to_use'][$i]['column']." from ".$_SESSION['collection_choice']." where res_id = ".$_SESSION['masterdoctype_res_id']);
													$res_to_view = $connexion->fetch_array();
													$_SESSION['indexing'][$_SESSION['index_to_use'][$i]['column']] = $res_to_view[$_SESSION['index_to_use'][$i]['column']];
													if($_SESSION['index_to_use'][$i]['date'])
													{
														$_SESSION['indexing'][$_SESSION['index_to_use'][$i]['column']] = $func->format_date_db($_SESSION['indexing'][$_SESSION['index_to_use'][$i]['column']], false);
													}
												}
												if((isset($_SESSION['index_to_use'][$i]['foreign_key']) && !empty($_SESSION['index_to_use'][$i]['foreign_key']) && isset($_SESSION['index_to_use'][$i]['foreign_label']) && !empty($_SESSION['index_to_use'][$i]['foreign_label']) && isset($_SESSION['index_to_use'][$i]['tablename']) && !empty($_SESSION['index_to_use'][$i]['tablename'])) || (isset($_SESSION['index_to_use'][$i]['values']) && count($_SESSION['index_to_use'][$i]['values']) > 0))
											{
												?>
												<p>
													<label>
														<?php
														if($_SESSION['index_to_use'][$i]['mandatory'])
														{
															echo "<b>".$_SESSION['index_to_use'][$i]['label']."</b> : ";
														}
														else
														{
															echo $_SESSION['index_to_use'][$i]['label']." : ";
														}
														?>
													</label>
													<select name="doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>" id="doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>">
													<option value=""><?php  echo _CHOOSE;?></option>
													<?php
													if(isset($_SESSION['index_to_use'][$i]['values']) && count($_SESSION['index_to_use'][$i]['values']) > 0)
													{
														for($k=0; $k < count($_SESSION['index_to_use'][$i]['values']); $k++)
														{
														?>
															<option value="<?php  echo $_SESSION['index_to_use'][$i]['values'][$k]['label'];?>" <?php  if($_SESSION['indexing'][$_SESSION['index_to_use'][$i]['column']] == $_SESSION['index_to_use'][$i]['values'][$k]['label']){ echo 'selected="selected"'; } ?>><?php  echo $_SESSION['index_to_use'][$i]['values'][$k]['label'];?></option>
														<?php
														}
													}
													else
													{
														$query = "select ".$_SESSION['index_to_use'][$i]['foreign_key'].", ".$_SESSION['index_to_use'][$i]['foreign_label']." from ".$_SESSION['index_to_use'][$i]['tablename'];
														if(isset($_SESSION['index_to_use'][$i]['where']) && !empty($_SESSION['index_to_use'][$i]['where']))
														{
															$query .= " where ".$_SESSION['index_to_use'][$i]['where'];
														}
														if(isset($_SESSION['index_to_use'][$i]['order']) && !empty($_SESSION['index_to_use'][$i]['order']))
														{
															$query .= ' '.$_SESSION['index_to_use'][$i]['order'];
														}
														$db->query($query);
														while($res = $db->fetch_object())
														{
															?>
															<option value="<?php  echo $res->$_SESSION['index_to_use'][$i]['foreign_key'];?>" <?php  if($_SESSION['indexing'][$_SESSION['index_to_use'][$i]['column']] == $res->$_SESSION['index_to_use'][$i]['foreign_key']){ echo 'selected="selected"'; } ?>><?php  echo $db->show_string($res->$_SESSION['index_to_use'][$i]['foreign_label']);?></option>
															<?php
														}
													}
													?>
													</select>
												</p>
												<?php
												}
												else
												{
													?>
													<p>
														<span>
															<?php
															if($_SESSION['index_to_use'][$i]['mandatory'])
															{
																echo "<b>".$_SESSION['index_to_use'][$i]['label']."</b> : ";
															}
															else
															{
																echo $_SESSION['index_to_use'][$i]['label']." : ";
															}
															?>
														</span>
														<?php
														if($_SESSION['index_to_use'][$i]['date'])
														{
														?>
															<input type="text" name="doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>" id="doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>" value="<?php  echo $func->format_date_db($_SESSION['indexing'][$_SESSION['index_to_use'][$i]['column']], false);?>" <?php  if($_SESSION['field_error'][$_SESSION['index_to_use'][$i]['column']]){?>style="background-color:#FF0000"<?php  }?> onclick="showCalender(this);" />
															<?php
														}
														else
														{
															?>
															<input type="text" name="doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>" id="doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>" value="<?php  echo $_SESSION['indexing'][$_SESSION['index_to_use'][$i]['column']];?>" <?php  if($_SESSION['field_error'][$_SESSION['index_to_use'][$i]['column']]){?>style="background-color:#FF0000"<?php  }?> />
															<?php
														}
														?>

														<?php
														if($_SESSION['index_to_use'][$i]['mandatory'])
														{
															?>
															<input type="hidden" name="mandatory_doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>" id="mandatory_doc_<?php  echo $_SESSION['index_to_use'][$i]['column'];?>" value="true" />
															<?php
														}
														if($_SESSION['index_to_use'][$i]['date'])
														{
														?>
															&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
														<?php
														}
														?>
													</p>
													<?php
												}
											}
										}
									}*/
								?>
								<p class="buttons">
                                <input type="hidden" name="coll_id" value="<?php  echo $_SESSION['collection_id_choice'];?>"/>
                                <input type="hidden" name="res_id" value="<?php  echo $_SESSION['unique_res_id'];?>" />
									<?php
									if($security->collection_user_right($_SESSION['collection_id_choice'], "can_update"))
									{
										?>
										<input type="submit" class="button"  value="<?php  echo _MODIFY_DOC;?>" name="submit_index_doc" />
										<?php
									}
									if($security->collection_user_right($_SESSION['collection_id_choice'], "can_delete"))
									{
										?>
										<input type="submit" class="button"  value="<?php  echo _DELETE_THE_DOC;?>" name="delete_doc" onclick="return(confirm('<?php  echo _REALLY_DELETE.' '._THIS_DOC;?> ?\n\r\n\r'));" />
										<?php
									}
									?>
								 </p>
								</form>
                                <hr/>
								<div  align="center"> <iframe src="<?php  echo $_SESSION['urltomodules']."indexing_searching/";?>view.php?id=<?php  echo $_SESSION['unique_res_id'] ;?>" id="pdf_iframe" frameborder="0" marginheight="0" marginwidth="0" width="95%" height="300px" ></iframe>
								<a href="<?php  echo $_SESSION['urltomodules']."indexing_searching/";?>view.php?id=<?php  echo $_SESSION['unique_res_id'];?>" target="_blank"><?php  echo _FULL_PAGE;?></a>
						</div>
							<?php
							}
							elseif($security->collection_user_right($_SESSION['collection_id_choice'], "can_delete"))
							{
								?>

								<form method="get" name="index_doc" action="index.php?page=view_folder&amp;module=folder" class="forms">
                                	<input type="hidden" name="coll_id" value="<?php  echo $_SESSION['collection_id_choice'];?>"/>
                                    <input type="hidden" name="res_id" value="<?php  echo $_SESSION['unique_res_id'];?>" />
                                    <input type="submit" class="button"  value="<?php  echo _DELETE_THE_DOC;?>" name="delete_doc" onclick="return(confirm('<?php  echo _REALLY_DELETE.' '._THIS_DOC;?> ?\n\r\n\r'));" />
								</form>
								<?php
							}
                    	}
							if(!empty($_SESSION['error_page']))
							{
								?>
								<script language="javascript" type="text/javascript">
									alert("<?php  echo $func->wash_html($_SESSION['error_page']);?>");
									<?php
									if(isset($_REQUEST['delete_doc']))
									{
										?>
										window.location.reload();
										<?php
									}
									?>
								</script>
								<?php
								$_SESSION['error'] = "";
								$_SESSION['error_page'] = "";
							}
                        }
						elseif(count($tab) > 1)
						{
							//$func->show_array($tab);
							$list=new list_show();
							$list->list_doc($tab,$i,$i." "._FOUND_DOC,"res_id","view_folder","res_id","folder_detail",false,true,"post","index.php?page=view_folder&amp;module=folder&amp;type_id=".$_REQUEST['type_id']."&amp;second_level=".$_REQUEST['second_level']."&amp;morethantwodocs=ok&amp;coll_id=".$_SESSION['collection_id_choice'],_CHOOSE, false, false, false,false, false, false, false, false, "", "", false, "", "");
						}
						else
						{
							?>
							<div align="center"><?php  echo _NO_DOC_SEARCH;?>.</div>
							<?php
						}
                	}
                    ?>
                </div>
            </div>
        </td>
    </tr>
</table>
</div>

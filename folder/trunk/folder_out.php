<?php
/**
* File : folder_out.php
*
* Manage the folder out
*
* @package  Maarch PeopleBox 1.0
* @version 2.0
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
$core_tools = new core_tools();
if(!$core_tools->is_module_loaded("folder"))
{
	echo "Folder module missing !<br/>Please install this module.";
	exit();
}
$core_tools->test_service('folder_out', 'folder');
require_once($_SESSION['pathtomodules']."folder".$_SESSION['slash_env']."class".$_SESSION['slash_env']."class_modules_tools.php");
require_once($_SESSION['pathtomodules']."folder".$_SESSION['slash_env']."class".$_SESSION['slash_env']."class_folders_show.php");
require_once($_SESSION['config']['businessapppath']."class".$_SESSION['slash_env']."class_types.php");
require_once($_SESSION['config']['businessapppath']."class".$_SESSION['slash_env']."class_list_show.php");
$func = new functions();
$folder_show=new folders_show();
$folder_object=new folder();
// $docserver = new docserver($_SESSION['tablename']['docservers'],'fr');
$type = new types();
$request= new request;
$select[$_SESSION['tablename']['fold_folders']]= array();
array_push($select[$_SESSION['tablename']['fold_folders']],"folder_id","folders_system_id","custom_t1","custom_t2");
$request= new request;
 if(trim($_REQUEST['folder_id'])<>"")
 {
 	$tmp = trim($_REQUEST['folder_id']);
 	if($_SESSION['config']['databasetype'] == "POSTGRESQL")
	{
		$where .="folder_id ilike '".$func->protect_string_db($tmp,$_SESSION['config']['databasetype'])."%' and status <> 'DEL' and status <> 'IMP'";
	}
	else
	{
		$where .="folder_id like '".$func->protect_string_db($tmp,$_SESSION['config']['databasetype'])."%' and status <> 'DEL' and status <> 'IMP'";
	}
	$tab=$request->select($select,$where,"",$_SESSION['config']['databasetype'],"500");

	for ($k=0;$k<count($tab);$k++)
	{
		for ($l=0;$l<count($tab[$k]);$l++)
		{
			foreach(array_keys($tab[$k][$l]) as $value)
			{
				if($tab[$k][$l][$value]=="folder_id")
				{
					$tab[$k][$l]["folder_id"]=$tab[$k][$l]['value'];
					$tab[$k][$l]["label"]=_MATRICULE;
					$tab[$k][$l]["size"]="10";
					$tab[$k][$l]["label_align"]="left";
					$tab[$k][$l]["align"]="center";
					$tab[$k][$l]["valign"]="bottom";
					$tab[$k][$l]["show"]=true;
				}
				if($tab[$k][$l][$value]=="folders_system_id")
				{
					$tab[$k][$l]["folders_system_id"]=$tab[$k][$l]['value'];
					$tab[$k][$l]["label"]="folders_system_id";
					$tab[$k][$l]["size"]="4";
					$tab[$k][$l]["label_align"]="left";
					$tab[$k][$l]["align"]="center";
					$tab[$k][$l]["valign"]="bottom";
					$tab[$k][$l]["show"]=false;
				}
				if($tab[$k][$l][$value]=="custom_t1")
				{
					$tab[$k][$l]["label"]=_LASTNAME;
					$tab[$k][$l]["size"]="15";
					$tab[$k][$l]["label_align"]="left";
					$tab[$k][$l]["align"]="left";
					$tab[$k][$l]["valign"]="bottom";
					$tab[$k][$l]["show"]=true;
				}
				if($tab[$k][$l][$value]=="custom_t2")
				{
					$tab[$k][$l]["label"]=_FIRSTNAME;
					$tab[$k][$l]["size"]="15";
					$tab[$k][$l]["label_align"]="left";
					$tab[$k][$l]["align"]="left";
					$tab[$k][$l]["valign"]="bottom";
					$tab[$k][$l]["show"]=true;
				}
			}
		}
	}

	if($k>1)
	{
		$show_list = true;
		$_SESSION['FOLDER']['SEARCH']['FOLDER_ID'] = '';
	}
	else
	{
		$_SESSION['FOLDER']['SEARCH']['FOLDER_ID'] = $tab[0][0]["folder_id"];

	}
 	//$_SESSION['FOLDER']['SEARCH']['FOLDER_ID'] = $_REQUEST['folder_id'];
 }
 elseif(trim($_REQUEST['field']) <> "")
 {
 	$_SESSION['FOLDER']['SEARCH']['FOLDER_ID'] = $_REQUEST['field'];
 }
 if(trim($_REQUEST['name'])<>"")
 {
 	$_SESSION['FOLDER']['SEARCH']['NAME'] = $_REQUEST['name'];
 }
 if(trim($_GET['type_id'])<>"")
 {
 	$_SESSION['FOLDER']['SEARCH']['TYPE_ID'] = $_GET['type_id'];
 }




if($_REQUEST['name']<>"")
{
	if($_SESSION['config']['databasetype'] == "POSTGRESQL")
	{
		$where .="custom_t1 ilike '".$func->protect_string_db($_REQUEST['name'],$_SESSION['config']['databasetype'])."%' and status <> 'DEL' and status <> 'IMP'";
	}
	else
	{
		$where .="custom_t1 like '".$func->protect_string_db($_REQUEST['name'],$_SESSION['config']['databasetype'])."%' and status <> 'DEL' and status <> 'IMP'";
	}
	$tab=$request->select($select,$where,"",$_SESSION['config']['databasetype'],"500");

	for ($k=0;$k<count($tab);$k++)
	{
		for ($l=0;$l<count($tab[$k]);$l++)
		{
			foreach(array_keys($tab[$k][$l]) as $value)
			{
				if($tab[$k][$l][$value]=="folder_id")
				{
					$tab[$k][$l]["folder_id"]=$tab[$k][$l]['value'];
					$tab[$k][$l]["label"]=_MATRICULE;
					$tab[$k][$l]["size"]="10";
					$tab[$k][$l]["label_align"]="left";
					$tab[$k][$l]["align"]="center";
					$tab[$k][$l]["valign"]="bottom";
					$tab[$k][$l]["show"]=true;
				}
				if($tab[$k][$l][$value]=="folders_system_id")
				{
					$tab[$k][$l]["folders_system_id"]=$tab[$k][$l]['value'];
					$tab[$k][$l]["label"]="folders_system_id";
					$tab[$k][$l]["size"]="4";
					$tab[$k][$l]["label_align"]="left";
					$tab[$k][$l]["align"]="center";
					$tab[$k][$l]["valign"]="bottom";
					$tab[$k][$l]["show"]=false;
				}
				if($tab[$k][$l][$value]=="custom_t1")
				{
					$tab[$k][$l]["label"]=_LASTNAME;
					$tab[$k][$l]["size"]="15";
					$tab[$k][$l]["label_align"]="left";
					$tab[$k][$l]["align"]="left";
					$tab[$k][$l]["valign"]="bottom";
					$tab[$k][$l]["show"]=true;
				}
				if($tab[$k][$l][$value]=="custom_t2")
				{
					$tab[$k][$l]["label"]=_FIRSTNAME;
					$tab[$k][$l]["size"]="15";
					$tab[$k][$l]["label_align"]="left";
					$tab[$k][$l]["align"]="left";
					$tab[$k][$l]["valign"]="bottom";
					$tab[$k][$l]["show"]=true;
				}
			}
		}
	}

	if($k>1)
	{
		$show_list = true;
		$_SESSION['FOLDER']['SEARCH']['FOLDER_ID'] = '';
	}
	else
	{
		$_SESSION['FOLDER']['SEARCH']['FOLDER_ID'] = $tab[0][0]["folder_id"];

	}

}

if($_SESSION['FOLDER']['SEARCH']['FOLDER_ID']<> "")
{
	$folder_object->load_folder2($_SESSION['FOLDER']['SEARCH']['FOLDER_ID'],$_SESSION['tablename']['fold_folders']);
	$folder_array = array();
	$folder_array = $folder_object->get_folder_info();
	if($folder_array['NOM']<>"")
	{
		$_SESSION['folder_id_search_out'] = $folder_object->get_folder_system_id();
		$request= new request;
	 	$select_folder_out[$_SESSION['tablename']['fold_folders_out']]= array();
	 	array_push($select_folder_out[$_SESSION['tablename']['fold_folders_out']],"folder_out_id,last_name_folder_out,put_out_date");
		$tab_folders_out=$request->select($select_folder_out,"folder_system_id = ".$_SESSION['folder_id_search_out']." and return_flag='N'","",$_SESSION['config']['databasetype'],"500");
		for ($i=0;$i<count($tab_folders_out);$i++)
		{
			for ($j=0;$j<count($tab_folders_out[$i]);$j++)
			{
				foreach(array_keys($tab_folders_out[$i][$j]) as $value)
				{
					if($tab_folders_out[$i][$j][$value]=="folder_out_id")
					{
						$tab_folders_out[$i][$j]["folder_out_id"]=$tab_folders_out[$i][$j]['value'];
						$tab_folders_out[$i][$j]["label"]=_FILE_OUT_NUM;
						$tab_folders_out[$i][$j]["size"]="10";
						$tab_folders_out[$i][$j]["label_align"]="center";
						$tab_folders_out[$i][$j]["align"]="center";
						$tab_folders_out[$i][$j]["valign"]="bottom";
						$tab_folders_out[$i][$j]["show"]=true;
					}
					if($tab_folders_out[$i][$j][$value]=="last_name_folder_out")
					{
						$tab_folders_out[$i][$j]["label"]=_FILE_OUT_NAME;
						$tab_folders_out[$i][$j]["size"]="10";
						$tab_folders_out[$i][$j]["label_align"]="center";
						$tab_folders_out[$i][$j]["align"]="center";
						$tab_folders_out[$i][$j]["valign"]="bottom";
						$tab_folders_out[$i][$j]["show"]=true;
					}
					if($tab_folders_out[$i][$j][$value]=="put_out_date")
					{
						$tab_folders_out[$i][$j]["label"]=_FILE_OUT_DATE2;
						$tab_folders_out[$i][$j]["size"]="10";
						$tab_folders_out[$i][$j]["label_align"]="center";
						$tab_folders_out[$i][$j]["align"]="center";
						$tab_folders_out[$i][$j]["valign"]="bottom";
						$tab_folders_out[$i][$j]['value'] = $func->format_date_db($tab_folders_out[$i][$j]['value']);
						$tab_folders_out[$i][$j]["show"]=true;
					}
				}
			}
		}
		if($i>0)
		{
			$flag_allready_exist = true;
		}
		else
		{
			$flag_allready_exist = false;
		}
	}
	else
	{
		echo _NO_FOLDER_FOUND;
	}
}

//get the var to create
if($_REQUEST['ins_first_name'] <> "" && $_REQUEST['ins_last_name'] <> "" && $_REQUEST['ins_last_name_in'] <> "" && $_REQUEST['ins_first_name_in'] <> "" && $_REQUEST['ins_retrait_date'] <> "" && $_REQUEST['ins_restitution_date'] <> "" && $_REQUEST['ins_motif'] <> "")
{
	if($_SESSION['config']['databasetype'] == "SQLSERVER")
	{
		 $pattern = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";
	 }
	 else // MYSQL & POSTGRESQL
	{
		 $pattern = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";
	 }
	$data = array();
	array_push($data, array('column' => "folder_system_id", 'value' => $func->protect_string_db($_REQUEST['ins_folder_system_id'],$_SESSION['config']['databasetype']), 'type' => "string"));
	array_push($data, array('column' => "last_name", 'value' => $func->protect_string_db($_REQUEST['ins_last_name'],$_SESSION['config']['databasetype']), 'type' => "string"));
	array_push($data, array('column' => "first_name", 'value' => $func->protect_string_db($_REQUEST['ins_first_name'],$_SESSION['config']['databasetype']), 'type' => "string"));
	array_push($data, array('column' => "last_name_folder_out", 'value' => $func->protect_string_db($_REQUEST['ins_last_name_in'],$_SESSION['config']['databasetype']), 'type' => "string"));
	array_push($data, array('column' => "first_name_folder_out", 'value' => $func->protect_string_db($_REQUEST['ins_first_name_in'],$_SESSION['config']['databasetype']), 'type' => "string"));
	if(preg_match($pattern,$_REQUEST['ins_retrait_date'])== false )
		{
			$_SESSION['error'] .= _FILE_OUT_DATE2." "._WRONG_FORMAT.".<br/>";
		}
		else
		{
			$date = array();

			if($database_type == "SQLSERVER")
			{
				$date = preg_split("/\//", $_REQUEST['ins_retrait_date']);
			}
			else // MYSQL & POSTGRESQL
			{
				$date = preg_split("/-/", $_REQUEST['ins_retrait_date']);
			}
			$func->verif_date($date[0], $date[1], $date[2]);
			if($database_type == "SQLSERVER")
			{
				$date_tmp = "";
				$date_tmp = str_replace("-", "/", $_REQUEST['ins_retrait_date']);
			}
			else // MYSQL & POSTGRESQL
			{
				$date_tmp = $date[2].$date[1].$date[0];
			}
			array_push($data, array('column' => "PUT_OUT_DATE", 'value' => $date_tmp, 'type' => "date"));
		}

	if(preg_match($pattern,$_REQUEST['ins_restitution_date'])== false )
		{
			$_SESSION['error'] .= _FOLDER_OUT_RETURN_DATE." "._WRONG_FORMAT.".<br/>";
		}
		else
		{
			$date = array();

			if($database_type == "SQLSERVER")
			{
				$date = preg_split("/\//", $_REQUEST['ins_restitution_date']);
			}
			else // MYSQL & POSTGRESQL
			{
				$date = preg_split("/-/", $_REQUEST['ins_restitution_date']);
			}
			$func->verif_date($date[0], $date[1], $date[2]);
			if($database_type == "SQLSERVER")
			{
				$date_tmp = "";
				$date_tmp = str_replace("-", "/", $_REQUEST['ins_restitution_date']);
			}
			else // MYSQL & POSTGRESQL
			{
				$date_tmp = $date[2].$date[1].$date[0];
			}
			array_push($data, array('column' => "return_date", 'value' => $date_tmp, 'type' => "date"));
		}

	array_push($data, array('column' => "put_out_pattern", 'value' => $func->protect_string_db($_REQUEST['ins_motif'],$_SESSION['config']['databasetype']), 'type' => "string"));
	$request->insert($_SESSION['tablename']['fold_folders_out'], $data, $_SESSION['config']['databasetype']);
	$data_folder = array();
	$flag_folder_out = "Y";
	array_push($data_folder, array('column' => "custom_t9", 'value' => $flag_folder_out, 'type' => "string"));
	$request->update($_SESSION['tablename']['fold_folders'],$data_folder,"", $_SESSION['config']['databasetpe']);

	$request->query('select folder_out_id from '.$_SESSION['tablename']['folders_out']." where folder_system_id = ".$_REQUEST['ins_folder_system_id']." and return_flag ='N'");
	$res = $request->fetch_object();
	$id = $res->folder_out_id;
	require_once($_SESSION['pathtocoreclass']."class_history.php");
	$users = new history();
	$users->add($_SESSION['tablename']['fold_folders'], $_REQUEST['ins_folder_system_id'],"FOUT", _FOLDER_OUT." ".strtolower(_NUM).$_REQUEST['ins_folder_system_id'], $_SESSION['config']['databasetype'],'folder');

?>
<script language="javascript" type="text/javascript">window.top.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=details_folder_out&module=folder&id=<?php  echo $id;?>';</script>
<?php
exit();
	//echo "<div class=\"error\">"._FOLDER_OUT_SHEET."</div>";
	//$_SESSION['FOLDER']['SEARCH']['FOLDER_ID'] = $_REQUEST['ins_folder_id'];
}
else
{
	if($_REQUEST['flag_ins'])
	{
		echo "<div class=\"error\">". _MISSING_FIELDS.".</div>";

	}
}

if(!empty($_SESSION['error']))
{
	echo "<div class=\"error\">".$_SESSION['error'].".</div>";
	$_SESSION['error'] = '';
}
?>
<h1><img src="<?php  echo $_SESSION['urltomodules']."folder/img/desarchivage_b.gif";?>" alt="" /> <?php  echo _FILE_OUT;?></h1>
<div id="inner_content">

<div>
       <form name="search_folder"  method="post" class="forms " >
             <p>
             	<label><?php  echo _MATRICULE;?> : </label>
                <input type="text" name="folder_id"  id="folder_id" />
              </p>
                <p>
                	<label><?php  echo _LASTNAME;?> :</label>
                    <input type="text" name="name" id="name"/>
                   <input class='button' name='imageField' type="submit"   value="<?php  echo _SEARCH;?>"/>
                 </p>

      </form>
  </div>
	<?php
	if($_SESSION['FOLDER']['SEARCH']['FOLDER_ID']<>"")
	{
	?>
			<div align="center">
								<?php
								if($_SESSION['FOLDER']['SEARCH']['FOLDER_ID']<>"")
								{
									if(!$flag_allready_exist)
									{

										$folder_object->load_folder2($_SESSION['FOLDER']['SEARCH']['FOLDER_ID'],$_SESSION['tablename']['fold_folders']);
										$folder_array = array();
										$folder_array = $folder_object->get_folder_info();
										if($folder_array['NOM']<>"")
										{
											//$docserver->select_docserver('trombi',$_SESSION['tablename']['docservers']);
											//$path_trombi = $docserver->get_path();
											echo "<hr/>";
											$folder_show->view_folder_out($folder_array,$_SESSION['config']['businessappurl']."index.php?page=folder_out&module=folder");
										}
										else
										{
											echo _NO_FOLDER_FOUND.".";
										}
										$_SESSION['current_folder_id'] = $folder_object->get_folder_system_id();
									}
									else
									{
										echo "<hr/>";
										$list=new list_show();
										$list->list_doc($tab_folders_out,$i,$i." "._CARDS_FOUND." : ".$_SESSION['FOLDER']['SEARCH']['FOLDER_ID'],"folder_out_id","folder_out","folder_out_id","details_folder_out&module=folder",false,false,"post","index.php?page=details_folder_out&module=folder","", true, false, false,false,false,false);
									}
								}

								?>
                                </div>

	<?php
	}
	else
	{
	?><div align="center"><?php
		if($_REQUEST['name']<>"")
		{
			if($show_list)
			{

				$list=new list_show();
				$list->list_doc($tab,$k,_SEARCH_RESULTS." : ".$k." "._FOUND_FOLDERS,"folder_id","folder_out","folder_id","folder_detail",false,true,"post",$_SESSION['config']['businessappurl']."index.php?page=folder_out&module=folder",_CHOOSE, false, false, false,false,false,false);
			}
		}
		elseif($_REQUEST['folder_id']<>"")
		{
			if($show_list)
			{

				$list=new list_show();
				$list->list_doc($tab,$k,_SEARCH_RESULTS." : ".$k." "._FOUND_FOLDERS,"folder_id","folder_out","folder_id","folder_detail",false,true,"post",$_SESSION['config']['businessappurl']."index.php?page=folder_out&module=folder",_CHOOSE, false, false, false,false,false,false);
			}
		}
		else
		{
			echo "<div class=\"error\">"._PLEASE_SELECT_FOLDER.".</div>";
		}
	?></div><?php
	}
	?>
</div>
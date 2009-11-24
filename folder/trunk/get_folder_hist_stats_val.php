<?php

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
$core_tools = new core_tools();
$core_tools->load_lang();
$db = new dbquery();
$db->connect();
$req = new request();
$list = new list_show();

if(isset($_REQUEST['folder_id']) && $_REQUEST['folder_id'] != '')
{
	$folderId = $db->protect_string_db($_REQUEST['folder_id']);

	$db->query("select folders_system_id, folder_name from ".$_SESSION['tablename']['fold_folders']." where folder_id = '".$folderId."'");
	//$db->show();
	if($db->nb_result() == 0)
	{
		?>
		<div class="error"><?php echo _FOLDER.' '._UNKNOWN;?></div>
		<?php
		exit();
	}
	else
	{
		$res = $db->fetch_object();
		$folder_sys_id = $res->folders_system_id;
		$folder_name = $res->folder_name;
	}
	if(isset($_REQUEST['type_report']) && $_REQUEST['type_report'] == 'action')
	{
		$action = isset($_REQUEST['id_action'])? $_REQUEST['id_action'] :'all';
		if($action == "all")
		{
			$db->query("SELECT h.event_date, h.event_type, h.user_id, u.lastname, u.firstname FROM ".$_SESSION['tablename']['history']." h INNER JOIN ".$_SESSION['tablename']['users']." u ON h.user_id = u.user_id WHERE h.table_name = '".$_SESSION['tablename']['fold_folders']."' AND h.record_id = '".$folder_sys_id."' ");
		}
		else
		{
			$db->query("SELECT h.event_date, h.event_type, h.user_id, u.lastname, u.firstname FROM ".$_SESSION['tablename']['history']." h INNER JOIN ".$_SESSION['tablename']['users']." u ON h.user_id = u.user_id WHERE h.table_name = '".$_SESSION['tablename']['fold_folders']."' AND h.event_type = '".$action."' AND h.record_id = '".$folder_sys_id."' ");
		}
		//$db->show();
		$tab=array();
		while($line = $db->fetch_array())
		{
			$temp= array();

			foreach (array_keys($line) as $resval)
			{
				if (!is_int($resval))
				{
					array_push($temp,array('column'=>$resval,'value'=>$line[$resval]));
				}
			}
			array_push($tab,$temp);
		}

		if (count($tab) > 0)
		{
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$hist = new history();

			for ($i=0;$i<count($tab);$i++)
			{
				for ($j=0;$j<count($tab[$i]);$j++)
				{
					foreach(array_keys($tab[$i][$j]) as $value)
					{
						if($tab[$i][$j][$value] == "user_id")
						{
							$tab[$i][$j]["label"]=_USER;
							$tab[$i][$j]["size"]="4";
							$tab[$i][$j]["label_align"]="left";
							$tab[$i][$j]["align"]="center";
							$tab[$i][$j]["valign"]="bottom";
							$tab[$i][$j]["show"]=true;
							//$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
							$tab[$i][$j]["value"]=$tab[$i][$j]['value'];
						}
						if($tab[$i][$j][$value]=="lastname")
						{
							$tab[$i][$j]["label"]=_LASTNAME;
							$tab[$i][$j]["size"]="15";
							$tab[$i][$j]["label_align"]="left";
							$tab[$i][$j]["align"]="left";
							$tab[$i][$j]["valign"]="bottom";
							$tab[$i][$j]["show"]=true;
							$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
						}
						if($tab[$i][$j][$value]=="firstname")
						{
							$tab[$i][$j]["label"]=_FIRSTNAME;
							$tab[$i][$j]["size"]="15";
							$tab[$i][$j]["label_align"]="left";
							$tab[$i][$j]["align"]="left";
							$tab[$i][$j]["valign"]="bottom";
							$tab[$i][$j]["show"]=true;
							$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
						}
						if($tab[$i][$j][$value]=="event_date"){
							$tab[$i][$j]["label"]=_DATE;
							$tab[$i][$j]["size"]="15";
							$tab[$i][$j]["label_align"]="left";
							$tab[$i][$j]["align"]="left";
							$tab[$i][$j]["valign"]="bottom";
							$tab[$i][$j]["show"]=true;
							//$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
							$tab[$i][$j]["value"] = $db->dateformat($tab[$i][$j]['value']);
						}
						if($tab[$i][$j][$value]=='event_type')
						{
							$tab[$i][$j]['value']= $db->show_string($hist->get_label_history_keyword($tab[$i][$j]['value']));
							$tab[$i][$j]['event_type']= $tab[$i][$j]['value'];
							$tab[$i][$j]["label"]=_ACTION;
							$tab[$i][$j]["size"]="8";
							$tab[$i][$j]["label_align"]="left";
							$tab[$i][$j]["align"]="left";
							$tab[$i][$j]["valign"]="bottom";
							$tab[$i][$j]["show"]=true;
						}
					}
				}
			}

			$title = _FOLDER_HISTORY_STAT.' '.$folderId;
			if($action <> "all")
			{
				$title .=' - '.$core_tools->is_var_in_history_keywords_tab($action);
			}
		//	echo '<h3>'.$title.'</h3>';
			?><div align="center"><?php
			$list->list_simple($tab, $i, $title, 'folder_id',  "folder_id",false);?></div>
			<?php
		}
		else
		{
			echo '<b>'._FOLDER_HISTORY_STAT.' '.$folderId;
			if($action <> "all")
			{
				echo ' - '.$core_tools->is_var_in_history_keywords_tab($action);
			}
			echo '</b> : '._NO_RESULTS;
		}
	}
	elseif(isset($_REQUEST['type_report']) && $_REQUEST['type_report'] == 'period')
	{
		$comp_where = '';
		$periodTitle = '';
		$periodTitle2 = '';
		if(isset($_REQUEST['date_start']) && $_REQUEST['date_start'] <> '')
		{
			$comp_where .= " AND ".$req->extract_date('event_date')." > '".$db->format_date_db($_REQUEST['date_start'])."'";
			$periodTitle.= _TITLE_STATS_DU.' '.$_REQUEST['date_start'].' ';
			$periodTitle2.= strtolower(_SINCE).' '.$_REQUEST['date_start'].' ';
		}

		if(isset($_REQUEST['date_fin']) && $_REQUEST['date_fin'] <> '')
		{
			$comp_where .= " AND ".$req->extract_date('event_date')." < '".$db->format_date_db($_REQUEST['date_fin'])."'";
			$periodTitle.= _TITLE_STATS_DU.' '.$_REQUEST['date_fin'].' ';
			$periodTitle2.= strtolower(_FOR).' '.$_REQUEST['date_fin'].' ';
		}

		$db->query("SELECT h.event_date, h.event_type, h.user_id, u.lastname, u.firstname FROM ".$_SESSION['tablename']['history']." h INNER JOIN ".$_SESSION['tablename']['users']." u ON h.user_id = u.user_id WHERE h.table_name = '".$_SESSION['tablename']['fold_folders']."' AND h.record_id = '".$folder_sys_id."' ".$comp_where);

		$tab=array();
		while($line = $db->fetch_array())
		{
			$temp= array();

			foreach (array_keys($line) as $resval)
			{
				if (!is_int($resval))
				{
					array_push($temp,array('column'=>$resval,'value'=>$line[$resval]));
				}
			}
			array_push($tab,$temp);
		}

		if (count($tab) > 0)
		{
			for ($i=0;$i<count($tab);$i++){

				for ($j=0;$j<count($tab[$i]);$j++){

					foreach(array_keys($tab[$i][$j]) as $value){

						if($tab[$i][$j][$value] == "folder_id"){
							$tab[$i][$j]["label"]=_ID;
							$tab[$i][$j]["size"]="4";
							$tab[$i][$j]["label_align"]="left";
							$tab[$i][$j]["align"]="center";
							$tab[$i][$j]["valign"]="bottom";
							$tab[$i][$j]["show"]=true;
							$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
							$tab[$i][$j]["value"]=$tab[$i][$j]['value'];
						}

						if($tab[$i][$j][$value]=="user_id")
						{
							$tab[$i][$j]["label"]=_USER;
							$tab[$i][$j]["size"]="15";
							$tab[$i][$j]["label_align"]="left";
							$tab[$i][$j]["align"]="left";
							$tab[$i][$j]["valign"]="bottom";
							$tab[$i][$j]["show"]=true;
							$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
						}
						if($tab[$i][$j][$value]=="lastname")
						{
							$tab[$i][$j]["label"]=_LASTNAME;
							$tab[$i][$j]["size"]="15";
							$tab[$i][$j]["label_align"]="left";
							$tab[$i][$j]["align"]="left";
							$tab[$i][$j]["valign"]="bottom";
							$tab[$i][$j]["show"]=true;
							$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
						}
						if($tab[$i][$j][$value]=="firstname")
						{
							$tab[$i][$j]["label"]=_FIRSTNAME;
							$tab[$i][$j]["size"]="15";
							$tab[$i][$j]["label_align"]="left";
							$tab[$i][$j]["align"]="left";
							$tab[$i][$j]["valign"]="bottom";
							$tab[$i][$j]["show"]=true;
							$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
						}
						if($tab[$i][$j][$value]=="event_type"){
							$tab[$i][$j]["label"]=_ACTION;
							$tab[$i][$j]["size"]="5";
							$tab[$i][$j]["label_align"]="left";
							$tab[$i][$j]["align"]="center";
							$tab[$i][$j]["valign"]="bottom";
							$tab[$i][$j]["show"]=true;
							//$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
							$tab[$i][$j]["value"] = $core_tools->is_var_in_history_keywords_tab($tab[$i][$j]['value']);
						}

						if($tab[$i][$j][$value]=="event_date"){
							$tab[$i][$j]["label"]=_DATE;
							$tab[$i][$j]["size"]="5";
							$tab[$i][$j]["label_align"]="left";
							$tab[$i][$j]["align"]="center";
							$tab[$i][$j]["valign"]="bottom";
							$tab[$i][$j]["show"]=true;
							//$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
							$tab[$i][$j]["value"] = $db->dateformat($tab[$i][$j]['value']);
						}
					}
				}
			}

			$title = _FOLDER_HISTORY_STAT.' "'.$folderId.'"'.' '.$periodTitle2.' : '.$line['nbr'];
			?><div align="center"><?php
			$list->list_simple($tab, $i, $title, 'folder_id',  "folder_id",false);	?></div>
			<?php
		}
		else
		{
			$title = _FOLDER_HISTORY_STAT.' "'.$folderId.'"'.'<br/>'.$periodTitle;
			echo '<h3>'.$title.'</h3><br/>'._NO_RESULTS;
		}
	} // fin elseif(isset($_REQUEST['rdChoix']) && $_REQUEST['rdChoix'] == 'byPeriod'){
}
else
{
	//$_SESSION['error'] = _STATS_ERROR_CHOSE_FOLDER;
?>
	<div class="error"><?php echo _FOLDER.' '._IS_EMPTY;?></div>
<?php
	exit();
}

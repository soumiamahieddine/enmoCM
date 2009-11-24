<?php

$core_tools = new core_tools();
$core_tools->load_lang();
$content = '';

function cmp($a, $b)
{
   	return strcmp(strtolower($a["label"]), strtolower($b["label"]));
}
usort($_SESSION['history_keywords'], "cmp");
$content .='<div id="params">';
$content .= '<form id="form" name="form" method="get" action="#">';
	$content .= '<table width="95%"  border="0" align="center">';
		$content .= '<tr>';
			$content .= '<td rowspan="3" width="25"><!--IMAGE--></td>';
			$content .= '<td align="left"><label for="folder_id">'._NUM.'&nbsp;</label><input type="text" id="folder_id" name="folder_id" value="" />&nbsp;<em><span><a href="javascript://" onclick="window.open(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=folder&page=select_folder_report\',\'select_folder\',\'width=912,height=600,resizable=yes\');" >'._CHOOSE_FOLDER.'</a></span></em></td>';
			$content .='<td rowspan="3" align="right" valign="middle"><input type="button" onclick="valid_histfolder( \''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=folder&page=get_folder_hist_stats_val\');" class="button" name="Submit1" value="'._VALIDATE.'" /></td>';
		$content .='</tr>';
		$content .='<tr>';
			$content .='<td align="left"><input type="radio" id="action" name="type_choice" value="action" checked="checked"><label for="action_id">'._TITLE_STATS_CHOICE_ACTION.'</label> ';
				$content .='<select name="action_id" id="action_id" >';
					$content .='<option value="all" selected="selected">'._ALL_ACTIONS.'</option>';
				for($i=0;$i<count($_SESSION['history_keywords']);$i++)
				{
					$content .='<option value="'.$_SESSION['history_keywords'][$i]['id'].'">'.$_SESSION['history_keywords'][$i]['label'].'</option>';
				}
				$content .='</select></td>';
		$content .='</tr>';
		$content .='<tr>';
			$content .='<td align="left"><input type="radio" id="period" name="type_choice" value="period"><label for="period">'._TITLE_STATS_CHOICE_PERIOD.'.&nbsp;</label>'._SINCE.'&nbsp;:&nbsp;<input name="datestart" type="text"  id="datestart" onclick="showCalender(this);"  />&nbsp;'._FOR.'&nbsp;:&nbsp;<input name="datefin" type="text"  id="datefin" onclick="showCalender(this);"/></td>';
		$content .='</tr>';
	$content .='</table>';
$content .='</form>';
$content .='</div>';
$content .='<div id="result_folderviewstat"></div>';
$js ='';

echo "{content : '".addslashes($content)."', exec_js : '".addslashes($js)."'}";
exit();

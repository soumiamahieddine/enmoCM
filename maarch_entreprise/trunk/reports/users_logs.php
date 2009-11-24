<?php

$core_tools = new core_tools();
$core_tools->load_lang();
$content = '';
$content .='<div id="params">';
$content .='<form id="form" name="form" method="get" action="">';
	$content .='<table width="95%"  border="0" align="center">';
		$content .= '<tr>';
			$content .= '<td width="25">&nbsp;</td>';
			$content .= '<td align="left"><input type="text" name="user_id" id="user_id" value="" />&nbsp;<em><label for="user_id"><a href="javascript://" onclick="window.open(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=reports&page=select_user_report\',\'select_user_report\',\'width=800,height=700,resizable=yes\');" >'._CHOOSE_USER2.'</a></label></em></td>';
			$content .= '<td align="right" valign="middle"><input type="button" onclick="valid_userlogs(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=reports&page=get_user_logs_stats_val\');" class="button" name="Submit1" value="'._VALIDATE.'" /></td>';
		$content .= '</tr>';
	$content .= '</table>';
$content .= '</form>';
$content .='</div>';
$content .='<div id="result_userlogsstat"></div>';
$js ='';

echo "{content : '".addslashes($content)."', exec_js : '".addslashes($js)."'}";
exit();

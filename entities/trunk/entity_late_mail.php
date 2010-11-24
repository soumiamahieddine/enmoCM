<?php
require_once('modules'.DIRECTORY_SEPARATOR."reports".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
$core_tools = new core_tools();
$rep = new reports();
$core_tools->load_lang();
$id = '';
if(isset($_REQUEST['arguments']) && !empty($_REQUEST['arguments']))
{
	$id = $rep->get_arguments_for_report($_REQUEST['arguments'], 'id');
}

$content = '';
$content .='<div id="params">';
	$content .='<form id="report_by_period_form" name="report_by_period_form" method="get" action="">';
	$content .='<input type="hidden" name="id_report" id="id_report" value="'.$id.'" />';
	$content .='<table width="100%" border="0">';
        $content .='<tr>';
          $content .='<td><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=stats_parameters.gif" alt="'._ADV_OPTIONS.'"  /></td>';
          $content .='<td align="left">';
          $content .='<p>';
            	$content .='<span>'._SHOW_FORM_RESULT.' : </span> <input type="radio" name="form_report" id="report_graph"  value="graph" checked="checked" /> '._GRAPH.' <input type="radio" name="form_report" id="report_array" value="array" /> '. _ARRAY;
            $content .='</p>';
            $content .='<br/>';
         
             $content .='<input type="radio" name="type_period" id="period_by_year" value="year" checked="checked" style="display:none;"/>';
          

            
        $content .='</td>';
        $content .='<td><input type="button" name="validate" value="'._VALIDATE.'" class="button" onclick="valid_report_by_period(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=entities&page=get_entity_late_mail\');" /></td>';
        $content .='</tr>';
       $content .='</table>';
	$content .='</form>';
$content .='</div>';
$content .='<div id="result_period_report"></div>';
$js ='valid_report_by_period(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=entities&page=get_entity_late_mail\');';


echo "{content : '".addslashes($content)."', exec_js : '".addslashes($js)."'}";
exit();

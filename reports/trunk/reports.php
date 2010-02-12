<?php
/*
*
*    Copyright 2008,2009 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*
/**
* @brief  Reports administration : laffect reports to groups
*
* @file
* @author Claire Yves Christian KPAKPO <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup reports
*/

$rep = new core_tools();
$db = new dbquery();
$db->connect();
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
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=reports&module=reports';
$page_label = _REPORTS;
$page_id = "reports";
$rep->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
$db->query("SELECT count(*) as total from ".$_SESSION['collections'][0]['view']." where status in ('NEW', 'COU')");
//$db->show();
$count_piece = $db->fetch_object();
if($rep->is_module_loaded('folder'))
{
	$db->query("SELECT count(*) as total from ".$_SESSION['tablename']['fold_folders']." where status = 'NEW'");
	$count_folder = $db->fetch_object();
}
?>
<h1><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=reports_b.gif&module=reports" alt="" /> <?php echo _REPORTS;?></h1>
<div id="inner_content" class="clearfix">
<p>
	<img src="<? echo $_SESSION['config']['businessappurl'];?>static.php?filename=contrat_mini.png" alt=""  /> <? echo _NB_TOTAL_DOC;?> : <b><? echo $count_piece->total; ?></b>
	<?php if($rep->is_module_loaded('folder'))
{?>
	&nbsp;&nbsp; <img src="<? echo $_SESSION['config']['businessappurl'];?>static.php?filename=folder_documents_mini.png" alt=""  /> <? echo _NB_TOTAL_FOLDER;?> : <b><? echo $count_folder->total; ?></b><?php 
}?>
	</p>
<?php include('modules'.DIRECTORY_SEPARATOR.'reports'.DIRECTORY_SEPARATOR.'user_reports.php');?>

</div>

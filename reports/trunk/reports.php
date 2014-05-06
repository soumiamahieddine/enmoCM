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
if (isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == "true") {
    $init = true;
}
$level = "";
if (isset($_REQUEST['level']) && ($_REQUEST['level'] == 2 
	|| $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 
	|| $_REQUEST['level'] == 1)
) {
    $level = $_REQUEST['level'];
}
$pagePath = $_SESSION['config']['businessappurl']
	. 'index.php?page=reports&module=reports';
$pageLabel = _REPORTS;
$pageId = "reports";
$rep->manage_location_bar($pagePath, $pageLabel, $pageId, $init, $level);
/***********************************************************/
$db->query(
	"select count(*) as total from " . $_SESSION['collections'][0]['view']
    . " inner join mlb_coll_ext on " . $_SESSION['collections'][0]['view']
    . ".res_id = mlb_coll_ext.res_id where " . $_SESSION['collections'][0]['view']
    . ".status not in ('DEL','BAD')"
);

//$db->show();

$countPiece = $db->fetch_object();
if ($rep->is_module_loaded('folder')) {
    $db->query(
    	"SELECT count(*) as total from " 
    	. $_SESSION['tablename']['fold_folders'] . " where status not in ('DEL','FOLDDEL')"
    );
    $countFolder = $db->fetch_object();
}
//$db->show();
?>
<h1><img src="<?php 
echo $_SESSION['config']['businessappurl'];
?>static.php?filename=reports_b.gif&module=reports" alt="" /> <?php 
echo _REPORTS;
?></h1>
<div id="inner_content" class="clearfix">
<p>
    <img src="<?php 
echo $_SESSION['config']['businessappurl'];
?>static.php?filename=contrat_mini.png" alt=""  /> <?php 
echo _NB_TOTAL_DOC;
?> : <b><?php echo $countPiece->total; ?></b>
    <?php 
if ($rep->is_module_loaded('folder')) {
	?>
    &nbsp;&nbsp; <img src="<?php 
    echo $_SESSION['config']['businessappurl'];
    ?>static.php?filename=folder_documents_mini.png" alt=""  /> <?php 
    echo _NB_TOTAL_FOLDER;
    ?> : <b><?php echo $countFolder->total; ?></b><?php
}
?>
    </p>
<?php 
include 'modules' . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR
	. 'user_reports.php';
?>

</div>

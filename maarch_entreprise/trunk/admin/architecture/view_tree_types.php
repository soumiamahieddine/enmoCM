<?php
/*
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
*/

/**
* @brief Architecture Administration view tree
*
* @file
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

$admin = new core_tools();
$admin->test_admin('admin_architecture', 'apps');
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
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=view_tree_types&admin=architecture';
$page_label = _VIEW_TREE_DOCTYPES;
$page_id = "view_tree_types";
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
unset($_SESSION['m_admin']);
$_SESSION['tree_foldertypes'] = array();
$db->query("select distinct foldertype_id, foldertype_label from ".$_SESSION['tablename']['fold_foldertypes']." order by foldertype_label");


while($res = $db->fetch_object())
{
	array_push($_SESSION['tree_foldertypes'], array("ID" => $res->foldertype_id, "LABEL" => $res->foldertype_label));
}
?>
<h1><img src="<?php  echo $_SESSION['config']['img'];?>/manage_architecture_b.gif" alt="" /> <?php  echo _VIEW_TREE_DOCTYPES;?></h1>
<div id="inner_content" class="clearfix">
	<table width="100%" border="0">
		<tr>
	    	<td>
				<iframe name="choose_tree" id="choose_tree" width="900px" height="40px" frameborder="0" scrolling="no" src="<?php  echo $_SESSION['config']['businessappurl']."index.php?display=true&admin=architecture&page=choose_tree";?>"></iframe>
			</td>
		</tr>
		<tr>
			<td>
				<iframe name="show_trees" id="show_trees" width="900px" height="600px" frameborder="0" scrolling="auto" src="<?php  echo $_SESSION['config']['businessappurl']."index.php?display=true&admin=architecture&page=show_trees";?>"></iframe>
			</td>
		</tr>
	</table>
</div>

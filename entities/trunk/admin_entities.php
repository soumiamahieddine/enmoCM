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
* File : admin_entities.php
*
* @brief Entities Administration summary Page
*
* @package  Maarch Framework 3.0
* @version 1
* @since 03/2009
* @license GPL
* @author  Cédric Ndoumba  <dev@maarch.org>
*/

$admin = new core_tools();
$admin->test_admin('manage_entities', 'entities');
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
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=admin_entities&module=entities';
$page_label = _ENTITIES;
$page_id = "admin_entities";
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
unset($_SESSION['m_admin']);

?>
<h1><img src="<?php  echo $_SESSION['urltomodules'];?>/entities/img/manage_entities_b.gif" alt="" /> <?php  echo _ENTITIES;?></h1>

<div id="inner_content" class="clearfix">
<h2 class="admin_subtitle block" ><?php  echo _ENTITIES;?></h2>
    <div class="admin_item" id="admin_entities_sub" title="<?php  echo _MANAGE_ENTITIES_DESC;?>" onclick="window.top.location='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=manage_entities&amp;module=entities';">
        <div class="sum_margin" >
				<strong><?php  echo _MANAGE_ENTITIES;?></strong>
		</div>
    </div>
    <div class="admin_item" id="admin_entities_tree_sub" title="<?php  echo _ENTITY_TREE_DESC;?>" onclick="window.top.location='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=view_tree_entities&amp;module=entities';">
        <div class="sum_margin">
				<strong><?php  echo _ENTITY_TREE;?></strong>
         </div>
    </div>
</div>

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
* @brief   Architecture Administration summary Page
*
* Architecture Administration summary Page
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

$admin = new core_tools();
$admin->test_admin('admin_contacts', 'apps');
/****************Management of the location bar  ************/
$init = false;
if(isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == "true")
{
    $init = true;
}
$level = "";
if(isset($_REQUEST['level']) && ($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1))
{
    $level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=admin_contacts&admin=contacts';
$page_label = _ADMIN_CONTACTS;
$page_id = "admin_contacts";
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
unset($_SESSION['m_admin']);
?>
<h1><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=manage_contact_b.gif" alt="" /> <?php  echo _ADMIN_CONTACTS_DESC;?></h1>
<div id="inner_content" class="clearfix">
    <h2 class="admin_subtitle block" ><?php  echo _ADMIN_CONTACTS;?></h2>
    <div  class="admin_item" id="admin_contact_types" title="<?php  echo _MANAGE_CONTACT_TYPES_DESC;?>" onclick="window.top.location='<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=contact_types';">
        <div class="sum_margin">
                <strong><?php  echo _MANAGE_CONTACT_TYPES;?></strong>
        </div>
    </div>

    <div class="admin_item" id="admin_contact_purposes" title="<?php  echo _MANAGE_CONTACT_PURPOSES_DESC;?>" onclick="window.top.location='<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=contact_purposes';">
        <div class="sum_margin">
                <strong><?php  echo _MANAGE_CONTACT_PURPOSES;?></strong>
         </div>
    </div>

    <div class="admin_item" id="admin_contacts_v2" title="<?php  echo _MANAGE_CONTACTS_DESC;?>" onclick="window.top.location='<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=contacts_v2';">
        <div class="sum_margin">
                <strong><?php  echo _MANAGE_CONTACTS;?></strong>
         </div>
    </div>

    <div class="admin_item" id="view_tree_contacts" title="<?php  echo _VIEW_TREE_CONTACTS_DESC;?>" onclick="window.top.location='<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=view_tree_contacts';">
        <div class="sum_margin">
                <strong><?php  echo _VIEW_TREE_CONTACTS;?></strong>
         </div>
    </div>

</div>

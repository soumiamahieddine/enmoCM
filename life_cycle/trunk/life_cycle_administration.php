<?php
/*
*   Copyright 2010 Maarch
*
*   This file is part of Maarch Framework.
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
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
* @brief life cycle Administration summary Page
*
* life cycle Administration summary Page
*
* @file
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup life_cycle
*/

$admin = new core_tools();
$admin->test_admin('admin_life_cycle', 'life_cycle');
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
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=life_cycle_administration&module=life_cycle';
$page_label = _ADMIN_LIFE_CYCLE_SHORT;
$page_id = "life_cycle_administration";
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
unset($_SESSION['m_admin']);
?>

<h1><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?module=life_cycle&filename=manage_lc_b.gif" alt="" /> <?php  echo _ADMIN_LIFE_CYCLE_SHORT;?></h1>

<div id="inner_content" class="clearfix">
<h2 class="admin_subtitle block" ><?php echo _ADMIN_LIFE_CYCLE;?></h1></h2>
	<div class="admin_item" id="admin_lc_policies" title="<?php  echo _MANAGE_LC_POLICIES;?>" onclick="window.top.location='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=lc_policies_management_controler&mode=list&module=life_cycle';">
		<div class="sum_margin" >
				<strong><?php  echo _MANAGE_LC_POLICIES;?></strong>
		</div>
	</div>
	<div class="admin_item" id="admin_lc_cycles" title="<?php  echo _MANAGE_LC_CYCLES;?>" onclick="window.top.location='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=lc_cycles_management_controler&mode=list&module=life_cycle';">
		<div class="sum_margin" >
				<strong><?php  echo _MANAGE_LC_CYCLES;?></strong>
		</div>
	</div>
	<div class="admin_item" id="admin_lc_cycle_steps" title="<?php  echo _MANAGE_LC_CYCLE_STEPS;?>" onclick="window.top.location='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=lc_cycle_steps_management_controler&mode=list&module=life_cycle';">
		<div class="sum_margin" >
				<strong><?php  echo _MANAGE_LC_CYCLE_STEPS;?></strong>
		</div>
	</div>
	<div class="admin_item" id="admin_docservers_locations" title="<?php  echo _MANAGE_DOCSERVERS_LOCATIONS;?>" onclick="window.top.location='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=docserver_locations_management_controler&mode=list&module=life_cycle';">
		<div class="sum_margin" >
				<strong><?php  echo _MANAGE_DOCSERVERS_LOCATIONS;?></strong>
		</div>
	</div>
	<div class="admin_item" id="admin_docservers" title="<?php  echo _MANAGE_DOCSERVERS;?>" onclick="window.top.location='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=docservers_management_controler&mode=list&module=life_cycle';">
		<div class="sum_margin" >
				<strong><?php  echo _MANAGE_DOCSERVERS;?></strong>
		</div>
	</div>
	<div class="admin_item" id="admin_docserver_types" title="<?php  echo _MANAGE_DOCSERVER_TYPES;?>" onclick="window.top.location='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=docserver_types_management_controler&mode=list&module=life_cycle';">
		<div class="sum_margin" >
				<strong><?php  echo _MANAGE_DOCSERVER_TYPES;?></strong>
		</div>
	</div>
    <!--<div class="admin_item" id="admin_folders" title="<?php  echo _READ_AGGREGATION;?>" onclick="window.top.location='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=read_aggregation_controler&module=moreq';">
        <div class="sum_margin" >
				<strong><?php  echo _READ_AGGREGATION;?></strong>
		</div>
    </div>
    <div class="admin_item" id="admin_folders" title="<?php  echo _UPDATE_AGGREGATION;?>" onclick="window.top.location='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=update_aggregation_controler&module=moreq';">
        <div class="sum_margin" >
				<strong><?php  echo _UPDATE_AGGREGATION;?></strong>
		</div>
    </div>
    <div class="admin_item" id="admin_folders" title="<?php  echo _DELETE_AGGREGATION;?>" onclick="window.top.location='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=delete_aggregation_controler&module=moreq';">
        <div class="sum_margin" >
				<strong><?php  echo _DELETE_AGGREGATION;?></strong>
		</div>
    </div>-->
</div>

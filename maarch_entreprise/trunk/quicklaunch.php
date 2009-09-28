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

/*
* @brief  Access to the baskets
*
*
* @file
* @author Loic Vinet <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
*/
session_name('PeopleBox');
session_start();

require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$core_tools->test_service('quicklaunch', "apps");
?>
<div id="welcome_box_left_quick_lunch" >
	<div class="block">
		<h2><?php echo _QUICKLAUNCH; ?> : </h2>
	</div>
	<div class="blank_space">&nbsp;</div>
	<table align="center">
		<tr >
		<?php
		$nb_max = 0;
		$menu_array = $_SESSION['menu'];
		foreach($menu_array as $element)
		{
			if($nb_max < 3)
			{
				if($element['id'] == 'physical_archive' && $element['show'] == true)
				{
						echo '<td><a href="index.php?page=boxes&module=physical_archive&reinit=true"><div class="bighome_physical_archive"><div class="label_for_bighome_physical_archive">'._PHYSICAL_ARCHIVE.'</div></div></a></td>';
						$nb_max++;
				}
				if ($element['id'] == 'adv_search_mlb' && $element['show'] == true)
				{
						echo '<td><a href="index.php?page=search_adv&dir=indexing_searching&reinit=true"><div class="bighome_search_adv"><div class="label_for_bighome_search_adv">'._ADV_SEARCH_TITLE.'</div></div></a></td>';
						$nb_max++;
				}
				if ($element['id'] == 'index_mlb' && $element['show'] == true )
				{
						echo '<td><a href="index.php?page=view_baskets&module=basket&baskets=IndexingBasket"><div class="bighome_indexing"><div class="label_for_bighome_indexing">'._INDEXING_MLB.'</div></div></a></td>';
						$nb_max++;
				}
			}
		}
		
		if ($nb_max <3)
		{
		?>
		<td><a href="index.php?page=modify_user&admin=users&reinit=true"><div class="bighome_userinfo"><div class="label_for_bighome_userinfo"><?php  echo _MY_INFO; ?></div></div></a> </td>
		<?php 
		}
		?> 
		</tr>
	</table>
	<div class="blank_space">&nbsp;</div>
</div>

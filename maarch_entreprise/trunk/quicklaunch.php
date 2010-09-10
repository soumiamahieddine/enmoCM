<?php
/*
*    Copyright 2008-2010 Maarch
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
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup apps
*/

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->test_service('quicklaunch', "apps");
?>
<div id="welcome_box_left_quick_lunch" >
	<div class="block">
		<h2><?php echo _QUICKLAUNCH; ?> : </h2>
	</div>
	<div class="blank_space">&nbsp;</div>
	<table class="center" summary="<?php echo _QUICKLAUNCH;?>">
		<tr >
		<?php
		$nb_max = 0;
		$menu_array = $_SESSION['menu'];
		foreach($menu_array as $element)
		{
			if($nb_max < 3)
			{
				if($element['id'] == 'physical_archive' && $element['show'] == true && (!isset($displayed_physical_archive) || isset($displayed_physical_archive) && $displayed_physical_archive <> true))
				{
						echo '<td><a href="index.php?page=boxes&amp;module=physical_archive&amp;reinit=true" class="bighome_physical_archive"><span >'._PHYSICAL_ARCHIVE.'</span></a></td>';
						$nb_max++;
						$displayed_physical_archive = true;
				}
				if ($element['id'] == 'index_mlb' && $element['show'] == true &&
				(!isset($displayed_index_mlb) || $displayed_index_mlb <> true))
				{
						echo '<td><a href="index.php?page=view_baskets&amp;module=basket&amp;baskets=IndexingBasket" class="bighome_indexing"><span>'._INDEXING_MLB.'</span></a></td>';
						$nb_max++;
						$displayed_index_mlb = true;
				}
				if ($element['id'] == 'adv_search_mlb' && $element['show'] == true && 
				(!isset($displayed_adv_search_mlb) || isset($displayed_index_mlb) && $displayed_adv_search_mlb <> true))
				{
						echo '<td><a href="index.php?page=search_adv&amp;dir=indexing_searching&amp;reinit=true" class="bighome_search_adv"><span >'._ADV_SEARCH_TITLE.'</span></a></td>';
						$nb_max++;
						$displayed_adv_search_mlb = true;
				}
				
			}
		}
		
		if ($nb_max <3)
		{
		?>
		<td><a href="index.php?page=modify_user&amp;admin=users&amp;reinit=true" class="bighome_userinfo"><span ><?php  echo _MY_INFO; ?></span></a> </td>
		<?php 
		}
		?> 
		</tr>
	</table>
	<div class="blank_space">&nbsp;</div>
</div>

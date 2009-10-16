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
* @brief  Lists the baskets for the current user (service)
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

if(!isset($_REQUEST['noinit']))
{
	$_SESSION['current_basket'] = array();
}
require_once($_SESSION['pathtomodules']."basket".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
/************/
$bask = new basket();
$db = new dbquery();
$db->connect();

?>
<div id="welcome_box_right">
<?php if($core_tools->test_service('display_basket_list','basket', false))
{
		if (count($_SESSION['user']['baskets']) > 0)
		{
			?>
			<div class="block">
			<h2><?php echo _MY_BASKETS; ?> : </h2>
			</div>
			<div class="blank_space">&nbsp;</div>
			<?
		}
		?> <ul class="basket_elem"><?
		$abs_basket = false;
		for ($i=0;$i<count($_SESSION['user']['baskets']);$i++)
		{
			if($_SESSION['user']['baskets'][$i]['abs_basket'] == true && !$abs_basket)
			{
				echo '</ul><h3>'._OTHER_BASKETS.' :</h3><ul class="basket_elem">';
				$abs_basket = true;
			}
			$nb = '';
			if(preg_match('/^CopyMailBasket/', $_SESSION['user']['baskets'][$i]['id']) && !empty($_SESSION['user']['baskets'][$i]['view']))
			{
				//$db->query('select r.RES_ID from '.$_SESSION['user']['baskets'][$i]['view']." r, ".$_SESSION['tablename']['ent_listinstance']." l where ".$_SESSION['user']['baskets'][$i]['clause']);
				//$nb = $db->nb_result();
			}
			elseif(!empty($_SESSION['user']['baskets'][$i]['table']))
			{
				if( trim($_SESSION['user']['baskets'][$i]['clause']) <> '')
				{
					$db->query('select RES_ID from '.$_SESSION['user']['baskets'][$i]['view']." where ".$_SESSION['user']['baskets'][$i]['clause']);
					$nb = $db->nb_result();
				}
			}

			if ($nb <> 0)
				$nb = "(".$nb.")";
			else
				$nb = "";
			if(!preg_match('/^IndexingBasket/', $_SESSION['user']['baskets'][$i]['id']))
			{
				echo '<li><a href="'.$_SESSION['config']['businessappurl'].'index.php?page=view_baskets&module=basket&baskets='.$_SESSION['user']['baskets'][$i]['id'].'"><img src="'.$_SESSION['urltomodules'].'basket/img/nature_send.gif" alt=""/> '.$_SESSION['user']['baskets'][$i]['desc'].'  <b>'.$nb.'</b> </a></li>';
			}
		}
		?>
	</ul>
	<div class="blank_space">&nbsp;</div>
<?php }?>
</div>




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
*/

/**
* @brief Displays absence management data in the user administration
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
*/
if($_SESSION['m_admin']['users']['Status'] == 'ABS')
{

 $content = '<h2>'._CANCEL_ABS.'</h2><div><form name="cancel_abs" id="cancel_abs" method="get" action="'.$_SESSION['urltomodules'].'basket/manage_cancel_abs.php"><p>'._REALLY_CANCEL_ABS.'</p><input type="submit" name="submit" value="'._VALIDATE.'" class="button" /> <input type="button" name="cancel" value="'._CANCEL.'" onclick="destroyModal(\'modal_cancel_abs\');" class="button" /></form></div>';

 ?><script >var abs_content = '<?php echo addslashes($content);?>';</script>
	<div>
        <h2<img src="<?php echo $_SESSION['urltomodules'];?>basket/img/missing_user_big.gif" alt="" /> <?php echo _ADMIN_ABS; ?> </a></h2>
        <p id="abs"><?php echo _USER_ABS; ?></p>
        <p><input type="button" onclick="createModal(abs_content, 'modal_cancel_abs', '100px', '300px');" value="<?php echo _CANCEL_ABS;?>" class="button"/></p>
    </div>
<?php
 }
 else
 {
  ?>
	<script language="javascript">
		function test_form()
		{
			var error_num = check_form_baskets("redirect_my_baskets_to");
			if( error_num == 1)
			{
				document.getElementById('redirect_my_baskets_to').submit();
			}
			else if(error_num == 2)
			{
				alert("<?php echo _FORMAT_ERROR_ON_USER_FIELD;?>");
			}
			else if(error_num == 3)
			{
				alert("<?php echo _BASKETS_OWNER_MISSING;?>");
			}
		 	else if(error_num == 4)
			{
				alert("<?php echo _CHOOSE_USER_TO_REDIRECT;?>");
			}
			else
			{
				alert("<?php echo _FORM_ERROR;?>");
			}
		}
	</script>
    <?php
    require_once($_SESSION['pathtomodules'].'basket'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_modules_tools.php');
	$bask = new basket();
	$arr_baskets = $bask->get_baskets($_SESSION['m_admin']['users']['UserId']);
	//print_r($arr_baskets);
	$modal_content = $bask->redirect_my_baskets_list($arr_baskets, count($arr_baskets), $_SESSION['m_admin']['users']['UserId'],"listingbasket specsmall");
	echo "<div>";
	?>
		<script>
			var modal_content = '<?php echo addslashes($modal_content);?>';
		</script>
		<h2><a href="javascript://" onclick="createModal(modal_content, 'modal_redirect', <?php if(count($arr_baskets) >0) {?>'400px', '800px'<?php }else{?>'100px', '320px'<?php }?>);autocomplete(<?php echo count($arr_baskets);?>, '<?php echo $_SESSION['urltomodules'].'basket/';?>autocomplete_users_list.php')"><img src="<?php echo $_SESSION['urltomodules'].'basket/img';?>/missing_user_big.gif" alt="" /> <?php echo _ADMIN_ABS; ?> </a></h2>
        <p id="abs"><?php echo _ADMIN_ABS_TXT; ?></p>
    </div>
<?php } ?>

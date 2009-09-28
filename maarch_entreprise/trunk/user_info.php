<?
/**
* File : user_info.php
*
* Page to show all data on a maarch user
*
* @package  Maarch Framework 3.0
* @version 3.0
* @since 10/2005
* @license GPL
* @author  Claire Figueras <dev@maarch.org>
*/
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
$core_tools = new core_tools();
$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header();
$func = new functions();
$db = new dbquery();
$db->connect();
if($_REQUEST['id'] == "")
{
	echo '<script type="text/javascript">window.resizeTo(300, 150);</script>';
	echo '<br/><br/><center>'._YOU_MUST_SELECT_USER.'</center><br/><br/><div align="center">
		<input name="close" type="button" value="'._CLOSE.'"  onclick="self.close();" class="button" />
		</div>';
}
else
{
	$db->query("select * from ".$_SESSION['tablename']['users']." where user_id = '".$db->protect_string_db($_REQUEST['id'])."'");
	if($db->nb_result() == 0)
	{
		$_SESSION['error'] = _THE_USER.' '._NOT_EXISTS;
		$state = false;
	}
	else
	{
		$user_data = array();
		$line = $db->fetch_object();
		$user_data['ID'] = $func->show_string($line->user_id);
		$user_data['LASTNAME'] = $func->show_string($line->lastname);
		$user_data['FIRSTNAME'] = $func->show_string($line->firstname);
		$user_data['PHONE'] = $func->show_string($line->phone);
		$user_data['MAIL'] = $func->show_string($line->email);
	}
	?>
	<script type="text/javascript">window.resizeTo(500, 350);</script>
<div class="popup_content">
	<br/>
	<h2 align="center"><img src="<? echo $_SESSION['config']['businessappurl'];?>img/account_off.gif" alt="<? echo _USER_DATA;?>" /> <? echo _USER_DATA;?></h2>	<br/>
	<form name="frmuserdata" id="frmuserdata" method="post" action="#" class="forms addforms">

		 <p id="lastname_p">
			<label for="lastname"><?php echo _LASTNAME; ?> : </label>
			<input name="lastname" type="text"  id="lastname" value="<?php echo $func->show($user_data['LASTNAME']); ?>" readonly="readonly"/>
		 </p>
		 <p id="firstname_p">
			<label for="firstname"><?php echo _FIRSTNAME; ?> : </label>
			<input name="firstname" type="text"  id="firstname" value="<?php echo $func->show($user_data['FIRSTNAME']); ?>" readonly="readonly"/>
		 </p>
		  <p>
			<label for="phone"><?php echo _PHONE; ?> : </label>
			<input name="phone" type="text"  id="phone" value="<?php echo $func->show($user_data['PHONE']); ?>" readonly="readonly"/>
		 </p>
		 <p>
			<label for="mail"><?php echo _MAIL; ?> : </label>
			<input name="mail" type="text" id="mail" value="<?php echo $func->show($user_data['MAIL']); ?>" readonly="readonly"/>
		 </p>
		 <?php
		if($core_tools->is_module_loaded('entities'))
		{
			require_once($_SESSION['pathtomodules'].'entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_manage_entities.php');
			$ent = new entity();
			$entities = $ent->get_entities_of_user($_REQUEST['id']);
			//$db->show_array($entities);
			?>
			<p>
				<label for="entities"><?php echo _ENTITIES;?></label>
				<select multiple="multiple" name="entities"  size="7">
				<?php for($i=0; $i<count($entities);$i++)
				{
					?><option value=""><?php
					if($entities[$i]['PRIMARY'] == 'Y')
					{
						echo '<b>'.$entities[$i]['LABEL'].'</b>';
					}
					else
					{
					 	echo $entities[$i]['LABEL'];
					}
					?></option><?php
				}?>
				</select>
			</p>
			<?php
		}
		 ?>
		<p class="buttons">
			<input name="close" type="button" value="<? echo _CLOSE;?>"  onclick="self.close();" class="button" />
		</p>
	</form >
</div>
	<?
}
?>

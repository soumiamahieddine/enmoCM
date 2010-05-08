<?php
/**
* File : users_entities_form.php
*
* Form to choose a entity in the user_entities management (iframe included in the user_entities management)
*
* @package  Maarch Framework 3.0
* @version 1
* @since 03/2009
* @license GPL
* @author  Cédric Ndoumba  <dev@maarch.org>
* @author  Claire Figueras  <dev@maarch.org>
*/

$admin = new core_tools();
//$admin->test_admin('manage_entities', 'entities');

$admin->load_lang();
require_once('modules'.DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_users_entities.php');

$func = new functions();


if(isset($_REQUEST['removeEntity']) && !empty($_REQUEST['removeEntity']))
{
	if(count($_REQUEST['entities'])>0)
	{
		$tab = array();
    	for ($i=0; $i<count($_REQUEST['entities']); $i++)
		{
			array_push($tab,$_REQUEST['entities'][$i]);
 		}
		$usersEnt = new users_entities();
		$usersEnt->remove_session($tab);
   	}
	$_SESSION['m_admin']['load_entities'] = false;
}

if(isset($_REQUEST['setPrimary']))
{
	if(count($_REQUEST['entities'])>0)
	{
    		$usersEnt = new users_entities();
			$usersEnt->erase_primary_entity_session();
			$usersEnt->set_primary_entity_session($_REQUEST['entities'][0]);
   	}

	$_SESSION['m_admin']['load_entities'] = false;

}

//here we loading the html
$admin->load_html();
//here we building the header
$admin->load_header(_USER_ENTITIES_TITLE, true, false);
?>

<body id="iframe">
<div class="block">
<form name="userEntity" method="get" action="<?php  $_SESSION['config']['businessappurl'];?>index.php" >
<input type="hidden" name="display" value="true" />
<input type="hidden" name="module" value="entities" />
<input type="hidden" name="page" value="users_entities_form" />
 <h2 class="tit"> <?php  echo _USER_ENTITIES_TITLE; ?> :</h2>

<?php

	if(empty($_SESSION['m_admin']['entity']['entities'])   )
	{
		echo _USER_BELONGS_NO_ENTITY.".<br/>";
		//echo _CHOOSE_ONE_ENTITY.".<br/>";
	}
	else
	{
		for($theline = 0; $theline < count($_SESSION['m_admin']['entity']['entities']) ; $theline++)
		{
				if( $_SESSION['m_admin']['entity']['entities'][$theline]['PRIMARY'] == 'Y')
				{
					?><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=arrow_primary.gif&module=entities" alt="<?php  echo _PRIMARY_ENTITY;?>" title="<?php  echo _PRIMARY_ENTITY;?>" /> <?php
				}
				else
				{
					echo "&nbsp;&nbsp;&nbsp;&nbsp;";
				}
				?>
				<input type="checkbox"  class="check" name="entities[]" value="<?php  echo $_SESSION['m_admin']['entity']['entities'][$theline]['ENTITY_ID']; ?>" ><?php if(isset($_SESSION['m_admin']['entity']['entities'][$theline]['SHORT_LABEL']) && !empty($_SESSION['m_admin']['entity']['entities'][$theline]['SHORT_LABEL'])){ echo $_SESSION['m_admin']['entity']['entities'][$theline]['SHORT_LABEL'] ; }else{ echo $_SESSION['m_admin']['entity']['entities'][$theline]['LABEL'];}?><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i><?php  echo $_SESSION['m_admin']['entity']['entities'][$theline]['ROLE']; ?></i><br/></input>
				<?php
		}
		 ?> <br/><input class="button" type="submit" name="removeEntity" value="<?php  echo _DELETE_ENTITY; ?>" /><br/><br/>
<?php 	}

	if (count($_SESSION['m_admin']['entity']['entities']) < $_SESSION['m_admin']['nbentities']  || empty($_SESSION['m_admin']['entity']['entities']))
	{
	?>
		<input class="button" type="button" name="addEntity" onClick="window.open('<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=entities&page=add_users_entities', 'add', 'toolbar=no, status=no, width=550, height=270, left=500, top=300, scrollbars=no, top=no, location=no, resizable=yes, menubar=no')" value="<?php  echo _ADD_TO_ENTITY; ?>" />
	<?php
	}
	?>
	<br/><br/>
	<?php  if (count($_SESSION['m_admin']['entity']['entities']) > 0)
	{
	?>
		<input type="submit" class="button" name="setPrimary" value="<?php  echo _CHOOSE_PRIMARY_ENTITY; ?>" />
	<?php
	}
	?>
	</form>
	</div>
<?php $admin->load_js();?>
</body>
</html>

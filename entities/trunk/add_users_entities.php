<?php
/**
* File : add_users_entities.php
*
* Form to add a entity to a user, pop up page
*
* @package  Maarch Framework 3.0
* @version 1
* @since 03/2009
* @license GPL
* @author  Cédric Ndoumba  <dev@maarch.org>
*/

$admin = new core_tools();
$admin->load_lang();
$admin->test_admin('manage_entities', 'entities');

require_once('modules'.DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_manage_entities.php');
$ent = new entity();

$except = array();
/* If you want that a user can not belong to an entity and one of this entity subentity, decomment these lines
for($i = 0; $i < count($_SESSION['m_admin']['entity']['entities']); $i++)
{
	$except[] = $_SESSION['m_admin']['entity']['entities'][$i]['ENTITY_ID'];
}
*/

//here we loading the html
$admin->load_html();
//here we building the header
$admin->load_header('', true, false);
$time = $admin->get_session_time_expire();

$entities = array();
if($_SESSION['user']['UserId'] == 'superadmin')
{
	$entities = $ent->getShortEntityTree($entities,'all', '', $except);
}
else
{
	$entities = $ent->getShortEntityTree($entities,$_SESSION['user']['entities'],  '' , $except);
}
?>
<body onLoad="setTimeout(window.close, <?php  echo $time;?>*60*1000);">
<div class="popup_content">
<h2 class="tit"><?php  echo USER_ADD_ENTITY;?></h2>
<form name="chooseEntity" id="chooseEntity" method="get" action="<?php  $_SESSION['config']['businessappurl'];?>index.php" class="forms">
<input type="hidden" name="display" value="true" />
<input type="hidden" name="module" value="entities" />
<input type="hidden" name="page" value="choose_user_entity" />
<p>
	<label for="groupe"> <?php  echo _CHOOSE_ENTITY;?> : </label>
	<select name="entity" id="entity" size="10">
	<?php
		for($i=0; $i<count($entities);$i++)
		{
		?>
			<option value="<?php  echo $entities[$i]['ID'];?>" ><?php  if(isset($entities[$i]['SHORT_LABEL']) && !empty($entities[$i]['SHORT_LABEL'])){ echo $entities[$i]['SHORT_LABEL'];}else{echo $entities[$i]['LABEL'];}?></option><?php
		}
	?>
	</select>
</p>
<br/>
<p>
	<label for="role"><?php  echo _ROLE;?> : </label>
	<input type="text"  name="role" id="role" />
</p>
<br/>
<p class="buttons">
	<input type="submit" class="button" name="Submit" value="<?php  echo _VALIDATE;?>"  />
	<input type="button" name="cancel" class="button"  value="<?php  echo _CANCEL;?>" onClick="window.close()"/>
	<input type="hidden" name="Submit" value="Validate"  />
</p>

</form>
</div>
<?php $core_tools->load_js();?>
</body>
</html>

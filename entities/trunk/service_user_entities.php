<?php
if($_SESSION['service_tag'] == 'user_init')
{
	require_once('modules'.DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_users_entities.php');
	$ue = new users_entities();
	$ue->service_load_entities($_SESSION['m_admin']['mode']);

}
elseif($_SESSION['service_tag'] == 'formuser')
{
?>
<div id="inner_content" class="clearfix">
	<div id="add_box" class="">
        <p>
            <?php  if($_SESSION['m_admin']['users']['UserId'] <> "superadmin")
			{?>
              <iframe name="user_entities" id="user_entities" class="frameform2" src="<?php  echo $_SESSION['config']['businessappurl'].'index.php?display=true&module=entities&page=users_entities_form';?>" frameborder="0"></iframe>
            <?php  } ?>
        </p>
</div>
<?php

}
elseif($_SESSION['service_tag'] == 'users_list_init')
{
	$_SESSION['m_admin']['load_entities'] = true;

}
elseif($_SESSION['service_tag'] == 'user_check')
{
	require_once('modules'.DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_users_entities.php');
	$ue = new users_entities();
	$ue->checks_info($_SESSION['m_admin']['mode']);
}
elseif($_SESSION['service_tag'] == 'users_add_db' || $_SESSION['service_tag'] == 'users_up_db')
{
	require_once('modules'.DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_users_entities.php');
	$ue = new users_entities();
	$ue->load_db(false);
}
?>

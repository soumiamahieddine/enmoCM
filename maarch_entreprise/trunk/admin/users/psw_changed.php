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
* @brief  Form to modify user password at the first connexion
*
*
* @file
* @author  Claire Figueras  <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/
$core_tools = new core_tools();
$core_tools->load_lang();

$db = new dbquery();
$db->connect();
$default_password = md5($_SESSION['config']['userdefaultpassword']);

$db->query("UPDATE ".$_SESSION['tablename']['users']." set password = '".$default_password."' , change_password ='Y' where user_id = '".$_SESSION['m_admin']['users']['UserId']."'");
if($_SESSION['history']['usersadd'] == "true")
{
	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
	$hist = new history();
	$hist->add($_SESSION['tablename']['users'], $_SESSION['m_admin']['users']['UserId'],"UP",_NEW_PASSWORD_USER." : ".$_SESSION['m_admin']['users']['LastName']." ".$_SESSION['m_admin']['users']['FirstName'], $_SESSION['config']['databasetype']);
}

//here we loading the html
$core_tools->load_html();
//here we building the header
$core_tools->load_header(_PASSWORD_MODIFICATION, true, false);
$time = $core_tools->get_session_time_expire();
?>

<body id="pop_up" onLoad="setTimeout(window.close, <?php  echo $time;?>*60*1000);">
<h2 class="tit"><?php  echo _PASSWORD_MODIFICATION;?></h2>


<p ><?php  echo _PASSWORD_FOR_USER;?> <b><?php  echo $_SESSION['m_admin']['users']['UserId'] ; ?></b> <?php  echo _HAS_BEEN_RESET;?>.
</p>
<p><?php  echo _NEW_PASW_IS." '".$_SESSION['config']['userdefaultpassword']."'";?></p>
<p >
<?php  echo _DURING_NEXT_CONNEXION;?>, <?php  echo $_SESSION['m_admin']['users']['UserId'] ; ?> <?php  echo _MUST_CHANGE_PSW;?>.
</p>
<br/>
<p class="buttons" ><input type="button" class="button" onclick="window.close()" name="close" value="<?php  echo _CLOSE_WINDOW;?>" /></p>
<?php $core_tools->load_js();?>
</body>
</html>

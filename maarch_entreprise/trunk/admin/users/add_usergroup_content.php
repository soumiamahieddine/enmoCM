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
* @brief Form to add a grant to a user, pop up page (User administration)
*
*
* @file
* @author  Claire Figueras  <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

include('core/init.php');

require_once("core/class/class_functions.php");
require("core/class/class_core_tools.php");

$core_tools = new core_tools();
//here we loading the lang vars
$core_tools->load_lang();
$core_tools->test_admin('admin_users', 'apps');
require_once("core/class/class_db.php");

$ugc = new dbquery();
//loading all the enabled groups in an array
$ugc->connect();
$ugc->query("select group_id, group_desc from ".$_SESSION['tablename']['usergroups']." where enabled = 'Y' order by group_desc asc");

$tab=array();
$i=0;
while($value = $ugc->fetch_array())
{
	$tab[$i]['ID'] = $value[0] ;
	$tab[$i]['LABEL'] = $ugc->show_string($value[1]);
	$i++;
}

$tab2 = array();
if ( count($_SESSION['m_admin']['users']['groups']) > 0 )
{

	//$tabtmp = array();
	for($i=0; $i < count($_SESSION['m_admin']['users']['groups']); $i++)
	{
		array_push($tab2, array('ID'=> $_SESSION['m_admin']['users']['groups'][$i]['GROUP_ID'], 'LABEL' => $_SESSION['m_admin']['users']['groups'][$i]['LABEL']));
	}
}

$res = $tab;
for($j=0; $j < count($tab); $j++)
{
	for($k=0; $k < count($tab2); $k++)
	{
		if($tab[$j]['ID'] ==  $tab2[$k]['ID'])
		{
			unset($res[$j]);
			break;
		}
	}
}
$res = array_values($res);


//here we loading the html
$core_tools->load_html();
//here we building the header
$core_tools->load_header();
$time = $core_tools->get_session_time_expire();
?>
<body onLoad="setTimeout(window.close, <?php  echo $time;?>*60*1000);">
<div class="popup_content">
<h2 class="tit"><?php  echo _ADD_GROUP;?></h2>
<form name="chooseGroup" method="get" action="choose_group.php" class="forms">

<p>
	<label for="groupe"> <?php  echo _CHOOSE_GROUP;?> : </label>
	<select name="groupe" id="groupe" >
<?php

for($j=0; $j<count($res); $j++)
{
	if(!empty($res[$j]['LABEL']))
	{
?>
	<option value="<?php  echo $res[$j]['ID'] ?>"><?php   echo $res[$j]['LABEL']; ?></option>
<?php
	}
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
</body>
</html>

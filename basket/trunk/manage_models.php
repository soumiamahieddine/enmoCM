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
* @brief   Frame : in the administration of entities, used to modify or del a model in a department
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
*/
session_name('PeopleBox');
session_start();

echo "<center>managment of the models coming soon...</center>";
exit;

if(file_exists($_SESSION['config']['lang'].'.php'))
{
	include($_SESSION['config']['lang'].'.php');
}
else
{
	$_SESSION['error'] = "Language file missing...<br/>";
}

require("class_functions.php");
require_once("class_db.php");

$db = new dbquery();
$db->connect();

if($_REQUEST['remove'] && count($_REQUEST['models'])>0)
{
	for($i=0; $i < count($_REQUEST['models']);$i++)
	{
		$db->query("delete from ".$_SESSION['tablename']['model_service']." where ID_SERVICE = '".$_SESSION['m_admin']['services']['ID']."' and ID_MODEL = ".$_REQUEST['models'][$i]);
	}
}

$models = array();
$db->query("select m.ID, m.LABEL from ".$_SESSION['tablename']['model_service']." ms, ".$_SESSION['tablename']['models']." m where ms.ID_SERVICE = '".$_SESSION['m_admin']['services']['ID']."' and ms.ID_MODEL = m.ID ");

while($res = $db->fetch_object())
{
	array_push($models, array('ID' => $res->ID, 'LABEL' => $res->LABEL));
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $_SESSION['config']['lang'] ?>" lang="<?php echo $_SESSION['config']['lang'] ?>">
<head>
	<title><?php echo $_SESSION['config']['applicationname']; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta http-equiv="Content-Language" content="<?php echo $_SESSION['config']['lang'] ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo $_SESSION['config']['css']; ?>" media="screen" />
	<!--[if lt IE 7.0]>  <link rel="stylesheet" type="text/css" href="<?php echo $_SESSION['config']['css_IE']; ?>" media="screen" />  <![endif]-->
	<!--[if gte IE 7.0]>  <link rel="stylesheet" type="text/css" href="<?php echo $_SESSION['config']['css_IE7']; ?>" media="screen" />  <![endif]-->
	<script type="text/javascript" src="js/functions.js"></script>
</head>

<body>
<form name="manage_models" id="manage_models" method="get">

<br/>
<h3 class="sstit"><?php echo _ASSOCIATED_MODELS;?>:</h3>
<?php
if(count($models) > 0)
{
	?>
	<ul>
	<?php
	for($i=0; $i < count($models); $i++)
	{
?>
	<li><input type="checkbox" class="check" name="models[]" value="<?php echo $models[$i]['ID']; ?>" class="check" />&nbsp;&nbsp;&nbsp;<a href="index.php?page=model_up&id=<?php echo $models[$i]['ID'];?>" target="_parent"  ><?php echo $models[$i]['LABEL']; ?></a>
	</li>
<?php
	}
	?>
	</ul>
	<br/><br/>
	<input type="submit" name="remove" id="remove" value="<?php echo _DELETE;?>" class="button" />
	<?php
}
else
{
?>
<div >&nbsp;&nbsp;&nbsp;<i><?php echo _NO_DEFINED_MODEL;?>.</i></div>
<?php
}
?>
</form>
</body>
</html>

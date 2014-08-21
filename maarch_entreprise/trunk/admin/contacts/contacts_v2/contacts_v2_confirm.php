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
* @brief  contacts list
*
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/
$admin = new core_tools();
if(!$admin->test_admin('admin_contacts', 'apps', false)){
	$admin->test_admin('my_contacts', 'apps');
}
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_contacts_v2.php");
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
require_once 'core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_request.php';
$contact = new contacts_v2();
$db = new dbquery();
$db->connect();

/****************Management of the location bar  ************/
$init = false;
if(isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == "true")
{
    $init = true;
}
$level = "";
if(isset($_REQUEST['level']) && ($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1))
{
    $level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=contacts_v2_confirm';
if($_GET['mode'] == 'up'){
	$page_label = _CONTACTS_CONFIRMATION_MODIFICATION;
} else {
	$page_label = _CONTACTS_CONFIRMATION;
}
$page_id = "contacts_v2_confirm";
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
?>

<h1><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_add_b.gif" alt="" />
	<?php if($_GET['mode'] == 'up'){
		echo _CONTACTS_CONFIRMATION_MODIFICATION;
	} else {
		echo _CONTACTS_CONFIRMATION;
	}?>
</h1>
<br/>
	<?php $contact->get_contact_form();?>
<br/>
	<?php echo _YOUR_CONTACT_LOOKS_LIKE_ANOTHER;?> 
<br/>
<br/>
<?php

$query = $contact->query_contact_exists($_GET['mode']);
$db->query($query);
$tab = array();
while ($res = $db->fetch_array()){
    $temp= array();
    foreach (array_keys($res) as $resval)
    {
        if (!is_int($resval))
        {
            array_push($temp,array('column'=>$resval,'value'=>$res[$resval]));
        }
    }
	array_push($tab, $temp);
}

$list = new list_show();

	for ($i=0;$i<count($tab);$i++)
	{
		for ($j=0;$j<count($tab[$i]);$j++)
		{
			foreach(array_keys($tab[$i][$j]) as $value)
			{
				if($tab[$i][$j][$value] == "contact_id"){
					$tab[$i][$j]["label"]=_ID;
					$tab[$i][$j]["size"]="20";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["value"]=$tab[$i][$j]['value'];
				}
				if($tab[$i][$j][$value] == "contact_type"){
					$tab[$i][$j]["label"]=_CONTACT_TYPE;
					$tab[$i][$j]["size"]="20";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["value"]=$contact->get_label_contact($tab[$i][$j]['value'], $_SESSION['tablename']['contact_types']);
				}
				if($tab[$i][$j][$value] == "society"){
					$tab[$i][$j]["label"]=_SOCIETY;
					$tab[$i][$j]["size"]="20";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["value"]=$tab[$i][$j]['value'];
				}
				if($tab[$i][$j][$value] == "firstname"){
					$tab[$i][$j]["label"]=_FIRSTNAME;
					$tab[$i][$j]["size"]="20";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["value"]=$tab[$i][$j]['value'];
				}
				if($tab[$i][$j][$value] == "lastname"){
					$tab[$i][$j]["label"]=_LASTNAME;
					$tab[$i][$j]["size"]="20";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
					$tab[$i][$j]["value"]=$tab[$i][$j]['value'];
				}
			}
		}
	}

?>
<div align="center">
	<?php $list->list_simple($tab, $i, '', 'contact_id', 'contact_id', false, "", 'listing spec', '', 400, 500); ?>
</div>
<br/>
<br/>
<?php 
	if($_GET['mode'] == 'up'){
		echo _CONFIRM_EDIT_CONTACT;
	} else {
		echo _CONFIRM_CREATE_CONTACT;
	}

	if($_GET['mycontact'] == 'N'){ ?> 
		&nbsp; &nbsp; &nbsp; &nbsp;<input class="button" type="button" value="<?php echo _YES;?>" name="Submit" onclick="javascript:window.location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=contacts_v2_up_db&confirm=Y&mode=<?php echo $_GET['mode'];?>';"> 
		&nbsp; &nbsp; &nbsp; &nbsp;<input class="button" type="button" value="<?php echo _NO;?>" name="Cancel" onclick="javascript:window.location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=contacts_v2';">
<?php 
	} else { ?>
		&nbsp; &nbsp; &nbsp; &nbsp;<input class="button" type="button" value="<?php echo _YES;?>" name="Submit" onclick="javascript:window.location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=my_contact_up_db&dir=my_contacts&confirm=Y&mode=<?php echo $_GET['mode'];?>';"> 
		&nbsp; &nbsp; &nbsp; &nbsp;<input class="button" type="button" value="<?php echo _NO;?>" name="Cancel" onclick="javascript:window.location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=my_contacts&dir=my_contacts&load';">
<?php 
	} ?>
	
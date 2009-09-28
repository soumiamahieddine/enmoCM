<?php
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
$func = new functions();
$db = new dbquery();
$contact = array();
$core_tools = new core_tools();
$core_tools->load_lang();
$contact['IS_CORPORATE_PERSON'] = $_REQUEST['is_corporate'];
if($contact['IS_CORPORATE_PERSON'] == 'Y')
{
	$contact['SOCIETY'] = $func->wash($_REQUEST['society'], "no", _SOCIETY." ");
	$contact['LASTNAME'] = '';
}
else
{
	$contact['LASTNAME'] = $func->wash($_REQUEST['lastname'], "no", _LASTNAME, 'yes', 0, 255);
	if ($_REQUEST['society'] <> '')
	{
		$contact['SOCIETY'] = $func->wash($_REQUEST['society'], "no", _SOCIETY." ", 'yes', 0, 255);
	}
	else
	{
		$contact['SOCIETY'] = '';
	}
}
if ($_REQUEST['title'] <> '')
{
	$contact['TITLE'] = $func->wash($_REQUEST['title'], "no", _TITLE2." ", 'yes', 0, 255);
}
else
{
	$contact['TITLE'] = '';
}
if ($_REQUEST['firstname'] <> '')
{
	$contact['FIRSTNAME'] = $func->wash($_REQUEST['firstname'], "no", _FIRSTNAME." ", 'yes', 0, 255);
}
else
{
	$contact['FIRSTNAME'] = '';
}
if ($_REQUEST['func'] <> '')
{
	$contact['FUNCTION'] = $func->wash($_REQUEST['func'], "no", _FUNCTION." ", 'yes', 0, 255);
}
else
{
	$contact['FUNCTION'] = '';
}
if ($_REQUEST['num'] <> '')
{
	$contact['ADD_NUM'] = $func->wash($_REQUEST['num'], "no", _NUM." ", 'yes', 0, 32);
}
else
{
	$contact['ADD_NUM'] = '';
}
if ($_REQUEST['street'] <> '')
{
	$contact['ADD_STREET'] = $func->wash($_REQUEST['street'], "no", _STREET." ", 'yes', 0, 255);
}
else
{
	$contact['ADD_STREET'] = '';
}
if ($_REQUEST['add_comp'] <> '')
{
	$contact['ADD_COMP'] = $func->wash($_REQUEST['add_comp'], "no", ADD_COMP." ", 'yes', 0, 255);
}
else
{
	$contact['ADD_COMP'] = '';
}
if ($_REQUEST['town'] <> '')
{
	$contact['ADD_TOWN'] = $func->wash($_REQUEST['town'], "no", _TOWN." " , 'yes', 0, 255);
}
else
{
	$contact['ADD_TOWN'] = '';
}
if ($_REQUEST['cp'] <> '')
{
	$contact['ADD_CP'] = $func->wash($_REQUEST['cp'], "no", _POSTAL_CODE, 'yes', 0, 255);
}
else
{
	$contact['ADD_CP'] = '';
}
if ($_REQUEST['country'] <> '')
{
	$contact['ADD_COUNTRY'] = $func->wash($_REQUEST['country'], "no", _COUNTRY, 'yes', 0, 255);
}
else
{
	$contact['ADD_COUNTRY'] = '';
}
if ($_REQUEST['phone'] <> '')
{
	$contact['PHONE'] = $func->wash(trim($_REQUEST['phone']), "num", _PHONE, 'yes', 0, 20);
}
else
{
	$contact['PHONE'] = '';
}
if ($_REQUEST['mail'] <> '')
{
	$contact['MAIL'] = $func->wash($_REQUEST['mail'], "mail", _MAIL, 'yes', 0, 255);

}
else
{
	$contact['MAIL'] = '';
}
if ($_REQUEST['comp_data'] <> '')
{
	$contact['OTHER_DATA'] = $func->wash($_REQUEST['comp_data'], "no", _COMP_DATA);
}
else
{
	$contact['OTHER_DATA'] = '';
}
if(!empty($_SESSION['error']))
{
	echo "{status : 1, error_txt : '".addslashes($_SESSION['error'])."'}";
	$_SESSION['error'] = '';
	exit();
}
else
{
	$db->connect();
	if($contact['IS_CORPORATE_PERSON'] == 'Y')
	{
		$db->query("INSERT INTO ".$_SESSION['tablename']['contacts']." (society, phone, email, address_num, address_street, address_complement, address_town, address_postal_code, address_country, other_data, is_corporate_person, user_id) values ('".$func->protect_string_db($contact['SOCIETY'])."', '".$func->protect_string_db($contact['PHONE'])."', '".$func->protect_string_db($contact['MAIL'])."', '".$func->protect_string_db($contact['ADD_NUM'])."','".$func->protect_string_db($contact['ADD_STREET'])."', '".$func->protect_string_db($contact['ADD_COMP'])."', '".$func->protect_string_db($contact['ADD_TOWN'])."', '".$func->protect_string_db($contact['ADD_CP'])."', '".$func->protect_string_db($contact['ADD_COUNTRY'])."', '".$func->protect_string_db($contact['OTHER_DATA'])."', '".$func->protect_string_db($contact['IS_CORPORATE_PERSON'])."', '".$func->protect_string_db($_SESSION['user']['UserId'])."')");
	}
	else
	{
		$db->query("INSERT INTO ".$_SESSION['tablename']['contacts']." (lastname , firstname , society , function , phone , email , address_num, address_street, address_complement, address_town, address_postal_code, address_country, other_data, title, is_corporate_person, user_id) values ('".$func->protect_string_db($contact['LASTNAME'])."', '".$func->protect_string_db($contact['FIRSTNAME'])."', '".$func->protect_string_db($contact['SOCIETY'])."', '".$func->protect_string_db($contact['FUNCTION'])."', '".$func->protect_string_db($contact['PHONE'])."', '".$func->protect_string_db($contact['MAIL'])."', '".$func->protect_string_db($contact['ADD_NUM'])."','".$func->protect_string_db($contact['ADD_STREET'])."', '".$func->protect_string_db($contact['ADD_COMP'])."', '".$func->protect_string_db($contact['ADD_TOWN'])."',  '".$func->protect_string_db($contact['ADD_CP'])."','".$func->protect_string_db($contact['ADD_COUNTRY'])."','".$func->protect_string_db($contact['OTHER_DATA'])."','".$func->protect_string_db($contact['TITLE'])."','".$func->protect_string_db($contact['IS_CORPORATE_PERSON'])."','".$func->protect_string_db($_SESSION['user']['UserId'])."')");
	}

	if($contact['IS_CORPORATE_PERSON'] == 'N')
	{
		$db->query("select contact_id, lastname, firstname, society from ".$_SESSION['tablename']['contacts']." where lastname = '".$func->protect_string_db($contact['LASTNAME'])."' and enabled = 'Y' order by contact_id desc");
		$res = $db->fetch_object();
		if(empty($res->society))
		{
			$value_contact = $res->lastname.', '.$res->firstname.' ('.$res->contact_id.')';
		}
		else
		{
			$value_contact = $res->society.', '.$res->lastname.' '.$res->firstname.' ('.$res->contact_id.')';
		}
	}
	else
	{
		$db->query("select contact_id, society from ".$_SESSION['tablename']['contacts']." where society = '".$func->protect_string_db($contact['SOCIETY'])."' and enabled = 'Y' order by contact_id desc");
		$res = $db->fetch_object();
		$value_contact = $res->society.' ('.$res->contact_id.')';
	}
	echo "{status : 0, value : '".addslashes($value_contact)."'}";
	exit();
}
?>

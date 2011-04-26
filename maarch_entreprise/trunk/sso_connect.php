<?php
require_once('core' . DIRECTORY_SEPARATOR . 'class' 
	. DIRECTORY_SEPARATOR . 'class_core_tools.php');
require_once('core' . DIRECTORY_SEPARATOR . 'class' 
	. DIRECTORY_SEPARATOR . 'class_request.php');
require_once('core' . DIRECTORY_SEPARATOR . 'class' 
	. DIRECTORY_SEPARATOR . 'users_controler.php');
require_once('core' . DIRECTORY_SEPARATOR . 'core_tables.php');
$core = new core_tools();
if (isset($_SESSION['error'])) {
	echo $_SESSION['error'];
	$_SESSION['error'] = '';
	exit;
}
//object2array
/*************** récupération entêtes *********************/
if (isset($_SESSION['HTTP_REQUEST'])) {
	//$core->show_array($_SESSION['HTTP_REQUEST']);
}
if (file_exists($_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
	. $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . 'apps'
    . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'mapping_sso.xml')
){
	$xmlPath = $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
	. $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . 'apps'
    . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'mapping_sso.xml';
} elseif (file_exists($_SESSION['config']['corepath'] . 'apps'
    . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'mapping_sso.xml')
){
	$xmlPath = $_SESSION['config']['corepath'] . DIRECTORY_SEPARATOR . 'apps'
    . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'mapping_sso.xml';
} else {
	echo _XML_FILE_NOT_EXISTS;
	exit;
}
$xmlconfig = simplexml_load_file($xmlPath);
$loginRequestArray = array();
$loginRequestArray = $core->object2array($xmlconfig);
//$core->show_array($loginRequestArray);
$loginArray = array();
$loginArray['userId'] = 
	findRequestArgument($loginRequestArray['USER_ID']);
$loginArray['userName'] = 
	findRequestArgument($loginRequestArray['USER_NAME']['FULL_NAME']);
$loginArray['userEmail'] = 
	findRequestArgument($loginRequestArray['EMAIL']);
$loginArray['userGroups'] = 
	findRequestArgument($loginRequestArray['GROUPS']['GROUP_ID']);
$loginArray['userEntities'] = 
	findRequestArgument($loginRequestArray['ENTITIES']['ENTITY_ID']);
/**** management of errors ****/
$_SESSION['error'] = '';
if (!$loginArray['userId']) {
	$_SESSION['error'] .= _MISSING . ' ' . _USER_ID;
}
if (!$loginArray['userName']) {
	$_SESSION['error'] .= _MISSING . ' ' . _LASTNAME . ' ' . _FIRSTNAME;
}
if (!$loginArray['userEmail']) {
	$_SESSION['error'] .= _MISSING . ' ' . _EMAIL;
}
if (!$loginArray['userGroups']) {
	$_SESSION['error'] .= _MISSING . ' ' . _GROUP_ID;
}
if (!$loginArray['userEntities']) {
	$_SESSION['error'] .= _MISSING . ' ' . _ENTITY;
}
if (isset($_SESSION['error']) && $_SESSION['error'] <> '') {
	//echo $_SESSION['error'];
	header("location: " . $loginRequestArray['WEB_SSO_URL']);
	exit;
}
/*************** test if user already exists *************************/
$db = new dbquery();
$db->connect();
$query = "select user_id from " . USERS_TABLE 
	   . " where user_id = '" . $loginArray['userId'] . "'";
$db->query($query);
//************** collect infos to save function *********************/
$loginArray['password'] = '$' . $loginArray['userId'] . '*';
//fill user object to update it
$userObject = fillUserObject($loginArray, $loginRequestArray);
$groupArray = fillGroupArray($loginArray, $loginRequestArray);
$entityArray = fillEntityArray($loginArray, $loginRequestArray);
//var_dump($userObject);
/*echo '<pre>';
print_r($groupArray);
print_r($entityArray);
echo '</pre>';
exit;*/
$params = array(
	'modules_services' => $_SESSION['modules_services'],
	'log_user_up' => $_SESSION['history']['usersup'],
	'log_user_add' => $_SESSION['history']['usersadd'],
	'databasetype' => $_SESSION['config']['databasetype'],
	'userdefaultpassword' => $loginArray['password'],
);
$uc = new users_controler();
if ($db->nb_result() > 0) {	
    //user exists, so update it
    $control = $uc->save($userObject, $groupArray, 'up', $params);
} else {
	//user doesn't exists, so create it
	$control = $uc->save($userObject, $groupArray, 'add', $params);
}
/*echo '<pre>';
print_r($control);
echo '</pre>';
exit;*/
if(!empty($control['error']) && $control['error'] <> 1) {
	echo $control['error'];exit;
	header("location: " . $loginRequestArray['WEB_SSO_URL']);
	exit;
} else {
	//fill user entities
	require_once('modules/entities/class/EntityControler.php');
	$entityCtrl = new EntityControler();
	$entityCtrl->cleanUsersentities($loginArray['userId'], 'user_id');
    $entityCtrl->loadDbUsersentities(
		$loginArray['userId'],
		$entityArray
	);
	//connection to Maarch
	header(
		"location: " . $_SESSION['config']['businessappurl'] 
		. "log.php?login=" . $loginArray['userId'] . "&pass=" 
		. $loginArray['password']
	);
	exit();
}

function fillEntityArray($loginArray, $loginRequestArray)
{
	$entityArray = array();
	$tmp = array();
	$tmp = explode(
		$loginRequestArray['ENTITIES']['SEP_TOKEN'],
		$loginArray['userEntities']
	);
	for ($cpt = 0;$cpt < count($tmp);$cpt++) {
		if ($cpt == 0) {
			$entityGroup = 'Y';
		} else {
			$entityGroup = 'N';
		}
		//echo $tmp[$cpt] . '<br>';
		array_push(
			$entityArray,
			array(
				'USER_ID' =>  $loginArray['userId'],
				'ENTITY_ID' =>  $tmp[$cpt],
				'PRIMARY' =>  $entityGroup,
				'ROLE' =>  '',
			)
		);
	}
	return $entityArray;
}

function fillGroupArray($loginArray, $loginRequestArray)
{
	$groupArray = array();
	$tmp = array();
	$tmp = explode(
		$loginRequestArray['GROUPS']['SEP_TOKEN'],
		$loginArray['userGroups']
	);
	for ($cpt = 0;$cpt < count($tmp);$cpt++) {
		if ($cpt == 0) {
			$primaryGroup = 'Y';
		} else {
			$primaryGroup = 'N';
		}
		//echo $tmp[$cpt] . '<br>';
		array_push(
			$groupArray,
			array(
				'USER_ID' =>  $loginArray['userId'],
				'GROUP_ID' =>  $tmp[$cpt],
				'PRIMARY' =>  $primaryGroup,
				'ROLE' =>  '',
			)
		);
	}
	return $groupArray;
}

function fillUserObject($loginArray, $loginRequestArray)
{
	$user = new users();
    $user->user_id = $loginArray['userId'];
    $user->password = md5($loginArray['password']);
    $fullName = array();
    $fullName = explode(
		$loginRequestArray['USER_NAME']['SEP_TOKEN'],
		$loginArray['userName']
	);
    $user->firstname = $fullName[0];
    $user->lastname = $fullName[1];
    $user->department  = ''; // à définir
    $user->phone  = ''; // à définir si besoin
    $user->mail = $loginArray['userEmail'];
    $user->loginmode = 'sso';
    $user->change_password = 'N';
    return $user;
}

//$core->show_array($loginArray);
function findRequestArgument($element)
{
	if (isset($_SESSION['HTTP_REQUEST'])) {
		foreach (array_keys($_SESSION['HTTP_REQUEST']) as $requestKey) {
			if ($element == $requestKey) {
				return $_SESSION['HTTP_REQUEST'][$requestKey];
				break;
			}
		}
	}
	return false;
}

//$core->show_array($loginArray);
//exit;

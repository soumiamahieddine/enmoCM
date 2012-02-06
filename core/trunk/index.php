<?php
/**
* File : index.php
*
* Maarch Portal entry
*
* @package maarch
* @version MEP 1.3
* @since 10/2005
* @license GPL v3
* @author Laurent Giovannoni  <dev@maarch.org>
*/

require_once('core/class/class_functions.php');
include_once('core/init.php');
require_once('core/class/class_core_tools.php');
$func = new functions();
$core = new core_tools();
$_SESSION['custom_override_id'] = $core->get_custom_id();
/**** retrieve HTTP_REQUEST FROM SSO ****/
$_SESSION['HTTP_REQUEST'] = $_REQUEST;
if(isset($_GET['origin']) && $_GET['origin'] == 'scan')
{
    header('location: apps/'.$_SESSION['businessapps'][0]['appid'].'/reopen.php');
} elseif(count($_SESSION['businessapps'])== 1) {
    $_SESSION['config']['app_id'] = $_SESSION['businessapps'][0]['appid'];
    header('location: apps/'.$_SESSION['config']['app_id']
        . '/index.php?display=true&page=login&coreurl='
        . $_SESSION['config']['coreurl']);
}

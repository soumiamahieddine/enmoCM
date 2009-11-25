<?php
/**
* File : index.php
*
* Maarch Portal entry
*
* @package  maarch
* @version 2.5
* @since 10/2005
* @license GPL v3
* @author  Laurent Giovannoni  <dev@maarch.org>
*/

include_once('core/init.php');
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_functions.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_portal.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_core_tools.php");
$portal = new portal();
$portal->unset_session();
$portal->build_config();
$func = new functions();
$core = new core_tools();
$_SESSION['custom_override_id'] = $core->get_custom_id();

//$func->show_array($_SESSION['config']);
//$func->show_array($_SESSION['businessapps']);
//$func->show_array($_SESSION['tablename']);
if(isset($_SESSION['config']['defaultlang']) && !empty($_SESSION['config']['defaultlang']))
{
	include("portal/".$_SESSION['config']['defaultlang'].'.php');
}
/*print_r($_SESSION['config']);
exit;*/
if(isset($_GET['origin']) && $_GET['origin'] == "scan")
{
	header("location: apps/".$_SESSION['businessapps'][0]['appid']."/index.php?display=true&page=reopen");
}
elseif(count($_SESSION['businessapps'])== 1)
{
	header("location: portal/launch_maarch.php?app=".$_SESSION['businessapps'][0]['appid']);
}
else
{
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php  echo $_SESSION['config']['defaultlang'] ?>" lang="<?php  echo $_SESSION['config']['defaultlang'] ?>">
    <head>
        <title><?php  echo _PORTAL_NAME;?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <meta http-equiv="Content-Language" content="<?php  echo $_SESSION['config']['defaultlang'] ?>" />
        <link rel="stylesheet" type="text/css" href="portal/css/styles.css" media="screen" />
        <!--[if lt IE 7.0]>  <link rel="stylesheet" type="text/css" href="portal/css/style_ie.css" media="screen" />  <![endif]-->
        <!--[if gte IE 7.0]>  <link rel="stylesheet" type="text/css" href="portal/css/style_ie7.css" media="screen" />  <![endif]-->
    </head>
    <body id="bodylogin">
        <div id="loginpage">
            <p id="logo"><img src="portal/img/logo.gif" alt="Maarch Archives in motion"/></p>
            <form name="formlogin" id="formlogin" method="get" action="portal/launch_maarch.php" class="forms">
                <p>
                    <?php  echo _SELECT_YOUR_APPLICATION;?> :
                </p>
                <p>
                    <select name="app" id="app">
                        <option value="">--</option>
                        <?php
                        for($i=0;$i<=count($_SESSION['businessapps'])-1;$i++)
                        {
                            echo "<option value='".$_SESSION['businessapps'][$i]['appid']."'>".$_SESSION['businessapps'][$i]['comment']."</option>";
                        }
                        ?>
                    </select>
                </p>
                <p>
                    <input type="submit" class="button" name="submit" value="<?php  echo _ENTER;?>" />
                </p>
            </form>
        </div>
    </body>
    </html>
<?php
}
?>

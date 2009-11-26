<?php
/**
* File : login.php
*
* Identification form : Login page
*
* @package  Maarch PeopleBox 1.1
* @version 1.1
* @since 02/2007
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
* @author  Laurent Giovannoni  <dev@maarch.org>
*/

//print_r($_SERVER);

if(trim($_GET["coreurl"]) <> '')
{
	$_SESSION['config']['coreurl'] = $_GET["coreurl"];
}

if(!isset($_SESSION['config']['corename']) || empty($_SESSION['config']['corename']))
{
	if(isset($_SESSION['config']['corepath']) && !empty($_SESSION['config']['corepath'] ))
	{
		$path = 'core'.DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'config.xml';
	} 
	else
	{
		$path = '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'config.xml';
	}
	$xmlconfig = simplexml_load_file($path );
	
	
	if ($_SERVER['HTTPS'] == "on")
		$protocol = "https";
	else
		$protocol = "http";
		
		
	
	foreach($xmlconfig->CONFIG as $CONFIG)
	{
		$_SESSION['config']['corename'] = (string) $CONFIG->corename;
		$_SESSION['config']['corepath'] = (string) $CONFIG->corepath;
		$_SESSION['config']['tmppath'] = (string) $CONFIG->tmppath;
		$_SESSION['config']['unixserver'] = (string) $CONFIG->unixserver;
		$_SESSION['config']['defaultpage'] = (string) $CONFIG->defaultpage;
		$_SESSION['config']['defaultlang'] = (string) $CONFIG->defaultlanguage;
		//$_SESSION['config']['coreurl'] = (string) $CONFIG->coreurl;
		if(!isset($_SESSION['config']['coreurl']))
		{
			if ($_SERVER['SERVER_PORT'] <> 443 && $protocol == "https")		
			{
				$server_port = ":".$_SERVER['SERVER_PORT'];
			}
			elseif ($_SERVER['SERVER_PORT'] <> 80 && $protocol == "http")
			{
				$server_port = ":".$_SERVER['SERVER_PORT'];
			}
			else
			{
					$server_port = "";
			}
			$array_uri = explode("/",$_SERVER['SCRIPT_NAME']);
			$slice_uri = array_slice($array_uri, 0, -3);
			$final_uri = implode("/", $slice_uri)."/";
			if($_SERVER['HTTP_X_FORWARDED_HOST'] <> "")
			{
				$host = $_SERVER['HTTP_X_FORWARDED_HOST'];
			}
			else
			{
				$host = $_SERVER['HTTP_HOST'];
			}
			$_SESSION['config']['coreurl'] = $protocol."://".$host.$server_port.$final_uri;

		}
	}
	$i=0;
	foreach($xmlconfig->BUSINESSAPPS as $BUSINESSAPPS)
	{
		$_SESSION['businessapps'][$i] = array("appid" => (string) $BUSINESSAPPS->appid,
																		"comment" => (string) $BUSINESSAPPS->comment);
		$i++;
	}	
	chdir($_SESSION['config']['corepath']);
}

$_SESSION['config']['app_id'] = $_SESSION['businessapps'][0]['appid'];
//print_r($_REQUEST);
if(isset($_GET['target_page']) && trim($_GET['target_page']) <> "")
{
	$_SESSION['target_page'] = $_GET['target_page'];
	if(trim($_GET['target_module']) <> "")
	{
		$_SESSION['target_module'] = $_GET['target_module'];
	}
	elseif(trim($_GET['target_admin']) <> "")
	{
		$_SESSION['target_admin'] = $_GET['target_admin'];
	}
}
/*
$_SESSION['requestUri'] = "";
if(trim($_SERVER['argv'][0]) <> "")
{
	$requestUri = $_SERVER['argv'][0];
	$requestUri = str_replace("coreurl=".$_REQUEST["coreurl"], "", $requestUri);
	$_SESSION['requestUri'] = $requestUri;
}
*/

$path_server = '';
//$path_server = $_SERVER['DOCUMENT_ROOT'];
if(strtoupper(substr(PHP_OS, 0, 3)) != "WIN" && strtoupper(substr(PHP_OS, 0, 3)) != "WINNT")
{
	//$_SESSION['slash_env'] = "/";
	$path_server = str_replace("\\",DIRECTORY_SEPARATOR, $path_server);
}
else
{
	//$_SESSION['slash_env'] = "\\";
	$path_server = str_replace("/",DIRECTORY_SEPARATOR, $path_server);
}
$_SESSION['slash_env'] = DIRECTORY_SEPARATOR;
$path_tmp = explode(DIRECTORY_SEPARATOR, str_replace('/', DIRECTORY_SEPARATOR,$_SERVER['SCRIPT_FILENAME']));
$path_server = implode(DIRECTORY_SEPARATOR,array_slice($path_tmp,0,array_search('apps',$path_tmp))).DIRECTORY_SEPARATOR;

$_SESSION['urltomodules'] = $_SESSION['config']['coreurl']."modules/";
$_SESSION['urltocore'] = $_SESSION['config']['coreurl'].'core/';
if(isset($_SESSION['error']))
{
	$error = $_SESSION['error'];
}
else
{
	$error ='';
}
if(isset($_SESSION['config']['corepath']) && !empty($_SESSION['config']['corepath'] ))
{
	require('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_business_app_tools.php");
	$path_core_config = 'core'.DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'config.xml';
}
else
{
	require("class".DIRECTORY_SEPARATOR."class_business_app_tools.php");
	$path_core_config = "..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'config.xml';
}

$core_tools = new core_tools();
$business_app_tools = new business_app_tools();
$func = new functions();

$core_tools->build_core_config($path_core_config );
$business_app_tools->build_business_app_config();
$core_tools->load_modules_config($_SESSION['modules']);
$core_tools->load_lang();
//$func->show_array($_SESSION);
$core_tools->load_app_services();
$core_tools->load_modules_services($_SESSION['modules']);
//$core_tools->load_menu($_SESSION['modules']); // transfer in class_security (login + reopen)

$core_tools->load_html();
$core_tools->load_header();
$time = $core_tools->get_session_time_expire();

?>
<body id="bodylogin" onload="setTimeout('window.location.reload(true)', <?php  echo $time;?>*60*1000);">
    <div id="loginpage">
        <p id="logo"><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=default_maarch.gif" alt="Maarch" /></p>
        <form name="formlogin" id="formlogin" method="post" action="<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=log" class="forms">
			<input type="hidden" name="display" value="true" />
			<input type="hidden" name="page" value="log" />
            <p>
                <label for="login"><?php  echo _ID; ?> :</label>
                <input name="login" id="login" value="" type="text"  />
            </p>

            <p>
                <label for="pass"><?php  echo _PASSWORD; ?> :</label>
                <input name="pass" id="pass" value="" type="password"  />
            </p>
            <p class="buttons">
                <input type="submit" class="button" name="submit" value="<?php  echo _SEND; ?>" />
            </p>
            <div class="error"><?php  echo $error;
            $_SESSION['error'] = '';
            ?>
            </div>
        </form>
    </div>
</body>
</html>

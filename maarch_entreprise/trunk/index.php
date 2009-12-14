<?php
/**
* File : index.php
*
* Maarch index
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author  Laurent Giovannoni <dev@maarch.org>
* @author  Claire Figueras  <dev@maarch.org>
* @author  Loïc Vinet <dev@maarch.org>
*/

include_once('../../core/init.php');
echo $_SESSION['custom_override_id'];
if(isset($_SESSION['config']['corepath']))
{
	require_once("core/class/class_functions.php");
	require_once("core/class/class_db.php");
	require_once("core/class/class_core_tools.php");
	$core_tools = new core_tools();
}
else
{
	require_once("../../core/class/class_functions.php");
	require_once("../../core/class/class_db.php");
	require_once("../../core/class/class_core_tools.php");	
	$core_tools = new core_tools();
	$_SESSION['custom_override_id'] = $core_tools->get_custom_id();
	chdir("../..");
}
if(isset($_SESSION['user']['UserId']) && !empty($_SESSION['user']['UserId']) && $_GET['page'] <> 'login' && $_GET['page'] <> 'log' && $_GET['page'] <> 'logout')
{
	$db = new dbquery();
	$key = md5(time()."%".$_SESSION['user']['FirstName']."%".$_SESSION['user']['UserId']."%".$_SESSION['user']['UserId']."%".date("dmYHmi")."%");

	if ($_SESSION['config']['databasetype'] == "ORACLE")
	{
		$db->query("update ".$_SESSION['tablename']['users']." set cookie_key = '".$key."', cookie_date = SYSDATE where user_id = '".$_SESSION['user']['UserId']."' and mail = '".$_SESSION['user']['Mail']."'", 1);
	}
	else
	{
		$db->query("update ".$_SESSION['tablename']['users']." set cookie_key = '".$key."', cookie_date = '".date("Y-m-d")." ".date("H:m:i")."' where user_id = '".$_SESSION['user']['UserId']."' and mail = '".$_SESSION['user']['Mail']."'", 1);
	}
	setcookie("maarch", "UserId=".$_SESSION['user']['UserId']."&key=".$key,time()+($_SESSION['config']['cookietime']*1000));
}
if(isset($_REQUEST['display']) )
{
	 $core_tools->insert_page();
	 exit();
} 

if(!isset($_SESSION['user']['UserId']))
{
	if(trim($_SERVER['argv'][0]) <> "")
	{
		header("location: reopen.php?".$_SERVER['argv'][0]);
	}
	else
	{
		header("location: reopen.php");
	}
	exit();
}



if(isset($_GET['show']))
{
	$show = $_GET['show'];
}
else
{
	$show = "true";
}

//$core_tools->test_user();
$core_tools->start_page_stat();
$core_tools->configPosition();
//here we loading the lang vars
$core_tools->load_lang();
//here we loading the html
$core_tools->load_html();
//here we building the header
$core_tools->load_header();
$time = $core_tools->get_session_time_expire();

?>
<body onLoad="HideMenu('menunav');session_expirate(<?php  echo $time;?>, '<?php  echo $_SESSION['config']['coreurl'];?>');">
	<div id="header">
        <div id="nav">
            <div id="menu" onMouseOver="ShowHideMenu('menunav','on');" onMouseOut="ShowHideMenu('menunav','off');" class="off">
                <p>
                	<img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=but_menu.gif" alt="<?php  echo _MENU;?>" />
                </p>
                <div id="menunav" style="display: none;">
                <?php
                echo '<div class="header_menu"><div class="user_name_menu">'.$_SESSION['user']['FirstName'].' '.$_SESSION['user']['LastName'].'</div></div>';
				echo '<div class="header_menu_blank">&nbsp;</div>';?>
                <ul  >
                    <?php
                    //Username for menu

                    //here we building the maarch menu
                    $core_tools->build_menu($_SESSION['menu']);

                   ?>
                </ul>
               		 <?php
                	echo '<div class="header_menu_blank">&nbsp;</div>';
                   	echo '<div class="footer_menu"><a style="color:white;" href="'.$_SESSION['config']['businessappurl'].'index.php?page=maarch_credits"">';
                   	echo ''._MAARCH_CREDITS.'</a></div>';?>
                </div>
        	</div>
			<div><p id="ariane"><?php 
			?></p></div>
			<p id="gauchemenu"><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=bando_tete_gche.gif" alt=""></p>
			<p id="logo"><a href="index.php"><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=bando_tete_dte.gif" alt="<?php  echo _LOGO_ALT;?>" /></a></p>
       </div>
	
	<div id="container">
        <div id="content">
            <div class="error" id="main_error">
				<?php  echo $_SESSION['error'];?>
            </div>
			<div class="info" id="main_info">
				<?php  echo $_SESSION['info'];?>
            </div>
            <?php
            if($core_tools->is_module_loaded("basket") && $_SESSION['abs_user_status'] ==true)
			{
				include('modules'.DIRECTORY_SEPARATOR.'basket'.DIRECTORY_SEPARATOR."advert_missing.php");
			}
			else
			{
          	  $core_tools->insert_page();
          	}
            ?>
        </div>
        <p id="footer">
			<?php
            $core_tools->load_footer();
            ?>
        </p>
        <?php
        $_SESSION['error'] = "";
		$_SESSION['info'] = "";
        $core_tools->view_debug();
        ?>
	</div>
</div>
</body>
</html>

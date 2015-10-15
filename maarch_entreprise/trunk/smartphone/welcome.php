<?php
require_once('core/class/class_functions.php');
require_once('core/class/class_core_tools.php');
$core = new core_tools();
$core->load_lang();
?>
<ul id="home" title="Maarch" selected="true">
    <li><a href="my_baskets.php"><i class="fa fa-inbox mCdarkGrey"></i>&nbsp;&nbsp;<?php echo _MY_BASKETS;?></a></li>
    <li><a href="search.php"><i class="fa fa-search mCdarkGrey"></i>&nbsp;&nbsp;<?php echo _SEARCH;?></a></li>
	<!--<li><a href="settings.php"><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_menu_admin.gif" alt=""/>&nbsp;&nbsp;Settings</a></li>-->
	<li><a href="my_colleagues.php"><i class="fa fa-users mCdarkGrey"></i>&nbsp;&nbsp;<?php echo _MY_COLLEAGUES;?></a></li>
	<li><a href="my_contacts.php"><i class="fa fa-book mCdarkGrey"></i>&nbsp;&nbsp;<?php echo _MY_CONTACTS_MENU;?></a></li>
    <li><a href="my_profile.php"><i class="fa fa-user mCdarkGrey"></i>&nbsp;&nbsp;<?php echo _MY_INFO;?></a></li>
    <li><a href="about.php"><i class="fa fa-question-circle mCdarkGrey"></i>&nbsp;&nbsp;<?php echo _MAARCH_CREDITS;?></a></li>
	<li><a href="info.php"><i class="fa fa-phone-square mCdarkGrey"></i>&nbsp;&nbsp;<?php echo _MAARCH_INFO;?></a></li>
    <li><a href="logout.php"><i class="fa fa-power-off mCdarkGrey"></i>&nbsp;&nbsp;<?php echo _LOGOUT;?></a></li>
</ul>

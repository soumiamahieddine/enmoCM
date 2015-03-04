<?php  
/**
* File : no_right.php
*
* Default error of right page
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/;

$core_tools = new core_tools();

unset($_SESSION['location_bar']['level2']);
unset($_SESSION['location_bar']['level3']);
unset($_SESSION['location_bar']['level4']);
$core_tools->manage_location_bar();
?>
<h1><?php  echo _NO_RIGHT;?></h1>
<div id="inner_content" class="clearfix">
<br/><br/>
<div class="error"><?php  echo _NO_RIGHT_TXT;?></div>
</div>

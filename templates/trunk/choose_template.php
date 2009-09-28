<?php
/**
* File : choose_template.php
*
* Pop up to choose a document template for an answer in the process
*
* @package Maarch LetterBox 2.3
* @version 1.0
* @since 10/2007
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
 session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");

$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();

if(isset($_REQUEST['template']) && !empty($_REQUEST['template']))
{
	/*?>
    <!--	<script type="text/javascript" language="javascript">
		function replace() {
			window.moveTo(0,0)
			window.resizeTo(screen.availWidth,screen.availHeight)
			}
		replace()
        </script>-->
    <?php */
	header('location: '.$_SESSION['urltomodules'].'templates/generate_attachment.php?mode=add&template='.$_REQUEST['template'].'&res_id='.$_REQUEST['res_id'].'&coll_id='.$_REQUEST['coll_id']);

	exit();
}

$db = new dbquery();
$db->connect();

$db->query("select m.id, m.label from ".$_SESSION['tablename']['temp_templates']." m, ".$_SESSION['tablename']['temp_templates_association']." ma where m.id = ma.template_id and ma.what = 'destination' and ma.value_field = '".$_REQUEST['entity']."'");


$templates = array();

while($res = $db->fetch_object())
{
	array_push($templates, array( 'ID' => $res->id, "LABEL" => $res->label));
}

$core_tools->load_html();
$time = $core_tools->get_session_time_expire();
//here we building the header
$core_tools->load_header(_CHOOSE_TEMPLATE);

?>
<body id="pop_up"  onLoad="setTimeout(window.close, <?php  echo $time;?>*60*1000);">
<h2 class="tit"><?php  echo _CHOOSE_TEMPLATE;?> </h2>

<div align="center"><b><?php  echo $erreur; ?></b></div>
<form enctype="multipart/form-data" method="post" name="attachement" action="<?php  echo $_SESSION['urltomodules']."templates/";?>choose_template.php"   >
	<input type="hidden" name="res_id" id="res_id" value="<?php  echo $_REQUEST['res_id'];?>" />
	<input type="hidden" name="coll_id" id="coll_id" value="<?php  echo $_REQUEST['coll_id'];?>" />
	<p><label><?php  echo _PLEASE_SELECT_TEMPLATE;?> :</label></p>
    <br/>
    <p>
    	<select name="template" id="template" style="width:150px" onchange="this.form.submit();">
        	<option value=""></option>
            <?php
				for($i=0; $i<count($templates); $i++)
				{
					?>
                    	<option value="<?php  echo $templates[$i]['ID'];?>"><?php  echo $templates[$i]['LABEL'];?></option>
                    <?php
				}
			?>
        </select>
    </p>

<br/>
	<p class="buttons">
   <!--    <input type="submit" value="<?php  echo _VALIDATE;?>" name="choix" id="choix" class="button" />-->
	<input type="button" value="<?php  echo _CANCEL;?>" name="cancel" class="button"  onclick="self.close();"/>

 </form>
</body>
</html>

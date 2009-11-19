<?php

/// DEPRECATED ?
  include('core/init.php');


require_once("core/class/class_functions.php");
require_once("core/class/class_db.php");
require_once("core/class/class_request.php");
require_once("core/class/class_core_tools.php");
$core_tools = new core_tools();
$core_tools->load_lang();
 $core_tools->load_html();
//here we building the header
$core_tools->load_header();

$db = new dbquery();
$db->connect();
$db->query("select foldertype_id, foldertype_label from ".$_SESSION['tablename']['fold_foldertypes']." order by foldertype_label");

$foldertypes = array();
while($res = $db->fetch_object())
{
	array_push($foldertypes, array('id' => $res->foldertype_id, 'label' => $db->show_string($res->foldertype_label)));
}

?>
<body>
<?php
if(isset($_REQUEST['foldertype']) && !empty($_REQUEST['foldertype']))
{
	$_SESSION['foldertype'] = $_REQUEST['foldertype'];
	?>
	<script language="javascript" type="text/javascript">window.parent.frames['frm_create_folder'].location.href='<?php  echo $_SESSION['urltomodules']."folder/";?>frm_create_folder.php';</script>
    <?php
}?>
<form name="choose_foldertype_form" id="choose_foldertype_form" action="<?php  echo $_SESSION['urltomodules']."folder/";?>choose_foldertype.php">
<p>
	<label><?php  echo _FOLDERTYPE;?></label>
    <select name="foldertype" id="foldertype" onchange="this.form.submit();">
    	<option value=""><?php  echo _CHOOSE_FOLDERTYPE;?></option>
        <?php  for($i=0; $i< count($foldertypes);$i++)
		{
		?><option value="<?php  echo $foldertypes[$i]['id'];?>" <?php  if($_SESSION['foldertype'] == $foldertypes[$i]['id']){ echo 'selected="selected"'; }?>><?php  echo $foldertypes[$i]['label'];?></option>
		<?php
		}?>
    </select>
</p>
</form>
</body>
</html>

<?php
include('core/init.php');

require_once("core/class/class_functions.php");
require_once("core/class/class_db.php");
require_once("core/class/class_request.php");
require_once("core/class/class_core_tools.php");
$core_tools = new core_tools();
$core_tools->load_lang();
$func = new functions();
$core_tools->load_html();
$core_tools->load_header();

if(isset($_REQUEST['tree_id']) && !empty($_REQUEST['tree_id']))
{
	$_SESSION['entities_chosen_tree'] = $_REQUEST['tree_id'];
	?>
    <script language="javascript" type="text/javascript">window.top.frames['show_trees'].location.href='<?php  echo $_SESSION['urltomodules'].'entities/show_trees.php';?>';</script>
    <?php
}
else
{
	$_SESSION['entities_chosen_tree'] = "";
}
?>
<body>
	<form name="frm_choose_tree" id="frm_choose_tree" method="get" action="<?php  echo "choose_tree.php";?>">
    	<p align="left">
        	<label><?php  echo _ENTITY;?> :</label>
            <select name="tree_id" id="tree_id" onChange="this.form.submit();">
            	<option value=""><?php  echo _CHOOSE_ENTITY;?></option>
                <?php
				for($i=0;$i<count($_SESSION['tree_entities']);$i++)
				{
					?>
					<option value="<?php  echo $_SESSION['tree_entities'][$i]['ID'];?>" <?php  if($_SESSION['entities_chosen_tree'] == $_SESSION['tree_entities'][$i]['ID'] ){ echo 'selected="selected"';}?>><?php  echo $_SESSION['tree_entities'][$i]['LABEL'];?></option>
					<?php
				}
				?>
            </select>
        </p>
    </form>
</body>
</html>

<?php
/**
* File : search_customer.php
*
* Advanced search form
*
* @package  Maarch Framework 3.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author Loïc Vinet  <dev@maarch.org>
* @author Claire Figueras  <dev@maarch.org>
*/

require_once
    "apps" . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR 
    . "class_business_app_tools.php";
    
$appTools   = new business_app_tools();
$core       = new core_tools();

$core->test_user();
$core->load_lang();
$core->test_service('view_folder_tree', 'folder');
$_SESSION['indexation'] = false;

//Definition de la collection
$_SESSION['collection_id_choice'] = $_SESSION['user']['collections'][0];
if ($_SESSION['user']['collections'][1] == 'letterbox_coll') {
    $_SESSION['collection_id_choice'] = 'letterbox_coll';
}
/****************Management of the location bar  ************/
$init = false;
if (isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == 'true') {
    $init = true;
}
$level = '';
if (isset($_REQUEST['level']) && ($_REQUEST['level'] == 2
    || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4
    || $_REQUEST['level'] == 1)
) {
    $level = $_REQUEST['level'];
}
$pagePath = $_SESSION['config']['businessappurl']
           . 'index.php?page=search_folder_tree&module=folder';
$pageLabel = _SEARCH_FOLDER_TREE;
$pageId = 'search_folder_tree';
$core->manage_location_bar(
    $pagePath, $pageLabel, $pageId, $init, $level
);
/***********************************************************/

//$core->show_array($_REQUEST);
?>
<script type="text/javascript" >
    BASE_URL = "<?php echo $_SESSION['config']['businessappurl'] ?>";
</script>
<h1><img src="<?php
echo $_SESSION['config']['businessappurl'] . "static.php?filename=search_proj_off.gif";
?>" alt="" /> <?php  echo _SEARCH_FOLDER_TREE; ?></h1>
<div id="inner_content" align="center">
    <div class="block">
		<form method="post" name="form_search_folder" id="form_search_folder" action="#">
        <table width="100%" border="0">
            <tr>
                <td align="right"><label for="folder"><?php
            echo _FOLDER;
            ?> :</label></td>
                            <td class="indexing_field">
                                <input type="text" name="folder" id="folder" size="45" onKeyPress="if(event.keyCode == 13) submitForm();" />
                                <div id="show_folder" class="autocomplete"></div>
                            </td>
                            <!-- <td align="right"><label for="subfolder"><?php  echo _SUBFOLDER;?> :</label></td>
                            <td>
                                <input type="text" name="subfolder" id="subfolder" size="45" onKeyPress="if(event.keyCode == 13) submitForm();" />
                                <div id="show_subfolder" class="autocomplete"></div>
                            </td>-->
                            <td>
                                <input type="button" value="<?php
            echo _SEARCH;
            ?>" onclick="javascript:submitForm();" class="button">
                </td>
				<td width="50%">&nbsp;</td>
            </tr>
        </table>
		</form>
    </div>
    <div class="clearsearch">
        <br>
        <a href="javascript://" onClick="window.top.location.href='<?php
                echo $_SESSION['config']['businessappurl'];
                ?>index.php?page=search_folder_tree&module=folder&erase=true';"><img src="<?php
                echo $_SESSION['config']['businessappurl']."static.php?filename=reset.gif";
                ?>" alt="" height="15px" width="15px" /><?php  echo _NEW_SEARCH; ?></a>
    </div>
    <!-- Display the layout of search_folder_tree -->
    <table width="100%" height="100%" cellspacing="5" style="border:1px solid #999999;">
        <tr>
            <td width="55%" height="720px" style="vertical-align: top; text-align: left;border-right:1px solid #999999;">
                <div id="myTree">&nbsp;</div>
            </td>
            <td width="45%">
                <div id="docView"><p align="center"><img src="<?php echo $_SESSION['config']['businessappurl']
                    .'static.php?filename=bg_home_home.gif'; 
                    ?>"  width="400px" alt="Maarch" /></p></div>
            </td>
        </tr>
    </table>
</div>
<script type="text/javascript">
    initList('folder', 'show_folder', '<?php
        echo $_SESSION['config']['businessappurl'];
        ?>index.php?display=true&module=folder&page=autocomplete_folders&mode=folder', 
        'Input', '2');

    function submitForm()
    {
        var folder = $('folder').value;
        tree_init('myTree', folder);
    }
</script>
<script type="text/javascript" src="<?php
echo $_SESSION['config']['businessappurl'] . 'tools/'
?>MaarchJS/dist/maarch.js"></script>
<script type="text/javascript" src="<?php
echo $_SESSION['config']['businessappurl'] . 'js/'
?>search_customer.js"></script>
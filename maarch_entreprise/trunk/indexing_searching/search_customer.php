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
    'apps/' . $_SESSION['config']['app_id']
    . '/class/class_business_app_tools.php';
$appTools = new business_app_tools();
$core = new core_tools();
$core->test_user();
$core->load_lang();
$core->test_service('search_customer', 'apps');
$_SESSION['indexation'] = false;
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
           . 'index.php?page=search_customer&dir=indexing_searching';
$pageLabel = _SEARCH_CUSTOMER;
$pageId = 'is_search_customer';
$core->manage_location_bar(
    $pagePath, $pageLabel, $pageId, $init, $level
);
/***********************************************************/
//Definition de la collection
$_SESSION['collection_id_choice'] = $_SESSION['user']['collections'][0];
if (isset($_REQUEST['erase']) && $_REQUEST['erase'] == 'true') {
    $_SESSION['search'] = array();
}
$_SESSION['origin'] = 'search_customer';
if (isset($_REQUEST['name_folder']) && $_REQUEST['name_folder'] <> '') {
    $_SESSION['search']['chosen_name_folder'] = $_REQUEST['name_folder'];
}
//$core->show_array($_REQUEST);
?>
<script type="text/javascript" >
    BASE_URL = "<?php echo $_SESSION['config']['businessappurl'] ?>";
</script>
<h1><i class="fa fa-search fa-2x"></i> <?php  echo _SEARCH_CUSTOMER_TITLE; ?></h1>
<div id="inner_content" align="center">
    <div class="block">
        <table width="100%" border="0">
            <tr>
                <td align="right"><label for="project"><?php
echo _PROJECT;
?> :</label></td>
                <td class="indexing_field">
                    <input type="text" name="project" id="project" size="45" />
                    <div id="show_project" class="autocomplete"></div>
                </td>
                 <td align="right"><?php  echo _MARKET;?> :</td>
                <td>
                    <input type="text" name="market" id="market" size="45" />
                    <div id="show_market" class="autocomplete"></div>
                </td>
                <td>
                    <input type="button" value="<?php
echo _SEARCH;
?>" onclick="javascript:submitForm();" class="button">
                </td>
            </tr>
        </table>
    </div>
    <div class="clearsearch">
        <br>
        <a href="<?php
echo $_SESSION['config']['businessappurl'];
?>index.php?page=search_customer&dir=indexing_searching&erase=true">
            <i class="fa fa-refresh fa-4x" title="<?php echo _CLEAR_FORM; ?>"></i>
        </a>
    </div>
    <!-- Display the layout of search_customer -->
    <table width="100%" height="100%" border="1">
        <tr>
            <td width="55%" height="720px" style="vertical-align: top; text-align: left;">
                <div id="myTree">&nbsp;</div>
            </td>
            <td width="45%">
                <div id="docView"><p align="center"><img src="<?php echo $_SESSION['config']['businessappurl'].'static.php?filename=bg_home_home.gif'; ?>"  width="400px" alt="Maarch" /></p></div>
            </td>
        </tr>
    </table>
</div>
<script type="text/javascript">
    launch_autocompleter_folders('<?php
echo $_SESSION['config']['businessappurl'];
?>index.php?display=true&module=folder&page=autocomplete_folders&mode=project', 'project');
    launch_autocompleter_folders('<?php
echo $_SESSION['config']['businessappurl'];
?>index.php?display=true&module=folder&page=autocomplete_folders&mode=market', 'market');
    function submitForm()
    {
        var project = $('project').value;
        if (project) {
            tree_init('myTree', project);
        } else {
            var market = $('market').value;
            if (market) {
        	   tree_init('myTree', market);
            }
        }
    }
</script>
<script type="text/javascript" src="<?php
echo $_SESSION['config']['businessappurl'] . 'tools/'
?>MaarchJS/dist/maarch.js"></script>
<script type="text/javascript" src="<?php
echo $_SESSION['config']['businessappurl'] . 'js/'
?>search_customer.js"></script>
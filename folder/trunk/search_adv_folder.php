<?php
/**
* File : search_adv.php
*
* Advanced search form
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/

require_once "core/class/class_request.php";
$core = new core_tools();
$core->test_user();
//$core->load_lang();
$core->test_service('folder_search', 'folder');

/****************Management of the location bar  ************/
$init = false;
if (isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == "true") {
    $init = true;
}
$level = "";
if (isset($_REQUEST['level']) && ($_REQUEST['level'] == 2
    || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4
    || $_REQUEST['level'] == 1)
) {
    $level = $_REQUEST['level'];
}
$pagePath = $_SESSION['config']['businessappurl']
    . 'index.php?page=search_adv_folder&module=folder';
$pageLabel = _SEARCH_ADV_FOLDER;
$pageId = "search_folder_adv";
$core->manage_location_bar($pagePath, $pageLabel, $pageId, $init, $level);
/***********************************************************/

$foldertypes = array();
$chooseColl = true;
$db = new dbquery;
$db->connect();
if (count($_SESSION['user']['collections']) == 1 ) {
    $chooseColl = false;

    $db->query(
    	"select foldertype_id, foldertype_label from "
        . $_SESSION['tablename']['fold_foldertypes'] . " where coll_id = '"
        . $_SESSION['user']['collections'][0] . "' order by foldertype_label"
    );
    while ($res = $db->fetch_object()) {
        array_push(
            $foldertypes,
            array(
            	'id' => $res->foldertype_id,
            	'label' => $res->foldertype_label
            )
        );
    }
}

if (isset($_REQUEST['erase']) && $_REQUEST['erase'] == 'true') {
    $_SESSION['folder_search'] = array();
}
?>
<h1><img src="<?php
echo $_SESSION['config']['businessappurl'] . "static.php?module=folder"
    . "&filename=picto_search_b.gif";
?>" alt="" /> <?php  echo _ADV_SEARCH_FOLDER_TITLE; ?></h1>
<br/>
<form name="search_folder_frm" method="get" action="<?php
echo $_SESSION['config']['businessappurl'];
?>index.php" id="search_folder_frm" class="forms2">
    <!--<input type="hidden" name="display"  value="true" />-->
    <input type="hidden" name="module"  value="folder" />
    <input type="hidden" name="page"  value="search_adv_folder_result" />
<?php
if ($chooseColl) {
    ?>
    <div align="center">
        <p>
            <label for="coll_id"><?php echo _COLLECTION;?> :</label>
            <select name="coll_id" id="coll_id" onchange="search_change_coll('<?php
    echo $_SESSION['config']['businessappurl']. 'index.php?display=true'
        . '&module=folder&page=get_foldertypes';
    ?>', this.options[this.options.selectedIndex].value)">
                <option value=""><?php echo _CHOOSE_COLLECTION;?></option>
                <?php
    foreach (array_keys($_SESSION['user']['security']) as $coll) {
        ?><option value="<?php echo $coll;?>"><?php
        echo $_SESSION['user']['security'][$coll]['DOC']['label_coll'];
        ?></option><?php
    }
    ?>
            </select>
        </p>
    </div>
    <?php
} else {
    ?><input type="hidden" name="coll_id" id="coll_id" value="<?php
    if (isset($_SESSION['user']['security'][0]['coll_id'])) {
        echo $_SESSION['user']['security'][0]['coll_id'];
    }
    ?>" /><?php
}
?>
<div id="folder_search_div" style="display:<?php
if ($chooseColl) {
    echo "none";
} else {
    echo "block";
}
?>">
    <div class="clearsearch">
        <a href="<?php
echo $_SESSION['config']['businessappurl'] . 'index.php?page=search_adv_folder'
    . '&module=folder&reinit=true&erase=true';
?>"><img src="<?php
echo $_SESSION['config']['businessappurl'] . "static.php?filename=reset.gif";
?>" alt="" /> <?php  echo _NEW_SEARCH; ?></a>
    </div>
    <br/>
    <br/>
    <br/>
    <div class="block">
        <br/>
        <h2><?php  echo _INFOS_FOLDERS;?></h2>
        <input type="hidden" name="page" value="search_adv_folder_result" />
        <input type="hidden" name="module" value="folder" />
        <table width="90%" border="0">
            <tr>
                <td width="25%" align="right"><label for="foldertype_id"><?php
echo _FOLDERTYPE;
?> :</label></td>
                <td width="24%">
                    <select name="foldertype_id" id="foldertype_id" onchange="get_folder_index('<?php
echo $_SESSION['config']['businessappurl'] . 'index.php?display=true'
    . '&module=folder&page=get_folder_search_index';
?>', this.options[this.options.selectedIndex].value, 'opt_indexes')">
                        <option value=""><?php echo _CHOOSE_FOLDERTYPE;?></option>
                        <?php
for ($i = 0; $i < count($foldertypes); $i ++) {
    ?><option value="<?php echo $foldertypes[$i]['id'];?>" <?php
    if (isset($_SESSION['folder_search']['foldertype_id'])
        && $foldertypes[$i]['id'] == $_SESSION['folder_search']['foldertype_id']
    ) {
        echo 'selected="selected"';
    }
    ?>><?php echo $foldertypes[$i]['label'];?></option><?php
}
?>
                    </select>
                </td>
                <td width="2%">&nbsp;</td>
                <td width="25%" align="right"><label for="folder_id"><?php
echo _FOLDERID;
?> :</label></td>
                <td width="24%">
                    <input type="text" name="folder_id" id="folder_id" value="<?php
if (isset($_SESSION['folder_search']['folder_id'])) {
    echo $_SESSION['folder_search']['folder_id'];
}
?>" />
                    <div id="foldersListById" class="autocomplete"></div>
                    <script type="text/javascript">
                        initList('folder_id', 'foldersListById', '<?php
echo $_SESSION['config']['businessappurl'];
?>index.php?display=true&module=folder&page=folders_list_by_id', 'Input', '2');
                    </script>
                </td>
            </tr>
            <tr>
                <td width="25%" align="right"><label for="creation_date_start"><?php
echo _CREATION_DATE . ' ' . _START;
?> :<label></td>
                <td width="24%">
                    <input name="creation_date_start" type="text" id="creation_date_start" value="<?php
if (isset($_SESSION['folder_search']['creation_date_start'])) {
    echo $_SESSION['folder_search']['creation_date_start'] ;
}
?>" onclick='showCalender(this)'/>
                </td>
                <td width="2%">&nbsp;</td>
                <td width="25%" align="right"><label for="creation_date_end"><?php
echo _CREATION_DATE . ' ' . _END;
?>:<label></td>
                <td width="24%">
                    <input name="creation_date_end" type="text" id="creation_date_end" value="<?php
if (isset($_SESSION['folder_search']['creation_date_end'])) {
    echo $_SESSION['folder_search']['creation_date_end'] ;
}
?>" onclick='showCalender(this)'/>
                </td>
            </tr>
            <tr>
                <td width="25%" align="right"><label for="folder_name"><?php
echo _FOLDERNAME;
?> :<label></td>
                <td colspan="3">
                    <input name="folder_name" type="text" id="folder_name" value="<?php
if (isset($_SESSION['folder_search']['folder_name'])) {
    echo $_SESSION['folder_search']['folder_name'];
}
?>" />
                </td>
            </tr>
        </table>
        <div id="opt_indexes"></div>
        <br/>
        <p class="buttons">
            <input class="button" name="imageField" type="submit" value="<?php  echo _SEARCH; ?>" onclick="javascript:return(verif_search(this.form));"  />
        </p>

    </div>
    <div class="block_end"></div>
</div>
</form>
<script type="text/javascript">
var foldertypes = $('foldertype_id');
if(foldertypes)
{
    get_folder_index('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=folder&page=get_folder_search_index', foldertypes.options[foldertypes.options.selectedIndex].value, 'opt_indexes');
}
</script>

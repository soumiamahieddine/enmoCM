<?php
/**
* File : show_folder.php
*
* Show the details of a folder
*
* @package  Maarch v3
* @version 1.0
* @since 10/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/

require_once "core/class/class_request.php";
require_once "core/class/class_security.php";
$core = new core_tools();
if (!$core->is_module_loaded("folder")) {
    echo "Folder module missing !<br/>Please install this module.";
    exit();
}
// $core->test_service('salary_sheet', 'folder');
/****************Management of the location bar  ************/
$sec = new security();
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
	. 'index.php?page=show_folder&module=folder&folder_id=' . $_REQUEST['id'];
$pageLabel = _SHOW_FOLDER;
$pageId = "fold_show_folder";
$core->manage_location_bar($pagePath, $pageLabel, $pageId, $init, $level);
/***********************************************************/
require_once "modules/folder/class/class_modules_tools.php";
require_once "apps/" . $_SESSION['config']['app_id'] 
	. "/class/class_list_show.php";
require_once "core/class/class_history.php";
$folderObject = new folder();
$request = new request;
$func = new functions();
$users = new history();
$users->connect();
$status = '';
$_SESSION['current_foldertype'] = '';
$_SESSION['origin'] = "show_folder";
$datePattern = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";

$view = '';
$updateRight = $core->test_service('modify_folder', 'folder', false);
$deleteRight = $core->test_service('delete_folder', 'folder', false);

//update folder index
if (isset($_POST['update_folder'])) {
    $folderObject->update_folder($_REQUEST, $_REQUEST['id']);
}
//delete the folder
if (isset($_POST['delete_folder'])) {
    $folderObject->delete_folder($_REQUEST['id'], $_REQUEST['foldertype_id']);
    ?>
        <script type="text/javascript">window.top.location.href='<?php  
    echo $_SESSION['config']['businessappurl']
    	. 'index.php?page=search_adv_folder&module=folder';
    ?>';</script>
    <?php
    exit();
}

?>
<div id="details_div" style="display:none;">
	<h1><img src="<?php  
echo $_SESSION['config']['businessappurl'];
?>static.php?filename=manage_structures.gif" alt="<?php 
echo _FOLDER
;?>" width="35px" height="30px"/> <?php  
echo _SHOW_FOLDER;
?></h1>
	<div id="inner_content">
		<div class="viewfolder">
    <?php
if ($_REQUEST['id'] <> "") {
	?>
    	
        <?php
    if ($_REQUEST['id'] <> "") {
    	$folderObject->load_folder(
    		$_REQUEST['id'], $_SESSION['tablename']['fold_folders']
    	);
        $status = $folderObject->get_field('status');
        $_SESSION['current_foldertype_coll_id'] = $folderObject->get_field('coll_id');
        $view = $sec->retrieve_view_from_coll_id($_SESSION['current_foldertype_coll_id']);
        if ($status == 'DEL') {
        	echo _NO_FOLDER_FOUND.".";
        } else {
        	$folderArray = array();
            $folderArray = $folderObject->get_folder_info();

            $_SESSION['current_folder_id'] = $folderArray['system_id'];
            $id = $_SESSION['current_folder_id'];
            $folderObject->modify_default_folder_in_db(
            	$_SESSION['current_folder_id'], $_SESSION['user']['UserId'], 
            	$_SESSION['tablename']['users']
            );

            if ($_SESSION['history']['folderview'] == true) {
            	$users->add(
            		$_SESSION['tablename']['fold_folders'], $id , "VIEW",
            		_VIEW_FOLDER . " " . strtolower(_NUM)
            		. $folderArray['folder_id'], 
            		$_SESSION['config']['databasetype'], 'folder'
            	);
            }
        }
    }
}
        ?>
        	<div class="block">
            	<h4><a href="#" onclick="history.go(-1);" class="back"></a></h4>
        	</div>
        	<div class=blank_space>&nbsp;</div>
        </div>
        <dl id="tabricator2">
            <dt><?php  echo _FOLDER_DETAILLED_PROPERTIES;?></dt>
            <dd>
            <form method="post" name="index_folder" id="index_folder" action="index.php?page=show_folder&module=folder&id=<?php  echo $_SESSION['current_folder_id'] ?>">
                <h2><span class="date"><b><?php  echo _FOLDER_DETAILLED_PROPERTIES;?></b></span></h2>
                <br/>
                <table cellpadding="2" cellspacing="2" border="0" class="block forms details" width="100%">
                    <tr>
                        <th align="left" class="picto" >&nbsp;</th>
                        <th ><?php  echo _FOLDERID_LONG; ?> :</th>
                        <td ><input type="text" class="readonly" readonly="readonly" value="<?php  echo $folderArray['folder_id'] ; ?>" size="40" id="folder_id" name="folder_id" /></td>
                        <th align="left" class="picto" >&nbsp;</th>
                        <th ><?php  echo _FOLDERNAME; ?> :</th>
                        <td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $folderArray['folder_name']; ?>" id="folder_name" name="folder_name" /></td>
                    </tr>
                    <tr>
                        <th align="left" class="picto" >&nbsp;</th>
                        <th ><?php  echo _FOLDERTYPE; ?> :</th>
                        <td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $folderArray['foldertype_label']; ?>" id="foldertype"  name="foldertype" />
                        <input type="hidden" name="foldertype_id" id="foldertype_id" value="<?php  echo $folderArray['foldertype_id']; ?>" />
                        </td>
                        <th align="left" class="picto" >&nbsp;</th>
                        <th ><?php  echo _STATUS; ?> :</th>
                        <td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $folderArray['status']; ?>" id="status" name="status" /></td>
                    </tr>
                </table>
                <?php 
if (count($folderArray['index']) > 0) {
	?>
    		    <br/>
                <h2>
                	<span class="date">
                		<b><?php  echo _OPT_INDEXES;?></b>
                    </span>
                </h2>
                <br/>
                <table cellpadding="2" cellspacing="2" border="0" class="block forms details" width="100%">
                    <?php
                            $i=0;
                            foreach(array_keys($folderArray['index']) as $key)
                            {
                                if($i%2 != 1 || $i==0) // pair
                                {
                                    ?>
                                    <tr class="col">
                                    <?php
                                }?>
                                <th align="left" class="picto" >
                                    <?php
                                    if(isset($indexes[$key]['img']))
                                    {
                                        ?>
                                        <img alt="<?php echo $folderArray['index'][$key]['label'];?>" title="<?php echo $folderArray['index'][$key]['label'];?>" src="<?php echo $folderArray['index'][$key]['img'];?>"  /></a>
                                        <?php
                                    }
                                    ?>&nbsp;
                                </th>
                                <th align="left" >
                                    <?php echo $folderArray['index'][$key]['label'];?> :
                                </th>
                                <td>
                                    <?php
                                    if($updateRight)
                                    {
                                        $value = '';
                                        if(!empty($folderArray['index'][$key]['show_value']))
                                        {
                                            $value = $folderArray['index'][$key]['show_value'];
                                        }
                                        elseif($folderArray['index'][$key]['default_value'])
                                        {
                                            $value = $folderArray['index'][$key]['default_value'];
                                        }
                                        if($folderArray['index'][$key]['type_field'] == 'input')
                                        {
                                            if($folderArray['index'][$key]['type'] == 'date')
                                            {
                                                ?>
                                                <input type="text" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $value; ?>" size="40"  title="<?php echo $value; ?>" alt="<?php echo $value; ?>" onclick="showCalender(this);" />
                                                <?php
                                            }
                                            else
                                            {
                                                ?>
                                                <input type="text" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $value;?>" size="40"  title="<?php echo $value;?>" alt="<?php echo $value;?>" />
                                                <?php
                                            }
                                        }
                                        else
                                        {
                                            ?>
                                            <select name="<?php echo $key;?>" id="<?php echo $key;?>" >
                                                <option value=""><?php echo _CHOOSE;?>...</option>
                                                <?php for($i=0; $i<count($folderArray['index'][$key]['values']);$i++)
                                                {?>
                                                    <option value="<?php echo $folderArray['index'][$key]['values'][$i]['id'];?>" <?php if($folderArray['index'][$key]['values'][$i]['id'] == $folderArray['index'][$key]['value'] || $folderArray['index'][$key]['values'][$i]['id'] == $value){ echo 'selected="selected"';}?>><?php echo $folderArray['index'][$key]['values'][$i]['label'];?></option>
                                                    <?php
                                                }?>
                                            </select>
                                                <?php
                                        }

                                    }
                                    else
                                    {
                                    ?>
                                        <input type="text" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $folderArray['index'][$key]['show_value'];?>" size="40"  title="<?php  echo $folderArray['index'][$key]['show_value']; ?>" alt="<?php  echo $folderArray['index'][$key]['show_value']; ?>" readonly="readonly" class="readonly" />
                                        <?php
                                    }
                                ?>
                                </td>
                                <?php
                                if($i%2 == 1 && $i!=0) // impair
                                {?>
                                    </tr>
                                    <?php
                                }
                                else
                                {
                                    if($i+1 == count($folderArray['index']))
                                    {
                                        echo '<td  colspan="2">&nbsp;</td></tr>';
                                    }
                                }
                                $i++;
                        }
                ?></table><?php
} 
?>
                <br/>
                <h2>
                    <span class="date"><b><?php  echo _FOLDER_PROPERTIES;?></b></span>
                </h2>
                <br/>
                <table cellpadding="2" cellspacing="2" border="0" class="block forms details" width="100%">
                    <tr>
                        <th align="left" class="picto" >&nbsp;</th>
                        <th ><?php  echo _TYPIST; ?> :</th>
                        <td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $folderArray['typist']; ?>" name="typîst" id="typist" /></td>
                        <th align="left" class="picto" >&nbsp;</th>
                        <th ><?php  echo _CREATION_DATE; ?> :</th>
                        <td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $folderArray['creation_date']; ?>" id="creation_date" name="creation_date"  /></td>
                    </tr>
                    <tr>
                        <th align="left" class="picto" >&nbsp;</th>
                        <th ><?php  echo _SYSTEM_ID; ?> :</th>
                        <td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $folderArray['system_id']; ?>" name="system_id" id="system_id" /></td>
                        <th align="left" class="picto" >&nbsp;</th>
                        <th ><?php  echo _MODIFICATION_DATE; ?> :</th>
                        <td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $folderArray['last_modified_date']; ?>" id="modification_date" name="modification_date"  /></td>
                    </tr>
                </table>
                <br/>
                <p class="buttons" align="center">
                    <?php if($updateRight && count($folderArray['index']) > 0)
                    {
                        ?><input type="submit" class="button" name="update_folder" id="update_folder" value="<?php echo _UPDATE_FOLDER;?>" /><?php
                    }?>
                    <?php if($deleteRight)
                        {?>
                        <input type="submit" class="button"  value="<?php  echo _DELETE_FOLDER;?>" name="delete_folder" onclick="return(confirm('<?php  echo _REALLY_DELETE.' '._THIS_FOLDER.'?\n\r\n\r'._WARNING.' '._ALL_DOCS_AND_SUFOLDERS_WILL_BE_DELETED; ?>'));" />
                        <?php } ?>
                </p>
                </form>
            </dd>
            <dt><?php  echo _ARCHIVED_DOC;?></dt>
            <dd>
                <table width="100%" border="0">
                    <tr>
                        <td valign="top">
                            <div align="left">
                                <?php
if (trim($_SESSION['current_folder_id']) <> '' && ! empty($view)) {
	$select2 = array();
    $select2[$view] = array();
    $tab2 = array();
    array_push($select2[$view], "res_id", "type_label");
    $tab2 = $request->select(
    	$select2, "folders_system_id = '" . $_SESSION['current_folder_id']
    	. "' and status <> 'DEL'", " order by type_label ",
    	$_SESSION['config']['databasetype'], "500", false
    );
    for ($i = 0; $i < count($tab2); $i ++) {
    	for ($j = 0; $j < count($tab2[$i]); $j ++) {
        	foreach (array_keys($tab2[$i][$j]) as $value) {
            	if ($tab2[$i][$j][$value] == 'res_id') {
                	$tab2[$i][$j]['res_id'] = $tab2[$i][$j]['value'];
                    $tab2[$i][$j]["label"] = _GED_NUM;
                    $tab2[$i][$j]["size"] = "10";
                    $tab2[$i][$j]["label_align"] = "left";
                    $tab2[$i][$j]["align"] = "right";
                    $tab2[$i][$j]["valign"] = "bottom";
                    $tab2[$i][$j]["show"] = false;
                }
                if ($tab2[$i][$j][$value] == "type_label") {
                    $tab2[$i][$j]["value"] = $request->show_string(
                    	$tab2[$i][$j]["value"]
                    );
                    $tab2[$i][$j]["label"] = _TYPE;
                    $tab2[$i][$j]["size"] = "40";
                    $tab2[$i][$j]["label_align"] = "left";
                    $tab2[$i][$j]["align"] = "left";
                    $tab2[$i][$j]["valign"] = "bottom";
                    $tab2[$i][$j]["show"] = true;
                }
            }
        }
    }
    $_SESSION['FILLING_RES']['PARAM']['RESULT'] = array();
    $_SESSION['FILLING_RES']['PARAM']['RESULT'] = $tab2;
    $_SESSION['FILLING_RES']['PARAM']['NB_TOTAL'] = $i;
    $_SESSION['FILLING_RES']['PARAM']['TITLE'] =  $i . " " . _FOUND_DOC;
    $_SESSION['FILLING_RES']['PARAM']['WHAT'] = 'res_id';
    $_SESSION['FILLING_RES']['PARAM']['NAME'] = "filling_res";
    $_SESSION['FILLING_RES']['PARAM']['KEY'] = 'res_id';
    $details_page = $sec->get_script_from_coll($folderArray['coll_id'], 'script_details');
    $_SESSION['FILLING_RES']['PARAM']['DETAIL_DESTINATION'] = $details_page."";
    $_SESSION['FILLING_RES']['PARAM']['BOOL_VIEW_DOCUMENT'] = true;
    $_SESSION['FILLING_RES']['PARAM']['BOOL_RADIO_FORM'] = false;
    $_SESSION['FILLING_RES']['PARAM']['METHOD'] = "";
    $_SESSION['FILLING_RES']['PARAM']['ACTION'] = "";
    $_SESSION['FILLING_RES']['PARAM']['BUTTON_LABEL'] = "";
    $_SESSION['FILLING_RES']['PARAM']['BOOL_DETAIL'] = true;
    $_SESSION['FILLING_RES']['PARAM']['BOOL_ORDER'] = false;
    $_SESSION['FILLING_RES']['PARAM']['BOOL_FRAME'] = true;

    ?>
    <iframe name="filling_res" id="filling_res" src="<?php  
    echo $_SESSION['config']['businessappurl']
    	. "index.php?display=true&module=folder&page=filling_res";
    ?>" frameborder="0" scrolling="auto" width="400px" height="580px"></iframe>
    <?php
                                    }
                                    else
                                    {
                                        echo "&nbsp;";
                                    }
                                    ?>
                                </div>
                            </td>
                            <td valign="top">
                                <table>
                                    <tr valign="top">
                                        <td>
                                        <iframe name="view_doc" id="view_doc" src="<?php  echo $_SESSION['config']['businessappurl']."index.php?display=true&module=folder&page=list_doc";?>" frameborder="0" scrolling="no" width="570px" height="580px"></iframe>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
            </dd>
            <dt><?php  echo _FOLDER_HISTORY;?></dt>
            <dd>
                <?php  echo $core->execute_modules_services($_SESSION['modules_services'], 'index.php?page=show_folder', "include", "show_history_folder", "folder");?>
            </dd>
            <!--<dt><?php  echo _MISSING_DOC;?></dt>
            <dd>
                <?php  //echo $core->execute_modules_services($_SESSION['modules_services'], 'index.php?page=show_folder', "include","show_missing_doc_in_folder", "folder");?>
            </dd>-->
        </dl>
	</div>
</div>

<script type="text/javascript">
    var item  = $('details_div');
    var tabricator2 = new Tabricator('tabricator2', 'DT');
    if(item)
    {
        item.style.display='block';
    }
</script>

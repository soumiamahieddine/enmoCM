<?php
/**
* File : little_details_invoices.php
*
* @package  Maarch Entreprise
* @version 2.1
* @since 05/2011
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/

require 'core/class/class_request.php';
require 'apps/' . $_SESSION['config']['app_id'] . '/class/class_list_show.php';
require_once 'core/class/class_security.php';
require_once 'core/class/class_history.php';
require_once 'core/manage_bitmask.php';
require_once "apps" . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR  . "security_bitmask.php";
$core = new core_tools();
$core->test_user();
$core->load_lang();

$resId = '';
if (isset($_REQUEST['value']) && !empty($_REQUEST['value'])) {
	$resId = $_REQUEST['value'];
}

$users = new history();
$security = new security();
$func = new functions();
$request = new request;

//$_SESSION['req'] = 'details_invoices';
$is_view = false;
//$_SESSION['indexing'] = array();
if (isset($_SESSION['collection_id_choice'])
	&& ! empty($_SESSION['collection_id_choice'])
) {
	$collId = $_SESSION['collection_id_choice'];
} else {
	$collId = $_SESSION['user']['collections'][0];
}

$table = $security->retrieve_view_from_coll_id($collId);
$is_view = true;
if (empty($table)) {
	$table = $security->retrieve_table_from_coll($collId);
	$is_view = false;
}

$_SESSION['id_to_view'] = $resId;
$_SESSION['doc_id'] = $resId;

$right = $security->test_right_doc($collId, $resId);

if (! $right && $resId <> '') {
	include('apps/'.$_SESSION['config']['app_id'].'/no_right.php');
	exit;
}
if ($resId == '') {
    echo '<br><br><center><h2 style="color:#FFC200;">' . _NO_RESULTS
    	. '</h2></center>';
    exit;
}
if (isset($resId) && ! empty($resId) 
	&& $_SESSION['history']['resview'] == 'true'
) {
	$users->add(
    	$table, $resId , 'VIEW', _VIEW_DOC_NUM . $resId,
        $_SESSION['config']['databasetype'], 'apps'
    );
}
    
$modify_doc = check_right(
    $_SESSION['user']['security'][$collId]['DOC']['securityBitmask'],
    DATA_MODIFICATION
);
$delete_doc = check_right(
    $_SESSION['user']['security'][$collId]['DOC']['securityBitmask'],
    DELETE_RECORD
);
    
if (empty($_SESSION['error'])) {
	$db = new dbquery();
    $db->connect();
    $db->query(
    	"select type_id, type_label, format, typist, creation_date, "
        . "fingerprint, filesize, res_id, work_batch, status, page_count,"
        . " doc_date, identifier, description, source, doc_language from "
        . $table . " where res_id = " . $resId . ""
    );
    //$db->show();
}
    ?>
    <div id="" class="clearfix">
    <?php
if (! empty($_SESSION['error']) ) {
    ?>
        <div class="error">
            <br />
            <br />
            <br />
            <?php  echo $_SESSION['error'];  $_SESSION['error'] = "";?>
            <br />
            <br />
            <br />
        </div>
        <?php
} else {
    if ($db->nb_result() == 0) {
            ?>
            <div align="center">
                <br />
                <br />
                <?php  echo _NO_DOCUMENT_CORRESPOND_TO_IDENTIFIER; ?>.
                <br />
                <br />
                <br />
            </div>
            <?php
	} else  {
            $res = $db->fetch_object();
            $title = '';
            if (isset($res->title)) {
            	$title = $res->title;
            }
            //$description = $res->description;
            $typist = $res->typist;
            $format = $res->format;
            $filesize = $res->filesize;
            $creation_date = $res->creation_date;
            //echo $creation_date;exit;
            $doc_date = $res->doc_date;
            $fingerprint = $res->fingerprint;
            $work_batch = $res->work_batch;
            $ref = $res->identifier;
            $tmp = "";
            $type = $res->type_id;
            $type_label = $res->type_label;
            $type_id = $res->type_id;           
            $res_id = $res->res_id;
            $status = $res->status;
            $page_count = $res->page_count;
            $identifier = $res->identifier;
            //$doc_date = $db->format_date_db($res->doc_date, false);
            //echo "doc_date ".$doc_date;exit;
            $description = $res->description;
            $source = $res->source;
            $doc_language = $res->doc_language;

            ?>
            <div align="center">
            <?php
            if ($type_id <> '0' && $type_id <> '') {
                    $db->query(
                        "select * from " . $_SESSION['tablename']['doctypes']
                        . " where type_id = " . $type_id
                    );
                    $res = $db->fetch_array();
                    $desc = str_replace('\\', '',$res['description']);
                    $type_id = $res['type_id'];
                   
                    require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_indexing_searching_app.php");
                    $indexing_searching = new indexing_searching_app();
                    //$indexing_searching->retrieve_index($res,$_SESSION['collection_id_choice'] );
                    ?>
                    <form method="post" name="index_doc" action="index.php?page=details_invoices&dir=indexing_searching&id=<?php  echo $_SESSION['id_to_view']; ?>" class="forms">
                        <div class="block">
                            <p align="left">
                                    <h3 align="left" onclick="new Effect.toggle('desc3', 'blind');" onmouseover="document.body.style.cursor='pointer';" onmouseout="document.body.style.cursor='auto';" id="h23" class="categorie">
                                        <a href="#"><?php echo _SHOW_DETAILS_DOC; ?></a>
                                    </h3>

                            </p>
                        </div>
                        <div class="desc block_light admin" id="desc3" style="display:none">
                            <div class="ref-unit">
                                <?php echo _MENU." : "; ?>
                                <a href="<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=view_resource_controler&id=<?php  echo $resId; ?>&dir=indexing_searching" target="_blank"><b><?php  echo _VIEW_DOC_FULL; ?></b> </a>
                                        |
                                <a href="<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=details&dir=indexing_searching&id=<?php  echo $resId; ?>" target="_blank"><b><?php  echo _DETAILS_DOC_FULL; ?> </b></a>
                                <hr/>
                                <p>
                                    <label>
                                    <?php echo _NUM_GED." : "; ?>
                                    </label>
                                    <input type="text" name="resId" id="resId" value="<?php  echo $resId;?>" />
                                </p>
                                <p>
                                    <label>
                                    <?php echo _PIECE_TYPE." : "; ?>
                                    </label>
                                    <input type="text" name="typeLabel" id="typeLabel" value="<?php  echo $func->show_string($type_label);?>" />
                                </p>
                                <?php
                                $db_invoices = new dbquery();
                                $db_invoices->connect();
                                for($cpt6=0;$cpt6<=count($_SESSION['index_to_use']);$cpt6++)
                                {
                                    if($_SESSION['index_to_use'][$cpt6]['label'] <> "")
                                    {
                                        $field = $_SESSION['index_to_use'][$cpt6]['column'];
                                        if($is_view)
                                        {
                                            $field = "doc_".$field;
                                        }
                                        $db->query("select ".$field." from ".$table." where res_id = ".$_SESSION['id_to_view']);
                                        $res_mastertype = $db->fetch_array();
                                        //$db->show_array($res_mastertype);
                                        $_SESSION['indexing'][$_SESSION['index_to_use'][$cpt6]['column']] = $res_mastertype[$field];
                                        if($_SESSION['index_to_use'][$cpt6]['date'])
                                        {
                                            $_SESSION['indexing'][$_SESSION['index_to_use'][$cpt6]['column']] = $func->format_date_db($_SESSION['indexing'][$_SESSION['index_to_use'][$cpt6]['column']], false);
                                        }
                                        ?>
                                        <p>
                                            <label for="<?php  echo $_SESSION['index_to_use'][$cpt6]['column'];?>">
                                                <?php
                                                if($_SESSION['index_to_use'][$cpt6]['mandatory'])
                                                {
                                                    echo "<b>".$_SESSION['index_to_use'][$cpt6]['label']."</b> : ";
                                                }
                                                else
                                                {
                                                    echo $_SESSION['index_to_use'][$cpt6]['label']." : ";
                                                }
                                                ?>
                                            </label>
                                            <input type="text" name="<?php  echo $_SESSION['index_to_use'][$cpt6]['column'];?>" id="<?php  echo $_SESSION['index_to_use'][$cpt6]['column'];?>" value="<?php  echo $_SESSION['indexing'][$_SESSION['index_to_use'][$cpt6]['column']];?>" <?php  if($_SESSION['field_error'][$_SESSION['index_to_use'][$cpt6]['column']]){?>style="background-color:#FF0000"<?php  }?> <?php  if(!$modify_doc){?> class="readonly" readonly="readonly" <?php  } ?>  <?php  if($_SESSION['index_to_use'][$cpt6]['date']){?> onclick='showCalender(this)'<?php  }?>/>
                                            <?php
                                            if($_SESSION['index_to_use'][$cpt6]['mandatory'] && $modify_doc)
                                            {
                                                ?>
                                                <input type="hidden" name="mandatory_<?php  echo $_SESSION['index_to_use'][$cpt6]['column'];?>" id="mandatory_<?php  echo $_SESSION['index_to_use'][$cpt6]['column'];?>" value="true" />
                                                <?php
                                            }
                                            ?>
                                        </p>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </form>
                <iframe name="view" id="view" width="100%" height="700" frameborder="0" scrolling="no" src="<?php  echo $_SESSION['config']['businessappurl']."index.php?display=true&dir=indexing_searching&page=view_resource_controler&id=".$resId;?>"></iframe>
                <?php
            }
            else
            {
                echo _DOC_NOT_QUALIFIED."<br/>";
                if($security->collection_user_right($_SESSION['collection_id_choice'], "can_delete"))
                {
                    ?>
                    <form method="post" name="index_doc" action="index.php?page=details&dir=indexing_searching&id=<?php  echo $_SESSION['id_to_view']; ?>" class="forms">
                        <input type="submit" class="button"  value="<?php  echo _DELETE_THE_DOC;?>" name="delete_doc" onclick="return(confirm('<?php  echo _REALLY_DELETE.' '._THIS_DOC;?> ?\n\r\n\r'));"/>
                    </form>
                    <?php
                }
            }
            if(!empty($_SESSION['error_page']))
            {
                ?>
                <script type="text/javascript">
                    alert("<?php  echo $func->wash_html($_SESSION['error_page']);?>");
                    <?php
                    if(isset($_POST['delete_doc']))
                    {
                        ?>
                        window.location.href = 'index.php';
                        <?php
                    }
                    ?>
                </script>
                <?php
                $_SESSION['error'] = "";
                $_SESSION['error_page'] = "";
            }
            ?>
            </div>
            <?php
        }
    }

$core->load_js();
?>
</div>

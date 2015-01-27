<?php
/*
*    Copyright 2008,2009 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief Delete a document type
*
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

$core_tools = new core_tools();
$core_tools->test_admin('admin_architecture', 'apps');
//here we loading the lang vars
$core_tools->load_lang();
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_types.php");
$func = new functions();
if(isset($_GET['id']))
{
    $s_id = addslashes($func->wash($_GET['id'], "no", _THE_DOCTYPE));
}
else
{
    $s_id = "";
}

// delete a doc type
$db = new dbquery();
$sec = new security();
$db->connect();
$db->query("select description from ".$_SESSION['tablename']['doctypes']." where type_id = '".$s_id."'");
if($db->nb_result() == 0)
{
    $_SESSION['error'] = _DOCTYPE.' '._UNKNOWN;
    ?>
        <script type="text/javascript">window.location.href="<?php echo $_SESSION['config']['businessappurl']; ?>index.php?page=types&order=<?php echo $_REQUEST['order'];?>&order_field=<?php echo $_REQUEST['order_field'];?>&start=<?php echo $_REQUEST['start'];?>&what=<?php echo $_REQUEST['what'];?>";</script>
    <?php
    exit();
}
else
{
    $db->query("select coll_id from doctypes where type_id = '".$s_id."'");
    $collId = $db->fetch_object();
    $table = $sec->retrieve_table_from_coll($collId->coll_id);

    $db->query("select res_id from ". $table ." where type_id = '".$s_id."' limit 1");

    if($db->nb_result() == 0)
    {
        $info = $db->fetch_object();
        $db->query("delete from ".$_SESSION['tablename']['doctypes']." where type_id = ".$s_id."");
        $db->query("delete from ".$_SESSION['tablename']['doctypes_indexes']." where type_id = ".$s_id."");

        $_SESSION['service_tag'] = "doctype_delete";
        $_SESSION['m_admin']['doctypes']['TYPE_ID'] = $s_id;
        $core_tools->execute_modules_services($_SESSION['modules_services'], 'doctype_del', "include");
        $core_tools->execute_app_services($_SESSION['app_services'], 'doctype_del', 'include');
        $_SESSION['service_tag'] = '';
        unset($_SESSION['m_admin']['doctypes']['TYPE_ID']);
        if($_SESSION['history']['doctypesdel'] == 'true')
        {
            require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
            $users = new history();
            $users->add($_SESSION['tablename']['doctypes'], $s_id,"DEL",'doctypesdel',_DOCTYPE_DELETION." : ".$info->description, $_SESSION['config']['databasetype']);
        }
        $_SESSION['error'] = _DELETED_DOCTYPE;

        ?>
            <script type="text/javascript">window.location.href="<?php echo $_SESSION['config']['businessappurl'] ?>index.php?page=types&order=<?php echo $_REQUEST['order'];?>&order_field=<?php echo $_REQUEST['order_field'];?>&start=<?php echo $_REQUEST['start'];?>&what=<?php echo $_REQUEST['what'];?>";</script>
        <?php
        exit();
    }else if(isset($_POST['id'])){
        $new_s_id=$_POST['doc_type_id'];
        $s_id=$_POST['id'];
        require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");

        $db->query("UPDATE ".$table." SET type_id = '".$new_s_id."' WHERE res_id IN (SELECT res_id FROM ".$table." WHERE type_id = '".$s_id."')");

        /*if($_SESSION['history']['res_up'] == 'true')
        {
            while($doctypes = $db->fetch_object())
            {
                echo 'toto';
                
                    
                $docs = new history();
                $docs->add($table, $s_id,"UP",'doctypesup',_DOC_OF_DOCTYPE_UPDATED." : ".$info->description, $_SESSION['config']['databasetype']);
            }
        }*/

        $db->query("delete from ".$_SESSION['tablename']['doctypes']." where type_id = ".$s_id."");
        $db->query("delete from ".$_SESSION['tablename']['doctypes_indexes']." where type_id = ".$s_id."");

        $_SESSION['service_tag'] = "doctype_delete";
        $_SESSION['m_admin']['doctypes']['TYPE_ID'] = $s_id;
        $core_tools->execute_modules_services($_SESSION['modules_services'], 'doctype_del', "include");
        $core_tools->execute_app_services($_SESSION['app_services'], 'doctype_del', 'include');
        $_SESSION['service_tag'] = '';
        unset($_SESSION['m_admin']['doctypes']['TYPE_ID']);
        if($_SESSION['history']['doctypesdel'] == 'true')
        {
            $users = new history();
            $users->add($_SESSION['tablename']['doctypes'], $s_id,"DEL",'doctypesdel',_DOCTYPE_DELETION." : ".$info->description, $_SESSION['config']['databasetype']);
        }
        $_SESSION['error'] = _DELETED_DOCTYPE;

        ?>
            <script type="text/javascript">window.location.href="<?php echo $_SESSION['config']['businessappurl'] ?>index.php?page=types&order=<?php echo $_REQUEST['order'];?>&order_field=<?php echo $_REQUEST['order_field'];?>&start=<?php echo $_REQUEST['start'];?>&what=<?php echo $_REQUEST['what'];?>";</script>
        <?php
        exit();
    }else{
        /****************Management of the location bar  ************/
        $admin = new core_tools();
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
        $pagePath = $_SESSION['config']['businessappurl'] . 'index.php?page=types';
        $pageLabel = _DOCTYPES_LIST2;
        $pageId = "types";
        $admin->manage_location_bar($pagePath, $pageLabel, $pageId, $init, $level);
        /***********************************************************/
        echo '<h1><img src="'.$_SESSION["config"]["businessappurl"].'static.php?filename=manage_entities_b.gif&module=entities" alt="" />'._DOCTYPE_DELETION.'</h1>';
        echo "<div class='error' id='main_error'>".$_SESSION['error']."</div>";
        $_SESSION['error'] = "";
        ?>
        <br>
        <div id="main_error">
            <b><?php
            echo _WARNING_MESSAGE_DEL_TYPE;
            ?></b>
        </div>
        <br> 
        <form name="entity_del" id="entity_del" method="post" class="forms">
            <input type="hidden" value="<?php echo $s_id;?>" name="id">
            <h2 class="tit"><?php echo _DOCTYPE_DELETION." : <i>".$label."</i>";?></h2>
            <?php

                echo " - ".$db->nb_result()." "._DOCS_IN_DOCTYPES;
                ?>
                <br>
                <br>
                <select name="doc_type_id" id="doc_type_id" onchange=''>
                    <option value=""><?php echo _CHOOSE_REPLACEMENT_DOCTYPES;?></option>
                    <?php
                    $db->query("select * from doctypes where coll_id = '".$collId->coll_id."' order by description ASC");
                    while($doctypes = $db->fetch_object())
                    {
                        if($doctypes->type_id != $s_id){
                         ?>
                        <option value="<?php echo $doctypes->type_id; ?>"><?php echo $doctypes->description; ?></option>
                        <?php
                        }
                       
                    }
                    ?>
                </select>
                 <p class="buttons">
                    <input type="submit" value="<?php echo _DEL_AND_REAFFECT;?>" name="valid" class="button" onclick='if(document.getElementById("doc_type_id").options[document.getElementById("doc_type_id").selectedIndex].value == ""){alert("<?php echo _CHOOSE_REPLACEMENT_DOCTYPES ?> !");return false;}else{return(confirm("<?php echo _REALLY_DELETE.$s_id; ?> \n\r\n\r<?php echo _DEFINITIVE_ACTION?>"));}'/>
                    <input type="button" value="<?php echo _CANCEL;?>" class="button" onclick="window.location.href='<?php echo $_SESSION['config']['businessappurl'] ?>index.php?page=types&order=<?php echo $_REQUEST['order'];?>&order_field=<?php echo $_REQUEST['order_field'];?>&start=<?php echo $_REQUEST['start'];?>&what=<?php echo $_REQUEST['what'];?>';"/>
                </p>
            </form>
            <script type="text/javascript"></script>
        <?php
        exit();

     }
}

?>

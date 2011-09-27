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
        $users->add($_SESSION['tablename']['doctypes'], $s_id,"DEL",_DOCTYPE_DELETION." : ".$info->description, $_SESSION['config']['databasetype']);
    }
    $_SESSION['error'] = _DELETED_DOCTYPE;

    ?>
        <script type="text/javascript">window.location.href="<?php echo $_SESSION['config']['businessappurl'] ?>index.php?page=types&order=<?php echo $_REQUEST['order'];?>&order_field=<?php echo $_REQUEST['order_field'];?>&start=<?php echo $_REQUEST['start'];?>&what=<?php echo $_REQUEST['what'];?>";</script>
    <?php
    exit();
}

?>

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
* @brief  Displays a document logs
*
* @file hist_doc.php
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup indexing_searching_mlb
*/

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
$core_tools = new core_tools();
$core_tools->test_user();
//here we loading the lang vars
$core_tools->load_lang();
//here we loading the html
$core_tools->load_html();
//here we building the header
$core_tools->load_header('', true, false);
$sec = new security();
$mode = 'small';
if(isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'normal')
{
    $mode = 'normal';
}
?>
<body <?php  if($_SESSION['req'] == 'action'){?>id="hist_action_frame"<?php  }else if($mode =='small'){?>id="hist_courrier_frame"<?php  }?>>
<?php
$func = new functions();
$db_hist = new dbquery();
$db_hist->connect();
$db_hist2 = new dbquery();
$db_hist2->connect();
$couleur=0;
if(isset($_SESSION['collection_id_choice']) && !empty($_SESSION['collection_id_choice']))
{
    $table = $sec->retrieve_table_from_coll($_SESSION['collection_id_choice']);
    $view = $sec->retrieve_view_from_coll_id($_SESSION['collection_id_choice']);
}
else
{
    $table = $_SESSION['collections'][0]['table'];
    $view = $_SESSION['collections'][0]['view'];
}
if(isset($_GET['id']))
{
    $s_id = $_GET['id'];
}
else
{
    $s_id = "";
}
if((empty($table)|| !$table) && (!empty($view) && $view <> false))
{
    $query = "select info, event_date, user_id  from ".$_SESSION['tablename']['history']." WHERE table_name= '".$view."' AND record_id= '".$s_id."' ORDER  BY event_date desc";
}
elseif((empty($view) || !$view) && (!empty($table)&& $table <> false))
{
    $query = "select info, event_date, user_id  from ".$_SESSION['tablename']['history']." WHERE table_name= '".$table."' AND record_id= '".$s_id."' ORDER  BY event_date desc";
}
elseif(!empty($view) && !empty($table)&& $view <> false && $table <> false)
{
    $query = "select info, event_date, user_id  from ".$_SESSION['tablename']['history']." WHERE (table_name= '".$table."' OR table_name = '".$view."') AND record_id= '".$s_id."' ORDER  BY event_date desc";
}
$db_hist->query($query);
//$db_hist->show();
?>
<table cellpadding="0" cellspacing="0" border="0" class="<?php if($mode == 'normal'){echo 'listing spec detailtabricatordebug';}else{echo'listing2';}?>">
    <thead>
        <tr>
            <th><?php  echo _DATE;?></th>
            <th><?php  echo _USER;?> </th>
            <th><?php  echo _DONE;?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $color = ' class="col"';
        while($res_hist=$db_hist->fetch_object())
        {
            if($color == ' class="col"')
            {
                $color = '';
            }
            else
            {
                $color = ' class="col"';
            }
            $db_hist2->query("select lastname, firstname from ".$_SESSION['tablename']['users']." where user_id = '".$res_hist->user_id."'");
            $res_hist2 = $db_hist2->fetch_object();
            if (isset($res_hist2->lastname)) {
                $nom = $res_hist2->lastname;
                $prenom = $res_hist2->firstname;
            }
            ?>
            <tr <?php  echo $color; ?>>
                <td><span><?php  echo $func->dateformat($res_hist->event_date);?></span></td>
                <td><span><?php  echo $prenom." ".$nom." "; ?></span></td>
                <td><span><?php  echo $res_hist->info; ?></span></td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>
<?php $core_tools->load_js();?>
</body>
</html>

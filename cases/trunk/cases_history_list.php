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
include('core/init.php');

require_once("core/class/class_functions.php");
require_once("core/class/class_db.php");
require_once("core/class/class_core_tools.php");
require_once("core/class/class_security.php");
require_once("modules/cases".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR.'class_modules_tools.php');


$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header();
$sec = new security();
$cases = new cases();
?>


<body id="hist_courrier_frame">
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

//Listing only document in this case...

//Get the case information
$case_limitation = " and record_id = '".$_SESSION['cases']['actual_case_id']."' ";

//Get the entire doc library
$docs_library = $cases->get_res_id($_SESSION['cases']['actual_case_id']);
$docs_limitation = ' and record_id in( ';

if(count($docs_library) >1)
{
	foreach($docs_library as $tmp_implode)
	{
		$docs_limitation .= '\''.$tmp_implode.'\',';
	}
	$docs_limitation = substr($docs_limitation, 0,-1);
}
else
$docs_limitation .= '\''.$docs_library[0].'\'';
$docs_limitation .= ' ) ';



$query = "select info, event_date, user_id  from ".$_SESSION['tablename']['history']." WHERE (table_name in ('".$table."', '".$view."') ".$docs_limitation.") OR (table_name= '".$_SESSION['tablename']['cases']."' ".$case_limitation.") ORDER  BY event_date desc";

$db_hist->query($query);
//$db_hist->show();
?>
<table cellpadding="0" cellspacing="0" border="0" class="listing">
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
            $nom = $res_hist2->lastname;
            $prenom = $res_hist2->firstname;
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
</body>
</html>

<?php
/*
*    Copyright 2014 Maarch
*
*   This file is part of Maarch Framework.
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
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*
* @file search_contacts.php
* @author <dev@maarch.org>
* @date $date$
* @version $Revision$
*/

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR."class_indexing_searching_app.php");
$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();

$_SESSION['search']['plain_text'] = "";

$func = new functions();
$conn = new dbquery();
$conn->connect();
$search_obj = new indexing_searching_app();

$_SESSION['indexation'] = false;

if (isset($_REQUEST['exclude'])){
    $_SESSION['excludeId'] = $_REQUEST['exclude'];
}

$mode = 'normal';

$core_tools->test_service('search_contacts', 'apps');
/****************Management of the location bar  ************/
$init = false;
if(isset($_REQUEST['reinit']) && $_REQUEST['reinit']  == "true")
{
    $init = true;
}
$level = "";
if(isset($_REQUEST['level']) && ($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1))
{
    $level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=search_contacts&dir=indexing_searching';
$page_label = _SEARCH_CONTACTS;
$page_id = "search_contacts";
$core_tools->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/

// load saved queries for the current user in an array
$conn->query("select query_id, query_name from ".$_SESSION['tablename']['saved_queries']." where user_id = '".$_SESSION['user']['UserId']."' order by query_name");
$queries = array();
while($res = $conn->fetch_object())
{
    array_push($queries, array('ID'=>$res->query_id, 'LABEL' => $res->query_name));
}

//Check if web brower is ie_6 or not
if (preg_match("/MSIE 6.0/", $_SERVER["HTTP_USER_AGENT"])) {
    $browser_ie = 'true';
    $class_for_form = 'form';
    $hr = '<tr><td colspan="2"><hr></td></tr>';
    $size = '';
} elseif(preg_match('/msie/i', $_SERVER["HTTP_USER_AGENT"]) && !preg_match('/opera/i', $HTTP_USER_AGENT) )
{
    $browser_ie = 'true';
    $class_for_form = 'forms';
    $hr = '';
     $size = '';
}
else
{
    $browser_ie = 'false';
    $class_for_form = 'forms';
    $hr = '';
     $size = '';
}

// building of the parameters array used to pre-load the category list and the search elements
$param = array();

// Sorts the param array
function cmp($a, $b)
{
    return strcmp(strtolower($a["label"]), strtolower($b["label"]));
}
uasort($param, "cmp");

$tab = $search_obj->send_criteria_data($param);
//$conn->show_array($param);
//$conn->show_array($tab);

// criteria list options
$src_tab = $tab[0];

$core_tools->load_js();
?>
<?php // echo $_SESSION['current_search_query'];?>
<script type="text/javascript" src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=search_adv.js" ></script>
<script type="text/javascript">
<!--
    var valeurs = { <?php echo $tab[1];?>};
    var loaded_query = <?php if(isset($_SESSION['current_search_query']) && !empty($_SESSION['current_search_query']))
    { echo $_SESSION['current_search_query'];}else{ echo '{}';}?>;

    function del_query_confirm()
    {
        if(confirm('<?php echo _REALLY_DELETE.' '._THIS_SEARCH.'?';?>')) {
            del_query_db($('query').options[$('query').selectedIndex], 'select_criteria', 'frmsearch2', '<?php echo _SQL_ERROR;?>', '<?php echo _SERVER_ERROR;?>', '<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=manage_query';?>');
            return false;
        }
    }
-->
</script>

<h1><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_search_b.gif" alt="" /> <?php echo _SEARCH_CONTACTS; ?></h1>
<div id="inner_content">

<?php 
if (count($queries) > 0) { ?>
    <form name="choose_query" id="choose_query" action="#" method="post" >
        <div align="center" style="display:block;" id="div_query">
            <label for="query"><?php echo _MY_SEARCHES;?> : </label>
            <select name="query" id="query" onchange="load_query_db(this.options[this.selectedIndex].value, 'select_criteria', 'frmsearch2', '<?php echo _SQL_ERROR;?>', '<?php echo _SERVER_ERROR;?>', '<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=manage_query';?>');return false;" >
                <option id="default_query" value=""><?php echo _CHOOSE_SEARCH;?></option>
                <?php for($i=0; $i< count($queries);$i++)
                {
                ?><option value="<?php echo $queries[$i]['ID'];?>" id="query_<?php echo $queries[$i]['ID'];?>"><?php echo $queries[$i]['LABEL'];?></option><?php }?>
            </select>
            <input name="del_query" id="del_query" value="<?php echo _DELETE_QUERY;?>" type="button"  onclick="del_query_confirm();" class="button" style="display:none" />
        </div>
    </form>
<?php } ?>

<form name="frmsearch2" method="get" action="<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=search_contacts_result';?>"  id="frmsearch2" class="<?php echo $class_for_form; ?>">
    <input type="hidden" name="dir" value="indexing_searching" />
    <input type="hidden" name="page" value="search_contacts_result" />
    <input type="hidden" name="mode" value="<?php echo $mode;?>" />
    <?php
    $contact_types = array();
    $conn->connect();
    $conn->query("SELECT id, label FROM ".$_SESSION['tablename']['contact_types']);
    while($res = $conn->fetch_object()){
        $contact_types[$res->id] = $conn->show_string($res->label); 
    }
    ?>
        <tr>
            <td colspan="2" ><h2><?php echo _SEARCH_CONTACTS; ?></h2></td>
        </tr>
        <tr >
            <td >
            <div class="block">
                <table border = "0" width="100%">
                    <tr>
                        <td width="70%">
                            <label for="contact_type" class="bold" ><?php echo _CONTACT_TYPE;?> :</label>
                            <select name="contact_type" id="contact_type" >
                                <option value=""><?php echo _CHOOSE_CONTACT_TYPES;?></option>
                                <?php
                                foreach(array_keys($contact_types) as $key)
                                {
                                    ?><option value="<?php echo $key;?>">
                                        <?php echo $contact_types[$key];?>
                                    </option><?php
                                }?>
                            </select>
                            <input type="hidden" name="meta[]" value="contact_type#contact_type#input_text" />
                        </td>
                    </tr>
                    <tr>
                        <td width="70%"><label for="society" class="bold" ><?php echo _STRUCTURE_ORGANISM;?> :</label>
                            <input type="text" name="society" id="society" <?php echo $size; ?>  />
                            <input type="hidden" name="meta[]" value="society#society#input_text" />
                        </td>
                    </tr>
                    <tr>
                        <td width="70%"><label for="lastname" class="bold"><?php echo _LASTNAME;?> :</label>
                            <input type="text" name="lastname" id="lastname" <?php echo $size; ?>  />
                            <input type="hidden" name="meta[]" value="lastname#lastname#input_text" />
                        </td>
                    </tr>
                    <tr>
                        <td width="70%"><label for="firstname" class="bold" ><?php echo _FIRSTNAME;?> :</label>
                            <input type="text" name="firstname" id="firstname" <?php echo $size; ?>  />
                            <input type="hidden" name="meta[]" value="firstname#firstname#input_text" />
                        </td>
                    </tr>
                    <tr>
                        <td width="70%"><label for="created_by" class="bold"><?php echo _CREATE_BY;?> :</label>
                            <input type="text" name="created_by" id="created_by" onkeyup="erase_contact_external_id('created_by', 'created_by_id');"/>
                            <input type="hidden" name="meta[]" value="created_by#created_by#input_text" />
                            <div id="contactListByName" class="autocomplete"></div>
                            <script type="text/javascript">
                                initList_hidden_input('created_by', 'contactListByName', '<?php 
                                    echo $_SESSION['config']['businessappurl'];?>index.php?display=true&admin=users&page=users_list_by_name_search', 'what', '2', 'created_by_id');
                            </script>
                            <input id="created_by_id" name="created_by_id" type="hidden" />
                        </td>
                        <td><em><?php echo ""; ?></em></td>
                    </tr>
                    <tr>
                        <td width="70%"><label for="contact_purpose" class="bold"><?php echo _CONTACT_PURPOSE;?> :</label>
                            <input type="text" name="contact_purpose" id="contact_purpose" onkeyup="erase_contact_external_id('contact_purpose', 'contact_purposes_id');"/>
                            <input type="hidden" name="meta[]" value="contact_purpose#contact_purpose#input_text" />
                            <div id="show_contact" class="autocomplete">
                                <script type="text/javascript">
                                    initList_hidden_input('contact_purpose', 'show_contact', '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=contact_purposes_list_by_name&id=<?php echo $id; ?>', 'what', '2', 'contact_purposes_id');
                                </script>
                            </div>
                            <input type="hidden" id="contact_purposes_id" name="contact_purposes_id" />
                        </td>
                        <td><em><?php echo ""; ?></em></td>
                    </tr>
                </table>
                </div>
                <div class="block_end">&nbsp;</div>
            </td>
        </tr>
        <tr><td colspan="2"><hr/></td></tr>

    <select name="select_criteria" id="select_criteria" style="display: none;"></select>

    <table align="center" border="0" width="100%">
        <tr>
            <td><a href="#" onclick="clear_search_form('frmsearch2','select_criteria');clear_q_list();"><img src="<?php  echo $_SESSION['config']['businessappurl']."static.php?filename=reset.gif";?>" alt="<?php echo _CLEAR_SEARCH;?>" /> <?php  echo _CLEAR_SEARCH; ?></a></td>
            <td align="right">
                <input class="button_search_adv" name="imageField" type="submit" value="" /><br/>
                <input class="button_search_adv_text" name="imageField" type="button" value="<?php echo _SEARCH; ?>" />
            </td>
        </tr>
    </table>

</form>
<br/>
<div align="right">
</div>
 </div>
<script type="text/javascript">
load_query(valeurs, loaded_query, 'frmsearch2', '<?php echo $browser_ie;?>, <?php echo _ERROR_IE_SEARCH;?>');
<?php if(isset($_REQUEST['init_search']))
{
    ?>clear_search_form('frmsearch2','select_criteria');clear_q_list(); <?php
}?>
</script>

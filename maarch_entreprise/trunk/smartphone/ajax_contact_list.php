<?php
if (file_exists('../../../core/init.php')) {
    include_once '../../../core/init.php';
}
if (!isset($_SESSION['config']['coreurl'])) {
    header('location: ../../../');
}
require_once('core/class/class_functions.php');
require_once('core/class/class_core_tools.php');
require_once('core/class/class_db.php');
require_once('core/class/class_security.php');
require_once('core/class/class_history.php');
require_once('modules/notes/notes_tables.php');
require_once('core/core_tables.php');
require_once('apps/maarch_entreprise/apps_tables.php');

$core = new core_tools();
$core->load_lang();

$db = new dbquery();
$db->connect();

$query = "SELECT "
    . "is_corporate_person, society, lastname, firstname, phone, email, user_id, contact_id "
. "FROM "
    .$_SESSION['tablename']['contacts']
." WHERE "
    . "(lower(lastname) like lower('".$db->protect_string_db($_REQUEST['searchValue'])."%')  or lower(firstname) like lower('".$db->protect_string_db($_REQUEST['searchValue'])."%') or lower(society) like lower('".$db->protect_string_db($_REQUEST['searchValue'])."%')) AND enabled = 'Y' AND user_id = '" .$_SESSION['user']['UserId']
. "' ORDER BY "
    ."lastname, society";
$returnId = $db->query($query);


// $db->show();

//$returnId = $db->query($query);    

if (!$returnId) {
    $return['status'] = 0;
    $return['msg']    = 'fail';
    echo json_encode($return);
    exit;
}

$return['status']  = 1;
$return['msg']     = 'Recherche effectué';
$return['query'] = $query;
$return['searchValue'] = $_REQUEST['searchValue'];
while ($line = $db->fetch_object()) {
    if($line->is_corporate_person == "N") {
        $return['rechercheContact'] .=
                        '<div id="rechercheContact" class="row" style="display: none;">
                        </div>
                        <div class="row"  id="contacts">
                            <a href="generic_profil_contacts.php?
                                &is_corporate_person=' .$line->is_corporate_person. '
                                &contact_id=' .$line->contact_id. '
                                &user_id=' .$line->user_id. '">
                                                            
                                <label>'
                                        .$line->lastname. ' ' 
                                        .$line->firstname. '
                                </label>
                            </a>
                            <table>
                                <tr>
                                    <td align="left" width="70%"><a href="mailto:' .$line->email. '">' .$line->email. '</a></td>
                                    <td align="right"><a href="tel:' .$line->phone. '">' .$line->phone. '</a></td>
                                </tr>
                            </table>
                        </div>
        ';
    } else {
        $return['rechercheContact'] .=
                        '<div id="rechercheContact" class="row" style="display: none;">
                        </div>
                        <div class="row"  id="contacts">
                            <a href="generic_profil_contacts.php?
                                &is_corporate_person=' .$line->is_corporate_person. '
                                &contact_id=' .$line->contact_id. '
                                &user_id=' .$line->user_id. '">
                                                            
                                <label>'
                                        .$line->society. '
                                </label>
                            </a>
                            <table>
                                <tr>
                                    <td align="left" width="70%"><a href="mailto:' .$line->email. '">' .$line->email. '</a></td>
                                    <td align="right"><a href="tel:' .$line->phone. '">' .$line->phone. '</a></td>
                                </tr>
                            </table>
                        </div>
        ';
    }
}

echo json_encode($return);
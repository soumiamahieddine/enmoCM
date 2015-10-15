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
require_once('core/core_tables.php');
require_once('apps/maarch_entreprise/apps_tables.php');
require_once('modules/notes/notes_tables.php');
require_once('modules/notes/lang/fr.php');
$core = new core_tools();
$core->load_lang();
$db = new dbquery();
$db->connect();

$db->query(
    "SELECT users.lastname as lastname, users.firstname as firstname, 
	users.phone as phone, users.mail as mail, users.user_id as user_id, 
	users.department as department FROM " .USERS_TABLE. " ORDER BY users.lastname
	");
//$db->show();
$notesList = '';
if ($db->nb_result() < 1) {
    $notesList = 'no notes';
} else {
    while ($line = $db->fetch_object()) {
		if($line->user_id != $_SESSION['user']['UserId']) {
			$line->phone = str_replace(' ','',$line->phone);
			$notesList .= '<div class="row">
							<a href="generic_profil_colleagues.php?
								lastname=' .$line->lastname. '
								&firstname=' .$line->firstname. '
								&mail=' .$line->mail. '
								&phone=' .$line->phone. '
								&user_id=' .$line->user_id. '">
							
								<label>' 
									.$line->lastname. ' ' 
									.$line->firstname. '
								</label>
							</a>
							<table>
								<tr>
									<td align="left" width="70%"><a href="mailto:' .$line->mail. '">' .$line->mail. '</a></td>
									<td align="right"><a href="tel:' .$line->phone. '">' .$line->phone. '</a></td>
								</tr>
							</table>
							</div>
			';
		}
    }
}
?>
<div id="colleagues" title="<?php echo _MY_COLLEAGUES;?>" class="panel">
    <input name="colleague" type="search" id="colleague" placeholder="Rechercher" onKeyUp="searchColleagues($('colleague').value);" style="padding:5px;width:100%;"/>
    <br/><br/>
    <fieldset>
       <div id="allColleagues">
            <?php
                echo $notesList;
            ?>
        </div>
    </fieldset>
</div>

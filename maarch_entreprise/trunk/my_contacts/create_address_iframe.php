<?php
/*
*    Copyright 2014 Maarch
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
*
*
* @file
* @author <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

$core_tools2 = new core_tools();
$core_tools2->load_lang();
$core_tools2->test_admin('my_contacts', 'apps');
$core_tools2->load_html();
$core_tools2->load_header('', true, false);

require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_contacts_v2.php");
require_once 'core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
    . 'class_request.php';

$request = new request();
$contact = new contacts_v2();

echo '<div class="error" id="main_error">';
echo $_SESSION['error'];
echo '</div>';

echo '<div class="info" id="main_info">';
echo $_SESSION['info'];
echo '</div>';

$_SESSION['error'] = '';
$_SESSION['info'] = '';

$request->connect();
$query = "select * from ".$_SESSION['tablename']['contacts_v2']." where contact_id = ".$_SESSION['contact']['current_contact_id']." and user_id = '".$request->protect_string_db($_SESSION['user']['UserId'])."'";

$request->query($query);

$_SESSION['m_admin']['contact'] = array();
$line = $request->fetch_object();
$_SESSION['m_admin']['contact']['ID'] = $line->contact_id;
$_SESSION['m_admin']['contact']['TITLE'] = $request->show_string($line->title);
$_SESSION['m_admin']['contact']['LASTNAME'] = $request->show_string($line->lastname);
$_SESSION['m_admin']['contact']['FIRSTNAME'] = $request->show_string($line->firstname);
$_SESSION['m_admin']['contact']['SOCIETY'] = $request->show_string($line->society);
$_SESSION['m_admin']['contact']['SOCIETY_SHORT'] = $request->show_string($line->society_short);
$_SESSION['m_admin']['contact']['FUNCTION'] = $request->show_string($line->function);
$_SESSION['m_admin']['contact']['OTHER_DATA'] = $request->show_string($line->other_data);
$_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] = $request->show_string($line->is_corporate_person);
$_SESSION['m_admin']['contact']['CONTACT_TYPE'] = $line->contact_type;
$_SESSION['m_admin']['contact']['OWNER'] = $line->user_id;

$core_tools2->load_js();
if (isset($_GET['iframe']) && $_GET['iframe'] == 'iframe_up_add') {
	$contact->formaddress("add", "", false, "iframe_add_up");
} else {
	unset($_SESSION['m_admin']['address']);
	$contact->formaddress("add", "", false, "iframe");
}

?>
	<script type="text/javascript">
		resize_frame_contact('address');
	</script>
<?php

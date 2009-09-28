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

/*
* @brief  Basket result page :  Displays mails to be validate (used in Letterbox application)
*
* Deprecated file
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
*/
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");

$core_tools = new core_tools();
$core_tools->load_lang();
if(!isset($_REQUEST['field']) || empty($_REQUEST['field']))
{
	$page = "waiting_res_list.php";
	header("location: ".$page);
	exit;
}
else
{
	$_SESSION['detail_id'] = $_REQUEST['field'];
	$core_tools = new core_tools();
	if(!$core_tools->is_module_loaded("indexing_searching"))
	{
		echo "Indexing_Searching module missing !<br/>Please install this module.";
		exit();
	}
	?>
	<script language="JavaScript" type="text/javascript" >
		window.top.location = '<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=index_file&module=indexing_searching';
	</script>
	<?php
}
?>

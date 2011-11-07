<?php
/**
* File : autocomplete_users.php
*
* Autocompletion list on market or project
*
* @package  maarch
* @version 1
* @since 10/2005
* @license GPL v3
* @author  Claire Figueras  <dev@maarch.org>
*/
require('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_request.php');
$req = new request();
$req->connect();
$table = 'users'
$where = '(user_id="'.$_REQUEST['user_id'].'" or firstname = "'.$_REQUEST['user_id'].'" and lastname = "'.$_REQUEST['user_id'].'" and STATUS <> "DEL"';
$select = array();
$select[$table]= array( 'user_id', 'lastname',  'firstname');
$other = 'order by lastname';

$res = $req->select($select, $where, $other, $_SESSION['config']['databasetype'], 11,false,"","","", false);

echo "<ul>\n";
for($i=0; $i< min(count($res), 10)  ;$i++)
{
	echo "<li>".$req->show_string($res[$i][0]['value']).', '.$req->show_string($res[$i][1]['value']).' ('.$res[$i][2]['value'].")</li>\n";
}
if(count($res) == 11)
{
		echo "<li>...</li>\n";
}
echo "</ul>";

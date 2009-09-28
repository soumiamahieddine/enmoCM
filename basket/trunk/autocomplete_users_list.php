<?php
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");

$req = new request();
$req->connect();

$select = array();
$select[$_SESSION['tablename']['users']]= array('lastname', 'firstname', 'user_id');
if($_SESSION['config']['databasetype'] == "POSTGRESQL")
{
	$where = " (lastname ilike '".$_REQUEST['UserInput']."%' or firstname ilike '".$_REQUEST['UserInput']."%' or user_id ilike '".$_REQUEST['UserInput']."%')  and user_id <> '".$_REQUEST['baskets_owner']."' and status = 'OK' and enabled = 'Y'";
}
else
{
	$where = " (lastname like '".$_REQUEST['UserInput']."%' or firstname like '".$_REQUEST['UserInput']."%' or user_id like '".$_REQUEST['UserInput']."%')  and user_id <> '".$_REQUEST['baskets_owner']."' and status = 'OK' and enabled = 'Y'";
}

$other = 'order by lastname';

$res = $req->select($select, $where, $other, $_SESSION['config']['databasetype'], 11,false,"","","", false);

echo "<ul>\n";
for($i=0; $i< min(count($res), 10)  ;$i++)
{
	echo "<li>".$res[$i][0]['value'].', '.$res[$i][1]['value'].' ('.$res[$i][2]['value'].")</li>\n";
}
if(count($res) == 11)
{
		echo "<li>...</li>\n";
}
echo "</ul>";

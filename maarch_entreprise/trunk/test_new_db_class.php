<?php

$db1 = new dbquery();
$db2 = new dbquery();

$db1->connect();
$db2->connect();

$select1 = "select * from users";
$db1->query($select1);
while ($resultSelect1 = $db1->fetch_object()) {
    $select2 = "select * from res_business where typist = '" . $resultSelect1->user_id . "'";
    echo $select2 . "<br />";
    $db2->query($select2);
    while ($resultSelect2 = $db2->fetch_object()) {
        echo $resultSelect2->res_id . "<br />";
    }
}

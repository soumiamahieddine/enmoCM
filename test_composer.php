<?php

echo "ls -al<br/>";
$return = exec("ls -al");
var_dump($return);
echo "composer -V<br/>";
$return = exec('composr', $ret);
var_dump($return);
var_dump($ret);


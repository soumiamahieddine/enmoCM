<?php
$row = 1;
$handle = fopen($_SESSION['config']['businessappurl']."indexing_searching/code_postaux_v201410.csv", "r");
echo "<ul>\n";
while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
    $num = count($data);
    
    
    for ($c=0; $c < $num; $c++) {
    	$pos = strpos($data[3], $_POST['what']);
    	if ($pos === 0) {
    		echo "<li id='".$data[2].",".$data[3]."'>".$data[2]." - ".$data[3]."</li>\n";
   			 break;
}
    }
    $row++;
}
echo "</ul>";
fclose($handle);
?>
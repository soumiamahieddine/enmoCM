<?php
	if (isset($_SESSION['sign']['encodedPinCode'])){
		echo "{status:1, pin : '".$_SESSION['sign']['encodedPinCode']."', index_key : '".$_SESSION['sign']['indexKey']."'}";		
	}
	else echo "{status:0}";		
?>
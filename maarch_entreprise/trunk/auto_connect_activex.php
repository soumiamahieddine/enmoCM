<form action="index.php?display=true&page=autolog_for_activex" id='formlogin'  method="post" name="connect"  class="forms"> 

<SCRIPT language="javascript"> 
var WShnetwork = new ActiveXObject('WScript.Network'); 
document.write('<input type="hidden" name="activex_login" value="' + WShnetwork.UserName + '">');

</SCRIPT> 


<p class="buttons">
<a href="#" onclick="javascript:window.open('');"><!--<?php echo _HOW_CAN_I_LOGIN; ?>-->&nbsp;</a>

<input type="submit" name="Submit" class="button" value="<? echo _CONNECT; ?>"></p>
<?php
if (empty($_SESSION['error'])&&($_GET['logout'] <> 'true'))
{
	?>
	<SCRIPT language="javascript"> 
		document.forms["formlogin"].submit();
	</SCRIPT> 
	<?php
	exit();
}


?>
</form> 
<div class="error">
	<? echo $_SESSION['error'];
	$_SESSION['error'] = '';
	?>
</div>
 


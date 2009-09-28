<script language="javascript">
	function test_form()
	{
		var error_num = check_form_baskets("redirect_my_baskets_to");
		if( error_num == 1)
		{
			document.getElementById('redirect_my_baskets_to').submit();
		}
		else if(error_num == 2)
		{
			alert("<?php echo _FORMAT_ERROR_ON_USER_FIELD;?>");
		}
		else if(error_num == 3)
		{
			alert("<?php echo _BASKETS_OWNER_MISSING;?>");
		}
		else if(error_num == 4)
		{
			alert("<?php echo _CHOOSE_USER_TO_REDIRECT;?>");
		}
		else
		{
			alert("<?php echo _FORM_ERROR;?>");
		}
	}
</script>
<?php
   //	$this->show_array($_SESSION['user']['baskets']);
    require_once($_SESSION['pathtomodules'].'basket'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_modules_tools.php');
	$bask = new basket();
	$modal_content = $bask->redirect_my_baskets_list($_SESSION['user']['baskets'], count($_SESSION['user']['baskets']), $_SESSION['user']['UserId'], "listingbasket specsmall");
	echo "<div>";
	?>
		<script>
			var modal_content = '<?php echo addslashes($modal_content);?>';
		</script>
		<h2><a href="javascript://" onclick="createModal(modal_content, 'modal_redirect', <?php if(count($_SESSION['user']['baskets']) >0) {?>'400px', '950px'<?php }else{?>'100px', '300px'<?php }?>);autocomplete(<?php echo count($_SESSION['user']['baskets']);?>, '<?php echo $_SESSION['urltomodules'].'basket/';?>autocomplete_users_list.php')"><img src = "<?php echo $_SESSION['urltomodules'].'basket/img';?>/missing_user_big_on.gif" alt="" /> <?php echo _MY_ABS; ?> </a></h2>
         <p id="abs"><?php echo _MY_ABS_TXT; ?></p>
    </div>

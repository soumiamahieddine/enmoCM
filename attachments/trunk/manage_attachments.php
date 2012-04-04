<?php
$core = new core_tools();
 ?>
<h2 onclick="change(100)" id="h2100" class="categorie">
	<img src="<?php
echo $_SESSION['config']['businessappurl'];
?>static.php?filename=plus.png" alt="" />&nbsp;<b><?php
echo _ATTACHMENTS;
?> :</b>
	<span class="lb1-details">&nbsp;</span>
</h2>
<br>
<div class="desc" id="desc100" style="display:none">
	<div class="ref-unit">
    <input type="button" name="attach" id="attach" class="button" value="<?php
echo _ATTACH_ANSWER;
?>" onclick="javascript:window.open('<?php
echo $_SESSION['config']['businessappurl'] . 'index.php?display=true'
    . '&module=attachments&page=join_file';
?>','', 'scrollbars=yes,menubar=no,toolbar=no,resizable=yes,status=no,width=550,height=200');" />
    <?php
if ($core->is_module_loaded("templates")) {
    ?>
    <input type="button" name="model" id="model" class="button" value="<?php
    echo _GENERATE_ANSWER;
    ?>" onclick="javascript:window.open('<?php
    echo $_SESSION['config']['businessappurl'] . 'index.php?display=true'
        . '&module=templates&page=choose_model';
    ?>','', 'scrollbars=yes,menubar=no,toolbar=no,resizable=yes,status=no,width=350,height=210');" />
     <?php
}
?>
    <iframe name="list_attach" id="list_attach" src="<?php
echo $_SESSION['config']['businessappurl'] . 'index.php?display=true'
    . '&module=attachments&page=frame_list_attachments&mode=normal';
?>" frameborder="0" width="100%" height="300px"></iframe>
   </div>
</div>

<script type="text/javascript">
var input_res = window.opener.$('res_id_link');
//console.log(input_res);
if (input_res) {
	input_res.value='<?php echo $_REQUEST['field'][0];?>';
}
else {
	window.opener.$('res_id').value='<?php echo $_REQUEST['field'][0];?>';
}
self.close();
</script>

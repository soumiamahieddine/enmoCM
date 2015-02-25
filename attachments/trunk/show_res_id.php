<script type="text/javascript">
var input_res = window.opener.$('res_id_link');
//console.log(input_res);
if (input_res) {
	input_res.value=<?php echo json_encode($_SESSION['stockCheckbox']);?>;
}
else {
	var tab = <?php echo json_encode($_SESSION['stockCheckbox']);?>;
	window.opener.$('res_id').value=tab;
}
self.close();
</script>

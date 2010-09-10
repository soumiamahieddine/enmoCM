<script type="text/javascript">
var input_res = window.opener.$('res_id');
//console.log(input_res);
if(input_res)
{
	input_res.value='<?php echo $_REQUEST['field'];?>';
}
self.close();
</script>

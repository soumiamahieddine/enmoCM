<?php
	
	require_once "modules" . DIRECTORY_SEPARATOR . "thumbnails" . DIRECTORY_SEPARATOR
			. "class" . DIRECTORY_SEPARATOR
			. "class_modules_tools.php";
			
	$res_id = $_REQUEST['res_id'];
	$coll_id = $_REQUEST['coll_id'];
	$tnl = new thumbnails();

	if (isset($_REQUEST['res_id_attach'])){
		$path = $tnl->getPathTnl($_REQUEST['res_id_attach'], $coll_id, 'res_attachments');

		if (!is_file($path)){
			exit();
		}	
		$tab_tnl = $tnl->testMultiPage($path);
		/*$tab_tnl = $tnl->testMultiPage($path);
		$db = new Database();
		$stmt = $db->query("SELECT res_id FROM res_attachments WHERE res_id_master = ? AND tnl_filename IS NOT NULL AND tnl_filename <> 'ERR' ", array($res_id));
		$tmp_array = array();
		$tab_tnl = array();
		while($res = $stmt->fetchObject()){
			$res_id_attach = $res->res_id;
			//echo " res_id_attach : ".$res_id_attach;
			$path = $tnl->getPathTnl($_REQUEST['res_id_attach'], $coll_id, 'res_attachments');
			//echo " path : ".$path;
			if (is_file($path)) array_push($tmp_array,array('id'=>$res_id_attach,'path'=>$path));
		}
		
		foreach ($tmp_array as $p) {
			$tab_tnl_tmp = array();
			$tab_tnl_tmp['id'] = $p['id'];
			$tab_tnl_tmp['path'] = $tnl->testMultiPage($p['path']);
			//$tab_tnl = array_merge($tab_tnl,$tab_tnl_tmp);
			array_push($tab_tnl,$tab_tnl_tmp);
		}*/
	}
	
	else{
		$path = $tnl->getPathTnl($res_id, $coll_id);

		if (!is_file($path)){
			exit();
		}	
		$tab_tnl = $tnl->testMultiPage($path);
	}
?>
<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="pswp__bg"></div>
	<div class="pswp__scroll-wrap">
		<div class="pswp__container">
			<div class="pswp__item"></div>
			<div class="pswp__item"></div>
			<div class="pswp__item"></div>
		</div>

		<div class="pswp__ui pswp__ui--hidden">
			<div class="pswp__top-bar">
				<div class="pswp__counter"></div>
				<!--<button class="pswp__button pswp__button--close" title="Close (Esc)"></button>-->
				<button class="pswp__button pswp__button--share" title="Share"></button>
				<!--<button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>-->
				<button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
				<div class="pswp__preloader">
					<div class="pswp__preloader__icn">
					  <div class="pswp__preloader__cut">
						<div class="pswp__preloader__donut"></div>
					  </div>
					</div>
				</div>
			</div>
			<div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
				<div class="pswp__share-tooltip"></div> 
			</div>
			<button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
			</button>
			<button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
			</button>
			<div class="pswp__caption">
				<div class="pswp__caption__center"></div>
			</div>
		  </div>
		</div>
</div>
<script>
	var pswpElement = document.querySelectorAll('.pswp')[0];
	// build items array
	var items = [
		<?php
		//ksort($tab_tnl);
		//echo "<pre>".print_r($tab_tnl,true)."</pre>";
		if (isset($_REQUEST['res_id_attach'])){
			foreach ($tab_tnl as $num_page=>$path){
				?>
				{
				
					src: 'index.php?page=doc_thumb&module=thumbnails&res_id=<?php echo $_REQUEST['res_id_attach'];?>&coll_id=letterbox_coll&body_loaded&display=true&num_page=<?php echo $num_page;?>&tablename=res_attachments',
					w: 827,
					h: 1169
				},
				<?php
			}
		}
		else{
			foreach ($tab_tnl as $num_page=>$path){
			?>
			{
			
				src: 'index.php?page=doc_thumb&module=thumbnails&res_id=<?php echo $res_id;?>&coll_id=letterbox_coll&body_loaded&display=true&num_page=<?php echo $num_page;?>',
				w: 827,
				h: 1169
			},
			<?php
			}
		}		
		?>
	];

	var options = {
		index: 0,
		closeOnVerticalDrag : false,
		closeOnScroll : false,
		history : false,
		pinchToClose : false
	};
	var gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
	gallery.init();

</script>
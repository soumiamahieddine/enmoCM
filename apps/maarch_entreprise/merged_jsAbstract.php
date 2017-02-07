<?php

class MergedJsAbstract {
	public function header() {
		if ( empty($_GET['debug']) ) {
			$date = mktime(0,0,0,date("m" ) + 2  ,date("d" ) ,date("Y" )  );
			$date = date("D, d M Y H:i:s", $date);
			$time = 30*12*60*60;
			header("Pragma: public");
			header("Expires: ".$date." GMT");
			header("Cache-Control: max-age=".$time.", must-revalidate");
			header('Content-type: text/javascript');
		}
	}
	public function start() {
		ob_start();
	}
	public function end() {
		ob_end_flush();
	}

	public function merge_lib() {
		readfile('apps/maarch_entreprise/js/accounting.js');
		readfile('apps/maarch_entreprise/js/functions.js');
		readfile('apps/maarch_entreprise/js/prototype.js');
		readfile('apps/maarch_entreprise/js/scriptaculous.js');
		readfile('apps/maarch_entreprise/js/jquery.min.js');
		readfile('apps/maarch_entreprise/js/indexing.js');
		readfile('apps/maarch_entreprise/js/scrollbox.js');
		readfile('apps/maarch_entreprise/js/effects.js');
		readfile('apps/maarch_entreprise/js/controls.js');
		readfile('apps/maarch_entreprise/js/tabricator.js');
		readfile('apps/maarch_entreprise/js/search_adv.js');
		readfile('apps/maarch_entreprise/js/maarch.js');
		readfile('apps/maarch_entreprise/js/keypress.js');
		readfile('apps/maarch_entreprise/js/Chart.js');
		readfile('apps/maarch_entreprise/js/chosen.proto.min.js');
		readfile('apps/maarch_entreprise/js/event.simulate.js');
	}

	public function merge_module() {
		if ( ! empty($_SESSION['modules_loaded']) ) {
			foreach(array_keys($_SESSION['modules_loaded']) as $value)
			{
			    if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$_SESSION['modules_loaded'][$value]['name'].DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."functions.js") || file_exists($_SESSION['config']['corepath'].'modules'.DIRECTORY_SEPARATOR.$_SESSION['modules_loaded'][$value]['name'].DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."functions.js"))
			    {
			        include('modules/'.$_SESSION['modules_loaded'][$value]['name'].'/js/functions.js');
			    }
			}
		}
	}

	public function merge() {
		if ( empty($_GET['html'] ) ) {
			$this->header();
			$this->start();
			$this->merge_lib();
			$this->merge_module();
			$this->end();
		} else {
			echo '<html><body><script>';
			$this->merge_lib();
			$this->merge_module();
			echo '</script></body></html>';exit;
		}
	}
}


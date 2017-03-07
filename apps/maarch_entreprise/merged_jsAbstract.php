<?php

/*
*    Copyright 2017 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

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
		include('apps/maarch_entreprise/js/accounting.js');
		include('apps/maarch_entreprise/js/functions.js');
		include('apps/maarch_entreprise/js/prototype.js');
		include('apps/maarch_entreprise/js/scriptaculous.js');
		include('apps/maarch_entreprise/js/jquery.min.js');
		include('apps/maarch_entreprise/js/angular.min.js');
		include('apps/maarch_entreprise/js/angular-route.js');
		include('apps/maarch_entreprise/js/ng-table.min.js');
		include('apps/maarch_entreprise/js/indexing.js');
		include('apps/maarch_entreprise/js/scrollbox.js');
		include('apps/maarch_entreprise/js/effects.js');
		include('apps/maarch_entreprise/js/controls.js');
		include('apps/maarch_entreprise/js/tabricator.js');
		include('apps/maarch_entreprise/js/search_adv.js');
		include('apps/maarch_entreprise/js/maarch.js');
		include('apps/maarch_entreprise/js/keypress.js');
		include('apps/maarch_entreprise/js/Chart.js');
		include('apps/maarch_entreprise/js/chosen.proto.min.js');
		include('apps/maarch_entreprise/js/event.simulate.js');
		include('apps/maarch_entreprise/js/RSVP.js');
                include('apps/maarch_entreprise/js/render.js');
                include('apps/maarch_entreprise/js/jio.js');

		include('apps/maarch_entreprise/js/app.module.js');
		include('apps/maarch_entreprise/js/aController.js');

        echo "\n";
	}

	public function merge_module() {
		if ( !empty($_SESSION['modules_loaded'])) {
			foreach(array_keys($_SESSION['modules_loaded']) as $value)
			{
			    if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$_SESSION['modules_loaded'][$value]['name'].DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."functions.js")
					|| file_exists($_SESSION['config']['corepath'].'modules'.DIRECTORY_SEPARATOR.$_SESSION['modules_loaded'][$value]['name'].DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."functions.js"))
			    {
			        include('modules/'.$_SESSION['modules_loaded'][$value]['name'].'/js/functions.js');
			    }
			    if(file_exists($_SESSION['config']['corepath'].'custom/'.$_SESSION['custom_override_id'].'/modules/'.$_SESSION['modules_loaded'][$value]['name'].'/js/aController.js')
					|| file_exists($_SESSION['config']['corepath'].'/modules/'.$_SESSION['modules_loaded'][$value]['name'].'/js/aController.js'))
			    {
			        include('modules/'.$_SESSION['modules_loaded'][$value]['name'].'/js/aController.js');
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

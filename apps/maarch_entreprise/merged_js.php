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

include_once('../../core/init.php');
include_once($_SESSION['config']['corepath'].DIRECTORY_SEPARATOR.'apps/maarch_entreprise'.DIRECTORY_SEPARATOR.'merged_jsAbstract.php');

class MergedJs extends MergedJsAbstract{
    // Your stuff her
    public function merge_lib() {
        readfile('apps/maarch_entreprise/js/accounting.js');
        readfile('apps/maarch_entreprise/js/prototype.js');
        readfile('apps/maarch_entreprise/js/scriptaculous.js');
        readfile('apps/maarch_entreprise/js/jquery.min.js');
        readfile('apps/maarch_entreprise/js/indexing.js');
        readfile('apps/maarch_entreprise/js/functions.js');
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

        echo "\n";
    }
    public function merge_module() {
        foreach(array_keys($_SESSION['modules_loaded']) as $value)
        {
            if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$_SESSION['modules_loaded'][$value]['name'].DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."functions.js") || file_exists($_SESSION['config']['corepath'].'modules'.DIRECTORY_SEPARATOR.$_SESSION['modules_loaded'][$value]['name'].DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."functions.js"))
            {
                include('modules/'.$_SESSION['modules_loaded'][$value]['name'].'/js/functions.js');
            }
        }
    }
}

$oMergedJs = new MergedJs();
$oMergedJs->merge();


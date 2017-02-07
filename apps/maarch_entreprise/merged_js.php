<?php
include_once('../../core/init.php');
include_once($_SESSION['config']['corepath'].DIRECTORY_SEPARATOR.'apps/maarch_entreprise'.DIRECTORY_SEPARATOR.'merged_jsAbstract.php');

//$date = mktime(0,0,0,date("m" ) + 2  ,date("d" ) ,date("Y" )  );
//$date = date("D, d M Y H:i:s", $date);
//$time = 30*12*60*60;
//header("Pragma: public");
//header("Expires: ".$date." GMT");
//header("Cache-Control: max-age=".$time.", must-revalidate");
//header('Content-type: text/javascript');
//ob_start();
//
//include('apps/maarch_entreprise/js/accounting.js');
//include('apps/maarch_entreprise/js/functions.js');
//include('apps/maarch_entreprise/js/prototype.js');
//include('apps/maarch_entreprise/js/scriptaculous.js');
//include('apps/maarch_entreprise/js/jquery.min.js');
//include('apps/maarch_entreprise/js/indexing.js');
//include('apps/maarch_entreprise/js/scrollbox.js');
//include('apps/maarch_entreprise/js/effects.js');
//include('apps/maarch_entreprise/js/controls.js');
//include('apps/maarch_entreprise/js/tabricator.js');
//include('apps/maarch_entreprise/js/search_adv.js');
//include('apps/maarch_entreprise/js/maarch.js');
//include('apps/maarch_entreprise/js/keypress.js');
//include('apps/maarch_entreprise/js/Chart.js');
//include('apps/maarch_entreprise/js/chosen.proto.min.js');
//include('apps/maarch_entreprise/js/event.simulate.js');
//
//foreach(array_keys($_SESSION['modules_loaded']) as $value)
//{
//    if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$_SESSION['modules_loaded'][$value]['name'].DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."functions.js") || file_exists($_SESSION['config']['corepath'].'modules'.DIRECTORY_SEPARATOR.$_SESSION['modules_loaded'][$value]['name'].DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."functions.js"))
//    {
//        include('modules/'.$_SESSION['modules_loaded'][$value]['name'].'/js/functions.js');
//    }
//}

class MergedJs extends MergedJsAbstract{
    // Your stuff her
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


//        readfile('apps/maarch_entreprise/js/angular.min.js');
//        readfile('custom/capgemini/apps/maarch_entreprise/js/angular-drag-and-drop-lists.js');
//        readfile('custom/capgemini/apps/maarch_entreprise/js/angular-timeago.js');
//        readfile('custom/capgemini/apps/maarch_entreprise/js/clickoutside.directive.js');
//        readfile('custom/capgemini/apps/maarch_entreprise/js/moment-with-locales.js');
//        readfile('custom/capgemini/apps/maarch_entreprise/js/ngDatepicker.js');
//        readfile('custom/capgemini/apps/maarch_entreprise/js/ngMask.js');
//        readfile('custom/capgemini/apps/maarch_entreprise/js/color-picker.js');
//
//        readfile('apps/maarch_entreprise/js/jgrowl.js');
//
//        readfile('apps/maarch_entreprise/js/jquery.browser.js');
//        readfile('apps/maarch_entreprise/js/jquery-iframe-auto-height.js');
//
//        readfile('custom/capgemini/modules/basket/js/app.js');
//        readfile('custom/capgemini/modules/basket/js/dragndrop.js');
//        readfile('custom/capgemini/modules/basket/js/sound.js');
//        readfile('custom/capgemini/modules/basket/js/check_error.js');

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

//ob_end_flush();


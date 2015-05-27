<?php
include_once('../../core/init.php');

    $date = mktime(0,0,0,date("m" ) + 2  ,date("d" ) ,date("Y" )  );
    $date = date("D, d M Y H:i:s", $date);
    $time = 30*12*60*60;
    header("Pragma: public");
    header("Expires: ".$date." GMT");
    header("Cache-Control: max-age=".$time.", must-revalidate");
    header('Content-type: text/javascript');
    ob_start();

    include('apps/'.$_SESSION['config']['app_id'] .'/js/accounting.js');
    include('apps/'.$_SESSION['config']['app_id'] .'/js/functions.js');
    include('apps/'.$_SESSION['config']['app_id'] .'/js/prototype.js');
    include('apps/'.$_SESSION['config']['app_id'] .'/js/scriptaculous.js');
    include('apps/'.$_SESSION['config']['app_id'] .'/js/scrollbox.js');
    include('apps/'.$_SESSION['config']['app_id'] .'/js/effects.js');
  //  include('apps/'.$_SESSION['config']['app_id'] .'/js/slider.js');
    include('apps/'.$_SESSION['config']['app_id'] .'/js/controls.js');
    //include('apps/'.$_SESSION['config']['app_id'] .'/js/concertina.js');
    //include('apps/'.$_SESSION['config']['app_id'] .'/js/protohuds.js');
    include('apps/'.$_SESSION['config']['app_id'] .'/js/tabricator.js');
    include('apps/'.$_SESSION['config']['app_id'] .'/js/indexing.js');
    include('apps/'.$_SESSION['config']['app_id'] .'/js/maarch.js');
    include('apps/'.$_SESSION['config']['app_id'] .'/js/keypress.js');
    include('apps/'.$_SESSION['config']['app_id'] .'/js/Chart.js');

    foreach(array_keys($_SESSION['modules_loaded']) as $value)
    {
        if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$_SESSION['modules_loaded'][$value]['name'].DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."functions.js") || file_exists($_SESSION['config']['corepath'].'modules'.DIRECTORY_SEPARATOR.$_SESSION['modules_loaded'][$value]['name'].DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."functions.js"))
        {
            include('modules/'.$_SESSION['modules_loaded'][$value]['name'].'/js/functions.js');
        }
    }
  ob_end_flush();
?>

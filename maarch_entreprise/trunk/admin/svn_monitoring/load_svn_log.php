<?php

/**
 *Change cycle list according to the life cycle policy 
 * 
 * 
 * 
 */
require_once("core/class/class_core_tools.php");
$core_tools = new core_tools();
$core_tools->load_lang();

if (   (isset($_REQUEST['onlineVersion']) && !empty($_REQUEST['onlineVersion']))
    && (isset($_REQUEST['localVersion'])  && !empty($_REQUEST['localVersion']))
) {
    
    $svnLogOnline = svn_log($_REQUEST['onlineVersion']);
    $svnLogLocal  = svn_log($_REQUEST['localVersion']);

    $svnReleaseOnline   = $svnLogOnline[0]['rev'];
    $svnReleaseLocal    = $svnLogLocal[0]['rev'];


    for ($j=0; $j<count($svnLogOnline); $j++) {
        $formatText .= 'N° release : '.$svnLogOnline[$j]['rev'];
        $formatText .= ', le '.substr($svnLogOnline[$j]['date'], 0, 10).' par '.$svnLogOnline[$j]['author'];
        if ($svnLogOnline[$j]['rev'] == $svnReleaseLocal) {
            $formatText .= '<span ';
            $formatText .= 'style="color: #CC7777;" ';
            $formatText .= '>';
                $formatText .= ' (version installée)';
            $formatText .= '</span>';
        }
        $formatText .= '<blockquote>';
            $formatText .= '<span ';
            $formatText .= 'style="color: #7777CC;"';
            $formatText .= '>';
                $formatText .= $svnLogOnline[$j]['msg'];
                $formatText .= '&nbsp;';
            $formatText .= '</span>';
            $formatText .= '<blockquote>';
                $formatText .= '<span ';
                $formatText .= 'style="color: #8888BB;" ';
                $formatText .= '>';
                for ($k=0; $k<count($svnLogOnline[$j]['paths']); $k++) {
                    $formatText .= $svnLogOnline[$j]['paths'][$k]['action'];
                    $formatText .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                    $formatText .= $svnLogOnline[$j]['paths'][$k]['path'];
                    $formatText .= '<br />';
                }
            $formatText .= '</blockquote>';
        $formatText .= '</blockquote>';
    }
    
    $formatText = str_replace("\n", ' ', $formatText);

    echo "{status : 0, svnLog : '" . addslashes($formatText) . "'}";
    exit ();

} else {
    echo "{status : 1}";
    exit ();
}

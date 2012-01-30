<?php
/*
*    Copyright 2008-2011 Maarch
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

/*
* @brief Contains the svn_monitoring return from ajax
*
*
* @file
* @author Arnaud Veber
* @date $date$
* @version $Revision$
* @ingroup admin
*/

require_once("core/class/class_core_tools.php");
$core_tools = new core_tools();
$core_tools->load_lang();

if ((isset($_REQUEST['onlineVersion']) && !empty($_REQUEST['onlineVersion']))
    && (isset($_REQUEST['localVersion'])  && !empty($_REQUEST['localVersion']))
) {
    if (@file_get_contents('http://svn.maarch.org/core') && extension_loaded('svn')) {
        $svnLogOnline = svn_log($_REQUEST['onlineVersion']);
        $svnReleaseOnline   = $svnLogOnline[0]['rev'];
        
        $entries = false;
        $svnReleaseLocal = $svnReleaseOnline.$svnReleaseOnline+1;
        $path = $_REQUEST['localVersion'].'/.svn/entries';
        if (file_exists($path)) {
            $entries = true;
            $fileLine = file($path);
            $svnReleaseLocal = $fileLine[10];
        }
        
        if (!$entries && !ini_get('safe_mode')) {
            exec('svn log '.$_REQUEST['localVersion'], $svnLogLocal);
            $svnReleaseLocalTemp = explode(' ', $svnLogLocal[1]);
            $svnReleaseLocal = substr($svnReleaseLocalTemp[0], 1);
        }

        for ($j=0; $j<count($svnLogOnline); $j++) {
            $formatText .= _RELEASE_NUMBER.' : <b>'.$svnLogOnline[$j]['rev'].'</b>';
            if ($svnLogOnline[$j]['rev'] == $svnReleaseLocal) {
                $formatText .= '<span ';
                $formatText .= 'style="color: #77CC77;" ';
                $formatText .= '>';
                    $formatText .= '<b>';
                        if ($j == 0) {
                            $formatText .= ' ('._UP_TO_DATE.')';
                        }
                        else {
                            $formatText .= ' ('._ACTUAL_INSTALLATION.')';
                        }
                    $formatText .= '</b>';
                $formatText .= '</span>';
            }
            /*else {
                if ($j == 0 && ($svnReleaseOnline > $svnReleaseLocal)) {
                    $formatText .= '<a href="'.$_SESSION['config']['businessappurl'].'admin/svn_monitoring/svn_monitoring_update.php?dir='.$_REQUEST['localVersion'].'" ';
                    $formatText .= 'target="_blank" ';
                    $formatText .= 'style="color: #CC7777;" ';
                    $formatText .= '>';
                        $formatText .= '<b>';
                            $formatText .= ' ('._MAKE_UPDATE.')';
                        $formatText .= '</b>';
                    $formatText .= '</a>';
                }
            }*/
            $formatText .= '<br />';
            $date = substr($svnLogOnline[$j]['date'], 0, 10);
            sscanf(substr($svnLogOnline[$j]['date'], 0, 10), "%4s-%2s-%2s", $date_Y, $date_m, $date_d);
            switch ($date_m) {
                case 01: $date_mois = _JANUARY; break;
                case 02: $date_mois = _FEBRUARY; break;
                case 03: $date_mois = _MARCH; break;
                case 04: $date_mois = _APRIL; break;
                case 05: $date_mois = _MAY; break;
                case 06: $date_mois = _JUNE; break;
                case 07: $date_mois = _JULY; break;
                case 08: $date_mois = _AUGUST; break;
                case 09: $date_mois = _SEPTEMBER; break;
                case 10: $date_mois = _OCTOBER; break;
                case 11: $date_mois = _NOVEMBER; break;
                case 12: $date_mois = _DECEMBER; break;
            }
            $formatText .= $date_d.' '.$date_mois.' '.$date_Y.' '._BY.' '.$svnLogOnline[$j]['author'];
            $formatText .= '<blockquote>';
                $formatText .= '<fieldset style="border: 1px solid #777799;">';
                    $formatText .= '<legend ';
                    $formatText .= 'style="color: #7777CC;"';
                    $formatText .= '>';
                        $formatText .= '-> '.$svnLogOnline[$j]['msg'];
                        $formatText .= '&nbsp;';
                    $formatText .= '</legend>';
                    $formatText .= '&nbsp;<br />';
                    $formatText .= '<span ';
                    $formatText .= 'style="color: #8888BB;" ';
                    $formatText .= '>';
                    for ($k=0; $k<count($svnLogOnline[$j]['paths']); $k++) {
                        $action = false;
                        if ($svnLogOnline[$j]['paths'][$k]['action'] == 'A') {
                            $action = true;
                            $formatText .= '<span ';
                            $formatText .= 'style="color: #77CC77;" ';
                            $formatText .= '>';
                        }
                        elseif ($svnLogOnline[$j]['paths'][$k]['action'] == 'D') {
                            $action = true;
                            $formatText .= '<span ';
                            $formatText .= 'style="color: #CC7777;" ';
                            $formatText .= '>';
                        }
                        elseif ($svnLogOnline[$j]['paths'][$k]['action'] == 'M') {
                            $action = true;
                            $formatText .= '<span ';
                            $formatText .= 'style="color: #AA8877;" ';
                            $formatText .= '>';
                        }
                        $formatText .= '&nbsp;&nbsp;'.$svnLogOnline[$j]['paths'][$k]['action'];
                        $formatText .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ';
                        $formatText .= '|';
                        $formatText .= ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                        $formatText .= $svnLogOnline[$j]['paths'][$k]['path'];
                        if ($action) {
                            $formatText .= '</span>';
                        }
                        $formatText .= '<br />';
                    }
                    $formatText .= '<br />&nbsp;';
                $formatText .= '</fieldset>';
            $formatText .= '</blockquote>';
        }
        
        $formatText = str_replace("\n", ' ', $formatText);

        echo "{status : 0, svnLog : '" . addslashes($formatText) . "'}";
        exit ();
    } else {
        $formatText = '';
        
        $path = $_REQUEST['localVersion'] . DIRECTORY_SEPARATOR . '.svn' 
              . DIRECTORY_SEPARATOR . 'entries';
        if (file_exists($path)) {
            $entries = true;
            $fileLine = file($path);
            $svnReleaseLocal = $fileLine[10];
        }
        
        if (!$entries && !ini_get('safe_mode')) {
            $entries = true;
            exec('svn log '.$_REQUEST['localVersion'], $svnLogLocal);
            $svnReleaseLocalTemp = explode(' ', $svnLogLocal[1]);
            $svnReleaseLocal = substr($svnReleaseLocalTemp[0], 1);
        }
        
        if ($entries) {
            $formatText .= _RELEASE_NUMBER.' <b>'.$svnReleaseLocal.'</b><br /><br />';
        }
        
        if (!extension_loaded('svn')) {
            $formatText .= _INSTALL_SVN_EXTENSION;
        } else {
            $formatText .= _TO_GET_LOG_PLEASE_CONNECT;
        }
        $formatText = str_replace("\n", ' ', $formatText);
        
        echo "{status : 0, svnLog : '" . addslashes($formatText) . "'}";
        exit ();
    }
} else {
    echo "{status : 1}";
    exit ();
}


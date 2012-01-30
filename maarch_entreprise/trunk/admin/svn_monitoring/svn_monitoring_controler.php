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
* @brief Contains the svn_monitoring controler page
*
*
* @file
* @author Arnaud Veber
* @date $date$
* @version $Revision$
* @ingroup admin
*/

$core_tools = new core_tools();
$core_tools->test_admin('manage_svn', 'apps');
$core_tools->load_lang();

//$databaseVersion = _DATABASE_VERSION.' : '.'1.2.1';
$version = trim(substr(_MEP_VERSION, ((strpos(_MEP_VERSION, 'v')+1)-(strlen(_MEP_VERSION)))));
if ( preg_match("/trunk/i", $_SESSION['config']['coreurl'])) {
    $version = 'trunk';
}

$svnDirToCheck = array(
    0 => 'core',
    1 => 'apps'.DIRECTORY_SEPARATOR.'maarch_entreprise'
);
$modules_loaded = array_keys($_SESSION['modules_loaded']);
for ($i=0; $i<count($modules_loaded); $i++) {
    $svnDirToCheck[count($svnDirToCheck)] = 'modules'.DIRECTORY_SEPARATOR.$modules_loaded[$i];
}

for ($i=0; $i<count($svnDirToCheck); $i++) {
    ////////////////////////////////////////////////////////////////////////////
    $dirName[$i] = end(explode(DIRECTORY_SEPARATOR, $svnDirToCheck[$i]));
    ////////////////////////////////////////////////////////////////////////////
    $svnUrlRepo[$i]  = 'http://svn.maarch.org/';
    $svnUrlRepo[$i] .= $dirName[$i];
    $svnUrlRepo[$i] .= '/';
    
    if (!$version) {
        $svnUrlRepo[$i]  = 'url repository construction failed for "';
        $svnUrlRepo[$i] .= $dirName[$i];
        $svnUrlRepo[$i] .= '"';
    } else {
        if ($version == 'trunk') {
            $svnUrlRepo[$i] .= 'trunk/';
        } else {
            $svnUrlRepo[$i] .= 'branches/'.$version;
        }
    }
}
////////////////////////////////////////////////////////////////////////////////
$fromUpdate = false;
if (isset($_REQUEST['show']) && !empty($_REQUEST['show'])) {
    $fromUpdate = true;
    $divUpdated = $_REQUEST['show'];
    for ($i=0; $i<count($svnDirToCheck); $i++) {
        if ($_REQUEST['show'] == end(explode('/', $svnDirToCheck[$i]))) {
            $svnUrlRepoUpdate = $svnUrlRepo[$i];
            $svnDirToCheckUpdate = $svnDirToCheck[$i];
        }
    }
}
////////////////////////////////////////////////////////////////////////////////
//                      Titre de la page dans header                          //
////////////////////////////////////////////////////////////////////////////////
$formatText  = '<h1>';
    $formatText .= '<img ';
     $formatText .= 'src="'.$_SESSION['config']['businessappurl'].'static.php?filename=manage_svn.gif" ';
     $formatText .= 'width="32" ';
     $formatText .= 'height="32" ';
     $formatText .= 'alt="" ';
    $formatText .= '/>';
    $formatText .= '&nbsp;&nbsp;'._SVN_MONITORING;
$formatText .= '</h1>';
////////////////////////////////////////////////////////////////////////////////
$formatText  .= '&nbsp;<br />';
$formatText .= '<div class="block">';
$formatText .= '<img ';
$formatText .= 'src="'.$_SESSION['config']['businessappurl'].'static.php?filename=puce_next.gif" ';
$formatText .= 'width="0" ';
$formatText .= 'height="0" ';
$formatText .= 'alt="" ';
if ($fromUpdate) {
    $formatText .= 'onload="';
     $formatText .= 'new Effect.toggle(';
      $formatText .= '\'div_'.$_REQUEST['show'].'\''; //id div to toogle
      $formatText .= ', \'appear\'';
      $formatText .= ', {duration:0.8}'; //delay toggle
     $formatText .= ');';
     $formatText .= 'loadSvnLog(';
      $formatText .= '\''.$_SESSION['config']['businessappurl'].'index.php?page=load_svn_log&admin=svn_monitoring&display=true\'';
      $formatText .= ', \''.$svnUrlRepoUpdate.'\''; //url of the repo
      $formatText .= ', \''.$_SESSION['config']['corepath'].$svnDirToCheckUpdate.'\''; //path to the dir
      $formatText .= ', \''.$_REQUEST['show'].'\''; //name of the dir
     $formatText .= ');';
    $formatText .= '" ';
}
$formatText .= '/>';
$formatText .= '<h5 style="font-size: 13px;">';
    $formatText .= 'Database version : '.$_SESSION['maarch_entreprise']['database_version'].' ('.strtolower($_SESSION['config']['databasetype']).')';
    $formatText .= '<br />&nbsp;';
$formatText .= '</h5>';
for ($i=0; $i<count($svnDirToCheck); $i++) {
    ////////////////////////////////////////////////////////////////////////////
    //         on boucle sur les repos pour afficher les logs a onclick       //
    ////////////////////////////////////////////////////////////////////////////
    $formatText .= '<a name="'.$dirName[$i].'"></a>';
    $formatText .= '<h5 ';
     $formatText .= 'onclick="';
      $formatText .= 'new Effect.toggle(';
       $formatText .= '\'div_'.$dirName[$i].'\''; //id div to toogle
       $formatText .= ', \'blind\'';
       $formatText .= ', {delay:0.1}'; //delay toggle
      $formatText .= ');';
      $formatText .= 'loadSvnLog(';
       $formatText .= '\''.$_SESSION['config']['businessappurl'].'index.php?page=load_svn_log&admin=svn_monitoring&display=true\'';
       $formatText .= ', \''.$svnUrlRepo[$i].'\''; //url of the repo
       $formatText .= ', \''.$_SESSION['config']['corepath'].$svnDirToCheck[$i].'\''; //path to the dir
      $formatText .= ', \''.$dirName[$i].'\''; //name of the dir
     $formatText .= ');"';
     $formatText .= 'style="';
      $formatText .= 'font-size: 13px;';
     $formatText .= '" ';
    $formatText .= '>';
        $formatText .= '<img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=puce_next.gif" alt="" />';
        $formatText .= ' '.$dirName[$i].' (v. '.$version.') ';
    $formatText .= '</h5>';
    $formatText .= '<div>&nbsp;</div>'; // Space for divs
    $formatText .= '<div ';
     $formatText .= 'class="';
      $formatText .= 'block_light';
     $formatText .= '" ';
     $formatText .= 'id="div_'.$dirName[$i].'" ';
     $formatText .= 'style="display: none; height: 550px; overflow: scroll;"';
    $formatText .= '>';
        $formatText .= getRevisionNumber($_SESSION['config']['corepath'].$svnDirToCheck[$i]);
        $formatText .= _LOADING_INFORMATIONS;
    $formatText .= '</div>';
    $formatText .= '<div>&nbsp;</div>'; // Space for divs
}
$formatText .= '</div>';
////////////////////////////////////////////////////////////////////////////////
//                          fil d'ariane                                      //
////////////////////////////////////////////////////////////////////////////////
$init = false;
if (isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == 'true') {
    $init = true;
}
$level = '';
if (isset($_REQUEST['level']) 
    && ($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 
        || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)
) {
    $level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'] 
           . 'index.php?page=svn_monitoring_controller&admin=svn_monitoring';
$page_label = _SVN_MONITORING;
$page_id = 'svn_monitoring';
core_tools::manage_location_bar($page_path, $page_label, $page_id, $init,
    $level);
    
////////////////////////////////////////////////////////////////////////////////
//                              Affichage                                     //
////////////////////////////////////////////////////////////////////////////////
echo $formatText;

////////////////////////////////////////////////////////////////////////////////
//                function getRevisionNumber()                                //
////////////////////////////////////////////////////////////////////////////////

function getRevisionNumber($dir) {
    $path = $dir.'/.svn/entries';
    
    if (file_exists($path)) {
        $entries = true;
        $fileLine = file($path);
        $svnReleaseLocal = $fileLine[10];
    }
    
    return _RELEASE_NUMBER.' <b>'.$svnReleaseLocal.'</b><br /><br />';
}

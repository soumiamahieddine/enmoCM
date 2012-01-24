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
* @brief Contains the action controler page
*
*
* @file
* @author Arnaud Veber
* @date $date$
* @version $Revision$
* @ingroup admin
*/

$core_tools = new core_tools();
$core_tools->test_admin('svn_monitoring', 'apps');
$core_tools->load_lang();

$databaseVersion = _DATABASE_VERSION.' : '.'1.2.1';
$version = trim(substr(_MEP_VERSION, ((strpos(_MEP_VERSION, 'v')+1)-(strlen(_MEP_VERSION)))));

$svnDirToCheck = array(
    0 => 'core',
    1 => 'apps/maarch_entreprise'
);
$modules_loaded = array_keys($_SESSION['modules_loaded']);
for ($i=0; $i<count($modules_loaded); $i++) {
    $svnDirToCheck[count($svnDirToCheck)] = 'modules/'.$modules_loaded[$i];
}

for ($i=0; $i<count($svnDirToCheck); $i++) {
    ////////////////////////////////////////////////////////////////////////////
    $dirName[$i] = end(explode('/', $svnDirToCheck[$i]));
    ////////////////////////////////////////////////////////////////////////////
    $svnUrlRepo[$i]  = 'http://svn.maarch.org/';
    $svnUrlRepo[$i] .= $dirName[$i];
    $svnUrlRepo[$i] .= '/';
    
    if (!$version) {
        $svnUrlRepo[$i]  = 'url repository construction failed for "';
        $svnUrlRepo[$i] .= $dirName[$i];
        $svnUrlRepo[$i] .= '"';
    } else {
        $svnUrlRepo[$i] .= 'branches/'.$version;
    }
}

$formatText = '<div class="block">';
for ($i=0; $i<count($svnDirToCheck); $i++) {
    ////////////////////////////////////////////////////////////////////////////
    $formatText .= '<h5 ';
    $formatText .= 'onclick="new Effect.toggle(\'div_'.$dirName[$i].'\', \'blind\', {delay:0.1});';
    $formatText .= 'loadSvnLog(\''.$_SESSION['config']['businessappurl'].'index.php?page=load_svn_log&admin=svn_monitoring&display=true\', \''.$svnUrlRepo[$i].'\', \''.$_SESSION['config']['corepath'].$svnDirToCheck[$i].'\', \''.$dirName[$i].'\')"';
    $formatText .= 'style="font-size:normal;" ';
    $formatText .= '>';
        $formatText .= $dirName[$i].'('.$version.') ';
        $formatText .= '<a title="par '.$svnLogOnline[$i][0]['author'].'">';
            $formatText .= '<span style="font-weight: normal;">';
                $formatText .= 'r.'.$svnReleaseOnline[$i];
            $formatText .= '</span>';
        $formatText .= '</a>';
    $formatText .= '</h5>';
    ////////////////////////////////////////////////////////////////////////////
    $formatText .= '<div>&nbsp;</div>'; // Space for divs
    ////////////////////////////////////////////////////////////////////////////
    $formatText .= '<div class="block_light" ';
    $formatText .= 'id="div_'.$dirName[$i].'" ';
    $formatText .= 'style="display:none; height:550px; overflow: scroll;"';
    $formatText .= '>';
    $formatText .= '</div>';
    ////////////////////////////////////////////////////////////////////////////
    $formatText .= '<div>&nbsp;</div>'; // Space for divs
    ////////////////////////////////////////////////////////////////////////////
}
$formatText .= '</div>';

echo $formatText;

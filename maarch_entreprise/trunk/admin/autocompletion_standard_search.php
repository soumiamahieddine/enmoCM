<?php

/*
*   Copyright 2008-2012 Maarch
*
*   This file is part of Maarch Framework.
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
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief Contains the search ajax php
*
* @file
* @author Arnaud Veber
* @author Laurent Giovannoni
* @date $date$
* @version $Revision$
* @ingroup apps
*/

$rootDataObject = $DataObjectController->loadRootDataObject(
    $object,
    $what
);

$listArray = array();
while($line = $db->fetch_object()) {
    array_push($listArray, $line->tag);
}
echo "<ul>\n";
$authViewList = 0;
$flagAuthView = false;
foreach($listArray as $what) {
    if($authViewList >= 10) {
        $flagAuthView = true;
    }
    if(stripos($what, $_REQUEST['what']) === 0) {
        echo "<li>".$what."</li>\n";
        if($flagAuthView) {
            echo "<li>...</li>\n";
            break;
        }
        $authViewList++;
    }
}
echo "</ul>";

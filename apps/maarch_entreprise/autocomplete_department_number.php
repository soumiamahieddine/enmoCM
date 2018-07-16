<?php
/*
*    Copyright 2014 Maarch
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

/**
*
*
* @file
* @author <dev@maarch.org>
* @date $date$
* @version $Revision$
*/

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");

require_once("apps".DIRECTORY_SEPARATOR."maarch_entreprise".DIRECTORY_SEPARATOR."department_list.php");

$department = $_REQUEST['Input'];

echo "<ul>\n";

$authViewList = 0;
$flagAuthView = false;

foreach ($depts as $key => $value) {
	if (stripos($value, $department) !== false) {
	    if ($authViewList >= 10) {
	        $flagAuthView = true;
	    }
	    echo "<li id=".$key.">".$key." - ".$value."</li>\n";
	    if($flagAuthView) {
	        echo "<li id=".$key.">...</li>\n";
	        break;
	    }
	    $authViewList++;
	} else if ($key == $department) {
	    if ($authViewList >= 10) {
	        $flagAuthView = true;
	    }
	    echo "<li id=".$key.">".$key." - ".$value."</li>\n";
	    if($flagAuthView) {
	        echo "<li id=".$key.">...</li>\n";
	        break;
	    }
	    $authViewList++;		
	}
}

echo "</ul>";
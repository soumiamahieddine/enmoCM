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

$core_tools = new core_tools();
$core_tools->test_user();

$aJsGlobal = [];
$aJsGlobal['coreurl'] = $_SESSION['config']['coreurl'];
$aJsGlobal['businessappurl'] = $_SESSION['config']['businessappurl'];
$aJsGlobal['applicationName'] = $_SESSION['config']['applicationname'];

$aJsGlobal['profileView'] = 'Views/profile.component.html';
if(file_exists($_SESSION['config']['corepath'].'custom/'.$_SESSION['custom_override_id'].'/apps/maarch_entreprise/Views/profile.component.html')) {
    $aJsGlobal['profileView'] = '../../custom/'.$_SESSION['custom_override_id'].'/apps/maarch_entreprise/Views/profile.component.html';
}
$aJsGlobal['signatureBookView'] = 'Views/signature-book.component.html';
if(file_exists($_SESSION['config']['corepath'].'custom/'.$_SESSION['custom_override_id'].'/apps/maarch_entreprise/Views/signature-book.component.html')) {
    $aJsGlobal['signatureBookView'] = '../../custom/'.$_SESSION['custom_override_id'].'/apps/maarch_entreprise/Views/signature-book.component.html';
}


echo json_encode($aJsGlobal);

exit;

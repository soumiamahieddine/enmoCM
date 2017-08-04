<?php

/*
*
*    Copyright 2008,2015 Maarch
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
*
*   @author <dev@maarch.org>
*/

require_once 'core/class/class_core_tools.php';
$core_tools = new core_tools();
//$core_tools->load_html();
//$core_tools->load_header();
//$core_tools->load_js();

// sessions use for temporary backup
if (!isset($_REQUEST['transmissionNumber'])) {
    $_SESSION['attachmentInfo'] = array();
    $_SESSION['attachmentInfo']['title'] = $_REQUEST['titleAttachment'];
    $_SESSION['attachmentInfo']['chrono'] = $_REQUEST['chronoAttachment'];
    $_SESSION['attachmentInfo']['type'] = $_REQUEST['attachType'];
    $_SESSION['attachmentInfo']['contactId'] = $_REQUEST['contactId'];
    $_SESSION['attachmentInfo']['addressId'] = $_REQUEST['addressId'];
    $_SESSION['attachmentInfo']['back_date'] = $_REQUEST['back_date'];
}

if (isset($_REQUEST['attachType']) && $_REQUEST['attachType'] == 'outgoing_mail'){
	$objType = 'outgoingMail';
}
else {
    $objType = $_REQUEST['objectType'];
}

if (
    file_exists(
        $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
        . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . 'modules'
        . DIRECTORY_SEPARATOR . 'content_management' . DIRECTORY_SEPARATOR . 'applet_launcher.php'
    )
) {
    $path = 'custom/'. $_SESSION['custom_override_id'] .'/modules/content_management/applet_launcher.php';
} else {
    $path = 'modules/content_management/applet_launcher.php';
}
$_SESSION['upfileTransmissionNumber'] = (isset($_REQUEST['transmissionNumber']) ? $_REQUEST['transmissionNumber'] : null);
$uniqueId = (isset($_REQUEST['transmissionNumber']) ? $_REQUEST['transmissionNumber'] : null);

$content = '<style type="text/css">html{overflow:hidden}</style>'
    . '<body>'
        . '<div id="container">'
            . '<div id="content">'
                . '<div class="error" id="divError" name="divError"></div>'
                . '<script language="javascript">'
                    . 'loadApplet(\'' 
                        . $_SESSION['config']['coreurl'] . '' . $path
                        . '?objectType=attachment&objectId=' 
                        . $_REQUEST['objectId']
                        . '&objectType='
                        . $objType
                        . '&objectTable='
                        . $_REQUEST['objectTable']
                        . '&uniqueId='
                        . $uniqueId
                        . '&resMaster='
                        . $_REQUEST['resMaster']
                        . '&contactId='
                        . $_REQUEST['contactId']
                        . '&addressId='
                        . $_REQUEST['addressId']
                        . '&chronoAttachment='
                        . $_REQUEST['chronoAttachment']
                        . '&custom_override_id='
                        . $_SESSION['custom_override_id']
                        . '\');'
                . '</script>'
                . '<style type="text/css">#CMApplet{width: 100%;height: 100%;text-align: center;padding: 0px;margin: 0px;padding-top: 10px;}</style>'
            . '</div>'
        . '</div>'
    . '</body>';


function _parse($text) {
    $text = str_replace("\r\n", "\n", $text);
    $text = str_replace("\r", "\n", $text);
    $text = str_replace("\n", "\\n ", $text);
    return $text;
}

$status = 0;
$error = $_SESSION['error'];
$js = '';

echo "{status : " . $status . ", content : '" . addslashes(_parse($content)) . "', error : '" . addslashes($error) . "', exec_js : '".addslashes($js)."'}";
exit ();

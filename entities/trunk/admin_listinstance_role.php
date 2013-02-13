<?php
/*
*
*    Copyright 2008,2012 Maarch
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
*   @author  Cyril Vazquez <dev@maarch.org>
*/

$admin = new core_tools();
$admin->test_admin('admin_listinstance_roles', 'entities');
$_SESSION['m_admin']= array();
/****************Management of the location bar  ************/
$init = false;
if(isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == "true")
{
    $init = true;
}
$level = "";
if(isset($_REQUEST['level']) && ($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1))
{
    $level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=admin_listinstance_role&module=entities';
$page_label = _LISTINSTANCE_ROLE;
$page_id = "admin_listinstance_role";
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
#******************************************************************************

require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("modules/entities/entities_tables.php");

# Prepare view for UP/ADD
#******************************************************************************
$view = new DOMDocument();
$view->loadHTMLFile(
    __DIR__ . DIRECTORY_SEPARATOR . 'html' 
        . DIRECTORY_SEPARATOR . 'admin_listinstance_role.html' 
);
$xview = new DOMXPath($view);

# Set action mode
$mode = $view->getElementById("mode");
$mode->setAttribute('value', $_REQUEST['mode']);

# Translate
$labels = $view->getElementsByTagName('label');
for($i=0, $l=$labels->length; $i<$l; $i++) {
    $label = $labels->item($i);
    $const = $label->nodeValue;
    if($text = @constant($const))
        $label->nodeValue = $text;
}
$buttons = $xview->query('//input[@type="button"]');
for($i=0, $l=$buttons->length; $i<$l; $i++) {
    $button = $buttons->item($i);
    $value = $button->getAttribute('value');
    if($text = @constant($value))
        $button->setAttribute('value', $text);
}

# Manage local path
$cancel_btn = $view->getElementById("cancel");
$cancel_btn->setAttribute(
    'onclick',
    "goTo('index.php?module=entities&page=admin_listinstance_roles');"
);

# Get data for UP/DEL
#******************************************************************************
$request = new request();
$request->connect();
$request->query(
    "select * from " . ENT_LISTINSTANCE_ROLES
    . " where role_id = '".$_REQUEST['id']."' "
);
$role = $request->fetch_object();

# Switch on mode/action
#******************************************************************************
switch($_REQUEST['mode']) {
case 'add':
    echo $view->saveXML();
    break;
    
case 'up':
    # Role id
    $role_id = $view->getElementById("role_id");
    $role_id->setAttribute('value', $role->role_id);
    $role_id->setAttribute('readonly', 'true');
    $role_id->setAttribute('disabled', 'true');
    
    # Role Label
    $role_label = $view->getElementById("role_label");
    $role_label->setAttribute('value', $role->role_label);
   
    # List Label
    $list_label = $view->getElementById("list_label");
    $list_label->setAttribute('value', $role->list_label);
    
    # List Img
    $list_img = $view->getElementById("list_img");
    $list_img->setAttribute('value', $role->list_img);
    
    # Allow entities
    $allow_entities = $view->getElementById("allow_entities");
    if($role->allow_entities == 'Y') 
        $allow_entities->setAttribute('checked', 'true');
    if($role->allow_entities == 'N')
        $allow_entities->removeAttribute('checked');
    
    echo $view->saveXML();
    break;

case "del":
    $res = $request->query(
        "delete from " . ENT_LISTINSTANCE_ROLES 
            . " where role_id = '" . $role->role_id . "'"
    );
    echo "<script type='text/javascript'> goTo('".$_SESSION['config']['businessappurl']."index.php?page=admin_listinstance_roles&module=entities');</script>";
    break;
}

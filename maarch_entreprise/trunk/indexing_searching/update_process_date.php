<?php
/*
*   Copyright 2008-2014 Maarch
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
* @brief  compute the process limit with the doctype and the admission_date
*
* @file update_process_date.php
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup indexing_searching_mlb
*/
require_once('core/class/class_security.php');
require_once('apps/' . $_SESSION['config']['app_id'] . '/class/class_types.php');

$db = new dbquery();
$core = new core_tools();
$core->load_lang();
$type = new types();

if (!isset($_REQUEST['type_id']) || empty($_REQUEST['type_id'])) {
    echo "{status : 1, error_txt : '".addslashes(_DOCTYPE . ' ' . _IS_EMPTY)."'}";
    exit();
} else {
    $typeId = $_REQUEST['type_id'];
}

if (!isset($_REQUEST['admission_date']) || empty($_REQUEST['admission_date'])) {
    echo "{status : 1, error_txt : '".addslashes(_ADMISSION_DATE . ' ' . _IS_EMPTY)."'}";
    exit();
} else {
    $admissionDate = $_REQUEST['admission_date'];
}

//Process limit process date compute
//Bug fix if delay process is disabled in services
if ($core->service_is_enabled('param_mlb_doctypes')) {
    $db->connect();
    $db->query("select process_delay from " 
        . $_SESSION['tablename']['mlb_doctype_ext'] . " where type_id = " 
        . $typeId
    );
    $res = $db->fetch_object();
    $delay = $res->process_delay;
}

if (isset($delay) && $delay > 0) {
    require_once('core/class/class_alert_engine.php');
    $alert_engine = new alert_engine();
    if (isset($admissionDate) && !empty($admissionDate)) {
        $convertedDate = $alert_engine->dateFR2Time(str_replace("-", "/", $admissionDate));
        $date = $alert_engine->WhenOpenDay($convertedDate, $delay);
    } else {
        $date = $alert_engine->date_max_treatment($delay, false);
    }
    $process_date = $db->dateformat($date, '-');
    echo "{status : 0, process_date : '" . trim($process_date) . "'}";
    exit();
} else {
    echo "{status : 1}";
    exit();
}

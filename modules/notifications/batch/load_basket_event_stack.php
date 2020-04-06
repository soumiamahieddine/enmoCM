<?php

/*
 *   Copyright 2008-2011 Maarch
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
 *   along with Maarch Framework. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @brief process the event stack
 *
 * @file
 * @author  Cyril Vazquez  <dev@maarch.org>
 * @date $date$
 * @version $Revision$
 * @ingroup notification
 */

/**
* @brief  Class to include the file error
*
*/
class IncludeFileError extends Exception
{
    public function __construct($file)
    {
        $this->file = $file;
        parent :: __construct('Include File \'$file\' is missing!', 1);
    }
}

// Globals variables definition
$GLOBALS['batchName'] = 'basket_event_stack';
$GLOBALS['wb'] = '';
$totalProcessedResources = 0;
$batchDirectory = '';
$log4PhpEnabled = false;

// Load tools
include('batch_tools.php');

$options = getopt("c:n:", ["config:", "notif:"]);
if (empty($options['c']) && empty($options['config'])) {
    print("Configuration file missing\n");
    exit(101);
} elseif (!empty($options['c']) && empty($options['config'])) {
    $options['config'] = $options['c'];
    unset($options['c']);
}
if (empty($options['n']) && empty($options['notif'])) {
    print("Notification id missing\n");
    exit(102);
} elseif (!empty($options['n']) && empty($options['notif'])) {
    $options['notif'] = $options['n'];
    unset($options['n']);
}

$txt = '';
foreach (array_keys($options) as $key) {
    if (isset($options[$key]) && $options[$key] == false) {
        $txt .= $key . '=false,';
    } else {
        $txt .= $key . '=' . $options[$key] . ',';
    }
}
print($txt . "\n");
$GLOBALS['configFile'] = $options['config'];
$notificationId = $options['notif'];

print("Load xml config file:" . $GLOBALS['configFile'] . "\n");

// Tests existence of config file
if (!file_exists($GLOBALS['configFile'])) {
    print(
        "Configuration file " . $GLOBALS['configFile']
        . " does not exist\n"
    );
    exit(102);
}
// Loading config file
print("Load xml config file:" . $GLOBALS['configFile'] . "\n");
$xmlconfig = simplexml_load_file($GLOBALS['configFile']);

if ($xmlconfig == false) {
    print("Error on loading config file:" . $GLOBALS['configFile'] . "\n");
    exit(103);
}

// Load config
$config          = $xmlconfig->CONFIG;
$lang            = (string)$config->Lang;
$maarchDirectory = (string)$config->MaarchDirectory;
$customID        = (string)$config->customID;
$customIDPath    = '';

if ($customID <> '') {
    $_SESSION['config']['corepath'] = $maarchDirectory;
    $_SESSION['custom_override_id'] = $customID;
    $customIDPath = $customID . '_';
}
chdir($maarchDirectory);
$maarchUrl  = (string)$config->MaarchUrl;
$maarchApps = (string) $config->MaarchApps;

$_SESSION['config']['app_id'] = $maarchApps;
$_SESSION['modules_loaded'] = array();

$_SESSION['config']['tmppath'] = (string)$config->TmpDirectory;
if (!is_dir($_SESSION['config']['tmppath'])) {
    mkdir($_SESSION['config']['tmppath'], 0777);
}

$GLOBALS['batchDirectory'] = $maarchDirectory . 'modules'
                           . DIRECTORY_SEPARATOR . 'notifications'
                           . DIRECTORY_SEPARATOR . 'batch';

set_include_path(get_include_path() . PATH_SEPARATOR . $maarchDirectory);

$mailerParams = $xmlconfig->MAILER;

// INCLUDES
try {
    Bt_myInclude('vendor/autoload.php');

    // // Notifications
    // Bt_myInclude(
    //     "modules" . DIRECTORY_SEPARATOR . "notifications"
    //     . DIRECTORY_SEPARATOR . "notifications_tables_definition.php"
    // );
    // Bt_myInclude(
    //     "modules" . DIRECTORY_SEPARATOR . "notifications"
    //     . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "events_controler.php"
    // );
    // // Templates
    // Bt_myInclude(
    //     'modules' . DIRECTORY_SEPARATOR . 'templates'
    //     . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'templates_controler.php'
    // );
} catch (IncludeFileError $e) {
    Bt_writeLog(['level' => 'ERROR', 'message' => 'Problem with the php include path:' .$e .' '. get_include_path()]);
    exit();
}

// $events_controler         = new events_controler();
// $templates_controler      = new templates_controler();

\SrcCore\models\DatabasePDO::reset();
new \SrcCore\models\DatabasePDO(['customId' => $_SESSION['custom_override_id']]);

$databasetype = (string)$xmlconfig->CONFIG_BASE->databasetype;

// Collection for res
$collparams = $xmlconfig->COLLECTION;
$coll_id    = $collparams->Id;
$coll_table = $collparams->Table;
$coll_view  = $collparams->View;

$GLOBALS['errorLckFile'] = $GLOBALS['batchDirectory'] . DIRECTORY_SEPARATOR
                         . $customIDPath . $GLOBALS['batchName'] . '_error.lck';
$GLOBALS['lckFile'] = $GLOBALS['batchDirectory'] . DIRECTORY_SEPARATOR
                    . $customIDPath . $GLOBALS['batchName'] . '.lck';
                    
if (file_exists($GLOBALS['errorLckFile'])) {
    $logger->write(
        'Error persists, please solve this before launching a new batch',
        'ERROR',
        13
    );
    exit(13);
}

Bt_getWorkBatch();

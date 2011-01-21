<?php 

class IncludeFileError extends Exception {
	public function __construct($file) {
		$this->file = $file;
		parent :: __construct("Include File \"$file\" is missing!", 1);
	}
}

function MyInclude($file) {
	if (file_exists($file)) {
		include_once ($file);
	} else {
		throw new IncludeFileError($file);
	}
}

try {
	include("Maarch_CLITools/ArgsParser.php");
	include("Maarch_CLITools/Logger.php");
	include("Maarch_CLITools/FileHandler.php");
	include("Maarch_CLITools/ConsoleHandler.php");
} catch (IncludeFileError $e) {
	echo "Maarch_CLITools required ! \n (pear.maarch.org)\n";
	exit(6);
}

// Globals variables definition
$state = '';
$configFile = '';
$MaarchDirectory = '';
$TmpDirectory = '';
$customPath = '';
$table = '';
$adrTable = '';
$view = '';
$coll = '';
$policy = '';
$cycle = '';
$steps = Array();
$currentStep = "";
$docservers = Array();
$docserverSourcePath = "";
$docserverSourceFingerprint = "";
$databasetype = '';
$exitCode = 0;
$running_date = date('Y-m-d H:i:s');
$func = "";
$db = "";
$db2 = "";
$db3 = "";
$docserverControler = "";
$wb = "";
$docserversFeatures = array();

function do_query($db_conn, $query_txt, $transaction = false) {
	$res = $db_conn->query($query_txt, true);
	if (!$res) {
		if ($transaction) {
			$GLOBALS['logger']->write('ROLLBACK', 'INFO');
			$db_conn->query('ROLLBACK', true);
		}
		$GLOBALS['logger']->write('SQL Query error:' . $query_txt, 'ERROR', 4);
		exit(4);
	}
	$GLOBALS['logger']->write('SQL query:' . $query_txt, 'DEBUG');
	return true;
}

/**
* Get the batch id
*/
function getWorkBatch() {
	$req = "select param_value_int from parameters where id = 'lc_process_stack_id'";
	$GLOBALS['db']->query($req);
	while($reqResult = $GLOBALS['db']->fetch_array()) {
		$GLOBALS['wb'] = $reqResult[0] + 1;
	}
	if($GLOBALS['wb'] == "") {
		$req = "insert into parameters(id, param_value_int) values ('lc_process_stack_id', 1)";
		$GLOBALS['db']->query($req);
		$GLOBALS['wb'] = 1;
	}
}

/**
* Update the database with the new batch id
*/
function updateWorkBatch() {
	$req = "update parameters set param_value_int  = ".$GLOBALS['wb']." where id = 'lc_process_stack_id'";
	$GLOBALS['db']->query($req);
}

// Defines scripts arguments
$argsparser = new ArgsParser();
$argsparser->add_arg("config", array (
	'short' => "c",
	'long' => "config",
	'mandatory' => true,
	'help' => "Config file path is mandatory."
));
$argsparser->add_arg("collection", array (
	'short' => "coll",
	'long' => "collection",
	'mandatory' => true,
	'help' => "Collection target is mandatory."
));
$argsparser->add_arg("policy", array (
	'short' => "p",
	'long' => "policy",
	'mandatory' => true,
	'help' => "Policy is mandatory."
));
$argsparser->add_arg("cycle", array (
	'short' => "cy",
	'long' => "cycle",
	'mandatory' => true,
	'help' => "Cycle is mandatory."
));
// Log management
$GLOBALS['logger'] = new Logger();
$GLOBALS['logger']->set_threshold_level('DEBUG');
$console = new ConsoleHandler();
$GLOBALS['logger']->add_handler($console);
$file = new FileHandler("logs/log.txt");
$GLOBALS['logger']->add_handler($file);
$GLOBALS['logger']->write("STATE:INIT", 'INFO');
// Parsing script options
try {
	$options = $argsparser->parse_args($GLOBALS["argv"]);
	// If option = help then options = false and the script continues ...
	if ($options == false) {
		exit(0);
	}
} catch (MissingArgumentError $e) {
	if ($e->arg_name == "config") {
		$GLOBALS['logger']->write('Configuration file missing', 'ERROR', 1);
		exit(1);
	}
	if ($e->arg_name == "collection") {
		$GLOBALS['logger']->write('Collection missing', 'ERROR', 1);
		exit(1);
	}
	if ($e->arg_name == "policy") {
		$GLOBALS['logger']->write('Policy missing', 'ERROR', 1);
		exit(1);
	}
	if ($e->arg_name == "cycle") {
		$GLOBALS['logger']->write('Cycle missing', 'ERROR', 1);
		exit(1);
	}
}
$txt = "";
foreach (array_keys($options) as $key) {
	if (isset($options[$key]) && $options[$key] == false) {
		$txt .= $key . '=false,';
	} else {
		$txt .= $key . '=' . $options[$key] . ',';
	}
}
$GLOBALS['logger']->write($txt, 'DEBUG');
$GLOBALS['configFile'] = $options['config'];
$GLOBALS['collection'] = $options['collection'];
$GLOBALS['policy'] = $options['policy'];
$GLOBALS['cycle'] = $options['cycle'];
$GLOBALS['logger']->write($txt, 'INFO');
// Tests existence of config file
if (!file_exists($GLOBALS['configFile'])) {
	$GLOBALS['logger']->write('Configuration file ' . $GLOBALS['configFile'] . ' does not exist', 'ERROR', 3);
	exit(3);
}
// Loading config file
$GLOBALS['logger']->write('Load xml config file:' . $GLOBALS['configFile'], 'INFO');
$xmlconfig = simplexml_load_file($GLOBALS['configFile']);
if ($xmlconfig == FALSE) {
	$GLOBALS['logger']->write('Error on loading config file:' . $GLOBALS['configFile'], 'ERROR', 5);
	exit(5);
}
$CONFIG = $xmlconfig->CONFIG;
$lang = (string) $CONFIG->Lang;
$GLOBALS['MaarchDirectory'] = (string) $CONFIG->MaarchDirectory;
$GLOBALS['TmpDirectory'] = (string) $CONFIG->TmpDirectory;
$GLOBALS['docserversFeatures']['DOCSERVERS']['PATHTOCOMPRESSTOOL'] = (string) $CONFIG->PathToCompressTool;
$MaarchApps = (string) $CONFIG->MaarchApps;
$log_level = (string) $CONFIG->LogLevel;
$DisplayedLogLevel = (string) $CONFIG->DisplayedLogLevel;
$GLOBALS['customPath'] = (string) $CONFIG->CustomPath;
$GLOBALS['databasetype'] = (string) $xmlconfig->CONFIG_BASE->DatabaseType;
foreach ($xmlconfig->COLLECTION as $col) {
	$GLOBALS['collections'][$i] = array("id" => (string) $col->Id, "table" => (string) $col->Table, "view" => (string) $col->View, "adr" => (string) $col->Adr);
	if($GLOBALS['collections'][$i]['id'] == $GLOBALS['collection']) {
		$GLOBALS['table'] = $GLOBALS['collections'][$i]['table'];
		$GLOBALS['adrTable'] = $GLOBALS['collections'][$i]['adr'];
		$GLOBALS['view'] = $GLOBALS['collections'][$i]['view'];
	}
	$i++;
}
if ($GLOBALS['table'] == "" || $GLOBALS['adrTable'] == "" || $GLOBALS['view'] == "") {
	$GLOBALS['logger']->write('Collection:' . $GLOBALS['collection'].' unknow', 'ERROR', 16);
	exit(16);
}
if (file_exists($GLOBALS['MaarchDirectory'] . "modules" .DIRECTORY_SEPARATOR . "life_cycle" .DIRECTORY_SEPARATOR . "lang" . DIRECTORY_SEPARATOR . $lang . ".php")) {
	include($GLOBALS['MaarchDirectory'] . "modules" .DIRECTORY_SEPARATOR . "life_cycle" .DIRECTORY_SEPARATOR . "lang" . DIRECTORY_SEPARATOR . $lang . ".php");
}
if ($log_level == 'DEBUG') {
	error_reporting(E_ALL);
}
$GLOBALS['logger']->change_handler_log_level($file, $log_level);
$GLOBALS['logger']->change_handler_log_level($console, $DisplayedLogLevel);
unset($xmlconfig);
set_include_path(get_include_path() . PATH_SEPARATOR . $GLOBALS['MaarchDirectory']);
try {
	MyInclude($GLOBALS['MaarchDirectory'] . "core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_functions.php");
	MyInclude($GLOBALS['MaarchDirectory'] . "core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_db.php");
	MyInclude($GLOBALS['MaarchDirectory'] . "core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_request.php");
	MyInclude($GLOBALS['MaarchDirectory'] . "core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_core_tools.php");
	MyInclude($GLOBALS['MaarchDirectory'] . "core" . DIRECTORY_SEPARATOR . "core_tables.php");
	MyInclude($GLOBALS['MaarchDirectory'] . "modules" .DIRECTORY_SEPARATOR . "life_cycle" .DIRECTORY_SEPARATOR . "life_cycle_tables_definition.php");
	MyInclude($GLOBALS['MaarchDirectory'] . "core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "docservers_controler.php");
	MyInclude($GLOBALS['MaarchDirectory'] . "core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "docserver_types_controler.php");
} catch (IncludeFileError $e) {
	$GLOBALS['logger']->write('Problem with the php include path:' . get_include_path(), 'ERROR', 14);
	exit(14);
}
if (!is_dir($GLOBALS['TmpDirectory'])) {
	$GLOBALS['logger']->write('Problem with the tmp dir:' . $GLOBALS['TmpDirectory'], 'ERROR', 17);
	exit(14);
}
core_tools::load_lang($lang, $GLOBALS['MaarchDirectory'], $MaarchApps);
$GLOBALS['func'] = new functions();
$GLOBALS['db'] = new dbquery($GLOBALS['configFile']);
$GLOBALS['db2'] = new dbquery($GLOBALS['configFile']);
$GLOBALS['db3'] = new dbquery($GLOBALS['configFile']);
$GLOBALS['db']->connect();
$GLOBALS['db2']->connect();
$GLOBALS['db3']->connect();
$GLOBALS['docserverControler'] = new docservers_controler();
getWorkBatch();

?>

<?php
//error mode and function
error_reporting(E_ERROR);
set_error_handler(errorHandler);
// global vars of the program
/**
* Name of the config (usefull for multi instance)
*/
$_ENV['config_name'] = "";
/**
* Path to the log file
*/
$_ENV['log'] = "";
/**
* User exit of the program, contains 1 if any problem appears
*/
$_ENV['ErrorLevel'] = 0;
/**
* Connection object to database 1
*/
$_ENV['db'] = "";
/**
* Connection object to database 2
*/
$_ENV['db2'] = "";

// Class to manage files includes errors
class IncludeFileError extends Exception
{
    public function __construct($file)
    {
        $this->file = $file;
        parent::__construct("Include File \"$file\" is missing!", 1);
    }
}

/**
* Creation of the log file
*/
function loginCreation()
{
    if (
        !is_dir(dirname($_SERVER['PHP_SELF']) . DIRECTORY_SEPARATOR
            . 'log' . DIRECTORY_SEPARATOR)
    ) {
        mkdir(dirname($_SERVER['PHP_SELF']) . DIRECTORY_SEPARATOR . 'log'
            . DIRECTORY_SEPARATOR,
            0777
        );
    }
    $folderLogName = dirname($_SERVER['PHP_SELF']) . DIRECTORY_SEPARATOR . 'log'
        . DIRECTORY_SEPARATOR;
    if (isset($_ENV['config_name']) && $_ENV['config_name'] <> '') {
        $_ENV['log'] = $folderLogName . $_ENV['config_name'] . '_thumbnail_'
            . date('Y') . '_' . date('m') . '_' . date('d') . '.log';
    } else {
        $_ENV['log'] = $folderLogName . 'full_text_' . date('Y') . '_' . date('m')
            . '_' . date('d') . '.log';
    }
    writeLog('Application start with : ' . $_SERVER['SCRIPT_FILENAME']);
}

/**
* Write on the log file
* @param  $eventInfo string text which is written in the log file
*/
function writeLog($EventInfo)
{
    $logFileOpened = fopen($_ENV['log'], 'a');
    fwrite($logFileOpened, '[' . date('d') . '/' . date('m') . '/' . date('Y')
        . ' ' . date('H') . ':' . date('i') . ':' . date('s') . '] ' . $EventInfo
        . "\r\n"
    );
    fclose($logFileOpened);
}

function MyInclude($file)
{
    if (file_exists($file)) {
        include_once($file);
    } else {
        throw new IncludeFileError($file);
    }
}

function r_mkdir($path, $mode = 0777, $recursive = true) {
	if(empty($path))
		return false;
	 
	if($recursive) {
		$toDo = substr($path, 0, strrpos($path, DIRECTORY_SEPARATOR));
		if($toDo !== '.' && $toDo !== '..')
			r_mkdir($toDo, $mode);
	}
	 
	if(!is_dir($path))
		mkdir($path, $mode);
	 
		return true;
}

/**
* Managing of errors
* @param  $errno integer number of the error
* @param  $errstr string text of the error
* @param  $errfile string file concerned with the error
* @param  $errline integer line of the error
* @param  $errcontext string context of the error
*/
function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
{
    writeLog('[ERROR] from  line ' . $errline . ' : ' . $errstr . ' [ERROR]');
    $_ENV['ErrorLevel'] = 1;
}

// Begin
date_default_timezone_set('Europe/Paris');
if ($argc != 2) {
    echo "You must specify the configuration file." . $argc;
    exit;
}
$conf = $argv[1];
$xmlconfig = simplexml_load_file($conf);
foreach ($xmlconfig->CONFIG as $CONFIG) {
    $_ENV['config_name'] = $CONFIG->CONFIG_NAME;
    $_ENV['databaseserver'] = $CONFIG->LOCATION;
    $_ENV['databaseport'] = $CONFIG->DATABASE_PORT;
    $_ENV['database'] = $CONFIG ->DATABASE;
    $_ENV['databasetype'] = $CONFIG->DATABASETYPE;
    $_ENV['databaseuser'] = $CONFIG -> USER_NAME;
    $_ENV['databasepwd'] = $CONFIG->PASSWORD;
    $_ENV['tablename'] = $CONFIG->TABLE_NAME;
    $_ENV['collection'] = $CONFIG->COLLECTION;
    /*$path_column_name = $CONFIG->PATH_COLUMN_NAME;
    $filename_column_name = $CONFIG->FILENAME_COLUMN_NAME;*/
    //$_ENV['input_ds'] = $CONFIG->INPUT_DOCSERVER;
   // $_ENV['output_ds'] = $CONFIG->OUTPUT_DOCSERVER;
	$_ENV['max_batch_size'] = $CONFIG->MAX_BATCH_SIZE;
	$maarchDirectory = (string) $CONFIG->MaarchDirectory;
	$_ENV['core_path'] = $maarchDirectory . 'core' . DIRECTORY_SEPARATOR;
}
if (DIRECTORY_SEPARATOR == "/") {
    $_ENV['osname'] = "UNIX";
} else {
    $_ENV['osname'] = "WINDOWS";
}
loginCreation();

writeLog("Launch of process of thumbnails conversion");
writeLog("Loading the xml config file");
writeLog("Config name : " . $_ENV['config_name']);
writeLog("Conversion launched for table : " . $_ENV['tablename']);

/*set_include_path(get_include_path() . PATH_SEPARATOR . $maarchDirectory);
try {
	MyInclude(
		$maarchDirectory . 'core' . DIRECTORY_SEPARATOR . 'class'
		. DIRECTORY_SEPARATOR . 'class_functions.php'
	);
	MyInclude(
		$maarchDirectory . 'core' . DIRECTORY_SEPARATOR . 'class'
		. DIRECTORY_SEPARATOR . 'class_db.php'
	);
	
	
} catch(IncludeFileError $e) {
	writeLog(
		'Problem with the php include path : ' . get_include_path()
	);
	exit();
}
	*/
require('class_db.php');
	
	
	
$_ENV['db'] = new dbquery();
$_ENV['db']->connect();
$_ENV['db2'] = new dbquery();
$_ENV['db2']->connect();
writeLog("connection on the DB server OK !");


$query = "select priority_number, docserver_id from docservers where is_readonly = 'N' and "
	   . " enabled = 'Y' and coll_id = '".$_ENV['collection']."' and docserver_type_id = 'TNL' order by priority_number";
	   
$_ENV['db']->query($query);
$docserverId = $_ENV['db']->fetch_object()->docserver_id;

writeLog($query);
writeLog($docserverId);
$docServers = "select docserver_id, path_template from docservers";
$_ENV['db']->query($docServers);
writeLog("docServers found : ");
while ($queryResult=$_ENV['db']->fetch_array()) {
  $pathToDocServer[$queryResult[0]] = $queryResult[1];
  writeLog($queryResult[0]. '-' .$queryResult[1]);
}

if (is_dir($pathToDocServer[(string)$docserverId])){
	$pathOutput = $pathToDocServer[(string)$docserverId];
	writeLog("path of output docserver : ".$pathOutput);
}
else {
	writeLog("output docserver unknown ! : ".$docserverId);
	exit();
}
$cpt_batch_size=0;

$queryMakeThumbnails = "select res_id, docserver_id, path, filename, format from "
    . $_ENV['tablename'] . " where tnl_filename = '' or tnl_filename is null ";
writeLog("query to found document with no thumbnail : ".$queryMakeThumbnails);
$_ENV['db']->query($queryMakeThumbnails);
while ($queryResult=$_ENV['db']->fetch_array()) {
	if ($_ENV['max_batch_size'] >= $cpt_batch_size) {
			$pathToFile = $pathToDocServer[$queryResult['docserver_id']] . str_replace("#", DIRECTORY_SEPARATOR, $queryResult['path'])
            . $queryResult['filename'];
			$outputPathFile  = $pathOutput . str_replace("#", DIRECTORY_SEPARATOR, $queryResult['path']) . str_replace(pathinfo($pathToFile, PATHINFO_EXTENSION), "png",$queryResult['filename']);
			
			
			writeLog("processing of document : " . $pathToFile . " | res_id : "
            . $queryResult['res_id']);
			echo "processing of document : " . $pathToFile . " \r\n res_id : "
            . $queryResult['res_id'] . "\n";
			
			
			
			$racineOut = $pathOutput . str_replace("#", DIRECTORY_SEPARATOR, $queryResult['path']);
			if (!is_dir($racineOut)){
				r_mkdir($racineOut,0777);
				writeLog("Create $racineOut directory ");
			}
			
			exec("convert -thumbnail x300 -background white -alpha remove ".$pathToFile."[0] $outputPathFile");
			$_ENV['db2']->query("UPDATE ".$_ENV['tablename']." SET tnl_path = '".$queryResult['path']."', tnl_filename = '".str_replace(pathinfo($pathToFile, PATHINFO_EXTENSION), "png",$queryResult['filename'])."' WHERE res_id = ".$queryResult['res_id']);
		
	} else {
    writeLog("Max batch size ! Stop processing !");
    echo "\r\nMax batch size ! Stop processing !";
    break;
  }
  $cpt_batch_size++;
}
writeLog("End of application !");
exit();
?>
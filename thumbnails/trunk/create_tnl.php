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
   
    $_ENV['tablename'] = $CONFIG->TABLE_NAME;
    $_ENV['collection'] = $CONFIG->COLLECTION;
	
	$_ENV['max_batch_size'] = $CONFIG->MAX_BATCH_SIZE;
	$maarchDirectory = (string) $CONFIG->MaarchDirectory;
	$_ENV['core_path'] = $maarchDirectory . 'core' . DIRECTORY_SEPARATOR;
}
$_ENV['databasetype'] = $xmlconfig->CONFIG_BASE->databasetype;
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

require($_ENV['core_path']."class/class_functions.php");
require($_ENV['core_path']."class/class_db_pdo.php");
	
$_ENV['db'] = new Database($conf);

$query = "select priority_number, docserver_id from docservers where is_readonly = 'N' and "
	   . " enabled = 'Y' and coll_id = ? and docserver_type_id = 'TNL' order by priority_number";
	   
$stmt1 = $_ENV['db']->query($query, array($_ENV['collection']));
$docserverId = $stmt1->fetchObject()->docserver_id;

writeLog($query);
writeLog($docserverId);
$docServers = "select docserver_id, path_template from docservers";
$stmt1 = $_ENV['db']->query($docServers);
writeLog("docServers found : ");

while ($queryResult = $stmt1->fetchObject()) {
  $pathToDocServer[$queryResult->docserver_id] = $queryResult->path_template;
  writeLog($queryResult->docserver_id. '-' .$queryResult->path_template);
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
$stmt1 = $_ENV['db']->query($queryMakeThumbnails);
while ($queryResult=$stmt1->fetchObject()) {
	if ($_ENV['max_batch_size'] >= $cpt_batch_size) {
			$pathToFile = $pathToDocServer[$queryResult->docserver_id] . str_replace("#", DIRECTORY_SEPARATOR, $queryResult->path)
            . $queryResult->filename;
			$outputPathFile  = $pathOutput . str_replace("#", DIRECTORY_SEPARATOR, $queryResult->path) . str_replace(pathinfo($pathToFile, PATHINFO_EXTENSION), "png",$queryResult->filename);
			
			
			writeLog("processing of document : " . $pathToFile . " | res_id : "
            . $queryResult->res_id);
			echo "processing of document : " . $pathToFile . " \r\n res_id : "
            . $queryResult->res_id . "\n";
			
			
			
			$racineOut = $pathOutput . str_replace("#", DIRECTORY_SEPARATOR, $queryResult->path);
			if (!is_dir($racineOut)){
				r_mkdir($racineOut,0777);
				writeLog("Create $racineOut directory ");
			}
			
			exec("convert -thumbnail x300 -background white -alpha remove ".$pathToFile."[0] $outputPathFile");
			$stmt2 = $_ENV['db']->query("UPDATE ".$_ENV['tablename']." SET tnl_path = ?, tnl_filename = ? WHERE res_id = ?", array($queryResult->path, str_replace(pathinfo($pathToFile, PATHINFO_EXTENSION), "png",$queryResult->filename), $queryResult->res_id));
			
		
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
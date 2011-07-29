<?php 
/*
*    Copyright 2008,2009 Maarch    
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
* @defgroup full_text Full-text Module
*/

/**
* Full-text is a Maarch module which allows you to make full text indexing with the Lucene engine.<br>
* We use PHP version of Lucene integrated into the ZEND framework.<br>
* This Maarch module proposes a batch allowing the full text indexing.<br>
* This batch is launched for each collection of Maarch and works on Linux or Windows OS.<br>
* It course a resources table and brings out documents candidates for full text.<br><br>
* A user exit code is stored in fulltext_result column of the document in "res_x" :
* <ul>
*   <li>1 : Full Text extraction successfull</li>
* <li>-1 : No file found</li>
* <li>-2 : File extension not allowed for lucene</li>
* <li>2 : no result for this extraction</li>
* </ul>
* @file
* @author Mathieu Donzel <mathieu.donzel@sages-informatique.com>
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup full_text
* @brief Extraction of information on PDF with lucene functions of Zend Framework
*/

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

/**
* Creation of the log file
*/
function loginCreation()
{
  if(!is_dir(dirname($_SERVER["PHP_SELF"]).DIRECTORY_SEPARATOR."log".DIRECTORY_SEPARATOR))
  {
      mkdir(dirname($_SERVER["PHP_SELF"]).DIRECTORY_SEPARATOR."log".DIRECTORY_SEPARATOR."",0777);
  }
  $folderLogName = dirname($_SERVER["PHP_SELF"]).DIRECTORY_SEPARATOR."log".DIRECTORY_SEPARATOR."";
  //$_ENV['log'] = $folderLogName."full_text_".date("Y")."_".date("m")."_".date("d")." ".date("H")."-".date("i")."-".date("s").".log";
  if (isset($_ENV['config_name']) && $_ENV['config_name'] <> '') {
      $_ENV['log'] = $folderLogName . $_ENV['config_name'] . "_full_text_" . date("Y") . "_" . date("m") . "_" . date("d") . ".log";
  } else {
      $_ENV['log'] = $folderLogName . "full_text_" . date("Y") . "_" . date("m") . "_" . date("d") . ".log";
  }
  writeLog("Application start with : ".$_SERVER['SCRIPT_FILENAME']);
}

/**
* Write on the log file
* @param  $eventInfo string text which is written in the log file
*/
function writeLog($EventInfo)
{
  $logFileOpened = fopen($_ENV['log'], "a");
  fwrite($logFileOpened, "[".date("d")."/".date("m")."/".date("Y")." ".date("H").":".date("i").":".date("s")."] ".$EventInfo."\r\n"); 
  fclose($logFileOpened);
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
    writeLog("[ERROR] from  line ".$errline." : ". $errstr." [ERROR]");
    $_ENV['ErrorLevel'] = 1;
}

/**
* Check if a folder is empty
* @param  $dir string path of the directory to chek
* @return boolean true if the directory exists
*/
function isDirEmpty($dir)
{
  $dir = opendir($dir);
  $isEmpty = true;
  while(($entry = readdir($dir)) !== false)
  {
    if($entry !== '.' && $entry !== '..')
    {
      $isEmpty = false;
      break;
    }
  }
  closedir($dir);
  return $isEmpty;
}

/**
* Launch the lucene engine if it's a pdf file
* @param  $pathToFile string path of the file to index
* @param  $indexFileDirectory string directory of the lucene index
* @param  $format string format of the document to index
* @param  $id integer id of the document to index
* @return integer  user exit code stored in fulltext_result column of the document in "res_x"
*/
Function indexFullText($pathToFile, $indexFileDirectory, $format, $Id)
{
  $result = -1;
  if (is_file($pathToFile))
  {
    switch (strtoupper($format))
    {
      case "PDF":
        writeLog("it's a PDF file");
        $result = indexFullTextPdf($pathToFile, $indexFileDirectory, $Id);
        break;
      default:
        $result = -2;
    }
  }
  return $result;
}

/**
* Retrieve the text of a pdftext and launch the lucene engine 
* @param  $pathToFile string path of the file to index
* @param  $indexFileDirectory string directory of the lucene index
* @param  $id integer id of the document to index
* @return integer user exit code is stored in fulltext_result column of the document in "res_x"
*/
Function indexFullTextPdf($pathToFile, $indexFileDirectory, $Id)
{
  $result = -1;
  if(is_file($pathToFile))
  {
    $tmpFile = $_ENV["base_directory"].DIRECTORY_SEPARATOR."tmp".DIRECTORY_SEPARATOR.basename($pathToFile).".ftx";
    //$pathToFile = str_replace("\\\\", "\\", $pathToFile);
    if($_ENV['osname'] == "WINDOWS")
    {
      //$resultExtraction = exec("\""."\"".$_ENV['maarch_tools_path']."pdftotext".DIRECTORY_SEPARATOR.$_ENV['pdftotext']."\" \"".$pathToFile."\" \"".$tmpFile."\""."\"");
      $resultExtraction = exec("\"".$_ENV['maarch_tools_path']."pdftotext".DIRECTORY_SEPARATOR.$_ENV['pdftotext']."\" \"".$pathToFile."\" \"".$tmpFile."\"");
      writeLog("\""."\"".$_ENV['maarch_tools_path']."pdftotext".DIRECTORY_SEPARATOR.$_ENV['pdftotext']."\" \"".$pathToFile."\" \"".$tmpFile."\""."\"");
    }
    elseif($_ENV['osname'] == "UNIX")
    {
      $resultExtraction = exec("pdftotext \"".$pathToFile."\" \"".$tmpFile."\"");
      writeLog("pdftotext \"".$pathToFile."\" \"".$tmpFile."\"");
    }
    $fileContent = trim(readFileF($tmpFile));
    if(is_file($tmpFile)) unlink($tmpFile);
    if(strlen($fileContent) > 50)
    {
      // Storing text in lucene index
      set_include_path($_ENV['maarch_tools_path'].DIRECTORY_SEPARATOR.PATH_SEPARATOR.get_include_path());
      require_once('Zend/Search/Lucene.php');
      if(!is_dir($indexFileDirectory))
      {
          writeLog($indexFileDirectory." not exists !");
        $index = Zend_Search_Lucene::create($indexFileDirectory);
      }
      else
      {
          if(isDirEmpty($indexFileDirectory))
        {
          writeLog($indexFileDirectory." empty !");
          $index = Zend_Search_Lucene::create($indexFileDirectory);
        }
        else
        {
          $index = Zend_Search_Lucene::open($indexFileDirectory);
        }
      }
      $term = new Zend_Search_Lucene_Index_Term($Id, 'Id');
      foreach($index->termDocs($term) as $id)
      {
        $index->delete($id);
      }
        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('Id', $Id));
        $doc->addField(Zend_Search_Lucene_Field::UnStored('contents', $fileContent));
        $index->addDocument($doc);
      $index->commit();
      $result = 1;
    }
    else
    {
      $result = 2;
    }
  }
  return $result;
}

/**
* Read a txt file 
* @param  $file string path of the file to read
* @return string contents of the file
*/
Function readFileF($file)
{
  $result = "";
  if(is_file($file))
  {
      $fp = fopen($file, "r");
      $result = fread($fp, filesize($file));
      fclose($fp);
  }
  Return $result;
}

// Begin
date_default_timezone_set('Europe/Paris');
if($argc != 2 )
{
    echo "You must specify the configuration file." . $argc;
    exit;
}
$conf = $argv[1];
$xmlconfig = simplexml_load_file($conf);
foreach($xmlconfig->CONFIG as $CONFIG)
{
    $_ENV['config_name'] = $CONFIG->CONFIG_NAME;
    $base_directory = $CONFIG->BASE_DIRECTORY;
    $_ENV["base_directory"] = $base_directory;
    $indexFileDirectory = $CONFIG->INDEX_FILE_DIRECTORY;
    $_ENV['databaseserver'] = $CONFIG->LOCATION;
    $_ENV['databaseport'] = $CONFIG->DATABASE_PORT;
    $_ENV['database'] = $CONFIG ->DATABASE;
    $_ENV['databasetype'] = $CONFIG->DATABASETYPE;
    $_ENV['databaseuser'] = $CONFIG -> USER_NAME;
    $_ENV['databasepwd'] = $CONFIG->PASSWORD;
    $_ENV['tablename'] = $CONFIG->TABLE_NAME;
    $fulltextColumnName = $CONFIG->FULLTEXT_COLUMN_NAME;
    $_ENV['maarch_tools_path'] = $CONFIG->MAARCH_TOOLS_PATH;
    $_ENV['max_batch_size'] = $CONFIG->MAX_BATCH_SIZE;
  }
if(DIRECTORY_SEPARATOR == "/")
{
    $_ENV['osname'] = "UNIX";
     $_ENV['pdftotext'] = "pdftotext";
}
else
{
    $_ENV['osname'] = "WINDOWS";
    $_ENV['pdftotext'] = "pdftotext.exe";
}
loginCreation();
writeLog("Launch of Lucene full text engine");
writeLog("Loading the xml config file");
writeLog("Config name : " . $_ENV['config_name']);
writeLog("Full text engine launched for table : ".$_ENV['tablename']);
require("class_db.php");
$_ENV['db'] = new dbquery();
$_ENV['db']->connect();
$_ENV['db2'] = new dbquery();
$_ENV['db2']->connect();
writeLog("connection on the DB server OK !");
$docServers = "select docserver_id, path_template from docservers";
$_ENV['db']->query($docServers);
writeLog("docServers found : ");
while($queryResult=$_ENV['db']->fetch_array())
{
  $pathToDocServer[$queryResult[0]] = $queryResult[1];
  writeLog($queryResult[1]);
}
$queryIndexFullText = "select res_id, docserver_id, path, filename, format from ".$_ENV['tablename']." where ".$fulltextColumnName." = '0' or ".$fulltextColumnName." = '' or ".$fulltextColumnName." is null ";
writeLog("query to found document with no full text : ".$queryIndexFullText);
$_ENV['db']->query($queryIndexFullText);
$cpt_batch_size=0;
writeLog("max_batch_size : ".$_ENV['max_batch_size']);
while($queryResult=$_ENV['db']->fetch_array())
{
  if($_ENV['max_batch_size'] >= $cpt_batch_size)
  {
    $pathToFile = $pathToDocServer[$queryResult[1]] . str_replace("#", DIRECTORY_SEPARATOR, $queryResult[2]) . DIRECTORY_SEPARATOR . $queryResult[3];
    writeLog("processing of document : ".$pathToFile." | res_id : ". $queryResult[0]);
    echo "processing of document : ".$pathToFile." \r\n res_id : ". $queryResult[0]."\n";
    $result = indexFullText($pathToFile, $indexFileDirectory, $queryResult[4], $queryResult[0]);
    writeLog("Result of processing : ".$result);
    echo "Result of processing : ".$result."\r\n";
    $updateDoc = "update ".$_ENV['tablename']." SET ".$fulltextColumnName." = '".$result."' where res_id = ".$queryResult[0];
    $queryUpdate = $_ENV['db2']->query($updateDoc);;
  }
  else
  {
    writeLog("Max batch size ! Stop processing !");
    echo "\r\nMax batch size ! Stop processing !";
    break;
  }
  $cpt_batch_size++;
}
writeLog("Return execution code : ".$_ENV['ErrorLevel']);
writeLog("End of application !");
exit($_ENV['ErrorLevel']);
?>

<?php
/*
*   Copyright 2008-2012 Maarch
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
* @brief class of install tools
*
* @file
* @author Laurent Giovannoni
* @author Arnaud Veber
* @date $date$
* @version $Revision$
* @ingroup install
*/

//Loads the required class
try {
    require_once 'core/class/class_functions.php';
    require_once 'core/class/class_db.php';
    require_once 'install/class/Class_Merge.php';
} catch (Exception $e) {
    echo $e->getMessage() . ' // ';
}

class Install extends functions
{
    private $lang = 'en';

    private $docservers = array(
        array('OFFLINE_1', 'offline'),
        array('FASTHD_AI', 'ai'),
        array('OAIS_MAIN_1', 'OAIS_main'),
        array('OAIS_SAFE_1', 'OAIS_safe'),
        array('FASTHD_MAN', 'manual'),
        array('TEMPLATES', 'templates'),
        array('FASTHD_RM_BATCH', 'RM_BATCH'),
        array('FASTHD_RM_MAN', 'RM_MAN'),
        array('IOS', 'IOS'),
        array('OAIS_RM', 'OAIS_RM'),
        array('LOG', 'LOG'),
        array('BUSINESS_MAN', 'BUSINESS_MAN'),
        array('PHOTO_CAPTURE', 'photo_capture'),
    );

    function __construct()
    {
        //merge css & js
        $Class_Merge = new Merge;
        //load lang
        $this->loadLang();
    }

    public function getLangList()
    {
        $langList = array();
        foreach(glob('install/lang/*.php') as $fileLangPath) {
            $langFile = str_replace('.php', '', end(explode('/', $fileLangPath)));
            array_push($langList, $langFile);
        }

        return $langList;
    }

    private function loadLang()
    {
        if (!isset($_SESSION['lang'])) {
            $this->lang = 'en';
        }
        $this->lang = $_SESSION['lang'];

        $langList = $this->getLangList();
        if (!in_array($this->lang, $langList)) {
            $this->lang = 'en';
        }

        require_once('install/lang/' . $this->lang . '.php');
    }

    public function getActualLang()
    {
        return $this->lang;
    }

    public function checkPrerequisites(
        $is = false,
        $optional = false
    )
    {
        if ($is) {
            return '<img src="img/green_light.png" width="20px"/>';
            exit;
        }
        if (!$optional) {
            return '<img src="img/red_light.png"  width="20px"/>';
            exit;
        }
        return '<img src="img/orange_light.png"  width="20px"/>';
    }

    public function checkAllNeededPrerequisites()
    {
        if (!$this->isPhpVersion()) {
            return false;
        }
        if (!$this->isPhpRequirements('pgsql')) {
            return false;
        }
        if (!$this->isPhpRequirements('mbstring')) {
            return false;
        }
        if (!$this->isMaarchPathWritable()) {
            return false;
        }
        if (!$this->isPhpRequirements('gd')) {
            return false;
        }
        if (!$this->isPearRequirements('System.php')) {
            return false;
        }
        if (!$this->isPearRequirements('MIME/Type.php')) {
            return false;
        }
        if (!$this->isIniErrorRepportingRequirements()) {
            return false;
        }
        if (!$this->isIniDisplayErrorRequirements()) {
            return false;
        }
        if (!$this->isIniShortOpenTagRequirements()) {
            return false;
        }
        if (!$this->isIniMagicQuotesGpcRequirements()) {
            return false;
        }
        
        if (DIRECTORY_SEPARATOR != '/' && !$this->isPhpRequirements('fileinfo')){
            return false;
        }
        
        return true;
    }

    public function isPhpVersion()
    {
        if (version_compare(PHP_VERSION, '5.3') < 0) {
            return false;
            exit;
        }
        return true;
    }

    public function isPhpRequirements($phpLibrary)
    {
        if (!@extension_loaded($phpLibrary)) {
            return false;
            exit;
        }
        return true;
    }

    public function isPearRequirements($pearLibrary)
    {
        $includePath = array();
        $includePath = explode(';', ini_get('include_path'));
        for ($i=0;$i<count($includePath);$i++) {
            if (file_exists($includePath[$i] . '/' . $pearLibrary)) {
                return true;
                exit;
            }
        }
        $includePath = explode(':', ini_get('include_path'));
        for ($i=0;$i<count($includePath);$i++) {
            if (file_exists($includePath[$i] . '/' . $pearLibrary)) {
                return true;
                exit;
            }
        }
        return false;
    }

    public function isIniErrorRepportingRequirements()
    {
        if (version_compare(PHP_VERSION, '5.4') >= 0) {
            if (ini_get('error_reporting') <> 24567) {
                return false;
            } else {
                return true;
            }
        } else {
            if (ini_get('error_reporting') <> 22519) {
                return false;
            } else {
                return true;
            }
        }
    }

    public function isIniDisplayErrorRequirements()
    {
        if (strtoupper(ini_get('display_errors')) ==  'OFF') {
            return false;
        } else {
            return true;
        }
    }

    public function isIniShortOpenTagRequirements()
    {
        if (strtoupper(ini_get('short_open_tag')) ==  'OFF') {
            return false;
        } else {
            return true;
        }
    }

    public function isIniMagicQuotesGpcRequirements()
    {
        if (strtoupper(ini_get('magic_quotes_gpc')) ==  'ON') {
            return false;
        } else {
            return true;
        }
    }

    public function getProgress(
        $stepNb,
        $stepNbTotal
    )
    {
        $stepNb--;
        $stepNbTotal--;
        if ($stepNb == 0) {
            return '';
            exit;
        }
        $return = '';
        $percentProgress = round(($stepNb/$stepNbTotal) * 100);
        $sizeProgress = round(($percentProgress * 910) / 100);

        $return .= '<div id="progressButton" style="width: '.$sizeProgress.'px;">';
            $return .= '<div align="center">';
                $return .= $percentProgress.'%';
            $return .= '</div>';
        $return .= '</div>';

        return $return;
    }

    public function setPreviousStep($previousStep)
    {
        $_SESSION['previousStep'] = $previousStep;
    }

    public function checkDatabaseParameters(
        $databaseserver,
        $databaseserverport,
        $databaseuser,
        $databasepassword,
        $databasetype
    )
    {
        $connect  = 'host='.$databaseserver . ' ';
        $connect .= 'port='.$databaseserverport . ' ';
        $connect .= 'user='.$databaseuser . ' ';
        $connect .= 'password='.$databasepassword . ' ';
        $connect .= 'dbname=postgres';

        if (!@pg_connect($connect)) {
            return false;
            exit;
        }

        pg_close();

        return true;
    }

    public function createDatabase(
        $databasename
    )
    {
        $connect  = 'host='.$_SESSION['config']['databaseserver'] . ' ';
        $connect .= 'port='.$_SESSION['config']['databaseserverport'] . ' ';
        $connect .= 'user='.$_SESSION['config']['databaseuser'] . ' ';
        $connect .= 'password='.$_SESSION['config']['databasepassword'] . ' ';
        $connect .= 'dbname=postgres';

        if (!@pg_connect($connect)) {
            return false;
            exit;
        }

        $sqlCreateDatabase  = 'CREATE DATABASE "'.$databasename.'"';
            $sqlCreateDatabase .= " WITH TEMPLATE template0";
            $sqlCreateDatabase .= " ENCODING = 'UTF8'";

        $execute = @pg_query($sqlCreateDatabase);
        if (!$execute) {
            return false;
            exit;
        }
        
        @pg_query('ALTER DATABASE "'.$databasename.'" SET DateStyle =iso, dmy');
        
        pg_close();

        $db = new dbquery();
        $db->connect();
        if (!$db) {
            return false;
            exit;
        }

        if (!$this->executeSQLScript('structure.sql')) {
            return false;
            exit;
        }

        if (!$this->setConfigXml()) {
            return false;
            exit;
        }
        return true;
    }

    private function setConfigXml()
    {
        $xmlconfig = simplexml_load_file('apps/maarch_entreprise/xml/config.xml.default');
        $CONFIG = $xmlconfig->CONFIG;

        $CONFIG->databaseserver = $_SESSION['config']['databaseserver'];
        $CONFIG->databaseserverport = $_SESSION['config']['databaseserverport'];
        $CONFIG->databasename = $_SESSION['config']['databasename'];
        $CONFIG->databaseuser = $_SESSION['config']['databaseuser'];
        $CONFIG->databasepassword = $_SESSION['config']['databasepassword'];
        $CONFIG->lang = $_SESSION['lang'];
        $res = $xmlconfig->asXML();
        $fp = @fopen("apps/maarch_entreprise/xml/config.xml", "w+");
        if (!$fp) {
            return false;
            exit;
        }
        $write = fwrite($fp,$res);
        if (!$write) {
            return false;
            exit;
        }
        return true;
    }

    public function getDataList()
    {
        $sqlList = array();
        foreach(glob('data*.sql') as $fileSqlPath) {
            $sqlFile = str_replace('.sql', '', end(explode('/', $fileSqlPath)));
            array_push($sqlList, $sqlFile);
        }

        return $sqlList;
    }

    public function createData(
        $dataFile
    )
    {
        $db = new dbquery();
        $db->connect();
        if (!$db) {
            return false;
            exit;
        }

        if (!$this->executeSQLScript($dataFile)) {
            return false;
            exit;
        }
        return true;
    }

    public function executeSQLScript($filePath)
    {
        $fileContent = fread(fopen($filePath, 'r'), filesize($filePath));
        $db = new dbquery();
        $db->connect();
        $execute = $db->query($fileContent, true, true);

        if (!$execute) {
            return false;
            exit;
        }
        return true;
    }

    /**
     * test if maarch path is writable
     * @return boolean or error message
     */
    public function isMaarchPathWritable()
    {
        if (!is_writable('.')
                || !is_readable('.')
        ) {
            $error .= _THE_MAARCH_PATH_DOES_NOT_HAVE_THE_ADEQUATE_RIGHTS;
        } else {
            return true;
        }
    }

    /**
     * test if docserver path is read/write
     * @param $docserverPath string path to the docserver
     * @return boolean or error message
     */
    public function checkDocserverRoot($docserverPath)
    {
        if (!is_dir($docserverPath)) {
            $error .= _PATH_OF_DOCSERVER_UNAPPROACHABLE;
        } else {
            if (!is_writable($docserverPath)
                || !is_readable($docserverPath)
            ) {
                $error .= _THE_DOCSERVER_DOES_NOT_HAVE_THE_ADEQUATE_RIGHTS;
            }
        }
        if ($error <> '') {
            return $error;
        } else {
            return true;
        }
    }

    /**
     * create the docservers
     * @param $docserverPath string path to the docserver
     * @return boolean
     */
    public function createDocservers($docserverPath)
    {
        for ($i=0;$i<count($this->docservers);$i++) {
            if (!is_dir(
                $docserverPath . DIRECTORY_SEPARATOR
                . $this->docservers[$i][1])
            ) {
                if (!mkdir(
                    $docserverPath . DIRECTORY_SEPARATOR
                    . $this->docservers[$i][1])
                ) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * update the docservers on DB
     * @param $docserverPath string path to the docserver
     * @return nothing
     */
    public function updateDocserversDB($docserverPath)
    {
        $db = new dbquery();
        $db->connect();
        for ($i=0;$i<count($this->docservers);$i++) {
          $query = "update docservers set path_template = '"
            . $db->protect_string_db($docserverPath . DIRECTORY_SEPARATOR
            . $this->docservers[$i][1] . DIRECTORY_SEPARATOR)
            . "' where docserver_id = '" . $this->docservers[$i][0] . "'";
            $db->query($query);
        }
    }

    public function setSuperadminPass(
        $newPass
    )
    {
        $db = new dbquery();
        $db->connect();
        $query = "UPDATE users SET password='".md5($newPass)."' WHERE user_id='superadmin'";
        $db->query($query);
    }
}

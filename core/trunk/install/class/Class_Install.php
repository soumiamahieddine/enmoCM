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
    require_once('core' . DIRECTORY_SEPARATOR . 'class'
        . DIRECTORY_SEPARATOR . 'class_security.php');
} catch (Exception $e) {
    functions::xecho($e->getMessage()) . ' // ';
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
        /*if (!$this->isPhpRequirements('imagick')) {
            return false;
        }*/
        /*if (!$this->isPhpRequirements('ghostscript')) {
            return false;
        }*/
        if (!$this->isPearRequirements('System.php')) {
            return false;
        }
        if (!$this->isPearRequirements('MIME/Type.php')) {
            return false;
        }
        /*if (!$this-&gt;isIniErrorRepportingRequirements()) {
            return false;
        }*/
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
            if (ini_get('error_reporting') <> 22519) {
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
		
        if (!$this->executeSQLScript('sql/structure.sql')) {
            return false;
            exit;
        }

        
        if (!$this->setConfigXmlThumbnails()) {
            return false;
            exit;
        }
        if (!$this->setConfig_batch_XmlThumbnails()) {
            return false;
            exit;
        }        
        if (!$this->setConfigScriptLaunchThumbnails()) {
            return false;
            exit;
        }
        if (!$this->setConfig_sendmail()) {
            return false;
            exit;
        }


       if (!$this->setConfigXml()) {
            return false;
            exit;
        }

        if (!$this->setConfigXmlVisa()) {
            return false;
            exit;
        }

        if (!$this->setScriptNotificationSendmailSh()) {
            return false;
            exit;
        }

        if (!$this->setScriptNotificationNctNccAndAncSh()) {
            return false;
            exit;
        }

        if (!$this->setScriptSendmailSendmailSh()) {
            return false;
            exit;
        }

        if (!$this->setConfig_LDAP()) {
            return false;
            exit;
        }

        if (!$this->setScript_syn_LDAP_sh()) {
            return false;
            exit;
        }
        


       /*if (!$this->setDatasourcesXsd()) {
            return false;
            exit;
        }*/
        
        return true;
    }

    private function setConfigXml()
    {
        $xmlconfig = simplexml_load_file('apps/maarch_entreprise/xml/config.xml.default');
        //$xmlconfig = 'apps/maarch_entreprise/xml/config.xml.default';
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

    private function setConfigXmlVisa()
    {
        $xmlconfig = simplexml_load_file('modules/visa/xml/config.xml.default');
        $CONFIG = $xmlconfig->CONFIG;
        //TODO fill the file...

        $res = $xmlconfig->asXML();
        $fp = @fopen("modules/visa/xml/config.xml", "w+");
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

        private function setConfigXmlThumbnails()
    {
        $xmlconfig = simplexml_load_file('modules/thumbnails/xml/config.xml.default');
        //$xmlconfig = 'apps/maarch_entreprise/xml/config.xml.default';
        $CONFIG = $xmlconfig->CONFIG;

        $CONFIG->docserver_id = 'TNL';
        $chemin_no_file = realpath('.').'/modules/thumbnails/no_thumb.png';
        $CONFIG->no_file = $chemin_no_file;
        $res = $xmlconfig->asXML();
        $fp = @fopen("modules/thumbnails/xml/config.xml", "w+");
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
            private function setConfig_batch_XmlThumbnails()
    {
        $xmlconfig = simplexml_load_file('modules/thumbnails/xml/config_batch_letterbox.xml.default');
        //$xmlconfig = 'apps/maarch_entreprise/xml/config.xml.default';
        $CONFIG = $xmlconfig->CONFIG;

        $chemin_core = realpath('.').'/core/';
        $CONFIG->CORE_PATH = $chemin_core;
        $CONFIG->MaarchDirectory = realpath('.');
        $CONFIG->LOCATION = $_SESSION['config']['databaseserver'];
        $CONFIG->DATABASE_PORT = $_SESSION['config']['databaseserverport'];
        $CONFIG->DATABASE = $_SESSION['config']['databasename'];
        $CONFIG->USER_NAME = $_SESSION['config']['databaseuser'];
        $CONFIG->PASSWORD = $_SESSION['config']['databasepassword'];
        $CONFIG->PATH_COLUMN_NAME = 'tnl_path';
        $CONFIG->FILENAME_COLUMN_NAME = 'tnl_filename';
        $CONFIG->OUTPUT_DOCSERVER = 'TNL';
        $res = $xmlconfig->asXML();
        $fp = @fopen("modules/thumbnails/xml/config_batch_letterbox.xml", "w+");
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

    private function setConfig_sendmail()
    {
        $xmlconfig = simplexml_load_file('modules/sendmail/batch/config/config.xml.default');
        //$xmlconfig = 'apps/maarch_entreprise/xml/config.xml.default';
        $CONFIG_BASE = $xmlconfig->CONFIG_BASE;

        $CONFIG_BASE->databaseserver = $_SESSION['config']['databaseserver'];
        $CONFIG_BASE->databaseserverport = $_SESSION['config']['databaseserverport'];
        $CONFIG_BASE->databasename = $_SESSION['config']['databasename'];
        $CONFIG_BASE->databaseuser = $_SESSION['config']['databaseuser'];
        $CONFIG_BASE->databasepassword = $_SESSION['config']['databasepassword'];
        $res = $xmlconfig->asXML();
        $fp = @fopen("modules/sendmail/batch/config/config.xml", "w+");
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

        private function setConfig_LDAP()
    {
        $xmlconfig = simplexml_load_file('modules/ldap/xml/config.xml.default');
        //$xmlconfig = 'apps/maarch_entreprise/xml/config.xml.default';
        $CONFIG_BASE = $xmlconfig->config_base;

        $CONFIG_BASE->databaseserver = $_SESSION['config']['databaseserver'];
        $CONFIG_BASE->databaseserverport = $_SESSION['config']['databaseserverport'];
        $CONFIG_BASE->databasename = $_SESSION['config']['databasename'];
        $CONFIG_BASE->databaseuser = $_SESSION['config']['databaseuser'];
        $CONFIG_BASE->databasepassword = $_SESSION['config']['databasepassword'];
        $res = $xmlconfig->asXML();
        $fp = @fopen("modules/ldap/xml/config.xml", "w+");
        if (!$fp) {
            var_dump('fp error');
            return false;
            exit;
        }
        $write = fwrite($fp,$res);
        if (!$write) {
            var_dump('write error');
            return false;
            exit;
        }
        return true;
    }


        private function setScript_syn_LDAP_sh()
    {
        $res = '#!/bin/bash';
        $res .= "\n";
        $res .= "cd ".realpath('.')."/modules/ldap/script/";
        $res .= "\n\n";
        $res .= '#generation des fichiers xml';
        $res .= "\n";
        $res .= "php ".realpath('.')."/modules/ldap/process_ldap_to_xml.php ".realpath('.')."/modules/ldap/xml/config.xml";
        $res .= "\n\n";
        $res .= '#mise a jour bdd';
        $res .= "\n";
        $res .= "php ".realpath('.')."/modules/ldap/process_entities_to_maarch.php ".realpath('.')."/modules/ldap/xml/config.xml";
        $res .= "\n";
        $res .= "php ".realpath('.')."/modules/ldap/process_users_to_maarch.php ".realpath('.')."/modules/ldap/xml/config.xml";
        $res .= "\n";
        $res .= "php ".realpath('.')."/modules/ldap/process_users_entities_to_maarch.php ".realpath('.')."/modules/ldap/xml/config.xml";

            $fp = @fopen("modules/ldap/script/syn_ldap.sh", "w+");
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

    private function setConfigScriptLaunchThumbnails()
    {
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {

            $res = 'cd "'.realpath('.');
            $res .= "\n";
            $res .= "cd ../../";
            $res .= "\n";
            $res .= "cd php";
            $res .= "\n";
            $res .= "\"php.exe\" ".realpath('.')."\modules\\thumbnails\create_tnl.php  ".realpath('.')."\modules\\thumbnails\xml\config_batch_letterbox.xml";
            $res .= "\n";
            $res .= "c:";
            $res .= "\n";
            $res .= "pause";

                $fp = @fopen("modules/thumbnails/scripts/launch_batch_thumbnails.sh", "w+");
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
            
        } elseif(strtoupper(substr(PHP_OS, 0, 3)) === 'LIN') {

            $res = '#!/bin/bash';
            $res .= "\n\n";
            $res .= "php ".realpath('.')."/modules/thumbnails/create_tnl.php ".realpath('.')."/modules/thumbnails/xml/config_batch_letterbox.xml";

                $fp = @fopen("modules/thumbnails/scripts/launch_batch_thumbnails.sh", "w+");
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

    }


    private function setScriptNotificationNctNccAndAncSh()
    {

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {

            $res = 'cd "'.realpath('.');
            $res .= "\n";
            $res .= "cd ../../";
            $res .= "\n";
            $res .= "cd php";
            $res .= "\n";
            $res .= "\"php.exe\" \"".realpath('.')."\modules\\notifications\batch\process_event_stack.php\" -c \"".realpath('.')."\modules\\notifications\batch\config\config.xml\" -n NCT";
            $res .= "\n";
            $res .= "\"php.exe\" \"".realpath('.')."\modules\\notifications\batch\process_event_stack.php\" -c \"".realpath('.')."\modules\\notifications\batch\config\config.xml\" -n NCC";
            $res .= "\n";
            $res .= "\"php.exe\" \"".realpath('.')."\modules\\notifications\batch\process_event_stack.php\" -c \"".realpath('.')."\modules\\notifications\batch\config\config.xml\" -n ANC";
            $res .= "\n";
            $res .= "\"php.exe\" \"".realpath('.')."\modules\\notifications\batch\process_event_stack.php\" -c \"".realpath('.')."\modules\\notifications\batch\config\config.xml\" -n AND";
            $res .= "\n";
            $res .= "\"php.exe\" \"".realpath('.')."\modules\\notifications\batch\process_event_stack.php\" -c \"".realpath('.')."\modules\\notifications\batch\config\config.xml\" -n RED";

                $fp = @fopen(realpath('.')."/modules/notifications/batch/scripts/nct-ncc-and-anc.bat", "w+");
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


            
        } elseif(strtoupper(substr(PHP_OS, 0, 3)) === 'LIN') {

            
            $res = '#!/bin/bash';
            $res .= "\n";
            $res .= "eventStackPath='".realpath('.')."/modules/notifications/batch/process_event_stack.php'";
            $res .= "\n";
            $res .= "cd ".realpath('.')."/modules/notifications/batch/";
            $res .= "\n";
            $res .= 'php $eventStackPath -c '.realpath('.').'/modules/notifications/batch/config/config.xml -n NCT';
            $res .= "\n";
            $res .= 'php $eventStackPath -c '.realpath('.').'/modules/notifications/batch/config/config.xml -n NCC';
            $res .= "\n";
            $res .= 'php $eventStackPath -c '.realpath('.').'/modules/notifications/batch/config/config.xml -n ANC';
            $res .= "\n";
            $res .= 'php $eventStackPath -c '.realpath('.').'/modules/notifications/batch/config/config.xml -n AND';
            $res .= "\n";
            $res .= 'php $eventStackPath -c '.realpath('.').'/modules/notifications/batch/config/config.xml -n RED';

                $fp = @fopen(realpath('.')."/modules/notifications/batch/scripts/nct-ncc-and-anc.sh", "w+");
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


    }

    private function setScriptNotificationSendmailSh(){


        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {

            $res = 'cd "'.realpath('.');
            $res .= "\n";
            $res .= "cd ../../";
            $res .= "\n";
            $res .= "cd php";
            $res .= "\n";

            $res .= "\"php.exe\" \"".realpath('.')."\modules\\notifications\batch\process_email_stack.php\" -c \"".realpath('.')."\modules\\notifications\batch\config\config.xml";
            $res .= "\n";
            $res .= "PAUSE";
                $fp = fopen(realpath('.')."/modules/notifications/batch/scripts/sendmail.bat", "w+");
            if (!$fp) {
                //var_dump('FALSE');
                return false;
                exit;
            }
            $write = fwrite($fp,$res);
            if (!$write) {
                return false;
                exit;
            }
            return true;


            
        } elseif(strtoupper(substr(PHP_OS, 0, 3)) === 'LIN') {

            $res = '#!/bin/bash';
            $res .= "\n";
            $res .= "cd ".realpath('.')."/modules/notifications/batch/";
            $res .= "\n";
            $res .= "emailStackPath='".realpath('.')."/modules/notifications/batch/process_email_stack.php'";
            $res .= "\n";
            $res .= 'php $emailStackPath -c '.realpath('.').'/modules/notifications/batch/config/config.xml';

                $fp = fopen(realpath('.')."/modules/notifications/batch/scripts/sendmail.sh", "w+");

            if (!$fp) {
                //var_dump('FALSE');
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

    }
    
    private function setScriptSendmailSendmailSh()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $res = "cd ".realpath('.');
            $res .= "\n";
            $res .= "cd ../../";
            $res .= "\n";
            $res .= "cd php";
            $res .= "\n";
            $res .= "\"php.exe\" \"".realpath('.')."\modules\sendmail\batch\process_emails.php\" -c \"".realpath('.')."\modules\sendmail\batch\config\config.xml\"";
            
            $fp = fopen(realpath('.')."/modules/sendmail/batch/scripts/sendmail.bat", "w+");
            if (!$fp) {
                //var_dump('FALSE');
                return false;
                exit;
            }
            $write = fwrite($fp,$res);
            if (!$write) {
                return false;
                exit;
            }
            return true;


        } elseif(strtoupper(substr(PHP_OS, 0, 3)) === 'LIN') {

           
            $res = '#!/bin/bash';
            $res .= "\n";
            $res .= "cd ".realpath('.')."/modules/sendmail/batch/";
            $res .= "\n";
            $res .= "emailStackPath='".realpath('.')."/modules/sendmail/batch/process_emails.php'";
            $res .= "\n";
            $res .= 'php $emailStackPath -c '.realpath('.').'/modules/sendmail/batch/config/config.xml';


                $fp = fopen(realpath('.')."/modules/sendmail/batch/scripts/sendmail.sh", "w+");
            if (!$fp) {
                //var_dump('FALSE');
                //exit;
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

    }


    private function setDatasourcesXsd()
    {
        $Fnm = 'apps/maarch_entreprise/xml/datasources.xsd.default';
        $inF = fopen($Fnm,"r");
        while (!feof($inF)) {
           $contentFile .= fgets($inF, 4096);
        }
        $contentFile = str_replace("##databaseserver##", $_SESSION['config']['databaseserver'], $contentFile);
        $contentFile = str_replace("##databaseserverport##", $_SESSION['config']['databaseserverport'], $contentFile);
        $contentFile = str_replace("##databasename##", $_SESSION['config']['databasename'], $contentFile);
        $contentFile = str_replace("##databaseuser##", $_SESSION['config']['databaseuser'], $contentFile);
        $contentFile = str_replace("##databasepassword##", $_SESSION['config']['databasepassword'], $contentFile);
        fclose($inF);
        if (file_exists('apps/maarch_entreprise/xml/datasources.xsd')) {
            unlink('apps/maarch_entreprise/xml/datasources.xsd');
        }
        copy('apps/maarch_entreprise/xml/datasources.xsd.default', 'apps/maarch_entreprise/xml/datasources.xsd'); 
        $fp = fopen('apps/maarch_entreprise/xml/datasources.xsd', "w+");
        if (!$fp) {
            return false;
            exit;
        }
        $write = fwrite($fp, $contentFile);
        if (!$write) {
            return false;
            exit;
        }
        return true;
    }


    public function getDataList()
    {
        $sqlList = array();
        foreach(glob('sql/data*.sql') as $fileSqlPath) {
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
        //create thumbnails_mlb dir
        if (!is_dir(
            $docserverPath . DIRECTORY_SEPARATOR
                . 'thumbnails_mlb')
        ) {
            if (!mkdir(
                $docserverPath . DIRECTORY_SEPARATOR
                    . 'thumbnails_mlb')
            ) {
                return false;
            }
        }
        //create indexes dir
        if (!is_dir(
            $docserverPath . DIRECTORY_SEPARATOR
                . 'indexes')
        ) {
            if (!mkdir(
                $docserverPath . DIRECTORY_SEPARATOR
                    . 'indexes')
            ) {
                return false;
            }
        }
        //create indexes dir for letterbox collection
        if (!is_dir(
            $docserverPath . DIRECTORY_SEPARATOR
                . 'indexes' . DIRECTORY_SEPARATOR . 'letterbox_coll')
        ) {
            if (!mkdir(
                $docserverPath . DIRECTORY_SEPARATOR
                    . 'indexes' . DIRECTORY_SEPARATOR . 'letterbox_coll')
            ) {
                return false;
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
        $sec = new security();
        $db->connect();
        $query = "UPDATE users SET password='" . $sec->getPasswordHash($newPass) . "' WHERE user_id='superadmin'";
        $db->query($query);
    }
}

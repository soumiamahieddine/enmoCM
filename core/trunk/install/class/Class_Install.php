<?php
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
        array('FASTHD_AI', 'ai'),
        array('FASTHD_MAN', 'manual'),
        array('OAIS_MAIN_1', 'OAIS_main'),
        array('OAIS_SAFE_1', 'OAIS_safe'),
        array('OFFLINE_1', 'offline'),
        array('TEMPLATES', 'templates')
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
        if (!$this->isPhpRequirements('gd')) {
            return false;
        }
        if (!$this->isPearRequirements('System.php')) {
            return false;
        }
        if (!$this->isPearRequirements('MIME/Type.php')) {
            return false;
        }
        if (!$this->isPearRequirements('Maarch_CLITools/FileHandler.php')) {
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
        if (ini_get('error_reporting') <> 22519) {
            return false;
        } else {
            return true;
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

    public function setPreviousStep(
        $previousStep
    )
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
        $connect .= 'password='.$databasepassword;


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
        $connect .= 'password='.$_SESSION['config']['databasepassword'];


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
        $execute = $db->query($fileContent, false, true);

        if (!$execute) {
            return false;
            exit;
        }
        return true;
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
}

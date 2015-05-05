<?php
/**
* modules tools Class for attachments
*
*  Contains all the functions to load modules tables for attachments
*
* @package  maarch
* @version 3.0
* @since 10/2005
* @license GPL v3
* @author  Claire Figueras  <dev@maarch.org>
*
*/

class attachments
{

    /**
    * Build Maarch module tables into sessions vars with a xml configuration
    * file
    */
    public function build_modules_tables()
    {
        if (file_exists(
            $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
            . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . "modules"
            . DIRECTORY_SEPARATOR . "attachments" . DIRECTORY_SEPARATOR . "xml"
            . DIRECTORY_SEPARATOR . "config.xml"
        )
        ) {
            $configPath = $_SESSION['config']['corepath'] . 'custom'
                        . DIRECTORY_SEPARATOR . $_SESSION['custom_override_id']
                        . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR
                        . "attachments" . DIRECTORY_SEPARATOR . "xml"
                        . DIRECTORY_SEPARATOR . "config.xml";
        } else {
            $configPath = "modules" . DIRECTORY_SEPARATOR . "attachments"
                        . DIRECTORY_SEPARATOR . "xml" . DIRECTORY_SEPARATOR
                        . "config.xml";
        }
        $xmlconfig = simplexml_load_file($configPath);
        foreach ($xmlconfig->TABLENAME as $tableName) {
            $_SESSION['tablename']['attach_res_attachments'] = (string) $tableName->attach_res_attachments;
        }
		$conf = $xmlconfig->CONFIG;
		$_SESSION['modules_loaded']['attachments']['convertPdf'] = (string) $conf->convertPdf;
		$_SESSION['modules_loaded']['attachments']['vbs_convert_path'] = (string) $conf->vbs_convert_path;
		$_SESSION['modules_loaded']['attachments']['useExeConvert'] = (string) $conf->useExeConvert;
		 
        $hist = $xmlconfig->HISTORY;
        $_SESSION['history']['attachadd'] = (string) $hist->attachadd;
        $_SESSION['history']['attachup'] = (string) $hist->attachup;
        $_SESSION['history']['attachdel'] = (string) $hist->attachdel;
        $_SESSION['history']['attachview'] = (string) $hist->attachview;
    }
}

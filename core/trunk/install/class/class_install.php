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
* @date $date$
* @version $Revision$
* @ingroup install
*/

//Loads the required class
try {
    require_once 'core/class/class_functions.php';
    require_once 'core/class/class_db.php';
} catch (Exception $e) {
    echo $e->getMessage() . ' // ';
}


class install extends functions
{
    private $lang;
    
    /**
     * get languages available
     * @return array of languages
     */
    public function getlanguages()
    {
        $languages = array();
        $classScan = dir('install/static/lang/');
        while (($filescan = $classScan->read()) != false) {
            if ($filescan == '.' || $filescan == '..' || $filescan == '.svn') {
                continue;
            } else {
                array_push($languages, str_replace('.php', '', $filescan));
            }
        }
        return $languages;
    }
    
    /**
     * load the lang constant file
     * @param $lang lang
     * @return nothing
     */
    public function loadLang($lang)
    {
        $this->lang = $lang;
        include_once('install/static/lang/' . $lang . '.php');
    }
    
    /**
     * load the header
     * @return nothing
     */
    public function loadHeader()
    {
        $header = '<head>';
            $header .= '<title>' . _INSTALL_TITLE . '</title>';
            $header .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
            $header .= '<meta http-equiv="Content-Language" content="' . $this->lang . '" />';
            $header .= $this->loadCss();
            $header .= $this->loadjs();
        $header .= '</head>';
        return $header;
    }
    
    /**
     * load the css
     * @return nothing
     */
    private function loadCss()
    {
        $includeCss = '<link rel="stylesheet" type="text/css" href="static/css/install.css" media="screen" />';
        return $includeCss;
    }
    
    /**
     * load the js
     * @return nothing
     */
    private function loadJs()
    {
        $includeJs = '<script type="text/javascript" src="static/js/install.js"></script>';
        return $includeJs;
    }
    
    /**
     * load the current page
     * @param $page string name of the page
     * @return nothing
     */
    public function loadView($viewName)
    {
        include 'install/view/' . $viewName . '.php';
    }
    
    /**
     * load the footer
     * @return nothing
     */
    public function loadFooter()
    {
        echo ' ' . _POWERED_BY;
    }
    
    /**
     * test if the php version is alright
     * @return boolean
     */
    public function isPhpVersion()
    {
        if (version_compare(PHP_VERSION, '5.3') < 0) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * test if php libray loaded
     * @param $phpLibrary string name of the library
     * @return boolean
     */
    public function isPhpRequirements($phpLibrary)
    {
        if (!@extension_loaded($phpLibrary)) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * test if pear library asked is installed
     * @param $pearLibrary string the library logical path
     * @return boolean
     */
    public function isPearRequirements($pearLibrary)
    {
        $includePath = array();
        $includePath = explode(';', ini_get('include_path'));
        for ($i=0;$i<count($includePath);$i++) {
            if (file_exists($includePath[$i] . '/' . $pearLibrary)) {
                return true;
            }
        }
        $includePath = explode(':', ini_get('include_path'));
        for ($i=0;$i<count($includePath);$i++) {
            if (file_exists($includePath[$i] . '/' . $pearLibrary)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * test if php ini error var correctly set
     * @return boolean
     */
    public function isIniErrorRepportingRequirements()
    {
        if (ini_get('error_reporting') <> 22519) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * test if php ini error var correctly set
     * @return boolean
     */
    public function isIniDisplayErrorRequirements()
    {
        if (strtoupper(ini_get('display_errors')) ==  'OFF') {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * test if php ini error var correctly set
     * @return boolean
     */
    public function isIniShortOpenTagRequirements()
    {
        if (strtoupper(ini_get('short_open_tag')) ==  'OFF') {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * test if php ini error var correctly set
     * @return boolean
     */
    public function isIniMagicQuotesGpcRequirements()
    {
        if (strtoupper(ini_get('magic_quotes_gpc')) ==  'ON') {
            return false;
        } else {
            return true;
        }
    }
}

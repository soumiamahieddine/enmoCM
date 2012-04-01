<?php

/*
*   Copyright 2012 Maarch
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
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @defgroup content_management content_management Module
*/

/**
* @brief   Module content_management :  Module Tools Class
*
* <ul>
* <li>Set the session variables needed to run the content_management module</li>
*</ul>
*
* @file
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup content_management
*/

/**
* @brief   Module content_management : Module Tools Class
*
* <ul>
* <li>Loads the tables used by the content_management</li>
* <li>Set the session variables needed to run the content_management module</li>
*</ul>
*
* @ingroup content_management
*/
class content_management extends dbquery
{
    function __construct()
    {
        parent::__construct();
        $this->index = array();
    }

    /**
    * Loads content_management  tables into sessions vars from the
    * content_management/xml/config.xml
    * Loads content_management log setting into sessions vars from the
    * content_management/xml/config.xml
    */
    public function build_modules_tables()
    {
        if (file_exists($_SESSION['config']['corepath'].'custom'
                        .DIRECTORY_SEPARATOR.$_SESSION['custom_override_id']
                        .DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR
                        ."content_management".DIRECTORY_SEPARATOR
                        ."xml".DIRECTORY_SEPARATOR."config.xml")
        ) {
            $path = $_SESSION['config']['corepath'].'custom'
                .DIRECTORY_SEPARATOR.$_SESSION['custom_override_id']
                .DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."content_management"
                .DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml";
        } else {
            $path = "modules".DIRECTORY_SEPARATOR."content_management"
                .DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml";
        }
        $xmlconfig = simplexml_load_file($path);
        //$CONFIG = $xmlconfig->CONFIG;
        // Loads the tables of the module content_management
        // into session ($_SESSION['tablename'] array)
        $TABLENAME = $xmlconfig->TABLENAME ;
        $_SESSION['tablename']['lc_cycle'] = (string) $TABLENAME->lc_cycle;
        $_SESSION['tablename']['lc_cycle_seq'] = (string) $TABLENAME
            ->lc_cycle_seq;
        $_SESSION['tablename']['lc_stack'] = (string) $TABLENAME->lc_stack;

        // Loads the log setting of the module content_management
        // into session ($_SESSION['history'] array)
        $HISTORY = $xmlconfig->HISTORY;
        $_SESSION['history']['lcadd'] = (string) $HISTORY->lcadd;
        $_SESSION['history']['lcup'] = (string) $HISTORY->lcup;
        $_SESSION['history']['lcdel'] = (string) $HISTORY->lcdel;
    }

    /**
    * Load into session vars all the content_management specific vars :
    * calls private methods
    */
    public function load_module_var_session($userData)
    {
        if (file_exists($_SESSION['config']['corepath'].'custom'
                        .DIRECTORY_SEPARATOR.$_SESSION['custom_override_id']
                        .DIRECTORY_SEPARATOR."modules"
                        .DIRECTORY_SEPARATOR."content_management"
                        .DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR
                        ."content_management_features.xml")
        ) {
            $path = $_SESSION['config']['corepath'].'custom'
                  .DIRECTORY_SEPARATOR.$_SESSION['custom_override_id']
                  .DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR
                  ."content_management".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR
                  ."content_management_features.xml";
        } else {
            $path = "modules".DIRECTORY_SEPARATOR."content_management"
                  .DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR
                  ."content_management_features.xml";
        }
        $_SESSION['lifeCycleFeatures'] = array();
        $_SESSION['lifeCycleFeatures'] = functions::object2array(
            simplexml_load_file($path)
        );
        //functions::show_array($_SESSION['lifeCycleFeatures']);
    }
}

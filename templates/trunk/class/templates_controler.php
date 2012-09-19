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
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief  Contains the controler of template object 
* (create, save, modify, etc...)
* 
* 
* @file
* @author Laurent Giovannoni
* @date $date$
* @version $Revision$
* @ingroup templates
*/

// To activate de debug mode of the class
$_ENV['DEBUG'] = false;

// Loads the required class
try {
    require_once ('modules/templates/class/templates.php');
    require_once ('modules/templates/templates_tables_definition.php');
    require_once ('core/class/ObjectControlerAbstract.php');
    require_once ('core/class/ObjectControlerIF.php');
    require_once ('core/class/SecurityControler.php');
} catch (Exception $e) {
    echo $e->getMessage() . ' // ';
}

/**
* @brief  Controler of the templates object 
*
*<ul>
*  <li>Get an templates object from an id</li>
*  <li>Save in the database a templates</li>
*  <li>Manage the operation on the templates related tables in the database 
*  (insert, select, update, delete)</li>
*</ul>
* @ingroup templates
*/
class templates_controler extends ObjectControler implements ObjectControlerIF
{
    
    private $stylesArray = array();
    
    /**
    * Save given object in database:
    * - make an update if object already exists,
    * - make an insert if new object.
    * @param object $template
    * @param string mode up or add
    * @return array
    */
    public function save($template, $mode='') 
    {
        $control = array();
        if (!isset($template) || empty($template)) {
            $control = array(
                'status' => 'ko', 
                'value' => '', 
                'error' => _TEMPLATE_ID_EMPTY,
            );
            return $control;
        }
        $template = $this->isATemplate($template);
        $this->set_foolish_ids(array('template_id'));
        $this->set_specific_id('template_id');
        if ($mode == 'up') {
            $control = $this->control($template, $mode);
            $this->set_foolish_ids(array('template_id'));
            $this->set_specific_id('template_id');
            if ($control['status'] == 'ok') {
                $template = $control['value'];
                if ($template->template_file_name <> '') {
                    unlink($_SESSION['m_admin']['templates']['current_style']);
                }
                //var_dump($this);exit;
                //Update existing template
                if ($this->update($template)) {
                    $control = array(
                        'status' => 'ok', 
                        'value' => $template->template_id,
                    );
                    $this->updateTemplateEntityAssociation($template->template_id);
                    //history
                    if ($_SESSION['history']['templateadd'] == 'true') {
                        $history = new history();
                        $history->add(
                            _TEMPLATES_TABLE_NAME, 
                            $template->template_id, 
                            'UP', 'templateadd',
                            _TEMPLATES_UPDATED.' : '.$template->template_id, 
                            $_SESSION['config']['databasetype']
                        );
                    }
                } else {
                    $control = array(
                        'status' => 'ko', 
                        'value' => '', 
                        'error' => _PB_WITH_TEMPLATE,
                    );
                }
                return $control;
            }
        } else {
            $control = $this->control($template, 'add');
            if ($control['status'] == 'ok') {
                $template = $control['value'];
                if ($template->template_file_name <> '') {
                    unlink($_SESSION['m_admin']['templates']['current_style']);
                }
                //Insert new template
                if ($this->insert($template)) {
                    $templateId = $this->getLastTemplateId($template->template_label);
                    $control = array(
                        'status' => 'ok', 
                        'value' => $templateId,
                    );
                    $this->updateTemplateEntityAssociation($templateId);
                    //history
                    if ($_SESSION['history']['templateadd'] == 'true') {
                        $history = new history();
                        $history->add(
                            _TEMPLATES_TABLE_NAME, 
                            $templateId, 
                            'ADD', 'templateadd',
                            _TEMPLATES_ADDED . ' : ' . $templateId, 
                            $_SESSION['config']['databasetype']
                        );
                    }
                } else {
                    $control = array(
                        'status' => 'ko', 
                        'value' => '', 
                        'error' => _PB_WITH_TEMPLATE,
                    );
                }
            }
        }
        return $control;
    }

    /**
    * control the template object before action
    *
    * @param  object $template template object
    * @param  string $mode up or add
    * @return array ok if the object is well formated, ko otherwise
    */
    private function control($template, $mode) 
    {
        $f = new functions();
        $sec = new SecurityControler();
        $error = '';

        $template->template_label = $f->protect_string_db(
            $f->wash($template->template_label, 'no', _TEMPLATE_LABEL.' ', 'yes', 0, 255)
        );
        $template->template_comment = $f->protect_string_db(
            $f->wash($template->template_comment, 'no', _TEMPLATE_COMMENT.' ', 'yes', 0, 255)
        );
        
        $template->template_content = str_replace(';', '###', $template->template_content);        
        $template->template_content = str_replace('--', '___', $template->template_content); 
        $allowedTags = '<html><head><body><title>'; //Structure
        $allowedTags .= '<h1><h2><h3><h4><h5><h6><b><i><tt><u><strike><blockquote><pre><blink><font><big><small><sup><sub><strong><em>'; // Text formatting
        $allowedTags .='<p><br><hr><center><div><span>'; // Text position
        $allowedTags .= '<li><ol><ul><dl><dt><dd>'; // Lists
        $allowedTags .= '<img><a>'; // Multimedia
        $allowedTags .= '<table><tr><td><th><tbody><thead><tfooter><caption>'; // Tables
        $allowedTags .= '<form><input><textarea><select>'; // Forms
        $template->template_content = strip_tags($template->template_content, $allowedTags);
        $template->template_content = $f->protect_string_db($template->template_content);
        
        $template->template_type = $f->protect_string_db(
            $f->wash($template->template_type, 'no', _TEMPLATE_TYPE.' ', 'yes', 0, 32)
        );
        $template->template_style = $f->protect_string_db(
            $f->wash($template->template_style, 'no', _TEMPLATE_STYLE.' ', 'no', 0, 255)
        );
        if ($mode == 'add' && $this->templateExists($template->template_id)) {
            $error .= $template->template_id.' '._ALREADY_EXISTS.'#';
        }
        $error .= $_SESSION['error'];
        //TODO:rewrite wash to return errors without html
        $error = str_replace('<br />', '#', $error);
        $return = array();
        if (!empty($error)) {
            $return = array(
                'status' => 'ko', 
                'value' => $template, 
                'error' => $error,
            );
        } else {
            if ($template->template_type <> 'HTML') {
                if (
                    $mode == 'add' 
                    && !$_SESSION['m_admin']['templates']['applet']
                ) {
                    $return = array(
                        'status' => 'ko', 
                        'value' => $template, 
                        'error' => _EDIT_YOUR_TEMPLATE,
                    );
                    return $return;
                }
                if (
                    ($mode == 'up' || $mode == 'add') 
                    && $_SESSION['m_admin']['templates']['applet']
                ) {
                    $storeInfos = array();
                    $storeInfos = $this->storeTemplateFile();
                    if (!$storeInfos) {
                        $return = array(
                            'status' => 'ko', 
                            'value' => $template, 
                            'error' => $_SESSION['error'],
                        );
                    } else {
                        //print_r($storeInfos);exit;
                        $template->template_path = $storeInfos['destination_dir'];
                        $template->template_file_name = $storeInfos['file_destination_name'];
                        $return = array(
                            'status' => 'ok', 
                            'value' => $template,
                        );
                    }
                } else {
                    $return = array(
                        'status' => 'ok',
                        'value' => $template,
                    );
                }
            } else {
                $return = array(
                    'status' => 'ok', 
                    'value' => $template,
                );
            }
        }
        return $return;
    }

    /**
    * Inserts in the database (templates table) a templates object
    *
    * @param  $template templates object
    * @return bool true if the insertion is complete, false otherwise
    */
    private function insert($template) 
    {
        return $this->advanced_insert($template);
    }

    /**
    * Updates in the database (templates table) a templates object
    *
    * @param  $template templates object
    * @return bool true if the update is complete, false otherwise
    */
    private function update($template) 
    {
        return $this->advanced_update($template);
    }

    /**
    * Returns an templates object based on a templates identifier
    *
    * @param  $template_id string  templates identifier
    * @param  $comp_where string  where clause arguments 
    * (must begin with and or or)
    * @param  $can_be_disabled bool  if true gets the template even if it is 
    * disabled in the database (false by default)
    * @return templates object with properties from the database or null
    */
    public function get($template_id, $comp_where='', $can_be_disabled=false) 
    {
        $this->set_foolish_ids(array('template_id'));
        $this->set_specific_id('template_id');
        $template = $this->advanced_get($template_id, _TEMPLATES_TABLE_NAME);
        $template->template_content = str_replace('###', ';', $template->template_content);
        $template->template_content = str_replace('___', '--', $template->template_content);
        if (get_class($template) <> 'templates') {
            return null;
        } else {
            //var_dump($template);
            return $template;
        }
    }

    /**
    * get templates with given id for a ws.
    * Can return null if no corresponding object.
    * @param $template_id of template to send
    * @return template
    */
    public function getWs($template_id) 
    {
        $this->set_foolish_ids(array('template_id'));
        $this->set_specific_id('template_id');
        $template = $this->advanced_get($template_id, _TEMPLATES_TABLE_NAME);
        if (get_class($template) <> 'templates') {
            return null;
        } else {
            $template = $template->getArray();
            return $template;
        }
    }

    /**
    * Deletes in the database (templates related tables) a given 
    * templates (template_id)
    *
    * @param  $template string  templates identifier
    * @return bool true if the deletion is complete, false otherwise
    */
    public function delete($template) 
    {
        $control = array();
        if (!isset($template) || empty($template)) {
            $control = array(
                'status' => 'ko', 
                'value' => '', 
                'error' => _TEMPLATES_EMPTY,
            );
            return $control;
        }
        $template = $this->isATemplate($template);
        if (!$this->templateExists($template->template_id)) {
            $control = array(
                'status' => 'ko', 
                'value' => '', 
                'error' => _TEMPLATES_NOT_EXISTS,
            );
            return $control;
        }
        if ($this->linkExists($template->template_id)) {
            $control = array(
                'status' => 'ko', 
                'value' => '', 
                'error' => _LINK_EXISTS,
            );
            return $control;
        }
        $db = new dbquery();
        $db->connect();
        $query = "delete from "._TEMPLATES_TABLE_NAME." where template_id ='"
            .$db->protect_string_db($template->template_id)."'";
        try {
            if ($_ENV['DEBUG']) {
                echo $query.' // ';
            }
            $db->query($query);
            $ok = true;
        } catch (Exception $e) {
            $control = array(
                'status' => 'ko', 
                'value' => '', 
                'error' => _CANNOT_DELETE_TEMPLATE_ID.' '.$template->template_id,
            );
            $ok = false;
        }
        $db->disconnect();
        $control = array(
            'status' => 'ok', 
            'value' => $template->template_id,
        );
        if ($_SESSION['history']['templatedel'] == 'true') {
            require_once('core/class/class_history.php');
            $history = new history();
            $history->add(
                _TEMPLATES_TABLE_NAME, $template->template_id, 'DEL', 'templatedel',
                _TEMPLATES_DELETED.' : '.$template->template_id, 
                $_SESSION['config']['databasetype']
            );
        }
        return $control;
    }

    /**
    * Disables a given templates
    * 
    * @param  $template templates object 
    * @return bool true if the disabling is complete, false otherwise 
    */
    public function disable($template) 
    {
        //
    }

    /**
    * Enables a given templates
    * 
    * @param  $template templates object  
    * @return bool true if the enabling is complete, false otherwise 
    */
    public function enable($template) 
    {
        //
    }

    /**
    * Fill a template object with an object if it's not a template
    *
    * @param  $object ws template object
    * @return object template
    */
    private function isATemplate($object) 
    {
        if (get_class($object) <> 'templates') {
            $func = new functions();
            $templateObject = new templates();
            $array = array();
            $array = $func->object2array($object);
            foreach (array_keys($array) as $key) {
                $templateObject->$key = $array[$key];
            }
            return $templateObject;
        } else {
            return $object;
        }
    }

    /**
    * Checks if the template exists
    * 
    * @param $template_id templates identifier
    * @return bool true if the template exists
    */
    public function templateExists($template_id) 
    {
        if (!isset ($template_id) || empty ($template_id)) {
            return false;
        }
        $db = new dbquery();
        $db->connect();
        $query = "select template_id from " . _TEMPLATES_TABLE_NAME 
            . " where template_id = '" . $template_id . "'";
        try {
            if ($_ENV['DEBUG']) {
                echo $query . ' // ';
            }
            $db->query($query);
        } catch (Exception $e) {
            echo _UNKNOWN . _TEMPLATES . ' ' . $template_id . ' // ';
        }
        if ($db->nb_result() > 0) {
            $db->disconnect();
            return true;
        }
        $db->disconnect();
        return false;
    }

    /**
    * Checks if the template is linked 
    * 
    * @param $template_id templates identifier
    * @return bool true if the template is linked
    */
    public function linkExists($template_id) 
    {
        if (!isset($template_id) || empty($template_id)) {
            return false;
        }
        $db = new dbquery();
        $db->connect();
        /*$query = "select template_id from " . _TEMPLATES_ASSOCIATION_TABLE_NAME
            . " where template_id = '" . $template_id . "'";
        $db->query($query);
        if ($db->nb_result() > 0) {
            $db->disconnect();
            return true;
        }*/
        $query = "select template_id from " . _TEMPLATES_DOCTYPES_EXT_TABLE_NAME
            . " where template_id = '" . $template_id . "'";
        $db->query($query);
        if ($db->nb_result() > 0) {
            $db->disconnect();
            return true;
        }
        $db->disconnect();
    }
    
    /**
    * Return the last templateId
    * 
    * @return bigint templateId
    */
    public function getLastTemplateId($templateLabel)
    {
        $db = new dbquery();
        $db->connect();
        $query = "select template_id from " . _TEMPLATES_TABLE_NAME
            . " where template_label = '" . $db->protect_string_db($templateLabel) . "'"
            . " order by template_id desc";
        $db->query($query);
        $queryResult = $db->fetch_object();
        return $queryResult->template_id;
    }

    /**
    * Return all templates ID
    * 
    * @return array of templates
    */
    public function getAllId($can_be_disabled = false) 
    {
        $db = new dbquery();
        $db->connect();
        $query = "select template_id from " . _TEMPLATES_TABLE_NAME . " ";
        if (!$can_be_disabled) {
            $query .= " where enabled = 'Y'";
        }
        try {
            if ($_ENV['DEBUG']) {
                echo $query . ' // ';
            }
            $db->query($query);
        } catch (Exception $e) {
            echo _NO_TEMPLATES . ' // ';
        }
        if ($db->nb_result() > 0) {
            $result = array();
            $cptId = 0;
            while ($queryResult = $db->fetch_object()) {
                $result[$cptId] = $queryResult->template_id;
                $cptId++;
            }
            $db->disconnect();
            return $result;
        } else {
            $db->disconnect();
            return null;
        }
    }
    
    /**
    * Return all templates in an array
    * 
    * @return array of templates
    */
    public function getAllTemplatesForSelect() {
        $return = array();
        
        $db = new dbquery();
        $db->connect();
        $db->query("select * from " . _TEMPLATES_TABLE_NAME . " ");
        
        while ($result = $db->fetch_object()) {
            $this_template = array();
            $this_template['id'] = $result->template_id;
            $this_template['label'] = $result->template_label;
            $this_template['comment'] = $result->template_comment;
            $this_template['type'] = $result->template_type;
            array_push($return, $this_template);
        }
        
        return $return;
    }
    
    /**
    * Return all templates in an array for an entity
    * 
    * @param $entityId entity identifier
    * @return array of templates
    */
    public function getAllTemplatesForProcess($entityId) 
    {
        $db = new dbquery();
        $db->connect();
        $db->query(
            "select * from " 
            . _TEMPLATES_TABLE_NAME . " t, " 
            . _TEMPLATES_ASSOCIATION_TABLE_NAME . " ta where "
            . "t.template_id = ta.template_id and ta.what = 'destination' and ta.value_field = '" 
            . $entityId . "'"
        );
        $templates = array();
        while ($res = $db->fetch_object()) {
            array_push(
                $templates, array(
                    'ID' => $res->template_id, 
                    'LABEL' => $res->template_label,
                    'TYPE' => $res->template_type,
                )
            );
        }
        return $templates;
    }
    
    public function updateTemplateEntityAssociation($templateId)
    {
        $db = new dbquery();
        $db->connect();
        $db->query("delete from " . _TEMPLATES_ASSOCIATION_TABLE_NAME 
            . " where template_id = '" . $templateId . "' and what = 'destination'"
        );
        for ($i=0;$i<count($_SESSION['m_admin']['templatesEntitiesSelected']);$i++) {
            $db->query("insert into " . _TEMPLATES_ASSOCIATION_TABLE_NAME 
                . " (template_id, what, value_field, maarch_module) VALUES (" 
                . $templateId . ", 'destination', '" 
                . $_SESSION['m_admin']['templatesEntitiesSelected'][$i] 
                . "', 'entities')"
            );
        }
    }

    /**
    * Displays templates according to a given entity 
    * 
    * @param $entityId templates entity identifier
    * @return array templates identifier
    */
    public function getAllIdByEntity($entityId) 
    {
        $db = new dbquery();
        $db->connect();
        $query = "select template_id from " . _TEMPLATES_TABLE_NAME 
            . " where entity_id = '".$entityId."'";
        try {
            if ($_ENV['DEBUG']) {
                echo $query . ' // ';
            }
            $db->query($query);
        } catch (Exception $e) {
            echo _NO_TEMPLATES . ' // ';
        }
        if ($db->nb_result() > 0) {
            $result = array();
            $cptId = 0;
            while ($queryResult = $db->fetch_object()) {
                $result[$cptId] = $queryResult->template_id;
                $cptId++;
            }
            $db->disconnect();
            return $result;
        } else {
            $db->disconnect();
            return null;
        }
    }
    
    public function getAllItemsLinkedToModel($templateId, $field ='')
    {
        $db = new dbquery();
        $db->connect();
        $items = array();
        if (empty($templateId)) {
            return $items;
        }
        $db->connect();
        if (empty($field)) {
            $db->query("select distinct what from " 
                . _TEMPLATES_ASSOCIATION_TABLE_NAME
                . " where template_id = " .$templateId
            );
            while ($res = $db->fetch_object()) {
                $items[$res->what] = array();
            }
            foreach (array_keys($items) as $key) {
                $db->query("select value_field from " 
                    . _TEMPLATES_ASSOCIATION_TABLE_NAME 
                    . " where template_id = " . $templateId 
                    . " and what = '" . $key . "'");
                $items[$key] = array();
                while ($res = $db->fetch_object()) {
                    array_push($items[$key], $res->value_field);
                }
            }
        } else {
            $items[$field] = array();
            $db->query("select value_field from " 
                . _TEMPLATES_ASSOCIATION_TABLE_NAME 
                . " where template_id = " . $templateId . " and what = '" 
                . $field . "'"
            );
            while ($res = $db->fetch_object()) {
                array_push($items[$field], $res->value_field);
            }
        }
        return $items;
    }
    
    public function getTemplatesStyles($dir, $stylesArray)
    {
        $this->stylesArray = $stylesArray;
        //Browse all files of the style template dir
        $classScan = dir($dir);
        while (($filescan = $classScan->read()) != false) {
            if ($filescan == '.' || $filescan == '..' || $filescan == '.svn') {
                continue;
            } elseif (is_dir($dir . $folder . $filescan)) {
                $this->getTemplatesStyles($dir . $folder . $filescan . '/', $this->stylesArray);
            } else {
                $filePath = $dir . $folder . '/' . $filescan;
                $info = pathinfo($filePath);
                array_push(
                    $this->stylesArray, 
                    array(
                        'fileName' => basename($filePath, '.' . $info['extension']),
                        'fileExt'  => strtoupper($info['extension']),
                        'filePath' => $filePath,
                    )
                );
            }
        }
        return $this->stylesArray;
    }
    
    public function getTemplatesDatasources($configXml) 
    {
        $datasources = array();
        //Browse all files of the style template dir
        $xmlcontent = simplexml_load_file($configXml);
        foreach($xmlcontent->datasource as $datasource) {
            //<id> <label> <script>    
            if(@constant((string) $datasource->label)) {
                $label = constant((string)$datasource->label);
            } else {
                $label = (string) $datasource->label;
            }
            array_push(
                $datasources, 
                array(
                    'id' => (string)$datasource->id,
                    'label'  => $label,
                    'script' => (string)$datasource->script,
                )
            );
        }
        return $datasources;
    }
    
    
    //returns file ext
    function extractFileExt($sFullPath) {
        $sName = $sFullPath;
        if (strpos($sName, '.')==0) {
            $ExtractFileExt = '';
        } else {
            $ExtractFileExt = explode ('.', $sName);
        }
        return $ExtractFileExt[1];
    }
    
    function storeTemplateFile() {
        if (!$_SESSION['m_admin']['templates']['applet']) {
            //echo $_SESSION['m_admin']['templates']['current_style'] . '<br>';exit;
            $tmpFileName = 'cm_tmp_file_' . $_SESSION['user']['UserId']
                . '_' . rand() . '.' 
                . strtolower(
                    $this->extractFileExt(
                        $_SESSION['m_admin']['templates']['current_style']
                    )
                );
            $tmpFilePath = $_SESSION['config']['tmppath'] . $tmpFileName;
            if (!copy(
                    $_SESSION['m_admin']['templates']['current_style'],
                    $tmpFilePath
                )
            ) {
                $_SESSION['error'] = _PB_TO_COPY_STYLE_ON_TMP . ' ' . $tmpFilePath;
                return false;
            } else {
                $_SESSION['m_admin']['templates']['current_style'] = $tmpFilePath;
            }
            //echo $_SESSION['m_admin']['templates']['current_style'];exit;
        }
        if ($_SESSION['m_admin']['templates']['current_style'] == '') {
            $_SESSION['error'] = _SELECT_A_TEMPLATE_STYLE;
            return false;
        } else {
            if (file_exists($_SESSION['m_admin']['templates']['current_style'])) {
                $storeInfos = array();
                $fileName = basename(
                    $_SESSION['m_admin']['templates']['current_style']
                );
                $fileSize = filesize(
                    $_SESSION['m_admin']['templates']['current_style']
                );
                $fileExtension = $this->extractFileExt(
                    $_SESSION['m_admin']['templates']['current_style']
                );
                include_once 'core/class/docservers_controler.php';
                $docservers_controler = new docservers_controler();
                $fileTemplateInfos = array(
                    'tmpDir'      => $_SESSION['config']['tmppath'],
                    'size'        => $fileSize,
                    'format'      => $fileExtension,
                    'tmpFileName' => $fileName,
                );
                $storeInfos = $docservers_controler->storeResourceOnDocserver(
                    'templates',
                    $fileTemplateInfos
                );
                if (!file_exists(
                        $storeInfos['path_template']
                        .  str_replace("#", DIRECTORY_SEPARATOR, $storeInfos['destination_dir'])
                        . $storeInfos['file_destination_name']
                    )
                ) {
                    $_SESSION['error'] = _FILE_NOT_EXISTS . ' : ' . $storeInfos['path_template']
                        .  str_replace("#", DIRECTORY_SEPARATOR, $storeInfos['destination_dir'])
                        . $storeInfos['file_destination_name'];
                    return false;
                }
                return $storeInfos;
            } else {
                $_SESSION['error'] = 'ERROR : file not exists ' 
                    . $_SESSION['m_admin']['templates']['current_style'];
                return false;
            }
        }
    }
    
    /**
    * replace the defined fields in the instance of the template
    *
    * @param string  $content template content
    */
    public function fieldsReplace(
        $content, 
        $res_id = '', 
        $coll_id = '', 
        $declareGlobals = false
    )
    {
        if (!empty($res_id) && !empty($coll_id)) {
            require_once('core/class/class_security.php');
            require_once('apps/' . $_SESSION['config']['app_id'] 
                . '/class/class_business_app_tools.php'
            );
            $sec = new security();
            $business = new business_app_tools();
            $db = new dbquery();
            $db->connect();
            $table = $sec->retrieve_view_from_coll_id($coll_id);
            $params = array();
            $params['coll_view'] = array();
            $params['users'] = array();
            $params['users']['table'] = $_SESSION['tablename']['users'];
            $params['users']['where'] = '';
            $params['contacts'] = array();
            $params['contacts']['table'] = $_SESSION['tablename']['contacts'];
            $params['contacts']['where'] = '';
            $params['entities'] = array();
            $params['entities']['where'] = '';
            $params['entities']['table'] = $_SESSION['tablename']['ent_entities'];
            $params['coll_view']['table'] = $table;
            $params['coll_view']['where'] = ' where res_id = ' . $res_id;
            $db->query("select exp_contact_id, exp_user_id, dest_user_id, "
                . "dest_contact_id, destination from " 
                . $params['coll_view']['table'] . " " . $params['coll_view']['where']
            );
            $res = $db->fetch_object();
            if (isset($res->exp_contact_id) && !empty($res->exp_contact_id)) {
                $params['contacts']['where'] = " where contact_id = ".$res->exp_contact_id;
            } else if (isset($res->dest_contact_id) && !empty($res->dest_contact_id)) {
                $params['contacts']['where'] = " where contact_id = ".$res->dest_contact_id;
            }
            if (isset($res->exp_user_id) && !empty($res->exp_user_id)) {
                $params['users']['where'] = " where user_id = '".$res->exp_user_id."'";
            } else if (isset($res->dest_user_id) && !empty($res->dest_user_id)) {
                $params['users']['where'] = " where user_id = '".$res->dest_user_id."'";
            }
            if (isset($res->destination) && !empty($res->destination)) {
                $params['entities']['where'] = " where entity_id = '".$res->destination."'";
            }
            if ($table <> '') {
                if (
                    file_exists(
                        $_SESSION['config']['corepath'] . 'custom/' 
                        . $_SESSION['custom_override_id'] 
                        . '/modules/templates/xml/mapping_file.xml')
                    ) {
                    $path = $_SESSION['config']['corepath'] . 'custom/' 
                    . $_SESSION['custom_override_id']
                    . '/modules/templates/xml/mapping_file.xml';
                } else {
                    $path = 'modules/templates/xml/mapping_file.xml';
                }
                $xml = simplexml_load_file($path);
                $items = array();
                foreach ($xml->item as $item) {
                    $field = (string) $item->field;
                    $var_name = (string) $item->var_name;
                    $used_table = '';
                    $used_where = '';
                    if (isset($item->table)) {
                        $tmp_table = (string) $item->table;
                        if ($tmp_table == 'coll_view') {
                            $used_table = $params['coll_view']['table'];
                            $used_where = $params['coll_view']['where'];
                        } else {
                            $used_table = $params[$tmp_table]['table'];
                            $used_where = $params[$tmp_table]['where'];
                        }
                    }
                    $type = '';
                    if (isset($item->type)) {
                        $type = (string) $item->type;
                    }
                    if (!empty($field) && !empty($tmp_table) && !empty($used_where)) {
                        $db->query("select " . $field . " as field from " 
                            . $used_table . " " . $used_where);
                        $res = $db->fetch_object();
                        $value = $res->field;
                        if ($var_name == '[CAT_ID]') {
                            $value = $_SESSION['mail_categories'][$value];
                        } elseif ($var_name == '[NATURE]') {
                            $value = $_SESSION['mail_natures'][$value];
                        } elseif ($var_name == '[CONTACT_TITLE]') {
                            $value = $business->get_label_title($value);
                        } elseif ($type == 'string') {
                            $value = $db->show_string($value);
                        } else if ($type == 'date') {
                            $value = $db->format_date_db($value, false);
                        }
                        array_push($items, array('var_name' =>  $var_name, 'value' => $value));
                    } else {
                        switch ($var_name) {
                            case "[NOW]" :
                                $value = date('j-m-Y');
                                break;
                            case "[CURRENT_USER_FIRSTNAME]" :
                                $value = $_SESSION['user']['FirstName'];
                                break;
                            case "[CURRENT_USER_LASTNAME]" :
                                $value = $_SESSION['user']['LastName'];
                                break;
                            case "[CURRENT_USER_PHONE]" :
                                $value = $_SESSION['user']['Phone'];
                                break;
                            case "[CURRENT_USER_EMAIL]" :
                                $value = $_SESSION['user']['Mail'];
                                break;
                            default :
                                $value = '';
                        }
                        array_push($items, array('var_name' => $var_name, 'value' => $value));
                    }
                }
            }
        }
        for ($i=0;i<count($items);$i++) {
            $content = str_replace($items[$i]['var_name'], $items[$i]['value'], $content);
            if ($i == count($items)) {
                break;
            }
        }
        if (!$declareGlobals) {
            return $content;
        } else {
            return $items;
        }
    }
    
     /**
    * Make a copy of template to temp directory for merge process
    *
    * @param object $templateObj : template object
    * @return string $templateCopyPath : path to working copy
    */
    private function getWorkingCopy($templateObj) {
        
        if($templateObj->template_type == 'HTML') {
            $fileExtension = 'html';
            $fileNameOnTmp = $_SESSION['config']['tmppath'] . 'tmp_template_' . $_SESSION['user']['UserId']
            . '_' . rand() . '.' . $fileExtension;
            $handle = fopen($fileNameOnTmp, 'w');
            if (fwrite($handle, $templateObj->template_content) === FALSE) {
                return false;
            }
            fclose($handle);
            return $fileNameOnTmp;
        } else {
            $dbTemplate = new dbquery();
            $dbTemplate->connect();
            $query = "select path_template from " . _DOCSERVERS_TABLE_NAME 
                . " where docserver_id = 'TEMPLATES'";
            $dbTemplate->query($query);
            $resDs = $dbTemplate->fetch_object();
            $pathToDs = $resDs->path_template;
            $pathToTemplateOnDs = $pathToDs . str_replace(
                    "#", 
                    DIRECTORY_SEPARATOR, 
                    $templateObj->template_path
                )
                . $templateObj->template_file_name;
            
            return $pathToTemplateOnDs;
        }
    
    }
    
    private function getDatasourceScript($datasourceId) 
    {
        if ($datasourceId <> '') {
            $fulllist = array();
            $fulllist = $this->getTemplatesDatasources('modules/templates/xml/datasources.xml');
            foreach ($fulllist as $ds) {
                if ($datasourceId == $ds['id']){
                    return (object)$ds;
                }
            }
        }
        return null;
    }
    
    private function getBaseDatasources() {
        $datasources = array();
        
        // Date and time
        $datasources['datetime'][0]['date'] = date('d-m-Y');
        $datasources['datetime'][0]['time'] = date('H:i:s.u');
        $datasources['datetime'][0]['timestamp'] = time();
        
        // Session
        if(isset($_SESSION)) {
            // Config (!!! database)
            if(count($_SESSION['config']) > 0) {
                $datasources['config'][0] = $_SESSION['config'];
                $datasources['config'][0]['linktoapp'] = $_SESSION['config']['businessappurl']."index.php";
            }
            
            // Current basket
            if(count($_SESSION['current_basket']) > 0) {
                foreach($_SESSION['current_basket'] as $name => $value) {
                    if(!is_array($value)) {
                        $datasources['basket'][0][$name] = $value;
                    }
                }
            }
            
            // User
            if(count($_SESSION['user']) > 0) {
                foreach($_SESSION['user'] as $name => $value) {
                    if(!is_array($value)) {
                        $datasources['user'][0][strtolower($name)] = $value;
                    }
                }
                if(count($_SESSION['user']['entities']) > 0) {
                    foreach($_SESSION['user']['entities'] as $entity) {
                        if($entity['ENTITY_ID'] === $_SESSION['user']['primaryentity']['id']) {
                            $datasources['user'][0]['entity'] = $_SESSION['user']['entities'][0]['ENTITY_LABEL'];
                            $datasources['user'][0]['role'] = $_SESSION['user']['entities'][0]['ROLE'];
                        }
                    }
                }
            }
            
        }
    
        return $datasources;
    }
    
    
    /** Merge template with data from a datasource to the requested output 
    * 
    * @param string $templateId : templates identifier
    * @param array $params : array of parameters for datasource retrieval
    * @param string $outputType : save to 'file', retrieve 'content'
    * @return merged content or path to file
    */
    public function merge($templateId, $params=array(), $outputType) 
    {
        require_once 'core/class/class_functions.php';
        require_once 'modules/templates/templates_tables_definition.php';
        include_once 'apps/maarch_entreprise/tools/tbs/tbs_class_php5.php';
        include_once 'apps/maarch_entreprise/tools/tbs/tbs_plugin_opentbs.php';

        $templateObj = $this->get($templateId);
        
        // Get template path from docserver or copy HTML template to temp file 
        $pathToTemplate = $this->getWorkingCopy($templateObj);
        
        $datasources = $this->getBaseDatasources();
		// Make params array for datasrouce script
        foreach($params as $paramName => $paramValue) {
            $$paramName = $paramValue;
        }
		//Retrieve script for datasources
        $datasourceObj = $this->getDatasourceScript($templateObj->template_datasource);
		if($datasourceObj->script) {
			require $datasourceObj->script;
		}
		
        // Merge with TBS
        $TBS = new clsTinyButStrong;
		$TBS->NoErr = true;
        if($templateObj->template_type == 'OFFICE') {
            $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
			$TBS->LoadTemplate($pathToTemplate, OPENTBS_ALREADY_UTF8);
        } else {
			$TBS->LoadTemplate($pathToTemplate);
		}
		
        foreach ($datasources as $name => $datasource) {
            // Scalar values or arrays ?
			if(!is_array($datasource)) {
				$TBS->MergeField($name, $datasource);
            } else {
			    $TBS->MergeBlock($name, 'array', $datasource);
		    }
        }
        
        switch($outputType) {
        case 'content':
            if($templateObj->template_type == 'OFFICE') {
                $TBS->Show(OPENTBS_STRING);
            } else {
                $TBS->Show(TBS_NOTHING);
            }
            $myContent = $TBS->Source;
            return $myContent;
            
        case 'file':
            $func = new functions();
            $fileExtension = $func->extractFileExt($pathToTemplate);
            $fileNameOnTmp = 'tmp_file_' . $_SESSION['user']['UserId']
            . '_' . rand() . '.' . $fileExtension;
            $myFile = $_SESSION['config']['tmppath'] . $fileNameOnTmp;
            if($templateObj->template_type == 'OFFICE') {
                $TBS->Show(OPENTBS_FILE, $myFile);
            } else {
                $TBS->Show(TBS_NOTHING);
                $myContent = $TBS->Source;
                $handle = fopen($myFile, 'w');
                fwrite($handle, $myContent);
                fclose($handle);
            }
            return $myFile;
        }
    }
    
    /** Copy a template master on tmp dir 
    * 
    * @param string $templateId : templates identifier
    * @return string path of the template in tmp dir
    */
    public function copyTemplateOnTmp($templateId) 
    {
        $templateObj = $this->get($templateId);
        // Get template path from docserver
        $pathToTemplate = $this->getWorkingCopy($templateObj);
		$fileExtension = $this->extractFileExt($pathToTemplate);
        $fileNameOnTmp = 'tmp_file_' . $_SESSION['user']['UserId']
            . '_' . rand() . '.' . $fileExtension;
        $filePathOnTmp = $_SESSION['config']['tmppath'] . $fileNameOnTmp;
        // Copy the template from the DS to the tmp dir
        if (!copy($pathToTemplate, $filePathOnTmp)) {
			return '';
		} else {
			return $filePathOnTmp;
		}
    }
}

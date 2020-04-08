<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   templates_controler_Abstract
* @author  dev <dev@maarch.org>
* @ingroup templates
*/

// To activate de debug mode of the class
$_ENV['DEBUG'] = false;

// Loads the required class
try {
    include_once 'modules/templates/class/templates.php';
    include_once 'modules/templates/templates_tables_definition.php';
    include_once 'core/class/ObjectControlerAbstract.php';
    include_once 'core/class/ObjectControlerIF.php';
    include_once 'core/class/SecurityControler.php';
} catch (Exception $e) {
    functions::xecho($e->getMessage()) . ' // ';
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
abstract class templates_controler_Abstract extends ObjectControler implements ObjectControlerIF
{
    protected $stylesArray = array();
    
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
                            'UP',
                            'templateadd',
                            _TEMPLATE_UPDATED.' : '.$template->template_id,
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
                            'ADD',
                            'templateadd',
                            _TEMPLATE_ADDED . ' : ' . $templateId,
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
    protected function control($template, $mode)
    {
        $f = new functions();
        $sec = new SecurityControler();
        $error = '';

        $template->template_label = $f->wash($template->template_label, 'no', _TEMPLATE_LABEL.' ', 'yes', 0, 255);
        $template->template_comment = $f->wash($template->template_comment, 'no', _TEMPLATE_COMMENT.' ', 'yes', 0, 255);

        
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
        
        $template->template_type = $f->protect_string_db(
            $f->wash($template->template_type, 'no', _TEMPLATE_TYPE.' ', 'yes', 0, 32)
        );
        $template->template_style = $f->protect_string_db(
            $f->wash($template->template_style, 'no', _TEMPLATE_STYLE.' ', 'no', 0, 255)
        );
        if ($mode == 'add' && $this->templateExists($template->template_id)) {
            $error .= $template->template_id.' '._ALREADY_EXISTS.'#';
        }
        $template->template_target = $f->protect_string_db(
            $f->wash($template->template_target, 'no', _TEMPLATE_TARGET.' ', 'no', 0, 255)
        );
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
            if ($template->template_type == 'OFFICE') {
                if (($mode == 'up' && $_SESSION['m_admin']['templates']['applet'])
                    || ($mode == 'up' && !empty($_SESSION['m_admin']['templates']['current_style']))
                    || $mode == 'add') {
                    $storeInfos = array();
                    $storeInfos = $this->storeTemplateFile();
                    if (!$storeInfos) {
                        $return = array(
                            'status' => 'ko',
                            'value' => $template,
                            'error' => $_SESSION['error'],
                        );
                    } else {
                        $template->template_path = $storeInfos['destination_dir'];
                        $template->template_file_name = $storeInfos['file_destination_name'];
                        $template->template_style = $storeInfos['template_style'];
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
    protected function insert($template)
    {
        return $this->advanced_insert($template);
    }

    /**
    * Updates in the database (templates table) a templates object
    *
    * @param  $template templates object
    * @return bool true if the update is complete, false otherwise
    */
    protected function update($template)
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
        if (!empty($template_id) && $template_id <> '' && $template_id <> 'empty') {
            $template = $this->advanced_get($template_id, _TEMPLATES_TABLE_NAME);
            $template->template_content = str_replace('###', ';', $template->template_content);
            $template->template_content = str_replace('___', '--', $template->template_content);
            if (get_class($template) <> 'templates') {
                return null;
            } else {
                //var_dump($template);
                return $template;
            }
        } else {
            return null;
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
        $db = new Database();
        $query = "delete from "._TEMPLATES_TABLE_NAME." where template_id = ? " ;
            
        try {
            //
            $stmt = $db->query($query, array($template->template_id));
            $ok = true;
        } catch (Exception $e) {
            $control = array(
                'status' => 'ko',
                'value' => '',
                'error' => _CANNOT_DELETE_TEMPLATE_ID.' '.$template->template_id,
            );
            $ok = false;
        }
        $control = array(
            'status' => 'ok',
            'value' => $template->template_id,
        );
        if ($_SESSION['history']['templatedel'] == 'true') {
            include_once 'core/class/class_history.php';
            $history = new history();
            $history->add(
                _TEMPLATES_TABLE_NAME,
                $template->template_id,
                'DEL',
                'templatedel',
                _TEMPLATE_DELETED.' : '.$template->template_id,
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
    protected function isATemplate($object)
    {
        if (get_class($object) <> 'templates') {
            $func = new functions();
            $templateObject = new templates();
            $array = array();
            $array = $func->object2array($object);
            foreach (array_keys($array) as $key) {
                $templateObject->{$key} = $array[$key];
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
        if (!isset($template_id) || empty($template_id)) {
            return false;
        }
        $db = new Database();
        
        $query = "select template_id from " . _TEMPLATES_TABLE_NAME
            . " where template_id = ? ";
        try {
            $stmt = $db->query($query, array($template_id));
        } catch (Exception $e) {
            echo _UNKNOWN . _TEMPLATES . ' ' . $template_id . ' // ';
        }
        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }
    
    /**
    * Return the last templateId
    *
    * @return bigint templateId
    */
    public function getLastTemplateId($templateLabel)
    {
        $db = new Database();
        $query = "select template_id from " . _TEMPLATES_TABLE_NAME
            . " where template_label = ? "
            . " order by template_id desc";
        $stmt = $db->query($query, array($templateLabel));
        $queryResult = $stmt->fetchObject();
        return $queryResult->template_id;
    }

    /**
    * Return all templates ID
    *
    * @return array of templates
    */
    public function getAllId($can_be_disabled = false)
    {
        $db = new Database();
        $query = "select template_id from " . _TEMPLATES_TABLE_NAME . " ";
        if (!$can_be_disabled) {
            $query .= " where enabled = 'Y'";
        }
        try {
            //
            $stmt = $db->query($query);
        } catch (Exception $e) {
            echo _NO_TEMPLATES . ' // ';
        }
        if ($db->rowCount() > 0) {
            $result = array();
            $cptId = 0;
            while ($queryResult = $stmt->fetchObject()) {
                $result[$cptId] = $queryResult->template_id;
                $cptId++;
            }
            return $result;
        } else {
            return null;
        }
    }
    
    /**
    * Return all templates in an array
    *
    * @return array of templates
    */
    public function getAllTemplatesForSelect()
    {
        $return = array();
        
        $db = new Database();
        $stmt = $db->query("select * from " . _TEMPLATES_TABLE_NAME . " ");
        
        while ($result = $stmt->fetchObject()) {
            $this_template = array();
            $this_template['ID'] = $result->template_id;
            $this_template['LABEL'] = $result->template_label;
            $this_template['COMMENT'] = $result->template_comment;
            $this_template['TYPE'] = $result->template_type;
            $this_template['TARGET'] = $result->template_target;
            array_push($return, $this_template);
        }
        
        return $return;
    }
    
    public function updateTemplateEntityAssociation($templateId)
    {
        $db = new Database();
        $db->query(
            "delete from " . _TEMPLATES_ASSOCIATION_TABLE_NAME
            . " where template_id = ?",
            array($templateId)
        );
       
        for ($i=0;$i<count($_SESSION['m_admin']['templatesEntitiesSelected']);$i++) {
            $db->query(
                "insert into " . _TEMPLATES_ASSOCIATION_TABLE_NAME
                . " (template_id, value_field) VALUES (?, ?)",
                array($templateId, $_SESSION['m_admin']['templatesEntitiesSelected'][$i])
            );
        }
    }
    
    //returns file ext
    public function extractFileExt($sFullPath)
    {
        $sName = $sFullPath;
        if (strpos($sName, '.')==0) {
            $ExtractFileExt = '';
        } else {
            $ExtractFileExt = explode('.', $sName);
        }
        return end($ExtractFileExt);
    }
    
    public function storeTemplateFile()
    {
        if (!$_SESSION['m_admin']['templates']['applet']) {
            $tmpFileName = 'cm_tmp_file_' . $_SESSION['user']['UserId']
                . '_' . rand() . '.'
                . strtolower(
                    $this->extractFileExt(
                        $_SESSION['m_admin']['templates']['current_style']
                    )
                );
            $tmpFilePath = $_SESSION['config']['tmppath'] . $tmpFileName;
            if (!copy($_SESSION['m_admin']['templates']['current_style'], $tmpFilePath)) {
                $_SESSION['error'] = _PB_TO_COPY_STYLE_ON_TMP . ' ' . $tmpFilePath;
                return false;
            } else {
                $_SESSION['m_admin']['templates']['current_style'] = $tmpFilePath;
            }
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
                $storeInfos['template_style'] = $_SESSION['m_admin']['templates']['template_style'];
                if (!file_exists($storeInfos['path_template'] . str_replace("#", DIRECTORY_SEPARATOR, $storeInfos['destination_dir']) . $storeInfos['file_destination_name'])) {
                    $_SESSION['error'] = $storeInfos['error'];
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
}

<?php
/**
* modules tools Class for templates
*
*  Contains all the functions to load modules tables for template
*
* @package  maarch
* @version 3.0
* @since 10/2005
* @license GPL v3
* @author  Claire Figueras  <dev@maarch.org>
*
*/

class templates extends dbquery
{
    function __construct()
    {
        parent::__construct();
    }

    /**
    * Build Maarch module tables into sessions vars with a xml configuration file
    */
    public function build_modules_tables()
    {
        if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml"))
        {
            $path_config = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml";
        }
        else
        {
            $path_config = "modules".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml";
        }
        $xmlconfig = simplexml_load_file($path_config);
        foreach($xmlconfig->TABLENAME as $TABLENAME)
        {
            $_SESSION['tablename']['temp_templates'] = (string) $TABLENAME->temp_templates;
            $_SESSION['tablename']['temp_templates_doctype_ext'] = (string) $TABLENAME->temp_templates_doctype_ext;
            $_SESSION['tablename']['temp_templates_association'] = (string) $TABLENAME->temp_templates_association;
        }
        $HISTORY = $xmlconfig->HISTORY;
        $_SESSION['history']['templateadd'] = (string) $HISTORY->templateadd;
        $_SESSION['history']['templateup'] = (string) $HISTORY->templateup;
        $_SESSION['history']['templatedel'] = (string) $HISTORY->templatedel;
    }


    public function getAllTemplates()
    {
            $return = array();
            
            $this->connect();   
            $this->query("select * from ".$_SESSION['tablename']['temp_templates']);
            
            while ($result = $this->fetch_object())
            {
                $this_template = array();
                $this_template['id'] = $result->id;
                $this_template['label'] = $result->label;
                $this_template['template_comment'] = $result->template_comment;
                
                array_push($return, $this_template);
            }
            
            return $return;
    }
    
    
    public function getAllItemsLinkedToModel($template_id, $field ='')
    {
        $items = array();
        if(empty($template_id))
        {
            return $items;
        }
        $this->connect();

        if(empty($field))
        {
            $this->query("select distinct what from ".$_SESSION['tablename']['temp_templates_association']." where template_id = ".$template_id);
            while($res = $this->fetch_object())
            {
                $items[$res->what] = array();
            }
            foreach(array_keys($items) as $key)
            {
                $this->query("select value_field from ".$_SESSION['tablename']['temp_templates_association']." where template_id = ".$template_id." and what = '".$key."'");
                $items[$key] = array();
                while($res = $this->fetch_object())
                {
                    array_push($items[$key], $res->value_field);
                }
            }
        }
        else
        {
            $items[$field] = array();
            $this->query("select value_field from ".$_SESSION['tablename']['temp_templates_association']." where template_id = ".$template_id." and what = '".$field."'");
            while($res = $this->fetch_object())
            {
                array_push($items[$field], $res->value_field);
            }
        }
        return $items;
    }

    public function getModelsFromResid($res_id, $coll_id, $field ='')
    {
        $templates = array();
        if(empty($res_id) || empty($coll_id))
        {
            return $templates;
        }
        require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
        $sec = new security();
        $table = $sec->retrieve_table_from_coll($coll_id);
        if(empty($table))
        {
            return $templates;
        }
        $templates_str = '';

        $this->connect();
        if(empty($field))
        {
            $db = new dbquery();
            $db->connect();
            $this->query("select distinct what from ".$_SESSION['tablename']['temp_templates_association']);
            while($res = $this->fetch_object())
            {
                $db->query("select ".$res->what." as what from ".$table." where res_id = ".$res_id );
                $line = $db->fetch_object();
                $what = $line->what;
                $db->query("select ma.template_id, m.label, ma. from ".$_SESSION['tablename']['temp_templates_association']." ma, ".$_SESSION['tablename']['temp_templates']." m where m.id = ma.template_id and ma.value_field = '".$what."'");
                $line = $db->fetch_object();
                array_push($templates, array('ID' => $line->template_id, 'LABEL' => $line->label));
                $templates_str .= $line->template_id.", ";
            }
        }
        else
        {
            $this->query("select ".$field." as what from ".$table." where res_id = ".$res_id );
            $line = $this->fetch_object();
            $what = $line->what;
            $this->query("select ma.template_id, m.label, ma. from ".$_SESSION['tablename']['temp_templates_association']." ma, ".$_SESSION['tablename']['temp_templates']." m where m.id = ma.template_id and ma.value_field = '".$what."'");
            $line = $this->fetch_object();
            array_push($templates, array('ID' => $line->template_id, 'LABEL' => $line->label));
            $templates_str = $line->template_id." ";
        }

        /*if(!empty($templates_str))
        {
            $templates_str = preg_replace('/, $/', '', $templates_str);
            $this->query("select id, label from ".$_SESSION['tablename']['temp_templates'].". where id not in (".$templates_str.")");
        }
        while($res = $this->fetch_object())
        {
            array_push($templates, array('ID' => $res->template_id, 'LABEL' => $res->label));
        }*/
        return $templates;
    }

}

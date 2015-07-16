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

class templates extends Database
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
			$db = new Database();
            $return = array();
              
            $stmt = $db->query("select * from ".$_SESSION['tablename']['temp_templates']);
            
            while ($result = $stmt->fetchObject())
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
		$db = new Database();
        $items = array();
        if(empty($template_id))
        {
            return $items;
        }

        if(empty($field))
        {
            $stmt = $db->query("select distinct what from ".$_SESSION['tablename']['temp_templates_association']." where template_id = ? ", 
							array($template_id)
					);
            while($res = $stmt->fetchObject())
            {
                $items[$res->what] = array();
            }
            foreach(array_keys($items) as $key)
            {
                $stmt2 = $db->query("select value_field from ".$_SESSION['tablename']['temp_templates_association']." where template_id = ? and what = ? ", 
									array($template_id,$key)
						);
                $items[$key] = array();
                while($res = $stmt2->fetchOject())
                {
                    array_push($items[$key], $res->value_field);
                }
            }
        }
        else
        {
            $items[$field] = array();
            $stmt = $db->query("select value_field from ".$_SESSION['tablename']['temp_templates_association']." where template_id = ? and what = ? ", 
							array($template_id,$field)
					);
            while($res = $stmt->fetchObject())
            {
                array_push($items[$field], $res->value_field);
            }
        }
        return $items;
    }

}

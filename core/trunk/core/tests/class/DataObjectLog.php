<?php
class DataObjectLog
    extends DOMComment
{
    const NONE    = 0;
    const CREATE  = 1;
    const READ    = 2;
    const UPDATE  = 3;
    const DELETE  = 4;
    const VALIDATE= 5;
    
    const INFO      = 0;
    const WARNING   = 1;
    const ERROR     = 2;
    const FATAL     = 3;
    
    
    public function getAttribute($name) 
    {
        $attribute_array = array();
        // Match attribute-name attribute-value pairs.
        $hasAttributes = preg_match_all(
                '#([^\s=]+)\s*=\s*(\'[^<\']*\'|"[^<"]*")#',
                $this, $matches, PREG_SET_ORDER);
        if ($hasAttributes) {
            foreach ($matches as $attribute) {
                $attribute_array[$attribute[1]] =
                        substr($attribute[2], 1, -1);
            }
        }
        return $attribute_array[$name];
    }
    
    public function __get($name)
    {
        if($name == 'tagName') {
            $found = preg_match('#^\s*[\w_]+\s#', $this, $matches);
            return $matches[0];
        }
    }
    
    public function __toString()
    {
        return (string)$this->nodeValue;
    }
} 

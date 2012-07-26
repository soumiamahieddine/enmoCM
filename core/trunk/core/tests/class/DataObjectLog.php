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
    
    public function __toString()
    {
        return (string)$this->nodeValue;
    }
} 

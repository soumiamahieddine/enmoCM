<?php

	

class DataObjectChange
{
    const NONE      = 0;
    const CREATE    = 1;
    const READ      = 2;
    const UPDATE    = 3;
    const DELETE    = 4;
   
    private $timestamp;
    private $type;
    private $name;
	private $valueBefore;
	private $valueAfter;
	
    public function DataObjectChange($type, $name=false, $valueBefore=false, $valueAfter=false)
    {
        $this->timestamp = date('d-m-y h:i:s');
        $this->type = $type;
        $this->name = $name;
        $this->valueBefore = $valueBefore;
        $this->valueAfter = $valueAfter;
    }
    
    public function __get($name)
    {
        return $this->$name;
    }
    
    public function __toString()
    {
        return print_r($this, true);
    }
} 

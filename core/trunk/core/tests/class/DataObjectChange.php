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
	private $ValueBefore;
	private $valueAfter;
	
    public function DataObjectChange($type, $name=false, $valueBefore=false, $valueAfter=false)
    {
        $this->type = $type;
        $this->timestamp = date('d-m-y h:i:s') ;
        $this->name = $name;
        $this->valueBefore = $valueBefore;
        $this->valueAfter = $valueAfter;
    }
    
    public function __get($name)
    {
        return $this->$name;
    }
    
} 

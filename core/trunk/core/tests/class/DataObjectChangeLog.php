<?php

class DataObjectChangeLog 
	extends ArrayObject
{
	
    private $active;
    private $logReads;
    
	public function DataObjectChangeLog()
	{
        $this->active = true;
	}
	
	public function logChange($type, $name=false, $valueBefore=false, $valueAfter=false) 
    {
        $newChange = new DataObjectChange($type, $name, $valueBefore, $valueAfter);
        //echo "<br/>DataObjectChange($type, $name, $valueBefore, $valueAfter)";
        $this->offsetSet(null, $newChange);
    }
    
    public function logCreation($name) 
    {
        $this->logChange(DataObjectChange::CREATE, $name);
    }
    
    public function logRead($name) 
    {
        $this->logChange(DataObjectChange::READ, $name);
    }
    
    public function __get($name)
    {
        switch($name) {
        case 'active'   : return $this->active;
        case 'changes'  : return (array)$this;
        case 'creation' :
            if(count($this) > 0) {
                $change = $this->offsetGet(0);
                if($change->type === DataObjectChange::CREATE) {
                    return true;
                }
            } 
            break;
            
        case 'updates'  :
            foreach($this as $offset => $change) {
                if($change->type === DataObjectChange::UPDATE) {
                    $updates[] = $change;
                }
            }
            return $updates;
        }
    }

}

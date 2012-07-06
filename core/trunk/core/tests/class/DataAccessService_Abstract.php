<?php

abstract class DataAccessService
{
	public $name;
    public $type;
    public $datatypes = array();
    public $tables = array();
    public $relations = array();
    

    public function getRelation($parentName, $childName) 
    {
        foreach($this->relations as $relationName => $relation) {
            if($relation->parentName == $parentName && $relation->childName == $childName) {
                return $relation;
            }
        }
    }
    
    private function saveChildObjects($dataObject) 
    {
        $children = $dataObject->children;
        for($i=0; $i<count($children); $i++) {
            $childObject = $children[$i];
            if($childObject->isDataObjectArray) {
                //echo "<br/>Save data of array $childObject->name";
                $this->saveChildObjects($childObject);
            } else {
                //echo "<br/>Save data of $childObject->name";
                $this->saveData($childObject);
            }
            if(!$result) return false;
        }
        return true;
    }

}

abstract class DataAccessService_Datatype 
{
    public $name;
    
}

abstract class DataAccessService_Table
{
    public $name;
    public $columns = array();
    public $primaryKey;
    public $foreignKeys = array();
    public $filter;
    public $keyValue;
    public $filterValue;
    
    public function DataAccessService_Table($name)
    {
        $this->name = $name;
    }
    
    public function addFilter($columns) 
    {
        $this->filter = $columns;
    }
    
    public function setFilter($filterValue)
    {
        $this->filterValue = $filterValue;
    }
    
    public function setKey($keyValue)
    {
        $this->keyValue = $keyValue;
    }
    
    public function getKey()
    {
        return $this->primaryKey->columns;
    }
    
}

abstract class DataAccessService_PrimaryKey
{
    public $name;
    public $columns;
    
    public function DataAccessService_PrimaryKey($columns, $name)
    {
        $this->name = $name;
        $this->columns = $columns;
    }
    
    public function getColumns() 
    {
        return explode(' ', $this->columns);
    }    
  

}

abstract class DataAccessService_Sort
{
    public $sortExpression;
    public $order;
       
}   

abstract class DataAccessService_Column
{
    public $name;
    public $type;
    public $default;
    public $nillable;
    public $fixed;
    
    public function DataAccessService_Column($name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }
    
}   

abstract class DataAccessService_Relation
{
    public $name;
    public $parentName;
    public $childName;
    public $parentColumns;
    public $childColumns;
    
    function DataAccessService_Relation($parentName, $childName, $parentColumns, $childColumns, $name) 
    {
        $this->name = $name;
        $this->parentName = $parentName;
        $this->childName = $childName;
        $this->parentColumns = $parentColumns;
        $this->childColumns = $childColumns;
    }
}
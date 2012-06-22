<?php
class DataAccessService_Database 
{
	
    private $connection;
    public $schema;
    public $tables = array();
    public $relations = array();
    
    public function DataAccessService_Database($schema='public') 
    {
        $this->schema=$schema;
    }
    
    public function addTable($tableName) 
    {
        $newTable = new DataAccessService_Database_Table($tableName);
        $this->tables[$tableName] = $newTable;
        return $newTable;
    }
    
    public function addColumn($tableName, $columnName, $columnType)
    {
        $table = $this->tables[$tableName];
        return $table->addColumn($columnName, $columnType);
    }
    
    public function addRelation($parentName, $childName, $parentColumns, $childColumns, $name=false) 
    {
        if(!$name) {
            $name = $parentName . '_' . $childName . '_FK';
        }
        $newRelation = new DataAccessService_Database_Relation($parentName, $childName, $parentColumns, $childColumns, $name);
        $this->relations[$name] = $newRelation;
    }
    
    public function setKey($tableName, $keyValue)
    {
        $table = $this->tables[$tableName];
        $table->setKey($keyValue);
    }
    
    public function setFilter($tableName, $filterValue)
    {
        $table = $this->tables[$tableName];
        $table->setFilter($filterValue);
    }
    
    
    public function setOrder($tableName, $orderElements, $orderMode)
    {
        $table = $this->tables[$tableName];
        $table->setOrder($orderElements, $orderMode);  
    }
    
    
    
    public function getData($dataObject) 
    {
        $parentObject = $dataObject->parentObject;
       
        $tableName = $dataObject->name;
        $table = $this->tables[$tableName];
        
        // Where
        $whereExpressionParts = array('1=1');
        $relationExpression = $this->makeRelationExpression($table, $parentObject);
        if($relationExpression) {
            $whereExpressionParts[] = $relationExpression;
        }
        $keyExpression = $table->makeSelectKeyExpression();
        if($keyExpression) {
            $whereExpressionParts[] = $keyExpression;
        }
        $filterExpression = $table->makeFilterExpression();
        if($filterExpression) {
            $whereExpressionParts[] = $filterExpression;
        }
        $whereExpression = implode(' and ', $whereExpressionParts);
        
        // Order
        $orderExpression = $table->makeOrderExpression();
        
        $query  = "SELECT " . $table->makeSelectExpression();
        $query .= " FROM  " . $tableName;
        $query .= " WHERE " . $whereExpression;
        $query .= " ORDER BY " . $orderExpression;
        $query .= " LIMIT 1000";
        
        //echo "<pre>DAS = " . print_r($this,true) . "</pre>";
        //echo "<pre>QUERY = " . $query . "</pre>";
        $db = new dbquery();
        $db->query($query);
        
        $results = array();
        while($result = $db->fetch_assoc()) {
            $results[] = $result;
        }
        return $results;
    }
    
    public function saveData($dataObject)
    {
        $parentObject = $dataObject->parentObject;
       
        $tableName = $dataObject->name;
        $table = $this->tables[$tableName];
        
        //UPDATE
        $updateExpression = $table->makeUpdateExpression($dataObject);
        // Key
        $keyExpression = $table->makeUpdateKeyExpression($dataObject);
    
        $query  = "UPDATE " . $tableName;
        $query .= " SET  " . $updateExpression;
        $query .= " WHERE " . $keyExpression;
        
        //echo "<pre>DAS = " . print_r($this,true) . "</pre>";
        //echo "<pre>QUERY = " . $query . "</pre>";
        $db = new dbquery();
        $db->query($query);
    }
    
    //*************************************************************************
    // PRIVATE FUNCTIONS
    //*************************************************************************
    private function getRelation($parentName, $childName) 
    {
        foreach($this->relations as $relationName => $relation) {
            if($relation->parentName == $parentName && $relation->childName == $childName) {
                return $relation;
            }
        }
    }
    
    private function makeRelationExpression($table, $parentObject) 
    {
        $parentName = $parentObject->name;
        
        $relation = $this->getRelation($parentName, $table->name);
        if($relation) {
            $relationExpressionParts = array();
            $childColumns = explode(' ', $relation->childColumns);
            $parentColumns = explode(' ', $relation->parentColumns);
            for($i=0; $i<count($childColumns); $i++) {
                $childColumnName = $childColumns[$i];
                $parentColumnName = $parentColumns[$i];
                
                $childColumn = $table->columns[$childColumnName];
                $childType = $childColumn->type;
                
                $parentColumnValue = $this->enclose($parentObject->{$parentColumnName}, $childType);  
                $relationExpressionParts[] = $childColumnName . " = " . $parentColumnValue;
            }
            $relationExpression = implode(' and ', $relationExpressionParts);
            return $relationExpression;
        }
    }
    
    public function enclose($value, $typeName)
    {
        if(!in_array(
            $typeName,
            array(
                'xsd:boolean',
                'xsd:double', 
                'xsd:decimal',
                    'xsd:integer',
                        'xsd:nonPositiveInteger',
                            'xsd:negativeInteger',
                        'xsd:long',
                            'xsd:int', 
                            'xsd:short', 
                            'xsd:byte',
                        'xsd:nonNegativeInteger',
                            'xsd:positiveInteger',
                            'xsd:unsignedLong',
                                'xsd:unsignedInt',
                                    'xsd:unsignedShort',
                                        'xsd:unsignedByte',
                'xsd:float',
                )
            )
        ) {
                $value = "'" . $value . "'";
            } 
        return $value;
    }
   
}

class DataAccessService_Database_Table
{
    public $name;
    public $columns = array();
    public $primaryKey;
    public $foreignKeys = array();
    public $indexes = array();
    public $filter;
    public $keyValue;
    public $order;
    public $filterValue;

    public function DataAccessService_Database_Table($name)
    {
        $this->name = $name;
    }
    
    public function addPrimaryKey($columns, $name=false)
    {
        if(!$name) $name = $this->name . '_pkey';
        $this->primaryKey = new DataAccessService_Database_Table_PrimaryKey($columns, $name);
    }
    
    public function addColumn($columnName, $columnType)
    {
        $newColumn = new DataAccessService_Database_Table_Column($columnName, $columnType);
        $this->columns[$columnName] = $newColumn;
        return $newColumn;
    }
    
    public function addFilter($columns) 
    {
        $this->filter = $columns;
    }
    
    public function setKey($keyValue)
    {
        $this->keyValue = $keyValue;
    }
    
    public function setOrder($orderElements, $orderMode)
    {
        $orderElementsComa = implode(', ', explode(' ', $orderElements));
        $this->order = $orderElementsComa . ' ' . $orderMode;
    }
    
    public function setFilter($filterValue)
    {
        $this->filterValue = $filterValue;
    }
    
    public function makeSelectExpression() 
    {
        $selectExpressionParts = array();
        foreach ($this->columns as $columnName => $column) {
            // DEFAULT, FIXED
            if($column->fixed) {
                $fixedValue = $this->enclose($column->fixed, $column->type);
                $selectExpressionPart = $fixedValue;
            } elseif($column->{'default'}) {
                $defaultValue = DataAccessService_Database::enclose($column->{'default'}, $column->type);
                $selectExpressionPart = "COALESCE(" . $this->name . "." . $column->name . ", " . $defaultValue . ") AS " . $column->name;
            } else {
                $selectExpressionPart = $this->name . "." . $column->name;
            }
            $selectExpressionParts[] = $selectExpressionPart;
        }
        return implode(', ', $selectExpressionParts);
    }
    
    public function makeUpdateExpression($dataObject)
    {
        $updateExpressionParts = array();
        
        $keyColumns = $this->primaryKey->getColumns();
        
        foreach ($this->columns as $columnName => $column) {
            if(in_array($columnName, $keyColumns)) continue;
            $columnValue = DataAccessService_Database::enclose($dataObject->{$columnName}, $column->type); 
            if($column->{'default'}) {
                $defaultValue = DataAccessService_Database::enclose($column->{'default'}, $column->type);
                $updateExpressionPart = $column->name . " = COALESCE(" . $columnValue . ", " . $defaultValue . ")";
            } else {
                $updateExpressionPart = $column->name . " = " . $columnValue; 
            }
            $updateExpressionParts[] = $updateExpressionPart;
        }
        return implode(', ', $updateExpressionParts);
    }
    
    public function makeSelectKeyExpression() 
    {
        $selectKeyExpressionParts = array();
        if(isset($this->primaryKey) && !is_null($this->primaryKey)
            && isset($this->keyValue) && !is_null($this->keyValue)) {
            $keyColumns = $this->primaryKey->getColumns();
            $keyValues = explode(' ', $this->keyValue);
            for($i=0; $i<count($keyColumns); $i++) {
                $keyColumnName = $keyColumns[$i];
                $keyColumn = $this->columns[$keyColumnName];
                $keyValue = DataAccessService_Database::enclose($keyValues[$i], $keyColumn->type);  
                $selectKeyExpressionParts[] = $this->name . '.' . $keyColumnName . '=' . $keyValue;
            }
            $selectKeyExpression = implode(' and ', $selectKeyExpressionParts);
            return $selectKeyExpression;
        }
    }
    
    public function makeFilterExpression()
    {
        $filterExpressionParts = array();
        if(isset($this->filter) && !is_null($this->filter)
            && isset($this->filterValue) && !is_null($this->filterValue)) {       
            $filterColumns = explode(' ', $this->filter);
            $filterValue = DataAccessService_Database::enclose($this->filterValue, $filterColumn->type);  
            for($i=0; $i<count($filterColumns); $i++) {
                $filterColumnName = $filterColumns[$i];
                $filterColumn = $this->columns[$filterColumnName];
                $filterExpressionParts[] = "upper(" . $this->name . '.' . $filterColumnName . ') like upper(' . $filterValue . ')';
            }
            $filterExpression = implode(' or ', $filterExpressionParts);
            return $filterExpression;
        }
    }
    
    public function makeUpdateKeyExpression($dataObject)
    {
        $updateKeyExpressionParts = array();
        if(isset($this->primaryKey) && !is_null($this->primaryKey)) {
            $keyColumns = $this->primaryKey->getColumns();
            for($i=0; $i<count($keyColumns); $i++) {
                $keyColumnName = $keyColumns[$i];
                $keyColumn = $this->columns[$keyColumn];
                $keyValue = DataAccessService_Database::enclose($dataObject->{$keyColumnName}, $keyColumn->type); 
                $updateKeyExpressionParts[] = $keyColumnName . ' = ' . $keyValue;
            }
            $updateKeyExpression = implode(' and ', $updateKeyExpressionParts);
            return $updateKeyExpression;
        }
    }
    
    public function makeOrderExpression() 
    {
        if(!is_null($this->order)) {
            return $this->order;
        } elseif(isset($this->primaryKey) && !is_null($this->primaryKey))  {
            $orderElementsComa = implode(', ', $this->primaryKey->getColumns());
            return $orderElementsComa .' ASC';
        }
    }   
}

class DataAccessService_Database_Table_PrimaryKey
{
    public $name;
    public $columns;
    
    public function DataAccessService_Database_Table_PrimaryKey($columns, $name)
    {
        $this->name = $name;
        $this->columns = $columns;
    }
    
    public function getColumns() 
    {
        return explode(' ', $this->columns);
    }    
  
}

class DataAccessService_Database_Table_Column
{
    public $name;
    public $type;
    public $default;
    public $nillable;
    public $fixed;
    
    public function DataAccessService_Database_Table_Column($name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }
    
}

class DataAccessService_Database_Relation
{
    public $name;
    public $parentName;
    public $childName;
    public $parentColumns;
    public $childColumns;
    
    function DataAccessService_Database_Relation($parentName, $childName, $parentColumns, $childColumns, $name) {
        $this->name = $name;
        $this->parentName = $parentName;
        $this->childName = $childName;
        $this->parentColumns = $parentColumns;
        $this->childColumns = $childColumns;
    } 
}
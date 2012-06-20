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
    
    public function setKey($tableName, $key)
    {
        $table = $this->tables[$tableName];
        $table->setKey($key);
    }
    
    public function getData($dataObject) 
    {
        $parentObject = $dataObject->getParentObject();
       
        $tableName = $dataObject->name;
        $table = $this->tables[$tableName];
        
        // Where
        $whereExpressionParts = array('1=1');
        $relationExpression = $this->makeRelationExpression($table, $parentObject);
        if($relationExpression) {
            $whereExpressionParts[] = $relationExpression;
        }
        $keyExpression = $table->makeKeyExpression();
        if($keyExpression) {
            $whereExpressionParts[] = $keyExpression;
        }
        $whereExpression = implode(' and ', $whereExpressionParts);
  
        $query  = "SELECT " . $table->makeSelectExpression();
        $query .= " FROM  " . $tableName;
        $query .= " WHERE " . $whereExpression;
        
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
    public $primaryKey = false;
    public $foreignKeys = array();
    public $indexes = array();
    public $relation;
    public $key;

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
    
    public function setKey($key)
    {
        $this->key = $key;
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
    
    public function makeKeyExpression() 
    {
        $keyExpressionParts = array();
        if(isset($this->primaryKey) && !is_null($this->primaryKey)
            && isset($this->key) && !is_null($this->key)) {
            $keyColumns = $this->primaryKey->getColumns();
            $keyValues = explode(' ', $this->key);
            for($i=0; $i<count($keyColumns); $i++) {
                $keyColumnName = $keyColumns[$i];
                $keyColumn = $this->columns[$keyColumn];
                $keyValue = DataAccessService_Database::enclose($keyValues[$i], $keyColumn->type);  
                $keyExpressionParts[] = $this->name . '.' . $keyColumnName . '=' . $keyValue;
            }
            $keyExpression = implode(' and ', $keyExpressionParts);
            return $keyExpression;
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
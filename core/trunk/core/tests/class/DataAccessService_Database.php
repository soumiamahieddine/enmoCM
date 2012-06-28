<?php
class DataAccessService_Database 
    
{
    public $type;
    public $tables = array();
    public $relations = array();
    private $limit;
    private $pdo;
    
    public function DataAccessService_Database($dsn, $user, $password, $options) 
    {
        $this->type = 'database';
        $this->pdo = new pdo($dsn, $user, $password, $options);
        $this->limit = 500;
    }
    
    public function addTable($tableName) 
    {
        $newTable = new DataAccessService_Database_Table($tableName);
        $this->tables[$tableName] = $newTable;
        return $newTable;
    }
    
    public function getTable($tableName)
    {
        return $this->tables[$tableName];
    }
    
    public function addRelation($parentName, $childName, $parentColumns, $childColumns, $name=false) 
    {
        if(!$name) {
            $name = $parentName . '_' . $childName . '_FK';
        }
        $newRelation = new DataAccessService_Database_Relation($parentName, $childName, $parentColumns, $childColumns, $name);
        $this->relations[$name] = $newRelation;
    }
    
    public function getData($dataObject) 
    {
        $parentObject = $dataObject->parentObject;
        $table = $this->tables[$dataObject->name];
        
        $tableName = $table->name;
        // Select 
        $selectExpression = $table->makeSelectExpression();
        
        // Where
        $whereExpressionParts = array('1=1');
        $relation = $this->getRelation($parentObject->name, $table->name);
        if($relation) {
            $whereExpressionParts[] = $relation->makeRelationExpression($table, $parentObject);
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
        $orderByExpression = $table->makeOrderByExpression();
        
        $query  = "SELECT " . $selectExpression;
        $query .= " FROM  " . $tableName;
        $query .= " WHERE " . $whereExpression;
        $query .= " ORDER BY " . $orderByExpression;
        $query .= " LIMIT " . $this->limit;
        
        //echo "<pre>DAS = " . print_r($this,true) . "</pre>";
        //echo "<pre>QUERY = " . $query . "</pre>";
        $statement = $this->pdo->query($query);
        $results = array();
        while($result = $statement->fetch(PDO::FETCH_ASSOC)) {
            $results[] = $result;
        }
        return $results;
    }
    
    public function saveData($dataObject)
    {
        if($dataObject->isCreated) {
            $this->insertData($dataObject);
        } elseif ($dataObject->isUpdated && count($dataObject->updates) > 0) {
            $this->updateData($dataObject);
        }
        return true;
    }
    
    //*************************************************************************
    // PRIVATE FUNCTIONS
    //*************************************************************************
    private function insertData($dataObject)
    {
        $parentObject = $dataObject->parentObject;
       
        $tableName = $dataObject->name;
        $table = $this->tables[$tableName];
        
        //UPDATE
        $columnsExpression = $table->makeColumnsExpression();
        $insertExpression = $table->makeInsertExpression($dataObject);
    
        $query  = "INSERT INTO " . $tableName;
        $query .= " (" . $columnsExpression . ")";
        $query .= " VALUES (" . $insertExpression . ")";
        
        //echo "<pre>DAS = " . print_r($this,true) . "</pre>";
        //echo "<pre>QUERY = " . $query . "</pre>";
        $this->pdo->query($query);
                
        $this->saveChildObjects($dataObject);
    
    }
    
    private function updateData($dataObject)
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
        $this->pdo->query($query);
        
        $this->saveChildObjects($dataObject);
        
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
        }
    
    }
    
    private function getRelation($parentName, $childName) 
    {
        foreach($this->relations as $relationName => $relation) {
            if($relation->parentName == $parentName && $relation->childName == $childName) {
                return $relation;
            }
        }
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
    
    public function addColumn($columnName, $columnType, $enclosed)
    {
        $newColumn = new DataAccessService_Database_Table_Column($columnName, $columnType, $enclosed);
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
    
    public function getKey()
    {
        return $this->primaryKey->columns;
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
    
    public function makeColumnsExpression()
    {
        $columnsExpressionParts = array();
        foreach ($this->columns as $columnName => $column) {
            if(!$column->readonly) {
                $columnsExpressionParts[] = $columnName;
            }
        }
        return implode(', ', $columnsExpressionParts);
    }
    
    public function makeInsertExpression($dataObject) 
    {
        $insertExpressionParts = array();
        foreach ($this->columns as $columnName => $column) {
            if($column->readonly) continue;
            $columnValue = $dataObject->{$columnName};
            if($column->fixed) {
                $columnValue = $column->fixed;
            } 
            $column->makeValueExpression($columnValue);
            $insertExpressionParts[] = $columnValue;
        }
        return implode(', ', $insertExpressionParts);
    }
    
    public function makeSelectExpression() 
    {
        $selectExpressionParts = array();
        foreach ($this->columns as $columnName => $column) {
            // DEFAULT, FIXED
            if($column->fixed) {
                $fixedValue = $column->makeValueExpression($column->fixed);
                $selectExpressionPart = $fixedValue;
            } elseif($column->{'default'}) {
                $defaultValue = $column->makeValueExpression($column->{'default'});
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
        $updates = $dataObject->updates;
        for($i=0; $i<count($updates); $i++) {
            $update = $updates[$i];
            $propertyName = $update->name;
            if(!isset($this->columns[$propertyName])) Die("Property $propertyName not defined in DAS");
            
            $column = $this->columns[$propertyName];
            $columnValue = $column->makeValueExpression($update->valueAfter); 
      
            if($column->{'default'}) {
                $defaultValue = $column->makeValueExpression($column->{'default'});
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
                $keyValue = $keyColumn->makeValueExpression($keyValues[$i]);  
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
            $filterValue = "'" . $this->filterValue . "'";
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
                $keyColumn = $this->columns[$keyColumnName];
                $keyValue = $keyColumn->makeValueExpression($dataObject->{$keyColumnName});
                $updateKeyExpressionParts[] = $keyColumnName . ' = ' . $keyValue;
            }
            $updateKeyExpression = implode(' and ', $updateKeyExpressionParts);
            return $updateKeyExpression;
        }
    }
    
    public function makeOrderByExpression() 
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
    public $enclosed;
    
    public function DataAccessService_Database_Table_Column($name, $type, $enclosed)
    {
        $this->name = $name;
        $this->type = $type;
        $this->enclosed = $enclosed;
    }
    
    public function makeValueExpression($value)
    {
        if($this->enclosed) {
            return "'" . $value . "'";
        } else {
            return $value;
        }
    }
}

class DataAccessService_Database_Relation
{
    public $name;
    public $parentName;
    public $childName;
    public $parentColumns;
    public $childColumns;
    
    function DataAccessService_Database_Relation($parentName, $childName, $parentColumns, $childColumns, $name) 
    {
        $this->name = $name;
        $this->parentName = $parentName;
        $this->childName = $childName;
        $this->parentColumns = $parentColumns;
        $this->childColumns = $childColumns;
    } 

    function makeRelationExpression($table, $parentObject)
    {
        $relationExpressionParts = array();
        $childColumns = explode(' ', $this->childColumns);
        $parentColumns = explode(' ', $this->parentColumns);
        for($i=0; $i<count($childColumns); $i++) {
            $childColumnName = $childColumns[$i];
            $parentColumnName = $parentColumns[$i];
            $childColumn = $table->columns[$childColumnName];
            $parentColumnValue = $childColumn->makeValueExpression($parentObject->{$parentColumnName});
            $relationExpressionParts[] = $childColumnName . " = " . $parentColumnValue;
        }
        $relationExpression = implode(' and ', $relationExpressionParts);
        return $relationExpression;
    }
}
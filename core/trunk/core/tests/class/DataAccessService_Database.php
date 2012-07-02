<?php
class DataAccessService_Database  
{
    public $type;
    public $host;
    public $port;
    public $dbname;
    public $user;
    private $password;
    public $datatypes = array();
    public $tables = array();
    public $relations = array();
    private $limit;
    private $pdo;
    
    public function DataAccessService_Database(
        $driver,
        $host, 
        $port,
        $dbname,
        $user, 
        $password, 
        $options
    ) 
    {
        $this->type = 'database';
        $this->driver = $driver;
        $this->host = $host;
        $this->dbname = $dbname;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        
        $dsn = sprintf(
            '%s:host=%s;dbname=%s;port=%s', 
            $driver, 
            $host, 
            $dbname, 
            $port
        );
        
        $this->pdo = new pdo($dsn, $user, $password, $options);
        $this->limit = 500;
    }
    
    public function addDatatype($typeName, $typeDef, $enclosed = true) 
    {
        $newDatatype = new DataAccessService_Database_Datatype(
            $typeName, 
            $typeDef, 
            $enclosed 
        );
        
        $this->datatypes[$typeName] = $newDatatype;
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
        
        // Select 
        $selectExpression = $this->makeSelectExpression($table);
        
        // FROM
        $tableName = $table->name;
        
        // Where
        $whereExpressionParts = array('1=1');
        $relation = $this->getRelation($parentObject->name, $table->name);
        if($relation) {
            $whereExpressionParts[] = $this->makeRelationExpression($relation, $table, $parentObject);
        }
        $keyExpression = $this->makeSelectKeyExpression($table);
        if($keyExpression) {
            $whereExpressionParts[] = $keyExpression;
        }
        $filterExpression = $this->makeFilterExpression($table);
        if($filterExpression) {
            $whereExpressionParts[] = $filterExpression;
        }
        $whereExpression = implode(' and ', $whereExpressionParts);
        
        // Order
        $orderByExpression = $this->makeOrderByExpression($table);
        
        $query  = "SELECT " . $selectExpression;
        $query .= " FROM  " . $tableName;
        $query .= " WHERE " . $whereExpression;
        $query .= " ORDER BY " . $orderByExpression;
        $query .= " LIMIT " . $this->limit;
        
        //echo "<pre>DAS = " . print_r($this,true) . "</pre>";
        //echo "<pre>QUERY = " . $query . "</pre>";
        $statement = $this->pdo->query($query);
        if(!$statement) {
            $this->throwQueryException($query);
        }

        $results = array();
        while($result = $statement->fetch(PDO::FETCH_ASSOC)) {
            $results[] = $result;
        }
        return $results;
    }
    
    public function saveData($dataObject)
    {
        try {
            if($dataObject->isCreated) {
                $this->insertData($dataObject);
            } elseif ($dataObject->isUpdated && count($dataObject->updates) > 0) {
                $this->updateData($dataObject);
            }
        } catch (maarch\Exception $e) {
            throw $e;
        }
        return true;
    }
    
    //*************************************************************************
    // PRIVATE SQL QUERY CREATION FUNCTIONS
    //*************************************************************************
    private function makeSelectExpression($table) 
    {
        $selectExpressionParts = array();
        foreach ($table->columns as $columnName => $column) {
            // DEFAULT, FIXED
            if($column->fixed) {
                $fixedValue = $this->makeValueExpression($column, $column->fixed);
                $selectExpressionPart = $fixedValue;
            } elseif($column->{'default'}) {
                $defaultValue = $this->makeValueExpression($column, $column->{'default'});
                $selectExpressionPart = "COALESCE(" . $table->name . "." . $column->name . ", " . $defaultValue . ") AS " . $column->name;
            } else {
                $selectExpressionPart = $table->name . "." . $column->name;
            }
            $selectExpressionParts[] = $selectExpressionPart;
        }
        return implode(', ', $selectExpressionParts);
    }
    
    private function makeValueExpression($column, $value)
    {
        $columnType = $this->datatypes[$column->type];
        if($columnType->enclosed) {
            return "'" . $value . "'";
        } else {
            return $value;
        }
    }
    
    private function makeRelationExpression($relation, $table, $parentObject)
    {
        $relationExpressionParts = array();
        $childColumns = explode(' ', $relation->childColumns);
        $parentColumns = explode(' ', $relation->parentColumns);
        for($i=0; $i<count($childColumns); $i++) {
            $childColumnName = $childColumns[$i];
            $parentColumnName = $parentColumns[$i];
            $childColumn = $table->columns[$childColumnName];
            $parentColumnValue = $this->makeValueExpression($childColumn, $parentObject->{$parentColumnName});
            $relationExpressionParts[] = $childColumnName . " = " . $parentColumnValue;
        }
        $relationExpression = implode(' and ', $relationExpressionParts);
        return $relationExpression;
    }
    
    private function makeSelectKeyExpression($table) 
    {
        $selectKeyExpressionParts = array();
        if(isset($table->primaryKey) && !is_null($table->primaryKey)
            && isset($table->keyValue) && !is_null($table->keyValue)) {
            $keyColumns = $table->primaryKey->getColumns();
            $keyValues = explode(' ', $table->keyValue);
            for($i=0; $i<count($keyColumns); $i++) {
                $keyColumnName = $keyColumns[$i];
                $keyColumn = $table->columns[$keyColumnName];
                $keyValue = $this->makeValueExpression($keyColumn, $keyValues[$i]);  
                $selectKeyExpressionParts[] = $table->name . '.' . $keyColumnName . '=' . $keyValue;
            }
            $selectKeyExpression = implode(' and ', $selectKeyExpressionParts);
            return $selectKeyExpression;
        }
    }
    
    private function makeFilterExpression($table)
    {
        $filterExpressionParts = array();
        if(isset($table->filter) && !is_null($table->filter)
            && isset($table->filterValue) && !is_null($table->filterValue)) {       
            $filterColumns = explode(' ', $table->filter);
            $filterValue = "'" . $table->filterValue . "'";
            for($i=0; $i<count($filterColumns); $i++) {
                $filterColumnName = $filterColumns[$i];
                $filterColumn = $table->columns[$filterColumnName];
                $filterExpressionParts[] = "upper(" . $this->name . '.' . $filterColumnName . ') like upper(' . $filterValue . ')';
            }
            $filterExpression = implode(' or ', $filterExpressionParts);
            return $filterExpression;
        }
    }
  
    private function makeOrderByExpression($table)
    {
        if(!is_null($table->order)) {
            return $table->order;
        } elseif(isset($table->primaryKey) && !is_null($table->primaryKey))  {
            $orderElementsComa = implode(', ', $table->primaryKey->getColumns());
            return $orderElementsComa .' ASC';
        }
    }  
  
    private function makeColumnsExpression($table)
    {
        $columnsExpressionParts = array();
        foreach ($table->columns as $columnName => $column) {
            if(!$column->readonly) {
                $columnsExpressionParts[] = $columnName;
            }
        }
        return implode(', ', $columnsExpressionParts);
    }
    
    private function makeInsertExpression($table, $dataObject) 
    {
        $insertExpressionParts = array();
        foreach ($table->columns as $columnName => $column) {
            if($column->readonly) continue;
            $columnValue = $dataObject->{$columnName};
            if($column->fixed) {
                $columnValue = $column->fixed;
            } 
            $this->makeValueExpression($column, $columnValue);
            $insertExpressionParts[] = $columnValue;
        }
        return implode(', ', $insertExpressionParts);
    }
    
    private function makeUpdateExpression($table, $dataObject)
    {
        $updates = $dataObject->updates;
        for($i=0; $i<count($updates); $i++) {
            $update = $updates[$i];
            $propertyName = $update->name;
            if(!isset($table->columns[$propertyName])) Die("Property $propertyName not defined in DAS");
            
            $column = $table->columns[$propertyName];
            $columnValue = $this->makeValueExpression($column, $update->valueAfter); 
      
            if($column->{'default'}) {
                $defaultValue = $this->makeValueExpression($column, $column->{'default'});
                $updateExpressionPart = $column->name . " = COALESCE(" . $columnValue . ", " . $defaultValue . ")";
            } else {
                $updateExpressionPart = $column->name . " = " . $columnValue; 
            }
            $updateExpressionParts[] = $updateExpressionPart;
        }
        return implode(', ', $updateExpressionParts);
    }
    
    private function makeUpdateKeyExpression($table, $dataObject)
    {
        $updateKeyExpressionParts = array();
        if(isset($table->primaryKey) && !is_null($table->primaryKey)) {
            $keyColumns = $table->primaryKey->getColumns();
            for($i=0; $i<count($keyColumns); $i++) {
                $keyColumnName = $keyColumns[$i];
                $keyColumn = $table->columns[$keyColumnName];
                $keyValue = $this->makeValueExpression($keyColumn, $dataObject->{$keyColumnName});
                $updateKeyExpressionParts[] = $keyColumnName . ' = ' . $keyValue;
            }
            $updateKeyExpression = implode(' and ', $updateKeyExpressionParts);
            return $updateKeyExpression;
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
    
    
    //*************************************************************************
    // PRIVATE SQL QUERY EXECUTION FUNCTIONS
    //*************************************************************************
    private function insertData($dataObject)
    {
        $parentObject = $dataObject->parentObject;
       
        $table = $this->tables[$dataObject->name];
        
        // INSERT INTO
        $tableName = $dataObject->name;
        // COLUMNS
        $columnsExpression = $this->makeColumnsExpression($table);
        // VALUES
        $insertExpression = $this->makeInsertExpression($table, $dataObject);
        
        $query  = "INSERT INTO " . $tableName;
        $query .= " (" . $columnsExpression . ")";
        $query .= " VALUES (" . $insertExpression . ")";
        
        //echo "<pre>DAS = " . print_r($this,true) . "</pre>";
        //echo "<pre>QUERY = " . $query . "</pre>";
        $result = $this->pdo->query($query);
        if(!$result) {
            $this->throwQueryException($query);
        }
        
        $this->saveChildObjects($dataObject);

        return true;
        
    }
    
    private function updateData($dataObject)
    {
        $parentObject = $dataObject->parentObject;  
        $table = $this->tables[$dataObject->name];
        
        //UPDATE
        $tableName = $dataObject->name;
        
        // COLUMNS / VALUES
        $updateExpression = $this->makeUpdateExpression($table, $dataObject);

        // Key
        $keyExpression = $this->makeUpdateKeyExpression($table, $dataObject);
    
        $query  = "UPDATE " . $tableName;
        $query .= " SET  " . $updateExpression;
        $query .= " WHERE " . $keyExpression;
        
        //echo "<pre>DAS = " . print_r($this,true) . "</pre>";
        //echo "<pre>QUERY = " . $query . "</pre>";
        $result = $this->pdo->query($query);
        if(!$result) {
            $this->throwQueryException($query);
        }
        
        $this->saveChildObjects($dataObject);

        return true;
        
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
    
    private function throwQueryException($query)
    {
        require_once 'core/tests/class/MessageController.php';
        require_once 'core/tests/class/Message.php';
        require_once 'core/tests/class/Exception.php';
        $messageController = new MessageController();
        $messageController->loadMessageFile('core/xml/DataAccessService_Messages.xml');
        $sqlError = $this->pdo->errorInfo();
        $message = $messageController->createMessage(
            __CLASS__ . '::queryError',
            false,
            array(
                $sqlError[0],
                $sqlError[1],
                $sqlError[2],
                $query
            )
        );
        throw new maarch\Exception($message);
    }
    
}
class DataAccessService_Database_Datatype
{
    public $name;
    public $sqltype;
    public $enclosed;
    
    public function DataAccessService_Database_Datatype($name, $type, $enclosed)
    {
        $this->name = $name;
        $this->sqltype = $type;
        $this->enclosed = $enclosed;
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
    
    function DataAccessService_Database_Relation($parentName, $childName, $parentColumns, $childColumns, $name) 
    {
        $this->name = $name;
        $this->parentName = $parentName;
        $this->childName = $childName;
        $this->parentColumns = $parentColumns;
        $this->childColumns = $childColumns;
    } 

}
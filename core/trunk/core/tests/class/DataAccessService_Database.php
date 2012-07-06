<?php
class DataAccessService_Database  
    extends DataAccessService
{
    
    public $host;
    public $port;
    public $dbname;
    public $user;
    private $password;
    private $limit;
    private $databaseObject;
    
    public function DataAccessService_Database(
        $name,
        $driver,
        $host, 
        $port,
        $dbname,
        $user, 
        $password
    ) 
    {
        $this->type = 'database';
        $this->driver = $driver;
        $this->host = $host;
        $this->port = $port;
        $this->dbname = $dbname;
        $this->user = $user;
        $this->password = $password;
        
        $params = array(
            'server' => $this->host,
            'databasetype' => strtoupper($this->driver),
            'user' => $this->user,
            'pass' => $this->password,
            'port' => $this->port,
            'base' => $this->dbname
        );
        
        $this->databaseObject = new dbquery($params);
        $this->databaseObject->connect();
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
    
    public function addRelation($parentName, $childName, $parentColumns, $childColumns, $name=false) 
    {
        if(!$name) {
            $name = $parentName . '_' . $childName . '_FK';
        }
        $newRelation = new DataAccessService_Database_Relation($parentName, $childName, $parentColumns, $childColumns, $name);
        $this->relations[$name] = $newRelation;
    }
    
    public function getTable($tableName)
    {
        return $this->tables[$tableName];
    }
    
    public function getData($dataObject) 
    {
        try {
            $results = $this->selectData($dataObject);
            return $results;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    public function saveData($dataObject)
    {
        try {
            if($dataObject->isCreated) {
                $this->insertData($dataObject);
            } elseif ($dataObject->isUpdated && count($dataObject->updates) > 0) {
                $this->updateData($dataObject);
            }
        } catch (Exception $e) {
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
                $filterExpressionParts[] = "upper(" . $table->name . '.' . $filterColumnName . ') like upper(' . $filterValue . ')';
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
     
    //*************************************************************************
    // PRIVATE SQL QUERY EXECUTION FUNCTIONS
    //*************************************************************************
    private function selectData($dataObject)
    {
        $parentObject = $dataObject->parentObject;
        
        $tableName = $dataObject->name;
        
        $table = $this->tables[$tableName];
        
        // Select 
        $selectExpression = $this->makeSelectExpression($table);
        
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
        echo "<pre>QUERY = " . print_r($query,true) . "</pre>";
        try {
            $this->databaseObject->query($query);
        } catch (Exception $e) {
            throw $e;
        }

        $results = array();
        while($result = $this->databaseObject->fetch_assoc()) {
            $results[] = $result;
        }
        return $results;
    }
    
    private function insertData($dataObject)
    {
        $parentObject = $dataObject->parentObject;
        
        $tableName = $dataObject->name;
        
        $table = $this->tables[$tableName];
        
        // COLUMNS
        $columnsExpression = $this->makeColumnsExpression($table);
        // VALUES
        $insertExpression = $this->makeInsertExpression($table, $dataObject);
        
        $query  = "INSERT INTO " . $tableName;
        $query .= " (" . $columnsExpression . ")";
        $query .= " VALUES (" . $insertExpression . ")";
        
        //echo "<pre>DAS = " . print_r($this,true) . "</pre>";
        //echo "<pre>QUERY = " . $query . "</pre>";
        try {
            $this->databaseObject->query($query);
        } catch (Exception $e) {
            throw $e;
        }
        
        $this->saveChildObjects($dataObject);

        return true;
        
    }
    
    private function updateData($dataObject)
    {
        $parentObject = $dataObject->parentObject;  
        
        $tableName = $dataObject->name;
        
        $table = $this->tables[$tableName];
        
     
        // COLUMNS / VALUES
        $updateExpression = $this->makeUpdateExpression($table, $dataObject);

        // Key
        $keyExpression = $this->makeUpdateKeyExpression($table, $dataObject);
    
        $query  = "UPDATE " . $tableName;
        $query .= " SET  " . $updateExpression;
        $query .= " WHERE " . $keyExpression;
        
        //echo "<pre>DAS = " . print_r($this,true) . "</pre>";
        //echo "<pre>QUERY = " . $query . "</pre>";
        try {
            $this->databaseObject->query($query);
        } catch (Exception $e) {
            throw $e;
        }
        
        $this->saveChildObjects($dataObject);

        return true;
        
    }
    
    private function throwDatabaseException($query)
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
    extends DataAccessService_Datatype
{
    
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
    extends DataAccessService_Table
{

    public $order;

    public function addPrimaryKey($columns, $name=false)
    {
        if(!$name) $name = $this->name . '_pkey';
        $this->primaryKey = new DataAccessService_Database_PrimaryKey($columns, $name);
    }
    
    public function addColumn($columnName, $columnType)
    {
        $newColumn = new DataAccessService_Database_Column($columnName, $columnType);
        $this->columns[$columnName] = $newColumn;
        return $newColumn;
    }
    
    public function setOrder($orderElements, $orderMode)
    {
        $orderElementsComa = implode(', ', explode(' ', $orderElements));
        if($orderMode == 'ascending') $orderMode = 'ASC';
        else $orderMode = 'DESC';
        $this->order = $orderElementsComa . ' ' . $orderMode;
    }
        
}

class DataAccessService_Database_PrimaryKey
    extends DataAccessService_PrimaryKey
{

}

class DataAccessService_Database_Column
    extends DataAccessService_Column
{
   
}

class DataAccessService_Database_Relation
    extends DataAccessService_Relation
{ 

}
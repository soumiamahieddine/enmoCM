<?php
class DataAccessService_Database  
    extends SchemaController
{

    private $databaseObject;
    
    public function connect($sourceNode) 
    {
        $this->type = 'database';
        $params = array(
            'server' => $sourceNode->getAttribute('host'),
            'databasetype' => strtoupper($sourceNode->getAttribute('driver')),
            'user' => $sourceNode->getAttribute('user'),
            'pass' => $sourceNode->getAttribute('password'),
            'port' => $sourceNode->getAttribute('port'),
            'base' => $sourceNode->getAttribute('dbname')
        );
        
        $this->databaseObject = new dbquery($params);
        $this->databaseObject->connect();
        $this->limit = 500;
    }
    
    public function loadData(
        $objectElement, 
        $parentObject, 
        $key, 
        $filter, 
        $sort, 
        $order = 'ASC', 
        $limit=500) 
    {
        $objectName = $objectElement->getAttribute('name');
        
        if(get_class($parentObject) == 'DataObjectDocument') {
            // Result will be document element == no data parent
            $dataObjectDocument = $parentObject;
        } else {
            //echo "<br/>parent object is not document, but " . $parentObject->tagName;
            $dataObjectDocument = $parentObject->ownerDocument;
        }
        
        // CREATE SELECT QUERY
        $selectParts = array();
        $selectExpression = $this->getSelectColumnsExpression($objectElement);
        //echo "<br/>selectExpression = " . $selectExpression;
        $selectParts[] = "SELECT " . $selectExpression;
        
        $tableExpression = $this->getTableExpression($objectElement);
        //echo "<br/>tableExpression = " . $tableExpression;
        $selectParts[] = "FROM " . $tableExpression;
        
        $whereParts = array();
        if(count($key) > 0) {
            $keyExpression = $this->getSelectKeyExpression($objectElement);
            foreach($key as $i => $keyValue) {
                $keyExpression = str_replace('$'.$i, $keyValue, $keyExpression);
            }
            //echo "<br/>keyExpression = " . $keyExpression;
            $whereParts[] = $keyExpression;
        }
        
        if(get_class($parentObject) != 'DataObjectDocument') {
            $relationExpression = $this->getRelationExpression($objectElement, $parentObject->tagName);
            //echo "<br/>relationExpression = " . $relationExpression;
            if($relationExpression) {
                preg_match('/\$\w+/', $relationExpression, $params);
                foreach($params as $paramName) {
                    $attrName = substr($paramName, 1);
                    $value = $parentObject->$attrName;
                    $relationExpression = str_replace($paramName, $value, $relationExpression);
                }
                $whereParts[] = $relationExpression;
            }
        }
        
        if(count($whereParts) > 0) {
            $selectParts[] = "WHERE " . implode(' and ', $whereParts);
        }
        
        $selectQuery = implode(' ', $selectParts);
        echo "<br/>Select query = " . $selectQuery;
       
        try {
            $this->databaseObject->query($selectQuery);
        } catch (Exception $e) {
            throw $e;
        }
        
        //echo "<br/>Create child object $objectName";
        while($recordSet = $this->databaseObject->fetch_object()) {
            $dataObject = $dataObjectDocument->createDataObject($objectName);
            $parentObject[] = $dataObject;
            foreach($recordSet as $columnName => $columnValue) {
                $dataObject->$columnName = $columnValue;
            } 
            $dataObject->logRead();
        }
    }
    
    public function saveData($dataObject)
    {
        try {
            if($dataObject->isCreated()) {
                $this->insertData($dataObject);
            } elseif ($dataObject->isUpdated() && count($dataObject->getUpdates()) > 0) {
                $this->updateData($dataObject);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return true;
    }
    
    //*************************************************************************
    // PRIVATE SQL QUERY EXECUTION FUNCTIONS
    //*************************************************************************
    
    private function insertData($dataObject)
    {
        $objectName = $dataObject->tagName;
        $objectElement = $this->getObjectElement($objectName);

        $dataObjectDocument = $dataObject->ownerDocument;
        
        // CREATE INSERT QUERY
        $insertParts = array();
        $tableExpression = $this->getTableExpression($objectElement);
        $insertParts[] = "INSERT INTO " . $tableExpression;
                
        $insertColumnsExpression = $this->getInsertColumnsExpression($objectElement, $dataObject);
        //echo "<br/>selectExpression = " . $selectExpression;
        $insertParts[] = "(" . $insertColumnsExpression . ")";
        
        $insertValuesExpression = $this->createInsertValuesExpression($objectElement, $dataObject);
        $insertParts[] = "VALUES (" . $insertValuesExpression . ")";
        
        $insertQuery = implode(' ', $insertParts);
        
        echo "<br/>INSERT QUERY = $insertQuery";
        
        /*try {
            $this->databaseObject->query($insertQuery);
        } catch (Exception $e) {
            throw $e;
        }*/
       
    }
    
    private function updateData($dataObject)
    {
        $parentObject = $dataObject->parentObject;  
        
        $tableName = $dataObject->name;
        
        $table = $this->tables[$tableName];
        
     
        // COLUMNS / VALUES
        $updateExpression = $this->createUpdateExpression($table, $dataObject);

        // Key
        $keyExpression = $this->createUpdateKeyExpression($table, $dataObject);
    
        $query  = "UPDATE " . $tableName;
        $query .= " SET  " . $updateExpression;
        $query .= " WHERE " . $keyExpression;
        
        //echo "<pre>DAS = " . print_r($this,true) . "</pre>";
        echo "<pre>QUERY = " . $query . "</pre>";exit;
        try {
            $this->databaseObject->query($query);
        } catch (Exception $e) {
            throw $e;
        }
        
        $this->saveChildObjects($dataObject);

        return true;
        
    }
    
    //*************************************************************************
    // PRIVATE QUERY CREATION FUNCTIONS
    //*************************************************************************
    
    // SELECT COLUMNS 
    //*************************************************************************
    private function getSelectColumnsExpression($objectElement)
    {
        $objectName = $objectElement->getAttribute('name');
        if(!isset($this->selectColumnsExpressions[$objectName])) {
            $this->selectColumnsExpressions[$objectName] = $this->createSelectColumnsExpression($objectElement);
        }
        return $this->selectColumnsExpressions[$objectName];
    }
    
    private function createSelectColumnsExpression($objectElement)
    {
        //$objectType = $this->getType($objectElement);
        $selectColumns = array();
        
        $properties = $this->getProperties($objectElement);
        
        for($i=0; $i< count($properties); $i++) {
            $property = $properties[$i];
            $columnAlias = $property->getAttribute('name');
            if($property->hasAttribute('das:column')) {
                $columnName = $property->getAttribute('das:column');
            } else {
                $columnName = $columnAlias;
            }
            $propertyType = $this->getType($property);
            if($propertyType->getAttribute('das:enclosed') == 'true') {
                $enclosure = "'";
            }
            
            if($property->hasAttribute('fixed')) {
                $selectColumn = $enclosure . $property->getAttribute('fixed') . $enclosure . " AS " . $columnAlias; 
            } elseif($property->hasAttribute('default')) {
                $selectColumn = "COALESCE (" . $columnName . ", " . $enclosure . $property->getAttribute('default') . $enclosure . ") AS " . $columnAlias; 
            } else {
                $selectColumn = $columnName . " AS " . $columnAlias; 
            }
            $selectColumns[] =  $selectColumn;
        }
        return implode(', ', $selectColumns);
    }
    
    // TABLE 
    //*************************************************************************
    private function getTableExpression($objectElement)
    {
        $objectName = $objectElement->getAttribute('name');
        if(!isset($this->tableExpressions[$objectName])) {
            $this->tableExpressions[$objectName] = $this->createTableExpression($objectElement);
        }
        return $this->tableExpressions[$objectName];
    }
    
    private function createTableExpression($objectElement)
    {
        if($objectElement->hasAttribute('das:view')) {
            $table = '';
        } else {
            $table = $this->getTableName($objectElement);
        }
        return $table;
    }
    
    // KEY 
    //*************************************************************************
    private function getSelectKeyExpression($objectElement)
    {
        $objectName = $objectElement->getAttribute('name');
        if(!isset($this->selectKeyExpressions[$objectName])) {
            $this->selectKeyExpressions[$objectName] = $this->createSelectKeyExpression($objectElement);
        }
        return $this->selectKeyExpressions[$objectName];
    }
    
    private function createSelectKeyExpression($objectElement)
    {
        $key = $this->getKey($objectElement);
        $keyFields = $this->getKeyFields($key);
        $keyExpression = $this->createSelectKeyFieldsExpression($keyFields);
        return $keyExpression;
    }
    
    private function createSelectKeyFieldsExpression($keyFields)
    {
        $keyFieldsLength = $keyFields->length;
        $selectKeyFields = array();
        for($i=0; $i<$keyFieldsLength; $i++) {
            $keyField = $keyFields->item($i);
            $keyAlias = str_replace("@", "", $keyField->getAttribute('xpath'));
            if($keyField->hasAttribute('das:column')) {
                $keyName = $keyField->getAttribute('das:column');
            } else {
                $keyName = $keyAlias;
            }
            if($keyField->getAttribute('das:enclosed') == 'true') {
                $enclosure = "'";
            }
            $selectKeyFields[] = $keyName . " = " . $enclosure . '$' . $i . $enclosure;  
        }
        return implode(' and ', $selectKeyFields);
    }

    // RELATION 
    //*************************************************************************
    private function getRelationExpression($objectElement, $parentName)
    {
        $objectName = $objectElement->getAttribute('name');
        if(!isset($this->relationExpressions[$objectName][$parentName])) {
            $this->relationExpressions[$objectName][$parentName] = $this->createRelationExpression($objectElement, $parentName);
        }
        return $this->relationExpressions[$objectName][$parentName];
    }
    
    private function createRelationExpression($objectElement, $parentName) {
        $relation = $this->getRelation($objectElement, $parentName);
        $fkeyFields = $this->query('./das:foreign-key', $relation);
        $relationExpression = $this->createRelationKeysExpression($fkeyFields);
        return $relationExpression;
    }
     
    private function createRelationKeysExpression($fkeyFields)
    {
        $fkeyFieldsLength = $fkeyFields->length;
        $relationKeys = array();
        for($i=0; $i<$fkeyFieldsLength; $i++) {
            $fkeyField = $fkeyFields->item($i);
            $fkeyAlias = str_replace("@", "", $fkeyField->getAttribute('child-key'));
            if($fkeyField->hasAttribute('column')) {
                $fkeyName = $fkeyField->getAttribute('column');
            } else {
                $fkeyName = $fkeyAlias;
            }
            if($fkeyField->getAttribute('enclosed') == 'true') {
                $enclosure = "'";
            }
            $pkeyName = $fkeyField->getAttribute('parent-key');
            $relationKeys[] = $fkeyName . " = " . $enclosure . '$' . $pkeyName . $enclosure;  
        }
        return implode(' and ', $relationKeys);
    } 
     
    // INSERT COLUMNS 
    //************************************************************************* 
    private function getInsertColumnsExpression($objectElement, $dataObject)
    {
        $objectName = $objectElement->getAttribute('name');
        if(!isset($this->insertColumnsExpressions[$objectName])) {
            $this->insertColumnsExpressions[$objectName] = $this->createInsertColumnsExpression($objectElement, $dataObject);
        }
        return $this->insertColumnsExpressions[$objectName];
    }
    
    private function createInsertColumnsExpression($objectElement, $dataObject)
    {
        $insertColumns = array();
        $properties = $this->getProperties($objectElement); 
        for($i=0; $i< count($properties); $i++) {
            $property = $properties[$i];
            if($property->hasAttribute('das:column')) {
                $columnName = $property->getAttribute('das:column');
            } else {
                $columnName = $property->getAttribute('name');
            }
            $insertColumns[] =  $columnName;
        }
        return implode(', ', $insertColumns);
    }
    
    // INSERT VALUES
    //************************************************************************* 
    private function createInsertValuesExpression($objectElement, $dataObject)
    {
        $insertValues = array();
        $properties = $this->getProperties($objectElement);
        for($i=0; $i< count($properties); $i++) {
            $property = $properties[$i];
            $propertyName = $property->getAttribute('name');
            $propertyType = $this->getType($property);
            if($propertyType->getAttribute('das:enclosed') == 'true') {
                $enclosure = "'";
            }
            $propertyValue = $enclosure . $dataObject->$propertyName . $enclosure; 
            $insertValues[] = $propertyValue;
        }
        return implode(', ', $insertValues);
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


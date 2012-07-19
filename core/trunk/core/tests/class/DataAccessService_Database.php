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
        $objectNode, 
        $parentObject, 
        $key, 
        $filter, 
        $sort, 
        $order = 'ASC', 
        $limit=500) 
    {
        $objectName = $objectNode->getAttribute('name');
        
        if(get_class($parentObject) == 'DataObjectDocument') {
            // Result will be document element == no data parent
            $dataObjectDocument = $parentObject;
        } else {
            //echo "<br/>parent object is not document, but " . $parentObject->tagName;
            $dataObjectDocument = $parentObject->ownerDocument;
        }
        
        // CREATE SELECT QUERY
        $selectParts = array();
        $selectExpression = $this->getSelectExpression($objectNode);
        //echo "<br/>selectExpression = " . $selectExpression;
        $selectParts[] = "SELECT " . $selectExpression;
        
        $tableExpression = $this->getTableExpression($objectNode);
        //echo "<br/>tableExpression = " . $tableExpression;
        $selectParts[] = "FROM " . $tableExpression;
        
        $whereParts = array();
        if(count($key) > 0) {
            $keyExpression = $this->getKeyExpression($objectNode);
            foreach($key as $i => $keyValue) {
                $keyExpression = str_replace('$'.$i, $keyValue, $keyExpression);
            }
            //echo "<br/>keyExpression = " . $keyExpression;
            $whereParts[] = $keyExpression;
        }
        
        if(get_class($parentObject) != 'DataObjectDocument') {
            $relationExpression = $this->getRelationExpression($objectNode, $parentObject->tagName);
            if($relationExpression) {
                foreach($parentObject as $name => $value) {
                    $relationExpression = str_replace('$'.$name, $value, $relationExpression);
                }
                //echo "<br/>relationExpression = " . $relationExpression;
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
            
            $dataObject = $dataObjectDocument->createElement($objectName);
            $parentObject->appendChild($dataObject);
            foreach($recordSet as $columnName => $columnValue) {
                $columnNode = $dataObjectDocument->createElement($columnName, $columnValue);
                $dataObject->appendChild($columnNode);
            } 
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
    // PRIVATE QUERY CREATION FUNCTIONS
    //*************************************************************************
    
    // SELECT COLUMNS 
    //*************************************************************************
    private function getSelectExpression($objectNode)
    {
        $objectName = $objectNode->getAttribute('name');
        if(!isset($this->selectExpressions[$objectName])) {
            $this->selectExpressions[$objectName] = $this->createSelectExpression($objectNode);
        }
        return $this->selectExpressions[$objectName];
    }
    
    private function createSelectExpression($objectNode)
    {
        ////echo "<br/>Getting object type";
        $objectType = $this->getType($objectNode);
        ////echo $objectType->getAttribute('name');
        $parts = array();
        
        ////echo "<br/>Getting type contents for select expression ";
        //simpleContent, complexContent, group, all, choice, sequence, attribute, attributeGroup, anyAttribute
        $complexTypeSequence = $this->query('./xsd:sequence', $objectType)->item(0);
        $parts[] = $this->createSequenceSelectExpression($complexTypeSequence);
        
        
        /*$complexTypeSimpleContent = $this->query('./xsd:simpleContent', $objectType);
        $complexTypeComplexContent = $this->query('./xsd:complexContent', $objectType);
        $complexTypeGroup = $this->query('./xsd:group', $objectType);
        $complexTypeAll = $this->query('./xsd:all', $objectType);
        $complexTyp//echoice = $this->query('./xsd:choice', $objectType);
        $complexTypeAttributes = $this->query('./xsd:attribute', $objectType);

        $complexTypeAttributeGroup = $this->query('./xsd:attributeGroup', $objectType);
        $complexTypeAnyAttribute = $this->query('./xsd:anyAttribute', $objectType);*/
        
        return implode(', ', $parts);
        
    }
    
    private function createSequenceSelectExpression($sequence)
    {
        ////echo "<br/>createSequenceSelectExpression()";
        $parts = array();
        //any, choice, element, group, sequence
        /*$sequenceAny = $this->query('./xsd:any', $sequence);
        $sequenc//echoice = $this->query('./xsd:choice', $sequence);
        $sequenceGroup = $this->query('./xsd:group', $sequence);
        $sequenceSequence = $this->query('./xsd:sequence', $sequence);*/
        
        $sequenceElements = $this->query('./xsd:element', $sequence);
        ////echo "<br/>Found $sequenceElements->length elements in sequence";
        $parts[] = $this->createElementsSelectExpression($sequenceElements);
        return implode(', ', $parts);
    
    }
    
    private function createElementsSelectExpression($elements) 
    {
        ////echo "<br/>createElementsSelectExpression for $elements->length elements";
        $elementsLength = $elements->length;
        $parts = array();
        for($i=0; $i<$elementsLength; $i++) {
            $element = $elements->item($i);
            if($element->hasAttribute('ref')) {
                $element = $this->getRefElement($element->getAttribute('ref'));
            } 
            if($part = $this->createElementSelectExpression($element)) {
                $parts[] = $part;
            }
        }
        
        return implode(', ', $parts);
    }
    
    private function createElementSelectExpression($element)
    {
        ////echo "<br/>createElementSelectExpression for " . $element->getAttribute('name');
        $elementType = $this->getType($element);
        if($elementType->tagName == 'xsd:simpleType') {
            $columnAlias = $element->getAttribute('name');
            if($element->hasAttribute('das:column')) {
                $columnName = $element->getAttribute('das:column');
            } else {
                $columnName = $columnAlias;
            }
            
            if($elementType->getAttribute('das:enclosed') == 'true') {
                $enclosure = "'";
            }
            
            if($element->hasAttribute('fixed')) {
                $expr = $enclosure . $element->getAttribute('fixed') . $enclosure . " AS " . $columnAlias; 
            } elseif($element->hasAttribute('default')) {
                $expr = "COALESCE (" . $columnName . ", " . $enclosure . $element->getAttribute('default') . $enclosure . ") AS " . $columnAlias; 
            } else {
                $expr = $columnName . " AS " . $columnAlias; 
            }
            return $expr;
        }
    }

    // TABLE 
    //*************************************************************************
    private function getTableExpression($objectNode)
    {
        $objectName = $objectNode->getAttribute('name');
        if(!isset($this->tableExpressions[$objectName])) {
            $this->tableExpressions[$objectName] = $this->createTableExpression($objectNode);
        }
        return $this->tableExpressions[$objectName];
    }
    
    private function createTableExpression($objectNode)
    {
        if($objectNode->hasAttribute('das:view')) {
            $expr = '';
        } else {
            $expr = $this->getTableName($objectNode);
        }
        return $expr;
    }
    

    
    // KEY 
    //*************************************************************************
    private function getKeyExpression($objectNode)
    {
        $objectName = $objectNode->getAttribute('name');
        if(!isset($this->keyExpressions[$objectName])) {
            $this->keyExpressions[$objectName] = $this->createKeyExpression($objectNode);
        }
        return $this->keyExpressions[$objectName];
    }
    
    private function createKeyExpression($objectNode)
    {
        $key = $this->getKey($objectNode);
        $keyFields = $this->query('./xsd:field', $key);
        $keyExpression = $this->createKeyFieldsExpression($keyFields);
        return $keyExpression;
    }
    
    private function createKeyFieldsExpression($keyFields)
    {
        $keyFieldsLength = $keyFields->length;
        $parts = array();
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
            $parts[] = $keyName . " = " . $enclosure . '$' . $i . $enclosure;  
        }
        return implode(' and ', $parts);
    }

    // RELATION 
    //*************************************************************************
    private function getRelationExpression($objectNode, $parentName)
    {
        $objectName = $objectNode->getAttribute('name');
        if(!isset($this->relationExpressions[$objectName][$parentName])) {
            $this->relationExpressions[$objectName][$parentName] = $this->createRelationExpression($objectNode, $parentName);
        }
        return $this->relationExpressions[$objectName][$parentName];
    }
    
    private function createRelationExpression($objectNode, $parentName) {
        $relation = $this->getRelation($objectNode, $parentName);
        $fkeyFields = $this->query('./das:foreign-key', $relation);
        $relationExpression = $this->createRelationKeysExpression($fkeyFields);
        return $relationExpression;
    }
     
    private function createRelationKeysExpression($fkeyFields)
    {
        $fkeyFieldsLength = $fkeyFields->length;
        $parts = array();
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
            $parts[] = $fkeyName . " = " . $enclosure . '$' . $pkeyName . $enclosure;  
        }
        return implode(' and ', $parts);
    } 
     
    //*************************************************************************
    // PRIVATE SQL QUERY EXECUTION FUNCTIONS
    //*************************************************************************
    
    private function insertData($dataObject)
    {
        $parentObject = $dataObject->parentObject;
        
        $tableName = $dataObject->name;
        
        $table = $this->tables[$tableName];
        
        // COLUMNS
        $columnsExpression = $this->createColumnsExpression($table);
        // VALUES
        $insertExpression = $this->createInsertExpression($table, $dataObject);
        
        $query  = "INSERT INTO " . $tableName;
        $query .= " (" . $columnsExpression . ")";
        $query .= " VALUES (" . $insertExpression . ")";
        
        //////echo "<pre>DAS = " . print_r($this,true) . "</pre>";
        //////echo "<pre>QUERY = " . $query . "</pre>";
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
        $updateExpression = $this->createUpdateExpression($table, $dataObject);

        // Key
        $keyExpression = $this->createUpdateKeyExpression($table, $dataObject);
    
        $query  = "UPDATE " . $tableName;
        $query .= " SET  " . $updateExpression;
        $query .= " WHERE " . $keyExpression;
        
        //////echo "<pre>DAS = " . print_r($this,true) . "</pre>";
        //////echo "<pre>QUERY = " . $query . "</pre>";
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


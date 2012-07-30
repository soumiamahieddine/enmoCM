<?php
class DataAccessService_Database  
    extends DataObjectController
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
        echo "<br/>Read with " . $objectElement->getAttribute('name') . " order $order";
        
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
        if($key) {
            $keyExpression = $this->getSelectKeyExpression($objectElement);
            $keyValues = explode(' ', $key);
            foreach($keyValues as $i => $keyValue) {
                $keyExpression = str_replace('$'.$i, $keyValue, $keyExpression);
            }
            //echo "<br/>keyExpression = " . $keyExpression;
            $whereParts[] = $keyExpression;
        }
        
        if($filter) {
            $filterExpression = $this->getSelectFilterExpression($objectElement);
            $filterExpression = str_replace('$filter', $filter, $filterExpression);
            echo "<br/>filterExpression = " . $filterExpression;
            $whereParts[] = $filterExpression;
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
        
        $selectOrderExpression = $this->getSelectOrderExpression($objectElement, $sort);
        $selectParts[] = "ORDER BY " . $selectOrderExpression . " " . $order;
        
        $selectQuery = implode(' ', $selectParts);
        echo "<br/>Select query = " . $selectQuery;
       
        try {
            $this->databaseObject->query($selectQuery);
        } catch (Exception $e) {
            throw $e;
        }
        
        //echo "<br/>Create object $objectName";
        while($recordSet = $this->databaseObject->fetch_object()) {
            $dataObject = $dataObjectDocument->createDataObject($objectName);
            $parentObject[] = $dataObject;
            foreach($recordSet as $columnName => $columnValue) {
                $dataObject->setAttribute($columnName, $columnValue);
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
        echo "<pre>QUERY = " . $query . "</pre>";
        
        /*try {
            $this->databaseObject->query($query);
        } catch (Exception $e) {
            throw $e;
        }*/

    }
    
    //*************************************************************************
    // PRIVATE QUERY CREATION FUNCTIONS
    //*************************************************************************
    
    // SELECT COLUMNS 
    //*************************************************************************
    private function getSelectColumnsExpression($objectElement)
    {
        if(!$selectColumnsExpression = $this->XRefs->getXRefData($objectElement, 'SelectColumnsExpression')) {
            $selectColumnsExpression = $this->createSelectColumnsExpression($objectElement);
            $this->XRefs->addXRefData($objectElement, 'SelectColumnsExpression', $selectColumnsExpression);
        }
        return $selectColumnsExpression;
    }
    
    private function createSelectColumnsExpression($objectElement)
    {
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
            $propertyType = $this->getSimpleType($property);
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
        if(!$tableExpression = $this->XRefs->getXRefData($objectElement, 'tableExpression')) {
            $tableExpression = $this->createTableExpression($objectElement);
            $this->XRefs->addXRefData($objectElement, 'tableExpression', $tableExpression);
        } 
        return $tableExpression;
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
        if(!$selectKeyExpression = $this->XRefs->getXRefData($objectElement, 'selectKeyExpression')) {
            $selectKeyExpression = $this->createSelectKeyExpression($objectElement);
            $this->XRefs->addXRefData($objectElement, 'selectKeyExpression', $selectKeyExpression);
        }
        return $selectKeyExpression;
    }
    
    private function createSelectKeyExpression($objectElement)
    {
        $key = $this->getKey($objectElement);
        $keyFields = $this->getKeyFields($key);
        //$objectProperties = $this->getProperties($objectElement);
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

    // FILTER 
    //*************************************************************************
    private function getSelectFilterExpression($objectElement)
    {
        if(!$selectFilterExpression = $this->XRefs->getXRefData($objectElement, 'selectFilterExpression')) {
            $selectFilterExpression = $this->createSelectFilterExpression($objectElement);
            $this->XRefs->addXRefData($objectElement, 'selectFilterExpression', $selectFilterExpression);
        }
        return $selectFilterExpression;
    }
    
    private function createSelectFilterExpression($objectElement) {
        if($filter = $this->getFilter($objectElement)) {
            $filterFields = explode(' ', $filter);
            $filterFieldsLength = count($filterFields);
            $filterExpressions = array();
            for($i=0; $i<$filterFieldsLength; $i++) {
                $filterField = $filterFields[$i];
                
                // get attribute or element + type
                
                $enclosure = "'";
                $filterExpressions[] = "UPPER(". $filterField . ") LIKE UPPER(" . $enclosure . '$filter%' . $enclosure . ")";  
            }
            return implode(' or ', $filterExpressions);
        }
    }
    
    // ORDER 
    //*************************************************************************
    private function getSelectOrderExpression($objectElement, $sort=false)
    {
        // No sort fields given, sort on key
        if(!$sort) {
            if(!$selectOrderExpression = $this->XRefs->getXRefData($objectElement, 'selectOrderExpression')) {
                $key = $this->getKey($objectElement);
                $keyFields = $this->getKeyFields($key);
                for($i=0; $i<$keyFields->length; $i++) {
                    $sortFields[] = str_replace("@", "", $keyFields->item($i)->getAttribute('xpath'));
                }
                $selectOrderExpression = $this->createSelectOrderExpression($objectElement, $sortFields);
                $this->XRefs->addXRefData($objectElement, 'selectOrderExpression', $selectOrderExpression);
            }
        } else {
            $sortFields = explode(' ', $sort);
            $selectOrderExpression = $this->createSelectOrderExpression($objectElement, $sortFields);
        }
        return $selectOrderExpression;
    }
    
    private function createSelectOrderExpression($objectElement, $sortFields) {
        $sortFieldsLength = count($sortFields);
        $sortExpressions = array();
        for($i=0; $i<$sortFieldsLength; $i++) {
            $sortField = $sortFields[$i];
            
            // Get column name
            
            $sortExpressions[] = $sortField;  
        }
        return implode(', ', $sortExpressions);
    }


    
    // RELATION 
    //*************************************************************************
    private function getRelationExpression($objectElement, $parentName)
    {
        if(!$relationExpression = $this->XRefs->getXRefData($objectElement, 'relationExpression')) {
            $relationExpression = $this->createRelationExpression($objectElement, $parentName);
            $this->XRefs->addXRefData($objectElement, 'relationExpressions', $relationExpression);
        }
        return $relationExpression;
    }
    
    private function createRelationExpression($objectElement, $parentName) {
        $relation = $this->getRelation($objectElement, $parentName);
        $fkeyFields = $this->query('./das:foreign-key', $relation);
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
            $propertyType = $this->getSimpleType($property);
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


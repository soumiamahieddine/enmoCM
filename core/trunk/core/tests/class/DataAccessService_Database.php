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
    }
    
    public function loadData(
        $objectElement,
        $parentObject,
        $dataObjectDocument,
        $key, 
        $filter, 
        $sortFields, 
        $sortOrder, 
        $limit
        ) 
    {
        
        
        // CREATE SELECT QUERY
        $selectParts = array();
        
        $selectExpression = $this->getSelectColumnsExpression($objectElement);
        $selectParts[] = "SELECT";
        $selectParts[] = $selectExpression;
        
        $tableExpression = $this->getTableExpression($objectElement);
        $selectParts[] = "FROM";
        $selectParts[] = $tableExpression;
        
        $whereParts = array();
        if($key) {
            $keyExpression = $this->getSelectKeyExpression($objectElement);
            $keyValues = explode(' ', $key);
            foreach($keyValues as $i => $keyValue) {
                $keyExpression = str_replace('$'.$i, $keyValue, $keyExpression);
            }
            $whereParts[] = $keyExpression;
        }
        
        if($filter) {
            $filterExpression = $this->getSelectFilterExpression($objectElement);
            $filterExpression = str_replace('$filter', $filter, $filterExpression);
            $whereParts[] = $filterExpression;
        }
        
        if(get_class($parentObject) != 'DataObjectDocument') {
            $relationExpression = $this->getRelationExpression($objectElement, $parentObject);
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
            $selectParts[] = "WHERE";
            $selectParts[] = implode(' and ', $whereParts);
        }
        
        $selectSortFieldsExpression = $this->getSelectSortFieldsExpression($objectElement, $sortFields);
        switch($sortOrder) {
        case 'descending' : 
            $sortOrder = 'DESC';
            break;
        case 'ascending' : 
        default : 
            $sortOrder = 'ASC';
        }
        $selectParts[] = "ORDER BY";
        $selectParts[] = $selectSortFieldsExpression;
        $selectParts[] = $sortOrder;
        
        $selectQuery = implode(' ', $selectParts);
        //echo "<pre>Select query = " . $selectQuery . "</pre>";
       
        try {
            $this->databaseObject->query($selectQuery);
        } catch (Exception $e) {
            throw $e;
        }
        
        //echo "<br/>Create object $objectName";
        $objectName = $this->getObjectName($objectElement);
        while($recordSet = $this->databaseObject->fetch_object()) {
            $childDataObject = $dataObjectDocument->createDataObject($objectName);
            $parentObject[] = $childDataObject;
            $childDataObject->logRead();
            foreach($recordSet as $columnName => $columnValue) {
                $childDataObject->setAttribute($columnName, $columnValue);
            } 
        }
    }
    
    public function saveData($objectElement, $dataObject, $dataObjectDocument)
    {
        try {
            if($dataObject->isCreated()) {
                $this->insertData($objectElement, $dataObject, $dataObjectDocument);
            } elseif ($dataObject->isUpdated()) {
                $this->updateData($objectElement, $dataObject, $dataObjectDocument);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return true;
    }
    
    public function deleteData($objectElement, $key)
    {
        
    }
    
    //*************************************************************************
    // PRIVATE SQL QUERY EXECUTION FUNCTIONS
    //*************************************************************************
    
    private function insertData($objectElement, $dataObject, $dataObjectDocument)
    {
        // CREATE INSERT QUERY
        $insertParts = array();
        
        $tableExpression = $this->getTableExpression($objectElement);
        $insertParts[] = "INSERT INTO";
        $insertParts[] = $tableExpression;
        
        $insertColumnsExpression = $this->getInsertColumnsExpression($objectElement);
        $insertParts[] = "(" . $insertColumnsExpression . ")";
        
        $insertValuesExpression = $this->createInsertValuesExpression($objectElement, $dataObject);
        $insertParts[] = "VALUES";
        $insertParts[] = "(" . $insertValuesExpression . ")";
        
        $insertQuery = implode(' ', $insertParts);
        
        //echo "<br/>INSERT QUERY = $insertQuery";
        
        try {
            $this->databaseObject->query($insertQuery);
        } catch (Exception $e) {
            throw $e;
        }
       
    }
    
    private function updateData($objectElement, $dataObject, $dataObjectDocument)
    {
        $updateParts = array();
        
        $tableExpression = $this->getTableExpression($objectElement);
        $updateParts[] = "UPDATE";
        $updateParts[] = $tableExpression;
        
        // COLUMNS / VALUES
        $updateExpression = $this->createUpdateExpression($objectElement, $dataObject);
        $updateParts[] = "SET";
        $updateParts[] = $updateExpression; 
        
        // Key
        $keyExpression = $this->createUpdateKeyExpression($objectElement, $dataObject);
        $updateParts[] = "WHERE";
        $updateParts[] = $keyExpression; 
        
        $updateQuery = implode(' ', $updateParts);
        
        //echo "<pre>UPDATE QUERY = " . $updateQuery . "</pre>";
        
        try {
            $this->databaseObject->query($updateQuery);
        } catch (Exception $e) {
            throw $e;
        }

    }
    
    //*************************************************************************
    // PRIVATE QUERY CREATION FUNCTIONS
    //*************************************************************************
    
    // SELECT COLUMNS 
    //*************************************************************************
    private function getSelectColumnsExpression($objectElement)
    {
        if(!$selectColumnsExpression = $this->XRefs->getXRefData($objectElement, 'SelectColumnsExpression')) {
            $selectColumns = array();
            $objectProperties = $this->getObjectProperties($objectElement);
            for($i=0; $i< count($objectProperties); $i++) {
                $propertyElement = $objectProperties[$i];
                $columnAlias = $propertyElement->getAttribute('name');
                if($propertyElement->hasAttribute('das:column')) {
                    $columnName = $propertyElement->getAttribute('das:column');
                } else {
                    $columnName = $columnAlias;
                }
                $propertyType = $this->getPropertyType($propertyElement);
                $enclosure = false;
                if($propertyType->getAttribute('das:enclosed') == 'true') {
                    $enclosure = "'";
                }
                if($propertyElement->hasAttribute('fixed')) {
                    $selectColumn = $enclosure . $propertyElement->getAttribute('fixed') . $enclosure . " AS " . $columnAlias; 
                } elseif($propertyElement->hasAttribute('default')) {
                    $selectColumn = "COALESCE (" . $columnName . ", " . $enclosure . $propertyElement->getAttribute('default') . $enclosure . ") AS " . $columnAlias; 
                } else {
                    $selectColumn = $columnName . " AS " . $columnAlias; 
                }
                $selectColumns[] =  $selectColumn;
            }
            $selectColumnsExpression = implode(', ', $selectColumns);
            $this->XRefs->addXRefData($objectElement, 'SelectColumnsExpression', $selectColumnsExpression);
        }
        return $selectColumnsExpression;
    }
    
    // TABLE 
    //*************************************************************************
    private function getTableExpression($objectElement)
    {
        if(!$tableExpression = $this->XRefs->getXRefData($objectElement, 'tableExpression')) {
            if($objectElement->hasAttribute('das:view')) {
                $tableExpression = '';
            } else {
                $tableExpression = $this->getObjectTable($objectElement);
            }
            $this->XRefs->addXRefData($objectElement, 'tableExpression', $tableExpression);
        } 
        return $tableExpression;
    }
    
    // KEY 
    //*************************************************************************
    private function getSelectKeyExpression($objectElement)
    {
        if(!$selectKeyExpression = $this->XRefs->getXRefData($objectElement, 'selectKeyExpression')) {
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
                $enclosure = false;
                if($keyField->getAttribute('das:enclosed') == 'true') {
                    $enclosure = "'";
                }
                $selectKeyFields[] = $keyName . " = " . $enclosure . '$' . $i . $enclosure;  
            }
            $selectKeyExpression =  implode(' and ', $selectKeyFields);
            $this->XRefs->addXRefData($objectElement, 'selectKeyExpression', $selectKeyExpression);
        }
        return $selectKeyExpression;
    }

    // FILTER 
    //*************************************************************************
    private function getSelectFilterExpression($objectElement)
    {
        if(!$selectFilterExpression = $this->XRefs->getXRefData($objectElement, 'selectFilterExpression')) {
            $filterExpressions = array();
            if($filter = $this->getFilter($objectElement)) {
                $filterFields = explode(' ', $filter);
                $filterFieldsLength = count($filterFields);
                for($i=0; $i<$filterFieldsLength; $i++) {
                    $filterField = $filterFields[$i];
                    
                    // get attribute or element + type
                    
                    $enclosure = "'";
                    $filterExpressions[] = "UPPER(". $filterField . ") LIKE UPPER(" . $enclosure . '$filter%' . $enclosure . ")";  
                }
            }
            $selectFilterExpression = implode(' or ', $filterExpressions);
            $this->XRefs->addXRefData($objectElement, 'selectFilterExpression', $selectFilterExpression);
        }
        return $selectFilterExpression;
    }
    
    // ORDER 
    //*************************************************************************
    private function getSelectSortFieldsExpression($objectElement, $sortFields=false)
    {
        // No sort fields given, sort on key
        if(!$sortFields) {
            if(!$selectSortFieldsExpression = $this->XRefs->getXRefData($objectElement, 'selectSortFieldsExpression')) {
                $sortFieldsExpressions = array();
                $key = $this->getKey($objectElement);
                $keyFields = $this->getKeyFields($key);
                for($i=0; $i<$keyFields->length; $i++) {
                    $sortField = str_replace("@", "", $keyFields->item($i)->getAttribute('xpath'));
                    
                    // Get column name
                    
                    $sortFieldsExpressions[] = $sortField;
                }
                $selectSortFieldsExpression = implode(', ', $sortFieldsExpressions);
                $this->XRefs->addXRefData($objectElement, 'selectSortFieldsExpression', $selectSortFieldsExpression);
            }
        } else {
            $sortFieldArray = explode(' ', $sortFields);
            for($i=0; $i<count($sortFieldArray); $i++) {
                // Get column name and type, enclosed ?
                
                $sortFieldsExpressions[] = $sortFieldArray[$i];
                $selectSortFieldsExpression = implode(', ', $sortFieldsExpressions);
            }
        }
        return $selectSortFieldsExpression;
    }

    // RELATION 
    //*************************************************************************
    private function getRelationExpression($objectElement, $dataObject)
    {
        if(!$relationExpression = $this->XRefs->getXRefData($objectElement, 'relationExpression')) {
            $relation = $this->getRelation($objectElement, $dataObject);
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
                $enclosure = false;
                if($fkeyField->getAttribute('enclosed') == 'true') {
                    $enclosure = "'";
                }
                $pkeyName = $fkeyField->getAttribute('parent-key');
                $relationKeys[] = $fkeyName . " = " . $enclosure . '$' . $pkeyName . $enclosure;  
            }
            $relationExpression = implode(' and ', $relationKeys);
            $this->XRefs->addXRefData($objectElement, 'relationExpressions', $relationExpression);
        }
        return $relationExpression;
    }
    
    // INSERT COLUMNS 
    //************************************************************************* 
    private function getInsertColumnsExpression($objectElement)
    {
        
        if(!$insertColumnsExpression = $this->XRefs->getXRefData($objectElement, 'insertColumnsExpression')) {
            $insertColumns = array();
            $objectProperties = $this->getObjectProperties($objectElement); 
            for($i=0; $i< count($objectProperties); $i++) {
                $propertyElement = $objectProperties[$i];
                if($propertyElement->hasAttribute('das:column')) {
                    $columnName = $propertyElement->getAttribute('das:column');
                } else {
                    $columnName = $propertyElement->getAttribute('name');
                }
                $insertColumns[] =  $columnName;
            }
            $insertColumnsExpression = implode(', ', $insertColumns);
            $this->XRefs->addXRefData($objectElement, 'insertColumnsExpression', $insertColumnsExpression);
        }
        return $insertColumnsExpression;
    }
    
    // INSERT VALUES
    //************************************************************************* 
    private function createInsertValuesExpression($objectElement, $dataObject)
    {
        $insertValues = array();
        $objectProperties = $this->getObjectProperties($objectElement);
        for($i=0; $i< count($objectProperties); $i++) {
            $propertyElement = $objectProperties[$i];
            $propertyName = $propertyElement->getAttribute('name');
            $propertyType = $this->getPropertyType($propertyElement);
            $enclosure = false;
            if($propertyType->getAttribute('das:enclosed') == 'true') {
                $enclosure = "'";
            }
            $propertyValue = $enclosure . $dataObject->$propertyName . $enclosure; 
            $insertValues[] = $propertyValue;
        }
        return implode(', ', $insertValues);
    }
    
    // UPDATE COLUMNS 
    //************************************************************************* 
    private function createUpdateExpression($objectElement, $dataObject)
    {
        $updatedProperties = $dataObject->getUpdatedProperties();
        $objectProperties = $this->getObjectProperties($objectElement);
        $updateColumns = array();
        for($i=0; $i< count($objectProperties); $i++) {
            $propertyElement = $objectProperties[$i];
            $propertyName = $propertyElement->getAttribute('name');
            if(in_array($propertyName, $updatedProperties)) {
                $propertyType = $this->getPropertyType($propertyElement);
                $enclosure = false;
                if($propertyType->getAttribute('das:enclosed') == 'true') {
                    $enclosure = "'";
                }
                if($propertyElement->hasAttribute('das:column')) {
                    $columnName = $propertyElement->getAttribute('das:column');
                } else {
                    $columnName = $propertyName;
                }
                $updateColumns[] =  $columnName . " = " . $enclosure . $dataObject->$propertyName . $enclosure; 
            }
        }
        return implode(', ', $updateColumns);
    }
    
    // UPDATE KEY  
    //************************************************************************* 
    private function createUpdateKeyExpression($objectElement, $dataObject)
    {
        $key = $this->getKey($objectElement);
        $keyFields = $this->getKeyFields($key);
        
        $keyFieldsLength = $keyFields->length;
        $updateKeyFields = array();
        for($i=0; $i<$keyFieldsLength; $i++) {
            $keyField = $keyFields->item($i);
            $keyAlias = str_replace("@", "", $keyField->getAttribute('xpath'));
            if($keyField->hasAttribute('das:column')) {
                $keyName = $keyField->getAttribute('das:column');
            } else {
                $keyName = $keyAlias;
            }
            $enclosure = false;
            if($keyField->getAttribute('das:enclosed') == 'true') {
                $enclosure = "'";
            }
            $updateKeyFields[] = $keyName . " = " . $enclosure . $dataObject->$keyAlias . $enclosure;  
        }
        return implode(' and ', $updateKeyFields);
    
    }
    
    // EXCEPTIONS 
    //************************************************************************* 
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


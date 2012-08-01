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
        /*
        $objectProperties = $this->getObjectProperties($objectElement);
        for($i=0; $i<count($query); $i++) {
            $predicat = $query[$i];
            switch($predicat['operator']) {
            case 'starts-with':
                for($j=0; $j<$objectProperties->length; $j++) {
                    $propertyNode = $objectProperties->item($j);
                    $propertyName = str_replace("@", "", $propertyNode->getName());
                    if($propertyName == $predicat['operand']) {
                        $columnName = $propertyNode->getColumn();
                        $propertyType = $this->getType($propertyNode);
                        $enclosure = $propertyType->getEnclosure();
                        $whereParts[] = 
                            "UPPER(" 
                            . $columnName 
                            . ") LIKE UPPER(" 
                            . $enclosure 
                            . $predicat['expression'] 
                            . '%' 
                            . $enclosure 
                            . ")"; 
                    }
                } 
                break;
            case 'contains':
                for($j=0; $j<$objectProperties->length; $j++) {
                    $propertyNode = $objectProperties->item($j);
                    $propertyName = str_replace("@", "", $propertyNode->getName());
                    if($propertyName == $predicat['operand']) {
                        $columnName = $propertyNode->getColumn();
                        $propertyType = $this->getType($propertyNode);
                        $enclosure = $propertyType->getEnclosure();
                        $whereParts[] = 
                            "UPPER(" 
                            . $columnName 
                            . ") LIKE UPPER(" 
                            . $enclosure 
                            . '%' 
                            . $predicat['expression'] 
                            . '%' 
                            . $enclosure 
                            . ")"; 
                    }
                } 
                break;
            case '=':
            default :
                for($j=0; $j<$objectProperties->length; $j++) {
                    $propertyNode = $objectProperties->item($j);
                    $propertyName = str_replace("@", "", $propertyNode->getName());
                    if($propertyName == $predicat['operand']) {
                        $columnName = $propertyNode->getColumn();
                        $propertyType = $this->getType($propertyNode);
                        $enclosure = $propertyType->getEnclosure();
                        $whereParts[] = 
                            $columnName 
                            . " = " 
                            . $enclosure 
                            . $predicat['expression'] 
                            . $enclosure;
                    }
                } 
            }
        }*/
        
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
        echo "<pre>Select query = " . $selectQuery . "</pre>";
       
        try {
            $this->databaseObject->query($selectQuery);
        } catch (Exception $e) {
            throw $e;
        }
        
        //echo "<br/>Create object $objectName";
        $objectName = $objectElement->getName();
        while($recordSet = $this->databaseObject->fetch_object()) {
            $childDataObject = $dataObjectDocument->createDataObject($objectName);
            $parentObject[] = $childDataObject;
            $childDataObject->logRead();
            foreach($recordSet as $columnName => $columnValue) {
                if($columnName[0] == "@") {
                    $columnName = substr($columnName, 1);
                    $childDataObject->setAttribute($columnName, $columnValue);
                } else {
                    $property = $dataObjectDocument->createDataObject($columnName, $columnValue);
                    $childDataObject->appendChild($property);
                }
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
        $tableExpression = $this->getTableExpression($objectElement);
        $deleteParts[] = "DELETE FROM";
        $deleteParts[] = $tableExpression;

        $keyExpression = $this->getSelectKeyExpression($objectElement);
        $keyValues = explode(' ', $key);
        foreach($keyValues as $i => $keyValue) {
            $keyExpression = str_replace('$'.$i, $keyValue, $keyExpression);
        }
        $deleteParts[] = "WHERE";
        $deleteParts[] = $keyExpression;
       
        $deleteQuery = implode(' ', $deleteParts);
        
        //echo "<br/>INSERT QUERY = $insertQuery";
        
        try {
            $this->databaseObject->query($deleteQuery);
        } catch (Exception $e) {
            throw $e;
        }
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
        
        echo "<br/>INSERT QUERY = $insertQuery";
        
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
        
        echo "<pre>UPDATE QUERY = " . $updateQuery . "</pre>";
        
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
            $objectPropertiesLength = $objectProperties->length;
            for($i=0; $i<$objectPropertiesLength; $i++) {
                $propertyNode = $objectProperties->item($i);
                if($propertyNode->tagName == 'xsd:attribute') {
                    $propertyName = '"@' . $propertyNode->getName() . '"';
                } else {
                    $propertyName = '"' . $propertyNode->getName() . '"';
                }
                $columnName = $propertyNode->getColumn();
                $propertyType = $this->getType($propertyNode);
                $enclosure = $propertyType->getEnclosure();
                
                if($propertyNode->hasAttribute('fixed')) {
                    $selectColumn = $enclosure . $propertyNode->getAttribute('fixed') . $enclosure . " AS " . $propertyName; 
                } elseif($propertyNode->hasAttribute('default')) {
                    $selectColumn = "COALESCE (" . $columnName . ", " . $enclosure . $propertyNode->getAttribute('default') . $enclosure . ") AS " . $propertyName; 
                } else {
                    $selectColumn = $columnName . " AS " . $propertyName; 
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
                $tableExpression = $objectElement->getTable();
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
            $keyFields = $this->getKeyFields($objectElement);
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
                $enclosure = $keyField->getEnclosure();
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
            if($filter = $objectElement->getFilter()) {
                $filterFields = explode(' ', $filter);
                $filterFieldsLength = count($filterFields);
                for($i=0; $i<$filterFieldsLength; $i++) {
                    $filterField = $filterFields[$i];
                    
                    // get attribute or element + type
                    
                    $enclosure = "'";
                    $filterExpressions[] = "UPPER(". $filterField . ") LIKE UPPER(" . $enclosure . '$filter' . $enclosure . ")";  
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
                $keyFields = $this->getKeyFields($objectElement);
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
            echo "<br/>Get relation between " . $objectElement->getAttribute('name') . " and " . $dataObject->tagName;
            if($relation = $this->getRelation($objectElement, $dataObject)) {
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
                    $enclosure = $fkeyFiled->getEnclosure();
                    $pkeyName = $fkeyField->getAttribute('parent-key');
                    $relationKeys[] = $fkeyName . " = " . $enclosure . '$' . $pkeyName . $enclosure;  
                }
                $relationExpression = implode(' and ', $relationKeys);
            }
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
            $objectPropertiesLength = $objectProperties->length;
            for($i=0; $i<$objectPropertiesLength; $i++) {
                $propertyElement = $objectProperties->item($i);
                $columnName = $propertyNode->getColumn();
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
        $objectPropertiesLength = $objectProperties->length;
        for($i=0; $i<$objectPropertiesLength; $i++) {
            $propertyElement = $objectProperties->item($i);
            $propertyName = $propertyElement->getName();
            $propertyType = $this->getType($propertyElement);
            $enclosure = $propertyType->getEnclosure();
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
        $updateColumns = array();
        
        $objectProperties = $this->getObjectProperties($objectElement);
        $objectPropertiesLength = $objectProperties->length;
        for($i=0; $i<$objectPropertiesLength; $i++) {
            $propertyNode = $objectProperties->item($i);
            $propertyName = $propertyNode->getName();
            if(in_array($propertyName, $updatedProperties)) {
                $propertyType = $this->getType($propertyNode);
                $enclosure = $propertyType->getEnclosure();
                $columnName = $propertyNode->getColumn();
                $updateColumns[] =  $columnName . " = " . $enclosure . $dataObject->$propertyName . $enclosure; 
            }
        }
        return implode(', ', $updateColumns);
    }
    
    // UPDATE KEY  
    //************************************************************************* 
    private function createUpdateKeyExpression($objectElement, $dataObject)
    {
        $keyFields = $this->getKeyFields($objectElement);
        
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
            $enclosure = $keyField->getEnclosure();
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


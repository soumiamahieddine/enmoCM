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
        
        $selectExpression = $this->getSelectExpression($objectElement);
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
        
        if(is_object($parentObject) && get_class($parentObject) != 'DataObjectDocument') {
            $relationExpression = $this->getRelationExpression($objectElement, $parentObject);
            if($relationExpression) {
                preg_match('/\$\w+/', $relationExpression, $params);
                foreach($params as $paramName) {
                    $attrName = substr($paramName, 1);
                    //echo "<br/>key is $paramName value of $attrName is " . get_class($parentObject) . " ". $parentObject->$attrName;
                    $value = $parentObject->$attrName;
                    if(!$value || $value == '') $value = "99999999";
                    $relationExpression = str_replace($paramName, $value, $relationExpression);
                }
                $whereParts[] = $relationExpression;
            }
        }
        
        if($query = $this->getQuery($objectElement)) {
            $whereParts[] = $query->nodeValue;
        }
        
        if(count($whereParts) > 0) {
            $selectParts[] = "WHERE";
            $selectParts[] = implode(' and ', $whereParts);
        }
        
        if($selectSortExpression = $this->getSelectSortExpression($objectElement, $sortFields, $sortOrder)) {
           $selectParts[] = $selectSortExpression;
        }
        $selectQuery = implode(' ', $selectParts);
        //echo "<pre>SELECT QUERY = " . $selectQuery . "</pre>";
       
        try {
            $this->databaseObject->query($selectQuery);
        } catch (Exception $e) {
            throw $e;
        }
        
        $objectName = $objectElement->getName();
        while($recordSet = $this->databaseObject->fetch_object()) {
            $dataObject = $dataObjectDocument->createElement($objectName);
            //echo "<br/>Create object $objectName";
            $parentObject->appendChild($dataObject);
            $dataObject->logRead();
            foreach($recordSet as $columnName => $columnValue) {
                if($columnName[0] == "@") {
                    $columnName = substr($columnName, 1);
                    $dataObject->setAttribute($columnName, $columnValue);
                    //echo "<br/>Add attribute $columnName = $columnValue";
                } else {
                    $property = $dataObjectDocument->createElement($columnName, $columnValue);
                    $dataObject->appendChild($property);
                    //echo "<br/>Add element $columnName = $columnValue";
                }
               
            } 
            
        }
    }
    
    public function saveData($objectElement, $dataObject)
    {
        try {
            if($dataObject->isCreated()) {
                $keys = $this->insertData($objectElement, $dataObject);
            } elseif ($dataObject->isUpdated()) {
                $keys = $this->updateData($objectElement, $dataObject);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return $keys;
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
        
        //echo "<br/>DELETE QUERY = $deleteQuery";
        
        try {
            $this->databaseObject->query($deleteQuery);
        } catch (Exception $e) {
            throw $e;
        }
    }
    

    //*************************************************************************
    // PRIVATE SQL QUERY EXECUTION FUNCTIONS
    //*************************************************************************
    
    private function insertData($objectElement, $dataObject)
    {
        // CREATE INSERT QUERY
        $insertParts = array();
        
        $tableExpression = $this->getTableExpression($objectElement);
        $insertParts[] = "INSERT INTO";
        $insertParts[] = $tableExpression;
        
        $insertColumnsExpression = $this->getInsertColumnsExpression($objectElement, $dataObject);
        $insertParts[] = "(" . $insertColumnsExpression . ")";
        
        $insertValuesExpression = $this->createInsertValuesExpression($objectElement, $dataObject);
        $insertParts[] = "VALUES";
        $insertParts[] = "(" . $insertValuesExpression . ")";
        
        $insertParts[] = "RETURNING";
        $insertParts[] = $this->createReturnKeyExpression($objectElement);
        
        $insertQuery = implode(' ', $insertParts);
        
        //echo "<br/>INSERT QUERY = $insertQuery";

        try {
            $this->databaseObject->query($insertQuery);
        } catch (Exception $e) {
            throw $e;
        }
        
        $keys = $this->databaseObject->fetch_object();
        return $keys;
    }
    
    private function updateData($objectElement, $dataObject)
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
        
        $updateParts[] = "RETURNING";
        $updateParts[] = $this->createReturnKeyExpression($objectElement);
        
        $updateQuery = implode(' ', $updateParts);
        
        //echo "<pre>UPDATE QUERY = " . $updateQuery . "</pre>";
        
        try {
            $this->databaseObject->query($updateQuery);
        } catch (Exception $e) {
            throw $e;
        }
        
        $keys = $this->databaseObject->fetch_object();
        return $keys;

    }
    
    //*************************************************************************
    // PRIVATE QUERY CREATION FUNCTIONS
    //*************************************************************************
    
    // SELECT COLUMNS 
    //*************************************************************************
    private function getSelectExpression($objectElement)
    {
        if(!$selectExpression = 
            $this->getXRefs($objectElement, 'SelectExpression')
        ) {
            $selectColumns = array();
            $objectProperties = $this->getObjectProperties($objectElement);
            $l = count($objectProperties);
            for($i=0; $i<$l; $i++) {
                $selectColumn = null;
                $propertyNode = $objectProperties[$i];

                // Column name
                $columnName = $propertyNode->getColumn();
                
                // Fixed, default and no defined values
                if($propertyNode->hasAttribute('fixed')) {
                    // Value enclosure
                    $propertyType = $this->getType($propertyNode);
                    $enclosure = $propertyType->getEnclosure();
                    $selectColumn = 
                        $enclosure 
                        . $this->databaseObject->escape_string(
                            $propertyNode->getAttribute('fixed')) 
                        . $enclosure; 
                } elseif($propertyNode->hasAttribute('default')) {
                    $propertyType = $this->getType($propertyNode);
                    $enclosure = $propertyType->getEnclosure();
                    $selectColumn = 
                        "COALESCE (" 
                            . $columnName 
                            . ", " 
                            . $enclosure 
                            . $this->databaseObject->escape_string(
                                $propertyNode->getAttribute('default'))
                            . $enclosure 
                        . ")"; 
                } elseif(!$columnName) {
                    $selectColumn = "*";
                } else {
                    $selectColumn = $columnName; 
                }
                
                // Column alias
                switch($propertyNode->tagName) {
                    case 'xsd:attribute':
                        $selectColumn .= ' AS "@' . $propertyNode->getName() . '"';
                        break;
                    case 'xsd:element':
                        $selectColumn .= ' AS "' . $propertyNode->getName() . '"';
                        break;
                    case 'xsd:any':
                        break;
                }

                $selectColumns[] = $selectColumn;
            }
            $selectExpression = implode(', ', $selectColumns);
            $this->addXRefs(
                $objectElement, 
                'SelectExpression', 
                $selectExpression
            );
        }
        return $selectExpression;
    }
    
    // TABLE 
    //*************************************************************************
    private function getTableExpression($objectElement)
    {
        if(!$tableExpression = 
            $this->getXRefs($objectElement, 'tableExpression')
        ) {
            $tableExpressionParts = array();
            $tableExpressionParts[] = $objectElement->getTable();
            // If view
            if($view = $this->getView($objectElement)) {
                $joinExpressions = array();
                $joins = $this->query('./das:join', $view);
                // Loop on joins
                $l = $joins->length;
                for($i=0; $i<$l; $i++) {
                    $join = $joins->item($i);

                    $joinExpressionParts = array();
                    
                    // joint tables
                    $parentElement = 
                        $this->getElementByName($join->getAttribute('parent'));
                    $parentTable = $parentElement->getTable();
                    $childElement = 
                        $this->getElementByName($join->getAttribute('child'));
                    $childTable = $childElement->getTable();
                    
                    // join mode
                    if($join->hasAttribute('join-mode')) {
                        $joinExpressionParts[] = $join->getAttribute('join-mode');
                    }
                    $joinExpressionParts[] = 'JOIN';
                    $joinExpressionParts[] = $childTable;
                    $joinExpressionParts[] = 'ON';
                    
                    // Loop on join keys
                    $joinKeyColumns = array();
                    $joinKeys = $this->query('./das:foreign-key', $join);
                    $m = $joinKeys->length;
                    for($j=0; $j<$m; $j++) {
                        $joinKey = $joinKeys->item($j);
                        $childKeyName = $joinKey->getAttribute('child-key');
                        $childKeyProperty = $this->getPropertyByName($childElement, $childKeyName);
                        $childKeyColumn = $childKeyProperty->getColumn();
                        if(strpos($childKeyColumn, ".")) $childKeyExpression = $childKeyColumn;
                        else $childKeyExpression = $childTable . "." . $childKeyColumn;

                        $parentKeyName = $joinKey->getAttribute('parent-key');
                        $parentKeyProperty = $this->getPropertyByName($parentElement, $parentKeyName);
                        $parentKeyColumn = $parentKeyProperty->getColumn();
                        if(strpos($parentKeyColumn, ".")) $parentKeyExpression = $parentKeyColumn;
                        else $parentKeyExpression = $parentTable . "." . $parentKeyColumn;
                        
                        $joinKeyColumns[] =
                            $parentKeyExpression 
                            . " = "  
                            . $childKeyExpression;  
                    } // End loop on join keys
                    $joinExpressionParts[] = implode(' and ', $joinKeyColumns);
                    $joinExpressions[] = implode(' ', $joinExpressionParts);
                } // End loop on joins
                $tableExpressionParts[] = implode(' ', $joinExpressions);  
            } // End if view
            $tableExpression = implode(' ', $tableExpressionParts);
            $this->addXRefs(
                $objectElement, 
                'tableExpression', 
                $tableExpression
            );
        } 
        return $tableExpression;
    }
    
    // SELECT KEY 
    //*************************************************************************
    private function getSelectKeyExpression($objectElement)
    {
        if(!$selectKeyExpression = 
            $this->getXRefs($objectElement, 'selectKeyExpression')
        ) {
            $keyFields = $this->getKeyFields($objectElement);
            $l = $keyFields->length;
            $selectKeyColumns = array();
            for($i=0; $i<$l; $i++) {
                $keyField = $keyFields->item($i);
                $keyName = str_replace("@", "", $keyField->getAttribute('xpath'));
                $keyProperty = $this->getPropertyByName($objectElement, $keyName);
                $keyColumn = $keyProperty->getColumn();
                $keyType = $this->getType($keyProperty);
                $enclosure = $keyType->getEnclosure();
                $selectKeyColumns[] = 
                    $keyColumn 
                    . " = " 
                    . $enclosure 
                    . '$' . $i 
                    . $enclosure;  
            }
            $selectKeyExpression =  implode(' and ', $selectKeyColumns);
            $this->addXRefs(
                $objectElement,
                'selectKeyExpression', 
                $selectKeyExpression
            );
        }
        return $selectKeyExpression;
    }

    // FILTER 
    //*************************************************************************
    private function getSelectFilterExpression($objectElement)
    {
        if(!$selectFilterExpression = 
            $this->getXRefs($objectElement, 'selectFilterExpression')
        ) {
            $filterExpressions = array();
            if($filter = $objectElement->getFilter()) {
                $filterFields = explode(' ', $filter);
                $l = count($filterFields);
                for($i=0; $i<$l; $i++) {
                    $filterName = $filterFields[$i];
                    $filterProperty = $this->getPropertyByName($objectElement, $filterName);
                    $filterColumn = $filterProperty->getColumn();
                    $filterType = $this->getType($filterProperty);
                    $enclosure = $filterType->getEnclosure();
                    $filterExpressions[] = 
                          "UPPER(". $filterColumn . ") " 
                        . "LIKE UPPER(" 
                            . $enclosure 
                            . '$filter' 
                            . $enclosure 
                        . ")";  
                }
            }
            $selectFilterExpression = implode(' or ', $filterExpressions);
            $this->addXRefs(
                $objectElement, 
                'selectFilterExpression', 
                $selectFilterExpression
            );
        }
        return $selectFilterExpression;
    }
    
    // ORDER 
    //*************************************************************************
    private function getSelectSortExpression(
        $objectElement, 
        $sortFields=false, 
        $sortOrder=false
    ) {
        // No sort fields given, sort on key
        if(!$selectSortExpression = 
            $this->getXRefs($objectElement, 'selectSortExpression')
        ) {
            $sortExpressionParts = array();
            $sortColumns = array();
            if(!$sortFields) {
                $sortFieldsArray = array();
                $keyFields = $this->getKeyFields($objectElement);
                for($i=0; $i<$keyFields->length; $i++) {
                    $keyField = $keyFields->item($i);
                    $sortName = str_replace("@", "", 
                        $keyField->getAttribute('xpath'));
                    $sortFieldsArray[] = $sortName;  
                }
            } else {
                $sortFieldsArray = explode(' ', $sortFields);
            }
            for($i=0; $i<count($sortFieldsArray); $i++) {
                $sortField = $sortFieldsArray[$i];
                $sortProperty = $this->getPropertyByName($objectElement, $sortField);
                $sortColumn = $sortProperty->getColumn();
                $sortColumns[] = $sortColumn;
            }
            
            if(count($sortColumns) > 0) {
                switch($sortOrder) {
                case 'descending' : 
                    $sortOrder = 'DESC';
                    break;
                case 'ascending' : 
                default : 
                    $sortOrder = 'ASC';
                }
                $sortExpressionParts[] = "ORDER BY";
                $sortExpressionParts[] = implode(', ', $sortColumns); 
                $sortExpressionParts[] = $sortOrder;
   
            }
            $selectSortExpression = implode(' ', $sortExpressionParts); 
            $this->addXRefs(
                $objectElement, 
                'selectSortExpression', 
                $selectSortExpression
            );
            
        }
        return $selectSortExpression;
    }

    // RELATION 
    //*************************************************************************
    private function getRelationExpression($objectElement, $dataObject)
    {
        if(!$relationExpression = 
            $this->getXRefs($objectElement, 'relationExpression')
        ) {
            if($relation = $this->getRelation($objectElement, $dataObject)) {
                $childTable = $objectElement->getTable();
                              
                $fkeys = $this->query('./das:foreign-key', $relation);
                $fkeysLength = $fkeys->length;
                $relationKeys = array();
                for($i=0; $i<$fkeysLength; $i++) {
                    $fkey = $fkeys->item($i);
                    $childKeyName = str_replace("@", "", $fkey->getAttribute('child-key'));
                    $childKeyElement = $this->getPropertyByName($objectElement, $childKeyName);
                    $childKeyColumn = $childKeyElement->getColumn();
                    $childKeyType = $this->getType($childKeyElement);
                    $enclosure = $childKeyType->getEnclosure();
                    
                    if(strpos($childKeyColumn, ".")) $childKeyExpression = $childKeyColumn;
                    else $childKeyExpression = $childTable . "." . $childKeyColumn;
                    
                    $parentKeyName = $fkey->getAttribute('parent-key');
                    $relationKeys[] = 
                        $childKeyExpression
                        . " = " 
                        . $enclosure 
                        . '$' . $parentKeyName
                        . $enclosure;  
                }
                $relationExpression = implode(' and ', $relationKeys);
            }
            $this->addXRefs(
                $objectElement, 
                'relationExpressions', 
                $relationExpression
            );
        }
        return $relationExpression;
    }
    
    // INSERT COLUMNS 
    //************************************************************************* 
    private function getInsertColumnsExpression(
        $objectElement, 
        $dataObject
    ) {
        $insertColumns = array();
        
        // Get the list of keys generated by SGBD
        $keyFields = $this->getKeyFields($objectElement);
        $keyFieldsLength = $keyFields->length;
        $ignoreKeyFields = array();
        for($i=0; $i<$keyFieldsLength; $i++) {
            $keyField = $keyFields->item($i);
            if($keyField->hasAttribute('das:serial')) {
                $keyName = 
                    str_replace("@", "", $keyField->getAttribute('xpath'));
                $ignoreKeyFields[] = $keyName;
            }
        }
        
        // Loop over properties, add column if value found or mandatory
        $objectProperties = $this->getObjectProperties($objectElement);
        $objectPropertiesLength = count($objectProperties);
        for($i=0; $i<$objectPropertiesLength; $i++) {
            $propertyNode = $objectProperties[$i];
            $propertyName = $propertyNode->getName();
            $columnName = $propertyNode->getColumn();
            if(!in_array($propertyName, $ignoreKeyFields)
                && ( ( isset($dataObject->$propertyName) 
                    && mb_strlen(trim($dataObject->$propertyName)) > 0 )
                    || $propertyNode->isRequired() )
            ) { 
                $insertColumns[] = $columnName;
            }
        }
        return implode(', ', $insertColumns);
    }
    
    // INSERT VALUES
    //************************************************************************* 
    private function createInsertValuesExpression($objectElement, $dataObject)
    {
        $insertValues = array();
        
        $keyFields = $this->getKeyFields($objectElement);
        $keyFieldsLength = $keyFields->length;
        $ignoreKeyFields = array();
        for($i=0; $i<$keyFieldsLength; $i++) {
            $keyField = $keyFields->item($i);
            if($keyField->hasAttribute('das:serial')) {
                $keyName = 
                    str_replace("@", "", $keyField->getAttribute('xpath'));
                $ignoreKeyFields[] = $keyName;
            }
        }
        
        $objectProperties = $this->getObjectProperties($objectElement);
        $objectPropertiesLength = count($objectProperties);
        for($i=0; $i<$objectPropertiesLength; $i++) {
            $propertyNode = $objectProperties[$i];
            $propertyName = $propertyNode->getName();
            if(!in_array($propertyName, $ignoreKeyFields)
                && ( ( isset($dataObject->$propertyName) 
                        && mb_strlen(trim($dataObject->$propertyName)) > 0 )
                    || $propertyNode->isRequired() )
            ) { 
                $propertyType = $this->getType($propertyNode);
                $enclosure = $propertyType->getEnclosure();
                $propertyValue = 
                    $enclosure 
                    . $this->databaseObject->escape_string(
                        $dataObject->$propertyName) 
                    . $enclosure; 
                $insertValues[] = $propertyValue;
            }
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
        $objectPropertiesLength = count($objectProperties);
        for($i=0; $i<$objectPropertiesLength; $i++) {
            $propertyNode = $objectProperties[$i];
            $propertyName = $propertyNode->getName();
            if(in_array($propertyName, $updatedProperties)) {
                $propertyType = $this->getType($propertyNode);
                $enclosure = $propertyType->getEnclosure();
                $columnName = $propertyNode->getColumn();
                $updateColumns[] =  
                    $columnName . " = " 
                    . $enclosure 
                    . $this->databaseObject->escape_string(
                        $dataObject->$propertyName
                    ) 
                    . $enclosure; 
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
            $keyName = 
                str_replace("@", "", $keyField->getAttribute('xpath'));
            $keyElement = $this->getPropertyByName($objectElement, $keyName);
            
            if($keyElement->hasAttribute('das:column')) {
                $keyColumn = $keyElement->getAttribute('das:column');
            } else {
                $keyColumn = $keyName;
            }
            $keyType = $this->getType($keyElement);
            $enclosure = $keyType->getEnclosure();
            $updateKeyFields[] = 
                $keyColumn . " = " 
                . $enclosure 
                . $this->databaseObject->escape_string(
                    $dataObject->$keyName) 
                . $enclosure;  
        }
        return implode(' and ', $updateKeyFields);
    }
    
    // RETURN CREATE KEY
    //************************************************************************* 
    private function createReturnKeyExpression($objectElement)
    {
        $keyFields = $this->getKeyFields($objectElement);
        $keyFieldsLength = $keyFields->length;
        $insertKeyFields = array();
        for($i=0; $i<$keyFieldsLength; $i++) {
            $keyField = $keyFields->item($i);
            $keyName = 
                str_replace("@", "", $keyField->getAttribute('xpath'));
            $keyElement = $this->getPropertyByName($objectElement, $keyName);
            if($keyElement->hasAttribute('das:column')) {
                $keyColumn = $keyElement->getAttribute('das:column');
            } else {
                $keyColumn = $keyName;
            }
            $insertKeyFields[] = 
                $keyColumn 
                . " AS " . $keyName;  
        }
        return implode(', ', $insertKeyFields);
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


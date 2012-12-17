<?php
class DataAccessService_Database  
    extends DataObjectController
{

    private $databaseObject;
    public $inTransaction;
    private $queries = array();
    
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
    
    public function startTransaction()
    {
        $this->databaseObject->connect();
        if(!$this->inTransaction) {
            //echo "<br/>DB Start transaction";
            $this->databaseObject->start_transaction();
            $this->inTransaction = true;
        }
    }
    
    public function commit()
    {
        if($this->inTransaction) {
            //echo "<br/>DB Commit";
            $this->databaseObject->commit();
            $this->inTransaction = false;
        }
    }
    
    public function rollback()
    {
        if($this->inTransaction) {
            //echo "<br/>DB Rollback";
            $this->databaseObject->rollback();
            $this->inTransaction = false;
        }
    }
    
    public function loadData(
        $objectElement,
        $parentObject,
        $dataObjectDocument,
        $key=false,
        $filter=false,
        $sortFields=false, 
        $sortOrder=false, 
        $limit=99999999,
        $query=false
        ) 
    {
        
        $objectName = $objectElement->getName();
        
        $selectParts = array();
        
        //*********************************************************************
        // SELECT
        //*********************************************************************
        if(!$selectClause = 
            $this->getXRefs(
                $objectElement, 
                'selectClause'
            )
        ) {
            $selectClause = $this->createSelectExpression($objectElement);
            $this->addXRefs(
                $objectElement, 
                'selectClause', 
                $selectClause
            );
        }
        $selectParts[] = "SELECT";
        $selectParts[] = $selectClause;
        
        //*********************************************************************
        // FROM
        //*********************************************************************
        if(!$fromClause = 
            $this->getXRefs(
                $objectElement, 
                'fromClause'
            )
        ) {
            $fromClause = $this->createFromExpression($objectElement);
            $this->addXRefs(
                $objectElement, 
                'fromClause', 
                $fromClause
            );
        }
        $selectParts[] = "FROM";
        $selectParts[] = $fromClause;
        
        //*********************************************************************
        // WHERE
        //*********************************************************************
        $whereParts = array();
        
        // WHERE KEY EXPRESSION 
        //*********************************************************************
        if($key) {
            if(!$keyExpression = 
                $this->getXRefs(
                    $objectElement, 
                    'keyExpression'
                )
            ) {
                $keyExpression = 
                    $this->createKeyExpression($objectElement);
                $this->addXRefs(
                    $objectElement,
                    'keyExpression',
                    $keyExpression
                );
            }
            
            $keyValues = explode(' ', $key);
            foreach($keyValues as $i => $keyValue) {
                $keyExpression = 
                    str_replace('$'.$i, $keyValue, $keyExpression);
            }
            $whereParts[] = $keyExpression;
        }
        
        // WHERE FILTER EXPRESSION 
        //*********************************************************************
        if($filter) {
            if(!$filterExpression = 
                $this->getXRefs(
                    $objectElement, 
                    'filterExpression'
                )
            ) {
                $filterExpression = 
                    $this->createFilterExpression($objectElement);
                $this->addXRefs(
                    $objectElement, 
                    'filterExpression', 
                    $selectFilterExpression
                );
            }
            //echo "<br/>filter expression $filterExpression";
            $filterExpression = str_replace('$filter', $filter, $filterExpression);
            $whereParts[] = '(' . $filterExpression .')';
        }
        
        // WHERE RELATION EXPRESSION 
        //*********************************************************************
        if(is_object($parentObject) 
            && get_class($parentObject) != 'DataObjectDocument'
            && $relation = $this->getRelation($objectElement, $parentObject)
        ) {
            //echo "<br/>Found relation between " . $objectElement->getName() . " and " . $parentObject->nodeName;
            /*if(!$relationExpression = 
                $this->getXRefs($objectElement, 'relationExpression')
            ) {*/
                $relationExpression = 
                    $this->createRelationExpression(
                        $objectElement, 
                        $relation
                    );
            /*    $this->addXRefs(
                    $objectElement, 
                    'relationExpression', 
                    $relationExpression
                );
            }*/
            
            if($relationExpression) {
                preg_match_all('/\$\w+/', $relationExpression, $params);
                foreach($params[0] as $paramName) {
                    $attrName = substr($paramName, 1);
                    //echo "<br/>Relation between " . $parentObject->getName() . " and " . $objectElement->getName() . " ==> key is $paramName value of $attrName is " . get_class($parentObject) . " ". $parentObject->$attrName;
                    $value = $parentObject->$attrName;
                    if(!$value || $value == '') $value = "99999999";
                    $relationExpression = str_replace($paramName, $value, $relationExpression);
                }
                //echo "<br/>Relation expression is '$relationExpression'"; 
                $whereParts[] = $relationExpression;
            }
        }
        
        if($objectQuery = $this->getQuery($objectElement)) {
            $whereParts[] = '(' . $objectQuery->nodeValue . ')';
        }
        
        if(count($whereParts) > 0) {
            $selectParts[] = "WHERE";
            $selectParts[] = implode(' and ', $whereParts);
        }
        
        //*********************************************************************
        // SORT EXPRESSION
        //*********************************************************************
        if(!$sortExpression = 
            $this->getXRefs(
                $objectElement, 
                'sortExpression'
            )
        ) {
            $sortExpression = 
                $this->createSortExpression(
                    $objectElement, 
                    $sortFields, 
                    $sortOrder
                );
            $this->addXRefs(
                $objectElement, 
                'sortExpression', 
                $sortExpression
            );    
        }
        if($sortExpression) {
           $selectParts[] = $sortExpression;
        }
        
                
        //*********************************************************************
        // MERGE QUERY PARTS / EXECUTE
        //*********************************************************************
        $selectQuery = implode(' ', $selectParts);
        
        $this->queries[] = $selectQuery;
        //echo "<pre>SELECT QUERY = " . $selectQuery . "</pre>";
        
        try {
            $this->databaseObject->connect();
            $result = $this->databaseObject->query(
                $selectQuery, 
                $catchErrors=true
            );
        } catch (Exception $e) {
            throw $e;
        }
        if(!$result) {
            $this->throwDatabaseException($selectQuery);
        } else {
            //*********************************************************************
            // CREATE / FILL OBJECTS
            //*********************************************************************
            while($recordSet = $this->databaseObject->fetch_object()) {
                $propertyCount = count($recordSet);
                /*if($propertyCount === 1) {
                    $dataObject = 
                        $dataObjectDocument->createElement(
                            $objectName,
                            $recordSet->$objectName
                        );
                } else {*/
                    $dataObject = $this->createDataObject(
                        $objectElement, 
                        $dataObjectDocument
                    );
                    foreach($recordSet as $columnName => $columnValue) {
                        if($columnValue != '') {
                            $dataObject->$columnName = $columnValue;
                        }
                    } 
                //}
                $parentObject[] = $dataObject;
                $dataObject->logRead();
            }
        }
    }
    
    public function deleteData($objectElement, $dataObject)
    {
        $tableExpression = $this->createFromExpression($objectElement);
        $deleteParts[] = "DELETE FROM";
        $deleteParts[] = $tableExpression;

        $keyExpression = $this->createUpdateKeyExpression($objectElement, $dataObject);
        $deleteParts[] = "WHERE";
        $deleteParts[] = $keyExpression;
       
        $deleteQuery = implode(' ', $deleteParts);
        
        $this->queries[] = $deleteQuery;
        //echo "<br/>DELETE QUERY = $deleteQuery";
        
        try {
            $this->databaseObject->connect();
            $result = $this->databaseObject->query(
                $deleteQuery, 
                $catchErrors=true
            );
        } catch (Exception $e) {
            throw $e;
        }
        
        if(!$result) {
            $this->throwDatabaseException($deleteQuery);
        } else {
            $keys = $this->databaseObject->fetch_object();
            return $keys;
        }
        
    }
    
    public function insertData($objectElement, $dataObject)
    {
        // CREATE INSERT QUERY
        $insertParts = array();
        
        $tableExpression = $this->createFromExpression($objectElement);
        $insertParts[] = "INSERT INTO";
        $insertParts[] = $tableExpression;
        
        $insertColumnsExpression = $this->createInsertColumnsExpression($objectElement, $dataObject);
        $insertParts[] = "(" . $insertColumnsExpression . ")";
        
        $insertValuesExpression = $this->createInsertValuesExpression($objectElement, $dataObject);
        $insertParts[] = "VALUES";
        $insertParts[] = "(" . $insertValuesExpression . ")";
        
        $insertParts[] = "RETURNING";
        $insertParts[] = $this->createReturnKeyExpression($objectElement);
        
        $insertQuery = implode(' ', $insertParts);
        
        $this->queries[] = $insertQuery;
        
        //echo "<br/>INSERT QUERY = $insertQuery";

        try {
            $this->databaseObject->connect();
            $result = $this->databaseObject->query(
                $insertQuery, 
                $catchErrors=true
            );
        } catch (Exception $e) {
            throw $e;
        }
        if(!$result) {
            $this->throwDatabaseException($insertQuery);
        } else {
            $keys = $this->databaseObject->fetch_object();
            return $keys;
        }
    }
    
    public function updateData($objectElement, $dataObject)
    {
        $updateParts = array();
        
        $tableExpression = $this->createFromExpression($objectElement);
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
        
        $this->queries[] = $updateQuery;
        //echo "<pre>UPDATE QUERY = " . $updateQuery . "</pre>";
        
        try {
            $this->databaseObject->connect();
            $result = $this->databaseObject->query(
                $updateQuery, 
                $catchErrors=true
            );
        } catch (Exception $e) {
            throw $e;
        }
        
        if(!$result) {
            $this->throwDatabaseException($updateQuery);
        } else {
            $keys = $this->databaseObject->fetch_object();
            return $keys;
        }

    }
    
    public function lastQuery()
    {
        return end($this->queries);
    }
    //*************************************************************************
    // PRIVATE QUERY CREATION FUNCTIONS
    //*************************************************************************
    
    // SELECT CLAUSE 
    //*************************************************************************
    private function createSelectExpression($objectElement)
    {
        $selectColumns = array();
        $typeContents = $this->getObjectContents($objectElement);
        $l = count($typeContents);
        for($i=0; $i<$l; $i++) {
            $contentNode = $typeContents[$i];
            $required = $contentNode->isRequired();
            $contentNode = $this->getRefNode($contentNode);

            if($contentNode->hasDatasource()) continue;
            
            $selectColumn = null;
            // Column name
            $columnName = $contentNode->getColumn();
            if($contentNode->hasAttribute('fixed')) {
                // Value enclosure
                $contentType = $this->getType($contentNode);
                $enclosure = $contentType->getEnclosure();
                $selectColumn = 
                    $enclosure 
                    . $this->databaseObject->escape_string(
                        $contentNode->getAttribute('fixed')) 
                    . $enclosure; 
            } elseif($contentNode->hasAttribute('default')) {
                $contentType = $this->getType($contentNode);
                $enclosure = $contentType->getEnclosure();
                $selectColumn = 
                    "COALESCE (" 
                        . $columnName 
                        . ", " 
                        . $enclosure 
                        . $this->databaseObject->escape_string(
                            $contentNode->getAttribute('default'))
                        . $enclosure 
                    . ")"; 
            } elseif(!$columnName) {
                $selectColumn = "*";
            } else {
                $selectColumn = $columnName; 
            }
            
            // Column alias
            switch($contentNode->tagName) {
                case 'xsd:attribute':
                case 'xsd:element':
                    $selectColumn .= ' AS "' . $contentNode->getName() . '"';
                    break;
                case 'xsd:any':
                    break;
            }

            $selectColumns[] = $selectColumn;
        }
        $selectClause = implode(', ', $selectColumns);
        return $selectClause;
    }
    
    // FROM CLAUSE 
    //*************************************************************************
    private function createFromExpression($objectElement)
    {
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
                    $childKeyProperty = 
                        $this->getContentByName(
                            $childElement, 
                            $childKeyName
                            );
                    $childKeyColumn = $childKeyProperty->getColumn();
                    if(strpos($childKeyColumn, ".")) $childKeyExpression = $childKeyColumn;
                    else $childKeyExpression = $childTable . "." . $childKeyColumn;

                    $parentKeyName = $joinKey->getAttribute('parent-key');
                    $parentKeyProperty = 
                        $this->getContentByName(
                            $parentElement, 
                            $parentKeyName
                        );
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
        return implode(' ', $tableExpressionParts);
    }
    
    // SELECT KEY 
    //*************************************************************************
    private function createKeyExpression($objectElement)
    {
        $key = $objectElement->getAttribute('das:key');
        if(!$key) return false;
        $keyFields = explode(' ', $key);
        $l = count($keyFields);
        $keyColumns = array();
        for($i=0; $i<$l; $i++) {
            $keyField = $keyFields[$i];
            $keyNode = 
                $this->getContentByName(
                    $objectElement, 
                    $keyField
                );
            $keyRefNode = $this->getRefNode($keyNode);
            $keyColumn = $keyRefNode->getColumn();
            $keyType = $this->getType($keyRefNode);
            $enclosure = $keyType->getEnclosure();
            $keyColumns[] = 
                $keyColumn 
                . " = " 
                . $enclosure 
                . '$' . $i 
                . $enclosure;  
        }
        return implode(' and ', $keyColumns);
    }

    // FILTER 
    //*************************************************************************
    private function createFilterExpression($objectElement)
    {
        $filterExpressions = array();
        if($filter = $objectElement->getFilter()) {
            $filterFields = explode(' ', $filter);
            $l = count($filterFields);
            for($i=0; $i<$l; $i++) {
                $filterName = $filterFields[$i];
                $filterProperty = 
                    $this->getContentByName(
                        $objectElement, 
                        $filterName
                    );
                $filterColumn = $filterProperty->getColumn();
                $filterType = $this->getType($filterProperty);
                $enclosure = $filterType->getEnclosure();
                if($enclosure) {
                    $filterExpressions[] = 
                          "UPPER(". $filterColumn . ") " 
                        . "LIKE UPPER(" 
                            . $enclosure 
                            . '$filter' 
                            . $enclosure 
                        . ")"; 
                } else {
                    $filterExpressions[] = 
                          $filterColumn 
                        . ' = $filter '; 
                }
            }
        }
        return implode(' or ', $filterExpressions);
    }
    
    // ORDER 
    //*************************************************************************
    private function createSortExpression(
        $objectElement, 
        $sortFields=false, 
        $sortOrder=false
    ) {
        $sortExpressionParts = array();
        $sortColumns = array();
        
        // No sort fields given, sort on key
        if(!$sortFields) {
            $sortFieldsArray = array();
            $key = $objectElement->getAttribute('das:key');
            if($key) {$keyFields = explode(' ', $key);
                $l = count($keyFields);
                for($i=0; $i<$l; $i++) {
                    $keyField = $keyFields[$i];
                    $sortFieldsArray[] = $keyField;  
                }
            }
        } else {
            $sortFieldsArray = explode(' ', $sortFields);
        }
        
        for($i=0; $i<count($sortFieldsArray); $i++) {
            $sortField = $sortFieldsArray[$i];
            $sortNode = 
                $this->getContentByName(
                    $objectElement, 
                    $sortField
                );
            $sortRefNode = $this->getRefNode($sortNode);
            $sortColumn = $sortRefNode->getColumn();
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
        return implode(' ', $sortExpressionParts); 
    }

    // RELATION 
    //*************************************************************************
    private function createRelationExpression($objectElement, $relation)
    {
        $childTable = $objectElement->getTable();
                      
        $fkeys = $this->query('./das:foreign-key', $relation);
        $fkeysLength = $fkeys->length;
        $relationKeys = array();
        for($i=0; $i<$fkeysLength; $i++) {
            $fkey = $fkeys->item($i);
            $childKeyName = $fkey->getAttribute('child-key');
            $childKeyElement = 
                $this->getContentByName(
                    $objectElement, 
                    $childKeyName
                );
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
        return implode(' and ', $relationKeys);
    }
    
    // INSERT COLUMNS 
    //************************************************************************* 
    private function createInsertColumnsExpression(
        $objectElement, 
        $dataObject
    ) {
        $insertColumns = array();
        
        //$serialKeyFields = $this->getSerialKeyFields($objectElement);
        
        $contents = $dataObject->getContents();
        $l = $contents->length;
        for($i=0; $i<$l; $i++) {
            $content = $contents->item($i);
            $contentName = $content->getName();
            $contentNode = 
                $this->getContentByName(
                    $objectElement, 
                    $contentName
                );
            $contentNode = $this->getRefNode($contentNode);
            if($contentNode->hasDatasource()) continue;
            $insertColumns[] = $contentNode->getColumn();
        }
        return implode(', ', $insertColumns);
    }
    
    // INSERT VALUES
    //************************************************************************* 
    private function createInsertValuesExpression(
        $objectElement, 
        $dataObject
    ) {
        $insertValues = array();
        
        //$serialKeyFields = $this->getSerialKeyFields($objectElement);
        
        $contents = $dataObject->getContents();
        $l = $contents->length;
        for($i=0; $i<$l; $i++) {
            $content = $contents->item($i);
            $contentName = $content->getName();
            $contentNode = 
                $this->getContentByName(
                    $objectElement, 
                    $contentName
                );
            $contentNode = $this->getRefNode($contentNode);
            if($contentNode->hasDatasource()) continue;
            //if(!in_array($contentName, $serialKeyFields)) {
                $contentType = $this->getType($contentNode);
                $enclosure = $contentType->getEnclosure();
                $contentValue = 
                    $enclosure 
                    . $this->databaseObject->escape_string(
                        $dataObject->$contentName) 
                    . $enclosure; 
                $insertValues[] = $contentValue;
            //}
        }
        return implode(', ', $insertValues);
    }
    
    // UPDATE COLUMNS 
    //************************************************************************* 
    private function createUpdateExpression($objectElement, $dataObject)
    {
        $updatedProperties = $dataObject->getUpdatedProperties();
        $updateColumns = array();
        //$serialKeyFields = $this->getSerialKeyFields($objectElement);
        $contents = $dataObject->getContents();
        $l = $contents->length;
        for($i=0; $i<$l; $i++) {
            $content = $contents->item($i);
            $contentName = $content->getName();
            if(in_array($contentName, $updatedProperties)) {
                $contentNode = 
                    $this->getContentByName(
                        $objectElement, 
                        $contentName
                    );
                $contentNode = $this->getRefNode($contentNode);
                if($contentNode->hasDatasource()) continue;
                $contentType = $this->getType($contentNode);
                $enclosure = $contentType->getEnclosure();
                $columnName = $contentNode->getColumn();
                $propertyValue = $dataObject->$contentName;
                if($propertyValue === '') {
                    $columnValue = 'null';
                } else {
                    $columnValue = 
                        $enclosure 
                        . $this->databaseObject->escape_string(
                            $propertyValue) 
                        . $enclosure;
                }
                $updateColumns[] =
                    $columnName 
                    . " = " 
                    . $columnValue;
                
            }
        }

        return implode(', ', $updateColumns);
    }
    
    // UPDATE KEY  
    //************************************************************************* 
    private function createUpdateKeyExpression($objectElement, $dataObject)
    {
        $key = $objectElement->getAttribute('das:key');
        $keyFields = explode(' ', $key);
        $l = count($keyFields);
        $updateKeyFields = array();
        for($i=0; $i<$l; $i++) {
            $keyField = $keyFields[$i];
            $keyNode = 
                $this->getContentByName(
                    $objectElement, 
                    $keyField
                );
            $keyRefNode = $this->getRefNode($keyNode);
            $keyColumn = $keyRefNode->getColumn();
            $keyType = $this->getType($keyRefNode);
            $enclosure = $keyType->getEnclosure();
            $updateKeyFields[] = 
                $keyColumn . " = " 
                . $enclosure 
                . $this->databaseObject->escape_string(
                    $dataObject->$keyField) 
                . $enclosure;  
        }
        return implode(' and ', $updateKeyFields);
    }
    
    // RETURN CREATE KEY
    //************************************************************************* 
    private function createReturnKeyExpression($objectElement)
    {
        $key = $objectElement->getAttribute('das:key');
        $keyFields = explode(' ', $key);
        $l = count($keyFields);
        $returnKeyFields = array();
        for($i=0; $i<$l; $i++) {
            $keyField = $keyFields[$i];
            $keyNode = 
                $this->getContentByName(
                    $objectElement, 
                    $keyField
                );
            $keyRefNode = $this->getRefNode($keyNode);
            $keyColumn = $keyRefNode->getColumn();
            $returnKeyFields[] = 
                $keyColumn 
                . " AS " . $keyField;  
        }
        return implode(', ', $returnKeyFields);
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
        $sqlError = $this->databaseObject->getError();
        if(!$sqlError) $sqlError = "@";
        $exception = $messageController->getMessageText(
            'query_error',
            false,
            array(
                $sqlError,
                $query
            )
        );
        throw new maarch\Exception($sqlError . ' [' .$query . ']');
    }
    
    private function enclose_reserved($columnName) 
    {
        $reserved_words = array(
            'when',
        );
        
        if(in_array($columnName, $reserved_words))
            return '"' . $columnName . "'";
        else return $columnName;
    }
    
}


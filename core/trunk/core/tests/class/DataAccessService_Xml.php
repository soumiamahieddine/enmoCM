<?php
class DataAccessService_XML 
    extends DataAccessService
{
    public $file;
    public $DOMDocument;
    public $DOMXPath;
    public $XSLT;
    
    public function DataAccessService_XML(
        $name,
        $file
    ) 
    {
        $this->name = $name;
        $this->file = $file;
        $this->type = 'xml';
        $this->DOMDocument = new DOMDocument();
        $this->DOMDocument->load($file);
        $this->DOMXPath = new DOMXPath($this->DOMDocument);
        $this->DOMXPath->registerPhpFunctions();
        $this->XSLT = new XSLTProcessor();
    }
    
    public function addTable($tableName) 
    {
        $newTable = new DataAccessService_XML_Table($tableName);
        $this->tables[$tableName] = $newTable;
        return $newTable;
    }
    
    public function addRelation($parentName, $childName, $parentColumns, $childColumns, $name=false) 
    {
        if(!$name) {
            $name = $parentName . '_' . $childName . '_FK';
        }
        $newRelation = new DataAccessService_XML_Relation($parentName, $childName, $parentColumns, $childColumns, $name);
        $this->relations[$name] = $newRelation;
    }
    
    public function getData($dataObject) 
    {
        try {
            $results = $this->queryData($dataObject);
            return $results;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    //*************************************************************************
    // PRIVATE XPATH QUERY CREATION FUNCTIONS
    //*************************************************************************
    private function makeSelectExpression($table)
    {
        $selectExpressionParts = array();
        foreach ($table->columns as $columnName => $column) {
            $selectExpressionParts[] = '<xsl:copy-of select="./'.$columnName.'" />';
            
            // DEFAULT, FIXED
            /*
            if($column->fixed) {
                $fixedValue = $this->makeValueExpression($column, $column->fixed);
                $selectExpressionPart = $fixedValue;
            } elseif($column->{'default'}) {
                $defaultValue = $this->makeValueExpression($column, $column->{'default'});
                $selectExpressionPart = "COALESCE(" . $table->name . "." . $column->name . ", " . $defaultValue . ") AS " . $column->name;
            } else {
                $selectExpressionPart = $table->name . "." . $column->name;
            }
            $selectExpressionParts[] = $selectExpressionPart;*/
        }
        return implode(' ', $selectExpressionParts);
    
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
                $keyValue = "'" . $keyValues[$i] . "'";  
                $selectKeyExpressionParts[] = './' . $keyColumnName . '=' . $keyValue;
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
                $filterExpressionParts[] = "starts-with(" . './' . $filterColumnName . ',' . $filterValue . ')';
            }
            $filterExpression = implode(' or ', $filterExpressionParts);
            return $filterExpression;
        }
    }
    
    private function makeSortExpression($table)
    {
        if($table->order) {
            return '<xsl:sort select="*['.$table->order->select.']" order="'.$table->order->mode.'"/>';
        } elseif(isset($table->primaryKey) && !is_null($table->primaryKey))  {
            $orderElements = $table->primaryKey->getColumns();
            foreach($orderElements as $orderElement) {
                $orderElement = "name()='".$orderElement."'";
            }
            $order->select = implode(' or ' . $orderElements);
            $order->mode = $orderMode;
            return '<xsl:sort select="*['.$order->select.']" order="'.$order->mode.'"/>';
        }
    }
    
    //*************************************************************************
    // PRIVATE XPATH QUERY EXECUTION FUNCTIONS
    //*************************************************************************
    private function queryData($dataObject)
    {
        $parentObject = $dataObject->parentObject;
        $table = $this->tables[$dataObject->name];
        
        // Select 
        $selectExpression = $this->makeSelectExpression($table);
        
        // Where
        $whereExpressionParts = array('.');
        /*
        $relation = $this->getRelation($parentObject->name, $table->name);
        if($relation) {
            $whereExpressionParts[] = $this->makeRelationExpression($relation, $table, $parentObject);
        }*/
        $keyExpression = $this->makeSelectKeyExpression($table);
        if($keyExpression) {
            $whereExpressionParts[] = $keyExpression;
        }
        $filterExpression = $this->makeFilterExpression($table);
        if($filterExpression) {
            $whereExpressionParts[] = $filterExpression;
        }
        $whereExpression = implode(' and ', $whereExpressionParts);
        
        $tableQuery = $table->name . "[" . $whereExpression . "]";
        
        // Order
        $sortExpression = $this->makeSortExpression($table);
      
        $xslString = 
            '<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
                <xsl:template match="/">
                  <xsl:apply-templates select="'.$tableQuery.'">
                    '. $sortExpression .'
                  </xsl:apply-templates>
                </xsl:template>
                <xsl:template match="*">
                  <xsl:copy>
                    '.$selectExpression.'
                  </xsl:copy>
                </xsl:template>
            </xsl:stylesheet>';
        
        echo "<pre>XSL = " . htmlspecialchars($xslString) . "</pre>";

        
        
        $XSL = new DOMDocument();
        $XSL->loadXML($xslString);
        $this->XSLT->importStylesheet( $XSL );

        $root = $this->DOMDocument->documentElement;
        
        $DOMResult = $this->XSLT->transformToDoc($root);
        echo htmlspecialchars($DOMResult->saveXML());exit;
        return $results;
        
        
        //echo "<pre>DAS = " . print_r($this,true) . "</pre>";
        //echo "<pre>TABLE QUERY = " . print_r($tableQuery,true) . "</pre>";
        //echo "<pre>COLUMN QUERY = " . print_r($columnQuery,true) . "</pre>";
        /*
        try {
            $rowNodeList = $this->DOMXPath->query($tableQuery);
        } catch (Exception $e) {
            throw $e;
        }
        $results = array();
        //echo "<br/>Found $rowNodeList->length table results<br/>";
        for($i=0; $i<$rowNodeList->length; $i++) {
            $rowNode = $rowNodeList->item($i);
            foreach ($table->columns as $columnName => $column) {
                $columnNodes = $this->DOMXPath->query('./' . $columnName, $rowNode);
                if($columnNodes->length == 0) { 
                    // throw Exception
                } 
                $columnNode = $columnNodes->item(0);
                $columnValue = $columnNode->nodeValue;
                
                if($column->fixed) {
                    $columnValue = $column->fixed;
                } elseif($column->{'default'} && $columnValue == '') {
                    $columnValue = $column->{'default'};    
                }
                
                $result[$columnName] = $columnValue;             
            }
            $results[] = $result;
        }
        */
    }
    
}

class DataAccessService_XML_Table
    extends DataAccessService_Table
{

    public function addPrimaryKey($columns, $name=false)
    {
        if(!$name) $name = $this->name . '_pkey';
        $this->primaryKey = new DataAccessService_XML_PrimaryKey($columns, $name);
    }
    
    public function addColumn($columnName, $columnType)
    {
        $newColumn = new DataAccessService_XML_Column($columnName, $columnType);
        $this->columns[$columnName] = $newColumn;
        return $newColumn;
    }
    
    public function setOrder($orderElements, $orderMode)
    {
        $orderElementsArray = explode(' ', $orderElements);
        for($i=0; $i<count($orderElementsArray); $i++) {
            $orderElementsArray[$i] = "name()='".$orderElementsArray[$i]."'";
        }
        $this->order->select = implode(' or ', $orderElementsArray);
        $this->order->mode = $orderMode;
    }
    
}

class DataAccessService_XML_PrimaryKey
    extends DataAccessService_PrimaryKey
{

}

class DataAccessService_XML_Sort
    extends DataAccessService_Sort
{
    
}

class DataAccessService_XML_Column
    extends DataAccessService_Column
{
   
}

class DataAccessService_XML_Relation
    extends DataAccessService_Relation
{ 

}
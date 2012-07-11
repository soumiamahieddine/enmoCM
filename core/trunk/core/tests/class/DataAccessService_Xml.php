<?php
class DataAccessService_XML 
    extends DataAccessService
{
    public $file;
    public $DOMDocument;
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
        $this->XSLT = new XSLTProcessor();
    }
    
    public function addTable($tableName) 
    {
        $newTable = new DataAccessService_XML_Table($tableName);
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
        $newRelation = new DataAccessService_XML_Relation($parentName, $childName, $parentColumns, $childColumns, $name);
        $this->relations[$name] = $newRelation;
    }
    
    public function loadData($objectSchema, $parentObject, $key=false) 
    {
        try {
            $this->queryData($objectSchema, $parentObject, $key);
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
            $selectExpressionParts[] = "name()='" .$columnName."'";
        }
        return implode(' or ', $selectExpressionParts);
    
    }
    
    private function makeValueExpression($table)
    {
        $valueExpressionParts = array();
        foreach ($table->columns as $columnName => $column) {
            $valueExpressionPart = 
                '<xsl:template match="'.$columnName.'">
                  <xsl:copy>';
            if($column->fixed) {
                $valueExpressionPart .= $column->fixed;
            } elseif($column->{'default'}) {
                $valueExpressionPart .= 
                    '<xsl:choose>
                      <xsl:when test="string-length(.) &gt; 0">
                        <xsl:value-of select="node()" />
                      </xsl:when>
                      <xsl:otherwise>'.$column->{'default'}.'</xsl:otherwise>
                    </xsl:choose>';
            } else {
                $valueExpressionPart .= 
                    '<xsl:value-of select="node()" />';
            }
            $valueExpressionPart .= 
                    '</xsl:copy>
                </xsl:template>';
            $valueExpressionParts[] = $valueExpressionPart;
        }
        return implode('', $valueExpressionParts);
    }
    
    private function makeSelectKeyExpression($table, $key) 
    {
        $selectKeyExpressionParts = array();
        if(isset($table->primaryKey) && !is_null($table->primaryKey)) {
            $keyColumns = $table->primaryKey->getColumns();
            $keyValues = explode(' ', $key);
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
    
    private function makeRelationExpression($relation, $table, $parentObject)
    {
        $relationExpressionParts = array();
        $childColumns = explode(' ', $relation->childColumns);
        $parentColumns = explode(' ', $relation->parentColumns);
        for($i=0; $i<count($childColumns); $i++) {
            $childColumnName = $childColumns[$i];
            $parentColumnName = $parentColumns[$i];
            $childColumn = $table->columns[$childColumnName];
            $parentColumnValue = "'" . $parentObject->{$parentColumnName} . "'";
            $relationExpressionParts[] = "./" . $childColumnName . " = " . $parentColumnValue;
        }
        $relationExpression = implode(' and ', $relationExpressionParts);
        return $relationExpression;
    }
    
    private function makeSortExpression($table)
    {
        if($table->order) {
            return '<xsl:sort select="*['.$table->order->select.']" order="'.$table->order->mode.'"/>';
        } elseif(isset($table->primaryKey) && !is_null($table->primaryKey))  {
            $orderElements = $table->primaryKey->getColumns();
            print_r($orderElements);
            for($i=0; $i<count($orderElements); $i++) {
                $orderElements[$i] = "name()='".$orderElements[$i]."'";
            }
            $order->select = implode(' or ' , $orderElements);
            $order->mode = 'ascending';
            return '<xsl:sort select="*['.$order->select.']" order="'.$order->mode.'"/>';
        }
    }
    
    //*************************************************************************
    // PRIVATE XPATH QUERY EXECUTION FUNCTIONS
    //*************************************************************************
    private function queryData($objectSchema, $parentObject, $key=false)
    {
        if(@$parentObject->ownerDocument) {
            $document = $parentObject->ownerDocument;
        } else {
            $document = $parentObject;
        }
        
        $tableName = $objectSchema->getTableName();
        
        $table = $this->tables[$tableName];
        
        // Select 
        $selectExpression = $this->makeSelectExpression($table);
        
        // Values
        $valueExpression = $this->makeValueExpression($table);
        
        // Where
        $whereExpressionParts = array('.');
        if($parentObject 
            && $parentObject != $document 
            && $relation = $this->getRelation($parentObject->tagName, $tableName)) {
            echo "<br/>relation " . print_r($relation, true);
            $whereExpressionParts[] = $this->makeRelationExpression($relation, $table, $parentObject);
        }
        if($key && $keyExpression = $this->makeSelectKeyExpression($table, $key)) {
            $whereExpressionParts[] = $keyExpression;
        }
        $filterExpression = $this->makeFilterExpression($table);
        if($filterExpression) {
            $whereExpressionParts[] = $filterExpression;
        }
        $whereExpression = implode(' and ', $whereExpressionParts);
        // === database ?
        
        $tableQuery = $table->name . "[" . $whereExpression . "]";
        
        // Order
        $sortExpression = $this->makeSortExpression($table);
      
        $xslString = 
            '<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
                <xsl:template match="/">
                  <root>
                      <xsl:apply-templates select="//'.$tableQuery.'">
                        '. $sortExpression .'
                      </xsl:apply-templates>
                  </root>
                </xsl:template>
                <xsl:template match="'.$tableName.'">
                  <xsl:copy>
                    <xsl:apply-templates select="*['.$selectExpression.']" />
                  </xsl:copy>
                </xsl:template>
                '.$valueExpression.'
              </xsl:stylesheet>';
        
        echo "<pre>XSL = " . htmlspecialchars($xslString) . "</pre>";
     
        $XSL = new DOMDocument();
        $XSL->formatOutput = true;
        $XSL->loadXML($xslString);
        $this->XSLT->importStylesheet( $XSL );

        $root = $this->DOMDocument->documentElement;
        
        $DOMDocument = $this->XSLT->transformToDoc($root);
        echo htmlspecialchars($DOMDocument->saveXML());
        $results = array();
        $DOMTable = $DOMDocument->documentElement->childNodes;
        for($i=0; $i<$DOMTable->length; $i++) {
            $dataObject = $document->createElement($objectSchema->name);
            $parentObject->appendChild($dataObject);
            
            $DOMRow = $DOMTable->item($i);
            $DOMColumns = $DOMRow->childNodes;
            for($j=0; $j<$DOMColumns->length; $j++) {
                $DOMColumn = $DOMColumns->item($j);
                $columnName = $DOMColumn->tagName;
                $columnValue = $DOMColumn->nodeValue;
                $columnNode = $document->createElement($columnName, $columnValue);
                $dataObject->appendChild($columnNode);
            }
        }
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
<?php
class DataAccessService_Database 
{
	
    private $connection;
    public $schema;
    public $tables = array();
    public $relations = array();
    
    function DataAccessService_Database($schema='public') 
    {
        $this->schema=$schema;
    }
    
    function addTable($tableName) 
    {
        $newTable = new DataAccessService_Database_Table($tableName);
        $this->tables[$tableName] = $newTable;
        return $newTable;
    }
    
    function addRelation($parentTable, $childTable, $parentColumns, $childColumns) 
    {
        $newRelation = new DataAccessService_Database_Relation($parentTable, $childTable, $parentColumns, $childColumns);
        $this->relations[] = $newRelation;
    }
    
    
    function getData($dataObject, $where=false) 
    {
        $parentObject = $dataObject->getParentObject();
        
        // Select Expression 
        $selectExpression = $this->makeSelectExpression();
      
        // Tables
        $fromExpression = $this->makeFromExpression();
        
        // Where
        $whereExpressionParts = array('1=1');
        if($parentObject && count($this->relations)) {
            $whereExpressionParts[] = $this->makeRelationExpression($table->name, $parentObject);
        }
        if($where) {
            $whereExpressionParts[] = $where;
        }
        $whereExpression = implode(' and ', $whereExpressionParts);
  
        $query  = "SELECT " . $selectExpression;
        $query .= " FROM  " . $fromExpression;
        $query .= " WHERE " . $whereExpression;
        
        //echo "<pre>DAS = " . print_r($this,true) . "</pre>";
        //echo "<pre>QUERY = " . $query . "</pre>";
        $db = new dbquery();
        $db->query($query);
        
        $results = array();
        while($result = $db->fetch_assoc()) {
            $results[] = $result;
        }
        return $results;
    }
    
    private function makeSelectExpression() 
    {
        $selectExpressionParts = array();
        foreach($this->tables as $table) {
            foreach ($table->columns as $column) {
                // DEFAULT, FIXED
                if($column->fixed) {
                    $fixedValue = $this->enclose($column->fixed, $column->type);
                    $selectExpressionPart = $fixedValue;
                } elseif($column->{'default'}) {
                    $defaultValue = $this->enclose($column->{'default'}, $column->type);
                    $selectExpressionPart = "COALESCE(" . $table->name . "." . $column->name . ", " . $defaultValue . ")";
                } else {
                    $selectExpressionPart = $table->name . "." . $column->name;
                }
                // ALIAS
                if($column->alias) {
                    $selectExpressionPart .= ' AS ' . $column->alias;
                }
                $selectExpressionParts[] = $selectExpressionPart;
            }
        }
        return implode(', ', $selectExpressionParts);
    }
    
    private function makeFromExpression()
    {
        $fromExpressionParts = array();
        foreach($this->tables as $tableName => $table) {
            $fromExpressionParts[] = $tableName;
        }
        return implode(', ', $fromExpressionParts);
    }
    
    private function makeRelationExpression($tableName, $parentObject) {
        $relation = $this->relations[0];
        $relationExpressionParts = array();
        $childColumns = explode(' ', $relation->childColumns);
        $parentColumns = explode(' ', $relation->parentColumns);
        for($i=0; $i<count($childColumns); $i++) {
            $childColumnName = $childColumns[$i];
            $parentColumnName = $parentColumns[$i];
            
            $childColumn = $table->columns[$childColumnName];
            $childType = $childColumn->type;
            
            $parentColumnValue = $this->enclose($parentObject->{$parentColumnName}, $childType);  
            $relationExpressionParts[] = $childColumnName . " = " . $parentColumnValue;
        }
        if(count($relationExpressionParts) > 0) return implode(' and ', $relationExpressionParts);
    }
    
    private function enclose($value, $typeName)
    {
        if($this->isQuoted($typeName)) {
                $value = "'" . $value . "'";
            } 
        return $value;
    }
    
    private function isQuoted($typeName) 
    {
        if(!in_array(
            $typeName,
            array(
                'xsd:boolean',
                'xsd:double', 
                'xsd:decimal',
                    'xsd:integer',
                        'xsd:nonPositiveInteger',
                            'xsd:negativeInteger',
                        'xsd:long',
                            'xsd:int', 
                            'xsd:short', 
                            'xsd:byte',
                        'xsd:nonNegativeInteger',
                            'xsd:positiveInteger',
                            'xsd:unsignedLong',
                                'xsd:unsignedInt',
                                    'xsd:unsignedShort',
                                        'xsd:unsignedByte',
                'xsd:float',
                )
            )
        ) {
            return true;
        }
    }
    
}

class DataAccessService_Database_Table
{
    public $name;
    public $columns = array();
    public $primaryKey = false;
    public $foreignKeys = array();
    public $indexes = array();
    public $relation;

    function DataAccessService_Database_Table($name)
    {
        $this->name = $name;
    }
    
    function addPrimaryKey($columns, $name=false)
    {
        if(!$name) $name = $this->name . '_pkey';
        $this->primaryKey = new DataAccessService_Database_Table_PrimaryKey($columns, $name);
    }
    
    function addColumn($columnName, $columnType)
    {
        $newColumn = new DataAccessService_Database_Table_Column($columnName, $columnType);
        $this->columns[$columnName] = $newColumn;
        return $newColumn;
    }
    
}

class DataAccessService_Database_Table_PrimaryKey
{
    public $name;
    public $columns;
    
    public function DataAccessService_Database_Table_PrimaryKey($columns, $name)
    {
        $this->name = $name;
        $this->columns = $columns;
    }
    
    
}

class DataAccessService_Database_Table_Column
{
    public $name;
    public $alias;
    public $type;
    public $default;
    public $nillable;
    public $fixed;
    
    public function DataAccessService_Database_Table_Column($name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }
    
}

class DataAccessService_Database_Relation
{
    public $parentTable;
    public $childTable;
    public $parentColumns;
    public $childColumns;
    
    function DataAccessService_Database_Relation($parentTable, $childTable, $parentColumns, $childColumns) {
        $this->parentTable = $parentTable;
        $this->childTable = $childTable;
        $this->parentColumns = $parentColumns;
        $this->childColumns = $childColumns;
    } 
}
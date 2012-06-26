<?php

class dataObjectController extends DOMDocument
{
    const NONE      = 0;
    const CREATE    = 1;
    const READ      = 2;
    const UPDATE    = 3;
    const DELETE    = 4;
    
    
    private $schema;
    private $prototypes = array();
    private $dataAccessService_Database;
    private $dataAccessService_XML;
    private $errors = array();
    
    public function dataObjectController() 
    {
        // DataObject classes
        require_once 'core/tests/class/DataObjectSchema.php';
        require_once 'core/tests/class/DataObjectArray.php';
        require_once 'core/tests/class/DataObject.php';
        require_once 'core/tests/class/DataObjectProperty.php';
        
        // ChangeLog classes
        require_once 'core/tests/class/DataObjectChangeLog.php';
        require_once 'core/tests/class/DataObjectChange.php';
        
        // DataAccessService / PDO classes
        require_once 'core/tests/class/DataAccessService_Database.php';
        require_once 'core/tests/class/DataAccessService_XML.php';
        
        // Validator classes
        require_once 'core/tests/class/error.php';
        
    }
    
    public function loadSchema($xsdFile) 
    {
        $this->schema = new DataObjectSchema();
        $this->schema->loadSchema($xsdFile);
        
        $this->dataAccessService_Database = new dataAccessService_Database();
        
        $objectSchemas = $this->schema->getObjectSchemas();
        for($i=0; $i<$objectSchemas->length; $i++) {
            $objectSchema = $objectSchemas->item($i);
            $this->prototypeDataObject($objectSchema);
        }
              
    }
    
    //*************************************************************************
    // PUBLIC OBJECT HANDLING FUNCTIONS
    //*************************************************************************
    /**************************************************************************
    ** public create
    **
    ** @description : 
    ** Creates a new empty DataObject from a root element name
    **
    ** @param (string) $objectName : Name of a root element definition
    **
    ** @return (object) new DataObject / DataObjectArray
    */
    public function create($objectName) 
    {
        //echo "<br/><br/><b> createDataObject($rootTypeName)</b>"; 
        $objectSchema = $this->schema->getObjectSchema($objectName);
        $dataObject = $this->instanciateDataObject($objectSchema);
        $dataObject->beginLogging();
        $dataObject->logCreation();
        return $dataObject;      
    }
    
    /**************************************************************************
    ** public read
    **
    ** @description : 
    ** Read a DataObject from with a data source
    **
    ** @param (string) $objectName : Name of a root element definition
    **
    ** @return (object) loaded DataObject / DataObjectArray
    */
    public function read($objectName)
    {
        $objectSchema = $this->schema->getObjectSchema($objectName);
        $dataObject = $this->instanciateDataObject($objectSchema);
        $this->loadDataObject($dataObject);
        $dataObject->beginLogging();
        $dataObject->logRead();
        return $dataObject;
    }
    
    public function validate($dataObject) 
    {
        $XmlDocument = $this->getXmlDocument($dataObject);
        $XsdString = $this->schema->saveXML();
        libxml_use_internal_errors(true);
        if($XmlDocument->schemaValidateSource($XsdString)) {
            return true;
        } else {
            $this->errors = libxml_get_errors();
            /*foreach ($libXMLErrors as $libXMLError) {
                $level = $libXMLError->level;
                $code = $libXMLError->code;
                $message = $libXMLError->message;
                $this->errors[] = new error($level, $code, $message);
            }*/
            return false;
        } 
        libxml_clear_errors();
        
        
        return $this->dataObjectValidator->validateDataObject($dataObject, $this->schema);
    }
    
    public function getValidationErrors()
    {
        return $this->errors;
    }
    
    public function save($dataObject) 
    {
        $schemaPath = $dataObject->schemaPath;
        $objectSchema = $this->schema->getSchemaElement($schemaPath);
        switch($objectSchema->{'das:source'}) {
        case 'database':
            $this->dataAccessService_Database->saveData($dataObject);
            break;
            
        case 'xml':
            break;
        }
    }
    
    public function delete($dataObject)
    {
    
    }
    
    public function copy($dataObject, $keepParent=true)
    {
        $dataObject = unserialize(serialize($dataObject));

        $key = $this->getKey($dataObject->name);
        $keyNames = explode(' ', $key);
        for($i=0; $i<count($keyNames); $i++) {
            $keyName = $keyNames[$i];
            $dataObject->{$keyName}->clear();
        }
        
        $children = $dataObject->children;
        for($i=0; $i<count($children); $i++) {
            $children[$i]->clear();
        }
        
        if(!$keepParent) $dataObject->parentObject = false;
        
        $dataObject->beginLogging();
        $dataObject->logCreation();
                
        return $dataObject;    
    
    }
    
    public function serialize($dataObject)
    {
        return serialize($dataObject);
    }
    
    public function unserialize($serializedDataObject)
    {
        return unserialize($serializedDataObject);
    }
    
    //*************************************************************************
    // PUBLIC DAS FUNCTIONS 
    //*************************************************************************
    public function setKey($objectName, $key) 
    {
        $objectSchema = $this->schema->getObjectSchema($objectName);
        $dasSource = $objectSchema->{'das:source'};
        switch($dasSource) {
        case 'database':
            $this->dataAccessService_Database->setKey($objectSchema->name, $key);
            break;
            
        case 'xml':
            break;
        }
    }
    
    public function setOrder($objectName, $orderElements, $orderMode='ASC') 
    {
        $objectSchema = $this->schema->getObjectSchema($objectName);
        $dasSource = $objectSchema->{'das:source'};
        switch($dasSource) {
        case 'database':
            $this->dataAccessService_Database->setOrder($objectSchema->name, $orderElements, $orderMode);
            break;
        case 'xml':
            break;
        }
    }
    
    public function setFilter($objectName, $filterValue) 
    {
        $objectSchema = $this->schema->getObjectSchema($objectName);
        $dasSource = $objectSchema->{'das:source'};
        switch($dasSource) {
        case 'database':
            //echo "<br/>Setting filter $filterValue for $objectSchema->name";
            $this->dataAccessService_Database->setFilter($objectSchema->name, $filterValue);
            break;
        case 'xml':
            break;
        }
        
    }

    public function getKey($objectName) 
    {
        $objectSchema = $this->schema->getObjectSchema($objectName);
        $keyColumnNames = $objectSchema->{'das:key-columns'};
        return $keyColumnNames;
    }
    
    public function getLabel($objectName)
    {
        $objectSchema = $this->schema->getObjectSchema($objectName);
        if($objectSchema->{'das:label'}) return $objectSchema->{'das:label'};
        else return $objectSchema->name;
    }
    
    public function getContentLabels($objectName) 
    {
        $objectSchema = $this->schema->getObjectSchema($objectName);
        $childElements = $objectSchema->getChildElements();
        for($i=0; $i<$childElements->length;$i++) {
            $childElement = $childElements->item($i);
            if($childElement->{'das:label'}) $label = $childElement->{'das:label'};
            elseif($childElement->ref) $label = $childElement->ref;
            else $label = $childElement->name;
            
            if($childElement->ref) $childName = $childElement->ref;
            else $childName = $childElement->name;
            $labels[$childName] = $label;
        }
        return $labels;
    }
    
    //*************************************************************************
    // PRIVATE OBJECT HANDLING FUNCTIONS
    //*************************************************************************
    /**************************************************************************
    ** private instanciateDataObject
    **
    ** @description : 
    ** Creates a new instance of DataObject or DataObjectArray including
    **  - DataObjectProperties
    **  - Children (instance of data objects or array data object)
    **
    ** @param (string) $schemaPath : Path to object in schema
    **
    ** @return (object) new DataObject / DataObjectArray
    */
    private function instanciateDataObject($objectSchema, $inlineObjectSchema=false)
    {
        $schemaPath = $objectSchema->getNodePath();
        if($inlineObjectSchema && $inlineObjectSchema->isDataObjectArray()) {
            $arraySchemaPath = $inlineObjectSchema->getNodePath();
            $dataObject = new DataObjectArray($objectSchema->name, $schemaPath, $arraySchemaPath);
        } else {
            $dataObject = unserialize(serialize($this->prototypes[$schemaPath]));
        }
        return $dataObject;
    }
     
    private function prototypeDataObject($objectSchema)
    {
        //$objectSchema = $this->schema->getSchemaElement($schemaPath);
        $schemaPath = $objectSchema->getNodePath();
        //echo "<br/>Create prototype object with $objectSchema->name";
        $prototypeDataObject = new DataObject($objectSchema->name, $schemaPath);
        
        // Set Das parameters
        $this->setDasSource($objectSchema);
        
        // Create Properties and children
        // ******************************************************************** 
        $childElements = $objectSchema->getChildElements();
        //echo "<br/>   Object has $childElements->length properties/children";
        for($i=0; $i<$childElements->length;$i++) {
            $inlineChildElement = $childElements->item($i);
            $childElement = $inlineChildElement->getRefElement();
            $childPath = $childElement->getNodePath();
            $childName = $childElement->name;
            $childType = $childElement->getType();
            if(!$childType) die("Unable to find data type for property " . $childElement->name);
            if ($childType->tagName == 'xsd:simpleType') {
                // DEFAULT and FIXED
                if($childElement->{'default'}) {
                    $childValue = $childElement->{'default'};
                }
                else if($childElement->fixed) {
                    $childValue = $childElement->fixed;
                } else {
                    $childValue = false;
                }
                //echo "<br/>    Adding property '$childName'";
                $dataObjectProperty = new DataObjectProperty($childName, $childPath, $childValue);
                $prototypeDataObject->$childName = $dataObjectProperty;
                
                $this->setDasProperty($objectSchema, $childElement);
            }
            if ($childType->tagName == 'xsd:complexType') {
                $childDataObject = $this->instanciateDataObject($childElement, $inlineChildElement);
                $prototypeDataObject->$childName = $childDataObject;
            }
        }
        $this->prototypes[$schemaPath] = $prototypeDataObject;
    }
    
    /**************************************************************************
    ** public createDataObject
    **
    ** @description : 
    ** Creates a new DataObject or DataObjectArray from a root element name
    **  - DataObjectProperties
    **  - Children (instance of data objects or array data object)
    **
    ** @param (string) $rootTypeName : Name of a root element definition
    **
    ** @return (object) new DataObject / DataObjectArray
    */
    private function loadDataObject($dataObject) 
    {      
        $objectDatas = $this->getData($dataObject);
        $objectData = $objectDatas[0];
        $this->loadProperties($dataObject, $objectData);
        $this->loadChildren($dataObject);
    }
    
    private function loadDataObjectArray($arrayDataObject)
    {
        $objectDatas = $this->getData($arrayDataObject);
        $schemaPath = $arrayDataObject->schemaPath;
        $objectSchema = $this->schema->getSchemaElement($schemaPath);
        for($i=0; $i<count($objectDatas); $i++) {
            $objectData = $objectDatas[$i];
            $dataObject = $this->instanciateDataObject($objectSchema);
            $dataObject->beginLogging();
            $dataObject->logRead();
            $arrayDataObject->append($dataObject);          
            $this->loadProperties($dataObject, $objectData);
            $this->loadChildren($dataObject);
        }
    }
     
    private function loadProperties($dataObject, $objectData)
    {
        $propertiesObjects = $dataObject->properties;
        for($i=0; $i<count($propertiesObjects); $i++) {
            $propertyObject = $propertiesObjects[$i];
            $propertyName = $propertyObject->name;
            $propertyValue = $objectData[$propertyName];
            $propertyObject->setValue($propertyValue);
        }
    }
    
    private function loadChildren($dataObject) 
    {
        $childrenObjects = $dataObject->children;
        for($i=0; $i<count($childrenObjects); $i++) {
            $childObject = $childrenObjects[$i];
            if($childObject->isDataObjectArray) {
                $this->loadDataObjectArray($childObject);
            } else {
                $this->loadDataObject($childObject);
            }
        }
    }
    
    //*************************************************************************
    // PRIVATE DAS FUNCTIONS
    //*************************************************************************
    private function setDasSource($objectSchema)
    {
        $dasSource = $objectSchema->{'das:source'};
        switch($dasSource) {
        case 'database':
            // Main source
            //echo "<br/> Add table $objectSchema->name";
            $dasTable = $this->dataAccessService_Database->addTable($objectSchema->name);
            $dasTable->addPrimaryKey(
                $objectSchema->{'das:key-columns'}
            );
            $dasTable->addFilter(
                $objectSchema->{'das:filter-columns'}
            );
            if($propertyElement->{'das:label'}) {
                $dasColumn->{'label'} = $propertyElement->{'das:label'};
            } else {
                $dasColumn->{'label'} = $propertyElement->name;
            }
            if($propertyElement->{'das:comment'}) {
                $dasColumn->{'comment'} = $propertyElement->{'das:comment'};
            }
            // Relation with parent
            $relationElements = $objectSchema->getRelationElements();
            for($i=0; $i<$relationElements->length; $i++) {
                $relationElement = $relationElements->item(0);
                //echo "<br/> Add relation between " .$relationElement->{'parent'} ." and ". $relationElement->{'child'};
                $this->dataAccessService_Database->addRelation(
                    $relationElement->{'parent'},
                    $relationElement->{'child'}, 
                    $relationElement->{'parent-keys'}, 
                    $relationElement->{'child-keys'}
                );
            }
            
            break;
            
        case 'xml':
            break;
        }

    }
    
    private function setDasProperty($objectSchema, $propertyElement)
    {
        $propertyType = $propertyElement->getType();
        
        $dasSource = $objectSchema->{'das:source'};
        switch($dasSource) {
        case 'database':
            ////echo "<br/> Add column $propertyName to $objectName";
            $dasColumn = $this->dataAccessService_Database->addColumn($objectSchema->name, $propertyElement->name, $propertyType->name);
            if($propertyElement->{'default'}) {
                $dasColumn->{'default'} = $propertyElement->{'default'};
            }
            if($propertyElement->{'fixed'}) {
                $dasColumn->{'fixed'} = $propertyElement->{'fixed'};
            }
            if(strtolower($propertyElement->{'nillable'}) === 'true') {
                $dasColumn->nillable = true;
            }
            break;
        case 'xml':
            break;
        }
    }   

    private function setDasOrder($objectSchema, $orderElements, $orderMode)
    {
        $dasSource = $objectSchema->{'das:source'};
        switch($dasSource) {
        case 'database':
            $this->dataAccessService_Database->setOrder($objectSchema->name, $orderElements, $orderMode);
            break;
        }
    }
    
    private function getData($dataObject) 
    {
        $schemaPath = $dataObject->schemaPath;
        $objectSchema = $this->schema->getSchemaElement($schemaPath);
        //echo "<br/>Get data from $objectSchema->name$objectSchema->ref ($schemaPath)";
        switch($objectSchema->{'das:source'}) {
        case 'database':
            return $this->dataAccessService_Database->getData($dataObject);
            break;
            
        case 'xml':
            break;
        }
    }
    
    //*************************************************************************
    // Web Service Object (properties/children - no method)
    //*************************************************************************
    public function getStdObject($dataObject)
    {
        $stdObject = new StdClass();
        $this->childrenToStdObject($dataObject, $stdObject); 
        
        return $stdObject;
    }
    
    public function loadStdObject($stdObject, $objectName, $mode)
    {
        $objectSchema = $this->schema->getObjectSchema($objectName);
        $dataObject = $this->instanciateDataObject($objectSchema);
        $this->loadDataObjectFromStdObject($dataObject, $stdObject, $mode);
        $dataObject->beginLogging();
        if($mode == self::CREATE) $dataObject->logCreation();
        if($mode== self::READ) $dataObject->logRead();
        return $dataObject;
    }
    
    private function loadDataObjectFromStdObject($dataObject, $stdObject, $mode) 
    {      
        $this->loadPropertiesFromStdObject($dataObject, $stdObject);
        $this->loadChildrenFromStdObject($dataObject, $stdObject, $mode);
    }
    
    private function loadDataObjectArrayFromStdObject($arrayDataObject, $stdArray, $mode)
    {
        $schemaPath = $arrayDataObject->schemaPath;
        $objectSchema = $this->schema->getSchemaElement($schemaPath);
        for($i=0; $i<count($stdArray); $i++) {
            $stdObject = $stdArray[$i];
            $dataObject = $this->instanciateDataObject($objectSchema);
            $dataObject->beginLogging();
            if($mode == self::CREATE) $dataObject->logCreation();
            if($mode== self::READ) $dataObject->logRead();
            $arrayDataObject->append($dataObject);          
            $this->loadPropertiesFromStdObject($dataObject, $stdObject);
            $this->loadChildrenFromStdObject($dataObject, $stdObject, $mode);
        }
    }
     
    private function loadPropertiesFromStdObject($dataObject, $stdObject)
    {
        $propertiesObjects = $dataObject->properties;
        for($i=0; $i<count($propertiesObjects); $i++) {
            $propertyObject = $propertiesObjects[$i];
            $propertyName = $propertyObject->name;
            $propertyValue = $stdObject->{$propertyName};
            $propertyObject->setValue($propertyValue);
        }
    }
    
    private function loadChildrenFromStdObject($dataObject, $stdObject, $mode) 
    {
        $childrenObjects = $dataObject->children;
        for($i=0; $i<count($childrenObjects); $i++) {
            $childObject = $childrenObjects[$i];
            $childName = $childObject->name;
            $childStdObject = $stdObject->{$childName};
            if($childObject->isDataObjectArray) {
                $this->loadDataObjectArrayFromStdObject($childObject, $childStdObject, $mode);
            } else {
                $this->loadDataObjectFromStdObject($childObject, $childStdObject, $mode);
            }
        }
    }
    
    private function childrenToStdObject($dataObject, $stdObject)
    {
        foreach($dataObject as $childObject) {
            if(!is_object($childObject)) Die("Non object value are forbidden");
            if($childObject->isDataObjectProperty) {
                //echo "<br/>Adding property element $childName => $childObject";
                $this->propertyToStdObject($childObject, $stdObject);
            } elseif($childObject->isDataObject) {
                //echo "<br/><b>Adding child object $childName</b>";
                $this->objectToStdObject($childObject, $stdObject);
            } elseif($childObject->isDataObjectArray) {
                //echo "<br/><b>Adding array of $childName</b>";
                $this->arrayToStdObject($childObject, $stdObject);
            }
        }
    }
    
    private function objectToStdObject($dataObject, $stdObject) 
    {
        $objectName = $dataObject->name;
        $childStdObject = new stdClass();
        $stdObject->{$objectName} = $childStdObject;
        $this->childrenToStdObject($dataObject, $childStdObject);
    }
    
    private function arrayToStdObject($dataObjectArray, $stdObject)
    {
        $arrayName = $dataObjectArray->name;
        $array = Array();
        for($i=0; $i<count($dataObjectArray); $i++) {
            $childObject = $dataObjectArray[$i];
            $childStdObject = new stdClass();
            $array[] = $childStdObject;
            $this->childrenToStdObject($childObject, $childStdObject);
        }
        $stdObject->{$arrayName} = $array;
    }
    
    private function propertyToStdObject($dataObjectProperty, $stdObject) 
    {
        $propertyName = $dataObjectProperty->name;
        $stdObject->{$propertyName} = (string)$dataObjectProperty;
    }
    
    //*************************************************************************
    // XML
    //*************************************************************************
    public function getXmlDocument($dataObject) 
    {
        $Document = new DOMDocument();
        $rootXml = $Document->createElement($dataObject->name);
        $Document->appendChild($rootXml);
        $this->childrenToXml($dataObject, $rootXml); 
        
        return $Document;
    }
    
    public function getXmlString($dataObject)
    {
        $XmlDocument = $this->getXmlDocument($dataObject);
        $XmlString = $XmlDocument->saveXML();
        $XmlPrettyString = $this->formatXmlString($XmlString);
        return $XmlPrettyString;
    }
    
    private function childrenToXml($dataObject, $parentXml) 
    {
        //echo "<br/><b>Adding ".count($dataObject)." children elements to $parentName</b>";
        foreach($dataObject as $childObject) {
            if(!is_object($childObject)) Die("Non object value are forbidden");
            if($childObject->isDataObjectProperty) {
                //echo "<br/>Adding property element $childName => $childObject";
                $this->propertyToXml($childObject, $parentXml);
            } elseif($childObject->isDataObject) {
                //echo "<br/><b>Adding child object $childName</b>";
                $this->objectToXml($childObject, $parentXml);
            } elseif($childObject->isDataObjectArray) {
                //echo "<br/><b>Adding array of $childName</b>";
                $this->arrayToXml($childObject, $parentXml);
            }
        }
    }
    
    private function objectToXml($dataObject, $parentXml) 
    {
        $objectXml = $parentXml->ownerDocument->createElement($dataObject->name);
        $parentXml->appendChild($objectXml);
        $this->childrenToXml($dataObject, $objectXml);
    }
    
    private function arrayToXml($dataObjectArray, $parentXml)
    {
        for($i=0; $i<count($dataObjectArray); $i++) {
            $childObject = $dataObjectArray[$i];
            //echo "<br/>Adding array item #$i";
            $this->objectToXml($childObject, $parentXml);
        }
    }
    
    private function propertyToXml($dataObjectProperty, $parentXml) 
    {
        if(strlen($dataObjectProperty->value) > 0) {
            $propertyXml = $parentXml->ownerDocument->createElement($dataObjectProperty->name, $dataObjectProperty->value);
        } else {
            $propertyXml = $parentXml->ownerDocument->createElement($dataObjectProperty->name);
        }
        $parentXml->appendChild($propertyXml);
    }
    
    private function formatXmlString($xml) 
    {  
        // add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
        $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);
        
        // now indent the tags
        $token      = strtok($xml, "\n");
        $result     = ''; // holds formatted version as it is built
        $pad        = 0; // initial indent
        $matches    = array(); // returns from preg_matches()
        
        // scan each line and adjust indent based on opening/closing tags
        while ($token !== false) {
            // 1. open and closing tags on same line - no change
            if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) { 
                $indent = 0;
            // 2. closing tag - outdent now
            } elseif (preg_match('/^<\/\w/', $token, $matches)) {
                $indent = 0;
                $pad--;
            // 3. opening tag - don't pad this one, only subsequent tags
            } elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) {
                $indent = 1;
            // 4. no indentation needed
            } else {
                $indent = 0; 
            }
            
            // pad the line with the required number of leading spaces
            $line    = str_pad($token, strlen($token)+($pad*4), ' ', STR_PAD_LEFT);
            $result .= $line . "\n"; // add to the cumulative result, with linefeed
            $token   = strtok("\n"); // get the next token
            $pad    += $indent; // update the pad size for subsequent lines    
        } 
        
        return $result;
    }

    
    
}
<?php
/******************************************************************************
**  Maarch DataObject Schema Definition
**
**  Schema root
**      Root Elements (defined directly under schema tag)
**          Appinfos :
**              Das Source : 
**                  type [database | xml], 
**                  source [db table | xml file],
**              Das relation : 
**                  database : parent-key element, child key element
**                  xml : parent-key xpath, child key xpath
**          complexType 
**              Property elements : database columns / xml text elements
**              Child elements -> Root Elements included as child
******************************************************************************/


class Schema extends DOMDocument {
    
    private $includes = array();
    
    public function processInclusions($Schema) 
    {
        $Schema->registerNodeClass('DOMElement', 'SchemaElement');
        $Schema->Schema->formatOutput = true;
        $Schema->Schema->preserveWhiteSpace = true;
        
        $xpath = new DOMXPath($Schema);
        $schema = $xpath->query('/xsd:schema')->item(0);
        $includes = $xpath->query('./xsd:include', $schema);
        for($i=0; $i<$includes->length; $i++) {
            $include = $includes->item($i);
            $schemaLocation = $include->schemaLocation;
            if(!$this->isIncluded($schemaLocation)) {
                $includeSchema = new Schema();
                $includeSchema->load($_SESSION['config']['corepath'] . $schemaLocation);
                $this->processInclusions($includeSchema);
                $includeXpath = new DOMXpath($includeSchema);
                $schemaContents = $includeXpath->query('/xsd:schema/*');
                for($j=0; $j<$schemaContents->length; $j++) {
                    $importNode = $schemaContents->item($j);
                    $importedNode = $Schema->importNode($importNode, true);
                    $Schema->documentElement->appendChild($importedNode);
                }
                $this->includes[] = $schemaLocation;
            }
            $schema->removeChild($include);
        }
    }
    
    function isIncluded($schemaLocation)
    {
        if (in_array($schemaLocation, $this->includes)) {
            return true;
        }
    }
    
    
    
}

class SchemaElement extends DOMElement {

    function __get($name) 
    {
        if($this->hasAttribute($name)) {
            return $this->getAttribute($name);
        }
    }
    
    function __set($name, $value) 
    {
        $this->setAttribute($name, $value);
    }
    
}

?>
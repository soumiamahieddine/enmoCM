<?php

class Schema
	extends DOMDocument
{
	
    public $includedSchemaLocations = array();
    
    public function Schema()
    {     
        parent::__construct();
        $this->registerNodeClass('DOMNode', 'SchemaNode');
        $this->registerNodeClass('DOMElement', 'SchemaElement');
        $this->registerNodeClass('DOMAttr', 'SchemaAttribute');
    }
    
    public function loadXSD($xsdFile, $rootSchema=false)
    {
        $this->load($xsdFile);
        if(!$rootSchema) $rootSchema = $this;
        $this->processIncludes($this, $rootSchema);
    }
    
	public function processIncludes($schema, $rootSchema) 
    {
        $includes = $schema->getElementsByTagName('include');
        while($includes->length > 0) {
            $include = $includes->item(0);
            $schemaLocation = $include->getAttribute('schemaLocation');
            if(!in_array(
                $schemaLocation, 
                $rootSchema->includedSchemaLocations)
            ) {
                $includeSchema = new Schema();
                $includeSchema->loadXSD($schemaLocation, $rootSchema);
                $schemaContents = $includeSchema->documentElement->childNodes;
                for($j=0; $j<$schemaContents->length; $j++) {
                    $importNode = $schemaContents->item($j);
                    $importedNode = $schema->importNode($importNode, true);
                    $schema->documentElement->appendChild($importedNode);
                }
                $rootSchema->includedSchemaLocations[] = $schemaLocation;
            }
            $include->parentNode->removeChild($include);
        }
    }

}

class SchemaNode
    extends DOMNode
{

}


class SchemaElement
    extends DOMElement
{
    // On xsd:element / xsd:attribute
    public function hasDatasource()
    {
        if($this->hasAttribute('das:source')) return true;
    }
    
    public function getName()
    {
        return $this->getAttribute('name');
    }
    
    public function getTable()
    {
        if($this->hasAttribute('das:table')) {
            return $this->getAttribute('das:table');
        } else {
            return $this->getAttribute('name');
        }
    }
    
    public function getRightTable()
    {
        return $this->getAttribute('right-table');
    }
    
    public function getColumn()
    {
        if($this->hasAttribute('das:column')) {
            return $this->getAttribute('das:column');
        } else {
            return $this->getAttribute('name');
        }
    }
    
    public function isRequired()
    {
        if($this->getAttribute('use') == "required"
            || strtolower($this->getAttribute('nillable')) == "false"
        ) {
            return true;
        }
    }
    
    public function getTypeName()
    {
        return $this->getAttribute('type');
    }
    
    public function getFilter()
    {
        return $this->getAttribute('das:filter');
    }
    
    public function getRef()
    {
        return $this->getAttribute('ref');
    }
    
    public function hasDefault()
    {
        if($this->hasAttribute('default')) return true;
    }
    
    public function hasFixed()
    {
        if($this->hasAttribute('fixed')) return true;
    }
    
    // On xsd:complexType / xsd:simpleType or das:foreign-key
    public function getEnclosure()
    {
        if($this->getAttribute('das:enclosed') == 'true' 
            || $this->getAttribute('enclosed') == 'true') 
        {
            return "'";
        } else {
            return "";
        }
    }
    
}

class SchemaAttribute
    extends DOMAttr
{

}

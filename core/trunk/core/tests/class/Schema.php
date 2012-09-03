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
    
    public function loadXSD($schemaLocation, $rootSchema=false)
    {
        if(!is_file($schemaLocation)) {
            die("Failed to load schema definition file $schemaLocation");
        }
        $this->load($schemaLocation);
        if(!$rootSchema) $rootSchema = $this;
        $this->processIncludes($this, $rootSchema);
    }
    
	public function processIncludes($schema, $rootSchema) 
    {
        $includes = $schema->getElementsByTagName('include');
        while($includes->length > 0) {
            $include = $includes->item(0);
            $includeSchemaLocation = $include->getAttribute('schemaLocation');
            if(!in_array(
                $includeSchemaLocation, 
                $rootSchema->includedSchemaLocations)
            ) {
                $this->includeXSD($schema, $includeSchemaLocation, $rootSchema);
                /*$includeSchema = new Schema();
                $includeSchema->loadXSD($schemaLocation, $rootSchema);
                $schemaContents = $includeSchema->documentElement->childNodes;
                for($j=0; $j<$schemaContents->length; $j++) {
                    $importNode = $schemaContents->item($j);
                    $importedNode = $schema->importNode($importNode, true);
                    $schema->documentElement->appendChild($importedNode);
                }
                $rootSchema->includedSchemaLocations[] = $schemaLocation;*/
            }
            $include->parentNode->removeChild($include);
        }
    }
    
    public function includeXSD($schema, $includeSchemaLocation, $rootSchema)
    {
        $includeSchema = new Schema();
        $includeSchema->loadXSD($includeSchemaLocation, $rootSchema);
        $schemaContents = $includeSchema->documentElement->childNodes;
        for($j=0; $j<$schemaContents->length; $j++) {
            $importNode = $schemaContents->item($j);
            $importedNode = $schema->importNode($importNode, true);
            $schema->documentElement->appendChild($importedNode);
        }
        $rootSchema->includedSchemaLocations[] = $includeSchemaLocation;
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
        if($this->hasAttribute('das:source')) 
            return true;
    }
    
    public function getName()
    {
        if($this->hasAttribute('name')) {
            return $this->getAttribute('name');
        } elseif($this->hasAttribute('ref')) {
            return $this->getAttribute('ref');
        }
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
    
    public function getOperations() 
    {
        if($this->hasAttribute('das:operations')) {
            return $this->getAttribute('das:operations');
        } else {
            return '-R--L';
        }
    }
    
    public function isCreatable()
    {
        $operations = $this->getOperations();
        if(strstr($operations, 'C')) return true;
    }
    
    public function isReadable()
    {
        $operations = $this->getOperations();
        if(strstr($operations, 'R')) return true;
    }
    
    public function isUpdatable()
    {
        $operations = $this->getOperations();
        if(strstr($operations, 'U')) return true;
    }
    
    public function isDeletable()
    {
        $operations = $this->getOperations();
        if(strstr($operations, 'D')) return true;
    }
    
    public function isListable()
    {
        $operations = $this->getOperations();
        if(strstr($operations, 'L')) return true;
    }
    
    public function isRequired()
    {
        if($this->tagName == 'xsd:attribute' 
            && ( $this->getAttribute('use') == "required" 
                || $this->hasAttribute('default')
                || $this->hasAttribute('fixed')
            )
        ) {  
            return true;
        }
        if($this->tagName == 'xsd:element'
            &&( strtolower($this->getAttribute('nillable')) == "false"
                || $this->hasAttribute('default')
                || $this->hasAttribute('fixed')
                || $this->getAttribute('minOccurs') > 0
                || !$this->hasAttribute('minOccurs')
            )
        ) {
            return true;
        }
    }
    
    public function getFilter()
    {
        return $this->getAttribute('das:filter');
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

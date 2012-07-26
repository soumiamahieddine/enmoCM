<?php
class SchemaController
	extends DOMXpath
{
    
    public function getObjectElement($objectName)
    {   
        $objectElement = $this->query('/xsd:schema/xsd:element[@name = "'.$objectName.'"]')->item(0);
        if(!$objectElement) Die("Object $objectName is unknown");
        return $objectElement;
    }
    
	public function getTableName($objectElement) 
    {
        if($objectElement->hasAttribute('das:table')) {
            $tableName = $objectElement->getAttribute('das:table');
        } else {
            $tableName = $objectElement->getAttribute('name');
        }
        return $tableName;
    }
    
    public function getType($element)
    {
        if($element->hasAttribute('type')) {           
            $typeName = $element->getAttribute('type');
            if(!isset($this->dataObjectTypes[$typeName])) {
                if(substr($typeName, 0, 3) == 'xsd') {
                    $typeNode = $this->document->createElement('xsd:simpleType');
                    $typeNode->setAttribute('name', $typeName);
                    // Define if enclosed or not
                    $typeNode->setAttribute('das:enclosed', 'true');
                    
                    $this->document->appendChild($typeNode);
                } else {            
                    $typeNode = $this->query('//xsd:complexType[@name="'.$typeName.'"] | //xsd:simpleType[@name="'.$typeName.'"]')->item(0);
                }
                $this->dataObjectTypes[$typeName] = $typeNode;
            }
        } elseif($typeNode = $this->query('./xsd:complexType | ./xsd:simpleType', $element)->item(0)) {
            $typeName = $element->getAttribute('name');
            if(!isset($this->dataObjectTypes[$typeName])) {
                $this->dataObjectTypes[$typeName] = $typeNode;
            }
        }
        return $this->dataObjectTypes[$typeName];
    }
    
    public function getRefElement($refName)
    {
        $element = $this->query('/xsd:schema/xsd:element[@name="'.$refName.'"]')->item(0);
        return $element;
    }
    
    public function getRefAttribute($refName)
    {
        $attribute = $this->query('/xsd:schema/xsd:attribute[@name="'.$refName.'"]')->item(0);
        return $attribute;
    }
    
    public function getKey($objectElement)
    {      
        $key = $this->query('./xsd:key', $objectElement)->item(0);
        return $key;
    }
    
    public function getKeyFields($keyNode) 
    {
        $keyFields = $this->query('./xsd:field', $keyNode);
        return $keyFields;
    }
    
    public function getRelation($objectElement, $parentName)
    {
        $relation = $this->query('./xsd:annotation/xsd:appinfo/das:relation[@parent="'.$parentName.'"]', $objectElement)->item(0);
        return $relation;
    }
    
    public function getAttributes($parentNode)
    {
        $attributes = $this->query('./xsd:attribute', $parentNode);
        //echo "<br/>getAttributes() => " . $attributes->length;
        if($attributes->length == 0) return false;
        return $attributes;
    }
    
    public function getSequence($parentNode)
    {
        $sequences = $this->query('./xsd:sequence', $parentNode);
        //echo "<br/>getSequence() => " . $sequences->length;
        if($sequences->length == 0) return false;
        return $sequences->item(0);
    }
    
    public function getElements($parentNode)
    {
        $elements = $this->query('./xsd:element', $parentNode);
        //echo "<br/>getElements() => " . $elements->length;
        if($elements->length == 0) return false;
        return $elements;
    }
    
    //*************************************************************************
    // GET OBJECT PROPERTIES (ATTRIBUTES)
    //*************************************************************************
    public function getProperties($element, $excludeOptional=false)
    {
        $elementName = $element->getAttribute('name');
        if(!isset($this->dataObjectProperties[$elementName][(integer)$excludeOptional])) {
            $this->dataObjectProperties[$elementName][(integer)$excludeOptional] = $this->getDataObjectProperties($element, $excludeOptional);
        }
        return $this->dataObjectProperties[$elementName][(integer)$excludeOptional];
    }

    private function getDataObjectProperties($element, $excludeOptional)
    {
        $type = $this->getType($element);
        $properties = array();
        if($typeAttributes = $this->getAttributes($type)) {
            $properties = $this->selectAttributes($typeAttributes, $excludeOptional);
        }
               
        /*$complexTypeSimpleContent = $this->query('./xsd:simpleContent', $objectType);
        $complexTypeComplexContent = $this->query('./xsd:complexContent', $objectType);
        $complexTypeGroup = $this->query('./xsd:group', $objectType);
        $complexTypeAll = $this->query('./xsd:all', $objectType);
        $complexTypeSequence = $this->getSequence($objectType);
        $complexTypeChoice = $this->query('./xsd:choice', $objectType);
        $complexTypeAttributeGroup = $this->query('./xsd:attributeGroup', $objectType);
        $complexTypeAnyAttribute = $this->query('./xsd:anyAttribute', $objectType);*/
        
        $properties = array_merge(
            $properties
        );
        //echo "<br/>getProperties() => " . count($properties);
        return $properties;
    }
    
    private function selectAttributes($attributes, $excludeOptional)
    {
        $selectedAttributes = array();
        $attributesLength = $attributes->length;
        for($i=0; $i<$attributesLength; $i++) {
            $attribute = $attributes->item($i);
            if($excludeOptional 
                && $attribute->hasAttribute('use') 
                && ($attribute->getAttribute('use') == 'optional' 
                    ||$attribute->getAttribute('use') == 'prohibited')) {
                continue;
            }
            if($attribute->hasAttribute('ref')) {
                $attribute = $this->getRefAttribute($attribute->getAttribute('ref'));
            }  
            $selectedAttributes[] = $attribute;           
        }
        //echo "<br/>selectAttributes() => " . count($selectedAttributes);
        return $selectedAttributes;
    }   
    
    //*************************************************************************
    // GET CHILD OBJECT
    //*************************************************************************
    public function getChildren($element, $excludeOptional=false)
    {
        $elementName = $element->getAttribute('name');
        if(!isset($this->dataObjectChildren[$elementName][(integer)$excludeOptional])) {
            $this->dataObjectChildren[$elementName][(integer)$excludeOptional] = $this->getDataObjectChildren($element, $excludeOptional);
        }
        return $this->dataObjectChildren[$elementName][(integer)$excludeOptional];
    }

    private function getDataObjectChildren($element, $excludeOptional)
    {
        $type = $this->getType($element);
        $children = array();
        
        // type/sequence/element
        $sequenceElements = array();
        if($sequence = $this->getSequence($type)) {
            $sequenceElements = $this->getSequenceElements($sequence, $excludeOptional);
        }
        
        
        /*$complexTypeSimpleContent = $this->query('./xsd:simpleContent', $objectType);
        $complexTypeComplexContent = $this->query('./xsd:complexContent', $objectType);
        $complexTypeGroup = $this->query('./xsd:group', $objectType);
        $complexTypeAll = $this->query('./xsd:all', $objectType);
        $complexTypechoice = $this->query('./xsd:choice', $objectType);
        $complexTypeAttributes = $this->query('./xsd:attribute', $objectType);

        $complexTypeAttributeGroup = $this->query('./xsd:attributeGroup', $objectType);
        $complexTypeAnyAttribute = $this->query('./xsd:anyAttribute', $objectType);*/
        
        // Merge all results
        $children = array_merge(
            $sequenceElements
        );
        //echo "<br/>getChildren() => " . count($children);
        return $children;
    }
   
    private function getSequenceElements($sequence, $excludeOptional)
    {
        $sequenceElements = array();
        //echo "<br/>getSequenceChildObjectElements()";
        //any, choice, element, group, sequence
        /*$sequenceAny = $this->query('./xsd:any', $sequence);
        $sequencechoice = $this->query('./xsd:choice', $sequence);
        $sequenceGroup = $this->query('./xsd:group', $sequence);
        $sequenceSequence = $this->query('./xsd:sequence', $sequence);*/
        
        if($elements = $this->getElements($sequence)) {
            $sequenceElements = $this->selectChildElements($elements, $excludeOptional);
        }
        
        // Merge all results
        $sequenceElements = array_merge(
            $sequenceElements
        );
        //echo "<br/>getSequenceElements() => " . count($sequenceElements);
        return $sequenceElements;
    }
    
    private function selectChildElements($elements, $excludeOptional) 
    {
        $selectedChildElements = array();
        //echo "<br/>getElementsChildObjectElements for $elements->length elements";
        $elementsLength = $elements->length;
        for($i=0; $i<$elementsLength; $i++) {
            $element = $elements->item($i);
            if($excludeOptional 
                && $element->hasAttribute('minOccurs') 
                && $element->getAttribute('minOccurs') == 0) {
                continue;
            }
            if($element->hasAttribute('ref')) {
                $element = $this->getRefElement($element->getAttribute('ref'));
            } 
            $selectedChildElements[] = $element;
        }
        //echo "<br/>selectChildElements() => " . count($selectedChildElements);
        return $selectedChildElements;
    }
        
    
    
}
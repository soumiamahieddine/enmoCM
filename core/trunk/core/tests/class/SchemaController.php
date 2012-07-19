<?php
class SchemaController
	extends DOMXpath
{
    
	public function getTableName($objectNode) 
    {
        if($objectNode->hasAttribute('das:table')) {
            $tableName = $objectNode->getAttribute('das:table');
        } else {
            $tableName = $objectNode->getAttribute('name');
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
    
    public function getKey($objectNode)
    {      
        $key = $this->query('./xsd:key', $objectNode)->item(0);
        return $key;
    }
    
    public function getFields($keyNode) 
    {
        $keyFields = $this->query('./xsd:field', $keyNode);
        return $keyFields;
    }
    
    public function getRelation($objectNode, $parentName)
    {
        $relation = $this->query('./xsd:annotation/xsd:appinfo/das:relation[@parent="'.$parentName.'"]', $objectNode)->item(0);
        return $relation;
    }
    
    public function getChildObjects($objectNode)
    {
        $objectType = $this->getType($objectNode);
        $children = array();
        
        $complexTypeSequence = $this->getSequence($objectType);
        $children = array_merge($children, $this->getSequenceChildObjects($complexTypeSequence));
        
        
        /*$complexTypeSimpleContent = $this->query('./xsd:simpleContent', $objectType);
        $complexTypeComplexContent = $this->query('./xsd:complexContent', $objectType);
        $complexTypeGroup = $this->query('./xsd:group', $objectType);
        $complexTypeAll = $this->query('./xsd:all', $objectType);
        $complexTyp//echoice = $this->query('./xsd:choice', $objectType);
        $complexTypeAttributes = $this->query('./xsd:attribute', $objectType);

        $complexTypeAttributeGroup = $this->query('./xsd:attributeGroup', $objectType);
        $complexTypeAnyAttribute = $this->query('./xsd:anyAttribute', $objectType);*/
        return $children;
    }
    
    public function getSequence($parentNode)
    {
        $sequence = $this->query('./xsd:sequence', $parentNode)->item(0);
        return $sequence;
    }
    
    public function getElements($parentNode)
    {
        $elements = $this->query('./xsd:element', $parentNode);
        return $elements;
    }
    
    private function getSequenceChildObjects($sequence)
    {
        $children = array();
        //echo "<br/>getSequenceChildObjects()";
        //any, choice, element, group, sequence
        /*$sequenceAny = $this->query('./xsd:any', $sequence);
        $sequenc//echoice = $this->query('./xsd:choice', $sequence);
        $sequenceGroup = $this->query('./xsd:group', $sequence);
        $sequenceSequence = $this->query('./xsd:sequence', $sequence);*/
        
        $sequenceElements = $this->getElements($sequence);
        $children = array_merge($children, $this->getElementsChildObjects($sequenceElements));
        //echo "<br/>getSequenceChildObjects found " . count($children);
        return $children;
    }
    
    private function getElementsChildObjects($elements) 
    {
        $children = array();
        //echo "<br/>getElementsChildObjects for $elements->length elements";
        $elementsLength = $elements->length;
        for($i=0; $i<$elementsLength; $i++) {
            $element = $elements->item($i);
            if($element->hasAttribute('ref')) {
                $element = $this->getRefElement($element->getAttribute('ref'));
            } 
            if($child = $this->getElementChildObject($element)) {
                $children[] = $child;
            }
        }
        //echo "<br/>getElementsChildObjects found " . count($children);
        return $children;
    }
    
    private function getElementChildObject($element)
    {
        //echo "<br/>getElementChildObject for " . $element->getAttribute('name');
        $elementType = $this->getType($element);
        if($elementType->tagName == 'xsd:complexType') {
            //echo "<br/>" . $element->getAttribute('name') . " is a child";
            return $element;
        }
    }
    
}
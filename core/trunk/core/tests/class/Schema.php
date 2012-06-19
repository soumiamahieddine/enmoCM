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
    
    function __toString()
    {
        return (string)$this->C14N();
    }

}

?>
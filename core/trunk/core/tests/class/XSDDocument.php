<?php

class XSDDocument extends DOMDocument {
    

}

class XSDElement extends DOMElement {

    function __get($name) {
        if($this->hasAttribute($name)) {
            return $this->getAttribute($name);
        }
    }
    
    function __set($name, $value) {
    
        $this->setAttribute($name, $value);

    }

}

?>
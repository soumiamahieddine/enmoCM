<?php

class ViewController
    extends DOMDocument
{
    
    function ViewController()
    {
        parent::__construct();
        $this->registerNodeClass('DOMElement', 'ViewElement');
    }
    
    function createSelect()
    {
        $select = $this->createElement('select');
        return $select;
    }
    
    function createOption($value, $label)
    {
        $option = $this->createElement('option', $label);
        $this->setAttribute('value', $value);
        return $option;
    }
    
    function createOptionGroup($label)
    {
        $optionGroup = $this->createElement('optgroup');
        $this->setAttribute('label', $label);
        return $optionGroup;
    }
}

class ViewElement   
    extends DOMElement
{
    
    //*************************************************************************
    // Standard
    //*************************************************************************
    function getSource()
    {
        return $this->C14N();
    }
    
    function show() 
    { 
        echo $this->getSource();
    }
    
    function setId($id) 
    {
        $this->setAttribute('id', $id);
    }
    
    function setName($name) 
    {
        $this->setAttribute('name', $name);
    }
    
    function addOption($value, $label)
    {
        $option = $this->ownerDocument->createOption($value, $label);
        $this->appendChild($option);
    }
    
    function disable()
    {
        $this->setAttribute('disabled', 'disabled');
    }
    
    function enable() 
    {
        $this->removeAttribute('disabled');
    }
    
    function select()
    {
        $this->setAttribute('selected', 'selected');
    }
    
    function unselect()
    {
        $this->removeAttribute('selected');
    }
    
}

<?php

class ViewController
    extends DOMXPath
{
    //*************************************************************************
    // Constructor
    //*************************************************************************
    function ViewController()
    {
        
    }
    
    function loadView($viewFile)
    {
        $view = new View($this);
        $view->loadView($viewFile);
        parent::__construct($view);
        return $view;
    }
    
    function createSelect()
    {
        $select = $this->document->createElement('select');
        $this->appendChild($select);
        return $select;
    }
    
    function createOption($value, $label)
    {
        $option = $this->document->createElement('option', $label);
        $option->setAttribute('value', $value);
        return $option;
    }
    
    function createOptionGroup($label)
    {
        $optionGroup = $this->document->createElement('optgroup');
        $optionGroup->setAttribute('label', $label);
        return $optionGroup;
    }
}

//*****************************************************************************
// HTML PAGE / FRAGMENT
//*****************************************************************************
class View
    extends DOMDocument
{
    
    public $viewController;
    
    function View($viewController) 
    {
        $this->viewController = $viewController;
    }
    
    function loadView($viewFile) {
        $this->load($viewFile);
        $this->registerNodeClass('DOMElement', 'ViewElement');
        $this->registerNodeClass('DOMAttr', 'ViewAttribute');
        $this->registerNodeClass('DOMText', 'ViewText');
    }

}


//*****************************************************************************
// HTML TAGS
//*****************************************************************************
class ViewElement   
    extends DOMElement
{
    
    //*************************************************************************
    // Retrieve / display
    //*************************************************************************
    function getSource()
    {
        return $this->C14N();
    }
    
    function show() 
    { 
        echo $this->getSource();
    }
    
    //*************************************************************************
    // Standard attributes
    //*************************************************************************
    // class dir id lang title style 
    function setId($id) 
    {
        $this->setAttribute('id', $id);
    }
    
    function setName($name) 
    {
        $this->setAttribute('name', $name);
    }
    
    //*************************************************************************
    // Text
    //*************************************************************************
    
    //*************************************************************************
    // Inputs
    //*************************************************************************
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

//*****************************************************************************
// ATTRIBUTES
//*****************************************************************************
class ViewAttribute
    extends DOMAttr
{

}

//*****************************************************************************
// TEXT
//*****************************************************************************
class ViewText
    extends DOMText
{

}

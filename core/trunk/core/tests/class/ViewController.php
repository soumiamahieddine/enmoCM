<?php

require_once(
    'core/tests/class/MessageController.php'
);

class ViewController
    extends DOMXPath
{
    //*************************************************************************
    // Constructor
    //*************************************************************************
    function ViewController()
    {
        
    }
    
    function loadHTMLFile($viewFile)
    {
        $view = new View();
        $view->loadHTMLFile($viewFile);
        if(!$view->encoding) $view->encoding = 'UTF-8';
        
        parent::__construct($view);
        $this->view = $this->document;
        return $this->view;
    }
    
    function loadHTML($viewString)
    {
        $view = new View();
        $view->loadHTML($viewString);
        if(!$view->encoding) $view->encoding = 'UTF-8';
        
        parent::__construct($view);
        $this->view = $this->document;
        return $this->view;
    }

    function showView()
    {
        print $this->document->saveHTML();
    }  
        
    //*************************************************************************
    // Get tags
    //*************************************************************************
    function getElementById($id, $contextNode=false)
    {
        if(!$contextNode) $contextNode = $this->view->documentElement;
        $elements = $this->query("//*[@id='$id']", $contextNode);
        if($elements->length == 0) return false;
        return $elements->item(0);
    }
    
    function getIds()
    {
        $ids = $this->query("//@id");
        return $ids;
    }
    
    function getLabelFor($for)
    {     
        $labels = $this->query("//label[@for='$for']");
        if($labels->length == 0) return false;
        return $labels->item(0);
    }
    
    function getLabels()
    {
        $labels = $this->query("//label");
        return $labels;
    }
    
    function getImgs()
    {
        $imgs = $this->query("//img | //IMG");
        return $imgs;
    }
    
    function getScripts()
    {
        $scripts = $this->query("//script | //SCRIPT");
        return $scripts;
    }
    
    function getTableHeaderCols()
    {
        $th = $this->query("//th[@axis]");
        if($th->length == 0) return false;
        return $th;
    }
    
    function getDataTranslate()
    {
        $dataTranslate = $this->query("//*[@data-translate]");
        if($dataTranslate->length == 0) return false;
        return $dataTranslate;
    }
    
    //*************************************************************************
    // Update tags
    //*************************************************************************
    function setLabelFor($for, $text)
    {
        $label = $this->getlabelFor($for);
        if($label) $label->nodeValue = htmlentities($text, 0, $this->document->encoding);
    }
    
    function setUniqueIds(
        $RefData = false
    ) {
        if($RefData && method_exists($RefData, 'getNodePath')) {
            $uniqueId = $RefData->getNodePath();
        } else {
            $uniqueId = uniqid();
        }
        
        // Add uniqueId prefix to ids
        $ids = $this->getIds();
        for ($i=0; $i<$ids->length; $i++) {
            $id = $ids->item($i);
            $localId = $id->nodeValue;
            
            if(strpos($localId, $uniqueId) === 0) continue; 
            
            /*$prefix = '';
            if($localId && $RefData->hasAttribute($localId)) {
                $prefix = '@';
            }*/
            $newId = $uniqueId;
            if($localId) {
                $newId .= '/' . $prefix . $localId;
            }
            $id->nodeValue = $newId;
        }
        
        // Add uniqueId to label fors
        $labels = $this->getLabels();
        for ($i=0; $i<$labels->length; $i++) {
            $label = $labels->item($i);
            $localLabelFor = $label->getAttribute('for');
            if(strpos($localLabelFor, $uniqueId) === 0) continue; 
            
            /*$prefix = '';
            if($localLabelFor && $RefData->hasAttribute($localLabelFor)) {
                $prefix = '@';
            }*/
            $newLabelFor = $uniqueId;
            if($localLabelFor) {
                $newLabelFor .= '/' . $localLabelFor;
            }
            $label->setAttribute(
                'for',
                $newLabelFor
            );
        }
    }
    
    function setSrcBaseUrl($tagName, $baseUrl)
    {
        $tags = $this->view->getElementsByTagName($tagName);
        $tagsLength = $tags->length;
        for ($i=0; $i<$tagsLength; $i++) {
            $tag = $tags->item($i);
            $tag->setAttribute(
                'src',
                $baseUrl . $tag->getAttribute('src')
            );
        }  
    }
    
    function populateWithXML(
        $XMLElement,
        $create=false
    ) {
        /***************************************************************************
        **  Attributes
        ***************************************************************************/
        foreach($XMLElement->attributes as $attribute) {
            if($input = 
                $this->getElementById(
                    $attribute->nodeName
                )
            ) {
                $input->setValue($attribute->nodeValue);
            }
        }
           
        /***************************************************************************
        **  Elements
        ***************************************************************************/
        $childElements = $XMLElement->childNodes;
        $childCount = $childElements->length;
        for($i=0; $i<$childCount; $i++) {
            $childElement = $childElements->item($i);
            if($childElement->nodeType === 1 
                && $childElement->nodeValue != '' 
                && $input = $this->getElementById(
                        $childElement->tagName
                    )
            ) {
                $input->setValue($childElement->nodeValue);
            }
        }
    
    }
    
    function translate($MessageController)
    {
      
        $labels = $this->getLabels();
        for ($i=0; $i<$labels->length; $i++) {
            $label = $labels->item($i);
            $labelFor = $label->getAttribute('for');
            $labelText = $MessageController->getMessageText(
                $labelFor
            );
            if($labelText == $labelFor) continue;
            $label->nodeValue = $labelText;
        }
        
        $buttons = $this->query('//input[@type="button"]');
        for($i=0; $i<$buttons->length; $i++) {
            $button = $buttons->item($i);
            if($button->hasAttribute('value')) {
                $button->setValue(
                    $MessageController->getMessageText(
                        $button->getAttribute('value')
                    )
                );
            }
        }
        
        $titles = $this->query('//@title');
        for($i=0; $i<$titles->length; $i++) {
            $title = $titles->item($i);
            $title->nodeValue = 
                $MessageController->getMessageText(
                    $title->nodeValue
                );
        }
        
        $translates = $this->query('//*[@data-translate != ""]');
        for($i=0; $i<$translates->length; $i++) {
            $translate = $translates->item($i);
            $message = $translate->getAttribute('data-translate');
            $translation = 
                $MessageController->getMessageText(
                    $message
                );
            if($translate->hasAttribute('value')) {
                $translate->setAttribute(
                    'value', 
                    $translation
                );
            } else {
                $translate->nodeValue = $translation;
            }
        }
    
    }
    
    
}

//*****************************************************************************
// HTML PAGE / FRAGMENT
//*****************************************************************************
class View
    extends DOMDocument
{
    
    //*************************************************************************
    // Constructor
    //*************************************************************************
    function View() 
    {
        parent::__construct();
        $this->registerNodeClass('DOMElement', 'ViewElement');
        $this->registerNodeClass('DOMAttr', 'ViewAttribute');
        $this->registerNodeClass('DOMText', 'ViewText');
        $this->validateOnParse = true;
    }
    
    //*************************************************************************
    // Create tags
    //*************************************************************************
    function createSelect()
    {
        $select = $this->createElement('select');
        $this->appendChild($select);
        return $select;
    }
    
    function createOption($value, $label)
    {
        $option = $this->createElement('option', $label);
        $option->setAttribute('value', $value);
        return $option;
    }
    
    function createOptionGroup($label)
    {
        $optionGroup = $this->createElement('optgroup');
        $optionGroup->setAttribute('label', $label);
        return $optionGroup;
    }
    
    function appendViewNode($ViewNode, $parentNode)
    {
        $parentNode->appendChild($this->importNode($ViewNode, true));
    }
    
    function replaceViewNode($ViewNode, $replaceNode) {
        $replaceNode->parentNode->replaceChild(
            $this->importNode($ViewNode, true),
            $replaceNode
        );
    }
    
    //*************************************************************************
    // Outputs
    //*************************************************************************
    public function show()
    {
        echo $this->saveHTML();
    }
    
    public function getHTML()
    {
        return $this->saveHTML();
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
    
    function setValue($value)
    {
        $this->setAttribute('value', $value);
    }
    
    //*************************************************************************
    // Custom HTML5 attributes
    //*************************************************************************
    function setDataAttribute($name, $value)
    {
        //$this->setAttribute('data-' . $name, htmlentities($value, 0, $this->encoding));
        $this->setAttribute('data-' . $name, $value);
    }
    
    //*************************************************************************
    // Inputs
    //*************************************************************************
    function addOption($value, $label)
    {
        $option = $this->ownerDocument->createOption($value, $label);
        $this->appendChild($option);
        return $option;
    }
    
    function addOptionGroup($label)
    {
        $optionGroup = $this->ownerDocument->createOptionGroup($label);
        $this->appendChild($optionGroup);
        return $optionGroup;
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

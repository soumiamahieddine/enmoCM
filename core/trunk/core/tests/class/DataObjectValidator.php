<?php
class DataObjectValidator 
{
    private $status;
	private $errors;
	
    public function validateDataObject($dataObject, $schema)
    {
        $XmlDocument = $dataObject->asXmlDocument();
       
        libxml_use_internal_errors(true);
        if($XmlDocument->schemaValidateSource($schema->saveXML())) {
            return true;
        } else {
            $libXMLErrors = libxml_get_errors();
            foreach ($libXMLErrors as $libXMLError) {
                $this->errors[] = new DataObjectValidatorError($libXMLError);
            }
            return false;
        } 
        libxml_clear_errors();
    }
    
    function getErrors() 
    {
        return $this->errors;
    }
}

class DataObjectValidatorError
{
    function DataObjectValidatorError($libXMLError)
    {
        $this->level = $libXMLError->level;
        $this->code = $libXMLError->code;
        $this->message = $libXMLError->message;
    }

}
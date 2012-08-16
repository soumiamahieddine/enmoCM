<?php

class basketCMIS
{
    var $strReturn = 'THE RESULT IS A BASKET WITH ';
    
    public function entryMethod ($atomFileContent, $requestedResourceId)
    {
        return $this->strReturn . $requestedResourceId . ' ' . $atomFileContent;
    }
    
}


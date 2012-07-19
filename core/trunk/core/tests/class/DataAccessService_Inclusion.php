<?php
class DataAccessService_Inclusion 
    extends schemaController
{
    public $parser;
    
    public function connect(
        $parser
    ) 
    {
        $this->parser = $parser;
    }
 

}
<?php

require_once('SOAP/Disco.php');
require_once('core/class/Url.php');

class Maarch_SOAP_DISCO_Server extends SOAP_DISCO_Server
{
    public function __construct() 
    {
        $funcGetArgs = func_get_args();
        call_user_func_array(array(parent, 'SOAP_DISCO_Server'),
                             $funcGetArgs);

        $this->host = array_key_exists('HTTP_X_FORWARDED_HOST', $_SERVER) 
                           ? $_SERVER['HTTP_X_FORWARDED_HOST']
                           : $_SERVER['HTTP_HOST'];
    }
    
    private function selfUrl()
    {
        $rootUri = self::_getRootUri();
        $protocol = ( (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == 'on') ||
                      (array_key_exists('HTTP_FORCE_HTTPS', $_SERVER) && $_SERVER['HTTP_FORCE_HTTPS'] == 'on') )
            ? 'https://' : 'http://' ;
        $lastChar = strlen($rootUri) - 1;
        if ($rootUri[$lastChar] != '/') {
            $rootUri .= '/';
        }
        return $protocol . $this->host . $rootUri . basename(Url::scriptName());
    }
    
    private static function _getRootUri()
    {
        return Url::baseUri();
    }
    
    public function _generate_WSDL()
    {
        parent::_generate_WSDL();

        $this->_wsdl['definitions']['service']['port']['soap:address']['attr']['location'] = 
            $this->selfUrl();
        
        $this->_generate_WSDL_XML();
    }
    
    public function _generate_DISCO()
    {
        parent::_generate_DISCO();
        
        $this->_disco['disco:discovery']['scl:contractRef']['attr']['ref'] =
            $this->selfUrl() . '?wsdl';

        // generate disco xml
        $this->_generate_DISCO_XML($this->_disco);
    }
}

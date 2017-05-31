<?php

    namespace MaarchTest;

    require_once __DIR__.'/define.php';

    class ParametersControllerTest extends \PHPUnit_Framework_TestCase
    {

        public function testCreate()
        {
            
            $aArgs = [
                'id'=> 'TEST',
                'description' => null,
                'param_value_string' => 'abcd',
                'param_value_int' => null,
                'param_value_date' => null                
            ];

            $client = new \GuzzleHttp\Client([
                'base_uri' => 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/parameters',
                // You can set any number of default request options.
                'timeout'  => 2.0,
                ]);
            $response = $client->request('POST', 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/parameters', [
                'auth'=> ['superadmin','superadmin'],
                'form_params' => $aArgs
            ]);

            $compare = '[{"id":"TEST","description":null,'
                . '"param_value_string":"abcd","param_value_int":null,"param_value_date":null}]';
            $this->assertSame($compare,(string)$response->getBody());
           
            //TEST EXISTE DEJA
            $aArgs = [
                'id'=> 'TEST',
                'description' => null,
                'param_value_string' => null,
                'param_value_int' => 1234,
                'param_value_date' => null                
            ];

            $client = new \GuzzleHttp\Client([
                'base_uri' => 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/parameters',
                // You can set any number of default request options.
                'timeout'  => 2.0,
                ]);
            $response = $client->request('POST', 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/parameters', [
                'auth'=> ['superadmin','superadmin'],
                'form_params' => $aArgs
            ]);

            $compare = '{"errors":["Identifiant TEST existe d\u00e9j\u00e0 !"]}';
            
            $this->assertSame($compare,(string)$response->getBody());

            //AUCUN PARAMETRE
            
            $aArgs = [
                'id'=> 'TEST3',
                'description' => null,
                'param_value_string' => null,
                'param_value_int' => null,
                'param_value_date' => null                
            ];
            
            $client = new \GuzzleHttp\Client([
                'base_uri' => 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/parameters',
                // You can set any number of default request options.
                'timeout'  => 2.0,
                ]);
            $response = $client->request('POST', 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/parameters', [
                'auth'=> ['superadmin','superadmin'],
                'form_params' => $aArgs
            ]);

            $compare = '{"errors":["_PARAM_VALUE_IS_EMPTY"]}';
            
            $this->assertSame($compare,(string)$response->getBody());

            //AUCUN ARGUMENTS
            $aArgs = [ ];

            $client = new \GuzzleHttp\Client([
                'base_uri' => 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/parameters',
                // You can set any number of default request options.
                'timeout'  => 2.0,
                ]);
            $response = $client->request('POST', 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/parameters', [
                'auth'=> ['superadmin','superadmin'],
                'form_params' => $aArgs
            ]);

            $compare = '{"errors":["_ID_IS_EMPTY_CONTROLLER","_PARAM_VALUE_IS_EMPTY"]}';

            $this->assertSame($compare,(string)$response->getBody());

            //DATE MAUVAIS FORMAT

            $aArgs = [
                'id'=> 'TEST4',
                'description' => null,
                'param_value_string' => null,
                'param_value_int' => null,
                'param_value_date' => '123456'                
            ];

            $client = new \GuzzleHttp\Client([
                'base_uri' => 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/parameters',
                // You can set any number of default request options.
                'timeout'  => 2.0,
                ]);
            $response = $client->request('POST', 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/parameters', [
                'auth'=> ['superadmin','superadmin'],
                'form_params' => $aArgs
            ]);

            $compare ='{"errors":["PARAMETRE DATE INVALIDE."]}';
            $this->assertSame($compare,(string)$response->getBody());

            //TEST ID MAUVAIS FORMAT (REGEX)

            $aArgs = [
                'id'=> 'A*-#==',
                'description' => "*///*//",
                'param_value_string' => "//-//**",
                'param_value_int' => null,
                'param_value_date' => null                
            ];

            $response = $client->request('POST', 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/parameters', [
                'auth'=> ['superadmin','superadmin'],
                'form_params' => $aArgs
            ]);

            $compare ='{"errors":["ID INVALIDE","DESCRIPTION INVALIDE","PARAM STRING INVALIDE"]}';
            $this->assertSame($compare,(string)$response->getBody());            
        }

        public function testGetList()
        {
            $client = new \GuzzleHttp\Client([
                'base_uri' => 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/parameters',
                // You can set any number of default request options.
                'timeout'  => 2.0,
                ]);
            $response = $client->request('GET', 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/parameters', [
                'auth'=> ['superadmin','superadmin']
            ]);

            $this->assertNotNull($response->getBody());
        }
        
        public function testGetById()
        {           

            $aArgs = [
                'id'=> 'TEST'
            ];

            $client = new \GuzzleHttp\Client([
                'base_uri' => 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/parameters',
                // You can set any number of default request options.
                'timeout'  => 2.0,
                ]);
            $response = $client->request('GET', 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/parameters/'.$aArgs['id'], [
                'auth'=> ['superadmin','superadmin']
            ]);
            $compare = '[[{"id":"TEST",'
                        .'"description":null,'
                        .'"param_value_string":"abcd",'
                        .'"param_value_int":null,'
                        .'"param_value_date":null}]]';

            $this->assertNotNull((string)$response->getBody());
        }

        public function testUpdate()
        {
            
            $aArgs = [
                'id'=> 'TEST',
                'description' =>'TEST AFTER UP'
            ];

            $client = new \GuzzleHttp\Client([
                'base_uri' => 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/parameters',
                // You can set any number of default request options.
                'timeout'  => 2.0,
                ]);
            $response = $client->request('PUT', 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/parameters/'.$aArgs['id'], [
                'auth'=> ['superadmin','superadmin'],
                'form_params' => $aArgs
            ]);

            $compare = '[{"id":"TEST",'
                        .'"description":"TEST AFTER UP",'
                        .'"param_value_string":"abcd",'
                        .'"param_value_int":null,'
                        .'"param_value_date":null}]';
            
            $this->assertSame($compare,(string)$response->getBody());
            
            //TEST ID NULL
            $aArgs = [
                'id'=> 'NEW'
            ];

            $client = new \GuzzleHttp\Client([
                'base_uri' => 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/parameters',
                // You can set any number of default request options.
                'timeout'  => 2.0,
                ]);
            $response = $client->request('PUT', 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/parameters/'.$aArgs['id'], [
                'auth'=> ['superadmin','superadmin'],
                'form_params' => $aArgs
            ]);

            $compare = '{"errors":["Identifiant n\'existe pas"]}';
            
            $this->assertSame($compare,(string)$response->getBody());

        
        }

        public function testDelete()
        {
            $aArgs = [
                'id'=> 'TEST'
            ];

            $client = new \GuzzleHttp\Client([
                'base_uri' => 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/parameters',
                // You can set any number of default request options.
                'timeout'  => 2.0,
                ]);
            $response = $client->request('DELETE', 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/parameters/'.$aArgs['id'], [
                'auth'=> ['superadmin','superadmin'],
                'form_params' => $aArgs
            ]);
            $compare='true';
            $this->assertSame($compare,(string)$response->getBody());
        }

    }

?>
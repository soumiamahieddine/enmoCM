<?php

    namespace MaarchTest;

    require_once __DIR__.'/define.php';
    use Psr\Http\Message\ServerRequest;
    use Psr\Http\Message\RequestInterface;
    use Psr\Http\Message\ResponseInterface;

    class PrioritiesControllerTest extends \PHPUnit_Framework_TestCase
    {
        public function testUpdatePriorities()
        {

            $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
            $request     = \Slim\Http\Request::createFromEnvironment($environment);

            $aArgs = [
                'priority1'=> [
                    'label' => 'labelTest',
                    'number'=> '123',
                    'wdays' => 'true'
                ],
                'priority2' => [
                    'label' => 'labelTest2',
                    'number'=> '123',
                    'wdays' => 'true'
                ]
            ];
            
            $request = \httpRequestCustom::addContentInBody($aArgs, $request);

            $priority = new \Core\Controllers\PrioritiesController();
            $response = $priority->updatePriorities($request, new \Slim\Http\Response(), []);

            //TEST TOUT OK
            $compare = "OK";
            $this->assertSame($compare, $response);

            //TEST INFOS VIDES
            $aArgs = [
                'priority1'=> [
                                'label' => null,
                                'number'=> 123,
                                'wdays' => 'true'
                    ],
                'priority2' => [
                                'label' => 'labelTest2',
                                'number'=> 123,
                                'wdays' => 'true'
                ]
            ];

            $client = new \GuzzleHttp\Client([
                'base_uri' => 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/priorities',
                'timeout'  => 2.0
            ]);

            $response = $client->request('PUT','http://127.0.0.1/maarch_courrier/cs_Maarch/rest/priorities',
            ['auth' =>['superadmin','superadmin'],
            'form_params' => $aArgs
            ]);
            $compare ='{"errors":["INFOS VIDES"]}';
            $this->assertSame($compare,(string)$response->getBody());

            //TEST wdays INVALIDE
            $aArgs = [
                'priority1'=> [
                                'label' => 'labelTest',
                                'number'=> 123,
                                'wdays' => 'true'
                    ],
                'priority2' => [
                                'label' => 'labelTest2',
                                'number'=> 123,
                                'wdays' => 'invalide'
                ]
            ];

            $client = new \GuzzleHttp\Client([
                'base_uri' => 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/priorities',
                'timeout'  => 2.0
            ]);

            $response = $client->request('PUT','http://127.0.0.1/maarch_courrier/cs_Maarch/rest/priorities',
            ['auth' =>['superadmin','superadmin'],
            'form_params' => $aArgs
            ]);
            $compare ='{"errors":["INFOS wdays INVALIDE"]}';
            $this->assertSame($compare,(string)$response->getBody());

            //TEST NUMERO INVALIDE
            $aArgs = [
                'priority1'=> [
                                'label' => 'labelTest',
                                'number'=> '123Quatre',
                                'wdays' => 'true'
                    ],
                'priority2' => [
                                'label' => 'labelTest2',
                                'number'=> 123,
                                'wdays' => 'true'
                ]
            ];

            $client = new \GuzzleHttp\Client([
                'base_uri' => 'http://127.0.0.1/maarch_courrier/cs_Maarch/rest/priorities',
                'timeout'  => 2.0
            ]);

            $response = $client->request('PUT','http://127.0.0.1/maarch_courrier/cs_Maarch/rest/priorities',
            ['auth' =>['superadmin','superadmin'],
            'form_params' => $aArgs
            ]);
            $compare ='{"errors":["NUMERO INVALIDE"]}';
            $this->assertSame($compare,(string)$response->getBody());
            */

        }

        public function testDeletePriority(){
            $query = 'id=TEST';

            $environment = \Slim\Http\Environment::mock([
                'REQUEST_METHOD' => 'DELETE',
                'QUERY_STRING'   => $query,
            ]);

            $parameter = new \Core\Controllers\ParametersController();
            $response = $parameter->delete(\Slim\Http\Request::createFromEnvironment($environment), new \Slim\Http\Response(), ['id' => 'TEST']);

            $compare = 'true';
            $this->assertSame($compare,(string)$response->getBody());
            
        }
        
    }
?>
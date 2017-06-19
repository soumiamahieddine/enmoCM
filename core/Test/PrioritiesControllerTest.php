<?php

namespace MaarchTest;

require_once __DIR__.'/define.php';
require_once 'apps/maarch_entreprise/services/Table.php';

class PrioritiesControllerTest extends \PHPUnit_Framework_TestCase
{

    public function testGetList(){
        $priorities = new \Core\Controllers\PrioritiesController();
        
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD'    =>  'GET'
        ]);
        $response = $priorities->getList(\Slim\Http\Request::createFromEnvironment($environment), new \Slim\Http\Response());
        $this->assertNotNull((string)$response->getBody());
    }

    public function testCreate()
    {
        $query = 'label_priority=priorityCreated&';
        $query .='color_priority=#ffffff&';
        $query .='working_days=Y&';
        $query .='delays=2';

        $environment = \Slim\Http\Environment::mock([
        'REQUEST_METHOD' => 'POST',
        'QUERY_STRING'   =>$query 
        ]);

        $priorities = new \Core\Controllers\PrioritiesController();
        $response = $priorities->create(\Slim\Http\Request::createFromEnvironment($environment), new \Slim\Http\Response(),null);

        //RECUPERATION DU DERNIER ID CREE
        $table = new \Apps_Table_Service();
        $tabPriorities = $table->select([
            'select'    => empty($aArgs['select']) ?  ['*'] : $aArgs['select'],
            'table'     => ['priorities'],
            'orderby'      => 'id'
        ]);
        var_dump($tabPriorities);
        end($tabPriorities);
        $key =key($tabPriorities);
        $id = $tabPriorities[$key]['id'];

        $compare = '[{"id":'.$id.',"label_priority":"priorityCreated","color_priority":"#ffffff","working_days":"Y","delays":"2"}]';
        $this->assertSame($compare,(string)$response->getBody());


        $query = 'label_priority=priorityError&';
        $query .='color_priority=#ffffff&';
        $query .='working_days=Y&';
        $query .='delays=';

        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'QUERY_STRING'   =>$query 
        ]);

        $priorities = new \Core\Controllers\PrioritiesController();
        $response = $priorities->create(\Slim\Http\Request::createFromEnvironment($environment), new \Slim\Http\Response(),null);
    }
    public function testUpdate()
    {
        var_dump('UPDATE');
        $table = new \Apps_Table_Service();
        $tabPriorities = $table->select([
            'select'    => empty($aArgs['select']) ?  ['*'] : $aArgs['select'],
            'table'     => ['priorities'],
            'orderby'      => 'id'
        ]);
        var_dump($tabPriorities);
        end($tabPriorities);
        $key =key($tabPriorities);
        $id = $tabPriorities[$key]['id'];
        //TEST TOUT OK
        $query ='id='.$id.'&';
        $query .= 'label_priority=labelTest&';
        $query .='delays=15&';
        $query .='working_days=Y';

        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'PUT',
            'QUERY_STRING'   =>$query
        ]);


        
        $priorities = new \Core\Controllers\PrioritiesController();
        $response = $priorities->update(\Slim\Http\Request::createFromEnvironment($environment), new \Slim\Http\Response(), ['id' => $id]);

        $compare = '[{"id":'.$id.',"label_priority":"labelTest","color_priority":"#ffffff","working_days":"Y","delays":"15"}]';

        $this->assertSame($compare,(string)$response->getBody());
        

        /*
        $aArgs = [
            'priority1'=> [
                            'label' => 'labelTest',
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
        $compare ="OK";
        $this->assertSame($compare,(string)$response->getBody());

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

    public function testDelete(){
        var_dump('DELETE');
        $table = new \Apps_Table_Service();
        $tabPriorities = $table->select([
            'select'    => empty($aArgs['']) ?  ['*'] : $aArgs['select'],
            'table'     => ['priorities'],
            'orderby'      => 'id'
        ]);
        
        end($tabPriorities);
        $key =key($tabPriorities);
        $id = $tabPriorities[$key]['id'];
        $query = 'id='.$id;

        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'DELETE',
            'QUERY_STRING'   => $query,
        ]);
        
        $priorities = new \Core\Controllers\PrioritiesController();
        $response = $priorities->delete(\Slim\Http\Request::createFromEnvironment($environment), new \Slim\Http\Response(), ['id' => $id]);
        var_dump((string)$response->getBody());
        $compare = 'true';
        $this->assertSame($compare,(string)$response->getBody());
        
    }
    
}
?>
<?php

namespace MaarchTest;

require_once __DIR__.'/define.php';
require_once 'apps/maarch_entreprise/services/Table.php';

class PrioritiesControllerTest extends \PHPUnit_Framework_TestCase
{   

    public function testCreate()
    {
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'POST'
        ]);        
        $priorities = new \Core\Controllers\PrioritiesController();
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $table = new \Apps_Table_Service();

        $aArgs = [
            'color_priority' => '#ffffff',
            'label_priority' => 'priorityCreated',
            'delays' => "2",
            'working_days' =>'Y'
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs,$request);
        $response = $priorities->create($fullRequest, new \Slim\Http\Response(), $aArgs);        
        $tabPriorities = $table->select([
            'select'    => empty($aArgs['select']) ?  ['*'] : $aArgs['select'],
            'table'     => ['priorities'],
            'orderby'      => 'id'
        ]);
        end($tabPriorities);
        $key =key($tabPriorities);
        $id = $tabPriorities[$key]['id'];
        $compare = '[{"id":'.$id.',"label_priority":"priorityCreated","color_priority":"#ffffff","working_days":"Y","delays":"2"}]';

        $this->assertSame($compare,(string)$response->getBody());

        $aArgs = [
            'color_priority' => '',
            'label_priority' => '',
            'delays' => '',
            'working_days' =>''
        ];
        $compare = '{"errors":["Valeur label vide","Aucune Couleur assign\u00e9e","Delai vide","jours vide"]}';

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs,$request);
        $response = $priorities->create($fullRequest, new \Slim\Http\Response(), $aArgs);
        $this->assertSame($compare,(string)$response->getBody());
        
        // Delays non numérique
        
        $aArgs = [
            'color_priority' => '#ffff',
            'label_priority' => 'ErrorColor',
            'delays' => 'abc',
            'working_days' =>'A'
        ];

        $compare = '{"errors":["Format couleur invalide","Valeur working_days invalide","Valeur delays non num\u00e9rique"]}';

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs,$request);
        $response = $priorities->create($fullRequest, new \Slim\Http\Response(), $aArgs);
        $this->assertSame($compare,(string)$response->getBody());

        $aArgs = [
            'color_priority' => '#ffff',
            'label_priority' => 'ErrorColor',
            'delays' => '-1',
            'working_days' =>'A'
        ];

        $compare = '{"errors":["Format couleur invalide","Valeur working_days invalide","Valeur delays non num\u00e9rique","Valeur n\u00e9gative"]}';

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs,$request);
        $response = $priorities->create($fullRequest, new \Slim\Http\Response(), $aArgs);
        $this->assertSame($compare,(string)$response->getBody());        
    }

    public function testGetList(){
        $priorities = new \Core\Controllers\PrioritiesController();
        
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD'    =>  'GET'
        ]);

        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $response = $priorities->getList($request, new \Slim\Http\Response());
        $this->assertNotNull((string)$response->getBody());
    }

    public function testGetById(){
        $priorities = new \Core\Controllers\PrioritiesController();

        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD'    =>  'GET'
        ]);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'id'    =>'1'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        
        $response = $priorities->getById($fullRequest, new \Slim\Http\Response(), $aArgs);
        $this->assertNotNull((string)$response->getBody());
        $compare = '[{"id":1,"label_priority":"priorityCreated","color_priority":"#ffffff","working_days":"Y","delays":"12"}]';
        $this->assertSame($compare,(string)$response->getBody());


        $aArgs = [
            'id'    => 187
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        
        $response = $priorities->getById($fullRequest, new \Slim\Http\Response(), $aArgs);
        $this->assertNotNull((string)$response->getBody());
        $compare = '{"errors":"Aucune priorit\u00e9 trouv\u00e9e"}';
        $this->assertSame($compare,(string)$response->getBody());
        /*$query = 'id=0';

        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD'    =>  'GET',
            'QUERY_STRING'      =>$query
        ]);
        $response = $priorities->getById(\Slim\Http\Request::createFromEnvironment($environment), new \Slim\Http\Response(), ['id' => 0]);
        $this->assertNotNull((string)$response->getBody());
        $compare = '[{"id":1,"label_priority":"labelTest","color_priority":"#ffffff","working_days":"Y","delays":"12"}]';
        $this->assertSame($compare,(string)$response->getBody());
        */
    }

    public function testUpdate()
    {
        //TEST TOUT OK
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'PUT'
        ]);        
        $priorities = new \Core\Controllers\PrioritiesController();
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $table = new \Apps_Table_Service();

        $tabPriorities = $table->select([
            'select'    => empty($aArgs['select']) ?  ['*'] : $aArgs['select'],
            'table'     => ['priorities'],
            'orderby'      => 'id'
        ]);
        end($tabPriorities);
        $key =key($tabPriorities);
        $id = $tabPriorities[$key]['id'];
        
        //TEST MAUVAIS FORMAT DE COULEUR

        $aArgs = [
            'id' => $id,
            'color_priority' => '#ffffff', //Mauvais format de couleurs
            'label_priority' => 'labelTestUpdated',
            'delays' => "15",
            'working_days' =>'Y'
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs,$request);
        $response = $priorities->update($fullRequest, new \Slim\Http\Response(), $aArgs);        
        $compare = '[{"id":'.$id.',"label_priority":"labelTestUpdated","color_priority":"#ffffff","working_days":"Y","delays":"15"}]';

        $this->assertSame($compare,(string)$response->getBody());

        $aArgs = [
            'id' => $id,
            'color_priority' => '#ffff',
            'label_priority' => 'labelTestUpdated',
            'delays' => "15",
            'working_days' =>'Y'
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs,$request);
        $response = $priorities->update($fullRequest, new \Slim\Http\Response(), $aArgs);        
        $compare = '{"errors":["Format couleur invalide"]}';

        $this->assertSame($compare,(string)$response->getBody());
        
        //TEST ID Non existant
        $aArgs = [
            'id' => '187',
            'color_priority' => '#ffffff', //Mauvais format de couleurs
            'label_priority' => 'labelTestUpdated',
            'delays' => "15",
            'working_days' =>'Y'
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs,$request);
        $response = $priorities->update($fullRequest, new \Slim\Http\Response(), $aArgs);        
        $compare = '{"errors":["Cette priorit\u00e9 n\'existe pas"]}';

        $this->assertSame($compare,(string)$response->getBody());        
    }

    public function testDelete(){
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
        $compare = 'true';
        $this->assertSame($compare,(string)$response->getBody());
        
    }
}
?>
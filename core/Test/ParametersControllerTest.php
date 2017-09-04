<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

namespace MaarchTest;
use PHPUnit\Framework\TestCase;

class ParametersControllerTest extends TestCase
{
    public function testCreate()
    {
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $parameter   = new \Core\Controllers\ParametersController();

        $aArgs = [
            'id'                 => 'TEST',
            'param_value_string' => 'abcd',
            'description'        => 'papa'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response  = $parameter->create($fullRequest, new \Slim\Http\Response());

        $compare = '[{"id":"TEST",'
                    .'"description":"papa",'
                    .'"param_value_string":"abcd",'
                    .'"param_value_int":null,'
                    .'"param_value_date":null}]';

        $this->assertSame($compare, (string)$response->getBody());
       
        //TEST EXISTE DEJA
        $response = $parameter->create($fullRequest, new \Slim\Http\Response());

        $compare = '{"errors":["Identifiant TEST existe d\u00e9j\u00e0 !"]}';

        $this->assertSame($compare, (string)$response->getBody());

        //TEST ENTIER NON VALIDE
        $aArgs = [
            'id'              => 'TEST23',
            'param_value_int' => 'abcd',
            'description'     => 'papa'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response  = $parameter->create($fullRequest, new \Slim\Http\Response());

        $compare = '{"errors":["Entier non valide"]}';

        $this->assertSame($compare, (string)$response->getBody());

        //AUCUN PARAMETRE
        $aArgs = [
            'id'                 => 'TEST23',
            'param_value_string' => '',
            'param_value_int'    => '',
            'param_value_date'   => '',
            'description'        => ''
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response = $parameter->create($fullRequest, new \Slim\Http\Response());

        $compare = '{"errors":[" La valeur du param\u00e8tre est vide"]}';
        
        $this->assertSame($compare, (string)$response->getBody());

        //AUCUN ARGUMENT
        $aArgs = [];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response = $parameter->create($fullRequest, new \Slim\Http\Response());

        $compare = '{"errors":[" L\'identifiant est vide"," La valeur du param\u00e8tre est vide"]}';

        $this->assertSame($compare, (string)$response->getBody());

        //DATE MAUVAIS FORMAT
        $aArgs = [
            'id'                 => 'TEST4',
            'param_value_string' => '',
            'param_value_int'    => '',
            'param_value_date'   => '123456',
            'description'        => ''
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response = $parameter->create($fullRequest, new \Slim\Http\Response());

        $compare = '{"errors":[" Param\u00e8tre date invalide"]}';
        $this->assertSame($compare, (string)$response->getBody());

        //TEST ID MAUVAIS FORMAT (REGEX)
        $aArgs = [
            'id'                 => 'A*-#==',
            'param_value_string' => '//-//**',
            'param_value_int'    => '',
            'param_value_date'   => '',
            'description'        => '*///*//'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response = $parameter->create($fullRequest, new \Slim\Http\Response());

        $compare = '{"errors":["Identifiant invalide","Description invalide","Chaine de caract\u00e8re invalide"]}';
        $this->assertSame($compare, (string)$response->getBody());
    }

    public function testGetList()
    {
        $parameter = new \Core\Controllers\ParametersController();

        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request = \Slim\Http\Request::createFromEnvironment($environment);

        $response = $parameter->getList($request, new \Slim\Http\Response());
        $this->assertNotNull((string)$response->getBody());
    }

    public function testGetLang()
    {
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $parameter   = new \Core\Controllers\ParametersController();

        $response = $parameter->getLang($request, new \Slim\Http\Response());
        $compare = '{"parameter":"Param\u00e8tre",';
        $compare .='"identifier":"Identifiant",';
        $compare .='"description":"Description",';
        $compare .='"value":"Valeur",';
        $compare .='"type":"Type",';
        $compare .='"string":"Chaine de caract\u00e8res",';
        $compare .='"integer":"Entier",';
        $compare .='"date":"Date",';
        $compare .='"validate":"Valider",';
        $compare .='"cancel":"Annuler",';
        $compare .='"modifyParameter":"Modifier param\u00e8tre",';
        $compare .='"deleteParameter":"Supprimer param\u00e8tre",';
        $compare .='"page":"Page",';
        $compare .='"outOf":"sur",';
        $compare .='"search":"Rechercher",';
        $compare .='"recordsPerPage":"r\u00e9sultats par page",';
        $compare .='"display":"Affichage",';
        $compare .='"noRecords":"Aucun r\u00e9sultat",';
        $compare .= '"available":"disponible",';
        $compare .='"filteredFrom":"filtr\u00e9 sur un ensemble de ",';
        $compare .='"records":"r\u00e9sultats",';
        $compare .='"first":"premier",';
        $compare .='"last":"dernier",';
        $compare .='"next":"Suivante",';
        $compare .='"previous":"Pr\u00e9c\u00e9dente",';
        $compare .='"paramCreatedSuccess":"Param\u00e8tre cr\u00e9\u00e9 avec succ\u00e8s",';
        $compare .='"paramUpdatedSuccess":"Mise \u00e0 jour effectu\u00e9e",';
        $compare .='"deleteConfirm":"Voulez-vous vraiment supprimer le param\u00e8tre",';
        $compare .='"controlTechnicalParams":"Contr\u00f4ler les param\u00e8tres techniques"}';

        $this->assertSame($compare, (string)$response->getBody());
    }
    
    public function testGetById()
    {
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $parameter   = new \Core\Controllers\ParametersController();

        $response = $parameter->getById($request, new \Slim\Http\Response(), ['id' => 'TEST']);

        $compare = '[{"id":"TEST",'
                    .'"description":"papa",'
                    .'"param_value_string":"abcd",'
                    .'"param_value_int":null,'
                    .'"param_value_date":null}]';

        $this->assertNotNull((string)$response->getBody());
        $this->assertSame($compare, (string)$response->getBody());
    }

    public function testUpdate()
    {
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $parameter   = new \Core\Controllers\ParametersController();

        $aArgs = [
            'id'                 => 'TEST',
            'param_value_string' => 'abcd',
            'description'        => 'TEST AFTER UP'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response = $parameter->update($fullRequest, new \Slim\Http\Response(), ['id' => 'TEST']);

        $compare = '[{"id":"TEST",'
                    .'"description":"TEST AFTER UP",'
                    .'"param_value_string":"abcd",'
                    .'"param_value_int":null,'
                    .'"param_value_date":null}]';
        
        $this->assertSame($compare, (string)$response->getBody());

        //TEST ID INVALIDE
        $aArgs = [
            'id'                 => 'NEWWW',
            'param_value_string' => 'abcd'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response = $parameter->update($fullRequest, new \Slim\Http\Response(), ['id' => 'NEWWW']);

        $compare = '{"errors":["Identifiant n\'existe pas"]}';
        
        $this->assertSame($compare, (string)$response->getBody());
    }

    public function testDelete()
    {
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $parameter   = new \Core\Controllers\ParametersController();

        $response = $parameter->delete($request, new \Slim\Http\Response(), ['id' => 'TEST']);

        $compare = 'true';
        $this->assertSame($compare, (string)$response->getBody());
    }
}

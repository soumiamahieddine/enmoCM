<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   FirstLevelControllerTest
* @author  dev <dev@maarch.org>
* @ingroup core
*/

use PHPUnit\Framework\TestCase;
use SrcCore\models\DatabaseModel;

class FirstLevelControllerTest extends TestCase
{
    private static $firstLevelId  = null;
    private static $secondLevelId = null;
    private static $doctypeId     = null;

    public function testReadList(){
        //  READ LIST
        $environment  = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request      = \Slim\Http\Request::createFromEnvironment($environment);

        $firstLevelController = new \Doctype\controllers\FirstLevelController();
        $response          = $firstLevelController->getTree($request, new \Slim\Http\Response());
        $responseBody      = json_decode((string)$response->getBody());

        $this->assertNotNull($responseBody->structure); 
        $this->assertNotNull($responseBody->structure[0]->doctypes_first_level_id);
        $this->assertInternalType('int', $responseBody->structure[0]->doctypes_first_level_id); 
        $this->assertNotNull($responseBody->structure[0]->doctypes_first_level_label); 
        $this->assertNotNull($responseBody->structure[0]->enabled); 
    }

    public function testinitFirstLevel(){
        $environment  = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request      = \Slim\Http\Request::createFromEnvironment($environment);

        $firstLevelController = new \Doctype\controllers\FirstLevelController();
        $response          = $firstLevelController->initFirstLevel($request, new \Slim\Http\Response());
        $responseBody      = json_decode((string)$response->getBody());

        $this->assertNotNull($responseBody->folderType);          
        $this->assertNotNull($responseBody->folderType[0]->foldertype_id);          
        $this->assertNotNull($responseBody->folderType[0]->foldertype_label);          
    }

    public function testinitSecondLevel(){
        $environment  = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request      = \Slim\Http\Request::createFromEnvironment($environment);

        $secondLevelController = new \Doctype\controllers\SecondLevelController();
        $response          = $secondLevelController->initSecondLevel($request, new \Slim\Http\Response());
        $responseBody      = json_decode((string)$response->getBody());

        $this->assertNotNull($responseBody->firstLevel);          
        $this->assertNotNull($responseBody->firstLevel[0]->doctypes_first_level_id);          
        $this->assertNotNull($responseBody->firstLevel[0]->doctypes_first_level_label);          
    }

    public function testCreateFirstLevel()
    {
        $firstLevelController = new \Doctype\controllers\FirstLevelController();

        //  CREATE
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'doctypes_first_level_label' => 'testTUfirstlevel',
            'foldertype_id'              => [1],
            'css_style'                    => '#99999',
            'enabled'                    => 'Y',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $firstLevelController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        self::$firstLevelId = $responseBody->firstLevel->doctypes_first_level_id;

        $this->assertInternalType('int', self::$firstLevelId);
        $this->assertSame('testTUfirstlevel', $responseBody->firstLevel->doctypes_first_level_label);
        $this->assertSame('#99999', $responseBody->firstLevel->css_style);
        $this->assertSame('Y', $responseBody->firstLevel->enabled);  

        // CREATE FAIL
        $aArgs = [
            'doctypes_first_level_label' => '',
            'foldertype_id'              => [],
            'css_style'                  => '#7777',
            'enabled'                    => 'gaz',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $firstLevelController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Invalid doctypes_first_level_label', $responseBody->errors[0]);
        $this->assertSame('Invalid foldertype_id', $responseBody->errors[1]);
    }

    public function testCreateSecondLevel()
    {
        $secondLevelController = new \Doctype\controllers\SecondLevelController();

        //  CREATE
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'doctypes_second_level_label' => 'testTUsecondlevel',
            'doctypes_first_level_id'     => self::$firstLevelId,
            'css_style'                   => '#99999',
            'enabled'                     => 'Y',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $secondLevelController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        self::$secondLevelId = $responseBody->secondLevel->doctypes_second_level_id;

        $this->assertInternalType('int', self::$secondLevelId);
        $this->assertSame('testTUsecondlevel', $responseBody->secondLevel->doctypes_second_level_label);
        $this->assertSame(self::$firstLevelId, $responseBody->secondLevel->doctypes_first_level_id);
        $this->assertSame('#99999', $responseBody->secondLevel->css_style);
        $this->assertSame('Y', $responseBody->secondLevel->enabled);  

        // CREATE FAIL
        $aArgs = [
            'doctypes_second_level_label' => '',
            'doctypes_first_level_id'     => '',
            'css_style'                  => '#7777',
            'enabled'                    => 'gaz',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $secondLevelController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Invalid doctypes_second_level_label', $responseBody->errors[0]);
        $this->assertSame('Invalid doctypes_first_level_id', $responseBody->errors[1]);
    }

    public function testUpdateFirstLevel()
    {
        $firstLevelController = new \Doctype\controllers\FirstLevelController();

        //  UPDATE
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'doctypes_first_level_label' => 'testTUfirstlevelUPDATE',
            'foldertype_id'              => [1],
            'css_style'                  => '#7777',
            'enabled'                    => 'Y',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $firstLevelController->update($fullRequest, new \Slim\Http\Response(), ["id" => self::$firstLevelId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$firstLevelId, $responseBody->firstLevel->doctypes_first_level_id);
        $this->assertSame('testTUfirstlevelUPDATE', $responseBody->firstLevel->doctypes_first_level_label);
        $this->assertSame('#7777', $responseBody->firstLevel->css_style);
        $this->assertSame('Y', $responseBody->firstLevel->enabled);  

        // UPDATE FAIL
        $aArgs = [
            'doctypes_first_level_label' => '',
            'foldertype_id'              => [],
            'css_style'                  => '#7777',
            'enabled'                    => 'gaz',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $firstLevelController->update($fullRequest, new \Slim\Http\Response(), ["id" => 'gaz']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Id is not a numeric', $responseBody->errors[0]);
        $this->assertSame('Id gaz does not exists', $responseBody->errors[1]);
        $this->assertSame('Invalid doctypes_first_level_label', $responseBody->errors[2]);
        $this->assertSame('Invalid foldertype_id', $responseBody->errors[3]);

    }

    public function testUpdateSecondLevel()
    {
        $secondLevelController = new \Doctype\controllers\SecondLevelController();

        //  UPDATE
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'doctypes_second_level_label' => 'testTUsecondlevelUPDATE',
            'doctypes_first_level_id'     => self::$firstLevelId,
            'css_style'                   => '#7777',
            'enabled'                     => 'Y',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $secondLevelController->update($fullRequest, new \Slim\Http\Response(), ["id" => self::$secondLevelId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$secondLevelId, $responseBody->secondLevel->doctypes_second_level_id);
        $this->assertSame('testTUsecondlevelUPDATE', $responseBody->secondLevel->doctypes_second_level_label);
        $this->assertSame(self::$firstLevelId, $responseBody->secondLevel->doctypes_first_level_id);
        $this->assertSame('#7777', $responseBody->secondLevel->css_style);
        $this->assertSame('Y', $responseBody->secondLevel->enabled);  

        // UPDATE FAIL
        $aArgs = [
            'doctypes_second_level_label' => '',
            'doctypes_first_level_id'     => '',
            'css_style'                  => '#7777',
            'enabled'                    => 'gaz',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $secondLevelController->update($fullRequest, new \Slim\Http\Response(), ["id" => 'gaz']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Id is not a numeric', $responseBody->errors[0]);
        $this->assertSame('Id gaz does not exists', $responseBody->errors[1]);
        $this->assertSame('Invalid doctypes_second_level_label', $responseBody->errors[2]);
        $this->assertSame('Invalid doctypes_first_level_id', $responseBody->errors[3]);

    }

    public function testRead(){
        //  READ FIRST LEVEL
        $environment  = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request      = \Slim\Http\Request::createFromEnvironment($environment);

        $firstLevelController = new \Doctype\controllers\FirstLevelController();
        $response          = $firstLevelController->getById($request, new \Slim\Http\Response(), ["id" => self::$firstLevelId]);
        $responseBody      = json_decode((string)$response->getBody());  
 
        $this->assertSame(self::$firstLevelId, $responseBody->firstLevel->doctypes_first_level_id);
        $this->assertSame('testTUfirstlevelUPDATE', $responseBody->firstLevel->doctypes_first_level_label);
        $this->assertSame('#7777', $responseBody->firstLevel->css_style);
        $this->assertSame(true, $responseBody->firstLevel->enabled);  
        $this->assertNotNull($responseBody->folderTypeSelected); 
        $this->assertNotNull($responseBody->folderTypes); 

        // READ FIRST LEVEL FAIL
        $response          = $firstLevelController->getById($request, new \Slim\Http\Response(), ["id" => 'GAZ']);
        $responseBody      = json_decode((string)$response->getBody());  
 
        $this->assertSame('wrong format for id', $responseBody->errors);

        //  READ SECOND LEVEL
        $secondLevelController = new \Doctype\controllers\SecondLevelController();
        $response     = $secondLevelController->getById($request, new \Slim\Http\Response(), ["id" => self::$secondLevelId]);
        $responseBody = json_decode((string)$response->getBody());  

        $this->assertSame(self::$secondLevelId, $responseBody->secondLevel->doctypes_second_level_id);
        $this->assertSame('testTUsecondlevelUPDATE', $responseBody->secondLevel->doctypes_second_level_label);
        $this->assertSame(self::$firstLevelId, $responseBody->secondLevel->doctypes_first_level_id);
        $this->assertSame(true, $responseBody->secondLevel->enabled); 

        // READ SECOND LEVEL FAIL
        $response          = $secondLevelController->getById($request, new \Slim\Http\Response(), ["id" => 'GAZ']);
        $responseBody      = json_decode((string)$response->getBody());  
 
        $this->assertSame('wrong format for id', $responseBody->errors);
    }

    public function testDeleteSecondLevel()
    {
        $secondLevelController = new \Doctype\controllers\SecondLevelController();

        //  DELETE
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $secondLevelController->delete($fullRequest, new \Slim\Http\Response(), ["id" => self::$secondLevelId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$secondLevelId, $responseBody->secondLevel->doctypes_second_level_id);
        $this->assertSame(self::$firstLevelId, $responseBody->secondLevel->doctypes_first_level_id);
        $this->assertSame('testTUsecondlevelUPDATE', $responseBody->secondLevel->doctypes_second_level_label);
        $this->assertSame('#7777', $responseBody->secondLevel->css_style);
        $this->assertSame('N', $responseBody->secondLevel->enabled);

        //  DELETE FAIL
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $secondLevelController->delete($fullRequest, new \Slim\Http\Response(), ["id" => 'gaz']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Id is not a numeric', $responseBody->errors); 

    }

    public function testDeleteFirstLevel()
    {
        $firstLevelController = new \Doctype\controllers\FirstLevelController();

        //  DELETE
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $firstLevelController->delete($fullRequest, new \Slim\Http\Response(), ["id" => self::$firstLevelId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$firstLevelId, $responseBody->firstLevel->doctypes_first_level_id);
        $this->assertSame('testTUfirstlevelUPDATE', $responseBody->firstLevel->doctypes_first_level_label);
        $this->assertSame('#7777', $responseBody->firstLevel->css_style);
        $this->assertSame('N', $responseBody->firstLevel->enabled);

        //  DELETE FAIL
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $firstLevelController->delete($fullRequest, new \Slim\Http\Response(), ["id" => 'gaz']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Id is not a numeric', $responseBody->errors); 

    }

    public function testDeleteSQL()
    {
        DatabaseModel::delete([
            'table' => 'doctypes_first_level',
            'where' => ['doctypes_first_level_id = ?'],
            'data'  => [self::$firstLevelId]
        ]);
        DatabaseModel::delete([
            'table' => 'doctypes_second_level',
            'where' => ['doctypes_second_level_id = ?'],
            'data'  => [self::$secondLevelId]
        ]);
    }

}

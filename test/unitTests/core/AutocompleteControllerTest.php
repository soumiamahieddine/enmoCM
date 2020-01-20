<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   ActionsControllerTest
* @author  dev <dev@maarch.org>
* @ingroup core
*/

use PHPUnit\Framework\TestCase;

class AutocompleteControllerTest extends TestCase
{
    public function testGetContactsForGroups()
    {
        $autocompleteController = new \SrcCore\controllers\AutoCompleteController();

        //  CREATE
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'search'    => 'maarch',
            'type'      => 'all'
        ];
        $fullRequest = $request->withQueryParams($aArgs);

        $response     = $autocompleteController->getContactsForGroups($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody);
        $this->assertNotEmpty($responseBody);

        foreach ($responseBody as $value) {
            $this->assertInternalType('int', $value->id);
            $this->assertInternalType('string', $value->contact);
            $this->assertInternalType('string', $value->address);
        }
    }

    public function testGetCorrespondents()
    {
        $autocompleteController = new \SrcCore\controllers\AutoCompleteController();

        //  GET
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'search'    => 'maarch',
            'color'      => true
        ];
        $fullRequest = $request->withQueryParams($aArgs);

        $response     = $autocompleteController->getCorrespondents($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        foreach ($responseBody as $value) {
            $this->assertInternalType('int', $value->id);
            // $this->assertInternalType('string', $value->idToDisplay);
            // $this->assertInternalType('string', $value->otherInfo);
            $this->assertNotEmpty($value->type);
            $this->assertNotEmpty($value->id);
            // $this->assertNotEmpty($value->idToDisplay);
            // $this->assertNotEmpty($value->otherInfo);
            if ($value->type == 'contact') {
                $this->assertNotEmpty($value->fillingRate->rate);
                $this->assertNotEmpty($value->fillingRate->thresholdLevel);
            }
        }
    }

    public function testGetUsers()
    {
        $autocompleteController = new \SrcCore\controllers\AutoCompleteController();

        //  CREATE
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'search'    => 'bain'
        ];
        $fullRequest = $request->withQueryParams($aArgs);

        $response     = $autocompleteController->getUsers($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody);
        $this->assertNotEmpty($responseBody);

        foreach ($responseBody as $value) {
            $this->assertSame('user', $value->type);
            $this->assertInternalType('string', $value->id);
            $this->assertNotEmpty($value->id);
            $this->assertInternalType('string', $value->idToDisplay);
            $this->assertNotEmpty($value->idToDisplay);
            $this->assertInternalType('string', $value->otherInfo);
        }
    }

    public function testGetUsersForAdministration()
    {
        $autocompleteController = new \SrcCore\controllers\AutoCompleteController();

        //  CREATE
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'search'    => 'bern',
        ];
        $fullRequest = $request->withQueryParams($aArgs);

        $response     = $autocompleteController->getUsersForAdministration($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody);
        $this->assertNotEmpty($responseBody);

        foreach ($responseBody as $value) {
            $this->assertSame('user', $value->type);
            $this->assertInternalType('int', $value->id);
            $this->assertNotEmpty($value->id);
            $this->assertInternalType('string', $value->idToDisplay);
            $this->assertNotEmpty($value->idToDisplay);
        }
    }

    public function testGetUsersForCircuit()
    {
        $autocompleteController = new \SrcCore\controllers\AutoCompleteController();

        //  CREATE
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'search'    => 'dau',
        ];
        $fullRequest = $request->withQueryParams($aArgs);

        $response     = $autocompleteController->getUsersForCircuit($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody);
        $this->assertNotEmpty($responseBody);

        foreach ($responseBody as $value) {
            $this->assertSame('user', $value->type);
            $this->assertInternalType('integer', $value->id);
            $this->assertNotEmpty($value->id);
            $this->assertInternalType('string', $value->idToDisplay);
            $this->assertNotEmpty($value->idToDisplay);
            $this->assertInternalType('string', $value->otherInfo);
        }
    }

    public function testGetEntities()
    {
        $autocompleteController = new \SrcCore\controllers\AutoCompleteController();

        //  CREATE
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'search'    => 'mai',
        ];
        $fullRequest = $request->withQueryParams($aArgs);

        $response     = $autocompleteController->getEntities($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody);
        $this->assertNotEmpty($responseBody);

        foreach ($responseBody as $value) {
            $this->assertSame('entity', $value->type);
            $this->assertInternalType('string', $value->id);
            $this->assertNotEmpty($value->id);
            $this->assertInternalType('string', $value->idToDisplay);
            $this->assertNotEmpty($value->idToDisplay);
            $this->assertInternalType('string', $value->otherInfo);
        }
    }

    public function testGetStatuses()
    {
        $autocompleteController = new \SrcCore\controllers\AutoCompleteController();

        //  CREATE
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $autocompleteController->getStatuses($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody);
        $this->assertNotEmpty($responseBody);

        foreach ($responseBody as $value) {
            $this->assertSame('status', $value->type);
            $this->assertInternalType('string', $value->id);
            $this->assertNotEmpty($value->id);
            $this->assertInternalType('string', $value->idToDisplay);
            $this->assertNotEmpty($value->idToDisplay);
            $this->assertInternalType('string', $value->otherInfo);
        }
    }

    public function testGetBanAddresses()
    {
        $autocompleteController = new \SrcCore\controllers\AutoCompleteController();

        //  CREATE
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'department'    => '75',
            'address'       => 'italie'
        ];
        $fullRequest = $request->withQueryParams($aArgs);

        $response     = $autocompleteController->getBanAddresses($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody);
        $this->assertNotEmpty($responseBody);

        foreach ($responseBody as $value) {
            $this->assertInternalType('string', $value->banId);
            $this->assertNotEmpty($value->banId);
            $this->assertInternalType('string', $value->number);
            $this->assertNotEmpty($value->number);
            $this->assertInternalType('string', $value->afnorName);
            $this->assertNotEmpty($value->afnorName);
            $this->assertInternalType('string', $value->postalCode);
            $this->assertNotEmpty($value->postalCode);
            $this->assertInternalType('string', $value->city);
            $this->assertNotEmpty($value->city);
            $this->assertInternalType('string', $value->address);
            $this->assertNotEmpty($value->address);
        }

        // Errors
        $aArgs = [
            'department'    => '100',
            'address'       => 'italie'
        ];
        $fullRequest = $request->withQueryParams($aArgs);

        $response     = $autocompleteController->getBanAddresses($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Department indexes do not exist', $responseBody->errors);

        $response     = $autocompleteController->getBanAddresses($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Bad Request', $responseBody->errors);
    }

    public function testGetTags()
    {
        $autocompleteController = new \SrcCore\controllers\AutoCompleteController();

        //  CREATE
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'search'    => 'maa'
        ];
        $fullRequest = $request->withQueryParams($aArgs);

        $response     = $autocompleteController->getTags($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody);
        $this->assertNotEmpty($responseBody);

        foreach ($responseBody as $value) {
            $this->assertInternalType('int', $value->id);
            $this->assertNotEmpty($value->id);
            $this->assertInternalType('string', $value->idToDisplay);
            $this->assertNotEmpty($value->idToDisplay);
        }
    }
}

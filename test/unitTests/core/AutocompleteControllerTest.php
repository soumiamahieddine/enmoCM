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
    public function testGetContacts()
    {
        $autocompleteController = new \SrcCore\controllers\AutoCompleteController();

        //  GET COLOR
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'search'    => 'maarch',
            'color'      => true
        ];
        $fullRequest = $request->withQueryParams($aArgs);

        $response     = $autocompleteController->getContacts($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody);
        $this->assertNotEmpty($responseBody);

        foreach ($responseBody as $value) {
            $this->assertSame('contact', $value->type);
            $this->assertInternalType('int', $value->id);
            $this->assertInternalType('string', $value->contact);
            $this->assertInternalType('string', $value->address);
            $this->assertInternalType('string', $value->idToDisplay);
            $this->assertInternalType('string', $value->otherInfo);
            $this->assertSame('#', substr($value->rateColor, 0, 1));
            $this->assertInternalType('string', substr($value->rateColor, 0));
            $this->assertNotEmpty($value->id);
            $this->assertNotEmpty($value->contact);
            $this->assertNotEmpty($value->idToDisplay);
            $this->assertNotEmpty($value->otherInfo);
            $this->assertNotEmpty(substr($value->rateColor, 1));
        }

        //  GET NO COLOR
        $aArgs = [
            'search'    => 'maarch',
            'color'      => false
        ];
        $fullRequest = $request->withQueryParams($aArgs);

        $response     = $autocompleteController->getContacts($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody);
        $this->assertNotEmpty($responseBody);

        foreach ($responseBody as $value) {
            $this->assertSame('contact', $value->type);
            $this->assertInternalType('int', $value->id);
            $this->assertInternalType('string', $value->contact);
            $this->assertInternalType('string', $value->address);
            $this->assertInternalType('string', $value->idToDisplay);
            $this->assertInternalType('string', $value->otherInfo);
            $this->assertNotEmpty($value->id);
            $this->assertNotEmpty($value->contact);
            $this->assertNotEmpty($value->idToDisplay);
            $this->assertNotEmpty($value->otherInfo);
            $this->assertEmpty($value->rateColor);
        }
    }

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

        $this->assertInternalType('int', $responseBody[0]->position);
        $this->assertInternalType('int', $responseBody[0]->addressId);
        $this->assertInternalType('string', $responseBody[0]->contact);
        $this->assertInternalType('string', $responseBody[0]->address);
    }

    public function testGetContactsAndUsers()
    {
        $autocompleteController = new \SrcCore\controllers\AutoCompleteController();

        //  GET
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'search'    => 'maarch',
            'color'      => true,
            'onlyContacts' => 'true'
        ];
        $fullRequest = $request->withQueryParams($aArgs);

        $response     = $autocompleteController->getContactsAndUsers($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        foreach ($responseBody as $value) {
            $this->assertInternalType('int', $value->id);
            $this->assertInternalType('string', $value->idToDisplay);
            $this->assertInternalType('string', $value->otherInfo);
            $this->assertNotEmpty($value->type);
            $this->assertNotEmpty($value->id);
            $this->assertNotEmpty($value->idToDisplay);
            $this->assertNotEmpty($value->otherInfo);
            if ($value->type == 'contact') {
                $this->assertNotEmpty($value->rateColor);
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

        $this->assertSame('user', $responseBody[0]->type);
        $this->assertInternalType('string', $responseBody[0]->id);
        $this->assertNotEmpty($responseBody[0]->id);
        $this->assertInternalType('string', $responseBody[0]->idToDisplay);
        $this->assertNotEmpty($responseBody[0]->idToDisplay);
        $this->assertInternalType('string', $responseBody[0]->otherInfo);
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

        $this->assertSame('user', $responseBody[0]->type);
        $this->assertInternalType('int', $responseBody[0]->id);
        $this->assertNotEmpty($responseBody[0]->id);
        $this->assertInternalType('string', $responseBody[0]->idToDisplay);
        $this->assertNotEmpty($responseBody[0]->idToDisplay);
    }

    public function testGetUsersForVisa()
    {
        $autocompleteController = new \SrcCore\controllers\AutoCompleteController();

        //  CREATE
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'search'    => 'dau',
        ];
        $fullRequest = $request->withQueryParams($aArgs);

        $response     = $autocompleteController->getUsersForVisa($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody);
        $this->assertNotEmpty($responseBody);

        $this->assertSame('user', $responseBody[0]->type);
        $this->assertInternalType('string', $responseBody[0]->id);
        $this->assertNotEmpty($responseBody[0]->id);
        $this->assertInternalType('string', $responseBody[0]->idToDisplay);
        $this->assertNotEmpty($responseBody[0]->idToDisplay);
        $this->assertInternalType('string', $responseBody[0]->otherInfo);
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

        $this->assertSame('entity', $responseBody[0]->type);
        $this->assertInternalType('string', $responseBody[0]->id);
        $this->assertNotEmpty($responseBody[0]->id);
        $this->assertInternalType('string', $responseBody[0]->idToDisplay);
        $this->assertNotEmpty($responseBody[0]->idToDisplay);
        $this->assertInternalType('string', $responseBody[0]->otherInfo);
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

        $this->assertSame('status', $responseBody[0]->type);
        $this->assertInternalType('string', $responseBody[0]->id);
        $this->assertNotEmpty($responseBody[0]->id);
        $this->assertInternalType('string', $responseBody[0]->idToDisplay);
        $this->assertNotEmpty($responseBody[0]->idToDisplay);
        $this->assertInternalType('string', $responseBody[0]->otherInfo);
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

        $this->assertInternalType('string', $responseBody[0]->banId);
        $this->assertNotEmpty($responseBody[0]->banId);
        $this->assertInternalType('string', $responseBody[0]->number);
        $this->assertNotEmpty($responseBody[0]->number);
        $this->assertInternalType('string', $responseBody[0]->afnorName);
        $this->assertNotEmpty($responseBody[0]->afnorName);
        $this->assertInternalType('string', $responseBody[0]->postalCode);
        $this->assertNotEmpty($responseBody[0]->postalCode);
        $this->assertInternalType('string', $responseBody[0]->city);
        $this->assertNotEmpty($responseBody[0]->city);
        $this->assertInternalType('string', $responseBody[0]->address);
        $this->assertNotEmpty($responseBody[0]->address);

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
}

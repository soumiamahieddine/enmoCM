<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Core Controller
 * @author dev@maarch.org
 * @ingroup core
 */

namespace Core\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;

include_once 'core/class/class_portal.php';

class CoreController
{
    public function initialize(RequestInterface $request, ResponseInterface $response)
    {
        if (empty($_SESSION['user']['UserId'])) {
            return $response->withStatus(401)->withJson(['errors' => 'User Not Connected']);
        }

        $aInit = [];
        $aInit['coreUrl'] = str_replace('rest/', '', \Url::coreurl());
        $aInit['applicationName'] = $_SESSION['config']['applicationname']; //Todo No Session

        $aInit['profileView'] = 'Views/profile.component.html';
        if(file_exists($_SESSION['config']['corepath'].'custom/'.$_SESSION['custom_override_id'].'/apps/maarch_entreprise/Views/profile.component.html')) {
            $aInit['profileView'] = '../../custom/'.$_SESSION['custom_override_id'].'/apps/maarch_entreprise/Views/profile.component.html';
        }
        $aInit['signatureBookView'] = 'Views/signature-book.component.html';
        if(file_exists($_SESSION['config']['corepath'].'custom/'.$_SESSION['custom_override_id'].'/apps/maarch_entreprise/Views/signature-book.component.html')) {
            $aInit['signatureBookView'] = '../../custom/'.$_SESSION['custom_override_id'].'/apps/maarch_entreprise/Views/signature-book.component.html';
        }

        return $response->withJson($aInit);
    }
}

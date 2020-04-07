<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/*
* @requires
*   $res_view   = Name of res view
*   $maarchApps = name of app
*   $maarchUrl  = Url to maarch (root url)
*   $recipient  = recipient of notification
*   $events     = array of events related to letterbox mails
*
* @returns
    [res_letterbox] = record of view + link to detail/doc page
*/

use Basket\models\BasketModel;
use Contact\controllers\ContactController;
use Contact\models\ContactModel;
use Entity\models\EntityModel;
use Resource\models\ResourceContactModel;
use SrcCore\models\DatabaseModel;
use User\models\UserBasketPreferenceModel;
use User\models\UserModel;

$datasources['recipient'][0]  = (array) $recipient;
$datasources['res_letterbox'] = [];
$datasources['contact']       = [];

$urlToApp = trim($maarchUrl, '/').'/apps/'.trim($maarchApps, '/').'/index.php?';

$user   = UserModel::getByLogin(['login' => $datasources['recipient'][0]['user_id'], 'select' => ['id']]);
$basket = BasketModel::getByBasketId(['select' => ['id'], 'basketId' => 'MyBasket']);
$preferenceBasket = UserBasketPreferenceModel::get([
    'select'  => ['group_serial_id'],
    'where'   => ['user_serial_id = ?', 'basket_id = ?'],
    'data'    => [$user['id'], 'MyBasket']
]);

foreach ($events as $event) {
    $table    = [$res_view . ' lb'];
    $leftJoin = [];
    $where    = [];
    $arrayPDO = [];

    switch ($event['table_name']) {
        case 'notes':
            $table[]    = 'notes';
            $leftJoin[] = 'notes.identifier = lb.res_id';
            $where[]    = 'notes.id = ?';
            $arrayPDO[] = $event['record_id'];
            break;

        case 'listinstance':
            $table[]    = 'listinstance li';
            $leftJoin[] = 'lb.res_id = li.res_id';
            $where[]    = 'listinstance_id = ?';
            $arrayPDO[] = $event['record_id'];
            break;

        case 'res_letterbox':
        case 'res_view_letterbox':
        default:
            $where[]    = 'lb.res_id = ?';
            $arrayPDO[] = $event['record_id'];
    }

    // Main document resource from view
    $res = DatabaseModel::select([
        'select'    => ['lb.*'],
        'table'     => $table,
        'left_join' => $leftJoin,
        'where'     => $where,
        'data'      => $arrayPDO,
    ])[0];

    // Lien vers la page detail
    $res['linktodoc']     = $urlToApp . 'linkToDoc='.$res['res_id'];
    $res['linktodetail']  = $urlToApp . 'linkToDetail='.$res['res_id'];
    if (!empty($res['res_id']) && !empty($preferenceBasket[0]['group_serial_id']) && !empty($basket['id']) && !empty($user['id'])) {
        $res['linktoprocess'] = $urlToApp . 'linkToProcess='.$res['res_id'].'&groupId='.$preferenceBasket[0]['group_serial_id'].'&basketId='.$basket['id'].'&userId='.$user['id'];
    }

    if (!empty($res['initiator'])) {
        $entityInfo = EntityModel::getByEntityId(['select' => ['*'], 'entityId' => $res['initiator']]);
        foreach ($entityInfo as $key => $value) {
            $res['initiator_'.$key] = $value;
        }
    }

    $datasources['res_letterbox'][] = $res;

    $resourceContacts = ResourceContactModel::get([
        'where' => ['res_id = ?', "mode='sender'", "type='contact'"],
        'data'  => [$res['res_id']],
        'limit' => 1
    ]);
    $resourceContacts = $resourceContacts[0];

    if (!empty($resourceContacts)) {
        $contact = ContactModel::getById(['id' => $resourceContacts['item_id'], 'select' => ['*']]);

        $postalAddress = ContactController::getContactAfnor($contact);
        unset($postalAddress[0]);
        $contact['postal_address'] = implode("\n", $postalAddress);

        $datasources['contact'][] = $contact;
    }
}

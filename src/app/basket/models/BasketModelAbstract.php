<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief   BasketModelAbstract
* @author  <dev@maarch.org>
*/

namespace Basket\models;

use Core\Models\CoreConfigModel;
use Core\Models\DatabaseModel;
use Core\Models\UserModel;
use Core\Models\ValidatorModel;
use Entities\Models\EntityModel;

require_once 'core/class/SecurityControler.php';

class BasketModelAbstract
{
    public static function get(array $aArgs = [])
    {
        ValidatorModel::arrayType($aArgs, ['select']);

        $aBaskets = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['baskets']
        ]);

        return $aBaskets;
    }

    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aBasket = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['baskets'],
            'where'     => ['basket_id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        return $aBasket[0];
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'name', 'description', 'clause', 'isVisible', 'isFolderBasket', 'flagNotif']);
        ValidatorModel::stringType($aArgs, ['id', 'name', 'color', 'description', 'clause', 'isVisible', 'isFolderBasket', 'flagNotif']);

        DatabaseModel::insert([
            'table'         => 'baskets',
            'columnsValues' => [
                'basket_id'         => $aArgs['id'],
                'basket_name'       => $aArgs['name'],
                'basket_desc'       => $aArgs['description'],
                'basket_clause'     => $aArgs['clause'],
                'is_visible'        => $aArgs['isVisible'],
                'is_folder_basket'  => $aArgs['isFolderBasket'],
                'flag_notif'        => $aArgs['flagNotif'],
                'color'             => $aArgs['color'],
                'coll_id'           => 'letterbox_coll',
            ]
        ]);

        return true;
    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'name', 'description', 'clause', 'isVisible', 'isFolderBasket', 'flagNotif']);
        ValidatorModel::stringType($aArgs, ['id', 'name', 'color', 'description', 'clause', 'isVisible', 'isFolderBasket', 'flagNotif']);

        DatabaseModel::update([
            'table'     => 'baskets',
            'set'       => [
                'basket_name'       => $aArgs['name'],
                'basket_desc'       => $aArgs['description'],
                'basket_clause'     => $aArgs['clause'],
                'is_visible'        => $aArgs['isVisible'],
                'is_folder_basket'  => $aArgs['isFolderBasket'],
                'flag_notif'        => $aArgs['flagNotif'],
                'color'             => $aArgs['color'],
                'coll_id'           => 'letterbox_coll',
            ],
            'where'     => ['basket_id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        return true;
    }

    public static function delete(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['id']);

        DatabaseModel::delete([
            'table' => 'baskets',
            'where' => ['basket_id = ?'],
            'data'  => [$aArgs['id']]
        ]);
        DatabaseModel::delete([
            'table' => 'groupbasket',
            'where' => ['basket_id = ?'],
            'data'  => [$aArgs['id']]
        ]);
        DatabaseModel::delete([
            'table' => 'groupbasket_redirect',
            'where' => ['basket_id = ?'],
            'data'  => [$aArgs['id']]
        ]);
        DatabaseModel::delete([
            'table' => 'groupbasket_status',
            'where' => ['basket_id = ?'],
            'data'  => [$aArgs['id']]
        ]);
        DatabaseModel::delete([
            'table' => 'actions_groupbaskets',
            'where' => ['basket_id = ?'],
            'data'  => [$aArgs['id']]
        ]);
        DatabaseModel::delete([
            'table' => 'user_baskets_secondary',
            'where' => ['basket_id = ?'],
            'data'  => [$aArgs['id']]
        ]);

        return true;
    }

    public static function getGroups(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aGroups = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['groupbasket'],
            'where'     => ['basket_id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        return $aGroups;
    }

    public static function createGroup(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'groupId', 'resultPage']);
        ValidatorModel::stringType($aArgs, ['id', 'groupId', 'resultPage']);

        DatabaseModel::insert([
            'table'         => 'groupbasket',
            'columnsValues' => [
                'basket_id'         => $aArgs['id'],
                'group_id'          => $aArgs['groupId'],
                'result_page'       => $aArgs['resultPage']
            ]
        ]);

        return true;
    }

    public static function createGroupAction(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'groupId', 'actionId', 'usedInBasketlist', 'usedInActionPage', 'defaultActionList']);
        ValidatorModel::stringType($aArgs, ['id', 'groupId', 'actionId', 'whereClause', 'usedInBasketlist', 'usedInActionPage', 'defaultActionList']);

        DatabaseModel::insert([
            'table'         => 'actions_groupbaskets',
            'columnsValues' => [
                'id_action'             => $aArgs['actionId'],
                'where_clause'          => $aArgs['whereClause'],
                'group_id'              => $aArgs['groupId'],
                'basket_id'             => $aArgs['id'],
                'used_in_basketlist'    => $aArgs['usedInBasketlist'],
                'used_in_action_page'   => $aArgs['usedInActionPage'],
                'default_action_list'   => $aArgs['defaultActionList'],
            ]
        ]);

        return true;
    }

    public static function createGroupActionRedirect(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'groupId', 'actionId', 'redirectMode']);
        ValidatorModel::stringType($aArgs, ['id', 'groupId', 'actionId', 'entityId', 'keyword', 'redirectMode']);

        DatabaseModel::insert([
            'table'         => 'groupbasket_redirect',
            'columnsValues' => [
                'action_id'     => $aArgs['actionId'],
                'group_id'      => $aArgs['groupId'],
                'basket_id'     => $aArgs['id'],
                'entity_id'     => $aArgs['entityId'],
                'keyword'       => $aArgs['keyword'],
                'redirect_mode' => $aArgs['redirectMode']
            ]
        ]);

        return true;
    }

    public static function createGroupActionStatus(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'groupId', 'actionId', 'statusId']);
        ValidatorModel::stringType($aArgs, ['id', 'groupId', 'actionId', 'statusId']);

        DatabaseModel::insert([
            'table'         => 'groupbasket_status',
            'columnsValues' => [
                'action_id'     => $aArgs['actionId'],
                'group_id'      => $aArgs['groupId'],
                'basket_id'     => $aArgs['id'],
                'status_id'     => $aArgs['statusId']
            ]
        ]);

        return true;
    }

    public static function deleteGroup(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'groupId']);
        ValidatorModel::stringType($aArgs, ['id', 'groupId']);

        DatabaseModel::delete([
            'table' => 'groupbasket',
            'where' => ['basket_id = ?', 'group_id = ?'],
            'data'  => [$aArgs['id'], $aArgs['groupId']]
        ]);
        DatabaseModel::delete([
            'table' => 'actions_groupbaskets',
            'where' => ['basket_id = ?', 'group_id = ?'],
            'data'  => [$aArgs['id'], $aArgs['groupId']]
        ]);
        DatabaseModel::delete([
            'table' => 'groupbasket_redirect',
            'where' => ['basket_id = ?', 'group_id = ?'],
            'data'  => [$aArgs['id'], $aArgs['groupId']]
        ]);
        DatabaseModel::delete([
            'table' => 'groupbasket_status',
            'where' => ['basket_id = ?', 'group_id = ?'],
            'data'  => [$aArgs['id'], $aArgs['groupId']]
        ]);

        return true;
    }

    public static function getActionsForGroupById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'groupId']);
        ValidatorModel::stringType($aArgs, ['id', 'groupId']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aGroups = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['actions_groupbaskets'],
            'where'     => ['basket_id = ?', 'group_id = ?'],
            'data'      => [$aArgs['id'], $aArgs['groupId']]
        ]);

        return $aGroups;
    }

    public static function getGroupBasketStatusesIn(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'groupId', 'actionIds']);
        ValidatorModel::stringType($aArgs, ['id', 'groupId']);
        ValidatorModel::arrayType($aArgs, ['select', 'actionIds']);

        $aStatuses = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['groupbasket_status'],
            'where'     => ['basket_id = ?', 'group_id = ?', 'action_id in (?)'],
            'data'      => [$aArgs['id'], $aArgs['groupId'], $aArgs['actionIds']]
        ]);

        return $aStatuses;
    }

    public static function getGroupBasketRedirectIn(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'groupId', 'actionIds']);
        ValidatorModel::stringType($aArgs, ['id', 'groupId']);
        ValidatorModel::arrayType($aArgs, ['select', 'actionIds']);

        $aRedirects = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['groupbasket_redirect'],
            'where'     => ['basket_id = ?', 'group_id = ?', 'action_id in (?)'],
            'data'      => [$aArgs['id'], $aArgs['groupId'], $aArgs['actionIds']]
        ]);

        return $aRedirects;
    }

    public static function hasGroup(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'groupId']);
        ValidatorModel::stringType($aArgs, ['id', 'groupId']);

        $groups = BasketModel::getGroups(['id' => $aArgs['id'], 'select' => ['group_id']]);

        foreach ($groups as $group) {
            if ($group['group_id'] == $aArgs['groupId']) {
                return true;
            }
        }

        return false;
    }

    public static function getResListById(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['basketId']);
        ValidatorModel::stringType($aArgs, ['basketId']);

        $aBasket = DatabaseModel::select([
            'select'    => ['basket_clause', 'basket_res_order'],
            'table'     => ['baskets'],
            'where'     => ['basket_id = ?'],
            'data'      => [$aArgs['basketId']]
        ]);

        if (empty($aBasket[0]) || empty($aBasket[0]['basket_clause'])) {
            return [];
        }

        $sec = new \SecurityControler();
        $where = $sec->process_security_where_clause($aBasket[0]['basket_clause'], $_SESSION['user']['UserId'], false);

        $aResList = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['res_view_letterbox'],
            'where'     => [$where],
            'order_by'  => empty($aBasket[0]['basket_res_order']) ? ['creation_date DESC'] : [$aBasket[0]['basket_res_order']],
        ]);

        return $aResList;
    }

    public static function getBasketsByUserId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);
        ValidatorModel::arrayType($aArgs, ['unneededBasketId']);

        $userGroups = UserModel::getGroupsByUserId(['userId' => $aArgs['userId']]);
        $groupIds = [];
        foreach ($userGroups as $value) {
            $groupIds[] = $value['group_id'];
        }

        $aBaskets = [];
        if (!empty($groupIds)) {
            $where = ['group_id in (?)', 'groupbasket.basket_id = baskets.basket_id'];
            $data = [$groupIds];
            if (!empty($aArgs['unneededBasketId'])) {
                $where[] = 'groupbasket.basket_id not in (?)';
                $data[]  = $aArgs['unneededBasketId'];
            }
            $aBaskets = DatabaseModel::select([
                    'select'    => ['groupbasket.basket_id', 'group_id', 'basket_name', 'basket_desc'],
                    'table'     => ['groupbasket, baskets'],
                    'where'     => $where,
                    'data'      => $data,
                    'order_by'  => ['group_id, basket_order, basket_name']
            ]);

            foreach ($aBaskets as $key => $value) {
                $aBaskets[$key]['is_virtual'] = 'N';
                $aBaskets[$key]['basket_owner'] = $aArgs['userId'];
                $aBaskets2 = DatabaseModel::select([
                        'select'    => ['new_user'],
                        'table'     => ['user_abs'],
                        'where'     => ['user_abs = ?', 'basket_id = ?'],
                        'data'      => [$aArgs['userId'],$value['basket_id']],
                ]);
                $aBaskets[$key]['userToDisplay'] = UserModel::getLabelledUserById(['userId' => $aBaskets2[0]['new_user']]);
                $aBaskets[$key]['enabled'] = true;
            }
            $aBaskets = array_merge($aBaskets, BasketModel::getAbsBasketsByUserId(['userId' => $aArgs['userId']]));
        }

        return $aBaskets;
    }

    public static function getAbsBasketsByUserId(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $aBaskets = DatabaseModel::select([
                'select'    => ['ba.basket_id', 'ba.basket_name', 'ua.user_abs', 'ua.basket_owner', 'ua.is_virtual'],
                'table'     => ['baskets ba, user_abs ua'],
                'where'     => ['ua.new_user = ?', 'ua.basket_id = ba.basket_id'],
                'data'      => [$aArgs['userId']],
                'order_by'  => ['ba.basket_order, ba.basket_name']
        ]);

        foreach ($aBaskets as $key => $value) {
            $aBaskets[$key]['userToDisplay'] = UserModel::getLabelledUserById(['userId' => $value['user_abs']]);
        }

        return $aBaskets;
    }

    public static function setRedirectedBaskets(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userAbs', 'newUser', 'basketId', 'basketOwner', 'isVirtual']);
        ValidatorModel::stringType($aArgs, ['userAbs', 'newUser', 'basketId', 'basketOwner', 'isVirtual']);

        DatabaseModel::insert([
            'table'         => 'user_abs',
            'columnsValues' => [
                'user_abs'      => $aArgs['userAbs'],
                'new_user'      => $aArgs['newUser'],
                'basket_id'     => $aArgs['basketId'],
                'basket_owner'  => $aArgs['basketOwner'],
                'is_virtual'    => $aArgs['isVirtual']
            ]
        ]);

        return true;
    }

    public static function updateRedirectedBaskets(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId', 'basketOwner', 'basketId', 'userAbs', 'newUser']);
        ValidatorModel::stringType($aArgs, ['userId']);

        DatabaseModel::update([
            'table'     => 'user_abs',
            'set'       => [
                'new_user' => $aArgs['newUser']
            ],
            'where'     => ['basket_id = ?', 'basket_owner = ?', 'user_abs = ?', 'new_user = ?'],
            'data'      => [$aArgs['basketId'], $aArgs['basketOwner'], $aArgs['userAbs'], $aArgs['userId']]
        ]);

        return true;
    }

    public static function deleteBasketRedirection(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId', 'basketId']);
        ValidatorModel::stringType($aArgs, ['userId', 'basketId']);

        DatabaseModel::delete([
            'table' => 'user_abs',
            'where' => ['(user_abs = ? OR basket_owner = ?)', 'basket_id = ?'],
            'data'  => [$aArgs['userId'], $aArgs['userId'], $aArgs['basketId']]
        ]);

        return true;
    }

    public static function getRedirectedBasketsByUserId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $aBaskets = DatabaseModel::select([
            'select'    => ['ba.basket_id', 'ba.basket_name', 'ua.new_user', 'ua.basket_owner'],
            'table'     => ['baskets ba, user_abs ua'],
            'where'     => ['ua.user_abs = ?', 'ua.basket_id = ba.basket_id'],
            'data'      => [$aArgs['userId']],
            'order_by'  => ['ua.system_id']
        ]);

        foreach ($aBaskets as $key => $value) {
            $user = UserModel::getByUserId(['userId' => $value['new_user'], 'select' => ['firstname', 'lastname']]);
            $aBaskets[$key]['userToDisplay']     = "{$user['firstname']} {$user['lastname']}";
            $aBaskets[$key]['userIdRedirection'] = $value['new_user'];
            $aBaskets[$key]['user']              = "{$user['firstname']} {$user['lastname']}" ;
        }

        return $aBaskets;
    }

    public static function getRegroupedBasketsByUserId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $regroupedBaskets = [];

        $user = UserModel::getByUserId(['userId' => $aArgs['userId'], 'select' => ['id']]);

        $groups = UserModel::getGroupsByUserId(['userId' => $aArgs['userId']]);
        foreach ($groups as $group) {
            $baskets = DatabaseModel::select([
                'select'    => ['baskets.basket_id', 'baskets.basket_name', 'baskets.color'],
                'table'     => ['groupbasket, baskets'],
                'where'     => ['groupbasket.basket_id = baskets.basket_id', 'groupbasket.group_id = ?', 'baskets.is_visible = ?', 'baskets.basket_id != ?'],
                'data'      => [$group['group_id'], 'Y', 'IndexingBasket'],
                'order_by'  => ['baskets.basket_order', 'baskets.basket_name']
            ]);
            $coloredBaskets = DatabaseModel::select([
                'select'    => ['basket_id', 'color'],
                'table'     => ['users_baskets'],
                'where'     => ['user_serial_id = ?', 'group_id = ?', 'color is not null'],
                'data'      => [$user['id'], $group['group_id']]
            ]);

            foreach ($baskets as $kBasket => $basket) {
                foreach ($coloredBaskets as $coloredBasket) {
                    if ($basket['basket_id'] == $coloredBasket['basket_id']) {
                        $baskets[$kBasket]['color'] = $coloredBasket['color'];
                    }
                }
                if (empty($baskets[$kBasket]['color'])) {
                    $baskets[$kBasket]['color'] = '#666666';
                }
            }

            $regroupedBaskets[] = [
                'groupId'     => $group['group_id'],
                'groupDesc'   => $group['group_desc'],
                'baskets'     => $baskets
            ];
        }

        return $regroupedBaskets;
    }

    public static function getColoredBasketsByUserId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $user = UserModel::getByUserId(['userId' => $aArgs['userId'], 'select' => ['id']]);

        $coloredBaskets = DatabaseModel::select([
            'select'    => ['basket_id', 'group_id', 'color'],
            'table'     => ['users_baskets'],
            'where'     => ['user_serial_id = ?', 'color is not null'],
            'data'      => [$user['id']]
        ]);

        return $coloredBaskets;
    }

    public static function getBasketPages()
    {
        $customId = CoreConfigModel::getCustomId();
        if (file_exists("custom/{$customId}/modules/basket/xml/basketpage.xml")) {
            $path = "custom/{$customId}/modules/basket/xml/basketpage.xml";
        } else {
            $path = 'modules/basket/xml/basketpage.xml';
        }

        $basketPages = [];
        if (file_exists($path)) {
            $loadedXml = simplexml_load_file($path);
            if ($loadedXml) {
                foreach ($loadedXml->BASKETPAGE as $value) {
                    $basketPages[] = [
                        'id'    => (string)$value->ID,
                        'label' => (string)$value->LABEL,
                        'name'  => (string)$value->NAME
                    ];
                }
            }
        }

        return $basketPages;
    }

    // TODO In Progress
    public static function getPreparedClauseById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'userId']);
        ValidatorModel::stringType($aArgs, ['id', 'userId']);

        $aBasket = DatabaseModel::select([
            'select'    => ['basket_clause'],
            'table'     => ['baskets'],
            'where'     => ['basket_id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        $clause = $aBasket[0]['basket_clause'];

        if (preg_match('/@user/', $clause)) {
            $clause = str_replace('@user', "'{$aArgs['userId']}'", $clause);
        }
        if (preg_match('/@email/', $clause)) {
            $user = UserModel::getByUserId(['userId' => $aArgs['userId'], 'select' => ['mail']]);
            $clause = str_replace('@email', "'{$user['mail']}'", $clause);
        }
        if (preg_match('/@my_entities/', $clause)) {
            $entities = EntityModel::getByUserId(['userId' => $aArgs['userId'], 'select' => ['entity_id']]);

            $myEntitiesClause = '';
            foreach ($entities as $key => $entity) {
                if ($key > 0) {
                    $myEntitiesClause .= ", ";
                }
                $myEntitiesClause .= "'{$entity['entity_id']}'";
            }

            if (empty($myEntitiesClause)) {
                $myEntitiesClause = "''";
            }

            $clause = str_replace('@my_entities', $myEntitiesClause, $clause);
        }
        if (preg_match('/@my_primary_entity/', $clause)) {
            $entity = UserModel::getPrimaryEntityByUserId(['userId' => $aArgs['userId']]);

            $primaryEntity = $entity['entity_id'];
            if (empty($entity)) {
                $primaryEntity = "''";
            }

            $clause = str_replace('@my_primary_entity', $primaryEntity, $clause);
        }

        return $clause;
    }
}
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
* @ingroup basket
*/

namespace Baskets\Models;

use Core\Models\DatabaseModel;
use Core\Models\UserModel;
use Core\Models\ValidatorModel;

require_once 'core/class/SecurityControler.php';

class BasketsModelAbstract
{

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

    public static function getActionByActionId(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['actionId']);
        ValidatorModel::intVal($aArgs, ['actionId']);

        $aAction = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['actions'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['actionId']]
        ]);

        return $aAction[0];
    }

    public static function getActionIdById(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['basketId']);
        ValidatorModel::stringType($aArgs, ['basketId']);

        $aAction = DatabaseModel::select([
            'select'    => ['id_action'],
            'table'     => ['actions_groupbaskets'],
            'where'     => ['basket_id = ?'],
            'data'      => [$aArgs['basketId']]
        ]);

        if (empty($aAction[0])) {
            return '';
        }

        return $aAction[0]['id_action'];
    }

    public static function getBasketsByUserId(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $userGroups = UserModel::getGroupsByUserId(['userId' => $aArgs['userId']]);
        $groupIds = [];
        foreach ($userGroups as $value) {
            $groupIds[] = $value['group_id'];
        }

        $aBaskets = [];
        if (!empty($groupIds)) {
            $aBaskets = DatabaseModel::select([
                    'select'    => ['groupbasket.basket_id', 'group_id', 'basket_name', 'basket_desc'],
                    'table'     => ['groupbasket, baskets'],
                    'debug'     => ['true'],
                    'where'     => ['group_id in (?)', 'groupbasket.basket_id = baskets.basket_id'],
                    'data'      => [$groupIds],
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
            $aBaskets = array_merge($aBaskets, BasketsModel::getAbsBasketsByUserId(['userId' => $aArgs['userId']]));
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

    public static function setBasketsRedirection(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['userId', 'data']);
        ValidatorModel::stringType($aArgs, ['userId']);
        ValidatorModel::arrayType($aArgs, ['data']);

        foreach ($aArgs['data'] as $value) {
            DatabaseModel::insert([
                'table'         => 'user_abs',
                'columnsValues' => [
                    'user_abs'      => $aArgs['userId'],
                    'new_user'      => $value['newUser'],
                    'basket_id'     => $value['basketId'],
                    'basket_owner'  => $value['basketOwner'],
                    'is_virtual'    => $value['virtual']
                ]
            ]);
        }

        return true;
    }
}
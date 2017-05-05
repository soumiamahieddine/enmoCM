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

require_once 'apps/maarch_entreprise/services/Table.php';
require_once 'core/class/SecurityControler.php';

class BasketsModelAbstract extends \Apps_Table_Service 
{

    public static function getResListById(array $aArgs = []) 
    {
        static::checkRequired($aArgs, ['basketId']);
        static::checkString($aArgs, ['basketId']);


        $aBasket = static::select(
            [
            'select'    => ['basket_clause'],
            'table'     => ['baskets'],
            'where'     => ['basket_id = ?'],
            'data'      => [$aArgs['basketId']]
            ]
        );

        if (empty($aBasket[0]) || empty($aBasket[0]['basket_clause'])) {
            return [];
        }

        $sec = new \SecurityControler();
        $where = $sec->process_security_where_clause($aBasket[0]['basket_clause'], $_SESSION['user']['UserId'], false);

        $aResList = static::select(
            [
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['res_view_letterbox'],
            'where'     => [$where],
            'order_by'  => empty($aArgs['order_by']) ? ['creation_date DESC'] : $aArgs['order_by'],
            ]
        );

        return $aResList;
    }

    public static function getActionByActionId(array $aArgs = []) 
    {
        static::checkRequired($aArgs, ['actionId']);
        static::checkNumeric($aArgs, ['actionId']);


        $aAction = static::select(
            [
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['actions'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['actionId']]
            ]
        );

        return $aAction[0];
    }

    public static function getActionIdById(array $aArgs = []) 
    {
        static::checkRequired($aArgs, ['basketId']);
        static::checkString($aArgs, ['basketId']);


        $aAction = static::select(
            [
            'select'    => ['id_action'],
            'table'     => ['actions_groupbaskets'],
            'where'     => ['basket_id = ?'],
            'data'      => [$aArgs['basketId']]
            ]
        );

        if (empty($aAction[0])) {
            return '';
        }

        return $aAction[0]['id_action'];
    }

    public static function getTemplateById(array $aArgs = []) 
    {
        $aAction = static::select(
            [
            'select'    => ['result_page'],
            'table'     => ['groupbasket'],
            'where'     => ['basket_id = ?'],
            'data'      => [$aArgs['basketId']]
            ]
        );

        if (empty($aAction)) {
            return '';
        }

        if (file_exists('custom/' .$_SESSION['custom_override_id']. '/mdoules/basket/xml/basketpage.xml')) {
            $path = 'custom/' .$_SESSION['custom_override_id']. '/modules/basket/xml/basketpage.xml';
        } else {
            $path = 'modules/basket/xml/basketpage.xml';
        }

        $xmlfile = simplexml_load_file($path);
        $name = '';
        foreach ($xmlfile as $basket) {
            if ($basket->ID == $aAction[0]["result_page"]) {
                $name = (string)$basket->NAME;
            } 
        }
        return $name;
    }
    

}
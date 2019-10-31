<?php

namespace Group\controllers;

use Group\models\ServiceModel;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;

class ServiceController
{
    const SERVICE_MENU = [
        "admin",
        "adv_search_mlb",
        "entities_print_sep_mlb",
        "reports",
        "save_numeric_package"
    ];

    public static function getMenuServicesByUserIdByXml(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $rawServicesStoredInDB = ServiceModel::getByUserId(['userId' => $aArgs['userId']]);
        $servicesStoredInDB = [];
        foreach ($rawServicesStoredInDB as $value) {
            $servicesStoredInDB[] = $value['service_id'];
        }

        $menu = [];
        if (!empty($servicesStoredInDB)) {
            $menu = ServiceModel::getApplicationServicesByUserServices(['userServices' => $servicesStoredInDB, 'type' => 'menu']);
            $menuModules = ServiceModel::getModulesServicesByUserServices(['userServices' => $servicesStoredInDB, 'type' => 'menu']);
            $menu = array_merge($menu, $menuModules);
        }

        $userGroups = UserModel::getGroupsByLogin(['login' => $aArgs['userId']]);
        $indexingGroups = [];
        foreach ($userGroups as $group) {
            if ($group['can_index']) {
                $indexingGroups[] = ['id' => $group['id'], 'label' => $group['group_desc']];
            }
        }
        if (!empty($indexingGroups)) {
            $menu[] = [
                'id'        => 'indexing',
                'style'     => 'fa fa-file-medical',
                'name'      => _INDEXING_MLB,
                'groups'    => $indexingGroups
            ];
        }

        return $menu;
    }

    public static function getMenuServicesByUserId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $rawServicesStoredInDB = ServiceModel::getByUserId(['userId' => $aArgs['userId']]);
        $servicesStoredInDB = array_column($rawServicesStoredInDB, 'service_id');

        $menu = [];
        if (!empty($servicesStoredInDB)) {
            foreach ($servicesStoredInDB as $service) {
                if (in_array($service, self::SERVICE_MENU)) {
                    $menu[] = $service;
                }
            }
        }

        $userGroups = UserModel::getGroupsByLogin(['login' => $aArgs['userId']]);
        $indexingGroups = [];
        foreach ($userGroups as $group) {
            if ($group['can_index']) {
                $indexingGroups[] = ['id' => $group['id'], 'label' => $group['group_desc']];
            }
        }
        if (!empty($indexingGroups)) {
            $menu[] = 'indexing';
        }

        return $menu;
    }

    public static function getServicesMenu() {
        return self::SERVICE_MENU;
    }
}

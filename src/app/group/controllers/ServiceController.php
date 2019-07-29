<?php

namespace Group\controllers;

use Group\models\ServiceModel;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;

class ServiceController
{
    public static function getMenuServicesByUserId(array $aArgs)
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
}

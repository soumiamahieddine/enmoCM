<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Service Model
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Models;

require_once 'apps/maarch_entreprise/services/Table.php';

class ServiceModelAbstract extends \Apps_Table_Service
{
    public static function getApplicationAdministrationServicesByXML()
    {
        if (file_exists('custom/' .$_SESSION['custom_override_id']. '/apps/maarch_entreprise/xml/services.xml')) { //Todo No Session
            $path = 'custom/' .$_SESSION['custom_override_id']. '/apps/maarch_entreprise/xml/services.xml';
        } else {
            $path = 'apps/maarch_entreprise/xml/services.xml';
        }

        $xmlfile = simplexml_load_file($path);
        $applicationServices = [];
        foreach ($xmlfile->SERVICE as $value) {
            if ((string)$value->servicetype == 'admin' && (string)$value->enabled === 'true') {
                $name = defined((string) $value->name) ? constant((string) $value->name) : (string) $value->name;
                $comment = defined((string) $value->comment) ? constant((string) $value->comment) : (string) $value->comment;
                $applicationServices[] = [
                    'name'          => $name,
                    'comment'       => $comment,
                    'servicepage'   => (string)$value->servicepage,
                    'style'         => (string)$value->style,
                    'angular'       => empty((string)$value->angular) ? 'false' : (string)$value->angular
                ];
            }
        }

        return $applicationServices;
    }

    public static function getApplicationAdministrationServicesByUserServices(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userServices']);
        static::checkArray($aArgs, ['userServices']);

        if (file_exists('custom/' .$_SESSION['custom_override_id']. '/apps/maarch_entreprise/xml/services.xml')) { //Todo No Session
            $path = 'custom/' .$_SESSION['custom_override_id']. '/apps/maarch_entreprise/xml/services.xml';
        } else {
            $path = 'apps/maarch_entreprise/xml/services.xml';
        }

        $xmlfile = simplexml_load_file($path);
        $applicationServices = [];
        foreach ($xmlfile->SERVICE as $value) {
            if ((string)$value->servicetype == 'admin' && (string)$value->enabled === 'true' && ((string)$value->system_service == 'true' || in_array((string) $value->id, $aArgs['userServices']))) {
                $name = defined((string) $value->name) ? constant((string) $value->name) : (string) $value->name;
                $comment = defined((string) $value->comment) ? constant((string) $value->comment) : (string) $value->comment;
                $applicationServices[] = [
                    'name'          => $name,
                    'comment'       => $comment,
                    'servicepage'   => (string)$value->servicepage,
                    'style'         => (string)$value->style,
                    'angular'       => empty((string)$value->angular) ? 'false' : (string)$value->angular
                ];
            }
        }

        return $applicationServices;
    }

    public static function getModulesAdministrationServicesByXML()
    {
        if (file_exists("custom/{$_SESSION['custom_override_id']}/apps/maarch_entreprise/xml/config.xml")) { //Todo No Session
            $path = "custom/{$_SESSION['custom_override_id']}/apps/maarch_entreprise/xml/config.xml";
        } else {
            $path = 'apps/maarch_entreprise/xml/config.xml';
        }

        $modulesServices = [];

        $xmlfile = simplexml_load_file($path);
        foreach ($xmlfile->MODULES as $mod) {
            $module = (string)$mod->moduleid;

            if (file_exists("custom/{$_SESSION['custom_override_id']}/modules/{$module}/xml/services.xml")) { //Todo No Session
                $path = "custom/{$_SESSION['custom_override_id']}/modules/{$module}/xml/services.xml";
            } else {
                $path = "modules/{$module}/xml/services.xml";
            }

            $xmlModuleFile = simplexml_load_file($path);
            foreach ($xmlModuleFile->SERVICE as $value) {
                if ((string)$value->servicetype == 'admin' && (string)$value->enabled === 'true') {
                    $name = defined((string) $value->name) ? constant((string) $value->name) : (string) $value->name;
                    $comment = defined((string) $value->comment) ? constant((string) $value->comment) : (string) $value->comment;
                    $modulesServices[] = [
                        'name'          => $name,
                        'comment'       => $comment,
                        'servicepage'   => (string)$value->servicepage,
                        'style'         => (string)$value->style,
                        'angular'       => empty((string)$value->angular) ? 'false' : (string)$value->angular
                    ];
                }
            }
        }

        return $modulesServices;
    }

    public static function getModulesAdministrationServicesByUserServices(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userServices']);
        static::checkArray($aArgs, ['userServices']);

        if (file_exists("custom/{$_SESSION['custom_override_id']}/apps/maarch_entreprise/xml/config.xml")) { //Todo No Session
            $path = "custom/{$_SESSION['custom_override_id']}/apps/maarch_entreprise/xml/config.xml";
        } else {
            $path = 'apps/maarch_entreprise/xml/config.xml';
        }

        $modulesServices = [];

        $xmlfile = simplexml_load_file($path);
        foreach ($xmlfile->MODULES as $mod) {
            $module = (string)$mod->moduleid;

            if (file_exists("custom/{$_SESSION['custom_override_id']}/modules/{$module}/xml/services.xml")) { //Todo No Session
                $path = "custom/{$_SESSION['custom_override_id']}/modules/{$module}/xml/services.xml";
            } else {
                $path = "modules/{$module}/xml/services.xml";
            }

            $xmlModuleFile = simplexml_load_file($path);
            foreach ($xmlModuleFile->SERVICE as $value) {
                if ((string)$value->servicetype == 'admin' && (string)$value->enabled === 'true' && ((string)$value->system_service == 'true' || in_array((string) $value->id, $aArgs['userServices']))) {
                    $name = defined((string) $value->name) ? constant((string) $value->name) : (string) $value->name;
                    $comment = defined((string) $value->comment) ? constant((string) $value->comment) : (string) $value->comment;
                    $modulesServices[] = [
                        'name'          => $name,
                        'comment'       => $comment,
                        'servicepage'   => (string)$value->servicepage,
                        'style'         => (string)$value->style,
                        'angular'       => empty((string)$value->angular) ? 'false' : (string)$value->angular
                    ];
                }
            }
        }

        return $modulesServices;
    }

    public static function getAdministrationServicesByUserId(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId']);
        static::checkString($aArgs, ['userId']);

        $rawServicesStoredInDB = UserModel::getServicesById(['userId' => $aArgs['userId']]);
        $servicesStoredInDB = [];
        foreach ($rawServicesStoredInDB as $value) {
            $servicesStoredInDB[] = $value['service_id'];
        }

        $administration = [];
        $administration['application'] = static::getApplicationAdministrationServicesByUserServices(['userServices' => $servicesStoredInDB]);
        $administration['modules'] = static::getModulesAdministrationServicesByUserServices(['userServices' => $servicesStoredInDB]);

        return $administration;
    }

}

<?php

require '../../vendor/autoload.php';

chdir('../..');

$migrated = 0;
$customs =  scandir('custom');
foreach ($customs as $custom) {
    if ($custom == 'custom.xml' || $custom == '.' || $custom == '..') {
        continue;
    }

    \SrcCore\models\DatabasePDO::reset();
    new \SrcCore\models\DatabasePDO(['customId' => $custom]);

    $xmlfile = null;
    $path = "custom/{$custom}/apps/maarch_entreprise/xml/export.xml";
    if (file_exists($path)) {
        $xmlfile = simplexml_load_file($path);
    } else {
        $xmlfile = simplexml_load_file("apps/maarch_entreprise/xml/export.xml");
    }

    if ($xmlfile) {
        $delimiter = (string)$xmlfile->CSVOPTIONS->DELIMITER;

        $aData = [];
        if (!empty($xmlfile->letterbox_coll->FIELD)) {
            foreach ($xmlfile->letterbox_coll->FIELD as $value) {
                $aData[] = json_encode(['value' => (string)$value->DATABASE_FIELD, 'label' => (string)$value->LIBELLE, 'isFunction' => false]);
            }
        }

        if (!empty($xmlfile->letterbox_coll->FUNCTIONS->FUNCTION)) {
            foreach ($xmlfile->letterbox_coll->FUNCTIONS->FUNCTION as $value) {
                $functionName = (string)$value->CALL;
                if ($functionName == 'get_status') {
                    $functionName = 'getStatus';
                } elseif ($functionName == 'get_priority') {
                    $functionName = 'getPriority';
                } elseif ($functionName == 'retrieve_copies') {
                    $functionName = 'getCopyEntities';
                } elseif ($functionName == 'makeLink_detail') {
                    $functionName = 'getDetailLink';
                } elseif ($functionName == 'get_parent_folder') {
                    $functionName = 'getParentFolder';
                } elseif ($functionName == 'get_category_label') {
                    $functionName = 'getCategory';
                } elseif ($functionName == 'get_entity_initiator_short_label') {
                    $functionName = 'getInitiatorEntity';
                } elseif ($functionName == 'get_entity_dest_short_label') {
                    $functionName = 'getDestinationEntity';
                } elseif ($functionName == 'get_contact_type') {
                    $functionName = 'getContactType';
                } elseif ($functionName == 'get_contact_civility') {
                    $functionName = 'getContactCivility';
                } elseif ($functionName == 'get_contact_function') {
                    $functionName = 'getContactFunction';
                } elseif ($functionName == 'get_tags') {
                    $functionName = 'getTags';
                } elseif ($functionName == 'get_signatory_name') {
                    $functionName = 'getSignatories';
                } elseif ($functionName == 'get_signatory_date') {
                    $functionName = 'getSignatureDates';
                }
                $aData[] = json_encode(['value' => $functionName, 'label' => (string)$value->LIBELLE, 'isFunction' => true]);
            }
        }

        if (!empty($xmlfile->letterbox_coll->EMPTYS->EMPTY)) {
            foreach ($xmlfile->letterbox_coll->EMPTYS->EMPTY as $value) {
                $aData[] = json_encode(['label' => (string)$value->LIBELLE, 'isFunction' => false]);
            }
        }

        $users = \User\models\UserModel::get([
            'select' => ['id'],
            'where'  => ['status != ?'],
            'data'   => ['DEL']
            ]);

        $aValues = [];
        foreach ($users as $user) {
            $aValues[] = [$user['id'], $delimiter, json_encode($aData)];
        }

        \SrcCore\models\DatabaseModel::insertMultiple([
            'table'     => 'exports_templates',
            'columns'   => ['user_id', 'delimiter', 'data'],
            'values'    => $aValues
        ]);

        $migrated++;
    }
}

printf($migrated . " custom(s) avec une configuration export trouvé(s) et migré(s).\n");

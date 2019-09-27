<?php

require '../../vendor/autoload.php';

chdir('../..');

$customs =  scandir('custom');

foreach ($customs as $custom) {
    if ($custom == 'custom.xml' || $custom == '.' || $custom == '..') {
        continue;
    }

    \SrcCore\models\DatabasePDO::reset();
    new \SrcCore\models\DatabasePDO(['customId' => $custom]);


    $oldIndexingModels = \SrcCore\models\DatabaseModel::select([
        'select' => ['*'],
        'table'  => ['indexingmodels']
    ]);

    $superadmin = \User\models\UserModel::getByLogin(['select' => ['id'], 'login' => 'superadmin']);
    if (empty($superadmin)) {
        $firstMan = \User\models\UserModel::get(['select' => ['id'], 'orderBy' => ['id'], 'limit' => 1]);
        $masterOwnerId = $firstMan[0]['id'];
    } else {
        $masterOwnerId = $superadmin['id'];
    }

    $migrated = 0;
    foreach ($oldIndexingModels as $oldIndexingModel) {
        $fieldContent = json_decode($oldIndexingModel['fields_content'], true);
        if (empty($fieldContent['category_id'])) {
            continue;
        }

        $modelId = \IndexingModel\models\IndexingModelModel::create([
            'label'     => $oldIndexingModel['label'],
            'category'  => $fieldContent['category_id'],
            'default'   => 'false',
            'owner'     => $masterOwnerId,
            'private'   => 'false'
        ]);

        foreach ($fieldContent as $key => $field) {
            if (in_array($key, ['type_id', 'priority', 'subject', 'destination'])) {
                if ($key == 'type_id') {
                    $identifier = 'doctype';
                } else {
                    $identifier = $key;
                }
                \IndexingModel\models\IndexingModelFieldModel::create([
                    'model_id'      => $modelId,
                    'identifier'    => $identifier,
                    'mandatory'     => 'false',
                    'default_value' => json_encode($field),
                    'unit'          => 'mail'
                ]);
            }
        }
        ++$migrated;
    }

    printf("Migration ancien modèles d'indexation (CUSTOM {$custom}) : " . $migrated . " modèle utilisé(s) et migré(s).\n");
}

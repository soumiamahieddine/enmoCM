<?php

require '../../vendor/autoload.php';

chdir('../..');

$customId = \SrcCore\models\CoreConfigModel::getCustomId();

if (file_exists("custom/{$customId}/apps/maarch_entreprise/xml/entreprise.xml")) {
    $path = "custom/{$customId}/apps/maarch_entreprise/xml/entreprise.xml";
} else {
    $path = 'apps/maarch_entreprise/xml/entreprise.xml';
}

$priorities = [];
if (file_exists($path)) {
    $loadedXml = simplexml_load_file($path);
    if ($loadedXml) {
        $i = 0;
        foreach ($loadedXml->priorities->priority as $value) {
            if (isset($loadedXml->priorities->default_priority) && $loadedXml->priorities->default_priority == $i) {
                $priorities[] = [
                    'id'                => $i,
                    'label'             => (string)$value,
                    'color'             => (string)$value['color'],
                    'working_days'      => (string)$value['working_days'],
                    'delays'            => (string)$value['with_delay'] == 'false' ? null : (int)$value['with_delay'],
                    'default_priority'  => 'true'
                ];
            } else {
                $priorities[] = [
                    'id'                => $i,
                    'label'             => (string)$value,
                    'color'             => (string)$value['color'],
                    'working_days'      => (string)$value['working_days'],
                    'delays'            => (string)$value['with_delay'] == 'false' ? null : (int)$value['with_delay'],
                    'default_priority'  => 'false'
                ];
            }
            ++$i;
        }
    }
}

foreach ($priorities as $key => $priority) {
    if ($priority['default_priority'] == 'true') {
        \Priority\models\PriorityModel::resetDefaultPriority();
    }
    $id = \Priority\models\PriorityModel::create($priority);

    \Resource\models\ResModel::update([
        'set'   => ['priority' => $id],
        'where' => ['priority = ?'],
        'data'  => [$priority['id']]
    ]);

    $priorities[$key]['priorityId'] = $id;
}

$i = 0;
foreach ($loadedXml->process_modes->process_mode as $processMode) {
    foreach ($priorities as $priority) {
        if ($priority['id'] == $processMode->process_mode_priority) {
            $loadedXml->process_modes->process_mode[$i]->process_mode_priority = $priority['priorityId'];
        }
    }
    ++$i;
}

$res = $loadedXml->asXML();
$fp = fopen($path, "w+");
if ($fp) {
    fwrite($fp,$res);
}

<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

*
* @brief   ExportControler
*
* @author  dev <dev@maarch.org>
* @ingroup core
*/
require_once 'core/class/class_functions.php';
require_once 'core/class/class_history.php';

class ExportControler
{
    public $array_export = [];
    public $pos = 0;

    public function __construct()
    {
        $this->retrieve_datas();
        $_SESSION['export']['filename'] = $this->make_csv();
    }

    private function retrieve_datas()
    {
        // Retrieve the query
        $query = $this->make_query();

        // Retrieve datas
        $db = new Database();

        $stmt = $db->query($query, $_SESSION['last_select_query_parameters']);

        while ($line = $stmt->fetchObject()) {
            $this->array_export[] = $line->res_id;
        }
    }

    private function make_query()
    {
        // Retrieve the end of last select query on the list
        $endLastQuery = substr(
                $_SESSION['last_select_query'],
                strpos(
                    $_SESSION['last_select_query'],
                    'FROM'
                )
            );

        // Create template for the new query
        $query_template = 'SELECT ';
        $query_template .= 'res_id ';
        $query_template .= $endLastQuery;

        return $query_template;
    }

    private function make_csv()
    {
        $currentUser = \User\models\UserModel::getByLogin(['login' => $_SESSION['user']['UserId'], 'select' => ['id']]);
        $rawTemplate = \Resource\models\ExportTemplateModel::get(['select' => ['delimiter', 'data'], 'where' => ['user_id = ?', 'format = ?'], 'data' => [$currentUser['id'], 'csv']]);
        if (!empty($rawTemplate[0])) {
            $rawTemplate = $rawTemplate[0];
            $data = json_decode($rawTemplate['data'], true);
        } else {
            $rawTemplate = ['delimiter' => ';'];
            $data = [
                ["value" => "res_id", "label" => "Identifiant GED", "isFunction" => false],
                ["value" => "doc_date", "label" => "Date d'arrivée", "isFunction" => false],
                ["value" => "getInitiatorEntity", "label" => "Entité initiatrice", "isFunction" => true],
                ["value" => "getDestinationEntity", "label" => "Entité traitante", "isFunction" => true],
                ["value" => "getAssignee", "label" => "Destinataire", "isFunction" => true],
                ["value" => "subject", "label" => "Objet", "isFunction" => false],
                ["value" => "type_label", "label" => "Type de courrier", "isFunction" => false],
                ["value" => "getStatus", "label" => "Statut", "isFunction" => true],
                ["value" => "getPriority", "label" => "Priorité", "isFunction" => true],
                ["value" => "getCopies", "label" => "Utilisateurs / Entités en copie", "isFunction" => true],
                ["value" => "getCategory", "label" => "Catégorie", "isFunction" => true],
                ["value" => "getSenders", "label" => "Expéditeurs", "isFunction" => true],
                ["value" => "getRecipients", "label" => "Destinataires", "isFunction" => true],
                ["value" => "getSignatories", "label" => "Signataires", "isFunction" => true],
                ["value" => "getSignatureDates", "label" => "Date de signature", "isFunction" => true],
                ["value" => "getTags", "label" => "Mots clés", "isFunction" => true],
            ];
        }

        $select           = ['res_view_letterbox.res_id'];
        $tableFunction    = [];
        $leftJoinFunction = [];
        $csvHead          = [];
        foreach ($data as $value) {
            $csvHead[] = $value['label'];
            if (empty($value['value'])) {
                continue;
            }
            if ($value['isFunction']) {
                if ($value['value'] == 'getStatus') {
                    $select[] = 'status.label_status AS "status.label_status"';
                    $tableFunction[] = 'status';
                    $leftJoinFunction[] = 'res_view_letterbox.status = status.id';
                } elseif ($value['value'] == 'getPriority') {
                    $select[] = 'priorities.label AS "priorities.label"';
                    $tableFunction[] = 'priorities';
                    $leftJoinFunction[] = 'res_view_letterbox.priority = priorities.id';
                } elseif ($value['value'] == 'getCategory') {
                    $select[] = 'res_view_letterbox.category_id';
                } elseif ($value['value'] == 'getInitiatorEntity') {
                    $select[] = 'enone.short_label AS "enone.short_label"';
                    $tableFunction[] = 'entities enone';
                    $leftJoinFunction[] = 'res_view_letterbox.initiator = enone.entity_id';
                } elseif ($value['value'] == 'getDestinationEntity') {
                    $select[] = 'entwo.short_label AS "entwo.short_label"';
                    $tableFunction[] = 'entities entwo';
                    $leftJoinFunction[] = 'res_view_letterbox.destination = entwo.entity_id';
                } elseif ($value['value'] == 'getDestinationEntityType') {
                    $select[] = 'enthree.entity_type AS "enthree.entity_type"';
                    $tableFunction[] = 'entities enthree';
                    $leftJoinFunction[] = 'res_view_letterbox.destination = enthree.entity_id';
                } elseif ($value['value'] == 'getTypist') {
                    $select[] = 'res_view_letterbox.typist';
                } elseif ($value['value'] == 'getAssignee') {
                    $select[] = 'res_view_letterbox.dest_user';
                }
            } else {
                $select[] = "res_view_letterbox.{$value['value']}";
            }
        }

        $order = 'CASE res_view_letterbox.res_id ';
        foreach ($this->array_export as $key => $resId) {
            $order .= "WHEN {$resId} THEN {$key} ";
        }
        $order .= 'END';

        $aChunkedResources = array_chunk($this->array_export, 10000);
        $resources = [];
        foreach ($aChunkedResources as $chunkedResource) {
            $resourcesTmp = \Resource\models\ResourceListModel::getOnView([
                'select'    => $select,
                'table'     => $tableFunction,
                'leftJoin'  => $leftJoinFunction,
                'where'     => ['res_view_letterbox.res_id in (?)'],
                'data'      => [$chunkedResource],
                'orderBy'   => [$order]
            ]);
            $resources = array_merge($resources, $resourcesTmp);
        }

        $file = \Resource\controllers\ExportController::getCsv(['delimiter' => $rawTemplate['delimiter'], 'data' => $data, 'resources' => $resources, 'chunkedResIds' => $aChunkedResources]);

        $csvName = $_SESSION['user']['UserId'].'-'.md5(date('Y-m-d H:i:s')).'.csv';
        $pathToCsv = $_SESSION['config']['tmppath'].$csvName;
        file_put_contents($pathToCsv, stream_get_contents($file));
        fclose($file);

        return $csvName;
    }
}

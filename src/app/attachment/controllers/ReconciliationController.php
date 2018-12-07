<?php

namespace Attachment\controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Attachment\models\ReconciliationModel;
use SrcCore\controllers\StoreController;

class ReconciliationController
{
    public function create(Request $request, Response $response, $aArgs)
    {
        if (empty($aArgs)) {
            $aArgs = $request->getParsedBody();
        }
        $aArgs['data'] = $this->object2array($aArgs['data']);

        $return = $this->getWs($aArgs);

        if ($return['errors']) {
            return $response
                ->withStatus(500)
                ->withJson(
                    ['errors' => _NOT_CREATE . ' ' . $return['errors']]
                );
        }

        $wsReturn['resId'] = $return[0];

        return $response->withJson($wsReturn);
    }

    public function getWs($aArgs)
    {
        $identifier = $aArgs['chrono'];
        $res_id = (int) $aArgs['res_id'];
        $encodedContent = $aArgs['encoded_content'];

        $info = $this->get_attachment_info_from_chrono($identifier, 'status IN (\'A_TRA\', \'NEW\', \'TMP\')');
        if (! $info) {
            return false;
        }

        $title           = $info['title'];
        $fileFormat      = 'pdf';
        $attachment_type = 'outgoing_mail_signed';
        $collIdMaster    = 'letterbox_coll';

        $data = [];

        array_push(
            $data,
            array(
                'column' => 'title',
                'value' => $title,
                'type' => 'string',
            )
        );
        array_push(
            $data,
            array(
                'column' => 'identifier',
                'value' => $identifier,
                'type' => 'string',
            )
        );
        array_push(
            $data,
            array(
                'column' => 'attachment_type',
                'value' => $attachment_type,
                'type' => 'string',
            )
        );
        array_push(
            $data,
            array(
                'column' => 'dest_contact_id',
                'value' => $info['dest_contact_id'],
                'type' => 'integer',
            )
        );
        array_push(
            $data,
            array(
                'column' => 'dest_address_id',
                'value' => $info['dest_address_id'],
                'type' => 'integer',
            )
        );

        $ac = new AttachmentController();
        $aArgs = [
            'resId'             => $res_id,
            'collId'            => $collIdMaster,
            'collIdMaster'      => $collIdMaster,
            'table'             => 'res_attachments',
            'encodedFile'       => $encodedContent,
            'fileFormat'        => $fileFormat,
            'data'              => $data
        ];

        $new_attachment = $ac->storeAttachmentResource($aArgs);

        // Suppression du projet de reponse
        $delete_response_project = $_SESSION['modules_loaded']['attachments']['reconciliation']['delete_response_project'] == 'true';

        if ($delete_response_project) {
            ReconciliationModel::updateReconciliation([
                'set'       => ['status' => 'DEL'],
                'where'     => ['res_id = (?)'],
                'data'      => [$info['res_id']],
                'table'     => 'res_attachments'
            ]);
        }

        // Cloture du courrier entrant
        $close_incoming = $_SESSION['modules_loaded']['attachments']['reconciliation']['close_incoming'] == 'true';
        if ($close_incoming) {
            ReconciliationModel::updateReconciliation([
                'set'       => ['status' => 'END'],
                'where'     => ['res_id = (?)'],
                'data'      => [$res_id],
                'table'     => 'res_letterbox'
            ]);
        }

        $result = [$new_attachment[0]];

        return $result;
    }

    /*
     *  Recupere toutes les infos d'une PJ avec son num chrono
     *  Retourne la PJ la plus recente
     */
    private function get_attachment_info_from_chrono($identifier, $filter=null)
    {
        $collId = 'attachments_coll';
        $sec    = new \security();
        $table  = $sec->retrieve_table_from_coll($collId);

        $result = ReconciliationModel::selectReconciliation([
            'select'    => ['*'],
            'table'     => [$table],
            'where'     => [isset($filter) ? 'identifier = (?) AND ' . $filter : 'identifier = (?)'],
            'data'      => [$identifier],
            'orderBy'   => ['res_id DESC']
        ]);
        return $result[0];
    }

    public function checkAttachment(Request $request, Response $response, $aArgs)
    {
        if (!empty($aArgs)) {
            $aArgs = $aArgs;
        } else {
            $aArgs = $request->getParsedBody();
        }
        $attachment = $this->get_attachment_info_from_chrono($aArgs['chrono'], "status IN ('TMP', 'A_TRA','NEW')");
        $result = ($attachment != false)? 'OK': 'KO';
        return $response->withJson(array('result' => $result));
    }


    private function object2array($object)
    {
        $return = null;
        if (is_array($object)) {
            foreach ($object as $key => $value) {
                $return[$key] = $this->object2array($value);
            }
        } else {
            if (is_object($object)) {
                $var = get_object_vars($object);
                if ($var) {
                    foreach ($var as $key => $value) {
                        $return[$key] = ($key && !$value) ? null : $this->object2array($value);
                    }
                } else {
                    return $object;
                }
            } else {
                return $object;
            }
        }
        return $return;
    }

    public function get_values_in_array($val)
    {
        $tab = explode('$$', $val);
        $values = array();
        for ($i=0; $i<count($tab); $i++) {
            $tmp = explode('#', $tab[$i]);

            $val_tmp=array();
            for ($idiese=1; $idiese<count($tmp); $idiese++) {
                $val_tmp[]=$tmp[$idiese];
            }
            $valeurDiese = implode("#", $val_tmp);
            if (isset($tmp[1])) {
                array_push($values, array('ID' => $tmp[0], 'VALUE' => $valeurDiese));
            }
        }
        return $values;
    }
}

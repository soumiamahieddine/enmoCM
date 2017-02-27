<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Export seda Action
* @author dev@maarch.org
* @ingroup export_seda
*/


/**
* $confirm  bool true
*/
$confirm = true;

/**
* $etapes  array Contains only one etap, the status modification
*/
 
$etapes = array('export');


function manage_export($arr_id, $history, $id_action, $label_action, $status)
{
    // récupérer l'entité racine du courrier
    // récupérer transferring_agency et archival_agreement

    // récupérer la duration et retention_rule du type de doc du courrier

    // appel fonction de transfert et génération bdx

    // historisation du transfert

    // modification statut -> fait automatiquement par mécanique bannette

    // ensuite il y a aura une suppression logique des documents et des contacts (si plus de courriers associés)

    for($i=0; $i<count($arr_id);$i++)
    {
        $result .= $arr_id[$i].'#';
        //$db->query("UPDATE ".$ext_table. " SET closing_date = CURRENT_TIMESTAMP WHERE res_id = ?", array($arr_id[$i]));

    }

    return array('result' => $result, 'history_msg' => '');
}


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
    require_once('modules/export_seda/ArchiveTransfer.php');

    $archiveTransfer = new ArchiveTransfer();

    
    // récupérer l'entité racine du courrier *
    // récupérer archival_agency et archival_agreement *

    // récupérer la retention_final_disposition et retention_rule du type de doc du courrier *

    // appel fonction de transfert et génération bdx *

    $result = $archiveTransfer->receive($arr_id);

    // historisation du transfert

    // modification statut -> fait automatiquement par mécanique bannette

    // ensuite il y a aura une suppression logique des documents et des contacts (si plus de courriers associés)

    return array('result' => $result, 'history_msg' => '');
}


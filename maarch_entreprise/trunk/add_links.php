<?php
if (isset($_REQUEST['res_id']) && isset($_REQUEST['res_id_child'])) {
    $res_child = $_REQUEST['res_id_child'];
    if (!empty($_REQUEST['res_id'])) {
        require_once('core/class/class_core_tools.php');
        require_once('core/class/class_history.php');
        require_once('core/class/LinkController.php');

        $Core_Tools = new core_tools;
        $Core_Tools->load_lang();
        $Class_LinkController = new LinkController();
        $db = new dbquery;
        $db->connect();

        $res_parent = $_REQUEST['res_id'];

        if ($_REQUEST['mode'] == 'add') {
            $self = false;
            if ($res_child == $res_parent) {
                $self = true;
            } else {
                $queryTest = "SELECT * FROM res_linked WHERE res_parent=".$res_parent." AND res_child=".$res_child." AND coll_id='".$_SESSION['collection_id_choice']."'";
                $db->query($queryTest);
                $i = 0;
                while($test = $db->fetch_object()) {
                    $i++;
                }
            }
            
            if ($i == 0 && !$self) {
                $queryAddLink = "INSERT INTO res_linked (res_parent, res_child, coll_id) VALUES('" . $res_parent . "', '" . $res_child . "', '" . $_SESSION['collection_id_choice'] . "')";

                $db->query($queryAddLink);

                $hist2 = new history();
                $hist2->add(
                    $_REQUEST['tableHist'],
                   $res_child,
                   "ADD",
                   'linkadd',
                   _LINKED_TO . $res_parent,
                   $_SESSION['config']['databasetype'],
                   'apps'
                );

                $hist3 = new history();
                $hist3->add(
                    $_REQUEST['tableHist'],
                    $res_parent,
                   "ADD",
                   'linkup',
                   _THE_DOCUMENT_LINK . $res_child . ' ' . _NOW_LINK_WITH_THIS_ONE,
                   $_SESSION['config']['databasetype'],
                   'apps'
                );
            }

        } elseif($_REQUEST['mode'] == 'del') {
            $queryDelLink = "DELETE FROM res_linked WHERE res_parent=".$res_parent." AND res_child=".$res_child." and coll_id='".$_SESSION['collection_id_choice']."'";

            $db->query($queryDelLink);

            $hist2 = new history();
            $hist2->add(
                $_REQUEST['tableHist'],
               $res_child,
               "DEL",
               'linkdel',
               _LINK_TO_THE_DOCUMENT. '  ('.$res_parent.') ' . _LINK_DELETED,
               $_SESSION['config']['databasetype'],
               'apps'
            );

            $hist3 = new history();
            $hist3->add(
                $_REQUEST['tableHist'],
                $res_parent,
               "DEL",
               'linkdel',
               _THE_DOCUMENT_LINK . $res_child . ' ' . _NO_LINK_WITH_THIS_ONE,
               $_SESSION['config']['databasetype'],
               'apps'
            );
        }

        $formatText = '';

        $nbLinkDesc = $Class_LinkController->nbDirectLink(
            $_SESSION['doc_id'],
            $_SESSION['collection_id_choice'],
            'desc'
        );
        if ($nbLinkDesc > 0) {
            $formatText .= '<img src="static.php?filename=cat_doc_incoming.gif" />';
            $formatText .= $Class_LinkController->formatMap(
                $Class_LinkController->getMap(
                    $_SESSION['doc_id'],
                    $_SESSION['collection_id_choice'],
                    'desc'
                ),
                'desc'
            );
            $formatText .= '<br />';
        }

        $nbLinkAsc = $Class_LinkController->nbDirectLink(
            $_SESSION['doc_id'],
            $_SESSION['collection_id_choice'],
            'asc'
        );
        if ($nbLinkAsc > 0) {
            $formatText .= '<img src="static.php?filename=cat_doc_outgoing.gif" />';
            $formatText .= $Class_LinkController->formatMap(
                $Class_LinkController->getMap(
                    $_SESSION['doc_id'],
                    $_SESSION['collection_id_choice'],
                    'asc'
                ),
                'asc'
            );
            $formatText .= '<br />';
        }
        
        if ($self) {
            $formatText .= '';
        }

        if ($i != 0) {
            $formatText .= '<br />';
            $formatText .= '<span style="color: rgba(255, 0, 0, 1); font-weight: bold; font-size: larger;">';
                $formatText .= _LINK_ALREADY_EXISTS;
            $formatText .= '</span>';
            $formatText .= '<br />';
            $formatText .= '<br />';
        }

        $nb = $Class_LinkController->nbDirectLink(
            $_SESSION['doc_id'],
            $_SESSION['collection_id_choice'],
            'all'
        );

        echo "{status : 0, links : '" . addslashes($formatText) . "', nb : '".$nb."'}";
        exit ();

    }
    //header("Location: index.php?page=".$_REQUEST['pageHeader']."&dir=".$_REQUEST['dirHeader']."&id=".$res_child);

} else {
    $Links .= '<h2>';
        $Links .= _ADD_A_LINK;
    $Links .= '</h2>';
    $Links .= '<br />';

    //formulaire
    $Links .= '<form action="index.php" method="">';
        $Links .= '<table width="50%" border="0" >';
            $Links .= '<tr>';
                $Links .= '<td style="text-align: left;">';
                    $Links .= '<input ';
                      $Links .= 'onclick="window.open(';
                        $Links .= '\'' . $_SESSION['config']['businessappurl'] . 'index.php?display=true&dir=indexing_searching&page=search_adv&mode=popup&action_form=show_res_id&modulename=attachments&init_search&exclude='.$_SESSION['doc_id'].'&nodetails\', ';
                        $Links .= '\'search_doc_for_attachment\', ';
                        $Links .= '\'scrollbars=yes,menubar=no,toolbar=no,resizable=yes,status=no,width=1100,height=775\'';
                        $Links .= ');"';
                      $Links .= 'type="button" ';
                      $Links .= 'value="Rechercher un document"';
                      $Links .= 'class="button" ';
                    $Links .= '/>';
                $Links .= '</td>';
                $Links .= '<td style="text-align: left;">';
                    $Links .= '<input ';
                      $Links .= 'type="hidden" ';
                      $Links .= 'name="res_id_child" ';
                      $Links .= 'value="'.$_SESSION['doc_id'].'" ';
                    $Links .= '>';
                    $Links .= '<input ';
                      $Links .= 'type="hidden" ';
                      $Links .= 'name="page" ';
                      $Links .= 'value="add_links" ';
                    $Links .= '>';
                    $Links .= '<input ';
                      $Links .= 'type="hidden" ';
                      $Links .= 'name="tableHist" ';
                      $Links .= 'value="'.$table.'" ';
                    $Links .= '>';
                    $Links .= '<label for="res_id_link">'.ucfirst(_DOC_NUM).':&nbsp;</label>';
                    $Links .= '<input ';
                      $Links .= 'type="text" ';
                      $Links .= 'disabled="disabled" ';
                      $Links .= 'readonly="readonly" ';
                      $Links .= 'name="res_id_link" ';
                      $Links .= 'id="res_id_link" ';
                    $Links .= '>';
                $Links .= '</td>';
            $Links .= '</tr>';
            $Links .= '<tr>';
                $Links .= '<td>';
                    $Links .= '<input ';
                      $Links .= 'type="button" ';
                      $Links .= 'class="button" ';
                      $Links .= 'onClick="if($(\'res_id_link\').value != \'\') addLinks(\''.$_SESSION['config']['businessappurl'].'index.php?page=add_links&display=true\', \''.$_SESSION['doc_id'].'\', $(\'res_id_link\').value, \'add\');';
                      $Links .= '$(\'res_id_link\').setValue(\'\');"';
                      $Links .= 'value="&nbsp;&nbsp; '._LINK_ACTION.' &nbsp;&nbsp;" ';
                    $Links .= '>';
                $Links .= '</td>';
                $Links .= '<td>';
                    $Links .= '&nbsp;';
                $Links .= '</td>';
            $Links .= '</tr>';
        $Links .= '</table>';
    $Links .= '</form>';
}

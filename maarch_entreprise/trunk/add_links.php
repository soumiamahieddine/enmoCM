<?php
if (isset($_REQUEST['res_id']) && isset($_REQUEST['res_id_child'])) {
    $res_child = $_REQUEST['res_id_child'];
    if (!empty($_REQUEST['res_id'])) {
        require_once('core/class/class_core_tools.php');
        require_once('core/class/class_history.php');

        $Core_Tools = new core_tools;
        $Core_Tools->load_lang();

        $db = new dbquery;
        $db->connect();

        $res_parent = $_REQUEST['res_id'];

        $queryLink = "INSERT INTO res_linked (res_parent, res_child, coll_id) VALUES('" . $res_parent . "', '" . $res_child . "', '" . $_SESSION['collection_id_choice'] . "')";

        $db->query($queryLink);

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
           "UP",
           'linkup',
           '(doc. ' . $res_child . ')' . _NOW_LINK_WITH_THIS_ONE,
           $_SESSION['config']['databasetype'],
           'apps'
        );
    }
    header("Location: index.php?page=".$_REQUEST['pageHeader']."&dir=".$_REQUEST['dirHeader']."&id=".$res_child);

} else {
    $Links .= '<h2>';
        $Links .= _ADD_A_LINK;
    $Links .= '</h2>';
    $Links .= '<br />';

    //formulaire
    $Links .= '<form action="index.php" method="">';
        $Links .= '<table width="10%" border="0" >';
            $Links .= '<tr>';
                $Links .= '<td style="text-align: right;">';
                    $Links .= '<a ';
                      $Links .= 'href="javascript://" ';
                      $Links .= 'onclick="window.open(';
                        $Links .= '\'' . $_SESSION['config']['businessappurl'] . 'index.php?display=true&dir=indexing_searching&page=search_adv&mode=popup&action_form=show_res_id&modulename=attachments&init_search&exclude='.$_SESSION['doc_id'].'&nodetails\', ';
                        $Links .= '\'search_doc_for_attachment\', ';
                        $Links .= '\'scrollbars=yes,menubar=no,toolbar=no,resizable=yes,status=no,width=1100,height=775\'';
                      $Links .= ');"';
                      $Links .= ' title="' . _SEARCH . '"';
                    $Links .= '>';
                        $Links .= '<span style="font-weight: bold;">';
                            $Links .= '<img ';
                              $Links .= 'src="' . $_SESSION['config']['businessappurl'] . 'static.php?filename=folder_search.gif" ';
                              $Links .= 'width="20px" ';
                              $Links .= 'height="20px" ';
                            $Links .= '/>';
                        $Links .= '</span>';
                    $Links .= '</a>';
                $Links .= '</td>';
                $Links .= '<td>';
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
                      $Links .= 'name="display" ';
                      $Links .= 'value="true" ';
                    $Links .= '>';

                    $Links .= '<input ';
                      $Links .= 'type="hidden" ';
                      $Links .= 'name="tableHist" ';
                      $Links .= 'value="'.$table.'" ';
                    $Links .= '>';

                    $Links .= '<input ';
                      $Links .= 'type="hidden" ';
                      $Links .= 'name="pageHeader" ';
                      $Links .= 'value="details" ';
                    $Links .= '>';
                    $Links .= '<input ';
                      $Links .= 'type="hidden" ';
                      $Links .= 'name="dirHeader" ';
                      $Links .= 'value="indexing_searching" ';
                    $Links .= '>';

                $Links .= '</td>';
                $Links .= '<td style="text-align: right;">';
                    $Links .= '<input ';
                      $Links .= 'type="text" ';
                      $Links .= 'name="res_id" ';
                      $Links .= 'id="res_id" ';
                      $Links .= 'class="readonly" ';
                      $Links .= 'readonly="readonly" ';
                      $Links .= 'style="';
                        $Links .= 'background-color: rgba(225, 225, 225, 1); ';
                        $Links .= 'border: solid 1px rgba(110, 110, 110, 1);';
                      $Links .= '" ';
                    $Links .= '/>';
                $Links .= '</td>';
            $Links .= '</tr>';
            $Links .= '<tr>';
                $Links .= '<td>';
                    $Links .= '&nbsp;';
                $Links .= '</td>';
                $Links .= '<td>';
                    $Links .= '&nbsp;';
                $Links .= '</td>';
                $Links .= '<td';
                $Links .= '>';
                    $Links .= '<input ';
                      $Links .= 'type="submit" ';
                      $Links .= 'class="button" ';
                      $Links .= 'value=" '._LINK_ACTION.' " ';
                    $Links .= '>';
                $Links .= '</td>';
            $Links .= '</tr>';
        $Links .= '</table>';
    $Links .= '</form>';
}

/*
AND res_id <> 170 AND (res_id not in ((SELECT res_parent FROM res_linked WHERE res_child = 170 )) and res_id not in ((SELECT res_child FROM res_linked WHERE res_parent = 170)))
*/


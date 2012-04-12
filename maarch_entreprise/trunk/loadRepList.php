<?php

$return = '';

if (isset($_REQUEST['res_id_master'])) {

    $status = 0;
    $return .= '<td colspan="6">';
        $return .= '<div align="center">';
            $return .= '<table width="90%" style="border: 1px solid #000; margin: 0;">';
                $return .= '<tr style="font-weight: bold;">';
                    $return .= '<td>';
                        $return .= 'Statut';
                    $return .= '</td>';
                    $return .= '<td>';
                        $return .= 'Date';
                    $return .= '</td>';
                    $return .= '<td>';
                        $return .= 'Titre';
                    $return .= '</td>';
                    $return .= '<td>';
                        $return .= 'Auteur';
                    $return .= '</td>';
                    $return .= '<td>';
                        $return .= 'Consulter';
                    $return .= '</td>';
                $return .= '</tr>';


                $db = new dbquery();
                $db->connect();

                $query = "SELECT * FROM res_attachments WHERE res_id_master = ".$_REQUEST['res_id_master'];

                $db->query($query);

                while ($return_db = $db->fetch_object()) {
                    $return .= '<tr>';
                        $return .= '<td>';
                            $return .= $return_db->status;
                        $return .= '</td>';
                        $return .= '<td>';
                            $return .= substr($return_db->creation_date, 0, 10);
                        $return .= '</td>';
                        $return .= '<td>';
                            $return .= $return_db->title;
                        $return .= '</td>';
                        $return .= '<td>';
                            $return .= $return_db->typist;
                        $return .= '</td>';
                        $return .= '<td>';
                            $return .= 'Voir';
                        $return .= '</td>';
                    $return .= '</tr>';
                }

            $return .= '</table>';
        $return .= '</div>';
    $return .= '</td>';
} else {
    $status = 1;
    $return .= '<td colspan="6" style="background-color: red;">';
        $return .= '<p style="padding: 10px; color: black;">';
            $return .= 'Error loading attachments';
        $return .= '</p>';
    $return .= '</td>';
}

echo "{status : " . $status . ", toShow : '" . addslashes($return) . "'}";
exit ();

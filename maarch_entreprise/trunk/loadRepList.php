<?php

require_once('core/class/class_core_tools.php');
$Core_Tools = new core_tools;
$Core_Tools->load_lang();

$return = '';

if (isset($_REQUEST['res_id_master'])) {

    $status = 0;
    $return .= '<td colspan="6" style="background-color: #FFF;">';
        $return .= '<div align="center">';
            $return .= '<table width="100%" style="background-color: rgba(100, 200, 213, 0.2);">';
                $return .= '<tr style="font-weight: bold;">';
                    $return .= '<th style="font-weight: bold; color: black;">';
                        $return .= _STATUS;
                    $return .= '</th>';
                    $return .= '<th style="font-weight: bold; color: black;">';
                        $return .= _DATE;
                    $return .= '</th>';
                    $return .= '<th style="font-weight: bold; color: black;">';
                        $return .= _SUBJECT;
                    $return .= '</th>';
                    $return .= '<th style="font-weight: bold; color: black;">';
                        $return .= _AUTHOR;
                    $return .= '</th>';
                    $return .= '<th style="font-weight: bold; color: black;">';
                        $return .= _CONSULT;
                    $return .= '</th>';
                $return .= '</tr>';


                $db = new dbquery();
                $db->connect();

                $query = "SELECT * FROM res_attachments WHERE res_id_master = ".$_REQUEST['res_id_master']." AND status <> 'DEL'";

                $db->query($query);

                while ($return_db = $db->fetch_object()) {
                    $return .= '<tr style="border: 1px solid;" style="background-color: #FFF;">';
                        $return .= '<td>';
                            $return .= '&nbsp;&nbsp;';
                            $db2 = new dbquery;
                            $db2->connect();
                            $query = "SELECT label_status FROM status WHERE id ='".$return_db->status."'";
                            $db2->query($query);
                            while ($status_db = $db2->fetch_object()) {
                                $return .= $status_db->label_status;
                            }
                        $return .= '</td>';
                        $return .= '<td>';
                            $return .= '&nbsp;&nbsp;';
                            sscanf(substr($return_db->creation_date, 0, 10), "%4s-%2s-%2s", $date_Y, $date_m, $date_d);
                            switch ($date_m)
                            {
                                case '01': $date_m_txt = _JANUARY; break;
                                case '02': $date_m_txt = _FEBRUARY; break;
                                case '03': $date_m_txt = _MARCH; break;
                                case '04': $date_m_txt = _APRIL; break;
                                case '05': $date_m_txt = _MAY; break;
                                case '06': $date_m_txt = _JUNE; break;
                                case '07': $date_m_txt = _JULY; break;
                                case '08': $date_m_txt = _AUGUST; break;
                                case '09': $date_m_txt = _SEPTEMBER; break;
                                case '10': $date_m_txt = _OCTOBER; break;
                                case '11': $date_m_txt = _NOVEMBER; break;
                                case '12': $date_m_txt = _DECEMBER; break;
                                default: $date_m_txt = $date_m;
                            }
                            $return .= $date_d.' '.$date_m_txt.' '.$date_Y;
                        $return .= '</td>';
                        $return .= '<td>';
                            $return .= '&nbsp;&nbsp;';
                            $return .= $return_db->title;
                        $return .= '</td>';
                        $return .= '<td>';
                            $return .= '&nbsp;&nbsp;';
                            $return .= $return_db->typist;
                        $return .= '</td>';
                        $return .= '<td>';
                            $return .= '&nbsp;&nbsp;';
                            $return .= '<a ';
                            $return .= 'href="';
                              $return .= 'index.php?display=true&module=attachments&page=view_attachment&id='.$return_db->res_id;
                            $return .= '" ';
                            $return .= 'target="_blank" ';
                            $return .= '>';
                                $return .= '<img ';
                                $return .= 'src="';
                                    $return .= 'static.php?filename=picto_dld.gif';
                                $return .= '" ';
                                $return .= '/>';
                            $return .= '</a>';
                        $return .= '</td>';
                    $return .= '</tr>';
                }

            $return .= '</table>';
            $return .= '<br />';
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

//usleep(900000);

echo "{status : " . $status . ", toShow : '" . addslashes($return) . "'}";
exit ();

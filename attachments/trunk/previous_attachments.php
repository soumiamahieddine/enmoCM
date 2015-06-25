<?php

require_once('core/class/class_core_tools.php');
require_once "apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR
            ."class".DIRECTORY_SEPARATOR."class_users.php";
$Core_Tools = new core_tools;
$Core_Tools->load_lang();

$users = new class_users();

$return = '';

if (isset($_REQUEST['res_id_version'])) {

    $status = 0;
    $return .= '<td colspan="6" style="background-color: #FFF;">';
        $return .= '<div align="center">';
            $return .= '<table width="100%" style="background-color: rgba(100, 200, 213, 0.2);">';
                $return .= '<tr style="font-weight: bold;">';
                    $return .= '<th style="font-weight: bold; color: black;">';
                        $return .= _STATUS;
                    $return .= '</th>';
                    $return .= '<th style="font-weight: bold; color: black;">';
                        $return .= _VERSION;
                    $return .= '</th>';
                    $return .= '<th style="font-weight: bold; color: black;">';
                        $return .= _CREATION_DATE;
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

                $query = "SELECT attachment_id_master, res_id_master FROM res_view_attachments WHERE res_id_version = "
                    . $_REQUEST['res_id_version'];
                $db->query($query);
				$attach = $db->fetch_object();

                $query = "SELECT status, '1' as relation, creation_date, title, typist, res_id FROM res_attachments WHERE res_id = "
                    . $attach->attachment_id_master 
                    . " UNION ALL SELECT status, relation, creation_date, title, typist, res_id_version FROM res_view_attachments WHERE attachment_id_master = "
                    . $attach->attachment_id_master. " AND status = 'OBS' ORDER BY relation desc";
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
                                $return .= functions::xssafe($status_db->label_status);
                            }
                        $return .= '</td>';
                        $return .= '<td>';
                            $return .= '&nbsp;&nbsp;';
                            $return .= $return_db->relation;
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
                            $return .= functions::xssafe($return_db->title);
                        $return .= '</td>';
                        $return .= '<td>';
                            $return .= '&nbsp;&nbsp;';
                            $current_user = $users->get_user($return_db->typist);
                            $return .= functions::xssafe($current_user['firstname']) . ' ' . functions::xssafe($current_user['lastname']);
                        $return .= '</td>';
                        $return .= '<td>';
                            $return .= '&nbsp;&nbsp;';
                            $return .= '<a ';
                            $return .= 'href="';
                              $return .= 'index.php?display=true&module=attachments&page=view_attachment&id='.$return_db->res_id
                              				.'&res_id_master='.$attach->res_id_master;
                            $return .= '" ';
                            $return .= 'target="_blank" ';
                            $return .= '>';
                                $return .= '<i class="fa fa-download fa-2x"></i>';
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

echo "{status : " . $status . ", toShow : '" . addslashes($return) . "'}";
exit ();

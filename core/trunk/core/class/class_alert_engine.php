<?php
/**
* alert_engine Class
*
* Contains all the specific functions of the diff engine
* @package  core
* @version 1.0
* @since 10/2005
* @license GPL
* @author  Laurent Giovannoni  <dev@maarch.org>
*/
class alert_engine extends dbquery
{
    /**
    * Redefinition of the alert_engine object constructor
    */
    function __construct()
    {
	$args = func_get_args();
	
        if (count($args) < 1) parent::__construct();
	
        else parent::__construct($args[0]);
    }

    /**
    * launch alert
    */
    public function launch_alert($alert_id, $sequence)
    {
        $send_email = true;
        $this->connect();
        $this->query("select * from ".$_SESSION['tablename']['al_alerts']." where alert_status = 'OPEN' and alert_id = ".$alert_id);
        $result = $this->fetch_object();
        //$this->show_array($result);
        $alert_label = $result->alert_label;
        $alert_type = $result->alert_type;
        $coll_id = $result->coll_id;
        $alert_table = $result->alert_table;
        $identifier = $result->identifier;
        $model_id = $result->model_id;
        $use_listinstance = $result->use_listinstance;
        $use_alerts_users = $result->use_alerts_users;
        $alert_unit = $result->alert_unit;
        $alert_frequency = $result->alert_frequency;
        $alert_parameter = $result->alert_parameter;
        $alert_begin_date = $result->alert_begin_date;
        $alert_end_date = $result->alert_end_date;
        $alert_creation_date = $result->alert_creation_date;
        $alert_status = $result->alert_status;
        $alert_text = $result->alert_text;
        $alert_sql_clause = $result->alert_sql_clause;
        $preprocess_script = $result->preprocess_script;
        $postprocess_script = $result->postprocess_script;
        $alert_creator = $result->alert_creator;
        if($use_listinstance == "y")
        {
            //use listinstance to alert
            //verify preprocess script
            if(strlen(trim($preprocess_script)) <> 0)
            {
                echo "\r\nlaunch preprocess : ".$preprocess_script."\r\n";
                include $preprocess_script;
            }
            //verify if we can send email
            /*if($send_email)
            {
                $this->send_email($user, $model_id, $email_object, $alert_text);
            }*/
            //verify postprocess script
            if(strlen(trim($postprocess_script)) <> 0)
            {
                echo "\r\nlaunch postprocess : ".$postprocess_script."\r\n";
                include $postprocess_script;
            }
        }
        elseif($use_alerts_users == "y")
        {
            //use alerts_users to alert
            //verify preprocess script
            if(strlen(trim($preprocess_script)) <> 0)
            {
                echo "\r\nlaunch preprocess : ".$preprocess_script."\r\n";
                include $preprocess_script;
            }
            //verify if we can send email
            if($send_email)
            {
                $this->send_email($user, $model_id, $email_object, $alert_text);
            }
            //verify postprocess script
            if(strlen(trim($postprocess_script)) <> 0)
            {
                echo "\r\nlaunch postprocess : ".$postprocess_script."\r\n";
                include $postprocess_script;
            }
        }
        else
        {
            //alert the alert_creator
            $user = array();
            $tab_alert_type = array();
            $user = $this->retrieve_info_of_user($alert_creator);
            $tab_alert_type = $this->retrieve_info_on_alert_type($alert_type);
            $email_object = $tab_alert_type['email_object'].$identifier." : ".$alert_label;
            //retrieve info on collection
            if($coll_id <> "")
            {
                $coll_script_details = substr($this->retrieve_info_from_coll_id($coll_id, "script_details"),0, strlen($this->retrieve_info_from_coll_id($coll_id, "script_details")) - 4);
                $tab_alert_type['link_page'] = $coll_script_details;
                $coll_script_label = $this->retrieve_info_from_coll_id($coll_id, "label");
                $coll_script_label = _OF_COLLECTION." ".$coll_script_label;
            }
            $alert_text = "<h3>".$tab_alert_type['label']." ".$identifier." ".$coll_script_label." : ".$alert_label."</h3>"._WELCOME_EMAIL_TEXT." : ".$user[0]['lastname']." ".$user[0]['firstname'].". "._HERE_IS_THE_REASON_OF_THE_ALERT."<br><br>".$alert_text;
            $alert_text .= "<br><br><a href=\"".$_SESSION['config']['businessappurl']."index.php?page=".$tab_alert_type['link_page']."&module=".$tab_alert_type['link_module']."&id=".$identifier."\">".$tab_alert_type['link']."</a>";
            //verify preprocess script
            if(strlen(trim($preprocess_script)) <> 0)
            {
                echo "\r\nlaunch preprocess : ".$preprocess_script."\r\n";
                include $preprocess_script;
            }
            //verify if we can send email
            if($send_email)
            {
                $this->send_email($user, $model_id, $email_object, $alert_text);
            }
            //verify postprocess script
            if(strlen(trim($postprocess_script)) <> 0)
            {
                echo "\r\nlaunch postprocess : ".$postprocess_script."\r\n";
                include $postprocess_script;
            }
        }
        $this->calcul_next_alert_launching($alert_id);
    }

    /**
    * Send emails to a box
    */
    /*public function send_email($recipient_info, $model_id, $email_object, $text)
    {
        require_once($_SESSION['config']['MaarchDirectory']."modules".DIRECTORY_SEPARATOR."alert_diffusion".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."mails".DIRECTORY_SEPARATOR."htmlMimeMail.php");
        $mail = new htmlMimeMail();
        //$this->show_array($recipient_info);
        //html struc of text
        $mail->setHtml($text, "Maarch Alert & Diffusion Module");
        //$mail->addHtmlImage($image1, 'logo_orange.PNG', 'image/png');
        //$mail->addHtmlImage($image2, 'logo_frt.PNG', 'image/png');
        //attachment of file
        //$mail->addAttachment($attachment, $users[$i]['TYPE_DOC'].".pdf", 'application/pdf');
        $mail->setReturnPath("'email-robot@maarch.org'");
        //headers
        $mail->setFrom("\"Maarch email robot\" <mail-robot@maarch.org>");
        $mail->setSubject($email_object);
        $mail->setHeader('X-Mailer', 'HTML Mime mail class');
        //sending of email
        $result = $mail->send(array($recipient_info[0]['mail']), 'mail');
        //Retrieve error code of sending mail
        if (!$result)
        {
            echo "***********************ERROR IN SENDING EMAIL********************\n";
            print_r($mail->errors);
            $log .=date("Y-m-d h:m:s")."************ERROR IN SENDING EMAIL*************\n";
            $log .=date("Y-m-d h:m:s").$mail->errors."\n";
        }
        else
        {
            echo "***********************EMAIL SENDED********************\n";
            $log .=date("Y-m-d h:m:s")."************EMAIL SENDED*************\n";
        }
    }*/

    /**
    * Calcul of the next alert launching
    */
    public function calcul_next_alert_launching($alert_id)
    {
        $this->connect();
        $this->query("select * from ".$_SESSION['tablename']['al_alerts']." where alert_id = ".$alert_id);
        $result = $this->fetch_object();
        //$this->show_array($result);
        $alert_label = $result->alert_label;
        $alert_type = $result->alert_type;
        $coll_id = $result->coll_id;
        $alert_table = $result->alert_table;
        $identifier = $result->identifier;
        $model_id = $result->model_id;
        $use_listinstance = $result->use_listinstance;
        $use_alerts_users = $result->use_alerts_users;
        $alert_unit = $result->alert_unit;
        $alert_frequency = $result->alert_frequency;
        $alert_parameter = $result->alert_parameter;
        $alert_begin_date = $result->alert_begin_date;
        $alert_end_date = $result->alert_end_date;
        $alert_creation_date = $result->alert_creation_date;
        $alert_status = $result->alert_status;
        $alert_text = $result->alert_text;
        $alert_sql_clause = $result->alert_sql_clause;
        $preprocess_script = $result->preprocess_script;
        $postprocess_script = $result->postprocess_script;
        $alert_creator = $result->alert_creator;
        $db2 = new dbquery();
        $db2->connect();
        $db2->query("select count(*) as seq from ".$_SESSION['tablename']['al_alert_insts']." where alert_id = ".$alert_id);
        $result2 = $db2->fetch_object();
        if($result2->seq <> 0)
        {
            $next_sequence = $result2->seq + 1;
            $last_sequence = $result2->seq;
            $db2->query("select status from ".$_SESSION['tablename']['al_alert_insts']." where alert_id = ".$alert_id." and sequence = 1");
            $result2 = $db2->fetch_object();
            if($result2->status == "WAIT")
            {
                $bool_first_launching = true;
            }
        }
        else
        {
            $next_sequence = 1;
        }
        echo "\nnext_sequence : ".$next_sequence."\n";
        echo "last_sequence : ".$last_sequence."\n";
        if($alert_status == "CLOSE")
        {
            return "1";
        }
        $insert_column_query = "insert into ".$_SESSION['tablename']['al_alert_insts']." (";
        $insert_value_query = " values(";
        $insert_column_query .= "alert_id, ";
        $insert_value_query .= "".$alert_id.", ";
        echo "alert_unit : ".$alert_unit."\n";
        //case once
        if($alert_unit == "once")
        {
            $due_date = $alert_begin_date;
            $insert_column_query .= "sequence, ";
            $insert_value_query .= "".$next_sequence.", ";
            $insert_column_query .= "due_date, ";
            $insert_value_query .= "'".$due_date."', ";
            $insert_column_query .= "status) ";
            $insert_value_query .= "'WAIT')";
        }
        //other case
        if($alert_unit <> "once")
        {
            if($this->control_alert_end_date_bigger_than_now($alert_end_date, $alert_id))
            {
                echo $_SESSION['error'];
                return "1";
            }
            if($next_sequence == 1)
            {
                echo "\nFirst alert, begin date : ".$alert_begin_date."\n";
                if($alert_unit <> "weekly" && $alert_unit <> "monthly")
                {
                    //due date = beginning
                    echo "\ndue date = beginning\n";
                    $due_date = $alert_begin_date;
                    $no_due_date_calcul = true;
                }
                else
                {
                    if($alert_parameter == "")
                    {
                        //due date = beginning cause of no parameter
                        echo "\ndue date = beginning cause of no parameter\n";
                        $due_date = $alert_begin_date;
                        $no_due_date_calcul = true;
                    }
                }
                $alert_last_execution_date = $alert_begin_date;
            }
            elseif($bool_first_launching)
            {
                $db2->query("select due_date from ".$_SESSION['tablename']['al_alert_insts']." where alert_id = ".$alert_id." and sequence = 1");
                //$db2->show();
                $result2 = $db2->fetch_object();
                $alert_last_execution_date = $result2->due_date;
                echo "\nthe first alert launching, alert_last_execution_date : ".$alert_last_execution_date."\n";
            }
            else
            {
                $db2->query("select due_date from ".$_SESSION['tablename']['al_alert_insts']." where alert_id = ".$alert_id." and sequence = ".$last_sequence);
                //$db2->show();
                $result2 = $db2->fetch_object();
                $alert_last_execution_date = $result2->due_date;
                echo "\nNot the first alert, alert_last_execution_date : ".$alert_last_execution_date."\n";
            }
            if($alert_unit == "weekly")
            {
                $alert_last_execution_week_number = $this->what_is_the_week_number($alert_last_execution_date);
                $alert_last_execution_week_day = $this->what_is_the_week_day($alert_last_execution_date);
                echo "\nweek day : ".$alert_last_execution_week_day."\n\n";
            }
            if($alert_unit == "monthly")
            {
                $alert_last_execution_month_number = $this->what_is_the_month_number($alert_last_execution_date);
                $alert_last_execution_month_day = $this->what_is_the_month_day($alert_last_execution_date);
                echo "\nmonth number : ".$alert_last_execution_month_number."\n\n";
                echo "\nmonth day : ".$alert_last_execution_month_day."\n\n";
            }
            if(!$no_due_date_calcul)
            {
                $due_date = $this->next_due_date_calculation($alert_unit, $alert_frequency, $alert_parameter, $alert_last_execution_date, $alert_last_execution_week_number, $alert_last_execution_week_day, $alert_last_execution_month_number, $alert_last_execution_month_day);
            }
            $insert_column_query .= "sequence, ";
            $insert_value_query .= "".$next_sequence.", ";
            $insert_column_query .= "due_date, ";
            $insert_value_query .= "'".$due_date."', ";
            $insert_column_query .= "status) ";
            $insert_value_query .= "'WAIT')";
        }
        if($alert_unit <> "once")
        {
            if(!$this->control_due_date_bigger_than_alert_end_date($due_date, $alert_end_date, $alert_id))
            {
                //filled alert_insts with the next launching of the alert
                $this->connect();
                $this->query($insert_column_query.$insert_value_query);
            }
            if($next_sequence > 1)
            {
                $this->update_alert_done($alert_id, $last_sequence);
            }
        }
        else
        {
            if($next_sequence == 1)
            {
                //filled alert_insts with the next launching of the alert
                $this->connect();
                $this->query($insert_column_query.$insert_value_query);
            }
            else
            {
                //update inst and close the once alert
                $this->update_alert_done($alert_id, $last_sequence);
                $this->close_alert($alert_id);
            }
        }
        return "0";
    }

    /**
    * Calcul of the next due date calculation
    */
    public function next_due_date_calculation($alert_unit, $alert_frequency, $alert_parameter = "", $alert_last_execution_date, $alert_last_execution_week_number = "", $alert_last_execution_week_day = "", $alert_last_execution_month_number = "", $alert_last_execution_month_day = "")
    {
        echo "alert_unit : ".$alert_unit."\n";
        echo "alert_frequency : ".$alert_frequency."\n";
        echo "alert_parameter : ".$alert_parameter."\n";
        echo "alert_last_execution_date : ".$alert_last_execution_date." ".$this->what_is_the_year($alert_last_execution_date)."\n";
        echo "alert_last_execution_week_number : ".$alert_last_execution_week_number."\n";
        echo "alert_last_execution_week_day : ".$alert_last_execution_week_day."\n\n";
        if($alert_unit <> "weekly" && $alert_unit <> "monthly")
        {
            $next_due_date = $this->calculDate($alert_last_execution_date, $alert_frequency, $alert_unit);
        }
        elseif($alert_unit == "weekly")
        {
            if($alert_last_execution_week_number == $this->what_is_the_week_number($alert_last_execution_date))
            {
                echo "\nsame week number\n";
                $week_number = $alert_last_execution_week_number;
                $same_week = true;
            }
            else
            {
                echo "\nnext week(s)\n";
                $alert_last_execution_week_number = $alert_last_execution_week_number + $alert_frequency;
                $alert_last_execution_year = $this->what_is_the_year($alert_last_execution_date);
                if($alert_last_execution_week_number > 52)
                {
                    $alert_last_execution_year++;
                }
                $week_number = $alert_last_execution_week_number%52;
                if($week_number = 0)
                {
                    $week_number = 52;
                }
                $same_week = false;
            }
            if($alert_parameter <> "")
            {
                $tab_alert_parameter = array();
                $tab_alert_parameter = explode("#", $alert_parameter);
                $this->show_array($tab_alert_parameter);
                if($same_week)
                {
                    for($count_tab_parameter=0;$count_tab_parameter<count($tab_alert_parameter);$count_tab_parameter++)
                    {
                        echo $tab_alert_parameter[$count_tab_parameter]." >? ".$alert_last_execution_week_day."\n";
                        if($tab_alert_parameter[$count_tab_parameter] > $alert_last_execution_week_day)
                        {
                            if($tab_alert_parameter[$count_tab_parameter] <> 1)
                            {
                                $dayfound = $tab_alert_parameter[$count_tab_parameter] - 1;
                            }
                            else
                            {
                                $dayfound = $tab_alert_parameter[$count_tab_parameter];
                            }
                            echo "\n$dayfound found\n\n";
                            break;
                        }
                    }
                    if($dayfound == "")
                    {
                        echo "\nnext week(s) after day not found\n";
                        $alert_last_execution_week_number = $alert_last_execution_week_number + $alert_frequency;
                        $alert_last_execution_year = $this->what_is_the_year($alert_last_execution_date);
                        if($alert_last_execution_week_number > 52)
                        {
                            $alert_last_execution_year++;
                        }
                        $week_number = $alert_last_execution_week_number%52;
                        if($week_number == 0)
                        {
                            $week_number = 52;
                        }
                        if($tab_alert_parameter[0] <> 1)
                        {
                            $dayfound = $tab_alert_parameter[0] - 1;
                        }
                        else
                        {
                            $dayfound = $tab_alert_parameter[0];
                        }
                    }
                    else
                    {
                        $alert_last_execution_year = $this->what_is_the_year($alert_last_execution_date);
                    }
                    $alert_week_begin = $this->week_begin($alert_last_execution_year, $week_number);
                    echo "\n\n\nweek begin ".$alert_week_begin."\n\n\n";
                    $next_due_date = $this->calculDate($alert_last_execution_date, $alert_frequency, $alert_unit, $dayfound, $alert_week_begin);
                }
                else
                {
                    $alert_week_begin = $this->week_begin($alert_last_execution_year, $week_number);
                    echo "\n\n\nweek begin ".$alert_week_begin."\n\n\n";
                    $next_due_date = $this->calculDate($alert_last_execution_date, $alert_frequency, $alert_unit, $tab_alert_parameter[0], $alert_week_begin);
                }
            }
            else
            {
                $alert_last_execution_year = $this->what_is_the_year($alert_last_execution_date);
                echo "\nnext week(s) after no parameter\n";
                $alert_last_execution_week_number = $alert_last_execution_week_number + $alert_frequency;
                if($alert_last_execution_week_number > 52)
                {
                    $alert_last_execution_year++;
                }
                $week_number = $alert_last_execution_week_number%52;
                if($week_number == 0)
                {
                    $week_number = 52;
                }
                $alert_week_begin = $this->week_begin($alert_last_execution_year, $week_number);
                $next_due_date = $this->calculDate($alert_last_execution_date, $alert_frequency, $alert_unit, 1, $alert_week_begin);
            }
        }
        elseif($alert_unit == "monthly")
        {
            if($alert_last_execution_month_number == $this->what_is_the_month_number($alert_last_execution_date))
            {
                echo "\nsame month number\n";
                $month_number = $alert_last_execution_month_number;
                $same_month = true;
            }
            else
            {
                echo "\nnext month(s)\n";
                $alert_last_execution_month_number = $alert_last_execution_month_number + $alert_frequency;
                $alert_last_execution_year = $this->what_is_the_year($alert_last_execution_date);
                if($alert_last_execution_month_number > 12)
                {
                    $alert_last_execution_year++;
                }
                $month_number = $alert_last_execution_month_number%12;
                if($month_number = 0)
                {
                    $month_number = 12;
                }
                $same_month = false;
            }
            if($alert_parameter <> "")
            {
                $tab_alert_parameter = array();
                $tab_alert_parameter = explode("#", $alert_parameter);
                $this->show_array($tab_alert_parameter);
                if($same_month)
                {
                    for($count_tab_parameter=0;$count_tab_parameter<count($tab_alert_parameter);$count_tab_parameter++)
                    {
                        echo $tab_alert_parameter[$count_tab_parameter]." >? ".$alert_last_execution_month_day."\n";
                        if($tab_alert_parameter[$count_tab_parameter] > $alert_last_execution_month_day)
                        {
                            if($tab_alert_parameter[$count_tab_parameter] <> 1)
                            {
                                $dayfound = $tab_alert_parameter[$count_tab_parameter] - 1;
                            }
                            else
                            {
                                $dayfound = $tab_alert_parameter[$count_tab_parameter];
                            }
                            echo "\n$dayfound found\n\n";
                            break;
                        }
                    }
                    if($dayfound == "")
                    {
                        echo "\nnext month(s) after day not found\n";
                        $alert_last_execution_month_number = $alert_last_execution_month_number + $alert_frequency;
                        $alert_last_execution_year = $this->what_is_the_year($alert_last_execution_date);
                        if($alert_last_execution_month_number > 12)
                        {
                            $alert_last_execution_year++;
                        }
                        $month_number = $alert_last_execution_month_number%12;
                        if($month_number == 0)
                        {
                            $month_number = 12;
                        }
                        if($tab_alert_parameter[0] <> 1)
                        {
                            $dayfound = $tab_alert_parameter[0] - 1;
                        }
                        else
                        {
                            $dayfound = $tab_alert_parameter[0];
                        }
                    }
                    else
                    {
                        $alert_last_execution_year = $this->what_is_the_year($alert_last_execution_date);
                    }
                    $alert_month_begin = $this->month_begin($alert_last_execution_year, $month_number);
                    echo "\n\n\nmonth begin ".$alert_month_begin."\n\n\n";
                    $next_due_date = $this->calculDate($alert_last_execution_date, $alert_frequency, $alert_unit, $dayfound, $alert_week_begin, $alert_month_begin);
                }
                else
                {
                    $alert_month_begin = $this->month_begin($alert_last_execution_year, $month_number);
                    echo "\n\n\nmonth begin ".$alert_month_begin."\n\n\n";
                    $next_due_date = $this->calculDate($alert_last_execution_date, $alert_frequency, $alert_unit, $tab_alert_parameter[0], $alert_week_begin, $alert_month_begin);
                }
            }
            else
            {
                $alert_last_execution_year = $this->what_is_the_year($alert_last_execution_date);
                echo "\nnext month(s) after no parameter\n";
                $alert_last_execution_month_number = $alert_last_execution_month_number + $alert_frequency;
                if($alert_last_execution_month_number > 12)
                {
                    $alert_last_execution_year++;
                }
                $month_number = $alert_last_execution_month_number%12;
                if($month_number == 0)
                {
                    $month_number = 12;
                }
                $alert_month_begin = $this->month_begin($alert_last_execution_year, $month_number);
                $next_due_date = $this->calculDate($alert_last_execution_date, $alert_frequency, $alert_unit, 1, $alert_week_begin, $alert_month_begin);
            }
        }
        echo "next_due_date : ".$next_due_date."\n";
        return $next_due_date;
    }

    /**
    *  Allow to add a date with an int
    *
    * @param date $Date
    * @param int $alert_frequency
    * @param var $alert_unit
    */
    public function calculDate($date1, $alert_frequency, $alert_unit, $dayfound = "", $alert_week_begin = "", $alert_month_begin = "")
    {
        echo "\n\ncalculDate : \n";
        echo "date1 : ".$date1."\n";
        echo "alert_frequency : ".$alert_frequency."\n";
        echo "alert_unit : ".$alert_unit."\n";
        echo "dayfound : ".$dayfound."\n";
        echo "alert_week_begin : ".$alert_week_begin."\n";
        $date1 = strtotime($date1);
        if($alert_unit == "hourly")
        {
            $factor = 3600;
        }
        if($alert_unit == "daily")
        {
            $factor = 86400;
        }
        if($alert_unit == "weekly")
        {
            if($dayfound <> "" && $alert_week_begin <> "")
            {
                $factor = 86400;
                $alert_week_begin = strtotime($alert_week_begin);
                if($dayfound == 1)
                {
                    //on week begining
                    echo "calcul : ".$alert_week_begin."\n";
                    $result = date('d-m-Y H:i', $alert_week_begin);
                }
                else
                {
                    echo "calcul : ".$alert_week_begin." + "."(".$factor." * ".$dayfound.")\n";
                    $result = date('d-m-Y H:i', $alert_week_begin + ($factor*$dayfound));
                }
                return $result;
            }
        }
        if($alert_unit == "monthly")
        {
            if($dayfound <> "" && $alert_month_begin <> "")
            {
                $factor = 86400;
                $alert_month_begin = strtotime($alert_month_begin);
                if($dayfound == 1)
                {
                    //on month begining
                    echo "calcul : ".$alert_month_begin."\n";
                    $result = date('d-m-Y H:i', $alert_month_begin);
                }
                else
                {
                    echo "calcul : ".$alert_month_begin." + "."(".$factor." * ".$dayfound.")\n";
                    $result = date('d-m-Y H:i', $alert_month_begin + ($factor*$dayfound));
                }
                return $result;
            }
        }
        echo "calcul : ".$date1." + "."(".$factor." * ".$alert_frequency.")\n";
        $result = date('d-m-Y H:i', $date1 + ($factor*$alert_frequency));
        return $result;
    }

    /**
    * update_alert_done
    */
    public function update_alert_done($alert_id, $last_sequence)
    {
        $this->connect();
        $this->query("update ".$_SESSION['tablename']['al_alert_insts']." set status = 'DONE' where alert_id = ".$alert_id." and sequence = ".$last_sequence);
        //$this->show();
    }

    /**
    * Return the week number of a date
    */
    public function what_is_the_week_number($date)
    {
        return date("W", strtotime($date));
    }

    /**
    * Return the month number of a date
    */
    public function what_is_the_month_number($date)
    {
        return date("m", strtotime($date));
    }

    /**
    * Return the month number of a date
    */
    public function what_is_the_month_day($date)
    {
        return date("d", strtotime($date));
    }

    /**
    * Return the week day of a date
    */
    public function what_is_the_week_day($date)
    {
        echo "\nwhat_is_the_week_day date init : ".$date."\n";
        echo "what_is_the_week_day strtotime date init : ".strtotime($date)."\n";
        echo "what_is_the_week_day return : ".date("N", strtotime($date))."\n";
        return date("N", strtotime($date));
    }

    /**
    * Return the year of a date
    */
    public function what_is_the_year($date)
    {
        return date("Y", strtotime($date));
    }

    /**
    * Return the week begin
    */
    function week_begin($year, $week_number)
    {
        echo "\nyear : ".$year;
        echo "\nweek_number : ".$week_number;
        //timestamp of first monday of a year
        $first_monday = strtotime('first monday', mktime(0, 0, 0, 1, 1, $year));
        //week number of the first monday
        $week_first_monday = date('W', $first_monday);
        $the_monday = ($week_first_monday == 1) ?
                    $first_monday :
                    strtotime('last monday', $first_monday);
        return date('d-m-Y', strtotime('+' . ($week_number - 1) . ' week', $the_monday));
    }

    /**
    * Return the month begin
    */
    function month_begin($year, $month_number)
    {
        echo "\nyear : ".$year;
        echo "\nmonth_number : ".$month_number;
        return date('d-m-Y', strtotime("01-".$month_number."-".$year));
    }

    /**
    * Return true if alert due date was lower than now date
    */
    public function control_alert_due_date_lower_than_now($due_date)
    {
        //echo "\ncontrol_alert_due_date_lower_than_now due_date : $due_date, now : ".date("d-m-Y")."\n";
        $comp_date = $this->compare_date($due_date, date("d-m-Y"));
        //echo "comp_date : $comp_date\n";
        if($comp_date == "date2" || $comp_date == "equal")
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
    * Return true if alert end date was bigger than now date
    */
    public function control_alert_end_date_bigger_than_now($alert_end_date,$alert_id)
    {
        $comp_date = $this->compare_date($alert_end_date, date("d-m-Y"));
        if($comp_date == "date2" && $comp_date <> "equal")
        {
            $_SESSION['error'] .= _END_DATE_MUST_BE_BIGGER_THAN_NOW." ".$alert_end_date."\n";
            $this->close_alert($alert_id);
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
    * Return true if due date was bigger than alert end date
    */
    public function control_due_date_bigger_than_alert_end_date($due_date, $alert_end_date, $alert_id)
    {
        $comp_date = $this->compare_date($alert_end_date, $due_date);
        if($comp_date == "date2" && $comp_date <> "equal")
        {
            $this->close_alert($alert_id);
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
    * close an alert
    */
    public function close_alert($alert_id)
    {
        echo "\nclosing alert\n";
        $this->connect();
        $this->query("update ".$_SESSION['tablename']['al_alerts']." set alert_status = 'CLOSE' where alert_id = ".$alert_id);
        //passed all insts alerts to done
        $this->query("update ".$_SESSION['tablename']['al_alert_insts']." set status = 'DONE' where alert_id = ".$alert_id);
    }

    /**
    * done an alert inst
    */
    public function done_alert_inst($alert_id, $sequence)
    {
        $this->connect();
        $this->query("update ".$_SESSION['tablename']['al_alert_insts']." set status = 'DONE' where alert_id = ".$alert_id)." and sequence = ".$sequence;
    }

    /**
    * Return the due date for the document
    */
    public function due_date($wf_id, $id)
    {
        $connexion = new dbquery();
        $connexion->connect();
        $connexion2 = new dbquery();
        $connexion2->connect();
        $connexion->query("select date(due_date) as due_date from ".$_SESSION['tablename']['wf_insts']." where wf_id = '".$wf_id."' and res_id = ".$id." and status = 'WAIT'");
        //$connexion->show();
        $wf_definitions = $connexion->fetch_object();
        $due_date = $wf_definitions->due_date;
        return $due_date;
    }

    /**
    * Insert a record in wf_insts
    */
    public function insert_insts($wf_id, $res_id, $task_id, $user_id, $group_id, $type_insts)
    {
        $connexion= new dbquery();
        $connexion->connect();
        if($type_insts == "new")
        {
            //calcul the new sequence first
            $connexion->query("select count(*) as seq from ".$_SESSION['tablename']['wf_insts']." where wf_id = '".$wf_id."' and res_id = ".$res_id);
            $result = $connexion->fetch_object();
            if($result->seq <> "")
            {
                $sequence = $result->seq + 1;
            }
            else
            {
                $sequence = 1;
            }
            $task_delay = $this->task_delay($wf_id, $task_id);
            //echo "task_delay : ".$task_delay."\n";
            $due_date = $this->next_due_date_calculation($task_delay);
            $connexion->query("insert into ".$_SESSION['tablename']['wf_insts']." (WF_ID, RES_ID, SEQUENCE, TASK_ID, USER_ID, GROUP_ID, BEGIN_DATE, INST_DATE, DUE_DATE
                              , ACTUAL_USER_ID, STATUS)
                               values('".$wf_id."', ".$res_id.", ".$sequence.", '".$task_id."', '".$user_id."', '".$group_id."'
                                      , '".strftime("%Y")."-".strftime("%m")."-".strftime("%d")."', '".strftime("%Y")."-".strftime("%m")."-".strftime("%d")."'
                                      , '".$due_date."', '".$_SESSION['user']['UserId']."', 'DONE')");
        }
        elseif($type_insts == "up")
        {
            $connexion->query("update ".$_SESSION['tablename']['wf_insts']." set INST_DATE = '".strftime("%Y")."-".strftime("%m")."-".strftime("%d")."'
                              , ACTUAL_USER_ID = '".$_SESSION['user']['UserId']."', STATUS = 'DONE'
                              where WF_ID = '".$wf_id."' and RES_ID = ".$res_id." and TASK_ID = '".$task_id."'");
        }
        elseif($type_insts == "next")
        {
            //calcul the new sequence first
            $connexion->query("select count(*) as seq from ".$_SESSION['tablename']['wf_insts']." where wf_id = '".$wf_id."' and res_id = ".$res_id);
            $result = $connexion->fetch_object();
            if($result->seq <> "")
            {
                $sequence = $result->seq + 1;
            }
            else
            {
                $sequence = 1;
            }
            $task_delay = $this->task_delay($wf_id, $task_id);
            $due_date = $this->next_due_date_calculation($task_delay);
            $connexion->query("insert into ".$_SESSION['tablename']['wf_insts']." (WF_ID, RES_ID, SEQUENCE, TASK_ID, USER_ID, GROUP_ID, BEGIN_DATE, DUE_DATE, STATUS)
                               values('".$wf_id."', ".$res_id.", ".$sequence.", '".$task_id."', '".$user_id."', '".$group_id."'
                                      , '".strftime("%Y")."-".strftime("%m")."-".strftime("%d")."', '".$due_date."', 'WAIT')");
        }
    }

    /**
    * Change the res status if demand
    */
    public function change_res_status($wf_id, $event_id, $task_id, $res_id)
    {
        $connexion= new dbquery();
        $connexion->connect();
        $db= new dbquery();
        $db->connect();
        $connexion->query("select * from ".$_SESSION['tablename']['wf_tasks']." where wf_id = '".$wf_id."' and task_id = '".$task_id."'");
        $event = $connexion->fetch_object();
        $table = $event->res_table;
        $connexion->query("select * from ".$_SESSION['tablename']['wf_events']." where (wf_id = '".$wf_id."' and task_id = '".$task_id."' and event_id = '".$event_id."') and (res_status is not null and res_status <> '')");
        while($event = $connexion->fetch_object())
        {
            $db->query("update ".$table." set status = '".$event->res_status."' where res_id = ".$res_id);
        }
    }

    /**
    * Load mail from actors
    */
    public function load_mail_from_actors($actors)
    {
        $connexion = new dbquery();
        $connexion->connect();
        $connexion2 = new dbquery();
        $connexion2->connect();
        $mail_users = array();
        $list_users = array();
        $list_groups = array();
        if($actors <> "")
        {
            for($i=0;$i<count($actors);$i++)
            {
                for($j=0;$j<count($actors[$i]['USERS']);$j++)
                {
                    $list_users[$j] = $actors[$i]['USERS'][$j];
                }
                for($j=0;$j<count($actors[$i]['GROUPS']);$j++)
                {
                    $list_groups[$j] = $actors[$i]['GROUPS'][$j];
                }
            }
            for($i=0;$i<count($list_users);$i++)
            {
                $connexion->query("select mail from ".$_SESSION['tablename']['users']." where user_id = '".$list_users[$i]."'");
                $res = $connexion->fetch_object();
                array_push($mail_users, array('MAIL' => $res->mail));
            }
            for($i=0;$i<count($list_groups);$i++)
            {
                $connexion->query("select user_id from ".$_SESSION['tablename']['usergroup_content']." where GROUP_ID = '".$list_groups[$i]."'");
                while($res = $connexion->fetch_object())
                {
                    $connexion2->query("select mail from ".$_SESSION['tablename']['users']." where user_id = '".$res->user_id."'");
                    $res2 = $connexion2->fetch_object();
                    array_push($mail_users, array('MAIL' => $res2->mail));
                }
            }
            return $mail_users;
        }
        else
        {
            //echo "No actors defined for this task ! please, verify the configuration of the workflow.";
            $_SESSION['error'] .= "No actors defined for this task ! please, verify the configuration of the workflow.\n";
            //exit();
        }
    }

    /**
    * Load user infos from actors
    */
    public function load_user_infos_from_actors($actors)
    {
        $connexion = new dbquery();
        $connexion->connect();
        $connexion2 = new dbquery();
        $connexion2->connect();
        $mail_users = array();
        $list_users = array();
        $list_groups = array();
        for($i=0;$i<count($actors);$i++)
        {
            for($j=0;$j<count($actors[$i]['USERS']);$j++)
            {
                $list_users[$j] = $actors[$i]['USERS'][$j];
            }
            for($j=0;$j<count($actors[$i]['GROUPS']);$j++)
            {
                $list_groups[$j] = $actors[$i]['GROUPS'][$j];
            }
        }
        for($i=0;$i<count($list_users);$i++)
        {
            $connexion->query("select * from ".$_SESSION['tablename']['users']." where USER_ID = '".$list_users[$i]."'");
            $res = $connexion->fetch_object();
            array_push($mail_users, array('USER_ID' => $res->user_id, 'MAIL' => $res->mail, 'LASTNAME' => $res->lastname, 'FIRSTNAME' => $res->firstname, 'DEPARTMENT' => $res->department));
        }
        for($i=0;$i<count($list_groups);$i++)
        {
            $connexion->query("select USER_ID from ".$_SESSION['tablename']['usergroupcontent']." where GROUP_ID = '".$list_groups[$i]."'");
            while($res = $connexion->fetch_object())
            {
                $connexion2->query("select * from ".$_SESSION['tablename']['users']." where USER_ID = '".$res->USER_ID."'");
                $res2 = $connexion2->fetch_object();
                array_push($mail_users, array('USER_ID' => $res2->user_id, 'MAIL' => $res2->mail, 'LASTNAME' => $res2->lastname, 'FIRSTNAME' => $res2->firstname, 'DEPARTMENT' => $res2->department));
            }
        }
        return $mail_users;
    }

    /**
    *  Allow to know information about an user
    *
    */
    public function retrieve_info_of_user($user_id)
    {
        $user = array();
        $this->connect();
        $this->query("select * from ".$_SESSION['tablename']['users']." where user_id = '".$user_id."'");
        //$this->show();
        $res = $this->fetch_object();
        array_push($user, array('user_id' => $res->user_id, 'mail' => $res->mail, 'lastname' => $res->lastname, 'firstname' => $res->firstname, 'department' => $res->department));
        return $user;
    }

    /**
    *  Allow to know information about alert type
    *
    */
    public function retrieve_info_on_alert_type($alert_type)
    {
        for($cpt_type=0;$cpt_type<count($_SESSION['alert_types']);$cpt_type++)
        {
            if($_SESSION['alert_types'][$cpt_type]['id'] == $alert_type)
            {
                return $_SESSION['alert_types'][$cpt_type];
            }
        }
    }

    /**
    * Given the coll id, return the info of the collection
    *
    * @param string $coll_id  Collection identifier
    */
    public function retrieve_info_from_coll_id($coll_id, $info)
    {
        for($i=0; $i<count($_SESSION['collections']);$i++)
        {
            if($_SESSION['collections'][$i]['id'] == $coll_id)
            {
                return $_SESSION['collections'][$i][$info];
            }
        }
        return '';
    }

    /**
    *  Allow to know when the next Easter come
    *
    * @param date $year
    */
    public function WhenEasterCelebrates($year = null)
    {
        if (is_null($year))
        {
            $year = (int)date ('Y');
        }
        $iN = $year - 1900;
        $iA = $iN%19;
        $iB = floor (((7*$iA)+1)/19);
        $iC = ((11*$iA)-$iB+4)%29;
        $iD = floor ($iN/4);
        $iE = ($iN-$iC+$iD+31)%7;
        $time = 25-$iC-$iE;
        if($time > 0)
        {
            $WhenEasterCelebrates = strtotime ($year.'/04/'.$time);
        }
        else
        {
            $WhenEasterCelebrates = strtotime ($year.'/03/'.(31+$time));
        }
        return $WhenEasterCelebrates;
    }

    /**
    *  Allow to know when the next open day come
    *
    * @param date $Date in timestamp format
    * @param int $Delta
    * @param boolean $isMinus
    */
    public function WhenOpenDay($Date, $Delta, $isMinus = false)
    {
        $Hollidays = array (
            '1_1',
            '1_5',
            '8_5',
            '14_7',
            '15_8',
            '1_11',
            '11_11',
            '25_12'
        );
        $this->connect();
        $this->query("select * from parameters where id like 'alert_stop%'");
        while ($result = $this->fetch_object()) {
            if ($result->param_value_date <> '') {
                $compare = $this->compare_date($result->param_value_date, date("d-m-Y"));
                //take the alert stop only if > now
                if ($compare == 'date1' || $compare == 'equal') {
                    $dateExploded = explode("-", str_replace(" 00:00:00", "", $result->param_value_date));
                    array_push($Hollidays, (int)$dateExploded[2] . "_" . (int)$dateExploded[1]);
                }
            }
        }
        //var_dump($Hollidays);
        
        if (function_exists ('easter_date')) {
            $WhenEasterCelebrates = easter_date ((int)date('Y'), $Date);
        } else {
            $WhenEasterCelebrates = $this->getEaster ((int)date('Y'), $Date);
        }
        $Hollidays[] = date ('j_n', $WhenEasterCelebrates);
        $Hollidays[] = date ('j_n', $WhenEasterCelebrates + (86400*39));
        $Hollidays[] = date ('j_n', $WhenEasterCelebrates + (86400*49));
        $iEnd = $Delta * 86400;

        $i = 0;
        while ($i < $iEnd) {
            $i = strtotime ('+1 day', $i);
            if ($isMinus) {
                if (in_array(
                    date('w', $Date-$i),array (0,6)
                ) || in_array (date ('j_n', $Date-$i), $Hollidays)
                ) {
                    $iEnd = strtotime ('+1 day', $iEnd);
                    $Delta ++;
                }
            } else {
                if (
                    in_array(
                        date('w', $Date+$i),array (0,6)
                    ) || in_array (date ('j_n', $Date+$i), $Hollidays)
                ) {
                    $iEnd = strtotime ('+1 day', $iEnd);
                    $Delta ++;
                }
            }
        }
        if ($isMinus) {
            return date('Y-m-d', $Date + (86400*-$Delta));
        } else {
            return date('Y-m-d', $Date + (86400*$Delta));
        }
    }

    /**
    *  Allow to know the next date to treat
    *
    * @param int $delay Delay in days
    * @param boolean $isMinus true if minus
    */
    public function date_max_treatment($delay, $isMinus = false)
    {
        $result = $this->WhenOpenDay(
             strtotime (strftime("%Y")."-".strftime("%m")."-".strftime("%d")), 
            $delay, 
            $isMinus
        );
        return $result;
    }
    
    /**
    *  Allow to know the next date to treat
    *
    * @param int $delay Delay in days
    * @param boolean $isMinus true if minus
    */
    public function processDelayDate($date, $delay, $isMinus = false)
    {
        $date = date('Y-m-d', $date);
        $result = $this->WhenOpenDay(
            $date, 
            $delay, 
            $isMinus
        );
        return $result;
    }
    
    function dateFR2Time($date)
    {
        list($day, $month, $year) = explode('/', $date);
        $timestamp = mktime(0, 0, 0, $month, $day, $year);
        return $timestamp;
    }
}

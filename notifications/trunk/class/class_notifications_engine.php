<?php 
/**
* modules tools Class for physical archives
*
*  Contains all the functions to  modules tables for physical archives
*
* @package  maarch Letterbox v3
* @version 3.0
* @since 10/2005
* @license GPL v3
* @author  Loic Vinet  <dev@maarch.org>
* 
*/

class notification_engine extends request
{
    
    public function add_DatesAlarm($res_array)
    {
        //print_r($res_array); exit();
        if (!$res_array) return null;
         
        foreach($res_array as $res)
        {
            $res_id = $res['res_id'];
            $this->connect();
            $this->query("SELECT res_id, type_id, process_limit_date, alarm1_date, alarm2_date FROM ".$_SESSION['ressources']['letterbox_view']." where res_id = '".$res_id."'");
            
            $result = $this -> fetch_object(); 
            
            $process_limit_date = $result->process_limit_date;
            $alarm1_date = $result->alarm1_date;
            $alarm2_date = $result->alarm2_date;
            $type_id = $result->type_id;
            
            if ($process_limit_date <> '' && $alarm1_date == '' && $alarm2_date == '')
            { 
                //on récupère la typologie documentaire
                $query = "SELECT delay1, delay2 from ".$_SESSION['tablename']['mlb_doctype_ext']." where type_id = '".$type_id."' "; 
                //$query = "SELECT delay1, delay2 from mlb_doctype_ext where type_id = '".$type_id."' "; 
                //Recuperation du délai de relance pour le type de document.
            
                $this->query($query);
                if ($this -> nb_result() > 0){
                    $result2 = $this->fetch_object();
                    $delay1 = $result2 -> delay1;
                    $delay2 = $result2 -> delay2;
                    
                    //Def de Alarm1
                    $date_alarm1_final = $this->WhenOpenDay($process_limit_date, $delay1, '-');
                    //Def de Alarm2
                    $date_alarm2_final = $this->WhenOpenDay($process_limit_date, $delay2);
                    
                    //On créer les date appropriées.
                    $final_query = "UPDATE ".$_SESSION['collection'][0]['extensions'][0]." set alarm1_date='".$date_alarm1_final."', alarm2_date='".$date_alarm2_final."' where res_id = '".$res_id."'";
                    $this->query($final_query);
                    //$this->show();
                }
            }
        }
        
        //Récupération de la date limite de traitement  
        //Pour chaque document
            //On ajoute alarm1 et alarm2 en base de données
    }
    
    public function WhenOpenDay($Date, $Delta, $operand = '+')
    {
        $Date = strtotime ($Date);
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
        if (function_exists('easter_date')) {
            $WhenEasterCelebrates = easter_date((int)date('Y'), $Date);
        } else {
            $WhenEasterCelebrates = getEaster ((int)date('Y'), $Date);
        }
        $Hollidays[] = date ('j_n', $WhenEasterCelebrates);
        $Hollidays[] = date ('j_n', $WhenEasterCelebrates + (86400*39));
        $Hollidays[] = date ('j_n', $WhenEasterCelebrates + (86400*49));
        $iEnd = $Delta * 86400;
        $i = 0;
        if ($operand == '+') {
            while ($i < $iEnd) {
                $i = strtotime('+1 day', $i);
                if (
                    in_array(date('N', $Date + $i), array(0, 4)) 
                    || in_array(date('j_n', $Date + $i), $Hollidays)
                ) {
                    echo date('d', $Date + $i) . ' ' . date('w', $Date + $i) . "\r\n";
                    $iEnd = strtotime('+1 day', $iEnd);
                    $Delta++;
                }
            }
            return date('Y-m-d', $Date + (86400 * $Delta));
        } else {
            while ($i < $iEnd) {
                $i = strtotime ('+1 day', $i);
                if (
                    in_array(date('N', $Date - $i), array(0, 4)) 
                    || in_array(date('j_n', $Date - $i), $Hollidays)
                ) {
                    echo date('d', $Date - $i) . ' ' . date('w', $Date - $i) . "\r\n";
                    $iEnd = strtotime('-1 day', $iEnd);
                    $Delta++;
                }
            }
            return date('Y-m-d', $Date - (86400 * $Delta));
        }
    }
    
    
    function execute_engine($working_template,$my_docs, $user)
    //Replace all arguments in Html template by differents value loaded in load_var_sys library
    //@args $workin_template : array : exploded template
    //@args $my_docs : array : list of documents to process with all properties
    {
        //print_r($working_template);
        //For the head...
        $head = $working_template['head'];
        $true_head = $head;
        preg_match_all('/##(.*?)##/', $true_head, $out);
        $theline = '';
        $content_list = '';
        for($i=0;$i<count($out[0]);$i++)
        {
            $remplacement_head = $this->load_var_sys($out[1][$i], $theline, $user);
            $true_head = str_replace($out[0][$i],$remplacement_head,$true_head);
        }
        $head = $true_head;
        
        //For the list--content
        for($li = 0; $li < count($my_docs) ; $li++)
        {
                
            $list = $working_template['list'];
            $true_content=$list;


            preg_match_all('/##(.*?)##/', $true_content, $out);



            for($i=0;$i<count($out[0]);$i++)
            {
                //appel des fonctions de remplacement;
                $remplacement = $this->load_var_sys($out[1][$i], $my_docs[$li], $user);
                //echo $out[1][$i]." ".$remplacement;
                $true_content = str_replace($out[0][$i],$remplacement,$true_content);

            }
            $content_list .= $true_content;
        }
    

        //For the Footer
        
        
        
        $footer = $working_template['footer'];
        $true_footer = $footer;
        
        preg_match_all('/##(.*?)##/', $true_footer, $out);

        for($i=0;$i<count($out[0]);$i++)
        {
            $remplacement_footer = $this->load_var_sys($out[1][$i],$the_line, $user);
            $true_footer = str_replace($out[0][$i],$remplacement_footer,$true_footer);
        }
        
        $footer = $true_footer;
        
        
        $mail_trait = $head.$content_list.$footer;
        //replace definition un the template by constants
        $mail_trait = preg_replace_callback('/\{\{(_[A-Z_]*)\}\}/',create_function('$match', 'return constant($match[1]);'),$mail_trait);   
        
        
        //Reset the content_list for next instance!
        unset($content_list);
        return $mail_trait;     
    }


    function build_users_list()
    //Build list of all enabled users 
    //Launch an specific case if user is 'abs'
    {
        
        $db2 = new dbquery();
        $db2->connect();
        
        
        $this->connect();
        $this->query("SELECT DISTINCT user_id, mail, lastname, firstname FROM ".$_SESSION['tablename']['users']." where enabled = 'Y'");
        
        $users  = array();
        while($res = $this->fetch_object())
        {
            //Load entities allowed for this user
            $ent = new entity();
            $allowed_entities = array();
            //$allowed_entities = $ent->get_entities_of_user($res->user_id);
        
            //Check for abs user
            $db2->query("select u.mail, ua.new_user from ".$_SESSION['tablename']['users']." u, ".$_SESSION['tablename']['user_abs']." ua where u.user_id = ua.new_user and ua.user_abs = '".htmlentities($res->user_id)."'");
                        
            if($db2->nb_result() < 1)
            {
                //No abs users
                array_push($users, array( 'ID' => htmlentities(htmlentities($res->user_id)), 'MAIL' => htmlentities($res->mail), 'NOM' => htmlentities($res->lastname), 'PRENOM' => htmlentities($res->firstname),'IS_ABS' => false, 'ENTITIES' => $allowed_entities));
                //array_push($users, array( 'ID' => htmlentities(htmlentities($res->user_id)), 'MAIL' => htmlentities($res->mail), 'NOM' => htmlentities($res->lastname), 'PRENOM' => htmlentities($res->firstname),'IS_ABS' => false));
            }
            else
            {
                //Abs users is true, add column mail_to_send and user_id_to_send
                $res2 = $db2->fetch_object();
                array_push($users, array( 'ID' => htmlentities($res->user_id), 'MAIL' => htmlentities($res->mail), 'NOM' => htmlentities($res->lastname), 'PRENOM' => htmlentities($res->firstname), 'IS_ABS' => true, 'MAIL_TO_SEND' => htmlentities($res2->mail), 'USER_ID_TO_SEND' => htmlentities($res2->new_user), 'ENTITIES' => $allowed_entities));
            }
        }
        if(isset($_SESSION['debug']['hide_console']) && $_SESSION['debug']['hide_console'] == "false")
        {
            print_r($users);
        }
        
        return $users;
}
    
    
    function utf8_maarch($value)
    //Send an corrected for utf8 convertion if necessary 
    //Remove random slashes in this string
    {
        $value = str_replace('\\','',$value);
        $default_value= $value;
        $encode_value = utf8_decode($value);
        
        if (strstr($encode_value, '?') <> false)
        {
            return $default_value;
        }
        else
        {
            return $encode_value;
        }
    
    }


    function get_ressource_for_process($user)
    //Retrive compete list of document fo actual user in processing
    {
            
        $result = array();  
        $this->connect();   
        $this->query("select l.res_id  from ".$_SESSION['ressources']['letterbox_view']." r, ".$_SESSION['tablename']['listinstance']." l  where r.res_id=l.res_id and l.item_id='".$user['ID']."'  and item_type = 'user_id' and  r.flag_notif = 'N' and (r.status = 'NEW' or r.status = 'COU') and l.item_mode = 'dest' and item_type='user_id'");
        if($this->nb_result() > 0)
        {
        
            $result = array();
            while ($res=$this->fetch_object() )
            {
                array_push($result, $res->res_id);
            }
            
            return $result;
        
        }
        else
        {
            return false;
        }
                    
    }

    function get_ressource_for_copy($user)
    //Retrive compete list of document for copy user
    {
            
        //Tranform Entity Sub Array in a string 
        $visible_entities = '';
        if (count($user['ENTITIES']) == 0)
        {
            $visible_entities = "'1=-1' ,";
        }
        foreach ($user['ENTITIES'] as $user_ent)
        {
            $visible_entities .= "'".$user_ent['ID']."', ";
        }   
            
        $visible_entities = substr($visible_entities, 0, -2);   

        $result = array();  
        $this->connect();   
        //$this->query("select l.res_id  from ".$_SESSION['ressources']['letterbox_view']." r, ".$_SESSION['tablename']['listinstance']." l  where r.res_id=l.res_id and l.item_id='".$user['ID']."'  and item_type = 'user_id' and  r.flag_notif = 'N' and (r.status = 'NEW' or r.status = 'COU') and l.item_mode = 'cc' and item_type='user_id';");
        $this->query("select distinct l.res_id  from ".$_SESSION['ressources']['letterbox_view']." r, ".$_SESSION['tablename']['listinstance']." l  where r.res_id=l.res_id  and  r.flag_notif = 'N' and (r.status = 'NEW' or r.status = 'COU') and l.item_mode = 'cc' and ((l.item_id='".$user['ID']."'  and item_type = 'user_id') or (l.item_type = 'entity_id' and item_id in (".$visible_entities.") ))");
        if($this->nb_result() > 0)
        {
        
            $result = array();
            while ($res=$this->fetch_object() )
            {
                array_push($result, $res->res_id);
            }
            
            return $result;
        
        }
        else
        {
            return false;
        }
                    
    }

    function get_ressource_for_alarm1($user)
    //Retrive compete list of document for actual user when date_alarm1 is today
    {
            
        $result = array();  
        $this->connect();   
        $this->query("select l.res_id  from ".$_SESSION['ressources']['letterbox_view']." r, ".$_SESSION['tablename']['listinstance']." l  where r.res_id=l.res_id and l.item_id='".$user['ID']."'  and item_type = 'user_id' and  r.flag_alarm1 = 'N' and (r.status = 'NEW' or r.status = 'COU') and date(r.alarm1_date) =date(now()) and l.item_mode = 'dest' and item_type='user_id'");
        if($this->nb_result() > 0)
        {
        
            $result = array();
            while ($res=$this->fetch_object() )
            {
                array_push($result, $res->res_id);
            }
            
            return $result;
        
        }
        else
        {
            return false;
        }
                    
    }

    function get_ressource_for_alarm1_copy($user)
    //Retrive compete list of document for copy  when date_alarm1 is today
    {
            
        //Tranform Entity Sub Array in a string 
        $visible_entities = '';
        if (count($user['ENTITIES']) == 0)
        {
            $visible_entities = "'1=-1' ,";
        }
        foreach ($user['ENTITIES'] as $user_ent)
        {
            $visible_entities .= "'".$user_ent['ID']."', ";
        }   
            
        $visible_entities = substr($visible_entities, 0, -2);   

        $result = array();  
        $this->connect();   
        $this->query("select distinct l.res_id  from ".$_SESSION['ressources']['letterbox_view']." r, ".$_SESSION['tablename']['listinstance']." l  where r.res_id=l.res_id  and  r.flag_alarm1 = 'N'and date(r.alarm1_date) =date(now())  and (r.status = 'NEW' or r.status = 'COU') and l.item_mode = 'cc' and ((l.item_id='".$user['ID']."'  and item_type = 'user_id') or (l.item_type = 'entity_id' and item_id in (".$visible_entities.") ))");
        if($this->nb_result() > 0)
        {
        
            $result = array();
            while ($res=$this->fetch_object() )
            {
                array_push($result, $res->res_id);
            }
            
            return $result;
        
        }
        else
        {
            return false;
        }
                    
    }


    function get_ressource_for_alarm2($user)
    //Retrive compete list of document for actual user  when date_alarm2 is today
    {
            
        $result = array();  
        $this->connect();   
        $this->query("select l.res_id  from ".$_SESSION['ressources']['letterbox_view']." r, ".$_SESSION['tablename']['listinstance']." l  where r.res_id=l.res_id and l.item_id='".$user['ID']."'  and item_type = 'user_id' and  r.flag_alarm2 = 'N' and (r.status = 'NEW' or r.status = 'COU') and date(r.alarm2_date) =date(now()) and l.item_mode = 'dest' and item_type='user_id'");
        if($this->nb_result() > 0)
        {
        
            $result = array();
            while ($res=$this->fetch_object() )
            {
                array_push($result, $res->res_id);
            }
            
            return $result;
        
        }
        else
        {
            return false;
        }
                    
    }

    function get_ressource_for_alarm2_copy($user)
    //Retrive compete list of document fo actual user in processing
    {
            
        //Tranform Entity Sub Array in a string 
        $visible_entities = '';
        if (count($user['ENTITIES']) == 0)
        {
            $visible_entities = "'1=-1' ,";
        }
        foreach ($user['ENTITIES'] as $user_ent)
        {
            $visible_entities .= "'".$user_ent['ID']."', ";
        }   
            
        $visible_entities = substr($visible_entities, 0, -2);   

        $result = array();  
        $this->connect();   
        $this->query("select distinct l.res_id  from ".$_SESSION['ressources']['letterbox_view']." r, ".$_SESSION['tablename']['listinstance']." l  where r.res_id=l.res_id  and  r.flag_alarm2 = 'N'and date(r.alarm2_date) =date(now())  and (r.status = 'NEW' or r.status = 'COU') and l.item_mode = 'cc' and ((l.item_id='".$user['ID']."'  and item_type = 'user_id') or (l.item_type = 'entity_id' and item_id in (".$visible_entities.") ))");
        if($this->nb_result() > 0)
        {
        
            $result = array();
            while ($res=$this->fetch_object() )
            {
                array_push($result, $res->res_id);
            }
            
            return $result;
        
        }
        else
        {
            return false;
        }
                    
    }


    //Get template and remove all comments
    public function get_template($this_file)
    {
            
            //Ouverture du fichier
            $list_trait = file_get_contents($this_file);
            //Suppression des commantaires dans la page
            $list_trait = preg_replace("/(<!--.*?-->)/s","", $list_trait);

            return $list_trait;

    }
    
    
    function decode_template($template)
    {
        $tmp = explode("#!#", $template);
        
        
        //Exploding template to lunch funtion in load_var_sys()
        $tab = array();
        $tab['head'] = '';
        $tab['list'] = '';
        $tab['footer'] = '';
        foreach($tmp as $ac_tmp)
        {
            
            if (substr($ac_tmp , 0, 4) == "HEAD")
            {
                $tab['head'] = substr($ac_tmp, 4);
            }
            elseif (substr($ac_tmp , 0, 4) == "LIST")
            {
                $tab['list'] = substr($ac_tmp, 4);
            }
            elseif (substr($ac_tmp , 0, 6) == "FOOTER")
            {
                $tab['footer'] = substr($ac_tmp, 6);
            }
        }
        
        return $tab;
        
    }
    
    function get_contact_information($res_id, $category_id)
    //Get contact full information for each case: incoming document or outgoing document
    {
        $stopthis=false;
        $column_title = false;
        $column_value = false;
        $column_join = false;   
            
            
        $this->connect();
        //For this 3 cases, we need to create a different string
        if ($category_id == 'incoming')
        {
            
            $prefix = _TO;
            $this->query("SELECT exp_user_id, exp_contact_id from ".$_SESSION['ressources']['letterbox_view']." WHERE res_id = ".$res_id);
            
            $compar = $this->fetch_object();
            
            
            if ($compar->exp_user_id <> '')
            {
            
                $column_title = "user_id";
                $column_value = $compar->exp_user_id;
                $column_join = $_SESSION['tablename']['users'];
            }
            elseif ($compar->exp_contact_id <> '')
            {
            
                $column_title = "contact_id";
                $column_value = $compar->exp_contact_id;
                $column_join = $_SESSION['tablename']['contacts'];
            }
            else
            {
                $stopthis = true;
            }
        }
        elseif ($category_id == 'outgoing'  || $category_id == 'internal')
        {
                $prefix = _FOR;
                
                $this->query("SELECT dest_user_id, dest_contact_id from ".$_SESSION['ressources']['letterbox_view']." WHERE res_id = ".$res_id);
                
                $compar = $this->fetch_object();
                if ($compar->dest_user_id <> '')
                {
                    
                    $column_title = "user_id";
                    $column_value = $compar->dest_user_id;
                    $column_join = $_SESSION['tablename']['users'];
                }
                elseif ($compar->dest_contact_id <> '')
                {
                    
                    $column_title = "contact_id";
                    $column_value = $compar->dest_contact_id;
                    $column_join = $_SESSION['tablename']['contacts'];
                }
                else
                {
                    $stopthis = true;
                }
        }
        else
        {
             $stopthis = true;
        }
        if($stopthis == true)
        {
            return false;
        }
        //If we need to find a contact, get the society first
        if ($column_join == $_SESSION['tablename']['contacts'])
            $fields = 'c.firstname, c.lastname, c.society ';
        elseif ($column_join == $_SESSION['tablename']['users'])
            $fields = 'c.firstname, c.lastname';
        else
            $fields = '';
            
        //Launching request to restore full contact string
        $this->query("SELECT ".$fields." from ".$column_join." c    where ".$column_title." = '".$column_value."'");
        
        $final = $this->fetch_object();         
                                
        $firstname = $this->utf8_maarch($final->firstname);                         
        $lastname = $this->utf8_maarch($final->lastname);                           
        if ($final->society <> '')
            $society = " (".$final->society.") ";   
        else
            $society = "";
        
        $the_contact =$prefix." ".$firstname." ".$lastname." ".$society;    
        return $the_contact;
        
    }



    //Restore information for this ressource
    function get_doc_info($res_id, $verif = 'true')
    {
        $db= new dbquery();
        $db->connect();

        //Get ressources information
        //$db->query("select r.res_id, r.type_id, date_format(date(r.creation_date) , '%d/%m/%Y') as creation_date, r.category_id, r.type_label,  r.dest_user, r.destination,  date_format(date(r.process_limit_date), '%d-%m-%Y') as process_limit_date, r.category_id, r.subject, u.firstname as ufirstname, u.lastname as ulastname, e.entity_label
        $db->query("select r.res_id, r.type_id, r.creation_date as creation_date, r.category_id, r.type_label,  r.dest_user, r.destination,  r.process_limit_date as process_limit_date, r.category_id, r.subject, u.firstname as ufirstname, u.lastname as ulastname, e.entity_label
                            from ".$_SESSION['ressources']['letterbox_view']." r , ".$_SESSION['tablename']['users']." u , ".$_SESSION['tablename']['entities']." e
                            where res_id = '".$res_id."' and r.dest_user = u.user_id and r.destination = e.entity_id ");
        
        //Get value in db object
        $result = $db->fetch_object();
        //var_dump($result); exit();
    
        //Return string with all information for this contact
        $doc_contact_field = $this->get_contact_information($res_id, $result->category_id);
    
        //Restore contact full information
        $rempl = array('res_id'=>$res_id, 
                                    'type_id'=>$this->utf8_maarch(html_entity_decode($result->type_id)),
                                    'type_label'=>$this->utf8_maarch(html_entity_decode($result->type_label)),
                                    'dest_user'=>$this->utf8_maarch(html_entity_decode($result->dest_user)),
                                    'full_dest_user'=>$this->utf8_maarch(html_entity_decode($result->ufirstname." ".$result->ulastname)),
                                    'destination'=>$this->utf8_maarch(html_entity_decode($result->destination)),
                                    'entity_label'=>$this->utf8_maarch(html_entity_decode($result->entity_label)),
                                    'process_limit_date'=>$this->utf8_maarch(html_entity_decode($result->process_limit_date)),
                                    'subject'=>$this->utf8_maarch(html_entity_decode($result->subject)),
                                    'contact_full' => $this->utf8_maarch(html_entity_decode($doc_contact_field)),
                                    'creation_date' => $this->utf8_maarch(html_entity_decode($result->creation_date)),
                                    'category_id' => $this->utf8_maarch(html_entity_decode($result->category_id)),
                                    'verif' => $verif,
                                    );
        return $rempl;
    }

    function sendmail_notes($res_table, $res_id, $notes = '', $user = '', $date = '')
    {
        $db= new dbquery();
        $db->connect();
        include_once('modules/notifications/lang/' . $_SESSION['config']['lang'] . '.php');
        
        //Get the dest user of this document
        $db->query("select dest_user from " . $res_table . " where RES_ID = '" . $res_id . "'");
        $result1 = $db->fetch_object();
        $this_dest_user = $result1->dest_user;
        
        if ($this_dest_user <> $_SESSION['user']['UserId'])
        {
            //Get mail address from this user
            $db->query("select mail from " . $_SESSION['tablename']['users'] . " where user_id = '" . $this_dest_user . "'");
            $result2= $db->fetch_object();
            $mail = $result2->mail;
            
            $mail_trait = _HELLO_NOTE . ' ' . $res_id . '.<br>';
            $mail_trait .= _NOTE_BODY . $notes . '.<br>';
            if ($user <> '') {
                if ($date == '') {
                    $date = date('d/m/Y');
                }
                $mail_trait .= _NOTE_DETAILS . $user . ' ' . _NOTE_DATE_DETAILS . ' ' . $date . '.<br>';
            }
            $mail_trait .= '<a href="' . $_SESSION['config']['coreurl'] . 'apps/maarch_entreprise/index.php?page=details&dir=indexing_searching&id=' 
                        . $res_id . '">'  . _LINK_TO_MAARCH . '</a>';
            //Send mail to the dest_user
            if ($mail <> '') {
                if (!mail($mail, _NEW_NOTE_BY_MAIL . ' ' . $res_id, $mail_trait, "From: " . $_SESSION['config']['adminmail'] . "\nReply-To: " . $_SESSION['config']['adminmail'] . " \nContent-Type: text/html; charset=\"iso-8859-1\"\n")) {
                    //echo "mail not sended";
                }
            }
        }
    }

    //Return date oh the day
    public function tmplt_day_date($actual_string)
    {
            return date("d-m-Y");
    }

    //Return full name of desired user
    public function tmplt_full_name_for_dest_mail($users)
    {
            return $users['PRENOM']." ".$users['NOM'];
    }
    
    //Generate link to view the document
    public function maarch_url($actual_string)
    {
        $return = $_SESSION['config']['MaarchURL'];
        return $return;
    }

    //Load value from db with $result tab
    public function tmplt_load_value($actual_string, $result)
    {
        $my_explode= explode ("|", $actual_string);
        if (!$my_explode[1])
        {
            return _WRONG_PARAM_FOR_LOAD_VALUE;
        }
        else
        {
            $to_share = $my_explode[1];
            return $result[$to_share];
        }
    }

    //Tag document when it's over, switch flag_notif
    public function add_flag_for_ressources($ressources, $column_flag)
    {
        $this->connect(); 
        if ($_SESSION['debug']['tag_when_send'] == "true") {
            for ($l=0;$l<count($ressources);$l++) {
                if ($ressources[$l]['verif'] == 'true') {
                    $this->query("UPDATE " . $_SESSION['collection'][0]['extensions'][0] . " set " . $column_flag . " = 'Y' where RES_ID= " . $ressources[$l]['res_id']);
                } else {
                    echo "notice : unable to updating flags because mail don't send for resource : " . $ressources[$l]['res_id'] . "\r\n";
                }
            }
        } else {
            echo "notice : tag_when_send is desactivated, all ressources will be sended again by the next script!\r\n";
        }
    }


    public function send_mail($user, $title, $content)
    //Send mail for an user
    {
        if ($_SESSION['debug']['send_mail'] == 'true')
        {
            if (!mail($user, $title, $content , "From: ".$_SESSION['config']['mail']."\nReply-To: ".$_SESSION['config']['mail']." \nContent-Type: text/html; charset=\"iso-8859-1\"\n"))
            {
                echo 'mail not sended'."\r\n";
                return 'false';
            }
            else
            {
                echo 'mail sended'."\r\n";
                return 'true';
            }
        }
        else
        {
            echo $content."\r\nnotice : debug mode is activated, features send_mail is desactivated\r\n";
            return 'false';
        }   
    }




    //Load string ans search all function defined in this string
     public function load_var_sys($actual_string, $result = array(), $users = 'empty' )
    {
        ##display_full_name_for_dest_mail##: display complete name for the user who has receive this mail 
        if (preg_match("/^display_full_name_for_dest_mail$/", $actual_string))
        {
            $my_var = $this->tmplt_full_name_for_dest_mail($users);
        }
        ##day_date##: Display the current date 
        elseif (preg_match("/^day_date$/", $actual_string))
        {
            $my_var = $this->tmplt_day_date($actual_string);
        }
        ##load_value|arg1##: load value in the db; arg1= column's value identifier
        elseif (preg_match("/^load_value\|/", $actual_string))
        {
            $my_var = $this->tmplt_load_value($actual_string, $result);
        }
        ##load_css|arg1## : load css style - arg1= name of this class
        elseif (preg_match("/^load_css\|/", $actual_string))
        {
            $my_var = $this->tmplt_load_css($actual_string);
        }
        ##css_line|coll|nonecoll## : load css style for line arg1,arg2 : switch beetwin style on line one or line two
        elseif (preg_match("/^css_line_reload$/", $actual_string))
        {
            $my_var = $this->tmplt_css_line_reload($actual_string);
        }
        ##css_line|coll|nonecoll## : load css style for line arg1,arg2 : switch beetwin style on line one or line two
        elseif (preg_match("/^css_line\|/", $actual_string))
        {
            $my_var = $this->tmplt_css_line($actual_string);
        }
        ##load_img|arg1## : show loaded image; arg1= name of img file
        elseif (preg_match("/^load_img\|/", $actual_string))
        {

            $my_var = $this->tmplt_load_img($actual_string);
        }
        ##order_link|arg1|arg2## : reload list and change order;  arg1=type; arg2=sort
        elseif (preg_match("/^order_link\|/", $actual_string))
        {

            $my_var = $this->tmplt_order_link($actual_string);

        }
        ##maarch_url## : url for acces to maarch
        elseif (preg_match("/^maarch_url$/", $actual_string))
        {
            $my_var = $this->maarch_url($actual_string);
        }
    
        ##url_docdetail## : load page detail for this file
        elseif (preg_match("/^url_docdetail$/", $actual_string))
        {
            $my_var = $this->tmplt_url_docdetail($actual_string, $theline, $result, $key);
        }
        ##func_bool_radio_form## : Activate parameters in class list show
        elseif (preg_match("/^func_bool_radio_form$/", $actual_string))
        {
            $my_var = $this->tmplt_func_bool_radio_form($actual_string, $theline, $result, $key);
        }
        ##func_bool_check_form## : Activate parameters in class list show
        elseif (preg_match("/^func_bool_check_form$/", $actual_string))
        {
            $my_var = $this->tmplt_func_bool_check_form($actual_string, $theline, $result, $key);
        }
        ##func_bool_view_doc## : Activate parameters in class list show
        elseif (preg_match("/^func_bool_view_doc$/", $actual_string))
        {
            $my_var = $this->tmplt_func_bool_view_doc($actual_string, $theline, $result, $key);
        }
        ##func_bool_detail_doc## : Activate parameters in class list show
        elseif (preg_match("/^func_bool_detail_doc$/", $actual_string))
        {
            $my_var = $this->tmplt_func_bool_detail_doc($actual_string, $theline, $result, $key);
        }
        else
        {
            $my_var = "WRONG_FUNCTION_OR_WRONG_PARAMETERS";
        }
        return $my_var;
    }
}

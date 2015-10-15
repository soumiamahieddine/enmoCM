<?php
require_once('core/class/class_request.php');
require_once('core/class/class_security.php');
require_once('apps/' . $_SESSION['config']['app_id'] 
    . '/class/class_list_show.php'
);
require_once('core/class/class_manage_status.php');
$list = new list_show();
$sec = new security();
$statusObj = new manage_status();
$select = array();
$select[$view]= array();
if($view=="view_folders"){
    array_push($select[$view], 'folders_system_id', 'status', 'folder_name', 'creation_date');

}else{
    array_push($select[$view], 'res_id', 'status', 'subject', 'creation_date');
}
$list = new list_show();
$orderstr = $list->define_order('desc', 'creation_date');

$requestCount = new request();
$tabCount = $requestCount->select($select, $whereRequest, $orderstr, $_SESSION['config']['databasetype'],$add_security);
//var_dump(count($tabCount));

//$nombreDeLignes = count($tabCount);


if (isset($whereRequest) && !empty($whereRequest)) {
    $request = new request();
    $tab = $request->select($select, 
        $whereRequest,
        $orderstr,
        $_SESSION['config']['databasetype'],
        500,
        false,
        '',
        '',
        '',
        $add_security
    );
$nombreDeLignes = count($tab);
$nombreDeLignesAffiche = $_SESSION['config']['nblinetoshow'];



echo '<ul id ="list">';
 

    ?>
    <button id="boutonSuivant" class="whiteButton" style="display:block;margin:auto;margin-top:5px;" onclick="toggle_visibility_suivant(<?php echo $nombreDeLignes; ?>)">Suivant</button>
    <button id="boutonPrecedent" class="whiteButton" style="display:block;margin:auto;margin-bottom:5px;" disabled="disabled" onclick="toggle_visibility_precedent(<?php echo $nombreDeLignes; ?>)">Précédent</button>
    <input type="hidden" id="start"  value=" <?php echo $nombreDeLignesAffiche; ?> "/>
    <input type="hidden" id="sendNbLineToShow" value=" <?php echo $nombreDeLignesAffiche; ?> "/>

    <?php
    if (count($tab) > 0) {
        for ($i=0;$i<count($tab);$i++) {
            if($view=="view_folders"){
                $line = '<li>';
            }else{ 
                if($i<$nombreDeLignesAffiche){
                $line = '<li id=res_'.$i.' style="display:block;"><a href="details.php?id=';}else{$line = '<li id=res_'.$i.' style="display:none;"><a href="details.php?id=';}
            }
            for ($j=0;$j<count($tab[$i]);$j++) {
                foreach(array_keys($tab[$i][$j]) as $value) {
                    if($tab[$i][$j][$value]=='res_id' || $tab[$i][$j][$value]=='folders_system_id') {
                        if($view=="view_folders"){
                            $line .= $tab[$i][$j]['value'] . '. ';
                        }else{
                            $line .= $tab[$i][$j]['value'] . '"><small style="color: #58585A;">'
                               . '<span style="float:left;width:90px;text-align:center;">'.$tab[$i][$j]['value'].'<br/>';
                        }
                        
                    }
                    if ($tab[$i][$j][$value]=="status") {
                        $resStatus = $statusObj->get_status_data(
                            $tab[$i][$j]['value'], ''
                        );
                        if ($resStatus['IMG_SRC'] <> '') {
                            $line .= '<i style="color:#009DC5;" class="fm fm-letter-status-attr fm-2x" alt="'.$resStatus['LABEL'].'" title="'.$resStatus['LABEL'].'"></i>'
                            //$line .= '<img src="' . $resStatus['IMG_SRC']
                                   . '</span>';
                        } else {
                            $line = $tab[$i][$j]['value'] . '<br/>';
                        }
                        /*$line .= '(' . $resStatus['LABEL'] 
                               . '), ';*/
                        
                    }
                    if ($tab[$i][$j][$value]=="subject" || $tab[$i][$j][$value]=="folder_name") {
                        $line .= '<span style="float:left;width:70%;">' . $request->cut_string(
                            $request->show_string($tab[$i][$j]['value']), 80
                            ) . '<br/><br/>';
                    }
                    if ($tab[$i][$j][$value]=="creation_date") {
                        $line .= '<span style="float:right;font-size:12px;">' . $request->format_date_db(
                            $tab[$i][$j]['value'], false
                        ).'</span></span><div style="clear:both;"></div>';
                    }
                }
            }

             if($view=="view_folders"){
                     echo $line . '</li>';
                        }else{
                                  echo $line . '</small></a></li>';

                        }
        }
    } else {
        echo '<p>' . _NO_RESULTS . '</p>';
    }
    echo '</ul>';   


    //echo '<a href="#" onclick="toggle_visibility("foo","foo2");">style="text-decoration:none"><input type="button" class="whiteButton" value="Suivant"></a>';
    //echo '<a type="submit" href="search_result.php?" style="text-decoration:none"><input type="button" class="whiteButton" value="Suivant"></a>';
    
}


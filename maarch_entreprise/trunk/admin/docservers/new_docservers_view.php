<?php
//CONTROLLER
    //INIT
        $modeList   = false;
        $modeCreate = false;
        $modeRead   = false;
        $modeUpdate = false;
        
        $formFields = array();
    
    //Titre de la page
        $titleImageSource = $_SESSION['config']['businessappurl'].'static.php?filename=favicon.png';
        
        if ($params['mode'] == 'list') {
            $modeList = true;
            $titleText = _DOCSERVERS_LIST.' : '.count($dataObjectList->$params['objectName']).' '._DOCSERVERS;
            
        } elseif ($params['mode'] == 'create') {
            $modeCreate = true;
            $titleText = _DOCSERVER_ADDITION;
                
        } elseif ($params['mode'] == 'read') {
            $modeRead = true;
            $titleText = _DOCSERVER_READ;
                
        } elseif ($params['mode'] == 'update') {
            $modeUpdate = true;
            $titleText = _DOCSERVER_MODIFICATION;
            
        } else {
            $titleText = _ERROR;
            
        }
        
    //SHOW
        if ($modeList) {
            /* just show the list */
            $str_returnShow = $listContent;
            
        } elseif ($modeCreate) {
            $formFields['docserver_id'] = '';
            
        } elseif ($modeRead) {
            $formFields['docserver_id'] = '';
            
            
        } elseif ($modeUpdate) {
            $formFields['docserver_id'] = '';
            
            
        }
?>

<!--VIEW-->
<h1>
    <img 
      src="<?php echo $titleImageSource; ?>" 
      alt="" 
    />
    <?php echo $titleText; ?>
</h1>
<?php echo $str_returnShow; ?>

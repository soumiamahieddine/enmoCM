<?php

$func = new functions();

if ($objectType == 'templateStyle') {
    // a new template
    $fileExtension = $func->extractFileExt($objectId);
    $fileNameOnTmp = 'tmp_file_' . $_SESSION['user']['UserId']
        . '_' . rand() . '.' . $fileExtension;
    $filePathOnTmp = $_SESSION['config']['tmppath'] . $fileNameOnTmp;
    if (!copy($objectId, $filePathOnTmp)) {
        createXML(
            'ERROR', 
            _FAILED_TO_COPY_ON_TMP . ':' . $objectId . ' ' . $filePathOnTmp
        );
    }
} elseif ($objectType == 'template' || $objectType == 'attachementFromTemplate') {
    if ($_SESSION['m_admin']['templates']['current_style'] <> '') {
        // edition in progress
        $fileExtension = $func->extractFileExt(
            $_SESSION['m_admin']['templates']['current_style']
        );
        $filePathOnTmp = $_SESSION['m_admin']['templates']['current_style'];
    } else {
        //new attachment from a template
        if (isset($_SESSION['cm']['resMaster']) && $_SESSION['cm']['resMaster'] <> '') {
            $sec = new security();
            $collId = $sec->retrieve_coll_id_from_table($objectTable);
            $_SESSION['cm']['collId'] = $collId;
        }
        // new edition
        require_once 'modules/templates/templates_tables_definition.php';
        $dbTemplate = new dbquery();
        $dbTemplate->connect();
        $query = "select path_template from " . _DOCSERVERS_TABLE_NAME 
            . " where docserver_id = 'TEMPLATES'";
        $dbTemplate->query($query);
        $resDs = $dbTemplate->fetch_object();
        $pathToDs = $resDs->path_template;
        $query = "select template_path, template_file_name from " . _TEMPLATES_TABLE_NAME 
            . " where template_id = '" . $objectId . "'";
        $dbTemplate->query($query);
        $resTemplate = $dbTemplate->fetch_object();
        $pathToTemplateOnDs = $pathToDs . str_replace(
                "#", 
                DIRECTORY_SEPARATOR, 
                $resTemplate->template_path
            )
            . $resTemplate->template_file_name;
        $fileExtension = $func->extractFileExt($pathToTemplateOnDs);    
        $fileNameOnTmp = 'tmp_file_' . $_SESSION['user']['UserId']
            . '_' . rand() . '.' . $fileExtension;
        $filePathOnTmp = $_SESSION['config']['tmppath'] . $fileNameOnTmp;
        
        if (!copy($pathToTemplateOnDs, $filePathOnTmp)) {
            createXML(
                'ERROR', 
                _FAILED_TO_COPY_ON_TMP . ':' . $pathToTemplateOnDs . ' ' . $filePathOnTmp
            );
        }
    }
}

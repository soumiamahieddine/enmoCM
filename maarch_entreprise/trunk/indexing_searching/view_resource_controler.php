<?php

/*
*   Copyright 2008-2011 Maarch
*
*   This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief  controler of the view resource page
*
* @file view_resource_controler.php
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup indexing_searching
*/

if (!isset($_SESSION['user']['UserId']) && $_SESSION['user']['UserId'] == '') {
    if (trim($_SERVER['argv'][0]) <> '') {
        header('location: reopen.php?' . $_SERVER['argv'][0]);
    } else {
        header('location: reopen.php');
    }
    exit();
}

try {
    require_once('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR 
        . 'class_request.php');
    require_once('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR 
        . 'class_security.php');
    require_once('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR 
        . 'class_resource.php');
    require_once('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR 
        . 'docservers_controler.php');
} catch (Exception $e) {
    echo $e->getMessage();
}
$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$function = new functions();
$sec = new security();
$mode = '';
$calledByWS = false;
//1:test the request ID
if (isset($_REQUEST['id'])) {
    $s_id = $_REQUEST['id'];
} else {
    $s_id = '';
}
if (isset($_REQUEST['resIdMaster'])) {
    $resIdMaster = $_REQUEST['resIdMaster'];
} else {
    $resIdMaster = '';
}
if ($s_id == '') {
    $_SESSION['error'] = _THE_DOC . ' ' . _IS_EMPTY;
    header('location: ' . $_SESSION['config']['businessappurl'] . 'index.php');
    exit();
} else {
    //2:retrieve the view
    $table = '';
    if (isset($_REQUEST['collid']) && $_REQUEST['collid'] <> '') {
        $_SESSION['collection_id_choice'] = $_REQUEST['collid'];
    }
    
    if (isset($_SESSION['collection_id_choice']) 
        && !empty($_SESSION['collection_id_choice'])
    ) {
        $table = $sec->retrieve_view_from_coll_id(
            $_SESSION['collection_id_choice']
        );
        if (!$table) {
            $table = $sec->retrieve_table_from_coll(
                $_SESSION['collection_id_choice']
            );
        }
    } else {
        
        if (isset($_SESSION['collections'][0]['view']) 
            && !empty($_SESSION['collections'][0]['view'])
        ) {
            $table = $_SESSION['collections'][0]['view'];
        } else {
            $table = $_SESSION['collections'][0]['table'];
        }
        $_SESSION['collection_id_choice'] = $_SESSION['collections'][0]['id'];
    }
    for ($cptColl = 0;$cptColl < count($_SESSION['collections']);$cptColl++) {
        if ($table == $_SESSION['collections'][$cptColl]['table'] 
            || $table == $_SESSION['collections'][$cptColl]['view']
        ) {
            $adrTable = $_SESSION['collections'][$cptColl]['adr'];
        }
    }
    if ($adrTable == '') {
        $adrTable = $_SESSION['collections'][0]['adr'];
    }
    
    $versionTable = $sec->retrieve_version_table_from_coll_id(
        $_SESSION['collection_id_choice']
    );
    //SECURITY PATCH
    require_once 'core/class/class_security.php';
    if ($resIdMaster <> '') {
        $idToTest = $resIdMaster;
    } else {
        $idToTest = $s_id;
    }
    $security = new security();
    $right = $security->test_right_doc(
        $_SESSION['collection_id_choice'], 
        $idToTest
    );
	
    //$_SESSION['error'] = 'coll '.$coll_id.', res_id : '.$s_id;
	if($_SESSION['origin'] = 'search_folder_tree'){
		$_SESSION['origin'] = 'search_folder_tree';
	}else{
		$_SESSION['origin'] = '';
	}	
    if (!$right) {
        ?>
        <script type="text/javascript">
        window.top.location.href = '<?php
            echo $_SESSION['config']['businessappurl'];
            ?>index.php?page=no_right';
        </script>
        <?php
        exit();
    }
    if (
        $versionTable <> '' 
        && !isset($_REQUEST['original'])
        && !isset($_REQUEST['aVersion'])
    ) {
        $selectVersions = "select res_id from " 
            . $versionTable . " where res_id_master = " 
            . $s_id . " and status <> 'DEL' order by res_id desc";
        $dbVersions = new dbquery();
        $dbVersions->connect();
        $dbVersions->query($selectVersions);
        $lineLastVersion = $dbVersions->fetch_object();
        $lastVersion = $lineLastVersion->res_id;
        if ($lastVersion <> '') {
            $s_id = $lastVersion;
            $table = $versionTable;
            $adrTable = '';
        }
    } elseif(isset($_REQUEST['aVersion'])) {
        $table = $versionTable;
    }
    $docserverControler = new docservers_controler();
    $viewResourceArr = array();
    $docserverLocation = array();
    $docserverLocation =
        $docserverControler->retrieveDocserverNetLinkOfResource(
            $s_id, $table, $adrTable
        );
    if ($docserverLocation['value'] <> '' 
        && $_SESSION['config']['coreurl'] <> $docserverLocation['value']
    ) {
        $connexion = new dbquery();
        $connexion->connect();
        $connexion->query(
            "select password from " . $_SESSION['tablename']['users'] 
            . " where user_id = '" . $_SESSION['user']['UserId'] . "'"
        );
        $resultUser = $connexion->fetch_object();
        if ($core_tools->isEncrypted() == 'true') {
            //$core_tools->generatePrivatePublicKey();
            $proxyOne = $core_tools->encrypt($_SESSION['user']['UserId']);
            $proxyTwo = $core_tools->encrypt($resultUser->password);
            if (!$proxyOne || !$proxyTwo) {
                $_SESSION['error'] = _PB_WITH_PUBLIC_OR_PRIVATE_KEY;
                header('location: ' . $_SESSION['config']['businessappurl'] 
                       . 'index.php');
                exit();
            }
        } else {
            $proxyOne = $_SESSION['user']['UserId'];
            $proxyTwo = $resultUser->password;
        }
        header('location: ' . $docserverLocation['value'] . 'ws_client.php?id=' 
               . $s_id . '&table=' . $table . '&proxy1=' . $proxyOne 
               . '&proxy2=' . $proxyTwo);
    } else {
        $viewResourceArr = $docserverControler->viewResource(
            $s_id, 
            $table, 
            $adrTable, 
            false
        );
        if ($viewResourceArr['error'] <> '') {
            //...
        } else {
            //$core_tools->show_array($viewResourceArr);
            if (strtoupper($viewResourceArr['ext']) == 'HTML' 
                && $viewResourceArr['mime_type'] == "text/plain"
            ) {
                $viewResourceArr['mime_type'] = "text/html";
            }
            if ($viewResourceArr['called_by_ws']) {
                $fileContent = base64_decode($viewResourceArr['file_content']);
                $fileNameOnTmp = 'tmp_file_' . rand() . '_' 
                    . md5($fileContent) . '.' 
                    . strtolower($viewResourceArr['ext']);
                $filePathOnTmp = $_SESSION['config']['tmppath'] 
                    . DIRECTORY_SEPARATOR . $fileNameOnTmp;
                $inF = fopen($filePathOnTmp, 'w');
                fwrite($inF, $fileContent);
                fclose($inF);
            } else {
                $filePathOnTmp = $viewResourceArr['file_path'];
            }
            if (strtolower(
                $viewResourceArr['mime_type']
            ) == 'application/maarch'
            ) {
                $myfile = fopen($filePathOnTmp, 'r');
                $data = fread($myfile, filesize($filePathOnTmp));
                fclose($myfile);
                $content = stripslashes($data);
            }
        }
        include('view_resource.php');
    }
}

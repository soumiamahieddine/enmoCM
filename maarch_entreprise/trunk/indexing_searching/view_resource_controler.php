<?php

/*
*   Copyright 2008-2015 Maarch
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
$_SESSION['HTTP_REFERER'] = Url::requestUri();
if (!isset($_SESSION['user']['UserId']) && $_SESSION['user']['UserId'] == '') {
    if (trim($_SERVER['argv'][0]) <> '') {
        header('location: reopen.php?' . $_SERVER['argv'][0]);
    } else {
        header('location: reopen.php');
    }
    exit();
}
$_SESSION['HTTP_REFERER'] = '';

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
        $selectVersions = "SELECT res_id FROM " 
            . $versionTable . " WHERE res_id_master = ? and status <> 'DEL' order by res_id desc";
        $db = new Database();
        $stmt = $db->query($selectVersions, array($s_id));
        $lineLastVersion = $stmt->fetchObject();
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

        //WATERMARK
        if (strtoupper($viewResourceArr['ext']) == 'PDF') {
            require_once 'apps/maarch_entreprise/class/class_pdf.php';
            if ($_SESSION['features']['watermark']['enabled'] == 'true') {
                $filePathOnTmp = $viewResourceArr['file_path'];
                $filePathOnTmpResult = $viewResourceArr['file_path'];
                if (
                    $_SESSION['features']['watermark']['column_name'] == ''
                    && $_SESSION['features']['watermark']['default_text'] == ''
                ) {
                    $watermark = 'watermark by ' . $_SESSION['user']['UserId'];
                } elseif ($_SESSION['features']['watermark']['column_name'] <> '') {
                    $dbView = new Database();
                    $query = " select " . $_SESSION['features']['watermark']['column_name'] 
                        . " as thecolumn from res_view_letterbox where res_id = ?";
                    $stmt = $dbView->query($query, array($s_id));
                    $returnQuery = $stmt->fetchObject();
                    $watermark = $returnQuery->thecolumn;
                } elseif ($_SESSION['features']['watermark']['default_text'] <> '') {
                    $watermark = $_SESSION['features']['watermark']['default_text'];
                }
                $positionDefault = array();
                $position = array();
                $positionDefault['X'] = 50;
                $positionDefault['Y'] = 450;
                $positionDefault['angle'] = 30;
                $positionDefault['opacity'] = 0.5;
                if ($_SESSION['features']['watermark']['position'] == '') {
                    $position = $positionDefault;
                } else {
                    $arrayPos = explode(',', $_SESSION['features']['watermark']['position']);
                    if (count($arrayPos) == 4) {
                        $position['X'] = trim($arrayPos[0]);
                        $position['Y'] = trim($arrayPos[1]);
                        $position['angle'] = trim($arrayPos[2]);
                        $position['opacity'] = trim($arrayPos[3]);
                    } else {
                        $position = $positionDefault;
                    }
                }
                $fontDefault = array();
                $font = array();
                $fontDefault['fontName'] = 'helvetica';
                $fontDefault['fontSize'] = '10';
                if ($_SESSION['features']['watermark']['font'] == '') {
                    $font = $fontDefault;
                } else {
                    $arrayFont = explode(',', $_SESSION['features']['watermark']['font']);
                    
                    if (count($arrayFont) == 2) {
                        $font['fontName'] = trim($arrayFont[0]);
                        $font['fontSize'] = trim($arrayFont[1]);
                    } else {
                        $font = $fontDefault;
                    }
                }
                $colorDefault = array();
                $color = array();
                $colorDefault['color1'] = '192';
                $colorDefault['color2'] = '192';
                $colorDefault['color3'] = '192';
                if ($_SESSION['features']['watermark']['text_color'] == '') {
                    $color = $colorDefault;
                } else {
                    $arrayColor = explode(',', $_SESSION['features']['watermark']['text_color']);
                    if (count($arrayColor) == 3) {
                        $color['color1'] = trim($arrayColor[0]);
                        $color['color2'] = trim($arrayColor[1]);
                        $color['color3'] = trim($arrayColor[2]);
                    } else {
                        $color = $colorDefault;
                    }
                }
                // Create a PDF object and set up the properties
                $pdf = new PDF("p", "pt", "A4");
                $pdf->SetAuthor("MAARCH");
                $pdf->SetTitle("Watermarking by MAARCH");
                $pdf->SetTextColor($color['color1'],$color['color2'],$color['color3']);

                $pdf->SetFont($font['fontName'], '', $font['fontSize']);
                //$stringWatermark = substr($watermark, 0, 11);
                $stringWatermark = $watermark;
                // Load the base PDF into template
                $nbPages = $pdf->setSourceFile($filePathOnTmp);
                //For each pages add the watermark
                for ($cpt=1;$cpt<=$nbPages;$cpt++) {
                    $tplidx = $pdf->ImportPage($cpt);
                    $specs = $pdf->getTemplateSize($tplidx);
                     //Add new page & use the base PDF as template
                    $pdf->addPage($specs['h'] > $specs['w'] ? 'P' : 'L');
                    $pdf->useTemplate($tplidx);
                    //Set opacity
                    $pdf->SetAlpha($position['opacity']);
                    //Add Watermark
                    $pdf->TextWithRotation(
                        $position['X'], 
                        $position['Y'], 
                        $stringWatermark, 
                        $position['angle']
                    );
                }
                $pdf->Output($filePathOnTmpResult, "F");
            }
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

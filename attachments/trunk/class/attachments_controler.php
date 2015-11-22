<?php
/*
*   Copyright 2013 Maarch
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
* @brief  Contains the controler of the attachment Object
*
*
* @file
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup attachments
*/

/**
* @brief  Controler of the attachment Object
*
* @ingroup attachments
*/
class attachments_controler
{
    #####################################
    ## Attachment on a resource
    #####################################
    //add datas to have the subject
    public function storeAttachmentResource($resId, $collId, $encodedContent, $fileFormat, $title)
    {
        require_once 'core/class/class_db_pdo.php';
        require_once 'core/class/class_request.php';
        require_once 'core/class/class_resource.php';
        require_once 'core/class/docservers_controler.php';
        require_once 'core/class/class_security.php';
        $sec = new security();
        $table = $sec->retrieve_table_from_coll($collId);
        $db = new Database();
        $query = 'select res_id from ' . $table . ' where res_id = ?';
        $stmt = $db->query($query,array($resId), true);
        if ($stmt->rowCount() == 0) {
            $returnCode = -2;
            $error .= 'res_id inexistant';
        } else {
            $fileContent = base64_decode($encodedContent);
            $tmpFileName = 'tmp_file_ws_'
                 . rand() . "_" . md5($fileContent) 
                 . "." . strtolower($fileFormat);
            $Fnm = $_SESSION['config']['tmppath'] . $tmpFileName; 
            $inF = fopen($Fnm, "w");
            fwrite($inF, $fileContent);
            fclose($inF);
            $docserverControler = new docservers_controler();
            $docserver = $docserverControler->getDocserverToInsert(
               $collId
            );
            if (empty($docserver)) {
                $returnCode = -3;
                $error = _DOCSERVER_ERROR . ' : '
                    . _NO_AVAILABLE_DOCSERVER . ". " . _MORE_INFOS . ".";
            } else {
                $newSize = $docserverControler->checkSize(
                    $docserver, $_SESSION['upfile']['size']
                );
                if ($newSize == 0) {
                    $returnCode = -4;
                    $error = _DOCSERVER_ERROR . ' : '
                        . _NOT_ENOUGH_DISK_SPACE . ". " . _MORE_INFOS . ".";
                } else {
                    $fileInfos = array(
                        "tmpDir"      => $_SESSION['config']['tmppath'],
                        "size"        => filesize($Fnm),
                        "format"      => strtolower($fileFormat),
                        "tmpFileName" => $tmpFileName,
                    );
                    $storeResult = array();
                    $storeResult = $docserverControler->storeResourceOnDocserver(
                        $collId, $fileInfos
                    );
                    if (isset($storeResult['error']) && $storeResult['error'] <> '') {
                        $returnCode = -5;
                        $error = $storeResult['error'];
                    } else {
                        unlink($Fnm);
                        $resAttach = new resource();
                        $_SESSION['data'] = array();
                        array_push(
                            $_SESSION['data'],
                            array(
                                'column' => "typist",
                                'value' => $_SESSION['user']['UserId'],
                                'type' => "string",
                            )
                        );
                        array_push(
                            $_SESSION['data'],
                            array(
                                'column' => "format",
                                'value' => strtolower($fileFormat),
                                'type' => "string",
                            )
                        );
                        array_push(
                            $_SESSION['data'],
                            array(
                                'column' => "docserver_id",
                                'value' => $storeResult['docserver_id'],
                                'type' => "string",
                            )
                        );
                        array_push(
                            $_SESSION['data'],
                            array(
                                'column' => "status",
                                'value' => 'NEW',
                                'type' => "string",
                            )
                        );
                        array_push(
                            $_SESSION['data'],
                            array(
                                'column' => "offset_doc",
                                'value' => ' ',
                                'type' => "string",
                            )
                        );
                        array_push(
                            $_SESSION['data'],
                            array(
                                'column' => "logical_adr",
                                'value' => ' ',
                                'type' => "string",
                            )
                        );
                        array_push(
                            $_SESSION['data'],
                            array(
                                'column' => "title",
                                'value' => strtolower($title),
                                'type' => "string",
                            )
                        );
                        array_push(
                            $_SESSION['data'],
                            array(
                                'column' => "coll_id",
                                'value' => $collId,
                                'type' => "string",
                            )
                        );
                        array_push(
                            $_SESSION['data'],
                            array(
                                'column' => "res_id_master",
                                'value' => $resId,
                                'type' => "integer",
                            )
                        );
                        if ($_SESSION['origin'] == "scan") {
                            array_push(
                                $_SESSION['data'],
                                array(
                                    'column' => "scan_user",
                                    'value' => $_SESSION['user']['UserId'],
                                    'type' => "string",
                                )
                            );
                            array_push(
                                $_SESSION['data'],
                                array(
                                    'column' => "scan_date",
                                    'value' => $req->current_datetime(),
                                    'type' => "function",
                                )
                            );
                        }
                        array_push(
                            $_SESSION['data'],
                            array(
                                'column' => "type_id",
                                'value' => 0,
                                'type' => "int",
                            )
                        );
                        $id = $resAttach->load_into_db(
                            'res_attachments',
                            $storeResult['destination_dir'],
                            $storeResult['file_destination_name'] ,
                            $storeResult['path_template'],
                            $storeResult['docserver_id'], $_SESSION['data'],
                            $_SESSION['config']['databasetype']
                        );
                        if ($id == false) {
                            $returnCode = -6;
                            $error = $resAttach->get_error();
                        } else {
                            $returnCode = 0;
                            if ($_SESSION['history']['attachadd'] == "true") {
                                $users = new history();
                                $view = $sec->retrieve_view_from_coll_id(
                                    $collId
                                );
                                $users->add(
                                    $view, $resId, "ADD", 'attachadd',
                                    ucfirst(_DOC_NUM) . $id . ' '
                                    . _NEW_ATTACH_ADDED . ' ' . _TO_MASTER_DOCUMENT
                                    . $resId,
                                    $_SESSION['config']['databasetype'],
                                    'apps'
                                );
                                $users->add(
                                    RES_ATTACHMENTS_TABLE, $id, "ADD",'attachadd',
                                    _NEW_ATTACH_ADDED . " (" . $title
                                    . ") ",
                                    $_SESSION['config']['databasetype'],
                                    'attachments'
                                );
                            }
                        }
                    }
                }
            }
        }
        $returnArray = array(
            'returnCode' => (int) $returnCode,
            'resId' => $id,
            'error' => $error,
        );
        return $returnArray;
    }
	
	public function getAttachmentInfos($resId){
		$db = new Database();
		
		$stmt = $db->query(
            "SELECT * 
                FROM res_view_attachments 
                WHERE (res_id = ? OR res_id_version = ?) and res_id_master = ? ORDER BY relation desc", array($resId, $resId, $_SESSION['doc_id'])
        );
		
		$infos = array();
		if ($stmt->rowCount() == 0) {
            $_SESSION['error'] = _THE_DOC . " " . _EXISTS_OR_RIGHT . "&hellip;";
            header(
                "location: " . $_SESSION['config']['businessappurl']
                . "index.php"
            );
            exit();
        } else {
			$line = $stmt->fetchObject();
            $docserver = $line->docserver_id;
            $path = $line->path;
            $filename = $line->filename;
            $format = $line->format;
            $stmt = $db->query(
                "select path_template from docservers where docserver_id = ?",array($docserver)
            );
			
            $lineDoc = $stmt->fetchObject();
            $docserver = $lineDoc->path_template;
            $file = $docserver . $path . $filename;
            $file = str_replace("#", DIRECTORY_SEPARATOR, $file);
			
			$file_pdf = str_replace(pathinfo($filename, PATHINFO_EXTENSION),'pdf',$file);
			$infos['pathfile'] = $file;
			$infos['path'] = $path;
			$infos['pathfile_pdf'] = $file_pdf;
			$infos['status'] = $line->status;
			$infos['attachment_type'] = $line->attachment_type;
			$infos['creation_date'] = $line->creation_date;
			$infos['type_id'] = $line->type_id;
			$infos['title'] = $line->title;
			$infos['typist'] = $line->typist;
		}
		return $infos;
	}
	
	public function getCorrespondingPdf($resId){
		$infos = $this->getAttachmentInfos($resId);
		$db2 = new Database();
		$result = 0;
		$stmt2 = $db2->query(
            "SELECT res_id
                FROM res_view_attachments 
                WHERE path = ? AND filename = ? and attachment_type = 'converted_pdf' ORDER BY relation desc", array($infos['path'],pathinfo($infos['pathfile_pdf'], PATHINFO_BASENAME))
        );
		$line = $stmt2->fetchObject();
		
		if ($line->res_id != 0) $result = $line->res_id;
		return $result;
	}
}

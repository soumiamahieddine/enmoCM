<?php
/*
*   Copyright 2008-2016 Maarch
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


abstract class attachments_controler_Abstract
{
    /**
     * Remove temporary attachment file on docserver
     * @param   bigint $resIdAttachment id of the attachment resource
     * @param   bigint $resIdMaster id of the master document
     * @param   string $userId user id who created the temporary attachment
     * @return  boolean if ok, return true.
     */
    public function removeTemporaryAttachmentOnDocserver($resIdAttachment, $resIdMaster, $userId)
    {
        $db = new Database();
        $stmt = $db->query(
            "SELECT docserver_id, path, filename, fingerprint
                FROM res_attachments
                WHERE res_id = ? AND res_id_master = ? AND status = 'TMP' AND typist = ? ORDER BY relation desc",
            array($resIdAttachment, $resIdAttachment, $resIdMaster, $userId)
        );

        if ($stmt->rowCount() == 0) {
            $_SESSION['error'] = _NO_DOC_OR_NO_RIGHTS;
            return false;
        } else {
            $line           = $stmt->fetchObject();
            $docserverOld   = $line->docserver_id;
            $pathOld        = $line->path;
            $filenameOld    = $line->filename;
            $fingerprintOld = $line->fingerprint;

            $stmt = $db->query("SELECT path_template FROM " . _DOCSERVERS_TABLE_NAME . " WHERE docserver_id = ?", array($docserverOld));
            $lineDoc   = $stmt->fetchObject();
            $docserver = $lineDoc->path_template;
            $file      = $docserver . $pathOld . $filenameOld;
            $file      = str_replace("#", DIRECTORY_SEPARATOR, $file);

            require_once 'core/class/docservers_controler.php';
            require_once 'core/class/docserver_types_controler.php';
            $docserverControler = new docservers_controler();
            $docserverTypeControler = new docserver_types_controler();

            require_once 'core/docservers_tools.php';

            $docserver           = $docserverControler->get($docserverOld);
            $docserverTypeObject = $docserverTypeControler->get($docserver->docserver_type_id);
            $fingerprintOldFile  = Ds_doFingerprint($file, $docserverTypeObject->fingerprint_mode);
            if ($fingerprintOld == $fingerprintOldFile) {
                unlink($file);
            }
            return true;
        }
    }
}

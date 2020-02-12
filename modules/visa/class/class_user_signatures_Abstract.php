<?php
/*
*    Copyright 2008-2016 Maarch
*
*  This file is part of Maarch Framework.
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
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once 'core/core_tables.php';

abstract class UserSignaturesAbstract extends Database
{
    public function getForUser($user_id) {
//        $db = new Database();
//
//        $stmt = $db->query('SELECT * FROM user_signatures WHERE user_id = ? ',
//            [$user_id]
//        );
//        $userSignatures = [];
//        while($res = $stmt->fetchObject())
//            $userSignatures[] = ['id' => $res->id, 'signature_label' => $res->signature_label, 'signature_path' => $res->signature_path, 'signature_file_name' => $res->signature_file_name];
//
        return [];
    }
}
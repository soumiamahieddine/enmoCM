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

abstract class EmailSignaturesAbstract extends Database
{

    protected function rawToHtml($text) {
        $text = str_replace("\r\n", PHP_EOL, $text);
        $text = str_replace("\r", PHP_EOL, $text);
        $text = str_replace('###', ';', $text);
        $text = str_replace('___', '--', $text);

        return $text;
    }

    public function getForCurrentUser() {
        $db = new Database();

        $stmt = $db->query('SELECT * FROM users_email_signatures WHERE user_id = ? order by title',
            [$_SESSION['user']['UserId']]
        );
        $mailSignatures = [];
        while($res = $stmt->fetchObject())
            $mailSignatures[] = ['id' => $res->id, 'title' => $res->title, 'signature' => $this->rawToHtml($res->html_body)];

        return $mailSignatures;
    }
}
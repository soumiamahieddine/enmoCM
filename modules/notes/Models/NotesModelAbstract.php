<?php

/*
 *    Copyright 2015 Maarch
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

require_once 'apps/maarch_entreprise/services/Table.php';

class NotesModelAbstract extends Apps_Table_Service 
{

    public static function getByResId(array $aArgs = []) 
    {
        static::checkRequired($aArgs, ['resId']);

        //get notes
        $aReturn = static::select([
                    'select' => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
                    'table' => ['notes', 'users'],
                    'left_join' => ['notes.user_id = users.user_id'],
                    'where' => ['notes.identifier = ?'],
                    'data' => [$aArgs['resId']],
                    'order_by' => empty($aArgs['orderBy']) ? ['date_note'] : $aArgs['orderBy']
        ]);

        $tmpNoteId = [];
        foreach ($aReturn as $value) {
            $tmpNoteId[] = $value['id'];
        }
        //get entities

        if (!empty($tmpNoteId)) {
            $tmpEntitiesRestriction = [];
            $entities = static::select([
                        'select' => ['note_id', 'item_id'],
                        'table' => ['note_entities'],
                        'where' => ['note_id in (?)'],
                        'data' => [$tmpNoteId],
                        'order_by' => ['item_id']
            ]);

            foreach ($entities as $key => $value) {
                $tmpEntitiesRestriction[$value['note_id']][] = $value['item_id'];
            }
        }

        foreach ($aReturn as $key => $value) {
            if (!empty($tmpEntitiesRestriction[$value['id']])) {
                $aReturn[$key]['entities_restriction'] = implode(", ", $tmpEntitiesRestriction[$value['id']]);
            }
        }

        return $aReturn;
    }

    public static function countByResId(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['resId']);

        $aReturn = static::select([
            'select' => 'COUNT(*)',
            'table' => ['notes'],
            'where' => ['identifier = ?'],
            'data' => [$aArgs['resId']]
        ]);

        return $aReturn[0]['count'];
    }

}

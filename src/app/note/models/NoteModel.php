<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Note Model
 * @author dev@maarch.org
 */

namespace Note\models;

use Entity\models\EntityModel;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;

class NoteModel
{
    public static function get(array $aArgs = [])
    {
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intType($aArgs, ['limit']);

        $notes = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['notes'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data'],
            'order_by'  => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy'],
            'limit'     => empty($aArgs['limit']) ? 0 : $aArgs['limit']
        ]);

        return $notes;
    }

    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $note = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['notes'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']],
        ]);

        if (empty($note[0])) {
            return [];
        }

        return $note[0];
    }

    public static function countByResId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId', 'login']);
        ValidatorModel::intVal($aArgs, ['resId']);
        ValidatorModel::stringType($aArgs, ['login']);

        $nb = 0;
        $countedNotes = [];
        $entities = [];

        $aEntities = DatabaseModel::select([
            'select'    => ['entity_id'],
            'table'     => ['users_entities'],
            'where'     => ['user_id = ?'],
            'data'      => [$aArgs['login']]
        ]);

        foreach ($aEntities as $value) {
            $entities[] = $value['entity_id'];
        }

        $aNotes = DatabaseModel::select([
            'select'    => ['notes.id', 'user_id', 'item_id'],
            'table'     => ['notes', 'note_entities'],
            'left_join' => ['notes.id = note_entities.note_id'],
            'where'     => ['identifier = ?'],
            'data'      => [$aArgs['resId']]
        ]);

        foreach ($aNotes as $value) {
            if (empty($value['item_id']) && !in_array($value['id'], $countedNotes)) {
                ++$nb;
                $countedNotes[] = $value['id'];
            } elseif (!empty($value['item_id'])) {
                if ($value['user_id'] == $aArgs['login'] && !in_array($value['id'], $countedNotes)) {
                    ++$nb;
                    $countedNotes[] = $value['id'];
                } elseif (in_array($value['item_id'], $entities) && !in_array($value['id'], $countedNotes)) {
                    ++$nb;
                    $countedNotes[] = $value['id'];
                }
            }
        }

        return $nb;
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId', 'note_text', 'login']);
        ValidatorModel::intVal($aArgs, ['resId']);
        ValidatorModel::stringType($aArgs, ['login', 'note_text']);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'notes_id_seq']);

        DatabaseModel::insert([
            'table'         => 'notes',
            'columnsValues' => [
                'id'            => $nextSequenceId,
                'identifier'    => $aArgs['resId'],
                'user_id'       => $aArgs['login'],
                'creation_date' => 'CURRENT_TIMESTAMP',
                'note_text'     => $aArgs['note_text']
            ]
        ]);

        return $nextSequenceId;
    }

    public static function getByResId(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        //get notes
        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['notes', 'users', 'users_entities', 'entities'],
            'left_join' => ['notes.user_id = users.user_id', 'users.user_id = users_entities.user_id', 'users_entities.entity_id = entities.entity_id'],
            'where'     => ['notes.identifier = ?', '(users_entities.primary_entity=\'Y\' or notes.user_id = \'superadmin\')'],
            'data'      => [$aArgs['resId']],
            'order_by'  => empty($aArgs['orderBy']) ? ['creation_date'] : $aArgs['orderBy']
        ]);
        $tmpNoteId = [];
        foreach ($aReturn as $value) {
            $tmpNoteId[] = $value['id'];
        }
        //get entities

        if (!empty($tmpNoteId)) {
            $tmpEntitiesRestriction = [];
            $entities = DatabaseModel::select([
                'select'   => ['note_id', 'item_id', 'short_label'],
                'table'    => ['note_entities', 'entities'],
                'left_join' => ['note_entities.item_id = entities.entity_id'],
                'where'    => ['note_id in (?)'],
                'data'     => [$tmpNoteId],
                'order_by' => ['short_label']
            ]);

            foreach ($entities as $key => $value) {
                $tmpEntitiesRestriction[$value['note_id']][] = $value['short_label'];
            }
        }

        foreach ($aReturn as $key => $value) {
            if (!empty($tmpEntitiesRestriction[$value['id']])) {
                $aReturn[$key]['entities_restriction'] = $tmpEntitiesRestriction[$value['id']];
            }
        }

        return $aReturn;
    }

    public static function getByUserIdForResource(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId', 'resId', 'select']);
        ValidatorModel::intVal($aArgs, ['userId', 'resId']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $user = UserModel::getById(['select' => ['user_id'], 'id' => $aArgs['userId']]);
        $rawUserEntities = EntityModel::getByLogin(['login' => $user['user_id'], 'select' => ['entity_id']]);

        $userEntities = [];
        foreach ($rawUserEntities as $rawUserEntity) {
            $userEntities[] = $rawUserEntity['entity_id'];
        }

        $allNotes = NoteModel::get([
            'select'   => $aArgs['select'],
            'where'    => ['identifier = ?'],
            'data'     => [$aArgs['resId']],
            'order_by' => ['id desc']
        ]);

        $notes = [];
        foreach ($allNotes as $note) {
            $allowed = false;

            if ($note['user_id'] == $user['user_id']) {
                $allowed = true;
            }

            $noteEntities = NoteEntityModel::getWithEntityInfo(['select' => ['item_id', 'short_label'], 'where' => ['note_id = ?'], 'data' => [$note['id']]]);
            if (!empty($noteEntities)) {
                foreach ($noteEntities as $noteEntity) {
                    $note['entities_restriction'][] = ['short_label' => $noteEntity['short_label'], 'item_id' => [$noteEntity['item_id']]];

                    if (in_array($noteEntity['item_id'], $userEntities)) {
                        $allowed = true;
                    }
                }
            } else {
                $allowed = true;
            }

            if ($allowed) {
                $notes[] = $note;
            }
        }

        return $notes;
    }
}

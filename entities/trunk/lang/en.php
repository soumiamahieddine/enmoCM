<?php
/*
 *
 *    Copyright 2008,2009 Maarch
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


/*************************** Entites management *****************/
if (!defined('_ADD_ENTITY'))
    define('_ADD_ENTITY','Department added');
if (!defined('_ENTITY_ADDITION'))
    define('_ENTITY_ADDITION', 'Add a department');
if (!defined('_ENTITY_MODIFICATION'))
    define('_ENTITY_MODIFICATION', 'Modify a department');
if (!defined('_ENTITY_AUTORIZATION'))
    define('_ENTITY_AUTORIZATION', 'Enable a department');
if (!defined('_ENTITY_SUSPENSION'))
    define('_ENTITY_SUSPENSION', 'disable a department');
if (!defined('_ENTITY_DELETION'))
    define('_ENTITY_DELETION', 'Delete a department');
if (!defined('_ENTITY_DELETED'))
    define('_ENTITY_DELETED', 'Department deleted');
if (!defined('_ENTITY_UPDATED'))
    define('_ENTITY_UPDATED', 'Department modified');
if (!defined('_ENTITY_AUTORIZED'))
    define('_ENTITY_AUTORIZED', 'Department enabled');
if (!defined('_ENTITY_SUSPENDED'))
    define('_ENTITY_SUSPENDED', 'department disabled');
if (!defined('_ENTITY'))
    define('_ENTITY', 'Department');
if (!defined('_ENTITIES'))
    define('_ENTITIES', 'Departments');
if (!defined('_ENTITIES_COMMENT'))
    define('_ENTITIES_COMMENT', 'Department');
if (!defined('_ALL_ENTITIES'))
    define('_ALL_ENTITIES', 'All departments');
if (!defined('_ENTITIES_LIST'))
    define('_ENTITIES_LIST', 'List of department');
if (!defined('_MANAGE_ENTITIES'))
    define('_MANAGE_ENTITIES', 'Manage departments');
if (!defined('_MANAGE_ENTITIES_DESC'))
    define('_MANAGE_ENTITIES_DESC', 'Manage depoartments');
if (!defined('_ENTITY_MISSING'))
    define('_ENTITY_MISSING', 'This department does not exist');
if (!defined('_ENTITY_TREE'))
    define('_ENTITY_TREE', 'Department tree-view');
if (!defined('_ENTITY_TREE_DESC'))
    define('_ENTITY_TREE_DESC', 'Department tree-view');
if (!defined('_ENTITY_HAVE_CHILD'))
    define('_ENTITY_HAVE_CHILD', 'This department has sub-departments');
if (!defined('_ENTITY_IS_RELATED'))
    define('_ENTITY_IS_RELATED', 'This departments has users');
if (!defined('_TYPE'))
    define('_TYPE', 'Type');

/*************************** Users - Entites management *****************/
if (!defined('_ENTITY_USER_DESC'))
    define('_ENTITY_USER_DESC', 'Attach users to departments');
if (!defined('_ENTITIES_USERS'))
    define('_ENTITIES_USERS', 'Users - Departments');
if (!defined('_ENTITIES_USERS_LIST'))
    define('_ENTITIES_USERS_LIST', 'List of user');
if (!defined('_USER_ENTITIES_TITLE'))
    define('_USER_ENTITIES_TITLE', 'The user belongs to following departments');
if (!defined('_USER_ENTITIES_ADDITION'))
    define('_USER_ENTITIES_ADDITION', 'Users - Departments');
if (!defined('_USER_BELONGS_NO_ENTITY'))
    define('_USER_BELONGS_NO_ENTITY', 'The user belongs to following departments');
if (!defined('_CHOOSE_ONE_ENTITY'))
    define('_CHOOSE_ONE_ENTITY', 'Select at least one department');
if (!defined('_CHOOSE_ENTITY'))
    define('_CHOOSE_ENTITY', 'Select a department');
if (!defined('_CHOOSE_PRIMARY_ENTITY'))
    define('_CHOOSE_PRIMARY_ENTITY', 'Choose as primary department');
if (!defined('_PRIMARY_ENTITY'))
    define('_PRIMARY_ENTITY', 'Primary department');
if (!defined('_DELETE_ENTITY'))
    define('_DELETE_ENTITY', 'Delete department');
if (!defined('USER_ADD_ENTITY'))
    define('USER_ADD_ENTITY', 'Add a department');
if (!defined('_ADD_TO_ENTITY'))
    define('_ADD_TO_ENTITY', 'Add to a department');
if (!defined('_NO_ENTITY_SELECTED'))
    define('_NO_ENTITY_SELECTED', 'No department selected');
if (!defined('_NO_PRIMARY_ENTITY'))
    define('_NO_PRIMARY_ENTITY', 'The primary department is mandatory');
if (!defined('_NO_ENTITIES_DEFINED_FOR_YOU'))
    define('_NO_ENTITIES_DEFINED_FOR_YOU', 'No department defined for this user');
if (!defined('_LABEL_MISSING'))
    define('_LABEL_MISSING', 'You must enter the name of the department');
if (!defined('_SHORT_LABEL_MISSING'))
    define('_SHORT_LABEL_MISSING', 'you must enter a shoirt name for the department');
if (!defined('_ID_MISSING'))
    define('_ID_MISSING', 'You must enter an identifier for the department');
if (!defined('_TYPE_MISSING'))
    define('_TYPE_MISSING', 'You must select the type of the department');
if (!defined('_PARENT_MISSING'))
    define('_PARENT_MISSING', 'You mus select the parent department');
if (!defined('_ENTITY_UNKNOWN'))
    define('_ENTITY_UNKNOWN', 'Unknown department');

/*************************** Entites form *****************/
if (!defined('_ENTITY_LABEL'))
    define('_ENTITY_LABEL', 'Name');
if (!defined('_SHORT_LABEL'))
    define('_SHORT_LABEL', 'Short name');
if (!defined('_ENTITY_ADR_1'))
    define('_ENTITY_ADR_1', 'Address 1');
if (!defined('_ENTITY_ADR_2'))
    define('_ENTITY_ADR_2', 'Address 2');
if (!defined('_ENTITY_ADR_3'))
    define('_ENTITY_ADR_3', 'Address 3');
if (!defined('_ENTITY_ZIPCODE'))
    define('_ENTITY_ZIPCODE', 'Postal code');
if (!defined('_ENTITY_CITY'))
    define('_ENTITY_CITY', 'Town');
if (!defined('_ENTITY_COUNTRY'))
    define('_ENTITY_COUNTRY', 'Country');
if (!defined('_ENTITY_EMAIL'))
    define('_ENTITY_EMAIL', 'Email');
if (!defined('_ENTITY_BUSINESS'))
    define('_ENTITY_BUSINESS', 'Registration number');
if (!defined('_ENTITY_PARENT'))
    define('_ENTITY_PARENT', 'Parent department');
if (!defined('_CHOOSE_ENTITY_PARENT'))
    define('_CHOOSE_ENTITY_PARENT', 'Select the parent department');
if (!defined('_CHOOSE_ENTITY_TYPE'))
    define('_CHOOSE_ENTITY_TYPE', 'Select the department');
if (!defined('_ENTITY_TYPE'))
    define('_ENTITY_TYPE', 'Type of entity');
if (!defined('_TO_USERS_OF_ENTITIES'))
    define('_TO_USERS_OF_ENTITIES', 'To users of department');
if (!defined('_ALL_ENTITIES'))
    define('_ALL_ENTITIES', 'All departments');
if (!defined('_ENTITIES_JUST_BELOW'))
    define('_ENTITIES_JUST_BELOW', 'Immediate children of parent department');
if (!defined('_ALL_ENTITIES_BELOW'))
    define('_ALL_ENTITIES_BELOW', 'Every children of parent department');
if (!defined('_ENTITIES_JUST_UP'))
    define('_ENTITIES_JUST_UP', 'Immediate parent of primary department');
if (!defined('_MY_ENTITIES'))
    define('_MY_ENTITIES', 'Every department of the user');
if (!defined('_MY_PRIMARY_ENTITY'))
    define('_MY_PRIMARY_ENTITY', 'Primary department');
if (!defined('_SAME_LEVEL_ENTITIES'))
    define('_SAME_LEVEL_ENTITIES', 'Every sister departments of primary one');
if (!defined('_INDEXING_ENTITIES'))
    define('_INDEXING_ENTITIES', 'Record for departments');
if (!defined('_SEARCH_DIFF_LIST'))
    define('_SEARCH_DIFF_LIST', 'Search a department or a user');
if (!defined('_ADD_CC'))
    define('_ADD_CC', 'Add in copy');
if (!defined('_TO_DEST'))
    define('_TO_DEST', 'To');
if (!defined('_NO_DIFF_LIST_ASSOCIATED'))
    define('_NO_DIFF_LIST_ASSOCIATED', 'No mailing list associated');
if (!defined('_PRINCIPAL_RECIPIENT'))
    define('_PRINCIPAL_RECIPIENT', 'Main recipient');
if (!defined('_ADD_COPY_IN_PROCESS'))
    define('_ADD_COPY_IN_PROCESS', 'Add users in copy');
if (!defined('_DIFF_LIST_COPY'))
    define('_DIFF_LIST_COPY', 'Mailing list');
if (!defined('_NO_COPY'))
    define('_NO_COPY', 'No user in copy');
if (!defined('_DIFF_LIST'))
    define('_DIFF_LIST', 'Mailing list');
if (!defined('_NO_USER'))
    define('_NO_USER', 'No user');
if (!defined('_MUST_CHOOSE_DEST'))
    define('_MUST_CHOOSE_DEST', 'You must choose the main recipient');
if (!defined('_ENTITIES__DEL'))
    define('_ENTITIES__DEL', 'Deletion');
if (!defined('_ENTITY_DELETION'))
    define('_ENTITY_DELETION', 'Entity deletion');
if (!defined('_THERE_ARE_NOW'))
    define('_THERE_ARE_NOW', 'There are now');
if (!defined('_DOC_IN_THE_DEPARTMENT'))
    define('_DOC_IN_THE_DEPARTMENT', 'documenys in the department');
if (!defined('_DEL_AND_REAFFECT'))
    define('_DEL_AND_REAFFECT', 'Delete and reaffect');
if (!defined('_THE_ENTITY'))
    define('_THE_ENTITY', 'The entity');

/******************** Keywords Helper ************/
if (!defined('_HELP_KEYWORD1'))
    define('_HELP_KEYWORD1', 'Every department the user belongs to. Does not include sub-departments');
if (!defined('_HELP_KEYWORD2'))
    define('_HELP_KEYWORD2', 'Primary department of the user');
if (!defined('_HELP_KEYWORD3'))
    define('_HELP_KEYWORD3', 'Sub-departments of the given list (the list can contain @my_entities or @my_primary_entity)');
if (!defined('_HELP_KEYWORD4'))
    define('_HELP_KEYWORD4', 'Parent of the given department');
if (!defined('_HELP_KEYWORD5'))
    define('_HELP_KEYWORD5', 'All departments at the same level of a given department');
if (!defined('_HELP_KEYWORD6'))
    define('_HELP_KEYWORD6', 'All active departments');
if (!defined('_HELP_KEYWORD7'))
    define('_HELP_KEYWORD7', 'Immediate sub-department of given departments');
if (!defined('_HELP_KEYWORDS'))
    define('_HELP_KEYWORDS', 'Help for keywords');
if (!defined('_HELP_KEYWORD_EXEMPLE_TITLE'))
    define('_HELP_KEYWORD_EXEMPLE_TITLE', 'Example of security clause ("where clause"): granting access to ressources of the primary department of the user and its sub-departments.');
if (!defined('_HELP_KEYWORD_EXEMPLE'))
    define('_HELP_KEYWORD_EXEMPLE', 'where_clause : (DESTINATION = @my_primary_entity or DESTINATION in (@subentities[@my_primary_entity])');
if (!defined('_HELP_BY_ENTITY'))
    define('_HELP_BY_ENTITY', 'Keywords defined by module "Entity"');

/******************** For reports ************/
if (!defined('_ENTITY_VOL_STAT'))
    define('_ENTITY_VOL_STAT', 'Volume des courriers par entit&eacute;');
if (!defined('_ENTITY_VOL_STAT_DESC'))
    define('_ENTITY_VOL_STAT', 'Volume des courriers par entit&eacute;');
if (!defined('_NO_DATA_MESSAGE'))
    define('_NO_DATA_MESSAGE', 'Pas assez de données');
if (!defined('_MAIL_VOL_BY_ENT_REPORT'))
    define('_MAIL_VOL_BY_ENT_REPORT', 'Volume de courrier par service');
if (!defined('_WRONG_DATE_FORMAT'))
    define('_WRONG_DATE_FORMAT', 'Format de date incorrect');
if (!defined('_ENTITY_PROCESS_DELAY'))
    define('_ENTITY_PROCESS_DELAY', 'D&eacute;lai moyen de traitement par entit&eacute;');
if (!defined('_ENTITY_LATE_MAIL'))
    define('_ENTITY_LATE_MAIL', 'Volume de courrier en retard par entit&eacute;');

/******************** Action put in copy ************/
if (!defined('_ADD_COPY_FOR_DOC'))
    define('_ADD_COPY_FOR_DOC', 'Put in copy for the resource');
if (!defined('_VALIDATE_PUT_IN_COPY'))
    define('_VALIDATE_PUT_IN_COPY', 'Validate the list');

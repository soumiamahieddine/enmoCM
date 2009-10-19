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
define('_ADD_ENTITY','Department added');
define('_ENTITY_ADDITION', 'Add a department');
define('_ENTITY_MODIFICATION', 'Modify a department');
define('_ENTITY_AUTORIZATION', 'Enable a department');
define('_ENTITY_SUSPENSION', 'disable a department');
define('_ENTITY_DELETION', 'Delete a department');
define('_ENTITY_DELETED', 'Department deleted');
define('_ENTITY_UPDATED', 'Department modified');
define('_ENTITY_AUTORIZED', 'Department enabled');
define('_ENTITY_SUSPENDED', 'department disabled');
define('_ENTITY', 'Department');
define('_ENTITIES', 'Departments');
define('_ENTITIES_COMMENT', 'Department');
define('_ALL_ENTITIES', 'All departments');
define('_ENTITIES_LIST', 'List of department');
define('_MANAGE_ENTITIES', 'Manage departments');
define('_MANAGE_ENTITIES_DESC', 'Manage depoartments');
define('_ENTITY_MISSING', 'This department does not exist');
define('_ENTITY_TREE', 'Department tree-view');
define('_ENTITY_TREE_DESC', 'Department tree-view');
define('_ENTITY_HAVE_CHILD', 'This department has sub-departments');
define('_ENTITY_IS_RELATED', 'This departments has users');
define('_TYPE', 'Type');

/*************************** Users - Entites management *****************/
define('_ENTITY_USER_DESC', 'Attach users to departments');
define('_ENTITIES_USERS', 'Users - Departments');
define('_ENTITIES_USERS_LIST', 'List of user');
define('_USER_ENTITIES_TITLE', 'The user belongs to following departments');
define('_USER_ENTITIES_ADDITION', 'Users - Departments');
define('_USER_BELONGS_NO_ENTITY', 'The user belongs to following departments');
define('_CHOOSE_ONE_ENTITY', 'Select at least one department');
define('_CHOOSE_ENTITY', 'Select a department');
define('_CHOOSE_PRIMARY_ENTITY', 'Choose as primary department');
define('_PRIMARY_ENTITY', 'Primary department');
define('_DELETE_ENTITY', 'Delete department');
define('USER_ADD_ENTITY', 'Add a department');
define('_ADD_TO_ENTITY', 'Add to a department');
define('_NO_ENTITY_SELECTED', 'No department selected');
define('_NO_PRIMARY_ENTITY', 'The primary department is mandatory');
define('_NO_ENTITIES_DEFINED_FOR_YOU', 'No department defined for this user');
define('_LABEL_MISSING', 'You must enter the name of the department');
define('_SHORT_LABEL_MISSING', 'you must enter a shoirt name for the department');
define('_ID_MISSING', 'You must enter an identifier for the department');
define('_TYPE_MISSING', 'You must select the type of the department');
define('_PARENT_MISSING', 'You mus select the parent department');
define('_ENTITY_UNKNOWN', 'Unknown department');

/*************************** Entites form *****************/
define('_ENTITY_LABEL', 'Name');
define('_SHORT_LABEL', 'Short name');
define('_ENTITY_ADR_1', 'Address 1');
define('_ENTITY_ADR_2', 'Address 2');
define('_ENTITY_ADR_3', 'Address 3');
define('_ENTITY_ZIPCODE', 'Postal code');
define('_ENTITY_CITY', 'Town');
define('_ENTITY_COUNTRY', 'Country');
define('_ENTITY_EMAIL', 'Email');
define('_ENTITY_BUSINESS', 'Registration number');
define('_ENTITY_PARENT', 'Parent department');
define('_CHOOSE_ENTITY_PARENT', 'Select the parent department');
define('_CHOOSE_ENTITY_TYPE', 'Select the department');
define('_ENTITY_TYPE', 'Type of entity');

define('_TO_USERS_OF_ENTITIES', 'To users of department');
define('_ALL_ENTITIES', 'All departments');
define('_ENTITIES_JUST_BELOW', 'Immediate children of parent department');
define('_ALL_ENTITIES_BELOW', 'Every children of parent department');
define('_ENTITIES_JUST_UP', 'Immediate parent of primary department');
define('_MY_ENTITIES', 'Every department of the user');
define('_MY_PRIMARY_ENTITY', 'Primary department');
define('_SAME_LEVEL_ENTITIES', 'Every sister departments of primary one');

define('_INDEXING_ENTITIES', 'Record for departments');
define('_SEARCH_DIFF_LIST', 'search a department or a user');
define('_ADD_CC', 'add in copy');
define('_TO_DEST', 'To');

define('_NO_DIFF_LIST_ASSOCIATED', 'No mailing list associated');
define('_PRINCIPAL_RECIPIENT', 'Main recipient');
define('_ADD_COPY_IN_PROCESS', 'Add users in copy');
define('_DIFF_LIST_COPY', 'Liste de diffusion, copies');
define('_NO_COPY', 'no user in copy');
define('_DIFF_LIST', 'mailing list');

define('_NO_USER', 'No user');


/******************** Keywords Helper ************/
define('_HELP_KEYWORD1', 'Every department the user belongs to. Does not include sub-departments');
define('_HELP_KEYWORD2', 'Primary department of the user');
define('_HELP_KEYWORD3', 'Sub-departments of the given list (the list can contain @my_entities or @my_primary_entity)');
define('_HELP_KEYWORD4', 'Parent of the given department');
define('_HELP_KEYWORD5', 'All departments at the same level of a given department');
define('_HELP_KEYWORD6', 'All active departments');
define('_HELP_KEYWORD7', 'Immediate sub-department of given departments');
define('_HELP_KEYWORDS', 'Help for keywords');
define('_HELP_KEYWORD_EXEMPLE_TITLE', 'Example of security clause ("where clause"): granting access to ressources of the primary department of the user and its sub-departments.');
define('_HELP_KEYWORD_EXEMPLE', 'where_clause : (DESTINATION = @my_primary_entity or DESTINATION in (@subentities[@my_primary_entity])');
define('_HELP_BY_ENTITY', 'Keywords defined by module "Entity"');

?>

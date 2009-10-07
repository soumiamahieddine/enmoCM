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

define('_ADMIN_BASKETS', 'Basket');
define('_ADMIN_BASKETS_DESC', 'Define basket contents and associate them with user groups. List available redirectionfor a user group. Associate the basket with result pages.');
define('_USE_BASKETS', 'Utiliser les corbeilles');
define('_DIFFUSION_LIST', 'Liste de diffusion');

//class basket
define('_BASKET', 'Basket');
define('_THE_BASKET', 'The basket ');
define('_THE_ID', 'the ID ');
define('_THE_DESC', 'The description ');
define('_BELONGS_TO_NO_GROUP', 'is not associated with any group');
define('_SYSTEM_BASKET_MESSAGE', 'This is a system basket. You can\'t modify the table or the where clause. They are only given as information.');
define('_BASKET_MISSING', 'The basket doesn\'t exists');
define('_BASKET_UPDATED', 'Basket updated');
define('_BASKET_UPDATE', 'Basket modified');
define('_BASKET_ADDED', 'Basket added');
define('_DELETED_BASKET', 'Basket deleted');
define('_BASKET_DELETION', 'Deleting basket');
define('_BASKET_AUTORIZATION', 'Enabling basket');
define('_BASKET_SUSPENSION', 'Disabling basket');
define('_AUTORIZED_BASKET', 'Enabled basket');
define('_SUSPENDED_BASKET', 'Disabled basket');
define('_NO_BASKET_DEFINED_FOR_YOU', 'No basket defined for this user');


define('_BASKETS_LIST', 'Basket list');

/////// frame corbeilles
define('_BASKETS', 'Baskets');
define('_CHOOSE_BASKET', 'Select a basket');

define('_MANAGE_BASKETS', 'Baskets');
define('_MANAGE_BASKETS_APP', 'Manage baskets');

/************** Corbeille : Liste + Formulaire**************/
define('_ALL_BASKETS', 'All baskets');
define('_BASKET_LIST', 'Basket list');
define('_ADD_BASKET', 'Add a basket');
define('_BASKET_ADDITION', 'Add a basket');
define('_BASKET_MODIFICATION', 'Modify a basket');
define('_BASKET_VIEW', 'View on the table');
define('_MODIFY_BASKET', 'Modify a baskete');
define('_ADD_A_NEW_BASKET', 'Add a basket');
define('_ADD_A_GROUP_TO_BASKET', 'Associate a group to the basket');
define('_DEL_GROUPS', 'Remove groups');
define('_BASKET_NOT_USABLE', 'No group associated (the basket cannot be used)');
define('_ASSOCIATED_GROUP', 'Groups associated to the basket');
define('_BASKETS', 'Basket(s)');

define('_TITLE_GROUP_BASKET', 'Associate a group to the b asket');
define('_ADD_TO_BASKET', 'Associate a group');
define('_TO_THE_GROUP', 'to the basket');
define('_ALLOWED_ACTIONS', 'Enabled actions');
define('_SERVICES_BASKETS', 'Department basket');
define('_USERGROUPS_BASKETS', 'Usergroup basket');
define('_BASKET_RESULT_PAGE', 'Result list');
define('_ADD_THIS_GROUP', 'Add a group');
define('_MODIFY_THIS_GROUP', 'Modify the group');
define('_DEFAULT_ACTION_LIST', 'Default action on the list<br/><i>(clic on the line)');
define('_NO_ACTION_DEFINED', 'No define action');

//BASKETS
define('_PROCESS_FOLDER_LIST', 'Processed folder list');
define('_INCOMPLETE_FOLDERS_LIST', 'Incomplete folder list');
define('_WAITING_VAL_LIST', 'List of document awaiting approval');
define('_WAITING_QUAL_LIST', 'List of document awaiting qualification');
define('_WAITING_DISTRIB_LIST', 'list of mail awaiting distribution');
define('_NO_REDIRECT_RIGHT', 'You have no redirection right on this basket');
define('_CLICK_LINE_BASKET1', 'Clic on a line to qualify a document');

//ENTITY
define('_SELECT_ENTITY', 'Select an entity');
define('_ENTITY', 'Entity');
define('_LABEL', 'Label');
define('_THE_ENTITY', 'The entity');
define('_ENTITIES', 'Entities');
define('_ALL_ENTITIES', 'All entities');
define('_ENTITY_LIST', 'List of entities');
define('_SELECTED_ENTITIES', 'Selected entities');
define('_CHOOSE_ENTITY', 'Select an entity');
define('_MUST_CHOOSE_AN_ENTITY', 'You must select an entity');
define('_ADMIN_ENTITIES', 'Manage entities');
define('_ADMIN_ENTITIES_DESC', 'Administration des services et des listes de diffusion associ&eacute;es');
define('_ENTITIES_LIST', 'Liste des services');
define('_ENTITY_ADDITION', 'Ajout d&rsquo;un service');
define('_ENTITY_MODIFICATION', 'Modification d&rsquo;un service');
define('_ENTITY_MISSING', 'Le service n&rsquo;existe pas');
define('_ENTITY_DELETION', 'Suppression d&rsquo;un service');
define('_ENTITY_ADDITION', 'Ajout d&rsquo;un service');
define('_ENTITY_ADDED', 'Service ajout&eacute;');
define('_ENTITY_UPDATED', 'Service modifi&eacute;');
define('_ENTITY_BASKETS','Services disponibles');
define('_PRINT_ENTITY_SEP','Imprimer le s&eacute;parateur de documents');
define('_PRINT_SEP_WILL_BE_START','L&rsquo;impression va d&eacute;marrer');
define('_PRINT_SEP_TITLE','SEPARATEUR DE DOCUMENTS');
define('_INGOING_UP','ARRIVEE');
define('_ONGOING_UP','DEPART');

//DIFFUSION LIST
define('_CHOOSE_DEPARTMENT_FIRST', 'Vous devez d&rsquo;abord choisir un service avant de pouvoir acc&eacute;der &agrave; la liste diffusion');
define('_NO_LIST_DEFINED__FOR_THIS_MAIL', 'Aucune liste n&rsquo;est d&eacute;finie pour ce courrier');
define('_NO_LIST_DEFINED__FOR_THIS_DEPARTMENT', 'Aucune liste n&rsquo;est d&eacute;finie pour ce service');
define('_NO_LIST_DEFINED', 'Pas de liste d&eacute;finie');
define('_REDIRECT_MAIL', 'Redirection du document');
define('_DISTRIBUTE_MAIL', 'Ventilation du document');
define('_REDIRECT_TO_OTHER_DEP', 'Rediriger vers un autre service');
define('_REDIRECT_TO_USER', 'Rediriger vers un utilisateur');
define('_LETTER_SERVICE_REDIRECT','Rediriger vers le service &eacute;metteur');
define('_LETTER_SERVICE_REDIRECT_VALIDATION','Souhaitez-vous vraiment rediriger vers le service &eacute;metteur');
define('_DOC_REDIRECT_TO_SENDER_ENTITY', 'Document redirig&eacute; vers service &eacute;metteur');
define('_DOC_REDIRECT_TO_ENTITY', 'Document redirig&eacute; vers service');
define('_DOC_REDIRECT_TO_USER', 'Document redirig&eacute; vers utilisateur');

define('_WELCOME_DIFF_LIST', 'Bienvenue dans l&rsquo;outil de diffusion de courrier');
define('_START_DIFF_EXPLANATION', 'Pour demarrer la diffusion, utilisez la navigation par service ou par utilisateur ci-dessus');
define('_CLICK_ON', 'cliquez sur');
define('_ADD_USER_TO_LIST_EXPLANATION', 'Pour ajouter un utilisateur &agrave; la liste de diffusion');
define('_REMOVE_USER_FROM_LIST_EXPLANATION', 'Pour retirer l&rsquo;utilisateur &agrave; cette liste de diffusion');
define('_TO_MODIFY_LIST_ORDER_EXPLANATION', 'Pour modifier l&rsquo;ordre d&rsquo;attribution d&rsquo;un courrier aux utilisateurs, utilisez les ic&ocirc;nes');
define('_AND', ' et ' );
define('_LINKED_DIFF_LIST', 'Liste de diffusion associ&eacute;e');
define('_NO_LINKED_DIFF_LIST', 'Pas de liste associ&eacute;e');
define('_CREATE_LIST', 'Cr&eacute;er une liste de diffusion');
define('_MODIFY_LIST', 'Modifier la liste');
define('_THE_ENTITY_DO_NOT_CONTAIN_DIFF_LIST', 'Le service s&eacute;lectionn&eacute; n&rsquo;a pas de mod&egrave;le de liste de diffusion associ&eacute;e');

//LIST MODEL
define('_MANAGE_MODEL_LIST_TITLE', 'Cr&eacute;ation / Modification Mod&egrave;le de liste de diffusion');
define('_SORT_BY', 'Trier par');
define('_WELCOME_MODEL_LIST_TITLE', 'Bienvenue dans l&rsquo;outil de cr&eacute;tion de mod&egrave;le de liste de diffusion');
define('_MODEL_LIST_EXPLANATION1', 'Pour d&eacute;marrer la cr&eacute;tion, utilisez la navigation par service ou par utilisateur cidessus');
define('_VALID_LIST', 'Valider la liste');

//LIST
define('_COPY_LIST', 'Liste des documents en copie');
define('_PROCESS_LIST', 'Liste des documents &agrave; traiter');
define('_CLICK_LINE_TO_VIEW', 'Cliquez sur une ligne pour visualiser');
define('_CLICK_LINE_TO_PROCESS', 'Cliquez sur une ligne pour traiter');

define('_REDIRECT_TO_SENDER_ENTITY', 'Redirection vers le service &eacute;metteur');
define('_CHOOSE_DEPARTMENT', 'Choisissez un service');
define('_REDIRECTION', 'Redirection');
define('_ENTITY_UPDATE', 'Service mis &agrave; jour');
// USER ABS
define('_MY_ABS', 'G&eacute;rer mes absences');
define('_MY_ABS_TXT', 'Permet de rediriger votre courrier en attente en cas de d&eacute;part en cong&eacute;.');
define('_MY_ABS_REDIRECT', 'Vos courriers sont actuellement redirig&eacute;s vers');
define('_MY_ABS_DEL', 'Pour supprimer la redirection, cliquez ici pour stopper');
define('_ADMIN_ABS', 'G&eacute;rer les absences.');
define('_ADMIN_ABS_TXT', 'Permet de rediriger le courrier de l&rsquo;utilisateur en attente en cas de d&eacute;part en cong&eacute;.');
define('_ADMIN_ABS_REDIRECT', 'Redirection d&rsquo;absence en cours.');
define('_ADMIN_ABS_FIRST_PART', 'Les courrier de');
define('_ADMIN_ABS_SECOND_PART', 'sont actuellement redirig&eacute;s vers ');
define('_ADMIN_ABS_THIRD_PART', '. Cliquez ici pour supprimer la redirection.');
define('_ACTIONS_DONE', 'Actions effectu&eacute;es le');
define('_PROCESSED_MAIL', 'Courriers trait&eacute;s');
define('_INDEXED_MAIL', 'Courriers index&eacute;s');
define('_REDIRECTED_MAIL', 'Courriers redirig&eacute;s');
define('_PROCESS_MAIL_OF', 'Courrier &agrave; traiter de');
define('_MISSING', 'Absent');
define('_BACK_FROM_VACATION', ' de retour');
define('_MISSING_ADVERT_TITLE','Gestion des absences');
define('_MISSING_ADVERT_01','Ce compte est actuellement d&eacute;finit en mode &rsquo;absent&rsquo; et les courriers sont redirig&eacute;s vers un autre utilisateur.');
define('_MISSING_ADVERT_02','Si vous desirez vous connecter avec ce compte, le mode &rsquo;absent&rsquo; sera alors supprim&eacute;.<br/> La redirection des courriers arrivera &agrave; son terme et l&rsquo;application sera r&eacute;activ&eacute;e');
define('_MISSING_CHOOSE','Souhaitez-vous continuer?');


define('_CHOOSE_PERSON_TO_REDIRECT', 'Choisissez la personne vers qui vous souhaitez rediriger ce courrier dans la liste ci-dessus');
define('_CLICK_ON_THE_LINE_OR_ICON', 'Il vous suffit de cliquer sur la ligne ou sur l&rsquo;ic&ocirc;ne');
define('_TO_SELECT_USER', 'pour s&eacute;lectionner un utilisateur');

define('_DIFFUSION_DISTRIBUTION', 'Diffusion et distribution du courrier');
define('_VALIDATED_ANSWERS', 'DGS R&eacute;ponses valid&eacute;es');
define('_REJECTED_ANSWERS', 'DGS R&eacute;ponses rejet&eacute;es');
define('_MUST_HAVE_DIFF_LIST', 'Vous devez d&eacute;finir une liste de diffusion');


define('_ASSOCIATED_STATUS', 'Associated status');
define('_SYSTEM_ACTION', 'System action');
define('_CANNOT_MODIFY_STATUS', 'You cannot modify the status');
define('_ASSOCIATED_ACTIONS', 'No available action on the result page');
define('_NO_ACTIONS_DEFINED', 'No defined action');
define('_CONFIG', '(configure)');
define('_CONFIG_ACTION', 'Action parameters');
define('_WHERE_CLAUSE_ACTION_TEXT', 'You can define a condition to choose wether the action apprear or not with the where clause (optional):');
define('_IN_ACTION', ' in the action');

define('_TO_ENTITIES', 'Vers des services');
define('_TO_USERGROUPS', 'To usergroups');
define('_USE_IN_MASS', 'Available action in the list.');
define('_USE_ONE', 'Available action in the action page');
define('_MUST_CHOOSE_WHERE_USE_ACTION','You must select where the action can be used ');

define('_MUST_CHOOSE_DEP', 'You must select a entity!');
define('_MUST_CHOOSE_USER', 'You must select an user!');
define('_REDIRECT_TO_DEP_OK', 'Redirection vers un service effectu&eacute;e');
define('_REDIRECT_TO_USER_OK', 'Redirection vers un utilisateur effectu&eacute;e');

define('_SAVE_CHANGES', 'Save changes');
define('_VIEW_BASKETS', 'My baskets');
define('_VIEW_BASKETS_TITLE', 'My baskets');

define('_INVOICE_LIST_TO_VAL', 'Invoices to approve');
define('_POSTINDEXING_LIST', 'Documents to check');
define('_MY_BASKETS', 'My baskets');
?>

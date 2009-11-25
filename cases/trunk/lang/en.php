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

if (!defined('_ADMIN_BASKETS')) define('_ADMIN_BASKETS', 'Basket');
if (!defined('_ADMIN_BASKETS_DESC')) define('_ADMIN_BASKETS_DESC', 'Define basket contents and associate them with user groups. List available redirectionfor a user group. Associate the basket with result pages.');
if (!defined('_USE_BASKETS')) define('_USE_BASKETS', 'Utiliser les corbeilles');
if (!defined('_DIFFUSION_LIST')) define('_DIFFUSION_LIST', 'Liste de diffusion');

//class basket
if (!defined('_BASKET')) define('_BASKET', 'Basket');
if (!defined('_THE_BASKET')) define('_THE_BASKET', 'The basket ');
if (!defined('_THE_ID')) define('_THE_ID', 'the ID ');
if (!defined('_THE_DESC')) define('_THE_DESC', 'The description ');
if (!defined('_BELONGS_TO_NO_GROUP')) define('_BELONGS_TO_NO_GROUP', 'is not associated with any group');
if (!defined('_SYSTEM_BASKET_MESSAGE')) define('_SYSTEM_BASKET_MESSAGE', 'This is a system basket. You can\'t modify the table or the where clause. They are only given as information.');
if (!defined('_BASKET_MISSING')) define('_BASKET_MISSING', 'The basket doesn\'t exists');
if (!defined('_BASKET_UPDATED')) define('_BASKET_UPDATED', 'Basket updated');
if (!defined('_BASKET_UPDATE')) define('_BASKET_UPDATE', 'Basket modified');
if (!defined('_BASKET_ADDED')) define('_BASKET_ADDED', 'Basket added');
if (!defined('_DELETED_BASKET')) define('_DELETED_BASKET', 'Basket deleted');
if (!defined('_BASKET_DELETION')) define('_BASKET_DELETION', 'Deleting basket');
if (!defined('_BASKET_AUTORIZATION')) define('_BASKET_AUTORIZATION', 'Enabling basket');
if (!defined('_BASKET_SUSPENSION')) define('_BASKET_SUSPENSION', 'Disabling basket');
if (!defined('_AUTORIZED_BASKET')) define('_AUTORIZED_BASKET', 'Enabled basket');
if (!defined('_SUSPENDED_BASKET')) define('_SUSPENDED_BASKET', 'Disabled basket');
if (!defined('_NO_BASKET_DEFINED_FOR_YOU')) define('_NO_BASKET_DEFINED_FOR_YOU', 'No basket defined for this user');


if (!defined('_BASKETS_LIST')) define('_BASKETS_LIST', 'Basket list');

/////// frame corbeilles
if (!defined('_BASKETS')) define('_BASKETS', 'Baskets');
if (!defined('_CHOOSE_BASKET')) define('_CHOOSE_BASKET', 'Select a basket');

if (!defined('_MANAGE_BASKETS')) define('_MANAGE_BASKETS', 'Baskets');
if (!defined('_MANAGE_BASKETS_APP')) define('_MANAGE_BASKETS_APP', 'Manage baskets');

/************** Corbeille : Liste + Formulaire**************/
if (!defined('_ALL_BASKETS')) define('_ALL_BASKETS', 'All baskets');
if (!defined('_BASKET_LIST')) define('_BASKET_LIST', 'Basket list');
if (!defined('_ADD_BASKET')) define('_ADD_BASKET', 'Add a basket');
if (!defined('_BASKET_ADDITION')) define('_BASKET_ADDITION', 'Add a basket');
if (!defined('_BASKET_MODIFICATION')) define('_BASKET_MODIFICATION', 'Modify a basket');
if (!defined('_BASKET_VIEW')) define('_BASKET_VIEW', 'View on the table');
if (!defined('_MODIFY_BASKET')) define('_MODIFY_BASKET', 'Modify a baskete');
if (!defined('_ADD_A_NEW_BASKET')) define('_ADD_A_NEW_BASKET', 'Add a basket');
if (!defined('_ADD_A_GROUP_TO_BASKET')) define('_ADD_A_GROUP_TO_BASKET', 'Associate a group to the basket');
if (!defined('_DEL_GROUPS')) define('_DEL_GROUPS', 'Remove groups');
if (!defined('_BASKET_NOT_USABLE')) define('_BASKET_NOT_USABLE', 'No group associated (the basket cannot be used)');
if (!defined('_ASSOCIATED_GROUP')) define('_ASSOCIATED_GROUP', 'Groups associated to the basket');
if (!defined('_BASKETS')) define('_BASKETS', 'Basket(s)');

if (!defined('_TITLE_GROUP_BASKET')) define('_TITLE_GROUP_BASKET', 'Associate a group to the b asket');
if (!defined('_ADD_TO_BASKET')) define('_ADD_TO_BASKET', 'Associate a group');
if (!defined('_TO_THE_GROUP')) define('_TO_THE_GROUP', 'to the basket');
if (!defined('_ALLOWED_ACTIONS')) define('_ALLOWED_ACTIONS', 'Enabled actions');
if (!defined('_SERVICES_BASKETS')) define('_SERVICES_BASKETS', 'Department basket');
if (!defined('_USERGROUPS_BASKETS')) define('_USERGROUPS_BASKETS', 'Usergroup basket');
if (!defined('_BASKET_RESULT_PAGE')) define('_BASKET_RESULT_PAGE', 'Result list');
if (!defined('_ADD_THIS_GROUP')) define('_ADD_THIS_GROUP', 'Add a group');
if (!defined('_MODIFY_THIS_GROUP')) define('_MODIFY_THIS_GROUP', 'Modify the group');
if (!defined('_DEFAULT_ACTION_LIST')) define('_DEFAULT_ACTION_LIST', 'Default action on the list<br/><i>(clic on the line)');
if (!defined('_NO_ACTION_DEFINED')) define('_NO_ACTION_DEFINED', 'No define action');

//BASKETS
if (!defined('_PROCESS_FOLDER_LIST')) define('_PROCESS_FOLDER_LIST', 'Processed folder list');
if (!defined('_INCOMPLETE_FOLDERS_LIST')) define('_INCOMPLETE_FOLDERS_LIST', 'Incomplete folder list');
if (!defined('_WAITING_VAL_LIST')) define('_WAITING_VAL_LIST', 'List of document awaiting approval');
if (!defined('_WAITING_QUAL_LIST')) define('_WAITING_QUAL_LIST', 'List of document awaiting qualification');
if (!defined('_WAITING_DISTRIB_LIST')) define('_WAITING_DISTRIB_LIST', 'list of mail awaiting distribution');
if (!defined('_NO_REDIRECT_RIGHT')) define('_NO_REDIRECT_RIGHT', 'You have no redirection right on this basket');
if (!defined('_CLICK_LINE_BASKET1')) define('_CLICK_LINE_BASKET1', 'Clic on a line to qualify a document');

//ENTITY
if (!defined('_SELECT_ENTITY')) define('_SELECT_ENTITY', 'Select an entity');
if (!defined('_ENTITY')) define('_ENTITY', 'Entity');
if (!defined('_LABEL')) define('_LABEL', 'Label');
if (!defined('_THE_ENTITY')) define('_THE_ENTITY', 'The entity');
if (!defined('_ENTITIES')) define('_ENTITIES', 'Entities');
if (!defined('_ALL_ENTITIES')) define('_ALL_ENTITIES', 'All entities');
if (!defined('_ENTITY_LIST')) define('_ENTITY_LIST', 'List of entities');
if (!defined('_SELECTED_ENTITIES')) define('_SELECTED_ENTITIES', 'Selected entities');
if (!defined('_CHOOSE_ENTITY')) define('_CHOOSE_ENTITY', 'Select an entity');
if (!defined('_MUST_CHOOSE_AN_ENTITY')) define('_MUST_CHOOSE_AN_ENTITY', 'You must select an entity');
if (!defined('_ADMIN_ENTITIES')) define('_ADMIN_ENTITIES', 'Manage entities');
if (!defined('_ADMIN_ENTITIES_DESC')) define('_ADMIN_ENTITIES_DESC', 'Administration des services et des listes de diffusion associ&eacute;es');
if (!defined('_ENTITIES_LIST')) define('_ENTITIES_LIST', 'Liste des services');
if (!defined('_ENTITY_ADDITION')) define('_ENTITY_ADDITION', 'Ajout d&rsquo;un service');
if (!defined('_ENTITY_MODIFICATION')) define('_ENTITY_MODIFICATION', 'Modification d&rsquo;un service');
if (!defined('_ENTITY_MISSING')) define('_ENTITY_MISSING', 'Le service n&rsquo;existe pas');
if (!defined('_ENTITY_DELETION')) define('_ENTITY_DELETION', 'Suppression d&rsquo;un service');
if (!defined('_ENTITY_ADDITION')) define('_ENTITY_ADDITION', 'Ajout d&rsquo;un service');
if (!defined('_ENTITY_ADDED')) define('_ENTITY_ADDED', 'Service ajout&eacute;');
if (!defined('_ENTITY_UPDATED')) define('_ENTITY_UPDATED', 'Service modifi&eacute;');
if (!defined('_ENTITY_BASKETS')) define('_ENTITY_BASKETS','Services disponibles');
if (!defined('_PRINT_ENTITY_SEP')) define('_PRINT_ENTITY_SEP','Imprimer le s&eacute;parateur de documents');
if (!defined('_PRINT_SEP_WILL_BE_START')) define('_PRINT_SEP_WILL_BE_START','L&rsquo;impression va d&eacute;marrer');
if (!defined('_PRINT_SEP_TITLE')) define('_PRINT_SEP_TITLE','SEPARATEUR DE DOCUMENTS');
if (!defined('_INGOING_UP')) define('_INGOING_UP','ARRIVEE');
if (!defined('_ONGOING_UP')) define('_ONGOING_UP','DEPART');

//DIFFUSION LIST
if (!defined('_CHOOSE_DEPARTMENT_FIRST')) define('_CHOOSE_DEPARTMENT_FIRST', 'Vous devez d&rsquo;abord choisir un service avant de pouvoir acc&eacute;der &agrave; la liste diffusion');
if (!defined('_NO_LIST_DEFINED__FOR_THIS_MAIL')) define('_NO_LIST_DEFINED__FOR_THIS_MAIL', 'Aucune liste n&rsquo;est d&eacute;finie pour ce courrier');
if (!defined('_NO_LIST_DEFINED__FOR_THIS_DEPARTMENT')) define('_NO_LIST_DEFINED__FOR_THIS_DEPARTMENT', 'Aucune liste n&rsquo;est d&eacute;finie pour ce service');
if (!defined('_NO_LIST_DEFINED')) define('_NO_LIST_DEFINED', 'Pas de liste d&eacute;finie');
if (!defined('_REDIRECT_MAIL')) define('_REDIRECT_MAIL', 'Redirection du document');
if (!defined('_DISTRIBUTE_MAIL')) define('_DISTRIBUTE_MAIL', 'Ventilation du document');
if (!defined('_REDIRECT_TO_OTHER_DEP')) define('_REDIRECT_TO_OTHER_DEP', 'Rediriger vers un autre service');
if (!defined('_REDIRECT_TO_USER')) define('_REDIRECT_TO_USER', 'Rediriger vers un utilisateur');
if (!defined('_LETTER_SERVICE_REDIRECT')) define('_LETTER_SERVICE_REDIRECT','Rediriger vers le service &eacute;metteur');
if (!defined('_LETTER_SERVICE_REDIRECT_VALIDATION')) define('_LETTER_SERVICE_REDIRECT_VALIDATION','Souhaitez-vous vraiment rediriger vers le service &eacute;metteur');
if (!defined('_DOC_REDIRECT_TO_SENDER_ENTITY')) define('_DOC_REDIRECT_TO_SENDER_ENTITY', 'Document redirig&eacute; vers service &eacute;metteur');
if (!defined('_DOC_REDIRECT_TO_ENTITY')) define('_DOC_REDIRECT_TO_ENTITY', 'Document redirig&eacute; vers service');
if (!defined('_DOC_REDIRECT_TO_USER')) define('_DOC_REDIRECT_TO_USER', 'Document redirig&eacute; vers utilisateur');

if (!defined('_WELCOME_DIFF_LIST')) define('_WELCOME_DIFF_LIST', 'Bienvenue dans l&rsquo;outil de diffusion de courrier');
if (!defined('_START_DIFF_EXPLANATION')) define('_START_DIFF_EXPLANATION', 'Pour demarrer la diffusion, utilisez la navigation par service ou par utilisateur ci-dessus');
if (!defined('_CLICK_ON')) define('_CLICK_ON', 'cliquez sur');
if (!defined('_ADD_USER_TO_LIST_EXPLANATION')) define('_ADD_USER_TO_LIST_EXPLANATION', 'Pour ajouter un utilisateur &agrave; la liste de diffusion');
if (!defined('_REMOVE_USER_FROM_LIST_EXPLANATION')) define('_REMOVE_USER_FROM_LIST_EXPLANATION', 'Pour retirer l&rsquo;utilisateur &agrave; cette liste de diffusion');
if (!defined('_TO_MODIFY_LIST_ORDER_EXPLANATION')) define('_TO_MODIFY_LIST_ORDER_EXPLANATION', 'Pour modifier l&rsquo;ordre d&rsquo;attribution d&rsquo;un courrier aux utilisateurs, utilisez les ic&ocirc;nes');
if (!defined('_AND')) define('_AND', ' et ' );
if (!defined('_LINKED_DIFF_LIST')) define('_LINKED_DIFF_LIST', 'Liste de diffusion associ&eacute;e');
if (!defined('_NO_LINKED_DIFF_LIST')) define('_NO_LINKED_DIFF_LIST', 'Pas de liste associ&eacute;e');
if (!defined('_CREATE_LIST')) define('_CREATE_LIST', 'Cr&eacute;er une liste de diffusion');
if (!defined('_MODIFY_LIST')) define('_MODIFY_LIST', 'Modifier la liste');
if (!defined('_THE_ENTITY_DO_NOT_CONTAIN_DIFF_LIST')) define('_THE_ENTITY_DO_NOT_CONTAIN_DIFF_LIST', 'Le service s&eacute;lectionn&eacute; n&rsquo;a pas de mod&egrave;le de liste de diffusion associ&eacute;e');

//LIST MODEL
if (!defined('_MANAGE_MODEL_LIST_TITLE')) define('_MANAGE_MODEL_LIST_TITLE', 'Cr&eacute;ation / Modification Mod&egrave;le de liste de diffusion');
if (!defined('_SORT_BY')) define('_SORT_BY', 'Trier par');
if (!defined('_WELCOME_MODEL_LIST_TITLE')) define('_WELCOME_MODEL_LIST_TITLE', 'Bienvenue dans l&rsquo;outil de cr&eacute;tion de mod&egrave;le de liste de diffusion');
if (!defined('_MODEL_LIST_EXPLANATION1')) define('_MODEL_LIST_EXPLANATION1', 'Pour d&eacute;marrer la cr&eacute;tion, utilisez la navigation par service ou par utilisateur cidessus');
if (!defined('_VALID_LIST')) define('_VALID_LIST', 'Valider la liste');

//LIST
if (!defined('_COPY_LIST')) define('_COPY_LIST', 'Liste des documents en copie');
if (!defined('_PROCESS_LIST')) define('_PROCESS_LIST', 'Liste des documents &agrave; traiter');
if (!defined('_CLICK_LINE_TO_VIEW')) define('_CLICK_LINE_TO_VIEW', 'Cliquez sur une ligne pour visualiser');
if (!defined('_CLICK_LINE_TO_PROCESS')) define('_CLICK_LINE_TO_PROCESS', 'Cliquez sur une ligne pour traiter');

if (!defined('_REDIRECT_TO_SENDER_ENTITY')) define('_REDIRECT_TO_SENDER_ENTITY', 'Redirection vers le service &eacute;metteur');
if (!defined('_CHOOSE_DEPARTMENT')) define('_CHOOSE_DEPARTMENT', 'Choisissez un service');
if (!defined('_REDIRECTION')) define('_REDIRECTION', 'Redirection');
if (!defined('_ENTITY_UPDATE')) define('_ENTITY_UPDATE', 'Service mis &agrave; jour');
// USER ABS
if (!defined('_MY_ABS')) define('_MY_ABS', 'G&eacute;rer mes absences');
if (!defined('_MY_ABS_TXT')) define('_MY_ABS_TXT', 'Permet de rediriger votre courrier en attente en cas de d&eacute;part en cong&eacute;.');
if (!defined('_MY_ABS_REDIRECT')) define('_MY_ABS_REDIRECT', 'Vos courriers sont actuellement redirig&eacute;s vers');
if (!defined('_MY_ABS_DEL')) define('_MY_ABS_DEL', 'Pour supprimer la redirection, cliquez ici pour stopper');
if (!defined('_ADMIN_ABS')) define('_ADMIN_ABS', 'G&eacute;rer les absences.');
if (!defined('_ADMIN_ABS_TXT')) define('_ADMIN_ABS_TXT', 'Permet de rediriger le courrier de l&rsquo;utilisateur en attente en cas de d&eacute;part en cong&eacute;.');
if (!defined('_ADMIN_ABS_REDIRECT')) define('_ADMIN_ABS_REDIRECT', 'Redirection d&rsquo;absence en cours.');
if (!defined('_ADMIN_ABS_FIRST_PART')) define('_ADMIN_ABS_FIRST_PART', 'Les courrier de');
if (!defined('_ADMIN_ABS_SECOND_PART')) define('_ADMIN_ABS_SECOND_PART', 'sont actuellement redirig&eacute;s vers ');
if (!defined('_ADMIN_ABS_THIRD_PART')) define('_ADMIN_ABS_THIRD_PART', '. Cliquez ici pour supprimer la redirection.');
if (!defined('_ACTIONS_DONE')) define('_ACTIONS_DONE', 'Actions effectu&eacute;es le');
if (!defined('_PROCESSED_MAIL')) define('_PROCESSED_MAIL', 'Courriers trait&eacute;s');
if (!defined('_INDEXED_MAIL')) define('_INDEXED_MAIL', 'Courriers index&eacute;s');
if (!defined('_REDIRECTED_MAIL')) define('_REDIRECTED_MAIL', 'Courriers redirig&eacute;s');
if (!defined('_PROCESS_MAIL_OF')) define('_PROCESS_MAIL_OF', 'Courrier &agrave; traiter de');
if (!defined('_MISSING')) define('_MISSING', 'Absent');
if (!defined('_BACK_FROM_VACATION')) define('_BACK_FROM_VACATION', ' de retour');
if (!defined('_MISSING_ADVERT_TITLE')) define('_MISSING_ADVERT_TITLE','Gestion des absences');
if (defined('_MISSING_ADVERT_01')) define('_MISSING_ADVERT_01','Ce compte est actuellement d&eacute;finit en mode &rsquo;absent&rsquo; et les courriers sont redirig&eacute;s vers un autre utilisateur.');
if (defined('_MISSING_ADVERT_02')) define('_MISSING_ADVERT_02','Si vous desirez vous connecter avec ce compte, le mode &rsquo;absent&rsquo; sera alors supprim&eacute;.<br/> La redirection des courriers arrivera &agrave; son terme et l&rsquo;application sera r&eacute;activ&eacute;e');
if (!defined('_MISSING_CHOOSE')) define('_MISSING_CHOOSE','Souhaitez-vous continuer?');


if (!defined('_CHOOSE_PERSON_TO_REDIRECT')) define('_CHOOSE_PERSON_TO_REDIRECT', 'Choisissez la personne vers qui vous souhaitez rediriger ce courrier dans la liste ci-dessus');
if (!defined('_CLICK_ON_THE_LINE_OR_ICON')) define('_CLICK_ON_THE_LINE_OR_ICON', 'Il vous suffit de cliquer sur la ligne ou sur l&rsquo;ic&ocirc;ne');
if (!defined('_TO_SELECT_USER')) define('_TO_SELECT_USER', 'pour s&eacute;lectionner un utilisateur');

if (!defined('_DIFFUSION_DISTRIBUTION')) define('_DIFFUSION_DISTRIBUTION', 'Diffusion et distribution du courrier');
if (!defined('_VALIDATED_ANSWERS')) define('_VALIDATED_ANSWERS', 'DGS R&eacute;ponses valid&eacute;es');
if (!defined('_REJECTED_ANSWERS')) define('_REJECTED_ANSWERS', 'DGS R&eacute;ponses rejet&eacute;es');
if (!defined('_MUST_HAVE_DIFF_LIST')) define('_MUST_HAVE_DIFF_LIST', 'Vous devez d&eacute;finir une liste de diffusion');


if (!defined('_ASSOCIATED_STATUS')) define('_ASSOCIATED_STATUS', 'Associated status');
if (!defined('_SYSTEM_ACTION')) define('_SYSTEM_ACTION', 'System action');
if (!defined('_CANNOT_MODIFY_STATUS')) define('_CANNOT_MODIFY_STATUS', 'You cannot modify the status');
if (!defined('_ASSOCIATED_ACTIONS')) define('_ASSOCIATED_ACTIONS', 'No available action on the result page');
if (!defined('_NO_ACTIONS_DEFINED')) define('_NO_ACTIONS_DEFINED', 'No defined action');
if (!defined('_CONFIG')) define('_CONFIG', '(configure)');
if (!defined('_CONFIG_ACTION')) define('_CONFIG_ACTION', 'Action parameters');
if (!defined('_WHERE_CLAUSE_ACTION_TEXT')) define('_WHERE_CLAUSE_ACTION_TEXT', 'You can define a condition to choose wether the action apprear or not with the where clause (optional):');
if (!defined('_IN_ACTION')) define('_IN_ACTION', ' in the action');

if (!defined('_TO_ENTITIES')) define('_TO_ENTITIES', 'Vers des services');
if (!defined('_TO_USERGROUPS')) define('_TO_USERGROUPS', 'To usergroups');
if (!defined('_USE_IN_MASS')) define('_USE_IN_MASS', 'Available action in the list.');
if (!defined('_USE_ONE')) define('_USE_ONE', 'Available action in the action page');
if (!defined('_MUST_CHOOSE_WHERE_USE_ACTION')) define('_MUST_CHOOSE_WHERE_USE_ACTION','You must select where the action can be used ');

if (!defined('_MUST_CHOOSE_DEP')) define('_MUST_CHOOSE_DEP', 'You must select a entity!');
if (!defined('_MUST_CHOOSE_USER')) define('_MUST_CHOOSE_USER', 'You must select an user!');
if (!defined('_REDIRECT_TO_DEP_OK')) define('_REDIRECT_TO_DEP_OK', 'Redirection vers un service effectu&eacute;e');
if (!defined('_REDIRECT_TO_USER_OK')) define('_REDIRECT_TO_USER_OK', 'Redirection vers un utilisateur effectu&eacute;e');

if (!defined('_SAVE_CHANGES')) define('_SAVE_CHANGES', 'Save changes');
if (!defined('_VIEW_BASKETS')) define('_VIEW_BASKETS', 'My baskets');
if (!defined('_VIEW_BASKETS_TITLE')) define('_VIEW_BASKETS_TITLE', 'My baskets');

if (!defined('_INVOICE_LIST_TO_VAL')) define('_INVOICE_LIST_TO_VAL', 'Invoices to approve');
if (!defined('_POSTINDEXING_LIST')) define('_POSTINDEXING_LIST', 'Documents to check');
if (!defined('_MY_BASKETS')) define('_MY_BASKETS', 'My baskets');
?>

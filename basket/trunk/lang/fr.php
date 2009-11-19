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

/*********************** SERVICES ***********************************/
define('_ADMIN_BASKETS', 'Corbeilles');
define('_ADMIN_BASKETS_DESC', 'D&eacute;finir le contenu des corbeilles et les affecter &agrave; des groupes d&rsquo;utilisateurs. Enum&eacute;rer les redirections possibles lors de l&rsquo;utilisation de la corbeille par un groupe donn&eacute;. Attribuer un format d&rsquo;affichage de la corbeille par ce groupe.');
define('_USE_BASKETS', 'Utiliser les corbeilles');
define('_DIFFUSION_LIST', 'Liste de diffusion');

//class basket
define('_BASKET', 'Corbeille');
define('_BASKETS_COMMENT', 'Corbeilles');
define('_THE_BASKET', 'La corbeille ');
define('_THE_ID', 'L&rsquo;identifiant ');
define('_THE_DESC', 'La description ');
define('_BELONGS_TO_NO_GROUP', 'n&rsquo;appartient &agrave; aucun groupe');
define('_SYSTEM_BASKET_MESSAGE', 'Cette corbeille est une corbeille syst&egrave;me, vous ne pouvez pas modifier la table et la where clause. Elles sont affich&eacute;es &agrave; titre indicatif');
define('_BASKET_MISSING', 'La Corbeille n&rsquo;existe pas');
define('_BASKET_UPDATED', 'Corbeille modifi&eacute;e');
define('_BASKET_UPDATE', 'Modification de la corbeille');
define('_BASKET_ADDED', 'Nouvelle corbeille ajout&eacute;e');
define('_DELETED_BASKET', 'Corbeille supprim&eacute;e');
define('_BASKET_DELETION', 'Suppression de la corbeille');
define('_BASKET_AUTORIZATION', 'Autorisation de la corbeille');
define('_BASKET_SUSPENSION', 'Suspension de la corbeille');
define('_AUTORIZED_BASKET', 'Corbeille autoris&eacute;e');
define('_SUSPENDED_BASKET', 'Corbeille suspendue');
define('_NO_BASKET_DEFINED_FOR_YOU', 'Aucune corbeille d&eacute;finie pour cet utilisateur');


define('_BASKETS_LIST', 'Liste des corbeilles');

/////// frame corbeilles
define('_BASKETS', 'Corbeilles');
define('_CHOOSE_BASKET', 'Choisissez une corbeille');
define('_PROCESS_BASKET', 'Votre courrier &agrave; traiter');
define('_VALIDATION_BASKET', 'Votre courrier &agrave; valider');

define('_MANAGE_BASKETS', 'G&eacute;rer les corbeilles');
define('_MANAGE_BASKETS_APP', 'G&eacute;rer les corbeilles de l&rsquo;application');

/************** Corbeille : Liste + Formulaire**************/
define('_ALL_BASKETS', 'Toutes les corbeilles');
define('_BASKET_LIST', 'Liste des corbeilles');
define('_ADD_BASKET', 'Ajouter une corbeille');
define('_BASKET_ADDITION', 'Ajout d&rsquo;une corbeille');
define('_BASKET_MODIFICATION', 'Modification d&rsquo;une corbeille');
define('_BASKET_VIEW', 'Vue sur la table');
define('_MODIFY_BASKET', 'Modifier la corbeille');
define('_ADD_A_NEW_BASKET', 'Cr&eacute;er une nouvelle corbeille');
define('_ADD_A_GROUP_TO_BASKET', 'Associer un nouveau groupe &agrave; la corbeille');
define('_DEL_GROUPS', 'Supprimer groupe(s)');
define('_BASKET_NOT_USABLE', 'Aucun groupe associ&eacute; (la corbeille est inutilisable pour l&rsquo;instant)');
define('_ASSOCIATED_GROUP', 'Liste des groupes associ&eacute;s &agrave; la corbeille');

define('_TITLE_GROUP_BASKET', 'Associer la corbeille &agrave; un groupe');
define('_ADD_TO_BASKET', 'Associer la corbeille');
define('_TO_THE_GROUP', '&agrave; un groupe');
define('_ALLOWED_ACTIONS', 'Actions autoris&eacute;es');
define('_SERVICES_BASKETS', 'Corbeilles de services');
define('_USERGROUPS_BASKETS', 'Corbeilles des groupes d&rsquo;utilisateurs');
define('_BASKET_RESULT_PAGE', 'Liste de r&eacute;sultats');
define('_ADD_THIS_GROUP', 'Ajouter le groupe');
define('_MODIFY_THIS_GROUP', 'Modifier le groupe');
define('_DEFAULT_ACTION_LIST', 'Action par d&eacute;faut sur la liste<br/><i>(Cliquez sur la ligne)');
define('_NO_ACTION_DEFINED', 'Aucune action d&eacute;finie');

//BASKETS
define('_PROCESS_FOLDER_LIST', 'Liste des dossiers trait&eacute;s');
define('_INCOMPLETE_FOLDERS_LIST', 'Liste des dossiers incomplets');
define('_WAITING_VAL_LIST', 'Liste des pi&egrave;ces en attente de validation');
define('_WAITING_QUAL_LIST', 'Liste des pi&egrave;ces en attente de qualification');
define('_WAITING_DISTRIB_LIST', 'Liste des courriers en attente de distribution');
define('_NO_REDIRECT_RIGHT', 'Vous n&rsquo;avez pas le droit de redirection dans cette corbeille');
define('_CLICK_LINE_BASKET1', 'Cliquez sur une ligne pour qualifier un document');

//ENTITY
/*
define('_SELECT_ENTITY', 'S&eacute;lection service');
define('_ENTITY', 'Service');
define('_LABEL', 'Label');
define('_THE_ENTITY', 'Le service');
define('_ENTITIES', 'Services');
define('_ALL_ENTITIES', 'Tous les services');
define('_ENTITY_LIST', 'Liste des services');
define('_SELECTED_ENTITIES', 'Services s&eacute;lectionn&eacute;s');
define('_CHOOSE_ENTITY', 'Choisir service');
define('_MUST_CHOOSE_AN_ENTITY', 'Vous devez choisir un service');
define('_ADMIN_ENTITIES', 'Administrer les services');
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
*/

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
if(!defined('_AND'))
{
	define('_AND', ' et ' );
}
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
//define('_REDIRECTION', 'Redirection');
define('_ENTITY_UPDATE', 'Service mis &agrave; jour');
// USER ABS
define('_MY_ABS', 'G&eacute;rer mes absences');
define('_MY_ABS_TXT', 'Permet de rediriger vos corbeilles en cas de d&eacute;part en cong&eacute;.');
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
if(!defined('_MISSING'))
{
	define('_MISSING', 'Absent');
}
define('_BACK_FROM_VACATION', 'de retour de son absence');
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


define('_ASSOCIATED_STATUS', 'Statut associ&eacute;');
define('_SYSTEM_ACTION', 'Action syst&egrave;me');
define('_CANNOT_MODIFY_STATUS', 'Vous ne pouvez pas modifier le statut');
define('_ASSOCIATED_ACTIONS', 'Actions possibles sur la page de r&eacute;sultat');
define('_NO_ACTIONS_DEFINED', 'Aucune action d&eacute;finie');
define('_CONFIG', '(param&egrave;trer)');
define('_CONFIG_ACTION', 'Param&egrave;trage de l&rsquo;action');
define('_WHERE_CLAUSE_ACTION_TEXT', 'D&eacute;finissez une condition d&rsquo;apparition de l&rsquo;action dans la page par une clause Where (Facultatif) : ');
define('_IN_ACTION', ' dans l&rsquo;action');

define('_TO_ENTITIES', 'Vers des services');
define('_TO_USERGROUPS', 'Vers des groupes d&rsquo;utilisateur');
define('_USE_IN_MASS', 'Action disponible dans la liste');
define('_USE_ONE', 'Action disponible dans la page d&rsquo;action');
define('_MUST_CHOOSE_WHERE_USE_ACTION','Vous devez d&eacute;finir o&ugrave; vous souhaitez utiliser l&rsquo;action ');

define('_MUST_CHOOSE_DEP', 'Vous devez s&eacute;lectionner un service!');
define('_MUST_CHOOSE_USER', 'Vous devez s&eacute;lectionner un utilisateur!');
define('_REDIRECT_TO_DEP_OK', 'Redirection vers un service effectu&eacute;e');
define('_REDIRECT_TO_USER_OK', 'Redirection vers un utilisateur effectu&eacute;e');

define('_SAVE_CHANGES', 'Enregistrer les modifications');
define('_VIEW_BASKETS', 'Mes corbeilles');
define('_VIEW_BASKETS_TITLE', 'Mes corbeilles');

define('_INVOICE_LIST_TO_VAL', 'Factures &agrave; valider');
define('_POSTINDEXING_LIST', 'Documents &agrave; vid&eacute;ocoder');
define('_MY_BASKETS', 'Mes corbeilles');
define('_REDIRECT_MY_BASKETS', 'Rediriger les corbeilles');
define('_NAME', 'Nom');
define('_CHOOSE_USER_TO_REDIRECT', 'Vous devez rediriger au moins une des corbeilles vers un utilisateur.');
define('_FORMAT_ERROR_ON_USER_FIELD', 'Un champ n&rsquo;est pas dans le bon format : Nom, Pr&eacute;nom (Identifiant)');
define('_BASKETS_OWNER_MISSING', 'Le propri&eacute;taire des corbeilles n&rsquo;est pas d&eacute;fini.');
define('_FORM_ERROR', 'Erreur dans la transmission du formulaire...');
define('_USER_ABS', 'Utilisateur absent : redirection d&eacute;j&agrave; param&eacute;tr&eacute;e.');
define('_ABS_LOG_OUT', 'si vous vous reconnectez, le mode absent sera annul&eacute;.');
define('_ABS_USER', 'Utilisateur absent');
define('_ABSENCE', 'Absence');
define('_BASK_BACK', 'Retour');

define('_CANCEL_ABS', 'Annulation d&rsquo;absence');
define('_REALLY_CANCEL_ABS', 'Voulez-vous vraiment annuler l&rsquo;absence ?');
define('_ABS_MODE', 'Gestion des absences');
define('_REALLY_ABS_MODE', 'Voulez-vous vraiment passer en mode absent ?');

//define('_REDIRECT_TO_ACTION', 'Rediriger vers une action');
//define('_DOCUMENTS_LIST', 'Liste simple');
define('_DOCUMENTS_LIST_WITH_FILTERS', 'Liste avec filtres');
define('_AUTHORISED_ENTITIES', 'Liste services autoris&eacute;s');
define('_ARCHIVE_LIST', 'Liste d&rsquo;unit&eacute;s d&rsquo;archive');

define('_FILTER_BY_ENTITY', 'Filtrer par service');
define('_FILTER_BY', 'Filtrer par');
define('_OTHER_BASKETS', 'Autres corbeilles');


define('_BASKET_WELCOME_TXT1', 'Durant votre navigation dans les corbeilles,');
define('_BASKET_WELCOME_TXT2', 'cliquez, &agrave; tout moment, dans la liste ci-dessus <br/>pour changer de corbeille');



?>

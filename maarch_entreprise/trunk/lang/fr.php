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

if (!defined('_MEP_VERSION')) define('_MEP_VERSION', 'Maarch Entreprise v1.2');

/************** Administration **************/
if (!defined('_ADMIN_USERS')) {
    define( '_ADMIN_USERS', 'Utilisateurs');
}
if (!defined('_ADMIN_DOCSERVERS')) {
    define( '_ADMIN_DOCSERVERS', 'Zones de stockage');
}
if (!defined('_ADMIN_USERS_DESC')) {
    define( '_ADMIN_USERS_DESC', 'Ajouter, suspendre, ou modifier des profils utilisateurs. Placer les utilisateurs dans leurs groupes d&rsquo;appartenance et définir leur groupe primaire.');
}
if (!defined('_ADMIN_DOCSERVERS_DESC')) {
    define( '_ADMIN_DOCSERVERS_DESC', 'Ajouter, suspendre, ou modifier des zones de stockage. Placer les zones de stockages par type d&rsquo;appartenance et définir leur groupe primaire.');
}
if (!defined('_ADMIN_GROUPS')) {
    define( '_ADMIN_GROUPS', 'Groupes d&rsquo;utilisateurs');
}
if (!defined('_ADMIN_GROUPS_DESC')) {
    define( '_ADMIN_GROUPS_DESC', 'Ajouter, suspendre, ou modifier des groupes d&rsquo;utilisateurs. Attribuer des privil&egrave;ges ou des autorisations d&rsquo;acc&egrave;s aux ressources.');
}
if (!defined('_ADMIN_ARCHITECTURE')) {
    define( '_ADMIN_ARCHITECTURE', 'Plan de classement');
}
if (!defined('_ADMIN_ARCHITECTURE_DESC')) {
    define( '_ADMIN_ARCHITECTURE_DESC', 'D&eacute;finir la structure interne d&rsquo;un dossier (chemise / sous-chemise / type de document). D&eacute;finir pour chaque pi&egrave;ce la liste des index &agrave; saisir, et leur caract&egrave;re obligatoire pour la compl&eacute;tude du dossier.');
}
if (!defined('_VIEW_HISTORY')) {
    define( '_VIEW_HISTORY', 'Historique');
}
if (!defined('_VIEW_HISTORY_BATCH')) {
    define( '_VIEW_HISTORY_BATCH', 'Historique des batchs');
}
if (!defined('_VIEW_HISTORY_DESC')) {
    define( '_VIEW_HISTORY_DESC', 'Consulter l&rsquo;historique des &eacute;v&egrave;nements relatifs à l&rsquo;utilisation de la GED Maarch.');
}
if (!defined('_VIEW_HISTORY_BATCH_DESC')) {
    define( '_VIEW_HISTORY_BATCH_DESC', 'Consulter l&rsquo;historique des batchs');
}
if (!defined('_ADMIN_MODULES')) {
    define( '_ADMIN_MODULES', 'G&eacute;rer les modules');
}
if (!defined('_ADMIN_SERVICE')) {
    define( '_ADMIN_SERVICE', 'Service d&rsquo;administration');
}
if (!defined('_XML_PARAM_SERVICE_DESC')) {
    define( '_XML_PARAM_SERVICE_DESC', 'Visualisation configuration XML des services');
}
if (!defined('_XML_PARAM_SERVICE')) {
    define( '_XML_PARAM_SERVICE', 'Visualisation configuration XML des services');
}
if (!defined('_MODULES_SERVICES')) {
    define( '_MODULES_SERVICES', 'Services d&eacute;finis par les modules');
}
if (!defined('_APPS_SERVICES')) {
    define( '_APPS_SERVICES', 'Services d&eacute;finis par l&rsquo;application');
}
if (!defined('_ADMIN_STATUS_DESC')) {
    define( '_ADMIN_STATUS_DESC', 'Cr&eacute;er ou modifier des statuts.');
}
if (!defined('_ADMIN_ACTIONS_DESC')) {
    define( '_ADMIN_ACTIONS_DESC', 'Cr&eacute;er ou modifier des actions.');
}
if (!defined('_ADMIN_SERVICES_UNKNOWN')) {
    define( '_ADMIN_SERVICES_UNKNOWN', 'Service d&rsquo;administration inconnu');
}
if (!defined('_NO_RIGHTS_ON')) {
    define( '_NO_RIGHTS_ON', 'Aucun droit sur');
}
if (!defined('_NO_LABEL_FOUND')) {
    define( '_NO_LABEL_FOUND', 'Aucun label trouv&eacute; pour ce service');
}

if (!defined('_FOLDERTYPES_LIST')) {
    define( '_FOLDERTYPES_LIST', 'Liste des types de dossier');
}
if (!defined('_SELECTED_FOLDERTYPES')) {
    define( '_SELECTED_FOLDERTYPES', 'Types de dossier s&eacute;lectionn&eacute;s');
}
if (!defined('_FOLDERTYPE_ADDED')) {
    define( '_FOLDERTYPE_ADDED', 'Nouveau dossier ajout&eacute;');
}
if (!defined('_FOLDERTYPE_DELETION')) {
    define( '_FOLDERTYPE_DELETION', 'Dossier supprim&eacute;');
}
if (!defined('_VERSION_BASE_AND_XML_BASEVERSION_NOT_MATCH')) {
    define( '_VERSION_BASE_AND_XML_BASEVERSION_NOT_MATCH', 'Attention: Le mod&egrave;le de donn&eacute;es de Maarch Entreprise doit &ecirc;tre mis &agrave; jour...');
}


/*********************** communs ***********************************/
if (!defined('_MODE')) {
    define('_MODE', 'Mode');
}
/************** Listes **************/
if (!defined('_GO_TO_PAGE')) {
    define( '_GO_TO_PAGE', 'Aller &agrave; la page');
}
if (!defined('_NEXT')) {
    define( '_NEXT', 'Suivante');
}
if (!defined('_PREVIOUS')) {
    define( '_PREVIOUS', 'Pr&eacute;c&eacute;dente');
}
if (!defined('_ALPHABETICAL_LIST')) {
    define( '_ALPHABETICAL_LIST', 'Liste alphab&eacute;tique');
}
if (!defined('_ASC_SORT')) {
    define( '_ASC_SORT', 'Tri ascendant');
}
if (!defined('_DESC_SORT')) {
    define( '_DESC_SORT', 'Tri descendant');
}
if (!defined('_ACCESS_LIST_STANDARD')) {
    define( '_ACCESS_LIST_STANDARD', 'Affichage des listes simples');
}
if (!defined('_ACCESS_LIST_EXTEND')) {
    define( '_ACCESS_LIST_EXTEND', 'Affichage des listes &eacute;tendues');
}
if (!defined('_DISPLAY')) {
    define( '_DISPLAY', 'Affichage');
}
/************** Actions **************/
if (!defined('_DELETE')) {
    define( '_DELETE', 'Supprimer');
}
if (!defined('_ADD')) {
    define( '_ADD', 'Ajouter');
}
if (!defined('_REMOVE')) {
    define( '_REMOVE', 'Enlever');
}
if (!defined('_MODIFY')) {
    define( '_MODIFY', 'Modifier');
}
if (!defined('_SUSPEND')) {
    define( '_SUSPEND', 'Suspendre');
}
if (!defined('_AUTHORIZE')) {
    define( '_AUTHORIZE', 'Autoriser');
}
if (!defined('_CHOOSE')) {
    define( '_CHOOSE', 'Choisir');
}
if (!defined('_SEND')) {
    define( '_SEND', 'Envoyer');
}
if (!defined('_SEARCH')) {
    define( '_SEARCH', 'Rechercher');
}
if (!defined('_RESET')) {
    define( '_RESET', 'R&eacute;initialiser');
}
if (!defined('_VALIDATE')) {
    define( '_VALIDATE', 'Valider');
}
if (!defined('_CANCEL')) {
    define( '_CANCEL', 'Annuler');
}
if (!defined('_ADDITION')) {
    define( '_ADDITION', 'Ajout');
}
if (!defined('_MODIFICATION')) {
    define( '_MODIFICATION', 'Modification');
}
if (!defined('_DIFFUSION')) {
    define( '_DIFFUSION', 'Diffusion');
}
if (!defined('_DELETION')) {
    define( '_DELETION', 'Suppression');
}
if (!defined('_SUSPENSION')) {
    define( '_SUSPENSION', 'Suspension');
}
if (!defined('_VALIDATION')) {
    define( '_VALIDATION', 'Validation');
}
if (!defined('_REDIRECTION')) {
    define( '_REDIRECTION', 'Redirection');
}
if (!defined('_DUPLICATION')) {
    define( '_DUPLICATION', 'Duplication');
}
if (!defined('_PROPOSITION')) {
    define( '_PROPOSITION', 'Proposition');
}
if (!defined('_ERR')) {
    define( '_ERR', 'Erreur');
}
if (!defined('_CLOSE')) {
    define( '_CLOSE', 'Fermer');
}
if (!defined('_CLOSE_WINDOW')) {
    define( '_CLOSE_WINDOW', 'Fermer la fen&ecirc;tre');
}
if (!defined('_DIFFUSE')) {
    define( '_DIFFUSE', 'Diffuser');
}
if (!defined('_DOWN')) {
    define( '_DOWN', 'Descendre');
}
if (!defined('_UP')) {
    define( '_UP', 'Monter');
}
if (!defined('_REDIRECT')) {
    define( '_REDIRECT', 'Rediriger');
}
if (!defined('_DELETED')) {
    define( '_DELETED', 'Supprim&eacute;');
}
if (!defined('_CONTINUE')) {
    define( '_CONTINUE', 'Continuer');
}
if (!defined('_VIEW')) {
    define( '_VIEW','Visualisation');
}
if (!defined('_CHOOSE_ACTION')) {
    define( '_CHOOSE_ACTION', 'Choisissez une action');
}
if (!defined('_ACTIONS')) {
    define( '_ACTIONS', 'Actions');
}
if (!defined('_ACTION_PAGE')) {
    define( '_ACTION_PAGE', 'Page de r&eacute;sultat de l&rsquo;action');
}
if (!defined('_DO_NOT_MODIFY_UNLESS_EXPERT')) {
    define( '_DO_NOT_MODIFY_UNLESS_EXPERT', ' Ne pas modifier cette section &agrave; moins de savoir ce que vous faites. Un mauvais param&egrave;trage peut entrainer des dysfonctionnements de l&rsquo;application!');
}
if (!defined('_INFOS_ACTIONS')) {
    define( '_INFOS_ACTIONS', 'Vous devez choisir au moins un statut et / ou un script.');
}


/************** Intitul&eacute;s formulaires et listes **************/
if (!defined('_ID')) {
    define( '_ID', 'Identifiant');
}
if (!defined('_PASSWORD')) {
    define( '_PASSWORD', 'Mot de passe');
}
if (!defined('_GROUP')) {
    define( '_GROUP', 'Groupe');
}
if (!defined('_USER')) {
    define( '_USER', 'Utilisateur');
}
if (!defined('_DESC')) {
    define( '_DESC', 'Description');
}
if (!defined('_LASTNAME')) {
    define( '_LASTNAME', 'Nom');
}
if (!defined('_THE_LASTNAME')) {
    define( '_THE_LASTNAME', 'Le nom');
}
if (!defined('_THE_FIRSTNAME')) {
    define( '_THE_FIRSTNAME', 'Le pr&eacute;nom');
}
if (!defined('_THE_ID')) {
    define( '_THE_ID', 'L&rsquo;identifiant');
}
if (!defined('_FIRSTNAME')) {
    define( '_FIRSTNAME', 'Pr&eacute;nom');
}
if (!defined('_STATUS')) {
    define( '_STATUS', 'Statut');
}
if (!defined('_DEPARTMENT')) {
    define( '_DEPARTMENT', 'D&eacute;partement');
}
if (!defined('_FUNCTION')) {
    define( '_FUNCTION', 'Fonction');
}
if (!defined('_PHONE_NUMBER')) {
    define( '_PHONE_NUMBER', 'Num&eacute;ro de t&eacute;l&eacute;phone');
}
if (!defined('_MAIL')) {
    define( '_MAIL', 'Courriel');
}
if (!defined('_DOCTYPE')) {
    define( '_DOCTYPE', 'Type de document');
}
if (!defined('_TYPE')) {
    define( '_TYPE', 'Type');
}
if (!defined('_SELECT_ALL')) {
    define( '_SELECT_ALL', 'S&eacute;lectionner tout');
}
if (!defined('_DATE')) {
    define( '_DATE', 'Date');
}
if (!defined('_ACTION')) {
    define( '_ACTION', 'Action');
}
if (!defined('_COMMENTS')) {
    define( '_COMMENTS', 'Commentaires');
}
if (!defined('_ENABLED')) {
    define( '_ENABLED', 'Autoris&eacute;');
}
if (!defined('_DISABLED')) {
    define( '_DISABLED', 'Suspendu');
}
if (!defined('_NOT_ENABLED')) {
    define( '_NOT_ENABLED', 'Suspendu');
}
if (!defined('_RESSOURCES_COLLECTION')) {
    define( '_RESSOURCES_COLLECTION','Collection documentaire');
}
if (!defined('_RECIPIENT')) {
    define( '_RECIPIENT', 'Destinataire');
}
if (!defined('_START')) {
    define( '_START', 'D&eacute;but');
}
if (!defined('_END')) {
    define( '_END', 'Fin');
}

if (!defined('_KEYWORD')) {
    define( '_KEYWORD', 'Mot cl&eacute;');
}

if (!defined('_SYSTEM_PARAMETERS')) {
    define( '_SYSTEM_PARAMETERS', 'param&egrave;tres syst&egrave;me');
}

if (!defined('_NO_KEYWORD')) {
    define( '_NO_KEYWORD', 'Aucun mot cl&eacute;');
}

if (!defined('_TO_VALIDATE')) {
    define( '_TO_VALIDATE', '&Agrave; valider');
}

if (!defined('_INDEXING')) {
    define( '_INDEXING', 'Indexation');
}

if (!defined('_QUALIFY')) {
    define( '_QUALIFY', 'Qualification');
}


/************** Messages pop up **************/
if (!defined('_REALLY_SUSPEND')) define( '_REALLY_SUSPEND', 'Voulez-vous vraiment suspendre ');
if (!defined('_REALLY_AUTHORIZE')) define( '_REALLY_AUTHORIZE', 'Voulez-vous vraiment autoriser ');
if (!defined('_REALLY_DELETE')) define( '_REALLY_DELETE', 'Voulez-vous vraiment supprimer ');
if (!defined('_DEFINITIVE_ACTION')) define( '_DEFINITIVE_ACTION', 'Cette action est d&eacute;finitive');

/************** Divers **************/
if (!defined('_YES')) define( '_YES', 'Oui');
if (!defined('_NO')) define( '_NO', 'Non');
if (!defined('_UNKNOWN')) define( '_UNKNOWN', 'Inconnu');
if (!defined('_SINCE')) define( '_SINCE','Depuis');
if (!defined('_FOR')) define( '_FOR','Jusqu&rsquo;&agrave;');
if (!defined('_HELLO')) define( '_HELLO','Bonjour');
if (!defined('_OBJECT')) define( '_OBJECT','Objet');
if (!defined('_BACK')) define( '_BACK','Retour');
if (!defined('_FORMAT')) define( '_FORMAT','Format');
if (!defined('_SIZE')) define( '_SIZE','Taille');
if (!defined('_DOC')) define( '_DOC', 'Document ');
if (!defined('_THE_DOC')) define( '_THE_DOC', 'Le document');
if (!defined('_BYTES')) define( '_BYTES', 'octets');
if (!defined('_OR')) define( '_OR', 'ou');
if (!defined('_NOT_AVAILABLE')) define( '_NOT_AVAILABLE', 'Indisponible');
if (!defined('_SELECTION')) define( '_SELECTION', 'S&eacute;lection');
if (!defined('_AND')) define( '_AND', ' et ' );
if (!defined('_FILE')) define( '_FILE','Fichier');
if (!defined('_UNTIL')) define( '_UNTIL', 'au');
if (!defined('_ALL')) define( '_ALL', 'Tous');

//class functions
if (!defined('_SECOND')) define( '_SECOND', 'seconde');
if (!defined('_SECONDS')) define( '_SECONDS', 'secondes');
if (!defined('_PAGE_GENERATED_IN')) define( '_PAGE_GENERATED_IN', 'Page g&eacute;n&eacute;r&eacute;e en');
if (!defined('_IS_EMPTY')) define( '_IS_EMPTY', 'est vide');
if (!defined('_MUST_MAKE_AT_LEAST')) define( '_MUST_MAKE_AT_LEAST', 'doit faire au minimum' );
if (!defined('_CHARACTER')) define( '_CHARACTER', 'caract&egrave;re');
if (!defined('_CHARACTERS')) define( '_CHARACTERS', 'caract&egrave;res');
if (!defined('MUST_BE_LESS_THAN')) define( 'MUST_BE_LESS_THAN', 'ne doit pas faire plus de');
if (!defined('_WRONG_FORMAT')) define( '_WRONG_FORMAT', 'n&rsquo;est pas dans le bon format');
if (!defined('_WELCOME')) define( '_WELCOME', 'Bienvenue sur Maarch Entreprise !');
if (!defined('_WELCOME_TITLE')) define( '_WELCOME_TITLE', 'Accueil');
if (!defined('_HELP')) define( '_HELP', 'Aide');
if (!defined('_SEARCH_ADV_SHORT')) define( '_SEARCH_ADV_SHORT', 'Recherche Avanc&eacute;e');
if (!defined('_SEARCH_SCOPE')) define( '_SEARCH_SCOPE', 'Port&eacute;e de la recherche');
if (!defined('_SEARCH_SCOPE_HELP')) define( '_SEARCH_SCOPE_HELP', 'Etend la recherche aux corbeilles; autorise les actions si une corbeille particuli&egrave;re est s&eacute;lectionn&eacute;e');
if (!defined('_RESULTS')) define( '_RESULTS', 'R&eacute;sultats');
if (!defined('_USERS_LIST_SHORT')) define( '_USERS_LIST_SHORT', 'Liste utilisateurs');
if (!defined('_MODELS_LIST_SHORT')) define( '_MODELS_LIST_SHORT', 'Liste mod&egrave;les');
if (!defined('_GROUPS_LIST_SHORT')) define( '_GROUPS_LIST_SHORT', 'Liste groupes');
if (!defined('_DEPARTMENTS_LIST_SHORT')) define( '_DEPARTMENTS_LIST_SHORT', 'Liste services');
if (!defined('_BITMASK')) define( '_BITMASK', 'Param&egrave;tre Bitmask');
if (!defined('_DOCTYPES_LIST_SHORT')) define( '_DOCTYPES_LIST_SHORT', 'Liste types');
if (!defined('_BAD_MONTH_FORMAT')) define( '_BAD_MONTH_FORMAT', 'Le mois est incorrect');
if (!defined('_BAD_DAY_FORMAT')) define( '_BAD_DAY_FORMAT', 'Le jour est incorrect');
if (!defined('_BAD_YEAR_FORMAT')) define( '_BAD_YEAR_FORMAT', 'L&rsquo;ann&eacute;e est incorrect');
if (!defined('_BAD_FEBRUARY')) define( '_BAD_FEBRUARY', 'Le mois de f&eacute;vrier ne peux contenir que 29 jours maximum');
if (!defined('_CHAPTER_SHORT')) define( '_CHAPTER_SHORT', 'Chap ');
if (!defined('_PROCESS_SHORT')) define( '_PROCESS_SHORT', 'Traitement');
if (!defined('_CARD')) define( '_CARD', 'Fiche');

/************************* First login ***********************************/
if (!defined('_MODIFICATION_PSW')) define( '_MODIFICATION_PSW', 'Modification du mot de passe');
if (!defined('_YOUR_FIRST_CONNEXION')) define( '_YOUR_FIRST_CONNEXION', 'Bienvenue sur Maarch Entreprise ! <br/>Ceci est votre premi&egrave;re connexion');
if (!defined('_PLEASE_CHANGE_PSW')) define( '_PLEASE_CHANGE_PSW', ' veuillez d&eacute;finir votre mot de passe');
if (!defined('_ASKED_ONLY_ONCE')) define( '_ASKED_ONLY_ONCE', 'Cela ne vous sera demand&eacute; qu&rsquo;une seule fois');
if (!defined('_FIRST_CONN')) define( '_FIRST_CONN', 'Premi&egrave;re connexion');
if (!defined('_LOGIN')) define( '_LOGIN', 'Connexion');
if (!defined('_RELOGIN')) define( '_RELOGIN', 'Reconnexion');
if (!defined('_RA_CODE')) define( '_RA_CODE', 'Code d\'acc&egrave;s compl&eacute;mentaire');

/*************************  index  page***********************************/
if (!defined('_LOGO_ALT')) define( '_LOGO_ALT', 'Retour &agrave; la page d&rsquo;accueil');
if (!defined('_LOGOUT')) define( '_LOGOUT', 'D&eacute;connexion');
if (!defined('_MENU')) define( '_MENU', 'Menu');
if (!defined('_ADMIN')) define( '_ADMIN', 'Administration');
if (!defined('_SUMMARY')) define( '_SUMMARY', 'Sommaire');
if (!defined('_MANAGE_DIPLOMA')) define( '_MANAGE_DIPLOMA', 'G&eacute;rer les dipl&ocirc;mes');
if (!defined('_MANAGE_CONTRACT')) define( '_MANAGE_CONTRACT', 'G&eacute;rer les types de contrats');
if (!defined('_MANAGE_REL_MODEL')) define( '_MANAGE_REL_MODEL', 'G&eacute;rer le mod&egrave;le de relance');
if (!defined('_MANAGE_DOCTYPES')) define( '_MANAGE_DOCTYPES', 'G&eacute;rer les types de documents');
if (!defined('_MANAGE_DOCTYPES_DESC')) define( '_MANAGE_DOCTYPES_DESC', 'Administrer les types de documents. Les types de documents sont rattach&eacute;s &agrave; une collection documentaire. Pour chaque type, vous pouvez d&eacute;finir les index &agrave; saisir et ceux qui sont obligatoires.');
if (!defined('_VIEW_HISTORY2')) define( '_VIEW_HISTORY2', 'Visualisation de l&rsquo;historique');
if (!defined('_VIEW_HISTORY_BATCH2')) define( '_VIEW_HISTORY_BATCH2', 'Visualisation de l&rsquo;historique des batchs');
if (!defined('_INDEX_FILE')) define( '_INDEX_FILE', 'Indexer un fichier');
if (!defined('_WORDING')) define( '_WORDING', 'Libell&eacute;');
if (!defined('_COLLECTION')) define( '_COLLECTION', 'Collection');
if (!defined('_VIEW_TREE_DOCTYPES')) define( '_VIEW_TREE_DOCTYPES', 'Arborescence du plan de classement');
if (!defined('_VIEW_TREE_DOCTYPES_DESC')) define( '_VIEW_TREE_DOCTYPES_DESC', 'Voir l&rsquo;arborescence du plan de classement (types de dossiers, chemises, sous-chemises et types de documents)');
if (!defined('_WELCOME_ON')) define( '_WELCOME_ON', 'Bienvenue sur');

/************************* Administration ***********************************/

/**************Sommaire**************/
if (!defined('_MANAGE_GROUPS_APP')) define( '_MANAGE_GROUPS_APP', 'G&eacute;rer les groupes de l&rsquo;application');
if (!defined('_MANAGE_USERS_APP')) define( '_MANAGE_USERS_APP', 'G&eacute;rer les utilisateurs de l&rsquo;application');
if (!defined('_MANAGE_DIPLOMA_APP')) define( '_MANAGE_DIPLOMA_APP', 'G&eacute;rer les dipl&ocirc;mes de l&rsquo;application');
if (!defined('_MANAGE_DOCTYPES_APP')) define( '_MANAGE_DOCTYPES_APP', 'G&eacute;rer les types de document de l&rsquo;application');
if (!defined('_MANAGE_ARCHI_APP')) define( '_MANAGE_ARCHI_APP', 'G&eacute;rer l&rsquo;architecture des types de document de l&rsquo;application');
if (!defined('_MANAGE_CONTRACT_APP')) define( '_MANAGE_CONTRACT_APP', 'G&eacute;rer les types de contrat de l&rsquo;application');
if (!defined('_HISTORY_EXPLANATION')) define( '_HISTORY_EXPLANATION', 'Surveiller les modifications, les suppressions et les ajouts dans l&rsquo;application');
if (!defined('_ARCHI_EXP')) define( '_ARCHI_EXP', 'les chemises, les sous-chemises et les types de document');


/************** Groupes : Liste + Formulaire**************/

if (!defined('_GROUPS_LIST')) define( '_GROUPS_LIST', 'Liste des groupes');
if (!defined('_ADMIN_GROUP')) define( '_ADMIN_GROUP', 'Groupe d&rsquo;administration');
if (!defined('_ADD_GROUP')) define( '_ADD_GROUP', 'Ajouter un groupe');
if (!defined('_ALL_GROUPS')) define( '_ALL_GROUPS', 'Tous les groupes');
if (!defined('_GROUPS')) define( '_GROUPS', 'groupes');

if (!defined('_GROUP_ADDITION')) define( '_GROUP_ADDITION', 'Ajout d&rsquo;un groupe');
if (!defined('_GROUP_MODIFICATION')) define( '_GROUP_MODIFICATION', 'Modification d&rsquo;un groupe');
if (!defined('_SEE_GROUP_MEMBERS')) define( '_SEE_GROUP_MEMBERS', 'Voir la liste des utilisateurs de ce groupe');
if (!defined('_SEE_DOCSERVERS_')) define( '_SEE_DOCSERVERS_', 'Voir la liste des docservers de ce type');
if (!defined('_SEE_DOCSERVERS_LOCATION')) define( '_SEE_DOCSERVERS_LOCATION', 'Voir la liste des docservers de ce lieu');
if (!defined('_OTHER_RIGHTS')) define( '_OTHER_RIGHTS', 'Autres droits');
if (!defined('_MODIFY_GROUP')) define( '_MODIFY_GROUP', 'Accepter les changements');
if (!defined('_THE_GROUP')) define( '_THE_GROUP', 'Le groupe');
if (!defined('_HAS_NO_SECURITY')) define( '_HAS_NO_SECURITY', 'n&rsquo;a aucune s&eacute;curit&eacute; d&eacute;finie' );

if (!defined('_DEFINE_A_GRANT')) define( '_DEFINE_A_GRANT', 'D&eacute;finissez au moins un acc&egrave;s');
if (!defined('_MANAGE_RIGHTS')) define( '_MANAGE_RIGHTS', 'Ce groupe a acc&egrave;s aux ressources suivantes');
if (!defined('_TABLE')) define( '_TABLE', 'Table');
if (!defined('_WHERE_CLAUSE')) define( '_WHERE_CLAUSE', 'Clause WHERE');
if (!defined('_INSERT')) define( '_INSERT', 'Insertion');
if (!defined('_UPDATE')) define( '_UPDATE', 'Mise &agrave; jour');
if (!defined('_REMOVE_ACCESS')) define( '_REMOVE_ACCESS', 'Supprimer acc&egrave;s');
if (!defined('_MODIFY_ACCESS')) define( '_MODIFY_ACCESS', 'Modifier acc&egrave;s');
if (!defined('_UPDATE_RIGHTS')) define( '_UPDATE_RIGHTS', 'Mise &agrave; jour des droits');
if (!defined('_ADD_GRANT')) define( '_ADD_GRANT', 'Ajouter acc&egrave;s');
if (!defined('_UP_GRANT')) define( '_UP_GRANT', 'Modifier acc&egrave;s');
if (!defined('_USERS_LIST_IN_GROUP')) define( '_USERS_LIST_IN_GROUP', 'Liste des utilisateurs du groupe');

/************** Utilisateurs : Liste + Formulaire**************/

if (!defined('_USERS_LIST')) define( '_USERS_LIST', 'Liste des utilisateurs');
if (!defined('_ADD_USER')) define( '_ADD_USER', 'Ajouter un utilisateur');
if (!defined('_ALL_USERS')) define( '_ALL_USERS', 'Tous les utilisateurs');
if (!defined('_USERS')) define( '_USERS', 'utilisateurs');
if (!defined('_USER_ADDITION')) define( '_USER_ADDITION', 'Ajout d&rsquo;un utilisateur');
if (!defined('_USER_MODIFICATION')) define( '_USER_MODIFICATION', 'Modification d&rsquo;un utilisateur');
if (!defined('_MODIFY_USER')) define( '_MODIFY_USER', 'Modifier l&rsquo;utilisateur');

if (!defined('_NOTES')) define( '_NOTES', 'Notes');
if (!defined('_NOTE1')) define( '_NOTE1', 'Les champs obligatoires sont marqu&eacute;s par un ast&eacute;risque rouge ');
if (!defined('_NOTE2')) define( '_NOTE2', 'Le groupe primaire est obligatoire');
if (!defined('_NOTE3')) define( '_NOTE3', 'Le premier groupe s&eacute;lectionn&eacute sera le groupe primaire');
if (!defined('_USER_GROUPS_TITLE')) define( '_USER_GROUPS_TITLE', 'L&rsquo;utilisateur appartient aux groupes suivants');
if (!defined('_USER_ENTITIES_TITLE')) define( '_USER_ENTITIES_TITLE', 'L&rsquo;utilisateur appartient aux entit&eacute;s suivantes');
if (!defined('_DELETE_GROUPS')) define( '_DELETE_GROUPS', 'Supprimer le(s) groupe(s)');
if (!defined('_ADD_TO_GROUP')) define( '_ADD_TO_GROUP', 'Ajouter &agrave; un groupe');
if (!defined('_CHOOSE_PRIMARY_GROUP')) define( '_CHOOSE_PRIMARY_GROUP', 'Choisir comme groupe primaire');
if (!defined('_USER_BELONGS_NO_GROUP')) define( '_USER_BELONGS_NO_GROUP', 'L&rsquo;utilisateur n&rsquo;appartient &agrave; aucun groupe');
if (!defined('_USER_BELONGS_NO_ENTITY')) define( '_USER_BELONGS_NO_ENTITY', 'L&rsquo;utilisateur n&rsquo;appartient &agrave; aucune  entit&eacute;');
if (!defined('_CHOOSE_ONE_GROUP')) define( '_CHOOSE_ONE_GROUP', 'Choisissez au moins un groupe');
if (!defined('_PRIMARY_GROUP')) define( '_PRIMARY_GROUP', 'Groupe primaire');
if (!defined('_CHOOSE_GROUP')) define( '_CHOOSE_GROUP', 'Choisissez un groupe');
if (!defined('_ROLE')) define( '_ROLE', 'R&ocirc;le');

if (!defined('_THE_PSW')) define( '_THE_PSW', 'Le mot de passe');
if (!defined('_THE_PSW_VALIDATION')) define( '_THE_PSW_VALIDATION', 'La validation du mot de passe' );
if (!defined('_REENTER_PSW')) define( '_REENTER_PSW', 'Retaper le mot de passe');
if (!defined('_USER_ACCESS_DEPARTMENT')) define( '_USER_ACCESS_DEPARTMENT', 'L&rsquo;utilisateur a acc&egrave;s aux services suivants');
if (!defined('_FIRST_PSW')) define( '_FIRST_PSW', 'Le premier mot de passe ');
if (!defined('_SECOND_PSW')) define( '_SECOND_PSW', 'Le deuxi&egrave;me mot de passe ');

if (!defined('_PASSWORD_MODIFICATION')) define( '_PASSWORD_MODIFICATION', 'Changement du mot de passe');
if (!defined('_PASSWORD_FOR_USER')) define( '_PASSWORD_FOR_USER', 'Le mot de passe pour l&rsquo;utilisateur');
if (!defined('_HAS_BEEN_RESET')) define( '_HAS_BEEN_RESET', 'a &eacute;t&eacute; r&eacute;initialis&eacute;');
if (!defined('_NEW_PASW_IS')) define( '_NEW_PASW_IS', 'Le nouveau mot de passe est ');
if (!defined('_DURING_NEXT_CONNEXION')) define( '_DURING_NEXT_CONNEXION', 'Lors de la prochaine connexion');
if (!defined('_MUST_CHANGE_PSW')) define( '_MUST_CHANGE_PSW', 'doit modifier son mot de passe');

if (!defined('_NEW_PASSWORD_USER')) define( '_NEW_PASSWORD_USER', 'R&eacute;initialisation du mot de passe de l&rsquo;utilisateur');
if (!defined('_PASSWORD_NOT_CHANGED')) {
    define('_PASSWORD_NOT_CHANGED', 'Probl&egrave;me lors du changement de mot de passe');
}
/************** Types de document : Liste + Formulaire**************/

if (!defined('_DOCTYPES_LIST')) define( '_DOCTYPES_LIST', 'Liste des types de document');
if (!defined('_ADD_DOCTYPE')) define( '_ADD_DOCTYPE', 'Ajouter un type');
if (!defined('_ALL_DOCTYPES')) define( '_ALL_DOCTYPES', 'Tous les types');
if (!defined('_TYPES')) define( '_TYPES', 'types');

if (!defined('_DOCTYPE_MODIFICATION')) define( '_DOCTYPE_MODIFICATION', 'Modification d&rsquo;un type de document');
if (!defined('_DOCTYPE_CREATION')) define( '_DOCTYPE_CREATION', 'Cr&eacute;ation d&rsquo;un type de document');

if (!defined('_MODIFY_DOCTYPE')) define( '_MODIFY_DOCTYPE', 'Valider les changements');
if (!defined('_ATTACH_SUBFOLDER')) define( '_ATTACH_SUBFOLDER', 'Rattach&eacute; &agrave; la sous-chemise');
if (!defined('_CHOOSE_SUBFOLDER')) define( '_CHOOSE_SUBFOLDER', 'Choisissez une sous-chemise');
if (!defined('_MANDATORY_FOR_COMPLETE')) define( '_MANDATORY_FOR_COMPLETE', 'Obligatoire pour la compl&eacute;tude du dossier d&rsquo;embauche');
if (!defined('_MORE_THAN_ONE')) define( '_MORE_THAN_ONE', 'Pi&egrave;ce it&eacute;rative');
if (!defined('_MANDATORY_FIELDS_IN_INDEX')) define( '_MANDATORY_FIELDS_IN_INDEX', 'Champs obligatoires &agrave; l&rsquo;indexation');
if (!defined('_DIPLOMA_LEVEL')) define( '_DIPLOMA_LEVEL', 'Niveau de dipl&ocirc;me');
if (!defined('_THE_DIPLOMA_LEVEL')) define( '_THE_DIPLOMA_LEVEL', 'Le niveau de dipl&ocirc;me');
if (!defined('_DATE_END_DETACH_TIME')) define( '_DATE_END_DETACH_TIME', 'Date de fin de p&eacute;riode de d&eacute;tachement');
if (!defined('_START_DATE')) define( '_START_DATE', 'Date de d&eacute;but');
if (!defined('_START_DATE_PROBATION')) define( '_START_DATE_PROBATION', 'Date de d&eacute;but de p&eacute;riode de probatoire');
if (!defined('_END_DATE')) define( '_END_DATE', 'Date de fin');
if (!defined('_END_DATE_PROBATION')) define( '_END_DATE_PROBATION', 'Date de fin de p&eacute;riode de probatoire');
if (!defined('_START_DATE_TRIAL')) define( '_START_DATE_TRIAL', 'Date de d&eacute;but de p&eacute;riode d&rsquo;essai');
if (!defined('_START_DATE_MISSION')) define( '_START_DATE_MISSION', 'Date de d&eacute;but de mission');
if (!defined('_END_DATE_TRIAL')) define( '_END_DATE_TRIAL', 'Date de fin de p&eacute;riode d&rsquo;essai');
if (!defined('_END_DATE_MISSION')) define( '_END_DATE_MISSION', 'Date de fin de mission');
if (!defined('_EVENT_DATE')) define( '_EVENT_DATE', 'Date de l&rsquo;&eacute;v&egrave;nement');
if (!defined('_VISIT_DATE')) define( '_VISIT_DATE', 'Date de la visite');
if (!defined('_CHANGE_DATE')) define( '_CHANGE_DATE', 'Date du changement ');
if (!defined('_DOCTYPES_LIST2')) define( '_DOCTYPES_LIST2', 'Liste des types de pi&egrave;ce');

if (!defined('_INDEX_FOR_DOCTYPES')) define( '_INDEX_FOR_DOCTYPES', 'Index possibles pour les types de document');
if (!defined('_FIELD')) define( '_FIELD', 'Champ');
if (!defined('_USED')) define( '_USED', 'Utilis&eacute;');
if (!defined('_MANDATORY')) define( '_MANDATORY', 'Obligatoire');
if (!defined('_ITERATIVE')) define( '_ITERATIVE', 'It&eacute;ratif');
if (!defined('_NATURE_FIELD')) define( '_NATURE_FIELD', 'Nature champ');
if (!defined('_TYPE_FIELD')) define( '_TYPE_FIELD', 'Type champ');
if (!defined('_DB_COLUMN')) define( '_DB_COLUMN', 'Colonne BDD');
if (!defined('_FIELD_VALUES')) define( '_FIELD_VALUES', 'Valeurs');

if (!defined('_MASTER_TYPE')) define( '_MASTER_TYPE', 'Type ma&icirc;tre');

/************** structures : Liste + Formulaire**************/
if (!defined('_STRUCTURE_LIST')) define( '_STRUCTURE_LIST', 'Liste des chemises');
if (!defined('_STRUCTURES')) define( '_STRUCTURES', 'chemise(s)');
if (!defined('_STRUCTURE')) define( '_STRUCTURE', 'Chemise');
if (!defined('_ALL_STRUCTURES')) define( '_ALL_STRUCTURES', 'Toutes les chemises');

if (!defined('_THE_STRUCTURE')) define( '_THE_STRUCTURE', 'La chemise');
if (!defined('_STRUCTURE_MODIF')) define( '_STRUCTURE_MODIF', 'Modification de la chemise');
if (!defined('_ID_STRUCTURE_PB')) define( '_ID_STRUCTURE_PB', 'Il y a un probl&egrave;me avec l&rsquo;identifiant de la chemise');
if (!defined('_NEW_STRUCTURE_ADDED')) define( '_NEW_STRUCTURE_ADDED', 'Ajout d&rsquo;une nouvelle chemise');
if (!defined('_NEW_STRUCTURE')) define( '_NEW_STRUCTURE', 'Nouvelle chemise');
if (!defined('_DESC_STRUCTURE_MISSING')) define( '_DESC_STRUCTURE_MISSING', 'Il manque la description de la chemise');
if (!defined('_STRUCTURE_DEL')) define( '_STRUCTURE_DEL', 'Suppression de la chemise');
if (!defined('_DELETED_STRUCTURE')) define( '_DELETED_STRUCTURE', 'Chemise supprim&eacute;e');
if (! defined('_FONT_COLOR')) {
    define('_FONT_COLOR', 'Couleur de la police');
}
if (! defined('_FONT_SIZE')) {
    define('_FONT_SIZE', 'Taille de la police');
}
if (! defined('_BLACK')) {
    define('_BLACK', 'Noir');
}
if (! defined('_CSS_STYLE')) {
    define('_CSS_STYLE', 'Classe css');
}
/************** sous-dossiers : Liste + Formulaire**************/
if (!defined('_SUBFOLDER_LIST')) define( '_SUBFOLDER_LIST', 'Liste des sous-chemises');
if (!defined('_SUBFOLDERS')) define( '_SUBFOLDERS', 'sous-chemise(s)');
if (!defined('_ALL_SUBFOLDERS')) define( '_ALL_SUBFOLDERS', 'Toutes les sous-chemises');
if (!defined('_SUBFOLDER')) define( '_SUBFOLDER', 'sous-chemise');

if (!defined('_ADD_SUBFOLDER')) define( '_ADD_SUBFOLDER', 'Ajouter une nouvelle sous-chemise');
if (!defined('_THE_SUBFOLDER')) define( '_THE_SUBFOLDER', 'La sous-chemise');
if (!defined('_SUBFOLDER_MODIF')) define( '_SUBFOLDER_MODIF', 'Modification de la sous-chemise');
if (!defined('_SUBFOLDER_CREATION')) define( '_SUBFOLDER_CREATION', 'Cr&eacute;ation de la sous-chemise');
if (!defined('_SUBFOLDER_ID_PB')) define( '_SUBFOLDER_ID_PB', 'Il y a un probleme avec l&rsquo;identifiant de la sous-chemise');
if (!defined('_SUBFOLDER_ADDED')) define( '_SUBFOLDER_ADDED', 'Ajout d&rsquo;unen nouvelle sous-chemise');
if (!defined('_NEW_SUBFOLDER')) define( '_NEW_SUBFOLDER', 'Nouvelle sous-chemise');
if (!defined('_STRUCTURE_MANDATORY')) define( '_STRUCTURE_MANDATORY', 'La chemise est obligatoire');
if (!defined('_SUBFOLDER_DESC_MISSING')) define( '_SUBFOLDER_DESC_MISSING', 'Il manque la description de la sous-chemise');

if (!defined('_ATTACH_STRUCTURE')) define( '_ATTACH_STRUCTURE', 'Rattachement &agrave; une chemise');
if (!defined('_CHOOSE_STRUCTURE')) define( '_CHOOSE_STRUCTURE', 'Choissisez une chemise');

if (!defined('_DEL_SUBFOLDER')) define( '_DEL_SUBFOLDER', 'Suppression de la sous-chemise');
if (!defined('_SUBFOLDER_DELETED')) define( '_SUBFOLDER_DELETED', 'Sous-chemise supprim&eacute;e');


/************** Status **************/

if (!defined('_STATUS_LIST')) define( '_STATUS_LIST', 'Liste des statuts');
if (!defined('_ADD_STATUS')) define( '_ADD_STATUS', 'Ajouter nouveau statut');
if (!defined('_ALL_STATUS')) define( '_ALL_STATUS', 'Tous les statuts');
if (!defined('_STATUS_PLUR')) define( '_STATUS_PLUR', 'Statut(s)');
if (!defined('_STATUS_SING')) define( '_STATUS_SING', 'statut');

if (!defined('_TO_PROCESS')) define( '_TO_PROCESS','A traiter');
if (!defined('_IN_PROGRESS')) define( '_IN_PROGRESS','En cours');
if (!defined('_FIRST_WARNING')) define( '_FIRST_WARNING','1ere Relance');
if (!defined('_SECOND_WARNING')) define( '_SECOND_WARNING','2e Relance');
if (!defined('_CLOSED')) define( '_CLOSED','Clos');
if (!defined('_NEW')) define( '_NEW','Nouveaux');
if (!defined('_LATE')) define( '_LATE', 'En retard');

if (!defined('_STATUS_DELETED')) define( '_STATUS_DELETED', 'Suppression du statut');
if (!defined('_DEL_STATUS')) define( '_DEL_STATUS', 'Statut supprim&eacute;');
if (!defined('_MODIFY_STATUS')) define( '_MODIFY_STATUS', 'Modification du statut');
if (!defined('_STATUS_ADDED')) define( '_STATUS_ADDED','Ajout d&rsquo;un nouveau statut');
if (!defined('_STATUS_MODIFIED')) define( '_STATUS_MODIFIED','Modification d&rsquo;un statut');
if (!defined('_NEW_STATUS')) define( '_NEW_STATUS', 'Nouveau statut');
if (!defined('_IS_SYSTEM')) define( '_IS_SYSTEM', 'Syst&egrave;me');
if (!defined('_CAN_BE_SEARCHED')) define( '_CAN_BE_SEARCHED', 'Recherche');
if (!defined('_CAN_BE_MODIFIED')) define( '_CAN_BE_MODIFIED', 'Modification des index');
if (!defined('_THE_STATUS')) define( '_THE_STATUS', 'Le statut ');
if (!defined('_ADMIN_STATUS')) define( '_ADMIN_STATUS', 'Statuts');
/************* Actions **************/

if (!defined('_ACTION_LIST')) define( '_ACTION_LIST', 'Liste des actions');
if (!defined('_ADD_ACTION')) define( '_ADD_ACTION', 'Ajouter nouvelle action');
if (!defined('_ALL_ACTIONS')) define( '_ALL_ACTIONS', 'Toutes les actions');
if (!defined('_ACTION_HISTORY')) define( '_ACTION_HISTORY', 'Tracer l&rsquo;action');

if (!defined('_ACTION_DELETED')) define( '_ACTION_DELETED', 'Suppression de l&rsquo;action');
if (!defined('_DEL_ACTION')) define( '_DEL_ACTION', 'Action supprim&eacute;e');
if (!defined('_MODIFY_ACTION')) define( '_MODIFY_ACTION', 'Modification de l&rsquo;action');
if (!defined('_ACTION_ADDED')) define( '_ACTION_ADDED','Ajout d&rsquo;une nouvelle action');
if (!defined('_ACTION_MODIFIED')) define( '_ACTION_MODIFIED','Modification d&rsquo;une action');
if (!defined('_NEW_ACTION')) define( '_NEW_ACTION', 'Nouvelle action');
if (!defined('_THE_ACTION')) define( '_THE_ACTION', 'L&rsquo;action ');
if (!defined('_ADMIN_ACTIONS')) define( '_ADMIN_ACTIONS', 'Actions');


/************** Historique**************/
if (!defined('_HISTORY_TITLE')) define( '_HISTORY_TITLE', 'Historique des &eacute;v&egrave;nements');
if (!defined('_HISTORY_BATCH_TITLE')) define( '_HISTORY_BATCH_TITLE', 'Historique des &eacute;v&egrave;nements des batchs');
if (!defined('_HISTORY')) define( '_HISTORY', 'Historique');
if (!defined('_HISTORY_BATCH')) define( '_HISTORY_BATCH', 'Historique du batch');
if (!defined('_BATCH_NAME')) define( '_BATCH_NAME', 'Nom batch');
if (!defined('_CHOOSE_BATCH')) define( '_CHOOSE_BATCH', 'Choisir batch');
if (!defined('_BATCH_ID')) define( '_BATCH_ID', 'Id batch');
if (!defined('_TOTAL_PROCESSED')) define( '_TOTAL_PROCESSED', 'Documents trait&eacute;s');
if (!defined('_TOTAL_ERRORS')) define( '_TOTAL_ERRORS', 'Documents en erreurs');
if (!defined('_ONLY_ERRORS')) define( '_ONLY_ERRORS', 'Seulement avec erreurs');
if (!defined('_INFOS')) define( '_INFOS', 'Infos');

/************** Admin de l'architecture  (plan de classement) **************/
if (!defined('_ADMIN_ARCHI')) define( '_ADMIN_ARCHI', 'Administration du plan de classement');
if (!defined('_MANAGE_STRUCTURE')) define( '_MANAGE_STRUCTURE', 'G&eacute;rer les chemises');
if (!defined('_MANAGE_STRUCTURE_DESC')) define( '_MANAGE_STRUCTURE_DESC', 'Administrer les chemises. Celles-ci constituent l&rsquo;&eacute;l&eacute;ment le plus haut du plan de classement. Si le module Folder est connect&eacute;, vous pouvez associer un type de dossier &agrave; un plan de classement.');
if (!defined('_MANAGE_SUBFOLDER')) define( '_MANAGE_SUBFOLDER', 'G&eacute;rer les sous-chemises');
if (!defined('_MANAGE_SUBFOLDER_DESC')) define( '_MANAGE_SUBFOLDER_DESC', 'G&eacute;rer les sous-chemises à l&rsquo;int&eacute;rieur des chemises.');
if (!defined('_ARCHITECTURE')) define( '_ARCHITECTURE', 'Plan de classement');

/************************* Messages d'erreurs ***********************************/
if (!defined('_MORE_INFOS')) define( '_MORE_INFOS', 'Pour plus d&rsquo;informations, contactez votre administrateur ');
if (!defined('_ALREADY_EXISTS')) define( '_ALREADY_EXISTS', 'existe d&eacute;j&agrave; !');

// class usergroups
if (!defined('_NO_GROUP')) define( '_NO_GROUP', 'Le groupe n&rsquo;existe pas !');
if (!defined('_NO_SECURITY_AND_NO_SERVICES')) define( '_NO_SECURITY_AND_NO_SERVICES', 'n&rsquo;a aucune s&eacute;curit&eacute; d&eacute;finie et aucun service');
if (!defined('_GROUP_ADDED')) define( '_GROUP_ADDED', 'Nouveau groupe ajout&eacute;');
if (!defined('_SYNTAX_ERROR_WHERE_CLAUSE')) define( '_SYNTAX_ERROR_WHERE_CLAUSE', 'erreur de syntaxe dans la clause where');
if (!defined('_GROUP_UPDATED')) define( '_GROUP_UPDATED', 'Groupe modifi&eacute;');
if (!defined('_AUTORIZED_GROUP')) define( '_AUTORIZED_GROUP', 'Groupe autoris&eacute;');
if (!defined('_SUSPENDED_GROUP')) define( '_SUSPENDED_GROUP', 'Groupe suspendu');
if (!defined('_DELETED_GROUP')) define( '_DELETED_GROUP', 'Groupe supprim&eacute;');
if (!defined('_GROUP_UPDATE')) define( '_GROUP_UPDATE', 'Modification du groupe;');
if (!defined('_GROUP_AUTORIZATION')) define( '_GROUP_AUTORIZATION', 'Autorisation du groupe');
if (!defined('_GROUP_SUSPENSION')) define( '_GROUP_SUSPENSION', 'Suspension du groupe');
if (!defined('_GROUP_DELETION')) define( '_GROUP_DELETION', 'Suppression du groupe');
if (!defined('_GROUP_DESC')) define( '_GROUP_DESC', 'La description du groupe ');
if (!defined('_GROUP_ID')) define( '_GROUP_ID', 'L&rsquo;identifiant du groupe');
if (!defined('_EXPORT_RIGHT')) define( '_EXPORT_RIGHT', 'Droits d&rsquo;export');

//class users
if (!defined('_USER_NO_GROUP')) define( '_USER_NO_GROUP', 'Vous n&rsquo;appartenez &agrave; aucun groupe');
if (!defined('_SUSPENDED_ACCOUNT')) define( '_SUSPENDED_ACCOUNT', 'Votre compte utilisateur a &eacute;t&eacute; suspendu');
if (!defined('_BAD_LOGIN_OR_PSW')) define( '_BAD_LOGIN_OR_PSW', 'Mauvais nom d&rsquo;utilisateur ou mauvais mot de passe');
if (!defined('_WRONG_SECOND_PSW')) define( '_WRONG_SECOND_PSW', 'Le deuxi&egrave;me mot de passe ne correspond pas au premier mot de passe !');
if (!defined('_AUTORIZED_USER')) define( '_AUTORIZED_USER', 'Utilisateur autoris&eacute;');
if (!defined('_SUSPENDED_USER')) define( '_SUSPENDED_USER', 'Utilisateur suspendu');
if (!defined('_DELETED_USER')) define( '_DELETED_USER', 'Utilisateur supprim&eacute;');
if (!defined('_USER_DELETION')) define( '_USER_DELETION', 'Suppression de l&rsquo;utilisateur;');
if (!defined('_USER_AUTORIZATION')) define( '_USER_AUTORIZATION', 'Autorisation de l&rsquo;utilisateur');
if (!defined('_USER_SUSPENSION')) define( '_USER_SUSPENSION', 'Suspension de l&rsquo;utilisateur');
if (!defined('_USER_UPDATED')) define( '_USER_UPDATED', 'Utilisateur modifi&eacute;');
if (!defined('_USER_UPDATE')) define( '_USER_UPDATE', 'Modification d&rsquo;un utilisateur');
if (!defined('_USER_ADDED')) define( '_USER_ADDED', 'Nouvel utilisateur ajout&eacute;');
if (!defined('_NO_PRIMARY_GROUP')) define( '_NO_PRIMARY_GROUP', 'Aucun groupe primaire s&eacute;lectionn&eacute; !');
if (!defined('_THE_USER')) define( '_THE_USER', 'L&rsquo;utilisateur ');
if (!defined('_USER_ID')) define( '_USER_ID', 'L&rsquo;identifiant de l&rsquo;utilisateur');
if (!defined('_MY_INFO')) define( '_MY_INFO', 'Mon Profil');


//class types
if (!defined('_UNKNOWN_PARAM')) define( '_UNKNOWN_PARAM', 'Param&egrave;tres inconnus');
if (!defined('_DOCTYPE_UPDATED')) define( '_DOCTYPE_UPDATED', 'Type de document modifi&eacute;');
if (!defined('_DOCTYPE_UPDATE')) define( '_DOCTYPE_UPDATE', 'Modification du type de document');
if (!defined('_DOCTYPE_ADDED')) define( '_DOCTYPE_ADDED', 'Nouveau type de document ajout&eacute;');
if (!defined('_DELETED_DOCTYPE')) define( '_DELETED_DOCTYPE', 'Type de document supprim&eacute;');
if (!defined('_DOCTYPE_DELETION')) define( '_DOCTYPE_DELETION', 'Suppression du type de document');
if (!defined('_THE_DOCTYPE')) define( '_THE_DOCTYPE', 'Le type de document ');
if (!defined('_THE_WORDING')) define( '_THE_WORDING', 'Le libell&eacute; ');
if (!defined('_THE_TABLE')) define( '_THE_TABLE', 'La table ');
if (!defined('_PIECE_TYPE')) define( '_PIECE_TYPE', 'Type de pi&egrave;ce');

//class db
if (!defined('_CONNEXION_ERROR')) define( '_CONNEXION_ERROR', 'Erreur &agrave; la connexion');
if (!defined('_SELECTION_BASE_ERROR')) define( '_SELECTION_BASE_ERROR', 'Erreur &agrave; la s&eacute;lection de la base');
if (!defined('_QUERY_ERROR')) define( '_QUERY_ERROR', 'Erreur &agrave; la requ&ecirc;te');
if (!defined('_CLOSE_CONNEXION_ERROR')) define( '_CLOSE_CONNEXION_ERROR', 'Erreur &agrave; la fermeture de la connexion');
if (!defined('_ERROR_NUM')) define( '_ERROR_NUM', 'L&rsquo;erreur n&deg;');
if (!defined('_HAS_JUST_OCCURED')) define( '_HAS_JUST_OCCURED', 'vient de se produire');
if (!defined('_MESSAGE')) define( '_MESSAGE', 'Message');
if (!defined('_QUERY')) define( '_QUERY', 'Requ&ecirc;te');
if (!defined('_LAST_QUERY')) define( '_LAST_QUERY', 'Derni&egrave;re requ&ecirc;te');

//Autres
if (!defined('_NO_GROUP_SELECTED')) define( '_NO_GROUP_SELECTED', 'Aucun groupe s&eacute;lectionn&eacute;');
if (!defined('_NOW_LOG_OUT')) define( '_NOW_LOG_OUT', 'Vous &ecirc;tes maintenant d&eacute;connect&eacute;');
if (!defined('_DOC_NOT_FOUND')) define( '_DOC_NOT_FOUND', 'Document introuvable');
if (!defined('_DOUBLED_DOC')) define( '_DOUBLED_DOC', 'Probl&egrave;me de doublons');
if (!defined('_NO_DOC_OR_NO_RIGHTS')) define( '_NO_DOC_OR_NO_RIGHTS', 'Ce document n&rsquo;existe pas ou vous n&rsquo;avez pas les droits n&eacute;cessaires pour y acc&eacute;der');
if (!defined('_INEXPLICABLE_ERROR')) define( '_INEXPLICABLE_ERROR', 'Une erreur inexplicable est survenue');
if (!defined('_TRY_AGAIN_SOON')) define( '_TRY_AGAIN_SOON', 'Veuillez r&eacute;essayer dans quelques instants');
if (!defined('_NO_OTHER_RECIPIENT')) define( '_NO_OTHER_RECIPIENT', 'Il n&rsquo;y a pas d&rsquo;autre destinataire de ce courrier');
if (!defined('_WAITING_INTEGER')) define( '_WAITING_INTEGER', 'Entier attendu');
if (!defined('_WAITING_FLOAT')) define( '_WAITING_FLOAT', 'Nombre flottant attendu');

if (!defined('_DEFINE')) define( '_DEFINE', 'Pr&eacute;ciser');
if (!defined('_NUM')) define( '_NUM', 'N&deg;');
if (!defined('_ROAD')) define( '_ROAD', 'Rue');
if (!defined('_POSTAL_CODE')) define( '_POSTAL_CODE','Code Postal');
if (!defined('_CITY')) define( '_CITY', 'Ville');

if (!defined('_CHOOSE_USER')) define( '_CHOOSE_USER', 'Vous devez choisir un utilisateur');
if (!defined('_CHOOSE_USER2')) define( '_CHOOSE_USER2', 'Choisissez un utilisateur');
if (!defined('_NUM2')) define( '_NUM2', 'Num&eacute;ro');
if (!defined('_UNDEFINED')) define( '_UNDEFINED', 'N.C.');
if (!defined('_CONSULT_EXTRACTION')) define( '_CONSULT_EXTRACTION', 'vous pouvez consulter les documents extraits ici');
if (!defined('_SERVICE')) define( '_SERVICE', 'Service');
if (!defined('_AVAILABLE_SERVICES')) define( '_AVAILABLE_SERVICES', 'Services disponibles');

// Mois
if (!defined('_JANUARY')) define( '_JANUARY', 'Janvier');
if (!defined('_FEBRUARY')) define( '_FEBRUARY', 'F&eacute;vrier');
if (!defined('_MARCH')) define( '_MARCH', 'Mars');
if (!defined('_APRIL')) define( '_APRIL', 'Avril');
if (!defined('_MAY')) define( '_MAY', 'Mai');
if (!defined('_JUNE')) define( '_JUNE', 'Juin');
if (!defined('_JULY')) define( '_JULY', 'Juillet');
if (!defined('_AUGUST')) define( '_AUGUST', 'Ao&ucirc;t');
if (!defined('_SEPTEMBER')) define( '_SEPTEMBER', 'Septembre');
if (!defined('_OCTOBER')) define( '_OCTOBER', 'Octobre');
if (!defined('_NOVEMBER')) define( '_NOVEMBER', 'Novembre');
if (!defined('_DECEMBER')) define( '_DECEMBER', 'D&eacute;cembre');




if (!defined('_NOW_LOGOUT')) define( '_NOW_LOGOUT', 'Vous &ecirc;tes maintenant d&eacute;connect&eacute;.');

if (!defined('_WELCOME2')) define( '_WELCOME2', 'Bienvenue');
if (!defined('_WELCOME_NOTES1')) define( '_WELCOME_NOTES1', 'Pour naviguer dans l&rsquo;application');
if (!defined('_WELCOME_NOTES2')) define( '_WELCOME_NOTES2', 'utilisez le <b>menu</b> ci-dessus');
if (!defined('_WELCOME_NOTES3')) define( '_WELCOME_NOTES3', 'L&rsquo;&eacute;quipe Maarch est tr&egrave;s fi&egrave;re de vous pr&eacute;senter ce nouveau Framework marquant une &eacute;tape importante dans le d&eacute;veloppement de Maarch.<br><br>Dans cette application d&rsquo;exemple, vous pouvez :<ul><li>o Cr&eacute;er des boites d&rsquo;archives afin d&rsquo;y ranger les documents papier num&eacute;ris&eacute;s <b>(module <i> Physical Archive</i>)</b></li><li>o Imprimer des s&eacute;parateurs code-barre <b>(module <i> Physical Archive</i>)</b></li><li>o Indexer de nouveaux documents dans deux collections documentaires distinctes (documents de production et factures client) <b>(module <i> Indexing & Searching</i>)</b></li><li>o Importer en masse des factures clients <b>(utilitaire <i> Maarch AutoImport</i>)</b></li><li>o Consulter les deux fonds documentaires d&rsquo;exemple <b>(module <i> Indexing & Searching</i>)</b></li><li>o Parcourir la collection des factures au travers d&rsquo;arbres dynamiques <b>(module <i> AutoFoldering</i>)</b></li></ul><br><br>');
if (!defined('_WELCOME_NOTES5')) define( '_WELCOME_NOTES5', 'Consultez le <u><a href="http://www.maarch.org/maarch_wiki/Maarch_Framework_v3">wiki maarch</a></u> pour plus d&rsquo;informations.');
if (!defined('_WELCOME_NOTES6')) define( '_WELCOME_NOTES6', 'Acc&eacute;der au <u><a href="http://www.maarch.org/">site communautaire</a></u> ou au <u><a href="http://www.maarch.org/maarch_forum/">forum</a></u> Maarch.');
if (!defined('_WELCOME_NOTES7')) define( '_WELCOME_NOTES7', '<b>Professionnels</b> : des <u><a href="http://www.maarch.fr/">solutions</a></u> adapt&eacute;es &agrave; vos besoins.');
if (!defined('_WELCOME_COUNT')) define( '_WELCOME_COUNT', 'Nombre de ressources sur la collection');

if (!defined('_CONTRACT_HISTORY')) define( '_CONTRACT_HISTORY', 'Historique des contrats');

if (!defined('_CLICK_CALENDAR')) define( '_CLICK_CALENDAR', 'Cliquez pour choisir une date');
if (!defined('_MODULES')) define( '_MODULES', 'Modules');
if (!defined('_CHOOSE_MODULE')) define( '_CHOOSE_MODULE', 'Choisissez un module');
if (!defined('_FOLDER')) define( '_FOLDER', 'Dossier');
if (!defined('_INDEX')) define( '_INDEX', 'Index');

//COLLECTIONS
if (!defined('_MAILS')) define( '_MAILS', 'Courriers');
if (!defined('_DOCUMENTS')) define( '_DOCUMENTS', 'Prets immobiliers');
if (!defined('_INVOICES')) define( '_INVOICES', 'Factures');
if (!defined('_CHOOSE_COLLECTION')) define( '_CHOOSE_COLLECTION', 'Choisir une collection');
if (!defined('_COLLECTION')) define( '_COLLECTION', 'Collection');

if (!defined('_EVENT')) define( '_EVENT', 'Ev&egrave;nement');
if (!defined('_LINK')) define( '_LINK', 'Lien');


//BITMASK
if (!defined('_BITMASK_VALUE_ALREADY_EXIST')) define( '_BITMASK_VALUE_ALREADY_EXIST' , 'Bitmask d&eacute;j&agrave; utilis&eacute; dans un autre type');

if (!defined('_ASSISTANT_MODE')) define( '_ASSISTANT_MODE', 'Mode assisstant');
if (!defined('_EDIT_WITH_ASSISTANT')) define( '_EDIT_WITH_ASSISTANT', 'Cliquez ici pour &eacute;diter la clause where avec le mode assistant');
if (!defined('_VALID_THE_WHERE_CLAUSE')) define( '_VALID_THE_WHERE_CLAUSE', 'Cliquez ici pour VALIDER la clause where');
if (!defined('_DELETE_SHORT')) define( '_DELETE_SHORT', 'Suppression');
if (!defined('_CHOOSE_ANOTHER_SUBFOLDER')) define( '_CHOOSE_ANOTHER_SUBFOLDER', 'Choisissez une autre sous-chemise');
if (!defined('_DOCUMENTS_EXISTS_FOR_COLLECTION')) define( '_DOCUMENTS_EXISTS_FOR_COLLECTION', 'documents existent pour la collection');
if (!defined('_MUST_CHOOSE_COLLECTION_FIRST')) define( '_MUST_CHOOSE_COLLECTION_FIRST', 'Vous devez choisir une collection');
if (!defined('_CANTCHANGECOLL')) define( '_CANTCHANGECOLL', 'Vous ne pouvez pas changer la collection');
if (!defined('_DOCUMENTS_EXISTS_FOR_COUPLE_FOLDER_TYPE_COLLECTION')) define( '_DOCUMENTS_EXISTS_FOR_COUPLE_FOLDER_TYPE_COLLECTION', 'documents existent pour le couple type de dossier/collection');

if (!defined('_NO_RIGHT')) define( '_NO_RIGHT', 'Erreur');
if (!defined('_NO_RIGHT_TXT')) define( '_NO_RIGHT_TXT', 'Vous avez tent&eacute; d&rsquo;acc&eacute;der &agrave; un document auquel vous n&rsquo;avez pas droit ou le document n&rsquo;existe pas...');
if (!defined('_NUM_GED')) define( '_NUM_GED', 'N&deg; GED');

///// Manage action error
if (!defined('_AJAX_PARAM_ERROR')) define( '_AJAX_PARAM_ERROR', 'Erreur passage param&egrave;tres Ajax');
if (!defined('_ACTION_CONFIRM')) define( '_ACTION_CONFIRM', 'Voulez-vous effectuer l&rsquo;action suivante : ');
if (!defined('_ACTION_NOT_IN_DB')) define( '_ACTION_NOT_IN_DB', 'Action non enregistr&eacute;e en base');
if (!defined('_ERROR_PARAM_ACTION')) define( '_ERROR_PARAM_ACTION', 'Erreur param&egrave;trage de l&rsquo;action');
if (!defined('_SQL_ERROR')) define( '_SQL_ERROR', 'Erreur SQL');
if (!defined('_ACTION_DONE')) define( '_ACTION_DONE', 'Action effectu&eacute;e');
if (!defined('_ACTION_PAGE_MISSING')) define( '_ACTION_PAGE_MISSING', 'Page de r&eacute;sultat de l&rsquo;action manquante');
if (!defined('_ERROR_SCRIPT')) define( '_ERROR_SCRIPT', 'Page de r&eacute;sultat de l&rsquo;action : erreur dans le script ou fonction manquante');
if (!defined('_SERVER_ERROR')) define( '_SERVER_ERROR', 'Erreur serveur');
if (!defined('_CHOOSE_ONE_DOC')) define( '_CHOOSE_ONE_DOC', 'Choisissez au moins un document');
if (!defined('_CHOOSE_ONE_OBJECT')) define( '_CHOOSE_ONE_OBJECT', 'Choisissez au moins un &eacute;l&eacute;ment');

if (!defined('_CLICK_LINE_TO_CHECK_INVOICE')) define( '_CLICK_LINE_TO_CHECK_INVOICE', 'Cliquer sur une ligne pour v&eacute;rifier une facture');
if (!defined('_FOUND_INVOICES')) define( '_FOUND_INVOICES', ' facture(s) trouv&eacute;e(s)');
if (!defined('_SIMPLE_CONFIRM')) define( '_SIMPLE_CONFIRM', 'Confirmation simple');
if (!defined('_CHECK_INVOICE')) define( '_CHECK_INVOICE', 'V&eacute;rifier facture');

if (!defined('_REDIRECT_TO')) define( '_REDIRECT_TO', 'Rediriger vers');
if (!defined('_NO_STRUCTURE_ATTACHED')) define( '_NO_STRUCTURE_ATTACHED', 'Ce type de document n&rsquo;est attach&eacute; &agrave; aucune chemise');

//Postindexing action pages
if (!defined('_POSTINDEXING_PAGE')) define('_POSTINDEXING_PAGE', 'Formulaire de vid&eacute;ocodage');
if (!defined('_POSTINDEXING_FOLDER_PAGE')) define('_POSTINDEXING_FOLDER_PAGE', 'Formulaire de vid&eacute;ocodage (dossiers)');

//Advanced Physical Archive action page
if (!defined('_APA_CONFIRM_IN_OUT')) define( '_APA_CONFIRM_IN_OUT', 'Confirmation de R&eacute;servation / R&eacute;int&eacute;gration');

///// Credits
if (!defined('_MAARCH_CREDITS')) define( '_MAARCH_CREDITS', 'A propos de Maarch&nbsp;');


if (!defined('_CR_LONGTEXT_INFOS')) define( '_CR_LONGTEXT_INFOS', '<p>Maarch Framework 3 est une infrastructure de <b>GED de Production</b>, r&eacute;pondant en standard &agrave; la plupart des besoins de gestion op&eacute;rationnelle de contenu d&rsquo;une organisation. La tr&egrave;s grande majorit&eacute; des composants du Framework est diffusé sous les termes de la licence open source GNU GPLv3, de sorte que le coût d&rsquo;impl&eacute;mentation rend la solution aborbable pour tout type d&rsquo;organisation (public, priv&eacute;, parapublic, monde associatif).</p> <p>Pour autant, Maarch Framework ayant &eacute;t&eacute; conçu par deux consultants cumulant &agrave; eux deux plus de 20 ans d&rsquo;expertise en Syst&egrave;mes d&rsquo;Archivage &Eacute;lectronique et en &Eacute;ditique, le produit offre <b>toutes les garanties de robustesse, d&rsquo;int&eacute;grit&eacute;, de performance</b> que l&rsquo;on doit attendre de ce type de produit. Un grand soin a &eacute;t&eacute; port&eacute; sur l&rsquo;architecture afin d&rsquo;autoriser des performances maximales sur du mat&eacute;riel standard.</p><p>Maarch est d&eacute;velopp&eacute; en PHP5 objet. Il est compatible avec les 4 moteurs de bases de donn&eacute;es suivants&nbsp;: MySQL, PostgreSQL, SQLServer, et bientôt Oracle.</p> <p>Maarch est <b>totalement modulaire</b>&nbsp;: toutes les fonctionnalit&eacute;s sont regroup&eacute;es dans des modules exposant des services qui peuvent être activ&eacute;s/d&eacute;sactiv&eacute;s en fonction du profil de l&rsquo;utilisateur. Un ing&eacute;nieur exp&eacute;riment&eacute; peut ajouter ou remplacer un module existant sans toucher au coeur du syst&egrave;me.</p><p>Maarch propose un sch&eacute;ma global et <b>tous les outils pour acqu&eacute;rir, g&eacute;rer, conserver puis restituer les flux documentaires de production</b>.');

if (!defined('_PROCESSING_DATE')) define( '_PROCESSING_DATE', 'Date limite de traitement');
if (!defined('_PROCESS_NUM')) define( '_PROCESS_NUM','Traitement du courrier n&deg;');
if (!defined('_PROCESS_LIMIT_DATE')) define( '_PROCESS_LIMIT_DATE', 'Date limite de traitement');
if (!defined('_LATE_PROCESS')) define( '_LATE_PROCESS', 'En retard');
if (!defined('_PROCESS_DELAY')) define( '_PROCESS_DELAY', 'D&eacute;lai de traitement');
if (!defined('_ALARM1_DELAY')) define( '_ALARM1_DELAY', 'D&eacute;lai relance 1 (jours) avant terme');
if (!defined('_ALARM2_DELAY')) define( '_ALARM2_DELAY', 'D&eacute;lai relance 2 (jours) apr&egrave;s terme');
if (!defined('_CATEGORY')) define( '_CATEGORY', 'Cat&eacute;gorie');
if (!defined('_CHOOSE_CATEGORY')) define( '_CHOOSE_CATEGORY', 'Choisissez une cat&eacute;gorie');
if (!defined('_RECEIVING_DATE')) define( '_RECEIVING_DATE', 'Date d&rsquo;arriv&eacute;e');
if (!defined('_SUBJECT')) define( '_SUBJECT', 'Objet');
if (!defined('_AUTHOR')) define( '_AUTHOR', 'Auteur');
if (!defined('_DOCTYPE_MAIL')) define( '_DOCTYPE_MAIL', 'Type de courrier');
if (!defined('_PROCESS_LIMIT_DATE_USE')) define( '_PROCESS_LIMIT_DATE_USE', 'Activer la date limite');
if (!defined('_DEPARTMENT_DEST')) define( '_DEPARTMENT_DEST', 'Service traitant');
if (!defined('_DEPARTMENT_EXP')) define( '_DEPARTMENT_EXP', 'Service exp&eacute;diteur');


// Mail Categories
if (!defined('_INCOMING')) define( '_INCOMING', 'Courrier Arriv&eacute;e');
if (!defined('_OUTGOING')) define( '_OUTGOING', 'Courrier D&eacute;part');
if (!defined('_INTERNAL')) define( '_INTERNAL', 'Courrier Interne');
if (!defined('_MARKET_DOCUMENT')) define( '_MARKET_DOCUMENT', 'Document de Sous-dossier');

// Mail Natures
if (!defined('_CHOOSE_NATURE')) define( '_CHOOSE_NATURE', 'Choisir');
if (!defined('_SIMPLE_MAIL')) define( '_SIMPLE_MAIL', 'Courrier simple');
if (!defined('_EMAIL')) define( '_EMAIL', 'Courriel');
if (!defined('_FAX')) define( '_FAX', 'Fax');
if (!defined('_CHRONOPOST')) define( '_CHRONOPOST', 'Chronopost');
if (!defined('_FEDEX')) define( '_FEDEX', 'Fedex');
if (!defined('_REGISTERED_MAIL')) define( '_REGISTERED_MAIL', 'Courrier AR');
if (!defined('_COURIER')) define( '_COURIER', 'Coursier');
if (!defined('_OTHER')) define( '_OTHER', 'Autre');

//Priorities
if (!defined('_NORMAL')) define( '_NORMAL', 'Normal');
if (!defined('_VERY_HIGH')) define( '_VERY_HIGH', 'Tr&egrave;s urgent');
if (!defined('_HIGH')) define( '_HIGH', 'Urgent');
if (!defined('_LOW')) define( '_LOW', 'Basse');
if (!defined('_VERY_LOW')) define( '_VERY_LOW', 'Tr&egrave;s Basse');


if (!defined('_INDEXING_MLB')) define( '_INDEXING_MLB', 'Enregistrer un courrier');
if (!defined('_ADV_SEARCH_MLB')) define( '_ADV_SEARCH_MLB', 'Rechercher un courrier');
if (!defined('_ADV_SEARCH_INVOICES')) define( '_ADV_SEARCH_INVOICES', 'Rechercher une facture');

if (!defined('_ADV_SEARCH_TITLE')) define( '_ADV_SEARCH_TITLE', 'Recherche avanc&eacute;e de document');
if (!defined('_MAIL_OBJECT')) define( '_MAIL_OBJECT', 'Objet du courrier');
//if (!defined('_SHIPPER')) define( '_SHIPPER', 'Emetteur');
//if (!defined('_SENDER')) define( '_SENDER', 'Exp&eacute;diteur');
//if (!defined('_SOCIETY')) define( '_SOCIETY', 'Soci&eacute;t&eacute;');
//if (!defined('_SHIPPER_SEARCH')) define( '_SHIPPER_SEARCH','Dans le champ &eacute;metteur, les recherches ne sont effectu&eacute;es ni sur les civilit&eacute;s, ni sur les pr&eacute;noms.');
//if (!defined('_MAIL_IDENTIFIER')) define( '_MAIL_IDENTIFIER','R&eacute;f&eacute;rence de l&rsquo;affaire');
if (!defined('_N_GED')) define( '_N_GED','Num&eacute;ro GED ');
if (!defined('_GED_NUM')) define( '_GED_NUM', 'N&deg; GED');
if (!defined('_CHOOSE_TYPE_MAIL')) define( '_CHOOSE_TYPE_MAIL','Choisissez un type de courrier');
//if (!defined('_INVOICE_TYPE')) define( '_INVOICE_TYPE','Nature de l&rsquo;envoi');
//if (!defined('_CHOOSE_INVOICE_TYPE')) define( '_CHOOSE_INVOICE_TYPE','Choisissez la nature de l&rsquo;envoi');
if (!defined('_REG_DATE')) define( '_REG_DATE','Date d&rsquo;enregistrement');
if (!defined('_PROCESS_DATE')) define( '_PROCESS_DATE','Date de traitement');
if (!defined('_CHOOSE_STATUS')) define( '_CHOOSE_STATUS','Choisissez un statut');
if (!defined('_PROCESS_RECEIPT')) define( '_PROCESS_RECEIPT','Destinataire(s) pour traitement');
if (!defined('_CHOOSE_RECEIPT')) define( '_CHOOSE_RECEIPT','Choisissez un destinataire');
if (!defined('_TO_CC')) define( '_TO_CC','En copie');
if (!defined('_ADD_COPIES')) define( '_ADD_COPIES','Ajouter des personnes en copie');
//if (!defined('_ANSWER_TYPE')) define( '_ANSWER_TYPE','Type(s) de r&eacute;ponse');
if (!defined('_PROCESS_NOTES')) define( '_PROCESS_NOTES','Notes de traitement');
if (!defined('_DIRECT_CONTACT')) define( '_DIRECT_CONTACT','Prise de contact direct');
if (!defined('_NO_ANSWER')) define( '_NO_ANSWER','Pas de r&eacute;ponse');
if (!defined('_DETAILS')) define( '_DETAILS', 'Fiche d&eacute;taill&eacute;e');
if (!defined('_DOWNLOAD')) define( '_DOWNLOAD', 'T&eacute;l&eacute;charger le courrier');
if (!defined('_SEARCH_RESULTS')) define( '_SEARCH_RESULTS', 'R&eacute;sultat de la recherche');
//if (!defined('_DOCUMENTS')) define( '_DOCUMENTS', 'documents');
if (!defined('_THE_SEARCH')) define( '_THE_SEARCH', 'La recherche');
if (!defined('_CHOOSE_TABLE')) define( '_CHOOSE_TABLE', 'Choisissez une collection');
if (!defined('_SEARCH_COPY_MAIL')) define( '_SEARCH_COPY_MAIL','Chercher dans mes courriers en copie');
if (!defined('_MAIL_PRIORITY')) define( '_MAIL_PRIORITY', 'Priorit&eacute; du courrier');
if (!defined('_CHOOSE_PRIORITY')) define( '_CHOOSE_PRIORITY', 'Choisissez une priorit&eacute;');
if (!defined('_ADD_PARAMETERS')) define( '_ADD_PARAMETERS', 'Ajouter des crit&egrave;res');
if (!defined('_CHOOSE_PARAMETERS')) define( '_CHOOSE_PARAMETERS', 'Choisissez vos crit&egrave;res');
if (!defined('_CHOOSE_ENTITES_SEARCH_TITLE')) define( '_CHOOSE_ENTITES_SEARCH_TITLE', 'Ajoutez le/les service(s) d&eacute;sir&eacute;(s) pour restreindre la recherche');
if (!defined('_CHOOSE_DOCTYPES_SEARCH_TITLE')) define( '_CHOOSE_DOCTYPES_SEARCH_TITLE', 'Ajoutez le(s) type(s) de document d&eacute;sir&eacute;(s) pour restreindre la recherche');
if (!defined('_DESTINATION_SEARCH')) define( '_DESTINATION_SEARCH', 'Service(s) affect&eacute;(s)');
if (!defined('_ADD_PARAMETERS_HELP')) define( '_ADD_PARAMETERS_HELP', 'Pour affiner le r&eacute;sultat, vous pouvez ajouter des crit&egrave;res &agrave; votre recherche... ');
if (!defined('_MAIL_OBJECT_HELP')) define( '_MAIL_OBJECT_HELP', 'Saisissez les mots cl&eacute;s de l&rsquo;objet du courrier... ');
if (!defined('_N_GED_HELP')) define( '_N_GED_HELP', '');
if (!defined('_CHOOSE_RECIPIENT_SEARCH_TITLE')) define( '_CHOOSE_RECIPIENT_SEARCH_TITLE', 'Ajoutez le/les destinataire(s) d&eacute;sir&eacute;(s) pour restreindre la recherche');
if (!defined('_MULTI_FIELD')) define( '_MULTI_FIELD','Multi-champs');
if (!defined('_MULTI_FIELD_HELP')) define( '_MULTI_FIELD_HELP','Objet, description, titre, Num chrono, notes de traitement...');
if (!defined('_SAVE_QUERY')) define( '_SAVE_QUERY', 'Enregistrer ma recherche');
if (!defined('_SAVE_QUERY_TITLE')) define( '_SAVE_QUERY_TITLE', 'Enregistrement de recherche');
if (!defined('_QUERY_NAME')) define( '_QUERY_NAME', 'Nom de ma recherche');
if (!defined('_QUERY_SAVED')) define( '_QUERY_SAVED', 'Recherche sauvegard&eacute;e');

//if (!defined('_SQL_ERROR')) define( '_SQL_ERROR', 'Erreur SQL lors de l&acute;enregistrement de la recherche');
if (!defined('_LOAD_QUERY')) define( '_LOAD_QUERY', 'Charger la recherche');
if (!defined('_DELETE_QUERY')) define( '_DELETE_QUERY', 'Supprimer la recherche');
if (!defined('_CHOOSE_SEARCH')) define( '_CHOOSE_SEARCH', 'Choisir une recherche');
if (!defined('_THIS_SEARCH')) define( '_THIS_SEARCH', 'cette recherche');
if (!defined('_MY_SEARCHES')) define( '_MY_SEARCHES', 'Mes recherches');
if (!defined('_CLEAR_SEARCH')) define( '_CLEAR_SEARCH', 'Effacer les crit&egrave;res');
if (!defined('_CHOOSE_STATUS_SEARCH_TITLE')) define( '_CHOOSE_STATUS_SEARCH_TITLE', 'Ajoutez le/les statut(s) d&eacute;sir&eacute;(s) pour restreindre la recherche');
if (!defined('_ERROR_IE_SEARCH')) define( '_ERROR_IE_SEARCH', 'Cet &eacute;l&eacute;ment est d&eacute;j&agrave; d&eacute;fini !');
//if (!defined('_CIVILITIES')) define( '_CIVILITIES', 'Civilit&eacute;(s)');
//if (!defined('_CIVILITY')) define( '_CIVILITY', 'Civilit&eacute;');
//if (!defined('_CHOOSE_CIVILITY_SEARCH_TITLE')) define( '_CHOOSE_CIVILITY_SEARCH_TITLE', 'Ajoutez le/les civilit&eacute;(s) d&eacute;sir&eacute;(s) pour restreindre la recherche');

if (!defined('_DEST_USER')) define( '_DEST_USER','Destinataire');
if (!defined('_DOCTYPES')) define( '_DOCTYPES','Type(s) de document');
if (!defined('_MAIL_NATURE')) define( '_MAIL_NATURE', 'Nature de l&rsquo;envoi');
if (!defined('_CHOOSE_MAIL_NATURE')) define( '_CHOOSE_MAIL_NATURE', 'Choisissez la nature de l&rsquo;envoi');
if (!defined('_ERROR_DOCTYPE')) define( '_ERROR_DOCTYPE', 'Type de document non valide');
if (!defined('_ADMISSION_DATE')) define( '_ADMISSION_DATE', 'Date d&rsquo;arriv&eacute;e');
if (!defined('_FOUND_DOC')) define( '_FOUND_DOC', 'document(s) trouv&eacute;(s)');
if (!defined('_PROCESS')) define( '_PROCESS', 'Traitement ');
if (!defined('_DOC_NUM')) define( '_DOC_NUM', 'document n&deg; ');
if (!defined('_GENERAL_INFO')) define( '_GENERAL_INFO', 'Informations g&eacute;n&eacute;rales');
if (!defined('_ON_DOC_NUM')) define( '_ON_DOC_NUM', ' sur le document n&deg;');
if (!defined('_PRIORITY')) define( '_PRIORITY', 'Priorit&eacute;');
if (!defined('_MAIL_DATE')) define( '_MAIL_DATE', 'Date du courrier');
if (!defined('_DOC_HISTORY')) define( '_DOC_HISTORY', 'Historique');
if (!defined('_DONE_ANSWERS')) define( '_DONE_ANSWERS','R&eacute;ponses effectu&eacute;es');
if (!defined('_MUST_DEFINE_ANSWER_TYPE')) define( '_MUST_DEFINE_ANSWER_TYPE', 'Vous devez pr&eacute;ciser le type de r&eacute;ponse');
if (!defined('_MUST_CHECK_ONE_BOX')) define( '_MUST_CHECK_ONE_BOX', 'Vous devez cocher au moins une case');
if (!defined('_ANSWER_TYPE')) define( '_ANSWER_TYPE','Type(s) de r&eacute;ponse');

if (!defined('_INDEXATION_TITLE')) define( '_INDEXATION_TITLE', 'Indexation d&rsquo;un document');
if (!defined('_CHOOSE_FILE')) define( '_CHOOSE_FILE', 'Choisissez le fichier');
if (!defined('_CHOOSE_TYPE')) define( '_CHOOSE_TYPE', 'Choisissez un type');

if (!defined('_FILE_LOADED_BUT_NOT_VISIBLE')) define( '_FILE_LOADED_BUT_NOT_VISIBLE', 'Le fichier est charg&eacute; et pr&ecirc;t &agrave; &ecirc;tre enregistr&eacute; sur le serveur.<br/>');
if (!defined('_ONLY_FILETYPES_AUTHORISED')) define( '_ONLY_FILETYPES_AUTHORISED', 'Seuls les fichiers suivants peuvent &ecirc;tre affich&eacute;s dans cette fen&ecirc;tre');
if (!defined('_PROBLEM_LOADING_FILE_TMP_DIR')) define( '_PROBLEM_LOADING_FILE_TMP_DIR', 'Probl&egrave;me lors du chargement du fichier sur le r&eacute;pertoire temporaire du serveur');
if (!defined('_DOWNLOADED_FILE')) define( '_DOWNLOADED_FILE', 'Fichier charg&eacute;');
if (!defined('_WRONG_FILE_TYPE')) define( '_WRONG_FILE_TYPE', 'Ce type de fichier n&rsquo;est pas permis');

if (!defined('_LETTERBOX')) define( '_LETTERBOX', 'Collection Courrier');
if (!defined('_APA_COLL')) define( '_APA_COLL', 'APA - ne pas utiliser');
if (!defined('_REDIRECT_TO_ACTION')) define( '_REDIRECT_TO_ACTION', 'Rediriger vers une action');
if (!defined('_DOCUMENTS_LIST')) define( '_DOCUMENTS_LIST', 'Liste');


/********* Contacts ************/
if (!defined('_ADMIN_CONTACTS')) define( '_ADMIN_CONTACTS', 'Contacts');
if (!defined('_ADMIN_CONTACTS_DESC')) define( '_ADMIN_CONTACTS_DESC', 'Administration des contacts');
if (!defined('_CONTACTS_LIST')) define( '_CONTACTS_LIST', 'Liste des contacts');
if (!defined('_CONTACT_ADDITION')) define( '_CONTACT_ADDITION', 'Ajouter contact');
if (!defined('_CONTACTS')) define( '_CONTACTS', 'contact(s)');
if (!defined('_CONTACT')) define( '_CONTACT', 'Contact');
if (!defined('_ALL_CONTACTS')) define( '_ALL_CONTACTS', 'Tous les contacts');
if (!defined('_ADD_CONTACT')) define( '_ADD_CONTACT', 'Ajout d&rsquo;un contact');
if (!defined('_PHONE')) define( '_PHONE', 'T&eacute;l&eacute;phone');
if (!defined('_ADDRESS')) define( '_ADDRESS', 'Adresse');
if (!defined('_STREET')) define( '_STREET', 'Rue');
if (!defined('_COMPLEMENT')) define( '_COMPLEMENT', 'Compl&eacute;ment');
if (!defined('_TOWN')) define( '_TOWN', 'Ville');
if (!defined('_COUNTRY')) define( '_COUNTRY', 'Pays');
if (!defined('_SOCIETY')) define( '_SOCIETY', 'Soci&eacute;t&eacute;');
if (!defined('_COMP')) define( '_COMP', 'Autres');
if (!defined('_COMP_DATA')) define( '_COMP_DATA', 'Informations compl&eacute;mentaires');
if (!defined('_CONTACT_ADDED')) define( '_CONTACT_ADDED', 'Contact ajout&eacute;');
if (!defined('_CONTACT_MODIFIED')) define( '_CONTACT_MODIFIED', 'Contact modifi&eacute;');
if (!defined('_CONTACT_DELETED')) define( '_CONTACT_DELETED', 'Contact supprim&eacute;');
if (!defined('_MODIFY_CONTACT')) define( '_MODIFY_CONTACT', 'Modifier un contact');
if (!defined('_IS_CORPORATE_PERSON')) define( '_IS_CORPORATE_PERSON', 'Personne morale');
if (!defined('_TITLE2')) define( '_TITLE2', 'Civilit&eacute;');

if (!defined('_YOU_MUST_SELECT_CONTACT')) define( '_YOU_MUST_SELECT_CONTACT', 'Vous devez s&eacute;lectionner un contact ');
if (!defined('_CONTACT_INFO')) define( '_CONTACT_INFO', 'Fiche contact');

if (!defined('_SHIPPER')) define( '_SHIPPER', 'Exp&eacute;diteur');
if (!defined('_DEST')) define( '_DEST', 'Destinataire');
if (!defined('_INTERNAL2')) define( '_INTERNAL2', 'Interne');
if (!defined('_EXTERNAL')) define( '_EXTERNAL', 'Externe');
if (!defined('_CHOOSE_SHIPPER')) define( '_CHOOSE_SHIPPER', 'Choisir un exp&eacute;diteur');
if (!defined('_CHOOSE_DEST')) define( '_CHOOSE_DEST', 'Choisir un destainataire');
if (!defined('_DOC_DATE')) define( '_DOC_DATE', 'Date du document');
if (!defined('_CONTACT_CARD')) define( '_CONTACT_CARD', 'Fiche contact');
if (!defined('_CREATE_CONTACT')) define( '_CREATE_CONTACT', 'Ajouter un contact');
if (!defined('_USE_AUTOCOMPLETION')) define( '_USE_AUTOCOMPLETION', 'Utiliser l&rsquo;autocompl&eacute;tion');

if (!defined('_USER_DATA')) define( '_USER_DATA', 'Fiche utilisateur');
if (!defined('_SHIPPER_TYPE')) define( '_SHIPPER_TYPE', 'Type d&rsquo;exp&eacute;diteur');
if (!defined('_DEST_TYPE')) define( '_DEST_TYPE', 'Type de destinataire');
if (!defined('_VALIDATE_MAIL')) define( '_VALIDATE_MAIL', 'Validation courrier');
if (!defined('_LETTER_INFO')) define( '_LETTER_INFO','Informations sur le courrier');
if (!defined('_DATE_START')) define( '_DATE_START','Date d&rsquo;arriv&eacute;e');
if (!defined('_LIMIT_DATE_PROCESS')) define( '_LIMIT_DATE_PROCESS','Date limite de traitement');


//// INDEXING SEARCHING
if (!defined('_NO_COLLECTION_ACCESS_FOR_THIS_USER')) define( '_NO_COLLECTION_ACCESS_FOR_THIS_USER', 'Aucun acc&egrave;s aux collections documentaires pour cet utilisateur');
if (!defined('_CREATION_DATE')) define( '_CREATION_DATE', 'Date de cr&eacute;ation');
if (!defined('_NO_RESULTS')) define( '_NO_RESULTS', 'Aucun r&eacute;sultat');
if (!defined('_FOUND_DOCS')) define( '_FOUND_DOCS', 'document(s) trouv&eacute;(s)');
if (!defined('_MY_CONTACTS')) define( '_MY_CONTACTS', 'Mes contacts');
if (!defined('_DETAILLED_PROPERTIES')) define( '_DETAILLED_PROPERTIES', 'Propri&eacute;t&eacute;s d&eacute;taill&eacute;es');
if (!defined('_VIEW_DOC_NUM')) define( '_VIEW_DOC_NUM', 'Visualisation du document n&deg;');
if (!defined('_VIEW_DETAILS_NUM')) define( '_VIEW_DETAILS_NUM', 'Visualisation de la fiche d&eacute;taill&eacute;e du document n&deg;');
if (!defined('_TO')) define( '_TO', 'vers');
if (!defined('_FILE_PROPERTIES')) define( '_FILE_PROPERTIES', 'Propri&eacute;t&eacute;s du fichier');
if (!defined('_FILE_DATA')) define( '_FILE_DATA', 'Informations sur le document');
if (!defined('_VIEW_DOC')) define( '_VIEW_DOC', 'Voir le document');
if (!defined('_TYPIST')) define( '_TYPIST', 'Op&eacute;rateur');
if (!defined('_LOT')) define( '_LOT', 'Lot');
if (!defined('_ARBOX')) define( '_ARBOX', 'Boite');
if (!defined('_ARBOXES')) define( '_ARBOXES', 'Boites');
if (!defined('_ARBATCHES')) define( '_ARBATCHES', 'Lot');
if (!defined('_CHOOSE_BOXES_SEARCH_TITLE')) define( '_CHOOSE_BOXES_SEARCH_TITLE', 'S&eacute;lectionnez la ou les boites pour restreindre la recherche');
if (!defined('_PAGECOUNT')) define( '_PAGECOUNT', 'Nb pages');
if (!defined('_ISPAPER')) define( '_ISPAPER', 'Papier');
if (!defined('_SCANDATE')) define( '_SCANDATE', 'Date de num&eacute;risation');
if (!defined('_SCANUSER')) define( '_SCANUSER', 'Utilisateur du scanner');
if (!defined('_SCANLOCATION')) define( '_SCANLOCATION', 'Lieu de num&eacute;risation');
if (!defined('_SCANWKSATION')) define( '_SCANWKSATION', 'Station de num&eacute;risation');
if (!defined('_SCANBATCH')) define( '_SCANBATCH', 'Lot de num&eacute;risation');
if (!defined('_SOURCE')) define( '_SOURCE', 'Source');
if (!defined('_DOCLANGUAGE')) define( '_DOCLANGUAGE', 'Langage du document');
if (!defined('_MAILDATE')) define( '_MAILDATE', 'Date du courrier');
if (!defined('_MD5')) define( '_MD5', 'Empreinte Num&eacute;rique');
if (!defined('_WORK_BATCH')) define( '_WORK_BATCH', 'Lot de chargement');
if (!defined('_DONE')) define( '_DONE','Actions effectu&eacute;es');
if (!defined('_ANSWER_TYPES_DONE')) define( '_ANSWER_TYPES_DONE', 'Type(s) de r&eacute;ponses effectu&eacute;es');
if (!defined('_CLOSING_DATE')) define( '_CLOSING_DATE', 'Date de cl&ocirc;ture');
if (!defined('_FULLTEXT')) define( '_FULLTEXT', 'Plein texte');
if (!defined('_FULLTEXT_HELP')) define( '_FULLTEXT_HELP', 'Recherche plein texte avec le moteur Luc&egrave;ne...');
if (!defined('_FILE_NOT_SEND')) define( '_FILE_NOT_SEND', 'Le fichier n&rsquo;a pas &eacute;t&eacute; envoy&eacute;');
if (!defined('_TRY_AGAIN')) define( '_TRY_AGAIN', 'Veuillez r&eacute;essayer');
if (!defined('_INDEX_UPDATED')) define( '_INDEX_UPDATED', 'Index mis &agrave; jour');
if (!defined('_DOC_DELETED')) define( '_DOC_DELETED', 'Document supprimé');
if (!defined('_UPDATE_DOC_STATUS')) define( '_UPDATE_DOC_STATUS', 'Statut du document mis à jour');

if (!defined('_DOCTYPE_MANDATORY')) define( '_DOCTYPE_MANDATORY', 'Le type de pi&egrave;ce est obligatoire');
if (!defined('_INDEX_UPDATED')) define( '_INDEX_UPDATED', 'Index mis &agrave; jour');
if (!defined('_DOC_DELETED')) define( '_DOC_DELETED', 'Document supprimé');
if (!defined('_UPDATE_DOC_STATUS')) define( '_UPDATE_DOC_STATUS', 'Statut du document mis à jour');
if (! defined('_CHECK_FORM_OK')) {
    define('_CHECK_FORM_OK', 'V&eacute;rification formulaire OK');
}
if (! defined('_MISSING_FORMAT')) {
    define('_MISSING_FORMAT', 'Il manque le format');
}
if (! defined('_ERROR_RES_ID')) {
    define('_ERROR_RES_ID', 'Probl&egrave;me lors du calcul du res_id');
}
if (! defined('_NEW_DOC_ADDED')) {
    define('_NEW_DOC_ADDED', 'Nouveau document enregistr&eacute;');
}
if (! defined('_STATUS_UPDATED')) {
    define('_STATUS_UPDATED', 'Statut mis &agrave; jour');
}

if (!defined('_QUICKLAUNCH')) define( '_QUICKLAUNCH', 'Raccourcis');
if (!defined('_SHOW_DETAILS_DOC')) define( '_SHOW_DETAILS_DOC', 'Voir les d&eacute;tails du document');
if (!defined('_VIEW_DOC_FULL')) define( '_VIEW_DOC_FULL', 'Voir le document');
if (!defined('_DETAILS_DOC_FULL')) define( '_DETAILS_DOC_FULL', 'Voir la fiche du document');
if (!defined('_IDENTIFIER')) define( '_IDENTIFIER', 'R&eacute;f&eacute;rence');
if (!defined('_CHRONO_NUMBER')) define( '_CHRONO_NUMBER', 'Num&eacute;ro chrono');
if (!defined('_NO_CHRONO_NUMBER_DEFINED')) define( '_NO_CHRONO_NUMBER_DEFINED', 'Le num&eacute;ro chrono n&rsquo;est pas d&eacute;fini');
if (!defined('_FOR_CONTACT_C')) define( '_FOR_CONTACT_C', 'Pour');
if (!defined('_TO_CONTACT_C')) define( '_TO_CONTACT_C', 'De');

if (!defined('_APPS_COMMENT')) define( '_APPS_COMMENT', 'Application Maarch Entreprise');
if (!defined('_CORE_COMMENT')) define( '_CORE_COMMENT', 'Coeur du Framework');
if (!defined('_CLEAR_FORM')) define( '_CLEAR_FORM', 'Effacer le formulaire');

if (!defined('_MAX_SIZE_UPLOAD_REACHED')) define( '_MAX_SIZE_UPLOAD_REACHED', 'Taille maximum de fichier d&eacute;pass&eacute;e');
if (!defined('_NOT_ALLOWED')) define( '_NOT_ALLOWED', 'interdit');
if (!defined('_CHOOSE_TITLE')) define( '_CHOOSE_TITLE', 'Choisissez une civilit&eacute;');
if (!defined('_INDEXING_STATUSES')) define( '_INDEXING_STATUSES', 'Indexer vers les status');
if (!defined('_LOAD_STATUSES_SESSION')) define( '_LOAD_STATUSES_SESSION', 'Chargement des status en session');
if (!defined('_PARAM_AVAILABLE_STATUS_ON_GROUP_BASKETS')) define( '_PARAM_AVAILABLE_STATUS_ON_GROUP_BASKETS', 'Param&eacute;trage des status d\'indexation');
/////////////////// Reports
if (!defined('_USERS_LOGS')) define( '_USERS_LOGS', 'Liste des acc&egrave;s &agrave; l&rsquo;application par agent');
if (!defined('_USERS_LOGS_DESC')) define( '_USERS_LOGS_DESC', 'Liste des acc&egrave;s &agrave; l&rsquo;application par agent');
if (!defined('_PROCESS_DELAY_REPORT')) define( '_PROCESS_DELAY_REPORT', 'D&eacute;lai moyen de traitement par type de courrier');
if (!defined('_PROCESS_DELAY_REPORT_DESC')) define( '_PROCESS_DELAY_REPORT_DESC', 'D&eacute;lai moyen de traitement par type de courrier');
if (!defined('_MAIL_TYPOLOGY_REPORT')) define( '_MAIL_TYPOLOGY_REPORT', 'Typologie des courriers par p&eacute;riode');
if (!defined('_MAIL_TYPOLOGY_REPORT_DESC')) define( '_MAIL_TYPOLOGY_REPORT_DESC', 'Typologie des courriers par p&eacute;riode');
if (!defined('_MAIL_VOL_BY_CAT_REPORT')) define( '_MAIL_VOL_BY_CAT_REPORT', 'Volume de courriers par cat&eacute;gorie par p&eacute;riode');
if (!defined('_MAIL_VOL_BY_CAT_REPORT_DESC')) define( '_MAIL_VOL_BY_CAT_REPORT_DESC', 'Volume de courriers par cat&eacute;gorie par p&eacute;riode');
if (!defined('_SHOW_FORM_RESULT')) define( '_SHOW_FORM_RESULT', 'Afficher le r&eacute;sultat sous forme de ');
if (!defined('_GRAPH')) define( '_GRAPH', 'Graphique');
if (!defined('_ARRAY')) define( '_ARRAY', 'Tableau');
if (!defined('_SHOW_YEAR_GRAPH')) define( '_SHOW_YEAR_GRAPH', 'Afficher le r&eacute;sultat pour l&rsquo;ann&eacute;e');
if (!defined('_SHOW_GRAPH_MONTH')) define( '_SHOW_GRAPH_MONTH', 'Afficher le r&eacute;sultat pour le mois de');
if (!defined('_OF_THIS_YEAR')) define( '_OF_THIS_YEAR', 'de cette ann&eacute;e');
if (!defined('_NB_MAILS1')) define( '_NB_MAILS1', 'Nombre de courriers enregistr&eacute;s');
if (!defined('_FOR_YEAR')) define( '_FOR_YEAR', 'pour l&rsquo;ann&eacute;e');
if (!defined('_FOR_MONTH')) define( '_FOR_MONTH', 'pour le mois de');
if (!defined('_N_DAYS')) define( '_N_DAYS','NB JOURS');

/******************** Specific ************/
if (!defined('_PROJECT')) define( '_PROJECT', 'Dossier');
if (!defined('_MARKET')) define( '_MARKET', 'Sous-dossier');
if (!defined('_SEARCH_CUSTOMER')) define( '_SEARCH_CUSTOMER', 'Consultation Dossiers Sous-dossiers');
if (!defined('_SEARCH_CUSTOMER_TITLE')) define( '_SEARCH_CUSTOMER_TITLE', 'Recherche Dossiers Sous-dossiers');
if (!defined('_TO_SEARCH_DEFINE_A_SEARCH_ADV')) define( '_TO_SEARCH_DEFINE_A_SEARCH_ADV', 'Pour lancer une recherche vous devez saisir un n&deg; de dossier ou un nom de Dossier ou de Sous-dossier');
if (!defined('_DAYS')) define( '_DAYS', 'jours');
if (!defined('_LAST_DAY')) define( '_LAST_DAY', 'Dernier jour');
if (!defined('_CONTACT_NAME')) define( '_CONTACT_NAME', 'Contact facture');
if (!defined('_AMOUNT')) define( '_AMOUNT', 'Montant facture');
if (!defined('_CUSTOMER')) define( '_CUSTOMER', 'Client facture');
if (!defined('_PO_NUMBER')) define( '_PO_NUMBER', 'BDC facture');
if (!defined('_INVOICE_NUMBER')) define( '_INVOICE_NUMBER', 'Num facture');


/******************** fulltext search Helper ************/
if (!defined('_HELP_GLOBAL_SEARCH')) define( '_HELP_GLOBAL_SEARCH', 'Recherche sur l\'objet, le titre, la description ou le contenu du document ');
if (!defined('_HELP_FULLTEXT_SEARCH')) define( '_HELP_FULLTEXT_SEARCH', 'Aide sur la recherche plein texte');
if (!defined('_TIPS_FULLTEXT')) define( '_TIPS_FULLTEXT', 'Astuces de recherche');

if (!defined('_TIPS_KEYWORD1')) define( '_TIPS_KEYWORD1', 'Pour effectuer une recherche avec joker sur plusieurs caractères');
if (!defined('_TIPS_KEYWORD2')) define( '_TIPS_KEYWORD2', 'Pour effectuer une recherche sur un groupe de mots, une phrase');

if (!defined('_TIPS_KEYWORD3')) define( '_TIPS_KEYWORD3', 'Pour effectuer une recherche approximative');
if (!defined('_HELP_FULLTEXT_SEARCH_EXEMPLE1')) define( '_HELP_FULLTEXT_SEARCH_EXEMPLE1', 'auto* trouve autoroute et automobile ');
if (!defined('_HELP_FULLTEXT_SEARCH_EXEMPLE2')) define( '_HELP_FULLTEXT_SEARCH_EXEMPLE2', '"route nationale" trouve l\'expression entière "route nationale"
                                                        <p> Sans guillemet la recherche trouve des documents contenant les mots route ou nationale</p>
                                                        <p> route + nationale trouve les documents contenant à la fois les mots route et nationale</p>');
if (!defined('_HELP_FULLTEXT_SEARCH_EXEMPLE3')) define( '_HELP_FULLTEXT_SEARCH_EXEMPLE3', 'vite~ trouve vote, vite');
if (!defined('_TIPS_FULLTEXT_TEXT')) define( '_TIPS_FULLTEXT_TEXT', 'La recherche peut se faire sur des nombres');
if (!defined('_CLOSE_MAIL')) define( '_CLOSE_MAIL', 'Cl&ocirc;turer un courrier');

/******************** Keywords Helper ************/
if (!defined('_HELP_KEYWORD0')) define( '_HELP_KEYWORD0', 'id de l&rsquo;utilisateur connect&eacute;');
if (!defined('_HELP_BY_CORE')) define( '_HELP_BY_CORE', 'Mots cl&eacute;s de Maarch Core');

if (!defined('_FIRSTNAME_UPPERCASE')) define( '_FIRSTNAME_UPPERCASE', 'PRENOM');
if (!defined('_TITLE_STATS_USER_LOG')) define( '_TITLE_STATS_USER_LOG', 'Acc&egrave;s &agrave; l&rsquo;application');

if (!defined('_DELETE_DOC')) define( '_DELETE_DOC', 'Supprimer ce document');
if (!defined('_THIS_DOC')) define( '_THIS_DOC', 'ce document');
if (!defined('_MODIFY_DOC')) define( '_MODIFY_DOC', 'Modifier des informations');
if (!defined('_BACK_TO_WELCOME')) define( '_BACK_TO_WELCOME', 'Retourner &agrave; la page d&rsquo;accueil');
if (!defined('_CLOSE_MAIL')) define( '_CLOSE_MAIL', 'Cl&ocirc;turer un courrier');


/************** R&eacute;ouverture courrier **************/
if (!defined('_MAIL_SENTENCE2')) define( '_MAIL_SENTENCE2', 'En saisissant le n&deg;GED du document, vous passerez le  statut de ce dernier &agrave; &quot;En cours&quot;.');
if (!defined('_MAIL_SENTENCE3')) define( '_MAIL_SENTENCE3', 'Cette fonction a pour but d&rsquo;ouvrir un courrier ferm&eacute; pr&eacute;matur&eacute;ment.');
if (!defined('_ENTER_DOC_ID')) define( '_ENTER_DOC_ID', 'Saisissez l&rsquo;identifiant du document');
if (!defined('_TO_KNOW_ID')) define( '_TO_KNOW_ID', 'Pour conna&icirc;tre l&rsquo;identifiant du document, effectuez une recherche ou demandez-le &agrave; l&rsquo;op&eacute;rateur');

if (!defined('_REOPEN_MAIL')) define( '_REOPEN_MAIL', 'R&eacute;ouverture de courrier');
if (!defined('_REOPEN_THIS_MAIL')) define( '_REOPEN_THIS_MAIL', 'R&eacute;ouverture du courrier');

if (!defined('_OWNER')) define( '_OWNER', 'Propri&eacute;taire');
if (!defined('_CONTACT_OWNER_COMMENT')) define( '_CONTACT_OWNER_COMMENT', 'Laisser vide pour rendre ce contact public.');

if (!defined('_OPT_INDEXES')) define( '_OPT_INDEXES', 'Informations compl&eacute;mentaires');
if (!defined('_NUM_BETWEEN')) define( '_NUM_BETWEEN', 'Compris entre');
if (!defined('_MUST_CORRECT_ERRORS')) define( '_MUST_CORRECT_ERRORS', 'Vous devez corriger les erreurs suivantes ');
if (!defined('_CLICK_HERE_TO_CORRECT')) define( '_CLICK_HERE_TO_CORRECT', 'Cliquez ici pour les corriger');

if (!defined('_FILETYPE')) define( '_FILETYPE', 'Type de fichier');
if (!defined('_WARNING')) define( '_WARNING', 'Attention ');
if (!defined('_STRING')) define( '_STRING', 'Chaine de caract&egrave;res');
if (!defined('_INTEGER')) define( '_INTEGER', 'Entier');
if (!defined('_FLOAT')) define( '_FLOAT', 'Flottant');
if (!defined('_CUSTOM_T1')) define( '_CUSTOM_T1', 'Champ Texte 1');
if (!defined('_CUSTOM_T2')) define( '_CUSTOM_T2', 'Champ Texte 2');
if (!defined('_CUSTOM_D1')) define( '_CUSTOM_D1', 'Champ Date');
if (!defined('_CUSTOM_N1')) define( '_CUSTOM_N1', 'Champ Entier');
if (!defined('_CUSTOM_F1')) define( '_CUSTOM_F1', 'Champ Flottant');

if (!defined('_ITEM_NOT_IN_LIST')) define( '_ITEM_NOT_IN_LIST', 'El&egrave;ment absent de la liste des valeurs autoris&eacute;es');
if (!defined('_PB_WITH_FINGERPRINT_OF_DOCUMENT')) define( '_PB_WITH_FINGERPRINT_OF_DOCUMENT', 'L&rsquo;empreinte num&eacute;rique initiale du document ne correspond pas &agrave; celle du document r&eacute;f&eacute;renc&eacute;');
if (!defined('_MISSING')) define( '_MISSING', 'manquant(e)');
if (!defined('_NATURE')) define( '_NATURE', 'Nature');
if (!defined('_NO_DEFINED_TREES')) define( '_NO_DEFINED_TREES', 'Aucun arbre d&eacute;fini');

if (!defined('_IF_CHECKS_MANDATORY_MUST_CHECK_USE')) define( '_IF_CHECKS_MANDATORY_MUST_CHECK_USE', 'Si vous cliquez sur un champ obligatoire, vous devez également cocher la case utilis&eacute;');

if (!defined('_SEARCH_DOC')) define( '_SEARCH_DOC', 'Rechercher un document');
if (!defined('_DOCSERVER_COPY_ERROR')) define( '_DOCSERVER_COPY_ERROR', ' Erreur lors de la copie sur le DocServer');
if (!defined('_MAKE_NEW_SEARCH')) define( '_MAKE_NEW_SEARCH', 'Effectuer une nouvelle recherche');
if (!defined('_NO_PAGE')) define( '_NO_PAGE', 'Aucune page');
if (!defined('_VALIDATE_QUALIF')) define( '_VALIDATE_QUALIF', 'Validation/Qualification');


if (!defined('_DB_CONNEXION_ERROR')) define( '_DB_CONNEXION_ERROR', 'Erreur de connexion &agrave; la base de donn&eacute;es');
if (!defined('_DATABASE_SERVER')) define( '_DATABASE_SERVER', 'Serveur de base de donn&eacute;es');
if (!defined('_DB_PORT')) define( '_DB_PORT', 'Port');
if (!defined('_DB_TYPE')) define( '_DB_TYPE', 'Type');
if (!defined('_DB_USER')) define( '_DB_USER', 'Utilisateur');
if (!defined('_DATABASE')) define( '_DATABASE', 'Base');


if (!defined('_TREE_ROOT')) define( '_TREE_ROOT', 'Racine');

if (!defined('_MODE')) define( '_MODE', 'Mode');

if (!defined('_TITLE_STATS_CHOICE_PERIOD'))  define('_TITLE_STATS_CHOICE_PERIOD','Pour une p&eacute;riode');


/******************** Authentification method  ************/


if (!defined('_STANDARD_LOGIN')) define( '_STANDARD_LOGIN', 'Authentification Maarch');
if (!defined('_ACTIVEX_LOGIN')) define( '_ACTIVEX_LOGIN', 'Authentification Ms Internet Explorer - ActiveX');
if (!defined('_HOW_CAN_I_LOGIN')) define( '_HOW_CAN_I_LOGIN', 'Je n&rsquo;arrive pas &agrave; me connecter...');
if (!defined('_CONNECT')) define( '_CONNECT', 'Se connecter');
if (!defined('_LOGIN_MODE')) define( '_LOGIN_MODE', 'Type d&rsquo;authentification');
if (!defined('_SSO_LOGIN')) define( '_SSO_LOGIN', 'Login via SSO');


/******** Admin groups **********/

if (!defined('_WHERE_CLAUSE_TARGET')) define( '_WHERE_CLAUSE_TARGET', 'Cible de la clause WHERE');
if (!defined('_WHERE_TARGET')) define( '_WHERE_TARGET', 'Cible');
if (!defined('_CLASS_SCHEME')) define( '_CLASS_SCHEME', 'Plan de classement');
if (!defined('_DOCS')) define( '_DOCS', 'Documents');
if (!defined('_GO_MANAGE_USER')) define( '_GO_MANAGE_USER', 'Modifier');
if (!defined('_GO_MANAGE_DOCSERVER')) define( '_GO_MANAGE_DOCSERVER', 'Modifier');
if (!defined('_TASKS')) define( '_TASKS', 'Actions disponibles');
if (!defined('_PERIOD')) define( '_PERIOD', 'P&eacute;riode');
if (!defined('_COMMENTS_MANDATORY')) define( '_COMMENTS_MANDATORY', 'Description obligatoire');

/******* Security Bitmask label ********/

if (!defined('_ADD_RECORD_LABEL')) define ('_ADD_RECORD_LABEL','Ajouter un document');
if (!defined('_DATA_MODIFICATION_LABEL')) define ('_DATA_MODIFICATION_LABEL','Modifier');
if (!defined('_DELETE_RECORD_LABEL')) define ('_DELETE_RECORD_LABEL','Supprimer un document');
if (!defined('_VIEW_LOG_LABEL')) define ('_VIEW_LOG_LABEL','Voir les journaux');


if (!defined('_PLUS')) define( '_PLUS', 'Plus');
if (!defined('_MINUS')) define( '_MINUS', 'Moins');


/*********ADMIN DOCSERVERS**********************/
if (!defined('_MANAGE_DOCSERVERS'))  define('_MANAGE_DOCSERVERS', 'G&eacute;rer les zones de stockage ');
if (!defined('_MANAGE_DOCSERVERS_DESC'))  define('_MANAGE_DOCSERVERS_DESC', 'Ajouter, modifier, supprimer les zones de stockage ');
if (!defined('_MANAGE_DOCSERVERS_LOCATIONS'))  define('_MANAGE_DOCSERVERS_LOCATIONS', 'G&eacute;rer les lieux de stockage de documents ');
if (!defined('_MANAGE_DOCSERVERS_LOCATIONS_DESC'))  define('_MANAGE_DOCSERVERS_LOCATIONS_DESC', 'Ajouter, supprimer, modifier les lieux de stockage de documents ');
if (!defined('_MANAGE_DOCSERVER_TYPES'))  define('_MANAGE_DOCSERVER_TYPES', 'G&eacute;rer les types de zones de stockage ');
if (!defined('_MANAGE_DOCSERVER_TYPES_DESC'))  define('_MANAGE_DOCSERVER_TYPES_DESC', 'Ajouter, modifier, supprimer les types de zones de stockage ');
if (!defined('_ADMIN_DOCSERVERS'))  define('_ADMIN_DOCSERVERS', ' Administration des zones de stockage');
if (!defined('_ADMIN_DOCSERVERS_DESC'))  define('_ADMIN_DOCSERVERS_DESC', ' Ajouter, modifier, supprimer des zones de stockage');
if (!defined('_DOCSERVER_ID'))  define('_DOCSERVER_ID', 'Identifiant docserver');

/**********DOCSERVERS****************/
if (!defined('_YOU_CANNOT_DELETE')) define( '_YOU_CANNOT_DELETE', 'Suppression imposible');
if (!defined('_UNKNOWN')) define( '_UNKNOWN', 'Inconnu');
if (!defined('_YOU_CANNOT_DISABLE')) define( '_YOU_CANNOT_DISABLE', 'Suspension impossible');
if (!defined('_DOCSERVER_TYPE_DISABLED')) define( '_DOCSERVER_TYPE_DISABLED', 'Type de zone de stockage suspendu');
if (!defined('_SIZE_LIMIT_UNAPPROACHABLE')) define( '_SIZE_LIMIT_UNAPPROACHABLE', 'Taille limite inaccessible');
if (!defined('_DOCSERVER_TYPE_ENABLED')) define( '_DOCSERVER_TYPE_ENABLED', 'Type de zone de stockage actif');
if (!defined('_SIZE_LIMIT_LESS_THAN_ACTUAL_SIZE')) define( '_SIZE_LIMIT_LESS_THAN_ACTUAL_SIZE', 'Taille limite inférieure à la taille actuelle');
if (!defined('_THE_DOCSERVER_DOES_NOT_HAVE_THE_ADEQUATE_RIGHTS')) define( '_THE_DOCSERVER_DOES_NOT_HAVE_THE_ADEQUATE_RIGHTS', 'Cette zone de stockage n&rsquo;a pas les droits suffisants...');
if (!defined('_DOCSERVER_DISABLED')) define( '_DOCSERVER_DISABLED', 'Zone de stockage suspendue');
if (!defined('_DOCSERVER_ENABLED')) define( '_DOCSERVER_ENABLED', 'Zone de stockage active');
if (!defined('_ALREADY_EXISTS_FOR_THIS_TYPE_OF_DOCSERVER')) define( '_ALREADY_EXISTS_FOR_THIS_TYPE_OF_DOCSERVER', 'existe déjà pour ce type');
if (!defined('_DOCSERVER_LOCATION_ENABLED')) define( '_DOCSERVER_LOCATION_ENABLED', 'Lieu de stockage actif');
if (!defined('_LINK_EXISTS')) {
    define('_LINK_EXISTS', 'Un lien avec un autre objet existe');
}


/***************DOCSERVERS TYPES*************************************/
if (!defined('_DOCSERVER_TYPE_ID'))  define('_DOCSERVER_TYPE_ID', 'Identifiant du type de zone de stockage ');
if (!defined('_DOCSERVER_TYPE'))  define('_DOCSERVER_TYPE', 'Type de zone ');
if (!defined('_DOCSERVER_TYPES_LIST'))  define('_DOCSERVER_TYPES_LIST', 'Liste de types de zone de stockage');
if (!defined('_ALL_DOCSERVER_TYPES'))  define('_ALL_DOCSERVER_TYPES', 'Tout afficher ');
if (!defined('_DOCSERVER_TYPE_LABEL'))  define('_DOCSERVER_TYPE_LABEL', 'Label du type de zone de stockage ');
if (!defined('_DOCSERVER_TYPES'))  define('_DOCSERVER_TYPES', 'Type(s) de zone de stockage ');
if (!defined('_IS_CONTAINER'))  define('_IS_CONTAINER', 'Conteneur');
if (!defined('_IS_COMPRESSED'))  define('_IS_COMPRESSED', 'Compressé');
if (!defined('_IS_META'))  define('_IS_META', 'Contient les métadonnées');
if (!defined('_IS_LOGGED'))  define('_IS_LOGGED', 'Contient les journaux');
if (!defined('_IS_SIGNED'))  define('_IS_SIGNED', 'Contient une empreinte');
if (!defined('_COMPRESS_MODE'))  define('_COMPRESS_MODE', 'Mode de compression');
if (!defined('_META_TEMPLATE'))  define('_META_TEMPLATE', 'Modèle de métadonnées');
if (!defined('_LOG_TEMPLATE'))  define('_LOG_TEMPLATE', 'Modèle de journal');
if (!defined('_FINGERPRINT_MODE'))  define('_FINGERPRINT_MODE', 'Algorythme de calcul d&rsquo;empreinte');
if (!defined('_CONTAINER_MAX_NUMBER'))  define('_CONTAINER_MAX_NUMBER', 'Taille maximale du conteneur');
if (!defined('_DOCSERVER_TYPE_MODIFICATION'))  define('_DOCSERVER_TYPE_MODIFICATION', 'Modification de type de zone de stockage ');
if (!defined('_DOCSERVER_TYPE_ADDITION'))  define('_DOCSERVER_TYPE_ADDITION', 'Ajouter un type de zone de stockage');
if (!defined('_DOCSERVER_TYPE_ADDED'))  define('_DOCSERVER_TYPE_ADDED', 'Type de zone de stockage ajouté ');
if (!defined('_DOCSERVER_TYPE_UPDATED'))  define('_DOCSERVER_TYPE_UPDATED', 'Type de zone de stockage mis à jour ');
if (!defined('_DOCSERVER_TYPE_DELETED'))  define('_DOCSERVER_TYPE_DELETED', 'Type de zone de stockage supprimé ');
if (!defined('_NOT_CONTAINER'))  define('_NOT_CONTAINER', 'Pas un container');
if (!defined('_CONTAINER'))  define('_CONTAINER', 'Un container');
if (!defined('_NOT_COMPRESSED'))  define('_NOT_COMPRESSED', 'Non compress&eacute;');
if (!defined('_COMPRESSED'))  define('_COMPRESSED', 'Compress&eacute;');
if (!defined('_COMPRESSION_MODE'))  define('_COMPRESSION_MODE', 'Mode de compression');
if (!defined('_GZIP_COMPRESSION_MODE'))  define('_GZIP_COMPRESSION_MODE', 'Mode de compression GZIP (tar.gz) est uniquement disponible pour la consultation');

/***************DOCSERVERS*********************************/
if (!defined('_DOCSERVERS'))  define('_DOCSERVERS', 'Zone(s) de stockage ');
if (!defined('_DEVICE_LABEL'))  define('_DEVICE_LABEL', 'Label dispositif ');
if (!defined('_SIZE_FORMAT'))  define('_SIZE_FORMAT', 'Format de la taille ');
if (!defined('_SIZE_LIMIT'))  define('_SIZE_LIMIT', 'Taille maximale ');
if (!defined('_ACTUAL_SIZE'))  define('_ACTUAL_SIZE', 'Taille actuelle ');
if (!defined('_COLL_ID'))  define('_COLL_ID', 'Identifiant de la collection');
if (!defined('_PATH_TEMPLATE'))  define('_PATH_TEMPLATE', 'Chemin d&rsquo;accès');
if (!defined('_ADR_PRIORITY'))  define('_ADR_PRIORITY', 'Priorité de sequence de zone de stockage');
if (!defined('_IS_READONLY'))  define('_IS_READONLY', 'Autorisé en lecture seule');
if (!defined('_PERCENTAGE_FULL'))  define('_PERCENTAGE_FULL', 'Pourcentage de remplissage');
if (!defined('_PATH_OF_DOCSERVER_UNAPPROACHABLE'))  define('_PATH_OF_DOCSERVER_UNAPPROACHABLE', 'Chemin inaccessible ');
if (!defined('_ALL_DOCSERVERS'))  define('_ALL_DOCSERVERS', 'Tout afficher ');
if (!defined('_DOCSERVER'))  define('_DOCSERVER', 'un docserver');
if (!defined('_DOCSERVER_MODIFICATION'))  define('_DOCSERVER_MODIFICATION', 'Modification de zone de stockage');
if (!defined('_DOCSERVER_ADDITION'))  define('_DOCSERVER_ADDITION', 'Ajouter une zone de stockage');
if (!defined('_DOCSERVER_UPDATED'))  define('_DOCSERVER_UPDATED', 'Zone de stockage mise à jour');
if (!defined('_DOCSERVER_DELETED'))  define('_DOCSERVER_DELETED', 'Zone de stockage supprimée');
if (!defined('_DOCSERVER_ADDED'))  define('_DOCSERVER_ADDED', 'Zone de stockage ajoutée');
if (!defined('_DOCSERVERS_LIST'))  define('_DOCSERVERS_LIST', 'Liste des zones de stockage ');
if (!defined('_GB'))  define('_GB', 'Gigaoctets ');
if (!defined('_TB'))  define('_TB', 'Teraoctets ');
if (!defined('_MB'))  define('_MB', 'Megaoctets ');
if (!defined('_SIZE_LIMIT_NUMBER')) define( '_SIZE_LIMIT_NUMBER', 'Taille limite');
if (!defined('_DOCSERVER_ATTACHED_TO_RES_X')) define( '_DOCSERVER_ATTACHED_TO_RES_X', 'Des documents sont stock&eacute;s sur cette espace de stockage');

/************DOCSERVER LOCATIONS******************************/
if (!defined('_DOCSERVER_LOCATION_ID'))  define('_DOCSERVER_LOCATION_ID', 'Identifiant de lieu de stockage ');
if (!defined('_DOCSERVER_LOCATIONS'))  define('_DOCSERVER_LOCATIONS', 'Lieu(x) de stockage ');
if (!defined('_IPV4'))  define('_IPV4', 'Adresse IPv4');
if (!defined('_IPV6'))  define('_IPV6', 'Adresse IPv6');
if (!defined('_NET_DOMAIN'))  define('_NET_DOMAIN', 'Domaine');
if (!defined('_MASK'))  define('_MASK', 'Masque');
if (!defined('_NET_LINK'))  define('_NET_LINK', 'URL du frontal');
if (!defined('_DOCSERVER_LOCATION_ADDITION'))  define('_DOCSERVER_LOCATION_ADDITION', 'Ajouter un lieu de stockage ');
if (!defined('_DOCSERVER_LOCATION_MODIFICATION'))  define('_DOCSERVER_LOCATION_MODIFICATION', 'Modification lieu de stockage');
if (!defined('_ALL_DOCSERVER_LOCATIONS'))  define('_ALL_DOCSERVER_LOCATIONS', 'Tout afficher');
if (!defined('_DOCSERVER_LOCATIONS_LIST'))  define('_DOCSERVER_LOCATIONS_LIST', 'Liste des lieux de stockage');
if (!defined('_DOCSERVER_LOCATION'))  define('_DOCSERVER_LOCATION', 'un lieu de stockage');
if (!defined('_DOCSERVER_LOCATION_UPDATED'))  define('_DOCSERVER_LOCATION_UPDATED', 'Lieu de stockage mis à jour');
if (!defined('_DOCSERVER_LOCATION_ADDED'))  define('_DOCSERVER_LOCATION_ADDED', 'Lieu de stockage ajouté');
if (!defined('_DOCSERVER_LOCATION_DELETED'))  define('_DOCSERVER_LOCATION_DELETED', 'Lieu de stockage supprimé');
if (!defined('_DOCSERVER_LOCATION_DISABLED'))  define('_DOCSERVER_LOCATION_DISABLED', 'Lieu de stockage desactiv&eacute;');
if (!defined('_DOCSERVER_LOCATION_ENABLED'))  define('_DOCSERVER_LOCATION_ENABLED', 'Lieu de stockage activ&eacute;');
if (!defined('_IP_V4_ADRESS_NOT_VALID')) define('_IP_V4_ADRESS_NOT_VALID', 'Adresse IPV4 inaccessible');
if (!defined('_IP_V4_FORMAT_NOT_VALID')) define('_IP_V4_FORMAT_NOT_VALID', 'Mauvais format adresse IPV4');
if (!defined('_IP_V6_NOT_VALID')) define('_IP_V6_NOT_VALID', 'Mauvais format adresse IPV6');
if (!defined('_MASK_NOT_VALID')) define('_MASK_NOT_VALID', 'Masque non valide');


/************FAILOVER******************************/
if (!defined('_FAILOVER'))  define('_FAILOVER', 'Reprise sur erreur');
if (!defined('_FILE_NOT_EXISTS_ON_THE_SERVER'))  define('_FILE_NOT_EXISTS_ON_THE_SERVER', 'Le fichier n&rsquo;existe pas sur le docserver');
if (!defined('_NO_RIGHT_ON_RESOURCE_OR_RESOURCE_NOT_EXISTS'))  define('_NO_RIGHT_ON_RESOURCE_OR_RESOURCE_NOT_EXISTS', 'Aucun droit sur la resource demand&eacute;e ou elle est non disponible');

if (!defined('_PROCESS_DELAY'))  define('_PROCESS_DELAY', 'D&eacute;lai de traitement');
if (!defined('_ALERT_DELAY_1'))  define('_ALERT_DELAY_1', 'D&eacute;lai de 1ere alerte');
if (!defined('_ALERT_DELAY_2'))  define('_ALERT_DELAY_2', 'D&eacute;lai de 2eme alerte');

if (!defined('_ERROR_PARAMETERS_FUNCTION'))  define('_ERROR_PARAMETERS_FUNCTION', 'Erreur de paramètres...');
if (!defined('_SYNTAX_OK'))  define('_SYNTAX_OK', 'Syntaxe OK');

/************TECHNICAL INFOS******************************/
if (!defined('_TECHNICAL_INFORMATIONS'))  define('_TECHNICAL_INFORMATIONS', 'Infos techniques');
if (!defined('_VIEW_TECHNICAL_INFORMATIONS'))  define('_VIEW_TECHNICAL_INFORMATIONS', 'Voir les informations techniques');
if (!defined('_SOURCE_FILE_PROPERTIES')) define( '_SOURCE_FILE_PROPERTIES', 'Propri&eacute;t&eacute;s du fichier source');
if (!defined('_FINGERPRINT'))  define('_FINGERPRINT', 'Empreinte num&eacute;rique');
if (!defined('_OFFSET'))  define('_OFFSET', 'Offset');
if (!defined('_SETUP'))  define('_SETUP', 'Configurer');


if (!defined('_PARAM_MLB_DOCTYPES')) {
    define('_PARAM_MLB_DOCTYPES', 'Param&eacute;trage des types de documents ');
}
if (!defined('_PARAM_MLB_DOCTYPES_DESC')) {
    define('_PARAM_MLB_DOCTYPES_DESC', 'Param&eacute;trage des types de documents ');
}
if (!defined('_WELCOME_TEXT_LOAD')) {
    define('_WELCOME_TEXT_LOAD', 'Chargement texte page d&rsquo;accueil');
}
if (!defined('_REOPEN_MAIL_DESC')) {
    define('_REOPEN_MAIL_DESC', 'R&eacute;ouverture de courrier');
}
if (!defined('_WRONG_FUNCTION_OR_WRONG_PARAMETERS')) {
    define('_WRONG_FUNCTION_OR_WRONG_PARAMETERS', 'Mauvais appel ou mauvaus param&eagrave;tre');
}
if (!defined('_INDEXING_INSERT_ERROR')) {
    define('_INDEXING_INSERT_ERROR', 'Indexation : erreur lors de l&rsquo;insertion');
}

if (!defined('_LOGIN_HISTORY')) {
    define('_LOGIN_HISTORY', 'Connexion de l&rsquo;utilisateur');
}

if (!defined('_LOGOUT_HISTORY')) {
    define('_LOGOUT_HISTORY', 'D&eacute;connexion de l&rsquo;utilisateur');
}

if (!defined('_TO_MASTER_DOCUMENT')) {
    define('_TO_MASTER_DOCUMENT', 'au document maitre n&deg;');
}

//print details
if (!defined('_DETAILS_PRINT')) {
    define( '_DETAILS_PRINT', 'Fiche de liaison N&deg;');
}
if (!defined('_PRINT_DETAILS')) {
    define( '_PRINT_DETAILS', 'Imprimer fiche de liaison');
}
if (!defined('_NOTES_1')) {
    define( '_NOTES_1', 'Exemple Notes service 1');
}
if (!defined('_NOTES_2')) {
    define( '_NOTES_2', 'Exemple Notes service 2');
}
if (!defined('_NOTES_3')) {
    define( '_NOTES_3', 'Exemple Notes service 3');
}

if (!defined('_WHERE_CLAUSE_NOT_SECURE')) {
    define(
        '_WHERE_CLAUSE_NOT_SECURE',
        'Clause where non s&eacute;curis&eacute;e'
    );
}

if (!defined('_SQL_QUERY_NOT_SECURE')) {
    define(
        '_SQL_QUERY_NOT_SECURE',
        'requete SQL non s&eacute;curis&eacute;e'
    );
}

//service to put doc on validation from details page
if (!defined('_PUT_DOC_ON_VALIDATION_FROM_DETAILS')) {
    define(
        '_PUT_DOC_ON_VALIDATION_FROM_DETAILS',
        'Envoyer le document en validation depuis la page d&eacute;tails'
    );
}

if (!defined('_PUT_DOC_ON_VALIDATION')) {
    define(
        '_PUT_DOC_ON_VALIDATION',
        'Envoyer le document en validation'
    );
}

if (!defined('_REALLY_PUT_DOC_ON_VALIDATION')) {
    define(
        '_REALLY_PUT_DOC_ON_VALIDATION',
        'Confirmer l&rsquo;envoi en validation'
    );
}

/*******************************************************************************
 * RA_CODE
*******************************************************************************/
if (!defined('_ASK_RA_CODE_1')) {
    define( '_ASK_RA_CODE_1', 'Un courriel va être envoyé à l\'adresse : ');
}

if (!defined('_ASK_RA_CODE_2')) {
    define( '_ASK_RA_CODE_2', 'Une fois le code connu, merci de renouveler votre tentative de connexion.');
}

if (!defined('_CONFIRM_ASK_RA_CODE_1')) {
    define( '_CONFIRM_ASK_RA_CODE_1', 'Bonjour, ');
}

if (!defined('_CONFIRM_ASK_RA_CODE_2')) {
    define( '_CONFIRM_ASK_RA_CODE_2', 'votre code de connexion distant a l\'application Maarch est : ');
}

if (!defined('_CONFIRM_ASK_RA_CODE_3')) {
    define( '_CONFIRM_ASK_RA_CODE_3', 'Ce code reste reste valide jusqu\'au ');
}

if (!defined('_CONFIRM_ASK_RA_CODE_4')) {
    define( '_CONFIRM_ASK_RA_CODE_4', 'Pour vous connecter, ');
}

if (!defined('_CONFIRM_ASK_RA_CODE_5')) {
    define( '_CONFIRM_ASK_RA_CODE_5', 'cliquez ici');
}

if (!defined('_CONFIRM_ASK_RA_CODE_6')) {
    define( '_CONFIRM_ASK_RA_CODE_6', 'Votre code de connexion Maarch');
}

if (!defined('_CONFIRM_ASK_RA_CODE_7')) {
    define( '_CONFIRM_ASK_RA_CODE_7', 'Un courriel a été envoyé à votre adresse mail');
}

if (!defined('_CONFIRM_ASK_RA_CODE_8')) {
    define( '_CONFIRM_ASK_RA_CODE_8', 'Tentative de reconnexion');
}

if (!defined('_TRYING_TO_CONNECT_FROM_NOT_ALLOWED_IP')) {
    define( '_TRYING_TO_CONNECT_FROM_NOT_ALLOWED_IP', 'Vous tentez de vous connecter depuis un emplacement non répertorié.');
}

if (!defined('_PLEASE_ENTER_YOUR_RA_CODE')) {
    define( '_PLEASE_ENTER_YOUR_RA_CODE', 'Veuillez entrer le code d\'acces complémentaire.');
}

if (!defined('_ASK_AN_RA_CODE')) {
    define( '_ASK_AN_RA_CODE', 'Demander un code d\'accès');
}

if (!defined('_RA_CODE_1')) {
    define( '_RA_CODE_1', 'Code complémentaire');
}

if (!defined('_CAN_T_CONNECT_WITH_THIS_IP')) {
    define( '_CAN_T_CONNECT_WITH_THIS_IP', 'Vous ne pouvez pas vous connecter depuis un emplacement non répertorié.');
}


/*******************************************************************************
* admin => svn_monitoring
*******************************************************************************/
if (!defined('_SVN_MONITORING')) {
    define( '_SVN_MONITORING', 'SVN Supervision');
}

if (!defined('_LOADING_INFORMATIONS')) {
    define( '_LOADING_INFORMATIONS', 'Chargement des informations');
}

if (!defined('_RELEASE_NUMBER')) {
    define( '_RELEASE_NUMBER', 'N&deg; de r&eacute;vision');
}

if (!defined('_BY')) {
    define( '_BY', 'par');
}

if (!defined('_UP_TO_DATE')) {
    define( '_UP_TO_DATE', '&agrave; jour');
}

if (!defined('_ACTUAL_INSTALLATION')) {
    define( '_ACTUAL_INSTALLATION', 'version install&eacute;e');
}

if (!defined('_MAKE_UPDATE')) {
    define( '_MAKE_UPDATE', 'mettre &agrave; jour');
}

if (!defined('_TO_GET_LOG_PLEASE_CONNECT')) {
    define( '_TO_GET_LOG_PLEASE_CONNECT', 'Pour obtenir la liste des logs, vous devez &ecirc;tre connect&eacute;');
}

if (!defined('_MANAGE_MEP_RELEASE')) {
    define( '_MANAGE_MEP_RELEASE', 'Gestion des r&eacute;visions de Maarch Entreprise');
}

if (!defined('_INSTALL_SVN_EXTENSION')) {
    define( '_INSTALL_SVN_EXTENSION', 'Vous devez installer la librairie PHP svn pour acc&eacute;der aux logs.');
}

if (!defined('_REVERSE_CHECK')) {
    define( '_REVERSE_CHECK', 'Inverser la s&eacute;lection');
}

if (!defined('_CHECK_ALL')) {
    define( '_CHECK_ALL', 'Tout cocher');
}

if (!defined('_UNCHECK_ALL')) {
    define( '_UNCHECK_ALL', '/ d&eacute;cocher');
}

//EXPORT

if (!defined('_EXPORT_LIST')) {
    define( '_EXPORT_LIST', 'Exporter');
}

/******************** Action put in copy ************/
if (!defined('_PUT_IN_COPY')) {
    define('_PUT_IN_COPY', 'Ajouter en copie');
}

if (!defined('_POWERED_BY')) {
    define('_POWERED_BY', 'Powered by Maarch&trade;.');
}

if (!defined('_LINK_TO_DOC')) {
    define('_LINK_TO_DOC', 'Lier &agrave; un courrier existant');
}

if (!defined('_LINK_REFERENCE')) {
    define('_LINK_REFERENCE', 'Pour lier vous devez choisir un courrier existant');
}

if (!defined('_LINKED_TO')) {
    define('_LINKED_TO', 'Li&eacute; au document ');
}

if (!defined('_NOW_LINK_WITH_THIS_ONE')) {
    define('_NOW_LINK_WITH_THIS_ONE', ' est maintenant li&eacute; &agrave; ce documents');
}

if (!defined('_LINK_TAB')) {
    define('_LINK_TAB', 'Liaisons');
}

if (!defined('_LINK_DESC_FOR')) {
    define('_LINK_DESC_FOR', 'Courrier(s) li&eacute;s &agrave; ce document');
}

if (!defined('_LINK_ASC_FOR')) {
    define('_LINK_ASC_FOR', 'Courriers au(x)quel(s) est li&eacute; ce document');
}

if (!defined('_ADD_A_LINK')) {
    define('_ADD_A_LINK', 'Ajouter une liaison');
}

if (!defined('_LINK_ACTION')) {
    define('_LINK_ACTION', 'Lier');
}

if (!defined('_LINK_ALREADY_EXISTS')) {
    define('_LINK_ALREADY_EXISTS', 'Cette liaison existe d&eacute;j&agrave;');
}

/******************** Versions ************/
if (!defined('_VERSIONS')) {
    define('_VERSIONS', 'Versions');
}
if (!defined('_CREATE_NEW_VERSION')) {
    define('_CREATE_NEW_VERSION', 'Cr&eacute;er une nouvelle version');
}

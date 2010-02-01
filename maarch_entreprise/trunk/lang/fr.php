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

/************** Administration **************/
if (!defined('_ADMIN_USERS'))  define( '_ADMIN_USERS', 'Utilisateurs'); 
if (!defined('_ADMIN_USERS_DESC')) define( '_ADMIN_USERS_DESC', 'Ajouter, suspendre, ou modifier des profils utilisateurs. Placer les utilisateurs dans leurs groupes d&rsquo;appartenance et définir leur groupe primaire.');
if (!defined('_ADMIN_GROUPS')) define( '_ADMIN_GROUPS', 'Groupes d&rsquo;utilisateurs');
if (!defined('_ADMIN_GROUPS_DESC')) define( '_ADMIN_GROUPS_DESC', 'Ajouter, suspendre, ou modifier des groupes d&rsquo;utilisateurs. Attribuer des privil&egrave;ges ou des autorisations d&rsquo;acc&egrave;s aux ressources.');
if (!defined('_ADMIN_ARCHITECTURE')) define( '_ADMIN_ARCHITECTURE', 'Plan de classement');
if (!defined('_ADMIN_ARCHITECTURE_DESC')) define( '_ADMIN_ARCHITECTURE_DESC', 'D&eacute;finir la structure interne d&rsquo;un dossier (chemise / sous-chemise / type de document). D&eacute;finir pour chaque pi&egrave;ce la liste des index &agrave; saisir, et leur caract&egrave;re obligatoire pour la compl&eacute;tude du dossier.');
if (!defined('_VIEW_HISTORY')) define( '_VIEW_HISTORY', 'Historique');
if (!defined('_VIEW_HISTORY_BATCH')) define( '_VIEW_HISTORY_BATCH', 'Historique des batchs');
if (!defined('_VIEW_HISTORY_DESC')) define( '_VIEW_HISTORY_DESC', 'Consulter l&rsquo;historique des &eacute;v&egrave;nements relatifs à l&rsquo;utilisation de la GED Maarch.');
if (!defined('_VIEW_HISTORY_BATCH_DESC')) define( '_VIEW_HISTORY_BATCH_DESC', 'Consulter l&rsquo;historique des batchs');
if (!defined('_ADMIN_MODULES')) define( '_ADMIN_MODULES', 'G&eacute;rer les modules');
if (!defined('_ADMIN_SERVICE')) define( '_ADMIN_SERVICE', 'Service d&rsquo;administration');
if (!defined('_XML_PARAM_SERVICE_DESC')) define( '_XML_PARAM_SERVICE_DESC', 'Visualisation configuration XML des services');
if (!defined('_XML_PARAM_SERVICE')) define( '_XML_PARAM_SERVICE', 'Visualisation configuration XML des services');
if (!defined('_MODULES_SERVICES')) define( '_MODULES_SERVICES', 'Services d&eacute;finis par les modules');
if (!defined('_APPS_SERVICES')) define( '_APPS_SERVICES', 'Services d&eacute;finis par l&rsquo;application');
if (!defined('_ADMIN_STATUS_DESC')) define( '_ADMIN_STATUS_DESC', 'Cr&eacute;er ou modifier des statuts.');
if (!defined('_ADMIN_ACTIONS_DESC')) define( '_ADMIN_ACTIONS_DESC', 'Cr&eacute;er ou modifier des actions.');
if (!defined('_ADMIN_SERVICES_UNKNOWN')) define( '_ADMIN_SERVICES_UNKNOWN', 'Service d&rsquo;administration inconnu');
if (!defined('_NO_RIGHTS_ON')) define( '_NO_RIGHTS_ON', 'Aucun droit sur');
if (!defined('_NO_LABEL_FOUND')) define( '_NO_LABEL_FOUND', 'Aucun label trouv&eacute; pour ce service');

if (!defined('_FOLDERTYPES_LIST')) define( '_FOLDERTYPES_LIST', 'Liste des types de dossier');
if (!defined('_SELECTED_FOLDERTYPES')) define( '_SELECTED_FOLDERTYPES', 'Types de dossier s&eacute;lectionn&eacute;s');
if (!defined('_FOLDERTYPE_ADDED')) define( '_FOLDERTYPE_ADDED', 'Nouveau dossier ajout&eacute;');
if (!defined('_FOLDERTYPE_DELETION')) define( '_FOLDERTYPE_DELETION', 'Dossier supprim&eacute;');



/*********************** communs ***********************************/

/************** Listes **************/
if (!defined('_GO_TO_PAGE')) define( '_GO_TO_PAGE', 'Aller &agrave; la page');
if (!defined('_NEXT')) define( '_NEXT', 'Suivante');
if (!defined('_PREVIOUS')) define( '_PREVIOUS', 'Pr&eacute;c&eacute;dente');
if (!defined('_ALPHABETICAL_LIST')) define( '_ALPHABETICAL_LIST', 'Liste alphab&eacute;tique');
if (!defined('_ASC_SORT')) define( '_ASC_SORT', 'Tri ascendant');
if (!defined('_DESC_SORT')) define( '_DESC_SORT', 'Tri descendant');
if (!defined('_ACCESS_LIST_STANDARD')) define( '_ACCESS_LIST_STANDARD', 'Affichage des listes simples');
if (!defined('_ACCESS_LIST_EXTEND')) define( '_ACCESS_LIST_EXTEND', 'Affichage des listes &eacute;tendues');
if (!defined('_DISPLAY')) define( '_DISPLAY', 'Affichage');
/************** Actions **************/
if (!defined('_DELETE')) define( '_DELETE', 'Supprimer');
if (!defined('_ADD')) define( '_ADD', 'Ajouter');
if (!defined('_REMOVE')) define( '_REMOVE', 'Enlever');
if (!defined('_MODIFY')) define( '_MODIFY', 'Modifier');
if (!defined('_SUSPEND')) define( '_SUSPEND', 'Suspendre');
if (!defined('_AUTHORIZE')) define( '_AUTHORIZE', 'Autoriser');
if (!defined('_SEND')) define( '_SEND', 'Envoyer');
if (!defined('_SEARCH')) define( '_SEARCH', 'Rechercher');
if (!defined('_RESET')) define( '_RESET', 'R&eacute;initialiser');
if (!defined('_VALIDATE')) define( '_VALIDATE', 'Valider');
if (!defined('_CANCEL')) define( '_CANCEL', 'Annuler');
if (!defined('_ADDITION')) define( '_ADDITION', 'Ajout');
if (!defined('_MODIFICATION')) define( '_MODIFICATION', 'Modification');
if (!defined('_DIFFUSION')) define( '_DIFFUSION', 'Diffusion');
if (!defined('_DELETION')) define( '_DELETION', 'Suppression');
if (!defined('_SUSPENSION')) define( '_SUSPENSION', 'Suspension');
if (!defined('_VALIDATION')) define( '_VALIDATION', 'Validation');
if (!defined('_REDIRECTION')) define( '_REDIRECTION', 'Redirection');
if (!defined('_DUPLICATION')) define( '_DUPLICATION', 'Duplication');
if (!defined('_PROPOSITION')) define( '_PROPOSITION', 'Proposition');
if (!defined('_CLOSE')) define( '_CLOSE', 'Fermer');
if (!defined('_CLOSE_WINDOW')) define( '_CLOSE_WINDOW', 'Fermer la fen&ecirc;tre');
if (!defined('_DIFFUSE')) define( '_DIFFUSE', 'Diffuser');
if (!defined('_DOWN')) define( '_DOWN', 'Descendre');
if (!defined('_UP')) define( '_UP', 'Monter');
if (!defined('_REDIRECT')) define( '_REDIRECT', 'Rediriger');
if (!defined('_DELETED')) define( '_DELETED', 'Supprim&eacute;');
if (!defined('_CONTINUE')) define( '_CONTINUE', 'Continuer');
if (!defined('_VIEW')) define( '_VIEW','Visualisation');
if (!defined('_CHOOSE_ACTION')) define( '_CHOOSE_ACTION', 'Choisissez une action');
if (!defined('_ACTIONS')) define( '_ACTIONS', 'Actions'); 
if (!defined('_ACTION_PAGE')) define( '_ACTION_PAGE', 'Page de r&eacute;sultat de l&rsquo;action');
if (!defined('_DO_NOT_MODIFY_UNLESS_EXPERT')) define( '_DO_NOT_MODIFY_UNLESS_EXPERT', ' Ne pas modifier cette section &agrave; moins de savoir ce que vous faites. Un mauvais param&egrave;trage peut entrainer des dysfonctionnements de l&rsquo;application!');
if (!defined('_INFOS_ACTIONS')) define( '_INFOS_ACTIONS', 'Vous devez choisir au moins un statut et / ou un script.');



/************** Intitul&eacute;s formulaires et listes **************/
if (!defined('_ID')) define( '_ID', 'Identifiant');
if (!defined('_PASSWORD')) define( '_PASSWORD', 'Mot de passe');
if (!defined('_GROUP')) define( '_GROUP', 'Groupe');
if (!defined('_USER')) define( '_USER', 'Utilisateur');
if (!defined('_DESC')) define( '_DESC', 'Description');
if (!defined('_LASTNAME')) define( '_LASTNAME', 'Nom');
if (!defined('_THE_LASTNAME')) define( '_THE_LASTNAME', 'Le nom');
if (!defined('_THE_FIRSTNAME')) define( '_THE_FIRSTNAME', 'Le pr&eacute;nom');
if (!defined('_FIRSTNAME')) define( '_FIRSTNAME', 'Pr&eacute;nom');
if (!defined('_STATUS')) define( '_STATUS', 'Statut');
if (!defined('_DEPARTMENT')) define( '_DEPARTMENT', 'D&eacute;partement');
if (!defined('_FUNCTION')) define( '_FUNCTION', 'Fonction');
if (!defined('_PHONE_NUMBER')) define( '_PHONE_NUMBER', 'Num&eacute;ro de t&eacute;l&eacute;phone');
if (!defined('_MAIL')) define( '_MAIL', 'Courriel');
if (!defined('_DOCTYPE')) define( '_DOCTYPE', 'Type de document');
if (!defined('_TYPE')) define( '_TYPE', 'Type');
if (!defined('_SELECT_ALL')) define( '_SELECT_ALL', 'S&eacute;lectionner tout');
if (!defined('_DATE')) define( '_DATE', 'Date');
if (!defined('_ACTION')) define( '_ACTION', 'Action');
if (!defined('_COMMENTS')) define( '_COMMENTS', 'Commentaires');
if (!defined('_ENABLED')) define( '_ENABLED', 'Autoris&eacute;');
if (!defined('_NOT_ENABLED')) define( '_NOT_ENABLED', 'Suspendu');
if (!defined('_RESSOURCES_COLLECTION')) define( '_RESSOURCES_COLLECTION','Collection documentaire');
if (!defined('_RECIPIENT')) define( '_RECIPIENT', 'Destinataire');
if (!defined('_START')) define( '_START', 'D&eacute;but');
if (!defined('_END')) define( '_END', 'Fin');

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
if (!defined('_DOCTYPE_DELETION')) define( '_DOCTYPE_DELETION', 'Suppression du type de document;');
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
if (!defined('_WELCOME_NOTES1')) define( '_WELCOME_NOTES1', 'Pour naviguer dans l\'application');
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
if (!defined('_NO_RIGHT_TXT')) define( '_NO_RIGHT_TXT', 'Vous avez tentez d&rsquo;acc&eacute;der &agrave; un document auquel vous n&rsquo;avez pas droit ou le document n&rsquo;existe pas...');
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


///// Credits
if (!defined('_MAARCH_CREDITS')) define( '_MAARCH_CREDITS', 'A propos de Maarch&nbsp;');


if (!defined('_CR_LONGTEXT_INFOS')) define( '_CR_LONGTEXT_INFOS', '<p>Maarch Framework 3 est une infrastructure de <b>GED de Production</b>, r&eacute;pondant en standard &agrave; la plupart des besoins de gestion op&eacute;rationnelle de contenu d\'une organisation. La tr&egrave;s grande majorit&eacute; des composants du Framework est diffusé sous les termes de la licence open source GNU GPLv3, de sorte que le coût d\'impl&eacute;mentation rend la solution aborbable pour tout type d\'organisation (public, priv&eacute;, parapublic, monde associatif).</p> <p>Pour autant, Maarch Framework ayant &eacute;t&eacute; conçu par deux consultants cumulant &agrave; eux deux plus de 20 ans d\'expertise en Syst&egrave;mes d\'Archivage &Eacute;lectronique et en &Eacute;ditique, le produit offre <b>toutes les garanties de robustesse, d\'int&eacute;grit&eacute;, de performance</b> que l\'on doit attendre de ce type de produit. Un grand soin a &eacute;t&eacute; port&eacute; sur l\'architecture afin d\'autoriser des performances maximales sur du mat&eacute;riel standard.</p><p>Maarch est d&eacute;velopp&eacute; en PHP5 objet. Il est compatible avec les 4 moteurs de bases de donn&eacute;es suivants&nbsp;: MySQL, PostgreSQL, SQLServer, et bientôt Oracle.</p> <p>Maarch est <b>totalement modulaire</b>&nbsp;: toutes les fonctionnalit&eacute;s sont regroup&eacute;es dans des modules exposant des services qui peuvent être activ&eacute;s/d&eacute;sactiv&eacute;s en fonction du profil de l\'utilisateur. Un ing&eacute;nieur exp&eacute;riment&eacute; peut ajouter ou remplacer un module existant sans toucher au coeur du syst&egrave;me.</p><p>Maarch propose un sch&eacute;ma global et <b>tous les outils pour acqu&eacute;rir, g&eacute;rer, conserver puis restituer les flux documentaires de production</b>.');

if (!defined('_PROCESSING_DATE')) define( '_PROCESSING_DATE', 'Date limite de traitement');
if (!defined('_PROCESS_NUM')) define( '_PROCESS_NUM','Traitement du courrier n&deg;');
if (!defined('_PROCESS_LIMIT_DATE')) define( '_PROCESS_LIMIT_DATE', 'Date limite de traitement');
if (!defined('_LATE_PROCESS')) define( '_LATE_PROCESS', 'En retard');
if (!defined('_PROCESS_DELAY')) define( '_PROCESS_DELAY', 'D&eacute;lai de traitement');
if (!defined('_ALARM1_DELAY')) define( '_ALARM1_DELAY', 'D&eacute;lai relance 1 (jours)');
if (!defined('_ALARM2_DELAY')) define( '_ALARM2_DELAY', 'D&eacute;lai relance 2 (jours)');
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
if (!defined('_SIMPLE_MAIL')) define( '_SIMPLE_MAIL', 'Courrier simple');
if (!defined('_EMAIL')) define( '_EMAIL', 'Mail');
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


if (!defined('_INDEXING_MLB')) define( '_INDEXING_MLB', 'Enregistrer un document');
if (!defined('_ADV_SEARCH_MLB')) define( '_ADV_SEARCH_MLB', 'Rechercher un document');

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

if (!defined('_LETTERBOX')) define( '_LETTERBOX', 'Collection principale');
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
if (!defined('_MD5')) define( '_MD5', 'Empreinte MD5');
if (!defined('_WORK_BATCH')) define( '_WORK_BATCH', 'Lot de chargement');
if (!defined('_DONE')) define( '_DONE','Actions effectu&eacute;es');
if (!defined('_ANSWER_TYPES_DONE')) define( '_ANSWER_TYPES_DONE', 'Type(s) de r&eacute;ponses effectu&eacute;es');
if (!defined('_CLOSING_DATE')) define( '_CLOSING_DATE', 'Date de cl&ocirc;ture');
if (!defined('_FULLTEXT')) define( '_FULLTEXT', 'Plein texte');
if (!defined('_FULLTEXT_HELP')) define( '_FULLTEXT_HELP', 'Recherche plein texte avec le moteur Luc&egrave;ne...');
if (!defined('_FILE_NOT_SEND')) define( '_FILE_NOT_SEND', 'Le fichier n&rsquo;a pas &eacute;t&eacute; envoy&eacute;');
if (!defined('_TRY_AGAIN')) define( '_TRY_AGAIN', 'Veuillez r&eacute;essayer');
if (!defined('_DOCTYPE_MANDATORY')) define( '_DOCTYPE_MANDATORY', 'Le type de pi&egrave;ce est obligatoire');
if (!defined('_INDEX_UPDATED')) define( '_INDEX_UPDATED', 'Index mis &agrave; jour');
if (!defined('_DOC_DELETED')) define( '_DOC_DELETED', 'Document supprimé');

if (!defined('_QUICKLAUNCH')) define( '_QUICKLAUNCH', 'Raccourcis');
if (!defined('_SHOW_DETAILS_DOC')) define( '_SHOW_DETAILS_DOC', 'Voir les d&eacute;tails du document');
if (!defined('_VIEW_DOC_FULL')) define( '_VIEW_DOC_FULL', 'Voir le document');
if (!defined('_DETAILS_DOC_FULL')) define( '_DETAILS_DOC_FULL', 'Voir la fiche du document');
if (!defined('_IDENTIFIER')) define( '_IDENTIFIER', 'R&eacute;f&eacute;rence');
if (!defined('_CHRONO_NUMBER')) define( '_CHRONO_NUMBER', 'Num&eacute;ro chrono');
if (!defined('_NO_CHRONO_NUMBER_DEFINED')) define( '_NO_CHRONO_NUMBER_DEFINED', 'Le num&eacute;ro chrono n\'est pas d&eacute;fini');
if (!defined('_FOR_CONTACT_C')) define( '_FOR_CONTACT_C', 'Pour');
if (!defined('_TO_CONTACT_C')) define( '_TO_CONTACT_C', 'De');

if (!defined('_APPS_COMMENT')) define( '_APPS_COMMENT', 'Application Maarch Entreprise');
if (!defined('_CORE_COMMENT')) define( '_CORE_COMMENT', 'Coeur du Framework');
if (!defined('_CLEAR_FORM')) define( '_CLEAR_FORM', 'Effacer le formulaire');

if (!defined('_MAX_SIZE_UPLOAD_REACHED')) define( '_MAX_SIZE_UPLOAD_REACHED', 'Taille maximum de fichier d&eacute;pass&eacute;e');
if (!defined('_NOT_ALLOWED')) define( '_NOT_ALLOWED', 'interdit');
if (!defined('_CHOOSE_TITLE')) define( '_CHOOSE_TITLE', 'Choisissez une civilit&eacute;');

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



/******************** Keywords Helper ************/
if (!defined('_ACTIONS')) define( '_HELP_KEYWORD0', 'id de l&rsquo;utilisateur connect&eacute;');
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



























?>

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
define('_ADMIN_USERS', 'Utilisateurs');
define('_ADMIN_USERS_DESC', 'Ajouter, suspendre, ou modifier des profils utilisateurs. Placer les utilisateurs dans leurs groupes d&rsquo;appartenance et définir leur groupe primaire.');
define('_ADMIN_GROUPS', 'Groupes d&rsquo;utilisateurs');
define('_ADMIN_GROUPS_DESC', 'Ajouter, suspendre, ou modifier des groupes d&rsquo;utilisateurs. Attribuer des privil&egrave;ges ou des autorisations d&rsquo;acc&egrave;s aux ressources.');
define('_ADMIN_ARCHITECTURE', 'Plan de classement');
define('_ADMIN_ARCHITECTURE_DESC', 'D&eacute;finir la structure interne d&rsquo;un dossier (chemise / sous-chemise / type de document). D&eacute;finir pour chaque pi&egrave;ce la liste des index &agrave; saisir, et leur caract&egrave;re obligatoire pour la compl&eacute;tude du dossier.');
define('_VIEW_HISTORY', 'Historique');
define('_VIEW_HISTORY_BATCH', 'Historique des batchs');
define('_VIEW_HISTORY_DESC', 'Consulter l&rsquo;historique des &eacute;v&egrave;nements relatifs à l&rsquo;utilisation de la GED Maarch.');
define('_VIEW_HISTORY_BATCH_DESC', 'Consulter l&rsquo;historique des batchs');
define('_ADMIN_MODULES', 'G&eacute;rer les modules');
define('_ADMIN_SERVICE', 'Service d&rsquo;administration');
define('_XML_PARAM_SERVICE_DESC', 'Visualisation configuration XML des services');
define('_XML_PARAM_SERVICE', 'Visualisation configuration XML des services');
define('_MODULES_SERVICES', 'Services d&eacute;finis par les modules');
define('_APPS_SERVICES', 'Services d&eacute;finis par l&rsquo;application');
define('_ADMIN_STATUS_DESC', 'Cr&eacute;er ou modifier des statuts.');
define('_ADMIN_ACTIONS_DESC', 'Cr&eacute;er ou modifier des actions.');
define('_ADMIN_SERVICES_UNKNOWN', 'Service d&rsquo;administration inconnu');
define('_NO_RIGHTS_ON', 'Aucun droit sur');
define('_NO_LABEL_FOUND', 'Aucun label trouv&eacute; pour ce service');

define('_FOLDERTYPES_LIST', 'Liste des types de dossier');
define('_SELECTED_FOLDERTYPES', 'Types des dossier s&eacute;lectionn&eacute;s');
define('_FOLDERTYPE_ADDED', 'Nouveau dossier ajout&eacute;');
define('_FOLDERTYPE_DELETION', 'Dossier supprim&eacute;');



/*********************** communs ***********************************/

/************** Listes **************/
define('_GO_TO_PAGE', 'Aller &agrave; la page');
define('_NEXT', 'Suivante');
define('_PREVIOUS', 'Pr&eacute;c&eacute;dente');
define('_ALPHABETICAL_LIST', 'Liste alphab&eacute;tique');
define('_ASC_SORT', 'Tri ascendant');
define('_DESC_SORT', 'Tri descendant');
define('_ACCESS_LIST_STANDARD', 'Affichage des listes simples');
define('_ACCESS_LIST_EXTEND', 'Affichage des listes &eacute;tendues');
define('_DISPLAY', 'Affichage');
/************** Actions **************/
define('_DELETE', 'Supprimer');
define('_ADD', 'Ajouter');
define('_REMOVE', 'Enlever');
define('_MODIFY', 'Modifier');
define('_SUSPEND', 'Suspendre');
define('_AUTHORIZE', 'Autoriser');
define('_SEND', 'Envoyer');
define('_SEARCH', 'Rechercher');
define('_RESET', 'R&eacute;initialiser');
define('_VALIDATE', 'Valider');
define('_CANCEL', 'Annuler');
define('_ADDITION', 'Ajout');
define('_MODIFICATION', 'Modification');
define('_DIFFUSION', 'Diffusion');
define('_DELETION', 'Suppression');
define('_SUSPENSION', 'Suspension');
define('_VALIDATION', 'Validation');
define('_REDIRECTION', 'Redirection');
define('_DUPLICATION', 'Duplication');
define('_PROPOSITION', 'Proposition');
define('_CLOSE', 'Fermer');
define('_CLOSE_WINDOW', 'Fermer la fen&ecirc;tre');
define('_DIFFUSE', 'Diffuser');
define('_DOWN', 'Descendre');
define('_UP', 'Monter');
define('_REDIRECT', 'Rediriger');
define('_DELETED', 'Supprim&eacute;');
define('_CONTINUE', 'Continuer');
define('_VIEW','Visualisation');
define('_CHOOSE_ACTION', 'Choisissez une action');
define('_ACTIONS', 'Actions');
define('_ACTION_PAGE', 'Page de r&eacute;sultat de l&rsquo;action');
define('_DO_NOT_MODIFY_UNLESS_EXPERT', ' Ne pas modifier cette section &agrave; moins de savoir ce que vous faites. Un mauvais param&egrave;trage peut entrainer des dysfonctionnements de l&rsquo;application!');
define('_INFOS_ACTIONS', 'Vous devez choisir au moins un statut et / ou un script.');



/************** Intitul&eacute;s formulaires et listes **************/
define('_ID', 'Identifiant');
define('_PASSWORD', 'Mot de passe');
define('_GROUP', 'Groupe');
define('_USER', 'Utilisateur');
define('_DESC', 'Description');
define('_LASTNAME', 'Nom');
define('_THE_LASTNAME', 'Le nom');
define('_THE_FIRSTNAME', 'Le pr&eacute;nom');
define('_FIRSTNAME', 'Pr&eacute;nom');
define('_STATUS', 'Statut');
define('_DEPARTMENT', 'D&eacute;partement');
define('_FUNCTION', 'Fonction');
define('_PHONE_NUMBER', 'Num&eacute;ro de t&eacute;l&eacute;phone');
define('_MAIL', 'Courriel');
define('_DOCTYPE', 'Type de document');
define('_TYPE', 'Type');
define('_SELECT_ALL', 'S&eacute;lectionner tout');
define('_DATE', 'Date');
define('_ACTION', 'Action');
define('_COMMENTS', 'Commentaires');
define('_ENABLED', 'Autoris&eacute;');
define('_NOT_ENABLED', 'Suspendu');
define('_RESSOURCES_COLLECTION','Collection documentaire');
define('_RECIPIENT', 'Destinataire');
define('_START', 'D&eacute;but');
define('_END', 'Fin');

/************** Messages pop up **************/
define('_REALLY_SUSPEND', 'Voulez-vous vraiment suspendre ');
define('_REALLY_AUTHORIZE', 'Voulez-vous vraiment autoriser ');
define('_REALLY_DELETE', 'Voulez-vous vraiment supprimer ');
define('_DEFINITIVE_ACTION', 'Cette action est d&eacute;finitive');

/************** Divers **************/
define('_YES', 'Oui');
define('_NO', 'Non');
define('_UNKNOWN', 'Inconnu');
define('_SINCE','Depuis');
define('_FOR','Jusqu&rsquo;&agrave;');
define('_HELLO','Bonjour');
define('_OBJECT','Objet');
define('_BACK','Retour');
define('_FORMAT','Format');
define('_SIZE','Taille');
define('_DOC', 'Document ');
define('_THE_DOC', 'Le document');
define('_BYTES', 'octets');
define('_OR', 'ou');
define('_NOT_AVAILABLE', 'Indisponible');
define('_SELECTION', 'S&eacute;lection');
define('_AND', ' et ' );
define('_FILE','Fichier');
define('_UNTIL', 'au');
define('_ALL', 'Tous');

//class functions
define('_SECOND', 'seconde');
define('_SECONDS', 'secondes');
define('_PAGE_GENERATED_IN', 'Page g&eacute;n&eacute;r&eacute;e en');
define('_IS_EMPTY', 'est vide');
define('_MUST_MAKE_AT_LEAST', 'doit faire au minimum' );
define('_CHARACTER', 'caract&egrave;re');
define('_CHARACTERS', 'caract&egrave;res');
define('MUST_BE_LESS_THAN', 'ne doit pas faire plus de');
define('_WRONG_FORMAT', 'n&rsquo;est pas dans le bon format');
define('_WELCOME', 'Bienvenue sur Maarch Entreprise !');
define('_WELCOME_TITLE', 'Accueil');
define('_HELP', 'Aide');
define('_SEARCH_ADV_SHORT', 'Recherche Avanc&eacute;e');
define('_RESULTS', 'R&eacute;sultats');
define('_USERS_LIST_SHORT', 'Liste utilisateurs');
define('_MODELS_LIST_SHORT', 'Liste mod&egrave;les');
define('_GROUPS_LIST_SHORT', 'Liste groupes');
define('_DEPARTMENTS_LIST_SHORT', 'Liste services');
define('_BITMASK', 'Param&egrave;tre Bitmask');
define('_DOCTYPES_LIST_SHORT', 'Liste types');
define('_BAD_MONTH_FORMAT', 'Le mois est incorrect');
define('_BAD_DAY_FORMAT', 'Le jour est incorrect');
define('_BAD_YEAR_FORMAT', 'L&rsquo;ann&eacute;e est incorrect');
define('_BAD_FEBRUARY', 'Le mois de f&eacute;vrier ne peux contenir que 29 jours maximum');
define('_CHAPTER_SHORT', 'Chap ');
define('_PROCESS_SHORT', 'Traitement');
define('_CARD', 'Fiche');

/************************* First login ***********************************/
define('_MODIFICATION_PSW', 'Modification du mot de passe');
define('_YOUR_FIRST_CONNEXION', 'Bienvenue sur Maarch Entreprise ! <br/>Ceci est votre premi&egrave;re connexion');
define('_PLEASE_CHANGE_PSW', ' veuillez d&eacute;finir votre mot de passe');
define('_ASKED_ONLY_ONCE', 'Cela ne vous sera demand&eacute; qu&rsquo;une seule fois');
define('_FIRST_CONN', 'Premi&egrave;re connexion');
define('_LOGIN', 'Connexion');
define('_RELOGIN', 'Reconnexion');

/*************************  index  page***********************************/
define('_LOGO_ALT', 'Retour &agrave; la page d&rsquo;accueil');
define('_LOGOUT', 'D&eacute;connexion');
define('_MENU', 'Menu');
define('_ADMIN', 'Administration');
define('_SUMMARY', 'Sommaire');
define('_MANAGE_DIPLOMA', 'G&eacute;rer les dipl&ocirc;mes');
define('_MANAGE_CONTRACT', 'G&eacute;rer les types de contrats');
define('_MANAGE_REL_MODEL', 'G&eacute;rer le mod&egrave;le de relance');
define('_MANAGE_DOCTYPES', 'G&eacute;rer les types de documents');
define('_MANAGE_DOCTYPES_DESC', 'Administrer les types de documents. Les types de documents sont rattach&eacute;s &agrave; une collection documentaire. Pour chaque type, vous pouvez d&eacute;finir les index &agrave; saisir et ceux qui sont obligatoires.');
define('_VIEW_HISTORY2', 'Visualisation de l&rsquo;historique');
define('_VIEW_HISTORY_BATCH2', 'Visualisation de l&rsquo;historique des batchs');
define('_INDEX_FILE', 'Indexer un fichier');
define('_WORDING', 'Libell&eacute;');
define('_COLLECTION', 'Collection');
define('_VIEW_TREE_DOCTYPES', 'Arborescence du plan de classement');
define('_VIEW_TREE_DOCTYPES_DESC', 'Voir l&rsquo;arborescence du plan de classement (types de dossiers, chemises, sous-chemises et types de documents)');
define('_WELCOME_ON', 'Bienvenue sur');

/************************* Administration ***********************************/

/**************Sommaire**************/
define('_MANAGE_GROUPS_APP', 'G&eacute;rer les groupes de l&rsquo;application');
define('_MANAGE_USERS_APP', 'G&eacute;rer les utilisateurs de l&rsquo;application');
define('_MANAGE_DIPLOMA_APP', 'G&eacute;rer les dipl&ocirc;mes de l&rsquo;application');
define('_MANAGE_DOCTYPES_APP', 'G&eacute;rer les types de document de l&rsquo;application');
define('_MANAGE_ARCHI_APP', 'G&eacute;rer l&rsquo;architecture des types de document de l&rsquo;application');
define('_MANAGE_CONTRACT_APP', 'G&eacute;rer les types de contrat de l&rsquo;application');
define('_HISTORY_EXPLANATION', 'Surveiller les modifications, les suppressions et les ajouts dans l&rsquo;application');
define('_ARCHI_EXP', 'les chemises, les sous-chemises et les types de document');


/************** Groupes : Liste + Formulaire**************/

define('_GROUPS_LIST', 'Liste des groupes');
define('_ADMIN_GROUP', 'Groupe d&rsquo;administration');
define('_ADD_GROUP', 'Ajouter un groupe');
define('_ALL_GROUPS', 'Tous les groupes');
define('_GROUPS', 'groupes');

define('_GROUP_ADDITION', 'Ajout d&rsquo;un groupe');
define('_GROUP_MODIFICATION', 'Modification d&rsquo;un groupe');
define('_SEE_GROUP_MEMBERS', 'Voir la liste des utilisateurs de ce groupe');
define('_OTHER_RIGHTS', 'Autres droits');
define('_MODIFY_GROUP', 'Accepter les changements');
define('_THE_GROUP', 'Le groupe');
define('_HAS_NO_SECURITY', 'n&rsquo;a aucune s&eacute;curit&eacute; d&eacute;finie' );

define('_DEFINE_A_GRANT', 'D&eacute;finissez au moins un acc&egrave;s');
define('_MANAGE_RIGHTS', 'Ce groupe a acc&egrave;s aux ressources suivantes');
define('_TABLE', 'Table');
define('_WHERE_CLAUSE', 'Clause WHERE');
define('_INSERT', 'Insertion');
define('_UPDATE', 'Mise &agrave; jour');
define('_REMOVE_ACCESS', 'Supprimer acc&egrave;s');
define('_MODIFY_ACCESS', 'Modifier acc&egrave;s');
define('_UPDATE_RIGHTS', 'Mise &agrave; jour des droits');
define('_ADD_GRANT', 'Ajouter acc&egrave;s');
define('_USERS_LIST_IN_GROUP', 'Liste des utilisateurs du groupe');

/************** Utilisateurs : Liste + Formulaire**************/

define('_USERS_LIST', 'Liste des utilisateurs');
define('_ADD_USER', 'Ajouter un utilisateur');
define('_ALL_USERS', 'Tous les utilisateurs');
define('_USERS', 'utilisateurs');
define('_USER_ADDITION', 'Ajout d&rsquo;un utilisateur');
define('_USER_MODIFICATION', 'Modification d&rsquo;un utilisateur');
define('_MODIFY_USER', 'Modifier l&rsquo;utilisateur');

define('_NOTES', 'Notes');
define('_NOTE1', 'Les champs obligatoires sont marqu&eacute;s par un ast&eacute;risque rouge ');
define('_NOTE2', 'Le groupe primaire est obligatoire');
define('_NOTE3', 'Le premier groupe s&eacute;lectionn&eacute sera le groupe primaire');
define('_USER_GROUPS_TITLE', 'L&rsquo;utilisateur appartient aux groupes suivants');
define('_USER_ENTITIES_TITLE', 'L&rsquo;utilisateur appartient aux entit&eacute;s suivantes');
define('_DELETE_GROUPS', 'Supprimer le(s) groupe(s)');
define('_ADD_TO_GROUP', 'Ajouter &agrave; un groupe');
define('_CHOOSE_PRIMARY_GROUP', 'Choisir comme groupe primaire');
define('_USER_BELONGS_NO_GROUP', 'L&rsquo;utilisateur n&rsquo;appartient &agrave; aucun groupe');
define('_USER_BELONGS_NO_ENTITY', 'L&rsquo;utilisateur n&rsquo;appartient &agrave; aucune  entit&eacute;');
define('_CHOOSE_ONE_GROUP', 'Choisissez au moins un groupe');
define('_PRIMARY_GROUP', 'Groupe primaire');
define('_CHOOSE_GROUP', 'Choisissez un groupe');
define('_ROLE', 'R&ocirc;le');

define('_THE_PSW', 'Le mot de passe');
define('_THE_PSW_VALIDATION', 'La validation du mot de passe' );
define('_REENTER_PSW', 'Retaper le mot de passe');
define('_USER_ACCESS_DEPARTMENT', 'L&rsquo;utilisateur a acc&egrave;s aux services suivants');
define('_FIRST_PSW', 'Le premier mot de passe ');
define('_SECOND_PSW', 'Le deuxi&egrave;me mot de passe ');

define('_PASSWORD_MODIFICATION', 'Changement du mot de passe');
define('_PASSWORD_FOR_USER', 'Le mot de passe pour l&rsquo;utilisateur');
define('_HAS_BEEN_RESET', 'a &eacute;t&eacute; r&eacute;initialis&eacute;');
define('_NEW_PASW_IS', 'Le nouveau mot de passe est ');
define('_DURING_NEXT_CONNEXION', 'Lors de la prochaine connexion');
define('_MUST_CHANGE_PSW', 'doit modifier son mot de passe');

define('_NEW_PASSWORD_USER', 'R&eacute;initialisation du mot de passe de l&rsquo;utilisateur');

/************** Types de document : Liste + Formulaire**************/

define('_DOCTYPES_LIST', 'Liste des types de document');
define('_ADD_DOCTYPE', 'Ajouter un type');
define('_ALL_DOCTYPES', 'Tous les types');
define('_TYPES', 'types');

define('_DOCTYPE_MODIFICATION', 'Modification d&rsquo;un type de document');
define('_DOCTYPE_CREATION', 'Cr&eacute;ation d&rsquo;un type de document');

define('_MODIFY_DOCTYPE', 'Valider les changements');
define('_ATTACH_SUBFOLDER', 'Rattach&eacute; &agrave; la sous-chemise');
define('_CHOOSE_SUBFOLDER', 'Choisissez une sous-chemise');
define('_MANDATORY_FOR_COMPLETE', 'Obligatoire pour la compl&eacute;tude du dossier d&rsquo;embauche');
define('_MORE_THAN_ONE', 'Pi&egrave;ce it&eacute;rative');
define('_MANDATORY_FIELDS_IN_INDEX', 'Champs obligatoires &agrave; l&rsquo;indexation');
define('_DIPLOMA_LEVEL', 'Niveau de dipl&ocirc;me');
define('_THE_DIPLOMA_LEVEL', 'Le niveau de dipl&ocirc;me');
define('_DATE_END_DETACH_TIME', 'Date de fin de p&eacute;riode de d&eacute;tachement');
define('_START_DATE', 'Date de d&eacute;but');
define('_START_DATE_PROBATION', 'Date de d&eacute;but de p&eacute;riode de probatoire');
define('_END_DATE', 'Date de fin');
define('_END_DATE_PROBATION', 'Date de fin de p&eacute;riode de probatoire');
define('_START_DATE_TRIAL', 'Date de d&eacute;but de p&eacute;riode d&rsquo;essai');
define('_START_DATE_MISSION', 'Date de d&eacute;but de mission');
define('_END_DATE_TRIAL', 'Date de fin de p&eacute;riode d&rsquo;essai');
define('_END_DATE_MISSION', 'Date de fin de mission');
define('_EVENT_DATE', 'Date de l&rsquo;&eacute;v&egrave;nement');
define('_VISIT_DATE', 'Date de la visite');
define('_CHANGE_DATE', 'Date du changement ');
define('_DOCTYPES_LIST2', 'Liste des types de pi&egrave;ce');

define('_INDEX_FOR_DOCTYPES', 'Index possibles pour les types de document');
define('_FIELD', 'Champ');
define('_USED', 'Utilis&eacute;');
define('_MANDATORY', 'Obligatoire');
define('_ITERATIVE', 'It&eacute;ratif');

define('_MASTER_TYPE', 'Type ma&icirc;tre');

/************** structures : Liste + Formulaire**************/
define('_STRUCTURE_LIST', 'Liste des chemises');
define('_STRUCTURES', 'chemise(s)');
define('_STRUCTURE', 'Chemise');
define('_ALL_STRUCTURES', 'Toutes les chemises');

define('_THE_STRUCTURE', 'La chemise');
define('_STRUCTURE_MODIF', 'Modification de la chemise');
define('_ID_STRUCTURE_PB', 'Il y a un probl&egrave;me avec l&rsquo;identifiant de la chemise');
define('_NEW_STRUCTURE_ADDED', 'Ajout d&rsquo;une nouvelle chemise');
define('_NEW_STRUCTURE', 'Nouvelle chemise');
define('_DESC_STRUCTURE_MISSING', 'Il manque la description de la chemise');
define('_STRUCTURE_DEL', 'Suppression de la chemise');
define('_DELETED_STRUCTURE', 'Chemise supprim&eacute;e');

/************** sous-dossiers : Liste + Formulaire**************/
define('_SUBFOLDER_LIST', 'Liste des sous-chemises');
define('_SUBFOLDERS', 'sous-chemise(s)');
define('_ALL_SUBFOLDERS', 'Toutes les sous-chemises');
define('_SUBFOLDER', 'sous-chemise');

define('_ADD_SUBFOLDER', 'Ajouter une nouvelle sous-chemise');
define('_THE_SUBFOLDER', 'La sous-chemise');
define('_SUBFOLDER_MODIF', 'Modification de la sous-chemise');
define('_SUBFOLDER_CREATION', 'Cr&eacute;ation de la sous-chemise');
define('_SUBFOLDER_ID_PB', 'Il y a un probleme avec l&rsquo;identifiant de la sous-chemise');
define('_SUBFOLDER_ADDED', 'Ajout d&rsquo;unen nouvelle sous-chemise');
define('_NEW_SUBFOLDER', 'Nouvelle sous-chemise');
define('_STRUCTURE_MANDATORY', 'La chemise est obligatoire');
define('_SUBFOLDER_DESC_MISSING', 'Il manque la description de la sous-chemise');

define('_ATTACH_STRUCTURE', 'Rattachement &agrave; une chemise');
define('_CHOOSE_STRUCTURE', 'Choissisez une chemise');

define('_DEL_SUBFOLDER', 'Suppression de la sous-chemise');
define('_SUBFOLDER_DELETED', 'Sous-chemise supprim&eacute;e');


/************** Status **************/

define('_STATUS_LIST', 'Liste des statuts');
define('_ADD_STATUS', 'Ajouter nouveau statut');
define('_ALL_STATUS', 'Tous les statuts');
define('_STATUS_PLUR', 'Statut(s)');
define('_STATUS_SING', 'statut');

define('_TO_PROCESS','A traiter');
define('_IN_PROGRESS','En cours');
define('_FIRST_WARNING','1ere Relance');
define('_SECOND_WARNING','2e Relance');
define('_CLOSED','Clos');
define('_NEW','Nouveaux');
define('_LATE', 'En retard');

define('_STATUS_DELETED', 'Suppression du statut');
define('_DEL_STATUS', 'Statut supprim&eacute;');
define('_MODIFY_STATUS', 'Modification du statut');
define('_STATUS_ADDED','Ajout d&rsquo;un nouveau statut');
define('_STATUS_MODIFIED','Modification d&rsquo;un statut');
define('_NEW_STATUS', 'Nouveau statut');
define('_IS_SYSTEM', 'Syst&egrave;me');
define('_CAN_BE_SEARCHED', 'Recherche');
define('_CAN_BE_MODIFIED', 'Modification des index');
define('_THE_STATUS', 'Le statut ');
define('_ADMIN_STATUS', 'Statuts');
define('_ADMIN_STATUS_DESC', 'G&eacute;rer les &eacute;tats des ressources dans l&rsquo;application');
/************* Actions **************/

define('_ACTION_LIST', 'Liste des actions');
define('_ADD_ACTION', 'Ajouter nouvelle action');
define('_ALL_ACTIONS', 'Toutes les actions');
define('_ACTIONS', 'actions');
define('_ACTION', 'action');
define('_ACTION_HISTORY', 'Tracer l&rsquo;action');

define('_ACTION_DELETED', 'Suppression de l&rsquo;action');
define('_DEL_ACTION', 'Action supprim&eacute;e');
define('_MODIFY_ACTION', 'Modification de l&rsquo;action');
define('_ACTION_ADDED','Ajout d&rsquo;une nouvelle action');
define('_ACTION_MODIFIED','Modification d&rsquo;une action');
define('_NEW_ACTION', 'Nouvelle action');
define('_THE_ACTION', 'L&rsquo;action ');
define('_ADMIN_ACTIONS', 'Actions');
define('_ADMIN_ACTIONS_DESC', 'G&eacute;rer les actions utilisables dans l&rsquo;application');

/************** Historique**************/
define('_HISTORY_TITLE', 'Historique des &eacute;v&egrave;nements');
define('_HISTORY_BATCH_TITLE', 'Historique des &eacute;v&egrave;nements des batchs');
define('_HISTORY', 'Historique');
define('_HISTORY_BATCH', 'Historique du batch');
define('_BATCH_NAME', 'Nom batch');
define('_CHOOSE_BATCH', 'Choisir batch');
define('_BATCH_ID', 'Id batch');
define('_TOTAL_PROCESSED', 'Documents trait&eacute;s');
define('_TOTAL_ERRORS', 'Documents en erreurs');
define('_ONLY_ERRORS', 'Seulement avec erreurs');
define('_INFOS', 'Infos');

/************** Admin de l'architecture  (plan de classement) **************/
define('_ADMIN_ARCHI', 'Administration du plan de classement');
define('_MANAGE_STRUCTURE', 'G&eacute;rer les chemises');
define('_MANAGE_STRUCTURE_DESC', 'Administrer les chemises. Celles-ci constituent l&rsquo;&eacute;l&eacute;ment le plus haut du plan de classement. Si le module Folder est connect&eacute;, vous pouvez associer un type de dossier &agrave; un plan de classement.');
define('_MANAGE_SUBFOLDER', 'G&eacute;rer les sous-chemises');
define('_MANAGE_SUBFOLDER_DESC', 'G&eacute;rer les sous-chemises à l&rsquo;int&eacute;rieur des chemises.');
define('_ARCHITECTURE', 'Plan de classement');

/************************* Messages d'erreurs ***********************************/
define('_MORE_INFOS', 'Pour plus d&rsquo;informations, contactez votre administrateur ');
define('_ALREADY_EXISTS', 'existe d&eacute;j&agrave; !');

// class usergroups
define('_NO_GROUP', 'Le groupe n&rsquo;existe pas !');
define('_NO_SECURITY_AND_NO_SERVICES', 'n&rsquo;a aucune s&eacute;curit&eacute; d&eacute;finie et aucun service');
define('_GROUP_ADDED', 'Nouveau groupe ajout&eacute;');
define('_SYNTAX_ERROR_WHERE_CLAUSE', 'erreur de syntaxe dans la clause where');
define('_GROUP_UPDATED', 'Groupe modifi&eacute;');
define('_AUTORIZED_GROUP', 'Groupe autoris&eacute;');
define('_SUSPENDED_GROUP', 'Groupe suspendu');
define('_DELETED_GROUP', 'Groupe supprim&eacute;');
define('_GROUP_UPDATE', 'Modification du groupe;');
define('_GROUP_AUTORIZATION', 'Autorisation du groupe');
define('_GROUP_SUSPENSION', 'Suspension du groupe');
define('_GROUP_DELETION', 'Suppression du groupe');
define('_GROUP_DESC', 'La description du groupe ');
define('_GROUP_ID', 'L&rsquo;identifiant du groupe');
define('_EXPORT_RIGHT', 'Droits d&rsquo;export');

//class users
define('_USER_NO_GROUP', 'Vous n&rsquo;appartenez &agrave; aucun groupe');
define('_SUSPENDED_ACCOUNT', 'Votre compte utilisateur a &eacute;t&eacute; suspendu');
define('_BAD_LOGIN_OR_PSW', 'Mauvais nom d&rsquo;utilisateur ou mauvais mot de passe');
define('_WRONG_SECOND_PSW', 'Le deuxi&egrave;me mot de passe ne correspond pas au premier mot de passe !');
define('_AUTORIZED_USER', 'Utilisateur autoris&eacute;');
define('_SUSPENDED_USER', 'Utilisateur suspendu');
define('_DELETED_USER', 'Utilisateur supprim&eacute;');
define('_USER_DELETION', 'Suppression de l&rsquo;utilisateur;');
define('_USER_AUTORIZATION', 'Autorisation de l&rsquo;utilisateur');
define('_USER_SUSPENSION', 'Suspension de l&rsquo;utilisateur');
define('_USER_UPDATED', 'Utilisateur modifi&eacute;');
define('_USER_UPDATE', 'Modification d&rsquo;un utilisateur');
define('_USER_ADDED', 'Nouvel utilisateur ajout&eacute;');
define('_NO_PRIMARY_GROUP', 'Aucun groupe primaire s&eacute;lectionn&eacute; !');
define('_THE_USER', 'L&rsquo;utilisateur ');
define('_USER_ID', 'L&rsquo;identifiant de l&rsquo;utilisateur');
define('_MY_INFO', 'Mon Profil');


//class types
define('_UNKNOWN_PARAM', 'Param&egrave;tres inconnus');
define('_DOCTYPE_UPDATED', 'Type de document modifi&eacute;');
define('_DOCTYPE_UPDATE', 'Modification du type de document');
define('_DOCTYPE_ADDED', 'Nouveau type de document ajout&eacute;');
define('_DELETED_DOCTYPE', 'Type de document supprim&eacute;');
define('_DOCTYPE_DELETION', 'Suppression du type de document;');
define('_THE_DOCTYPE', 'Le type de document ');
define('_THE_WORDING', 'Le libell&eacute; ');
define('_THE_TABLE', 'La table ');
define('_PIECE_TYPE', 'Type de pi&egrave;ce');

//class db
define('_CONNEXION_ERROR', 'Erreur &agrave; la connexion');
define('_SELECTION_BASE_ERROR', 'Erreur &agrave; la s&eacute;lection de la base');
define('_QUERY_ERROR', 'Erreur &agrave; la requ&ecirc;te');
define('_CLOSE_CONNEXION_ERROR', 'Erreur &agrave; la fermeture de la connexion');
define('_ERROR_NUM', 'L&rsquo;erreur n&deg;');
define('_HAS_JUST_OCCURED', 'vient de se produire');
define('_MESSAGE', 'Message');
define('_QUERY', 'Requ&ecirc;te');
define('_LAST_QUERY', 'Derni&egrave;re requ&ecirc;te');

//Autres
define('_NO_GROUP_SELECTED', 'Aucun groupe s&eacute;lectionn&eacute;');
define('_NOW_LOG_OUT', 'Vous &ecirc;tes maintenant d&eacute;connect&eacute;');
define('_DOC_NOT_FOUND', 'Document introuvable');
define('_DOUBLED_DOC', 'Probl&egrave;me de doublons');
define('_NO_DOC_OR_NO_RIGHTS', 'Ce document n&rsquo;existe pas ou vous n&rsquo;avez pas les droits n&eacute;cessaires pour y acc&eacute;der');
define('_INEXPLICABLE_ERROR', 'Une erreur inexplicable est survenue');
define('_TRY_AGAIN_SOON', 'Veuillez r&eacute;essayer dans quelques instants');
define('_NO_OTHER_RECIPIENT', 'Il n&rsquo;y a pas d&rsquo;autre destinataire de ce courrier');
define('_WAITING_INTEGER', 'Entier attendu');

define('_DEFINE', 'Pr&eacute;ciser');
define('_NUM', 'N&deg;');
define('_ROAD', 'Rue');
define('_POSTAL_CODE','Code Postal');
define('_CITY', 'Ville');

define('_CHOOSE_USER', 'Vous devez choisir un utilisateur');
define('_CHOOSE_USER2', 'Choisissez un utilisateur');
define('_NUM2', 'Num&eacute;ro');
define('_UNDEFINED', 'N.C.');
define('_CONSULT_EXTRACTION', 'vous pouvez consulter les documents extraits ici');
define('_SERVICE', 'Service');
define('_AVAILABLE_SERVICES', 'Services disponibles');

// Mois
define('_JANUARY', 'Janvier');
define('_FEBRUARY', 'F&eacute;vrier');
define('_MARCH', 'Mars');
define('_APRIL', 'Avril');
define('_MAY', 'Mai');
define('_JUNE', 'Juin');
define('_JULY', 'Juillet');
define('_AUGUST', 'Ao&ucirc;t');
define('_SEPTEMBER', 'Septembre');
define('_OCTOBER', 'Octobre');
define('_NOVEMBER', 'Novembre');
define('_DECEMBER', 'D&eacute;cembre');

define('_NOW_LOGOUT', 'Vous &ecirc;tes maintenant d&eacute;connect&eacute;.');
define('_LOGOUT', 'D&eacute;connexion');

define('_WELCOME2', 'Bienvenue');
define('_WELCOME_NOTES1', 'Pour naviguer dans l\'application');
define('_WELCOME_NOTES2', 'utilisez le <b>menu</b> ci-dessus');
define('_WELCOME_NOTES3', 'L&rsquo;&eacute;quipe Maarch est tr&egrave;s fi&egrave;re de vous pr&eacute;senter ce nouveau Framework marquant une &eacute;tape importante dans le d&eacute;veloppement de Maarch.<br><br>Dans cette application d&rsquo;exemple, vous pouvez :<ul><li>o Cr&eacute;er des boites d&rsquo;archives afin d&rsquo;y ranger les documents papier num&eacute;ris&eacute;s <b>(module <i> Physical Archive</i>)</b></li><li>o Imprimer des s&eacute;parateurs code-barre <b>(module <i> Physical Archive</i>)</b></li><li>o Indexer de nouveaux documents dans deux collections documentaires distinctes (documents de production et factures client) <b>(module <i> Indexing & Searching</i>)</b></li><li>o Importer en masse des factures clients <b>(utilitaire <i> Maarch AutoImport</i>)</b></li><li>o Consulter les deux fonds documentaires d&rsquo;exemple <b>(module <i> Indexing & Searching</i>)</b></li><li>o Parcourir la collection des factures au travers d&rsquo;arbres dynamiques <b>(module <i> AutoFoldering</i>)</b></li></ul><br><br>');
define('_WELCOME_NOTES5', 'Consultez le <u><a href="http://www.maarch.org/maarch_wiki/Maarch_Framework_v3">wiki maarch</a></u> pour plus d&rsquo;informations.');
define('_WELCOME_NOTES6', 'Acc&eacute;der au <u><a href="http://www.maarch.org/">site communautaire</a></u> ou au <u><a href="http://www.maarch.org/maarch_forum/">forum</a></u> Maarch.');
define('_WELCOME_NOTES7', '<b>Professionnels</b> : des <u><a href="http://www.maarch.fr/">solutions</a></u> adapt&eacute;es &agrave; vos besoins.');
define('_WELCOME_COUNT', 'Nombre de ressources sur la collection');

define('_CONTRACT_HISTORY', 'Historique des contrats');

define('_CLICK_CALENDAR', 'Cliquez pour choisir une date');
define('_MODULES', 'Modules');
define('_CHOOSE_MODULE', 'Choisissez un module');
define('_FOLDER', 'Dossier');
define('_INDEX', 'Index');

//COLLECTIONS
define('_MAILS', 'Courriers');
define('_DOCUMENTS', 'Prets immobiliers');
define('_INVOICES', 'Factures');
define('_CHOOSE_COLLECTION', 'Choisir une collection');

define('_EVENT', 'Ev&egrave;nement');
define('_LINK', 'Lien');


//BITMASK
define('_BITMASK_VALUE_ALREADY_EXIST' , 'Bitmask d&eacute;j&agrave; utilis&eacute; dans un autre type');

define('_ASSISTANT_MODE', 'Mode assisstant');
define('_EDIT_WITH_ASSISTANT', 'Cliquez ici pour &eacute;diter la clause where avec le mode assistant');
define('_VALID_THE_WHERE_CLAUSE', 'Cliquez ici pour VALIDER la clause where');
define('_DELETE_SHORT', 'Suppression');
define('_CHOOSE_ANOTHER_SUBFOLDER', 'Choisissez une autre sous-chemise');
define('_DOCUMENTS_EXISTS_FOR_COLLECTION', 'documents existent pour la collection');
define('_MUST_CHOOSE_COLLECTION_FIRST', 'Vous devez choisir une collection');
define('_CANTCHANGECOLL', 'Vous ne pouvez pas changer la collection');
define('_DOCUMENTS_EXISTS_FOR_COUPLE_FOLDER_TYPE_COLLECTION', 'documents existent pour le couple type de dossier/collection');

define('_NO_RIGHT', 'Erreur');
define('_NO_RIGHT_TXT', 'Vous avez tentez d&rsquo;acc&eacute;der &agrave; un document auquel vous n&rsquo;avez pas droit ou le document n&rsquo;existe pas...');
define('_NUM_GED', 'N&deg; GED');

///// Manage action error
define('_AJAX_PARAM_ERROR', 'Erreur passage param&egrave;tres Ajax');
define('_ACTION_CONFIRM', 'Voulez-vous effectuer l&rsquo;action suivante : ');
define('_ACTION_NOT_IN_DB', 'Action non enregistr&eacute;e en base');
define('_ERROR_PARAM_ACTION', 'Erreur param&egrave;trage de l&rsquo;action');
define('_SQL_ERROR', 'Erreur SQL');
define('_ACTION_DONE', 'Action effectu&eacute;e');
define('_ACTION_PAGE_MISSING', 'Page de r&eacute;sultat de l&rsquo;action manquante');
define('_ERROR_SCRIPT', 'Page de r&eacute;sultat de l&rsquo;action : erreur dans le script ou fonction manquante');
define('_SERVER_ERROR', 'Erreur serveur');
define('_CHOOSE_ONE_DOC', 'Choisissez au moins un document');
define('_CHOOSE_ONE_OBJECT', 'Choisissez au moins un &eacute;l&eacute;ment');

define('_CLICK_LINE_TO_CHECK_INVOICE', 'Cliquer sur une ligne pour v&eacute;rifier une facture');
define('_FOUND_INVOICES', ' facture(s) trouv&eacute;e(s)');
define('_TO_PROCESS', 'Nouvelle facture');
define('_IN_PROGRESS', 'Facture en cours');
define('_SIMPLE_CONFIRM', 'Confirmation simple');
define('_CHECK_INVOICE', 'V&eacute;rifier facture');

define('_REDIRECT_TO', 'Rediriger vers');
define('_NO_STRUCTURE_ATTACHED', 'Ce type de document n&rsquo;est attach&eacute; &agrave; aucune chemise');


///// Credits
define('_MAARCH_CREDITS', 'A propos de Maarch&nbsp;');
define('_CR_LONGTEXT_INFOS', '<p>Maarch Framework 3 est une infrastructure de <b>GED de Production</b>, r&eacute;pondant en standard &agrave; la plupart des besoins de gestion op&eacute;rationnelle de contenu d\'une organisation. La tr&egrave;s grande majorit&eacute; des composants du Framework est diffusé sous les termes de la licence open source GNU GPLv3, de sorte que le coût d\'impl&eacute;mentation rend la solution aborbable pour tout type d\'organisation (public, priv&eacute;, parapublic, monde associatif).</p> <p>Pour autant, Maarch Framework ayant &eacute;t&eacute; conçu par deux consultants cumulant &agrave; eux deux plus de 20 ans d\'expertise en Syst&egrave;mes d\'Archivage &Eacute;lectronique et en &Eacute;ditique, le produit offre <b>toutes les garanties de robustesse, d\'int&eacute;grit&eacute;, de performance</b> que l\'on doit attendre de ce type de produit. Un grand soin a &eacute;t&eacute; port&eacute; sur l\'architecture afin d\'autoriser des performances maximales sur du mat&eacute;riel standard.</p><p>Maarch est d&eacute;velopp&eacute; en PHP5 objet. Il est compatible avec les 4 moteurs de bases de donn&eacute;es suivants&nbsp;: MySQL, PostgreSQL, SQLServer, et bientôt Oracle.</p> <p>Maarch est <b>totalement modulaire</b>&nbsp;: toutes les fonctionnalit&eacute;s sont regroup&eacute;es dans des modules exposant des services qui peuvent être activ&eacute;s/d&eacute;sactiv&eacute;s en fonction du profil de l\'utilisateur. Un ing&eacute;nieur exp&eacute;riment&eacute; peut ajouter ou remplacer un module existant sans toucher au coeur du syst&egrave;me.</p><p>Maarch propose un sch&eacute;ma global et <b>tous les outils pour acqu&eacute;rir, g&eacute;rer, conserver puis restituer les flux documentaires de production</b>.');

define('_CLOSED', 'Valid&eacute;');
define('_PROCESS_SHORT', 'Traitement');
define('_PROCESSING_DATE', 'Date limite de traitement');
define('_PROCESS_NUM','Traitement du courrier n&deg;');
define('_PROCESS_LIMIT_DATE', 'Date limite de traitement');
define('_TO_PROCESS', 'A traiter');
define('_IN_PROGRESS', 'En cours');
define('_LATE_PROCESS', 'En retard');
define('_PROCESS_DELAY', 'D&eacute;lai de traitement');
define('_ALARM1_DELAY', 'D&eacute;lai relance 1');
define('_ALARM2_DELAY', 'D&eacute;lai relance 2');
define('_CATEGORY', 'Cat&eacute;gorie');
define('_CHOOSE_CATEGORY', 'Choisissez une cat&eacute;gorie');
define('_RECEIVING_DATE', 'Date d&rsquo;arriv&eacute;e');
define('_SUBJECT', 'Objet');
define('_AUTHOR', 'Auteur');
define('_DOCTYPE_MAIL', 'Type de courrier');
define('_PROCESS_LIMIT_DATE_USE', 'Activer la date limite');
define('_DEPARTMENT_DEST', 'Service traitant');
define('_DEPARTMENT_EXP', 'Service exp&eacute;diteur');


// Mail Categories
define('_INCOMING', 'Courrier Arriv&eacute;e');
define('_OUTGOING', 'Courrier D&eacute;part');
define('_INTERNAL', 'Courrier Interne');
define('_MARKET_DOCUMENT', 'Document de Sous-dossier');

// Mail Natures
define('_SIMPLE_MAIL', 'Courrier simple');
define('_EMAIL', 'Mail');
define('_FAX', 'Fax');
define('_CHRONOPOST', 'Chronopost');
define('_FEDEX', 'Fedex');
define('_REGISTERED_MAIL', 'Courrier AR');
define('_COURIER', 'Coursier');
define('_OTHER', 'Autre');

//Priorities
define('_NORMAL', 'Normal');
define('_VERY_HIGH', 'Tr&egrave;s urgent');
define('_HIGH', 'Urgent');
define('_LOW', 'Basse');
define('_VERY_LOW', 'Tr&egrave;s Basse');


define('_INDEXING_MLB', 'Enregistrer un courrier');
define('_ADV_SEARCH_MLB', 'Rechercher un courrier');

define('_ADV_SEARCH_TITLE', 'Recherche avanc&eacute;e du courrier');
define('_MAIL_OBJECT', 'Objet du courrier');
//define('_SHIPPER', 'Emetteur');
//define('_SENDER', 'Exp&eacute;diteur');
//define('_SOCIETY', 'Soci&eacute;t&eacute;');
//define('_SHIPPER_SEARCH','Dans le champ &eacute;metteur, les recherches ne sont effectu&eacute;es ni sur les civilit&eacute;s, ni sur les pr&eacute;noms.');
//define('_MAIL_IDENTIFIER','R&eacute;f&eacute;rence de l&rsquo;affaire');
define('_N_GED','Num&eacute;ro GED ');
define('_GED_NUM', 'N&deg; GED');
define('_CHOOSE_TYPE_MAIL','Choisissez un type de courrier');
//define('_INVOICE_TYPE','Nature de l&rsquo;envoi');
//define('_CHOOSE_INVOICE_TYPE','Choisissez la nature de l&rsquo;envoi');
define('_REG_DATE','Date d&rsquo;enregistrement');
define('_PROCESS_DATE','Date de traitement');
define('_CHOOSE_STATUS','Choisissez un statut');
define('_PROCESS_RECEIPT','Destinataire(s) pour traitement');
define('_CHOOSE_RECEIPT','Choisissez un destinataire');
define('_TO_CC','En copie');
define('_ADD_COPIES','Ajouter des personnes en copie');
//define('_ANSWER_TYPE','Type(s) de r&eacute;ponse');
define('_PROCESS_NOTES','Notes de traitement');
define('_DIRECT_CONTACT','Prise de contact direct');
define('_NO_ANSWER','Pas de r&eacute;ponse');
define('_DETAILS', 'Fiche d&eacute;taill&eacute;e');
define('_DOWNLOAD', 'T&eacute;l&eacute;charger le courrier');
define('_SEARCH_RESULTS', 'R&eacute;sultat de la recherche');
define('_DOCUMENTS', 'documents');
define('_THE_SEARCH', 'La recherche');
define('_CHOOSE_TABLE', 'Choisissez une collection');
define('_SEARCH_COPY_MAIL','Chercher dans mes courriers en copie');
define('_MAIL_PRIORITY', 'Priorit&eacute; du courrier');
define('_CHOOSE_PRIORITY', 'Choisissez une priorit&eacute;');
define('_ADD_PARAMETERS', 'Ajouter des crit&egrave;res');
define('_CHOOSE_PARAMETERS', 'Choisissez vos crit&egrave;res');
define('_CHOOSE_ENTITES_SEARCH_TITLE', 'Ajoutez le/les service(s) d&eacute;sir&eacute;(s) pour restreindre la recherche');
define('_CHOOSE_DOCTYPES_SEARCH_TITLE', 'Ajoutez le(s) type(s) de document d&eacute;sir&eacute;(s) pour restreindre la recherche');
define('_DESTINATION_SEARCH', 'Service(s) affect&eacute;(s)');
define('_ADD_PARAMETERS_HELP', 'Pour affiner le r&eacute;sultat, vous pouvez ajouter des crit&egrave;res &agrave; votre recherche... ');
define('_MAIL_OBJECT_HELP', 'Saisissez les mots cl&eacute;s de l&rsquo;objet du courrier... ');
define('_N_GED_HELP', '');
define('_CHOOSE_RECIPIENT_SEARCH_TITLE', 'Ajoutez le/les destinataire(s) d&eacute;sir&eacute;(s) pour restreindre la recherche');
define('_MULTI_FIELD','Multi-champs');
define('_MULTI_FIELD_HELP','Objet, description, titre, Num chrono, notes de traitement...');
define('_SAVE_QUERY', 'Enregistrer ma recherche');
define('_SAVE_QUERY_TITLE', 'Enregistrement de recherche');
define('_QUERY_NAME', 'Nom de ma recherche');
define('_QUERY_SAVED', 'Recherche sauvegard&eacute;e');
define('_SERVER_ERROR', 'Erreur serveur lors de l&acute;enregistrement de la recherche');
//define('_SQL_ERROR', 'Erreur SQL lors de l&acute;enregistrement de la recherche');
define('_LOAD_QUERY', 'Charger la recherche');
define('_DELETE_QUERY', 'Supprimer la recherche');
define('_CHOOSE_SEARCH', 'Choisir une recherche');
define('_THIS_SEARCH', 'cette recherche');
define('_MY_SEARCHES', 'Mes recherches');
define('_CLEAR_SEARCH', 'Effacer les crit&egrave;res');
define('_CHOOSE_STATUS_SEARCH_TITLE', 'Ajoutez le/les statut(s) d&eacute;sir&eacute;(s) pour restreindre la recherche');
define('_ERROR_IE_SEARCH', 'Cet &eacute;l&eacute;ment est d&eacute;j&agrave; d&eacute;fini !');
//define('_CIVILITIES', 'Civilit&eacute;(s)');
//define('_CIVILITY', 'Civilit&eacute;');
//define('_CHOOSE_CIVILITY_SEARCH_TITLE', 'Ajoutez le/les civilit&eacute;(s) d&eacute;sir&eacute;(s) pour restreindre la recherche');

define('_DEST_USER','Destinataire');
define('_DOCTYPES','Type(s) de document');
define('_MAIL_NATURE', 'Nature de l&rsquo;envoi');
define('_CHOOSE_MAIL_NATURE', 'Choisissez la nature de l&rsquo;envoi');
define('_ERROR_DOCTYPE', 'Type de document non valide');
define('_ADMISSION_DATE', 'Date d&rsquo;arriv&eacute;e');
define('_FOUND_DOC', 'document(s) trouv&eacute;(s)');
define('_PROCESS', 'Traitement ');
define('_DOC_NUM', 'document n&deg; ');
define('_GENERAL_INFO', 'Informations g&eacute;n&eacute;rales');
define('_ON_DOC_NUM', ' sur le document n&deg;');
define('_PRIORITY', 'Priorit&eacute;');
define('_MAIL_DATE', 'Date du courrier');
define('_DOC_HISTORY', 'Historique');
define('_DONE_ANSWERS','R&eacute;ponses effectu&eacute;es');
define('_MUST_DEFINE_ANSWER_TYPE', 'Vous devez pr&eacute;ciser le type de r&eacute;ponse');
define('_MUST_CHECK_ONE_BOX', 'Vous devez cocher au moins une case');
define('_ANSWER_TYPE','Type(s) de r&eacute;ponse');

define('_INDEXATION_TITLE', 'Indexation d&rsquo;un document');
define('_CHOOSE_FILE', 'Choisissez le fichier');
define('_CHOOSE_TYPE', 'Choisissez un type');

define('_FILE_LOADED_BUT_NOT_VISIBLE', 'Le fichier est charg&eacute; et pr&ecirc;t &agrave; &ecirc;tre enregistr&eacute; sur le serveur.<br/>');
define('_ONLY_FILETYPES_AUTHORISED', 'Seuls les fichiers suivants peuvent &ecirc;tre affich&eacute;s dans cette fen&ecirc;tre');
define('_PROBLEM_LOADING_FILE_TMP_DIR', 'Probl&egrave;me lors du chargement du fichier sur le r&eacute;pertoire temporaire du serveur');
define('_DOWNLOADED_FILE', 'Fichier charg&eacute;');
define('_WRONG_FILE_TYPE', 'Ce type de fichier n&rsquo;est pas permis');

define('_LETTERBOX', 'Gestion de courrier');
define('_APA_COLL', 'APA - ne pas utiliser');
define('_REDIRECT_TO_ACTION', 'Rediriger vers une action');
define('_DOCUMENTS_LIST', 'Liste');


/********* Contacts ************/
define('_ADMIN_CONTACTS', 'Contacts');
define('_ADMIN_CONTACTS_DESC', 'Administration des contacts');
define('_CONTACTS_LIST', 'Liste des contacts');
define('_CONTACT_ADDITION', 'Ajouter contact');
define('_CONTACTS', 'contact(s)');
define('_CONTACT', 'Contact');
define('_ALL_CONTACTS', 'Tous les contacts');
define('_ADD_CONTACT', 'Ajout d&rsquo;un contact');
define('_PHONE', 'T&eacute;l&eacute;phone');
define('_ADDRESS', 'Adresse');
define('_STREET', 'Rue');
define('_COMPLEMENT', 'Compl&eacute;ment');
define('_TOWN', 'Ville');
define('_COUNTRY', 'Pays');
define('_SOCIETY', 'Soci&eacute;t&eacute;');
define('_COMP', 'Autres');
define('_COMP_DATA', 'Informations compl&eacute;mentaires');
define('_CONTACT_ADDED', 'Contact ajout&eacute;');
define('_CONTACT_MODIFIED', 'Contact modifi&eacute;');
define('_CONTACT_DELETED', 'Contact supprim&eacute;');
define('_MODIFY_CONTACT', 'Modifier un contact');
define('_IS_CORPORATE_PERSON', 'Personne morale');
define('_TITLE2', 'Civilit&eacute;');
define('_TITLE2', 'Civilit&eacute;');
define('_YOU_MUST_SELECT_CONTACT', 'Vous devez s&eacute;lectionner un contact ');
define('_CONTACT_INFO', 'Fiche contact');

define('_SHIPPER', 'Exp&eacute;diteur');
define('_DEST', 'Destinataire');
define('_INTERNAL2', 'Interne');
define('_EXTERNAL', 'Externe');
define('_CHOOSE_SHIPPER', 'Choisir un exp&eacute;diteur');
define('_CHOOSE_DEST', 'Choisir un destainataire');
define('_DOC_DATE', 'Date du document');
define('_CONTACT_CARD', 'Fiche contact');
define('_CREATE_CONTACT', 'Ajouter un contact');
define('_USE_AUTOCOMPLETION', 'Utiliser l&rsquo;autocompl&eacute;tion');

define('_USER_DATA', 'Fiche utilisateur');
define('_SHIPPER_TYPE', 'Type d&rsquo;exp&eacute;diteur');
define('_DEST_TYPE', 'Type de destinataire');
define('_VALIDATE_MAIL', 'Validation courrier');
define('_LETTER_INFO','Informations sur le courrier');
define('_DATE_START','Date d&rsquo;arriv&eacute;e');
define('_LIMIT_DATE_PROCESS','Date limite de traitement');


//// INDEXING SEARCHING
define('_NO_RESULTS', 'Aucun r&eacute;sultat');
define('_FOUND_DOCS', 'document(s) trouv&eacute;(s)');
define('_MY_CONTACTS', 'Mes contacts');
define('_DETAILLED_PROPERTIES', 'Propri&eacute;t&eacute;s d&eacute;taill&eacute;es');
define('_VIEW_DOC_NUM', 'Visualisation du document n&deg;');
define('_TO', 'vers');
define('_FILE_PROPERTIES', 'Propri&eacute;t&eacute;s du fichier');
define('_FILE_DATA', 'Informations sur le document');
define('_VIEW_DOC', 'Voir le document');
define('_TYPIST', 'Op&eacute;rateur');
define('_LOT', 'Lot');
define('_ARBOX', 'Boite');
define('_ARBOXES', 'Boites');
define('_ARBATCHES', 'Lot');
define('_CHOOSE_BOXES_SEARCH_TITLE', 'S&eacute;lectionnez la ou les boites pour restreindre la recherche');
define('_PAGECOUNT', 'Nb pages');
define('_ISPAPER', 'Papier');
define('_SCANDATE', 'Date de num&eacute;risation');
define('_SCANUSER', 'Utilisateur du scanner');
define('_SCANLOCATION', 'Lieu de num&eacute;risation');
define('_SCANWKSATION', 'Station de num&eacute;risation');
define('_SCANBATCH', 'Lot de num&eacute;risation');
define('_SOURCE', 'Source');
define('_DOCLANGUAGE', 'Langage du document');
define('_MAILDATE', 'Date du courrier');
define('_MD5', 'Empreinte MD5');
define('_WORK_BATCH', 'Lot de chargement');
define('_DONE','Actions effectu&eacute;es');
define('_ANSWER_TYPES_DONE', 'Type(s) de r&eacute;ponses effectu&eacute;es');
define('_CLOSING_DATE', 'Date de cl&ocirc;ture');
define('_FULLTEXT', 'Plein texte');
define('_FULLTEXT_HELP', 'Recherche plein texte avec le moteur Luc&egrave;ne...');
define('_FILE_NOT_SEND', 'Le fichier n&rsquo;a pas &eacute;t&eacute; envoy&eacute;');
define('_TRY_AGAIN', 'Veuillez r&eacute;essayer');
define('_DOCTYPE_MANDATORY', 'Le type de pi&egrave;ce est obligatoire');
define('_INDEX_UPDATED', 'Index mis &agrave; jour');

define('_QUICKLAUNCH', 'Raccourcis');
define('_SHOW_DETAILS_DOC', 'Voir les d&eacute;tails du document');
define('_VIEW_DOC_FULL', 'Voir le document');
define('_DETAILS_DOC_FULL', 'Voir la fiche du document');
define('_IDENTIFIER', 'R&eacute;f&eacute;rence');
define('_CHRONO_NUMBER', 'Num&eacute;ro chrono');
define('_NO_CHRONO_NUMBER_DEFINED', 'Le num&eacute;ro chrono n\'est pas d&eacute;fini');
define('_FOR_CONTACT_C', 'Pour');
define('_TO_CONTACT_C', 'De');

define('_APPS_COMMENT', 'Application Maarch Entreprise');
define('_CORE_COMMENT', 'Coeur du Framework');
define('_CLEAR_FORM', 'Effacer le formulaire');

define('_MAX_SIZE_UPLOAD_REACHED', 'Taille maximum de fichier d&eacute;pass&eacute;e');
define('_NOT_ALLOWED', 'interdit');
define('_CHOOSE_TITLE', 'Choisissez une civilit&eacute;');

/////////////////// Reports
define('_USERS_LOGS', 'Liste des acc&egrave;s &agrave; l&rsquo;application par agent');
define('_USERS_LOGS_DESC', 'Liste des acc&egrave;s &agrave; l&rsquo;application par agent');
define('_PROCESS_DELAY_REPORT', 'D&eacute;lai moyen de traitement par type de courrier');
define('_PROCESS_DELAY_REPORT_DESC', 'D&eacute;lai moyen de traitement par type de courrier');
define('_MAIL_TYPOLOGY_REPORT', 'Typologie des courriers par p&eacute;riode');
define('_MAIL_TYPOLOGY_REPORT_DESC', 'Typologie des courriers par p&eacute;riode');
define('_MAIL_VOL_BY_CAT_REPORT', 'Volume de courriers par cat&eacute;gorie par p&eacute;riode');
define('_MAIL_VOL_BY_CAT_REPORT_DESC', 'Volume de courriers par cat&eacute;gorie par p&eacute;riode');
define('_SHOW_FORM_RESULT', 'Afficher le r&eacute;sultat sous forme de ');
define('_GRAPH', 'Graphique');
define('_ARRAY', 'Tableau');
define('_SHOW_YEAR_GRAPH', 'Afficher le r&eacute;sultat pour l&rsquo;ann&eacute;e');
define('_SHOW_GRAPH_MONTH', 'Afficher le r&eacute;sultat pour le mois de');
define('_OF_THIS_YEAR', 'de cette ann&eacute;e');
define('_NB_MAILS1', 'Nombre de courriers enregistr&eacute;s');
define('_FOR_YEAR', 'pour l&rsquo;ann&eacute;e');
define('_FOR_MONTH', 'pour le mois de');
define('_N_DAYS','NB JOURS');

/******************** Specific DGGT ************/
define('_PROJECT', 'Dossier');
define('_MARKET', 'Sous-dossier');
define('_SEARCH_CUSTOMER', 'Consultation Dossiers Sous-dossiers');
define('_SEARCH_CUSTOMER_TITLE', 'Recherche Dossiers Sous-dossiers');
define('_TO_SEARCH_DEFINE_A_SEARCH_ADV', 'Pour lancer une recherche vous devez saisir un n&deg; de dossier ou un nom de Dossier ou de Sous-dossier');
define('_DAYS', 'jours');
define('_LAST_DAY', 'Dernier jour');



/******************** Keywords Helper ************/
define('_HELP_KEYWORD0', 'id de l&rsquo;utilisateur connect&eacute;');
define('_HELP_BY_CORE', 'Mots cl&eacute;s de Maarch Core');

define('_FIRSTNAME_UPPERCASE', 'PRENOM');
define('_TITLE_STATS_USER_LOG', 'Acc&egrave;s &agrave; l&rsquo;application');

define('_DELETE_DOC', 'Supprimer ce document');
define('_THIS_DOC', 'ce document');
define('_MODIFY_DOC', 'Modifier des informations');
define('_BACK_TO_WELCOME', 'Retourner &agrave; la page d&rsquo;accueil');
define('_CLOSE_MAIL', 'Cl&ocirc;turer un courrier');

/************** R&eacute;ouverture courrier **************/
define('_MAIL_SENTENCE2', 'En saisissant le n&deg;GED du document, vous passerez le  statut de ce dernier &agrave; &quot;En cours&quot;.');
define('_MAIL_SENTENCE3', 'Cette fonction a pour but d&rsquo;ouvrir un courrier ferm&eacute; pr&eacute;matur&eacute;ment.');
define('_ENTER_DOC_ID', 'Saisissez l&rsquo;identifiant du document');
define('_TO_KNOW_ID', 'Pour conna&icirc;tre l&rsquo;identifiant du document, effectuez une recherche ou demandez-le &agrave; l&rsquo;op&eacute;rateur');
define('_MODIFY_STATUS', 'Modifier le statut');
define('_REOPEN_MAIL', 'R&eacute;ouverture de courrier');
define('_REOPEN_THIS_MAIL', 'R&eacute;ouverture du courrier');

define('_OWNER', 'Propri&eacute;taire');
define('_CONTACT_OWNER_COMMENT', 'Laisser vide pour rendre ce contact public.');

define('_OPT_INDEXES', 'Informations compl&eacute;mentaires');
define('_NUM_BETWEEN', 'Compris entre');
define('_MUST_CORRECT_ERRORS', 'Vous devez corriger les erreurs suivantes ');
define('_CLICK_HERE_TO_CORRECT', 'Cliquez ici pour les corriger');

define('_FILETYPE', 'Type de fichier');
define('_WARNING', 'Attention ');
define('_STRING', 'Chaine de caract&egrave;res');
define('_INTEGER', 'Entier');
define('_FLOAT', 'Flottant');
define('_CUSTOM_T1', 'Champ Texte 1');
define('_CUSTOM_T2', 'Champ Texte 2');
define('_CUSTOM_D1', 'Champ Date');
define('_CUSTOM_N1', 'Champ Entier');
define('_CUSTOM_F1', 'Champ Flottant');
?>

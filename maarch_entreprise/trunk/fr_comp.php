<?php 
/*******************************Statut courrier*********************************/
define('_TO_PROCESS','A traiter');
define('_IN_PROGRESS','En cours');
define('_FIRST_WARNING','1ere Relance');
define('_SECOND_WARNING','2e Relance (Retard)');
define('_CLOSED','Clos');
define('_NEW','Nouveaux');
define('_LATE', 'En retard');

define('_QUICKSEARCH', 'Recherche rapide');
define('_MANAGE_DEPARTMENTS', 'G&eacute;rer les services');
define('_REOPEN_MAIL', 'R&eacute;ouverture de courrier');
define('_MAIL_TO_PROCESS', 'Courrier &agrave; traiter');
define('_MAIL_TO_VALIDATE', 'Courrier &agrave; valider');
define('_QUICK_GUIDE', 'Guide rapide d&acute;utilisation');
define('_ONLINE_REGISTER', 'Enregistrement en ligne');
define('_SEARCH_UPDATES', 'Rechercher des mises &agrave; jour');
define('_CONTACT_US', 'Contactez-nous');
define('_MAARCH_INTEGRATION', 'Maarch&trade; et l&acute;int&eacute;gration');

/**************Mise à jour message**************/
//Permet de mettre à jour le message d'accueil 
define('_UPDATE_MSG_TITLE', 'Modification du message d&acute;accueil de l&acute;administrateur');
define('_UPDATE_MSG_LINE01','Modifier le formulaire ci-dessous pour mettre &agrave; jour le message d&acute;accueil de l&acute;application.');
define('_UPDATE_MSG_LINE02','Vous pouvez integrer &agrave; ce message des balises HTML pour mettre en forme le message.');

define('_MANAGE_DEPARTMENT_LIST', 'G&eacute;rer la liste des services')
define('_REOPEN_MAIl_EXPLANATION', 'Modifier le statut d&acute;un courrier en cas de cl&ocirc;ture trop rapide du traitement d&acute;un courrier');
;

define('_DEPARTMENTS_LIST', 'Liste des services');
define('_SELECTED_DEPARTMENTS', 'Services s&eacute;lectionn&eacute;s');

define('_ELECTED', 'Responsable');
define('_AGENT', 'Agent');
define('_CHOOSE_DEPARTMENT', 'Choisissez un service');

define('_PROCESS_DELAY', 'D&eacute;lai de traitement');
define('_FIRST_WARNING_DELAY', 'D&eacute;lai premi&egrave;re relance');
define('_SECOND_WARNING_DELAY', 'D&eacute;lai deuxi&egrave;me relance');
define('_LINKED_DIFF_LIST', 'Liste de diffusion associ&eacute;e');
define('_NO_LINKED_DIFF_LIST', 'Pas de liste associ&eacute;e');
define('_CREATE_LIST', 'Cr&eacute;er une liste de diffusion');
define('_MODIFY_LIST', 'Modifier la liste');

/* Pop up creation / modification mod�le liste */
define('_MANAGE_MODEL_LIST_TITLE', 'Cr&eacute;ation / Modification Mod&egrave;le de liste de diffusion');
define('_SORT_BY', 'Trier par');
define('_WELCOME_MODEL_LIST_TITLE', 'Bienvenue dans l&acute;outil de cr&eacute;tion de mod&egrave;le de liste de diffusion');
define('_MODEL_LIST_EXPLANATION1', 'Pour d&eacute;marrer la cr&eacute;tion, utilisez la navigation par service ou par utilisateur ci-dessus');


/************** Services : Liste + pop up **************/
define('_ADD_DEPARTMENT', 'Ajouter un service');
define('_ALL_DEPARTMENTS', 'Tous les services');

define('_DEPARTMENT_ADDITION', 'Ajout d&acute;un service');
define('_CREATE_MODIFY_DEPARTMENT', 'Cr&eacute;ation / Modification d&acute;un service');
define('_DEPARTMENT_DELETION', 'Suppression d&acute;un service');
define('_MODIFY_DEPARTMENT', 'Valider les changements');
define('_THERE_ARE_NOW', 'Il y a actuellement');
define('_DOC_IN_THE_DEPARTMENT', 'documents attach&eacute;s &agrave; ce service');
define('_TO_DEL_DEPARTMENT', 'Pour supprimer ce service, vous devez d&acute;abord r&eacute;affecter ces documents &agrave; un service existant');
define('_REALLY_DEL_DEPARTMENT', 'Voulez vous vraiment supprimer ce service ?');
define('_DEL_AND_REAFFECT', 'Supprimer et r&eacute;affecter les documents');

/************** R�ouverture courrier **************/
define('_REOPEN_THIS_MAIL', 'R&eacute;ouverture du courrier');
define('_MAIL_SENTENCE1', 'Cette page permet &agrave; l&acute;administrateur de Maarch LetterBox de corriger une erreur utilisateur.');
define('_MAIL_SENTENCE2', 'En saisissant le n&deg;GED du document, vous passerez le  statut de ce dernier &agrave; &quot;En cours&quot;.');
define('_MAIL_SENTENCE3', 'Cette fonction a pour but d&rsquo;ouvrir  &agrave; nouveau un courrier ferm&eacute; pr&eacute;matur&eacute;ment.');
define('_ENTER_DOC_ID', 'Saisissez l&acute;identifiant du document');
define('_TO_KNOW_ID', 'Pour conna&icirc;tre l&acute;identifiant du document, effectuez une recherche ou demandez-le &agrave; l&acute;op&eacute;rateur');
define('_MODIFY_STATUS', 'Modifier le statut');



//liste de diffusion
define('_CHOOSE_DEPARTMENT_FIRST', 'Vous devez d&acute;abord choisir un service avant de pouvoir acc&eacute;der &agrave; la liste diffusion');
define('_NO_LIST_DEFINED__FOR_THIS_MAIL', 'Aucune liste n&acute;est d&eacute;finie pour ce courrier');
define('_NO_LIST_DEFINED__FOR_THIS_DEPARTMENT', 'Aucune liste n&acute;est d&eacute;finie pour ce service');
define('_NO_LIST_DEFINED', 'Pas de liste d&eacute;finie');

define('_WELCOME_DIFF_LIST', 'Bienvenue dans l&acute;outil de diffusion de courrier');
define('_START_DIFF_EXPLANATION', 'Pour demarrer la diffusion, utilisez la navigation par service ou par utilisateur ci-dessus');
define('_CLICK_ON', 'cliquez sur');
define('_ADD_USER_TO_LIST_EXPLANATION', 'Pour ajouter un utilisateur &agrave; la liste de diffusion');
define('_REMOVE_USER_FROM_LIST_EXPLANATION', 'Pour retirer l&acute;utilisateur &agrave; cette liste de diffusion');
define('_TO_MODIFY_LIST_ORDER_EXPLANATION', 'Pour modifier l&acute;ordre d&acute;attribution d&acute;un courrier aux utilisateurs, utilisez les ic&ocirc;nes');


/************************* Validation ***********************************/
define('_MAIL_TO_VALIDATE', 'Courrier &agrave; valider');
define('_CLICK_LINE_VALID', 'Cliquer sur une ligne pour valider le document');
define('_CLICK_LINE_PROCESS', 'Cliquer sur une ligne pour traiter le document');
define('_MAIL_VALIDATION', 'Validation courrier');
define('_MAIL_VALIDATE', 'Valider courrier');

/************************* Gestion des abscences ***********************************/

define('_MISSING_ADVERT_TITLE','Gestion des absences');
define('_MISSING_ADVERT_01','Ce compte est actuellement d&eacute;finit en mode &acute;absent&acute; et les courriers sont redirig&eacute;s vers un autre utilisateur.');
define('_MISSING_ADVERT_02','Si vous desirez vous connecter avec ce compte, le mode &acute;absent&acute; sera alors supprim&eacute;.<br/> La redirection des courriers arrivera &agrave; son terme et l&acute;application sera r&eacute;activ&eacute;e');
define('_MISSING_CHOOSE','Souhaitez-vous continuer?');

/************************** Traitement *********************************************/
define('_TITLE_PROCESS','Page de traitement du courrier');
define('_N_PROCESS_LETTER','Traitement du courrier n&deg;');
define('_GENERAL_INFO_LETTER','Informations g&eacute;n&eacute;rales sur le courrier');
define('_SHOW_LETTER_RECEIVER_LIST','Voir la liste des destinataires de ce courrier');
define('_ANSWERS_CREATED','R&eacute;ponses effectu&eacute;es');
define('_SIMPLE_MAIL','Par courrier simple');
define('_MORE_INFORMATIONS','Informations compl&eacute;mentaires');
define('_QUALITY','Qualit&eacute');
define('_ADDRESS','Adresse');
define('_DISTRICTS','Quartier');
define('_CHOOSE_DISTRICT','Choisissez un quartier');
define('_ACTIONS','Actions');
define('_LETTER_SERVICE_REDIRECT','Rediriger vers le service courrier');
define('_LETTER_SERVICE_REDIRECT_VALIDATION','Souhaitez-vous vraiment rediriger vers le service courrier');
define('_IF_REDIRECT_YOU_LOOSE_RIGHT','En redirigeant le courrier, vous perdrez les droits d&acute;acc&egrave;s sur ce courrier');
define('_MAIL_TO_AFFECT','Affecter &agrave; un agent du service');
define('_REDIRECT_TO_ANOTHER','Rediriger vers une autre personne');
define('_ACCEPT_UPDATE','Valider modification');
define('_ACCEPT_UPDATE_TEXT','Enregistre les modifications effectu&eacute;es sur le courrier actuel. Si vous choisissez cette option, vous pourrez &agrave; nouveau modifier le traitement, mais le calcul du d&eacute;lai de traitement n&acute;est pas arr&ecirc;t&eacute;!');
define('_CLOSE_LETTER','Cl&ocirc;turer  le dossier');
define('_CLOSE_LETTER_TEXT','Enregistre les modifications et cl&ocirc;ture de courrier. En cl&ocirc;turant le courrier, vous perdrez les droits d&acute;acc&egrave;s sur ce dernier.');
define('_CANCEL_TEXT','Ferme la fen&ecirc;tre actuelle sans enregistrer les modifications.');
define('_CC_LIST', 'Liste des destinataires en copie');

define('_ANSWER_JOINED','R&eacute;ponses attach&eacute;es');
define('_ATTACH_ANSWER','Attacher une r&eacute;ponse');

define('_GENERATE_ANSWER','G&eacute;n&eacute;rer une r&eacute;ponse');
define('_DELETE_ANSWER','Supprimer une r&eacute;ponse');

define('_PLEASE_SELECT_FILE', 'Veuillez s&eacute;lectionner le document &agrave; attacher');
define('_PLEASE_SELECT_MODEL', 'Veuillez s&eacute;lectionner un mod&egrave;le de r&eacute;ponse');
define('_NEW_ANSWER_ADDED', 'R&eacute;ponse ajout&eacute;e pour le courrier');
define('_ANSWER_UPDATED', 'R&eacute;ponse modifi&eacute;e pour le courrier');
define('_REDIRECT_TO', 'Rediriger vers');
define('_CHOOSE_PERSON_TO_REDIRECT', 'Choisissez la personne vers qui vous souhaitez rediriger ce courrier dans la liste ci-dessus');
define('_CLICK_ON_THE_LINE_OR_ICON', 'Il vous suffit de cliquer sur la ligne ou sur l&acute;ic&ocirc;ne');
define('_TO_SELECT_USER', 'pour s&eacute;lectionner un utilisateur');

define('_MAIL_PROCESS_END_NUM', 'Cl&ocirc;ture du traitement du courrier n&deg;');
define('_REDIRECTED_TO_GENERAL', ' redirig&eacute; vers le secr&eacute;tariat g&eacute;n&eacute;ral');


//del_doc_popup
define('_DEL_MAIl_NUM', 'Suppression de(s) courrier(s) n&deg;');
define('_THIS_MAIL', 'ce(s) courrier(s)');
define('_NO_DEL_RIGHT', 'Vous n&acute;avez pas le droit de suppression dans cette corbeille.');

define('_MY_ABS', 'G&eacute;rer mes absences');
define('_MY_ABS_TXT', 'Permet de rediriger votre courrier en attente en cas de d&eacute;part en cong&eacute;.');
define('_MY_ABS_REDIRECT', 'Vos courriers sont actuellement redirig&eacute;s vers');
define('_MY_ABS_DEL', 'Pour supprimer la redirection, cliquez ici pour stopper');
define('_ADMIN_ABS', 'G&eacute;rer les absences.');
define('_ADMIN_ABS_TXT', 'Permet de rediriger le courrier de l&acute;utilisateur en attente en cas de d&eacute;part en cong&eacute;.');
define('_ADMIN_ABS_REDIRECT', 'Redirection d&acute;absence en cours.');
define('_ADMIN_ABS_FIRST_PART', 'Les courrier de');
define('_ADMIN_ABS_SECOND_PART', 'sont actuellement redirig&eacute;s vers ');
define('_ADMIN_ABS_THIRD_PART', '. Cliquez ici pour supprimer la redirection.');
define('_ACTIONS_DONE', 'Actions effectu&eacute;es le');
define('_PROCESSED_MAIL', 'Courriers trait&eacute;s');
define('_INDEXED_MAIL', 'Courriers index&eacute;s');
define('_REDIRECTED_MAIL', 'Courriers redirig&eacute;s');
define('_PROCESS_MAIL_OF', 'Courrier &agrave; traiter de');
define('_MISSING', 'Absent');

define('_THE_PROCESS_LIMIT', 'La date limite de traitement ');
define('_THE_FIRST_WARNING', 'Le d&eacute;lai de premi&egrave;re relance ');
define('_THE_SECOND_WARNING', 'Le d&eacute;lai de deuxi&eagrave;me relance ');


//services : liste_modif.php + del_service.php
define('_DEPARTMENT_ADDED', 'Nouveau service cr&eacute;e');
define('_DEPARTMENT_MODIF', 'Modification du service');
define('_DEPARTMENT_DESC_MISSING', 'Il manque la description du service');
define('_DEPARTMENT_ID_MISSING', 'Il manque l&acute;identifiant du service');
define('_CANT_DEL_DEPARTMENT', 'Vous ne pouvez pas supprimer ce service si vous ne r&eacute;affectez pas les courriers !');
define('_THE_DEPARTMENT', 'Le d&eacute;partement ');


//admin_status_up.php
define('_MAIL_MODIF_OK', 'La modification a &eacute;t&eacute; effectu&eacute;e avec succ&egrave;s');
define('_MAIL_MODIF_NOT_OK', 'Erreur lors de la modification, veuillez v&eacute;rifier l&acute;identifiant du document.');


//Gestion des r�ponses lors du traitement ( generate_answer.php et manage_generate_answer.php)
define('_NO_MODE_DEFINED', 'Erreur : mode absent');
define('_MODEL_OR_ANSWER_ERROR', 'Erreur : probl&egrave;me au chargement du mod&egrave;le ou de la r&eacute;ponse');
define('_NO_CONTENT', 'Erreur : Contenu de la r&eacute;ponse vide');
define('_FILE_OPEN_ERROR', 'Ouverture fichier impossible');
define('_ANSWER_OPEN_ERROR', 'Erreur : probl&egrave;me &agrave; l&acute;ouverture de la r&eacute;ponse');
define('_MAIL_SEND', 'Mail envoy&eacute;');
define('_RECEPT_MAIL', 'Mail destinataire');


define('_MAIL_STATUS', 'Statut des courriers');
define('_ANSWER_TITLE', 'Titre de la r&eacute;ponse');
define('_INGOING', 'Entrant');
define('_ONGOING', 'Sortant');
define('_SELECT_ONE_MAIL', 'Vous devez s&eacute;lectionner au moins un courrier');
define('_SELECT_ACTION', 'Vous devez s&eacute;lectionner une action');
define('_ANSWER_DELETED', 'R&eacute;ponse supprim&eacute;e pour le courrier');
define('_MODIFY_ANSWER','Modifier la r&eacute;ponse');

define('_REDIRECT_TO_DEP', 'Redirection vers le service');
define('_CHOOSE_DEP', 'Vous devez choisir un service');
define('_REDIRECT_TO', 'Redirection vers');
define('_REDIRECT_TO_OTHER_DEP', 'Rediriger vers un autre service');
define('_REDIRECT_TO_USER', 'Rediriger vers un utilisateur');
define('_REDIRECT_MAIL', 'Redirection de(s) courrier(s) n&deg;');
define('_REDIRECT_MAIL_OF', 'Redirection des courriers de');
define('_MAIL_ID_UNDEFINED', 'L&acute;identifiant du courrier n&acute;est pas d&eacute;fini');
define('_SHIPPER_DATA', 'Informations sur l&acute;&eacute;metteur du courrier n&deg;');


?>
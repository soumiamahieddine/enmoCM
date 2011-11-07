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

//communs
if (!defined('_NOTIFICATIONS')) define('_NOTIFICATIONS', 'Notifications');
if (!defined('_MAIL_TO_PROCESS')) define('_MAIL_TO_PROCESS', 'Courriers pour traitement');
if (!defined('_HELLO')) define('_HELLO','Bonjour');
if (!defined('_THE_MAIL_NUM')) define('_THE_MAIL_NUM', 'le courrier n&deg;');
if (!defined('_OF_TYPE')) define('_OF_TYPE', 'de type');
if (!defined('_OBJECT')) define('_OBJECT', 'objet');
if (!defined('_RECEIVED_THE')) define('_RECEIVED_THE', 're&ccedil;u le');
if (!defined('_SEND_BY')) define('_SEND_BY', 'adress&eacute; par');
if (!defined('_MUST_BE_PROCESSED_BEFORE')) define('_MUST_BE_PROCESSED_BEFORE', 'est &agrave; traiter avant le');
if (!defined('_ACCESS_TO_MAIL_TO_PROCESS')) define('_ACCESS_TO_MAIL_TO_PROCESS', 'Acc&eacute;der au courrier pour le traiter');
if (!defined('_PROCESS_MAIL')) define('_PROCESS_MAIL', 'Traitement courrier');
if (!defined('_USER')) define('_USER', 'Utilisateur');
if (!defined('_MAIL')) define('_MAIL', 'Courrier');
if (!defined('_WAS_SENT_TO')) define('_WAS_SENT_TO', 'a &eacute;t&eacute; transmis &agrave;');
if (!defined('_SEE_MAIL')) define('_SEE_MAIL', 'Voir ce courrier');
if (!defined('_NO_SENDED')) define('_NO_SENDED',' L&rsquo;email n\'a pas  &eacute;t&eacute; envoy&eacute;');
// notifs.php
if (!defined('_MUST_SPECIFY_CONFIG_FILE')) define('_MUST_SPECIFY_CONFIG_FILE', 'Vous devez sp&eacute;cifier le fichier de configuration');
if (!defined('_DU')) define('_DU', 'du');
if (!defined('_OF')) define('_OF', 'de');
if (!defined('_TO')) define('_TO', 'de la part de ');
if (!defined('_FOR')) define('_FOR', 'd&eacute;stin&eacute; &agrave;');
if (!defined('_LIST_OF_MAIL_TO_PROCESS')) define('_LIST_OF_MAIL_TO_PROCESS', 'voici la liste des nouveaux courriers &agrave; traiter');
if (!defined('_MAIL_COPIES_TO')) define('_MAIL_COPIES_TO', 'Courriers pour information du');
if (!defined('_MAIL_COPIES_LIST')) define('_MAIL_COPIES_LIST', 'voici la liste des copies des courriers dont vous &ecirc;tes destinataire');
if (!defined('_WHO_MUST_PROCESS_BEFORE')) define('_WHO_MUST_PROCESS_BEFORE', 'qui doit le traiter avant le');
if (!defined('_ORIGINAL_PAPERS_ALREADY_SEND')) define('_ORIGINAL_PAPERS_ALREADY_SEND', 'Les originaux papier vous ont &eacute;t&eacute; adress&eacute;s');
if (!defined('_WARNING')) define('_WARNING', 'Attention');
if (!defined('_YOU_MUST_BE_LOGGED')) define('_YOU_MUST_BE_LOGGED', 'vous devez &ecirc;tre identifi&eacute; sur le logiciel avant de tenter d\'acc&eacute;der au courrier');
if (!defined('_MAIL_TO_PROCESS_LIST')) define('_MAIL_TO_PROCESS_LIST','Liste des courriers pour traitement');
if (!defined('_COPIES_MAIL_LIST')) define('_COPIES_MAIL_LIST', 'Liste des courriers pour information');
if (!defined('_BY')) define('_BY', 'par');
if (!defined('_DEPARTMENT')) define('_DEPARTMENT', 'le service');
if (!defined('_')) define('_','');
//relance1.php
if (!defined('_FIRST_WARNING')) define('_FIRST_WARNING', 'Premi&egrave;re relance');
if (!defined('_FIRST_WARNING_TITLE')) define('_FIRST_WARNING_TITLE', 'Relance sur les courriers');
if (!defined('_THIS_IS_FIRST_WARNING')) define('_THIS_IS_FIRST_WARNING', 'Ceci est la premi&egrave;re relance pour les courriers suivants');
if (!defined('_MUST_BE_ADDED_TO_PRIORITY')) define('_MUST_BE_ADDED_TO_PRIORITY', 'Merci d&rsquo;ajouter ce ou ces courriers &agrave; vos traitements prioritaires');
if (!defined('_TO_PROCESS')) define('_TO_PROCESS', '&agrave; traiter');
//relance2.php
if (!defined('_SECOND_WARNING')) define('_SECOND_WARNING', 'Relance sur mes courriers');
if (!defined('_LATE_MAIL_TO_PROCESS')) define('_LATE_MAIL_TO_PROCESS', 'Courriers en retard &agrave; traiter');
if (!defined('_YOU_ARE_LATE')) define('_YOU_ARE_LATE', 'Vous avez du retard dans le traitement des courriers suivants');
if (!defined('_WAS_TO_PROCESS_BEFORE')) define('_WAS_TO_PROCESS_BEFORE', '&eacute;tait &agrave; traiter avant le');
if (!defined('_PROCESS_THIS_MAIL_QUICKLY')) define('_PROCESS_THIS_MAIL_QUICKLY', 'Merci de r&eacute;diger une r&eacute;ponse sous 48 heures');
if (!defined('_LATE')) define('_LATE', 'Retard');
if (!defined('_MAIL_TO_CC')) define('_MAIL_TO_CC', 'courriers en copies');
if (!defined('_FOLLOWING_MAIL_ARE_LATE')) define('_FOLLOWING_MAIL_ARE_LATE', 'Le traitement des courriers suivants a pris du retard');
if (!defined('_WHO_MUST_BE_PROCESSED_BEFORE')) define('_WHO_MUST_BE_PROCESSED_BEFORE', 'qui devait le traiter avant le');
if (!defined('_COPY_TITLE')) define('_COPY_TITLE', 'En copie');
//notifications engine
if (!defined('_WRONG_FUNCTION_OR_WRONG_PARAMETERS')) define('_WRONG_FUNCTION_OR_WRONG_PARAMETERS','Mauvaise fonction ou mauvais param&egrave;tre');




//v2.0
if (!defined('_ADMIN_NOTIFICATIONS')) define('_ADMIN_NOTIFICATIONS', 'Administration des notifications');
if (!defined('_MANAGE_EVENTS')) define('_MANAGE_EVENTS', 'G&eacute;rer les &eacute;venements');
if (!defined('_MANAGE_EVENTS_DESC')) define('_MANAGE_EVENTS_DESC', 'Ajouter ou modifier des &eacute;venement &agrave; notifier');

if (!defined('_TEST_SENDMAIL')) define('_TEST_SENDMAIL', 'Tester la configuration');
if (!defined('_TEST_SENDMAIL_DESC')) define('_TEST_SENDMAIL_DESC', 'V&eacute;rifier le param&eacute;trage du module de notification');

if (!defined('_EVENTS_LIST')) define('_EVENTS_LIST', 'Liste des &eacute;venements');
if (!defined('_THIS_EVENT')) define('_THIS_EVENT', 'Cet &eacute;venement');
if (!defined('_IS_UNKNOWN')) define('_IS_UNKNOWN', 'est inconnu');
if (!defined('_MODIFY_EVENT')) define('_MODIFY_EVENT', 'Modifier &eacute;venement');
if (!defined('_ADD_EVENT')) define('_ADD_EVENT', 'Ajouter &eacute;venement');


if (!defined('_DIFFUSION_TYPE')) define('_DIFFUSION_TYPE', 'Type de diffusion');
if (!defined('_EXCLUSION_TYPE')) define('_EXCLUSION_TYPE', 'Type d&rsquo;exclusion');


if (!defined('_ATTACH_MAIL_FILE')) define('_ATTACH_MAIL_FILE', 'Envoyer les courriers par notification');



//List of require
if (!defined('_NOTIFICATIONS_LISTINSTANC_DIFF_TYPE')) define('_NOTIFICATIONS_LISTINSTANC_DIFF_TYPE', 'Les courriels de notifications seront diffus&eacute;s &agrave tous les utilisateurs de la liste de diffusion (destinataire principal et copies)');
//if (!defined('_DIFFUSION_LIST')) define('_DIFFUSION_LIST', 'Liste de diffusion');


if (!defined('_NOTIFICATIONS_DEST_USER_DIFF_TYPE')) define('_NOTIFICATIONS_DEST_USER_DIFF_TYPE', 'Les courriels de notifications seront diffus&eacute;s au destinataire principal du document');
//if (!defined('_DEST_USER')) define('_DEST_USER', 'destinataire principal');


if (!defined('_NOTIFICATIONS_COPYLIST_DIFF_TYPE')) define('_NOTIFICATIONS_COPYLIST_DIFF_TYPE', 'Les courriels de notifications seront diffus&eacute;s aux utilisateurs en copie');
//if (!defined('_COPY_LIST')) define('_COPY_LIST', 'Liste des copies');

if (!defined('_NOTIFICATIONS_GROUP_DIFF_TYPE')) define('_NOTIFICATIONS_GROUP_DIFF_TYPE', 'Les courriels de notifications seront diffus&eacute;s au(x) groupe(s) sp&eacute;cifi&eacute;(s)');

if (!defined('_NOTIFICATIONS_ENTITY_DIFF_TYPE')) define('_NOTIFICATIONS_ENTITY_DIFF_TYPE', 'Les courriels de notifications seront diffus&eacute;s au(x) entit&eacute;e(s) sp&eacute;cifi&eacute;e(s)');

if (!defined('_NOTIFICATIONS_USER_DIFF_TYPE')) define('_NOTIFICATIONS_USER_DIFF_TYPE', 'Les courriels de notifications seront diffus&eacute;s au(x) utilisateurs(s) sp&eacute;cifi&eacute;(s)');

if (!defined('_NOTIFICATIONS_MAIL_DIFF_TYPE')) define('_NOTIFICATIONS_MAIL_DIFF_TYPE', 'Les courriels de notifications seront diffus&eacute;s a l&rsquo;adresse courriel sp&eacute;cifi&eacute;e');


if (!defined('_EVENT_ADDED')) define('_EVENT_ADDED', 'Evenement ajout&eacute;');
if (!defined('_EVENT_DELETED')) define('_EVENT_DELETED', 'Evenement supprim&eacute;');
if (!defined('_EVENT_MODIFIED')) define('_EVENT_MODIFIED', 'Evenement modifi&eacute;');
if (!defined('_EVENT_EMPTY')) define('_EVENT_EMPTY', 'Evenement vide');
if (!defined('_ALL_EVENTS')) define('_ALL_EVENTS', 'Tous les &eacute;venements');
if (!defined('_SYSTEM')) define('_SYSTEM', 'Syst&egrave;me');



?>

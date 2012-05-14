<?php
/**
* File : fr.php
*
* French language file
*
* @package  Maarch workflow 1.0
* @version 1.0
* @since 06/2007
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
/*********************** Module Board ***********************************/
if (!defined('_ADMIN_TEMPLATE_ANSWER'))  define('_ADMIN_TEMPLATE_ANSWER', 'Administrer les mod&egrave;les');
if (!defined('_ADMIN_TEMPLATES_DESC')) define('_ADMIN_TEMPLATES_DESC', 'Cr&eacute;er des mod&egrave;les de document avec les suites Office et Html');
/*********************** Menu ***********************************/
if (!defined('_EXPLOIT_TEMPLATE_ANSWER'))  define('_EXPLOIT_TEMPLATE_ANSWER', 'Exploiter les mod&egrave;les');


if (!defined('_TEMPLATE_NAME'))  define('_TEMPLATE_NAME', 'Nom du mod&egrave;le');
if (!defined('_TEMPLATES_COMMENT'))  define('_TEMPLATES_COMMENT', 'Mod&egrave;les de document');
if (!defined('_TEMPLATE_EMPTY'))  define('_TEMPLATE_EMPTY', 'Le mod&egrave;le est vide');
if (!defined('_TEMPLATE_LABEL')) define( '_TEMPLATE_LABEL', 'Nom du mod&egrave;le');
if (!defined('_TEMPLATE_COMMENT')) define( '_TEMPLATE_COMMENT', 'Description');
if (!defined('_TEMPLATE_TYPE')) define( '_TEMPLATE_TYPE', 'Type de mod&egrave;le');
if (!defined('_TEMPLATE_STYLE')) define( '_TEMPLATE_STYLE', 'Nature du mod&egrave;le');
if (!defined('_EDIT_TEMPLATE')) define( '_EDIT_TEMPLATE', 'Edition du mod&egrave;le');
if (!defined('_TEMPLATE_ID')) define( '_TEMPLATE_ID', 'ID du mod&egrave;le');
if (!defined('_ATTACH_TEMPLATE_TO_ENTITY'))  define('_ATTACH_TEMPLATE_TO_ENTITY', 'Le mod&egrave;le doit &ecirc;tre rattach&eacute; &agrave; au moins un service');
if (!defined('_TEMPLATE_DATASOURCE'))  define('_TEMPLATE_DATASOURCE', 'Source de donn&eacute;es');
if (!defined('_OFFICE'))  define('_OFFICE', 'Office');
if (!defined('_HTML'))  define('_HTML', 'HTML');

if (!defined('_MANAGE_TEMPLATES'))  define('_MANAGE_TEMPLATES', 'G&eacute;rer les mod&egrave;les de courrier');
if (!defined('_MANAGE_TEMPLATES_APP'))  define('_MANAGE_TEMPLATES_APP', 'G&eacute;rer les mod&egrave;les de courrier de l&rsquo;application');

/************** Models : Liste + Formulaire**************/
if (!defined('_TEMPLATES_LIST'))  define('_TEMPLATES_LIST', 'Liste des mod&egrave;les');
if (!defined('_ALL_TEMPLATES'))  define('_ALL_TEMPLATES', 'Tous les mod&egrave;les');
if (!defined('_TEMPLATE'))  define('_TEMPLATE', 'Mod&egrave;le');
if (!defined('_ADD_TEMPLATE'))  define('_ADD_TEMPLATE', 'Ajouter un mod&egrave;le');

if (!defined('_THE_TEMPLATE'))  define('_THE_TEMPLATE', 'Le mod&egrave;le ');
if (!defined('_TEMPLATE_ADDITION'))  define('_TEMPLATE_ADDITION', 'Ajout d&rsquo;un mod&egrave;le');
if (!defined('_TEMPLATE_MODIFICATION'))  define('_TEMPLATE_MODIFICATION', 'Modification d&rsquo;un mod&egrave;le');
if (!defined('_TEMPLATE_DELETION'))  define('_TEMPLATE_DELETION', 'Suppression d&rsquo;un mod&egrave;le');
if (!defined('_MODIFY_TEMPLATE'))  define('_MODIFY_TEMPLATE', 'Valider les changements');
if (!defined('_TEMPLATE_ADDED'))  define('_TEMPLATE_ADDED', 'Nouveau mod&egrave;le cr&eacute;&eacute;');
if (!defined('_TEMPLATE_UPDATED'))  define('_TEMPLATE_UPDATED', 'Md&egrave;le modifi&eacute;');
if (!defined('_CHOOSE_ENTITY_TEMPLATE'))  define('_CHOOSE_ENTITY_TEMPLATE', 'Choisissez le(s) service(s) au(x)quel(s) vous souhaitez associer ce mod&egrave;le');
if (!defined('_REALLY_DEL_TEMPLATE'))  define('_REALLY_DEL_TEMPLATE', 'Voulez vous vraiment supprimer ce mod&egrave;le ?');

if (!defined('_NEW_TEMPLATE'))  define('_NEW_TEMPLATE', 'Nouveau mod&egrave;le');
if (!defined('_CHOOSE_TEMPLATE'))  define('_CHOOSE_TEMPLATE','Choisissez un mod&egrave;le');
if (!defined('_TEMPLATE_DELETED'))  define('_TEMPLATE_DELETED', 'Mod&egrave;le supprim&eacute;');
if (!defined('_DELETED_TEMPLATE'))  define('_DELETED_TEMPLATE', 'Mod&egrave;le supprim&eacute;');
if (!defined('_ASSOCIATED_TEMPLATES'))  define('_ASSOCIATED_TEMPLATES', 'Mod&egrave;les associ&eacute;s');
if (!defined('_NO_DEFINED_TEMPLATE'))  define('_NO_DEFINED_TEMPLATE', 'Pas de mod&egrave;le d&eacute;fini');
if (!defined('_EDIT_YOUR_TEMPLATE'))  define('_EDIT_YOUR_TEMPLATE', 'Veuillez &eacute;diter au moins une fois le mod&egrave;le');
if (!defined('_TEMPLATE_NAME2'))  define('_TEMPLATE_NAME2', 'Le nom du mod&egrave;le ');
if (!defined('_TEMPLATE_CONTENT'))  define('_TEMPLATE_CONTENT', 'Le contenu du mod&egrave;le ');
if (!defined('_TEMPLATES'))  define('_TEMPLATES', 'mod&egrave;le(s)');
if (!defined('_ADMIN_TEMPLATES'))  define('_ADMIN_TEMPLATES','Administrer les mod&egrave;les de document');
if (!defined('_LOADED_FILE'))  define('_LOADED_FILE', 'Fichier import&eacute;');
if (!defined('_GENERATED_FILE'))  define('_GENERATED_FILE', 'Fichier gen&eacute;r&eacute;');
if (!defined('_MUST_CHOOSE_TEMPLATE'))  define('_MUST_CHOOSE_TEMPLATE', 'Vous devez choisir un mod&egrave;le');
if (!defined('_GENERATE_ANSWER'))  define('_GENERATE_ANSWER', 'G&eacute;n&eacute;rer une pi&egrave;ce jointe');
if (!defined('_GENERATE'))  define('_GENERATE', 'G&eacute;n&eacute;rer PJ');
if (!defined('_PLEASE_SELECT_TEMPLATE'))  define('_PLEASE_SELECT_TEMPLATE', 'Veuillez s&eacute;lectionner un mod&egrave;le de pi&egrave;ce jointe');
if (!defined('_NO_MODE_DEFINED'))  define('_NO_MODE_DEFINED', 'Erreur : mode absent');
if (!defined('_TEMPLATE_OR_ANSWER_ERROR'))  define('_TEMPLATE_OR_ANSWER_ERROR', 'Erreur : probl&egrave;me au chargement du mod&egrave;le ou de la r&eacute;ponse');
if (!defined('_NO_CONTENT'))  define('_NO_CONTENT', 'Erreur : Contenu de la r&eacute;ponse vide');
if (!defined('_FILE_OPEN_ERROR'))  define('_FILE_OPEN_ERROR', 'Ouverture fichier impossible');
if (!defined('_ANSWER_OPEN_ERROR'))  define('_ANSWER_OPEN_ERROR', 'Erreur : probl&egrave;me &agrave; l&rsquo;ouverture de la r&eacute;ponse');
if (!defined('_TEMPLATE_UPDATE'))  define('_TEMPLATE_UPDATE', 'Mod&egrave;le mis &agrave; jour');

if (!defined('_ANSWER_UPDATED'))  define('_ANSWER_UPDATED', 'Pi&egrave;ce jointe mise &agrave; jour');
if (!defined('_ANSWER_TITLE'))  define('_ANSWER_TITLE', 'Titre de la pi&egrave;ce jointe');

if (!defined('_VALID_TEXT'))  define('_VALID_TEXT', 'Valider texte');

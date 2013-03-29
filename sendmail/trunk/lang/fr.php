<?php
/*
 *
 *    Copyright 2013 Maarch
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

if (!defined('_SENDMAIL'))
    define('_SENDMAIL', 'Envoi de mails');
if (!defined('_SENDMAIL_COMMENT'))
    define('_SENDMAIL_COMMENT', 'Envoi de mails');
if (!defined('_EMAIL_LIST'))
    define('_EMAIL_LIST', 'Emails');
if (!defined('_EMAIL_LIST_DESC'))
    define('_EMAIL_LIST_DESC', 'Liste des  emails');
if (!defined('_SENDED_EMAILS'))
    define('_SENDED_EMAILS', 'Emails envoy&eacute;s');
 
//STATUS
if (!defined('_EMAIL_DRAFT'))
    define('_EMAIL_DRAFT','Brouillon');
if (!defined('_EMAIL_WAIT'))
    define('_EMAIL_WAIT','En attente d\'envoi');
if (!defined('_EMAIL_IN_PROGRESS'))
    define('_EMAIL_IN_PROGRESS','En cours d\'envoi');
if (!defined('_EMAIL_SENT'))
    define('_EMAIL_SENT','Envoy&eacute;');
if (!defined('_EMAIL_ERROR'))
    define('_EMAIL_ERROR','Erreur lors de l\'envoi');
    
//FORM
if (!defined('_FROM'))
    define('_FROM','Exp&eacute;diteur');
if (!defined('_FROM_SHORT'))
    define('_FROM_SHORT','De');
if (!defined('_SEND_TO'))
    define('_SEND_TO','Destinataire');
if (!defined('_SEND_TO_SHORT'))
    define('_SEND_TO_SHORT','&Agrave;');
if (!defined('_COPY_TO'))
    define('_COPY_TO','En copie');
if (!defined('_COPY_TO_SHORT'))
    define('_COPY_TO_SHORT','Cc');
if (!defined('_COPY_TO_INVISIBLE'))
    define('_COPY_TO_INVISIBLE','En copie invisible');
if (!defined('_COPY_TO_INVISIBLE_SHORT'))
    define('_COPY_TO_INVISIBLE_SHORT','Cci');
if (!defined('_JOINED_FILES'))
    define('_JOINED_FILES','Fichiers joints');
if (!defined('_SHOW_OTHER_COPY_FIELDS'))
    define('_SHOW_OTHER_COPY_FIELDS','Afficher/masquer les champs Cc et Cci');
if (!defined('_EMAIL_OBJECT'))
    define('_EMAIL_OBJECT','Objet');
if (!defined('_HTML_OR_RAW'))
    define('_HTML_OR_RAW','Mise en forme avanc&eacute;e / Texte brut');  
if (!defined('_DEFAULT_BODY'))
    define('_DEFAULT_BODY','Votre message est pr&ecirc;t &agrave;  &ecirc;tre envoy&eacute;e avec les fichiers joints suivants :');
if (!defined('_NOTES_FILE'))
    define('_NOTES_FILE', 'Notes du document');
if (!defined('_EMAIL_WRONG_FORMAT'))
    define('_EMAIL_WRONG_FORMAT', 'L\'adresse email n&rsquo;est pas dans le bon format');
    
    
//ERROR
if (!defined('_EMAIL_NOT_EXIST'))
    define('_EMAIL_NOT_EXIST', 'Cet email n&rsquo;existe pas');

//ADD
if (!defined('_NEW_EMAIL'))
    define('_NEW_EMAIL','Nouveau message');
if (!defined('_CREATE_EMAIL'))
    define('_CREATE_EMAIL', 'Cr&eacute;er');
if (!defined('_EMAIL_ADDED'))
    define('_EMAIL_ADDED', 'Email ajout&eacute;');
    
//SEND
if (!defined('_SEND_EMAIL'))
    define('_SEND_EMAIL','Envoyer'); 
if (!defined('_RESEND_EMAIL'))
    define('_RESEND_EMAIL','Renvoyer');
    
//SAVE
if (!defined('_SAVE_EMAIL'))
    define('_SAVE_EMAIL','Enregistrer');
    
//READ
if (!defined('_READ_EMAIL'))
    define('_READ_EMAIL','Afficher le mail');
    
//TRANSFER
if (!defined('_TRANSFER_EMAIL'))
    define('_TRANSFER_EMAIL','Transf&eacute;rer le mail');
    
//EDIT    
if (!defined('_EDIT_EMAIL'))
    define('_EDIT_EMAIL', 'Modifier le mail');
if (!defined('_SAVE_EMAIL'))
    define('_SAVE_EMAIL', 'Enregistrer');
if (!defined('_SAVE_COPY_EMAIL'))
    define('_SAVE_COPY_EMAIL', 'Enregistrer une copie');
if (!defined('_EMAIL_UPDATED'))
    define('_EMAIL_UPDATED', 'Email modifi&eacute;');
 
//REMOVE 
if (!defined('_REMOVE_EMAIL'))
    define('_REMOVE_EMAIL', 'Supprimer');
if (!defined('_REMOVE_EMAILS'))
    define('_REMOVE_EMAILS', 'Supprimer les mails');
if (!defined('_REALLY_REMOVE_EMAIL'))
    define('_REALLY_REMOVE_EMAIL', 'Voulez-vous supprimez le message');
if (!defined('_EMAIL_REMOVED'))
    define('_EMAIL_REMOVED', 'Email supprim&eacute;');
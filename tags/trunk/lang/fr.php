<?php
/*
*    Copyright 2008,2012 Maarch
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

/**
* Module : Tags
*
* This module is used to store ressources with any keywords
* V: 1.0
*
* @file
* @author Loic Vinet
* @date $date$
* @version $Revision$
*/

/*********************** TAGS ***********************************/
if (!defined('_TAG_DEFAULT'))
    define('_TAG_DEFAULT', 'Mot-cl&eacute;');
if (!defined('_TAGS_DEFAULT'))
    define('_TAGS_DEFAULT', 'Mots-cl&eacute;s');
if (!defined('_TAGS_COMMENT'))
    define('_TAGS_COMMENT', 'Mots-cl&eacute;s');
if (!defined('_TAGS_LIST'))
    define('_TAGS_LIST', 'Liste des mots-cl&eacute;s');
if (!defined('_MODIFY_TAG'))
    define('_MODIFY_TAG', 'Modifier le mot-cl&eacute;');
if (!defined('_MANAGE_TAGS'))
    define('_MANAGE_TAGS', 'G&eacute;rer les mots-cl&eacute;s');
if (!defined('_ADMIN_TAGS'))
    define('_ADMIN_TAGS', 'Mots-cl&eacute;s');
if (!defined('_ADMIN_TAGS_DESC'))
    define('_ADMIN_TAGS_DESC', 'Permet de modifier, supprimer, ajouter ou fusionner des mots-cl&eacute;s');
if (!defined('_ALL_TAGS'))
    define('_ALL_TAGS', 'Tous les mots-cl&eacute;s');
if (!defined('_TAG_DELETED'))
    define('_TAG_DELETED', _TAG_DEFAULT.' supprim&eacute;');
if (!defined('_TAG_ADDED'))
    define('_TAG_ADDED', _TAG_DEFAULT.' ajout&eacute;');
if (!defined('_TAG_UPDATED'))
    define('_TAG_UPDATED', _TAG_DEFAULT.' modifi&eacute;');
if (!defined('_TAG_LABEL_IS_EMPTY'))
    define('_TAG_LABEL_IS_EMPTY', 'Le libell&eacute; est vide');
if (!defined('_NO_TAG'))
    define('_NO_TAG', 'Pas de '._TAG_DEFAULT);
if (!defined('_TAG_VIEW'))
    define('_TAG_VIEW', 'Visualisation des '._TAGS_DEFAULT);
if (!defined('_TAG_VIEW_DESC'))
    define('_TAG_VIEW_DESC', 'Permet de visualiser les '._TAGS_DEFAULT);
if (!defined('_ADD_TAG'))
    define('_ADD_TAG', 'Ajouter un '._TAG_DEFAULT);
if (!defined('_ADD_TAG_TO_RES'))
    define('_ADD_TAG_TO_RES', 'Ajouter des '._TAGS_DEFAULT.' &agrave; une ressource');
if (!defined('_CREATE_TAG'))
    define('_CREATE_TAG', 'Cr&eacute;er des '._TAGS_DEFAULT);
if (!defined('_ADD_TAG_TO_RES_DESC'))
    define('_ADD_TAG_TO_RES_DESC', 'Permet d&rsquo;ajouter des '._TAGS_DEFAULT.' &agrave; une ressource');
if (!defined('_DELETE_TAG_TO_RES'))
    define('_DELETE_TAG_TO_RES', 'Supprimer des '._TAGS_DEFAULT.' &agrave; une ressource');
if (!defined('_DELETE_TAG_TO_RES_DESC'))
    define('_DELETE_TAG_TO_RES_DESC', 'Permet de supprimer des '._TAGS_DEFAULT.' &agrave; une ressource');
if (!defined('_NEW_TAG_IN_LIBRARY_RIGHTS'))
    define('_NEW_TAG_IN_LIBRARY_RIGHTS', 'Cr&eacute;er des nouveaux '._TAGS_DEFAULT.' dans la librairie Maarch');
if (!defined('_NEW_TAG_IN_LIBRARY_RIGHTS_DESC'))
    define('_NEW_TAG_IN_LIBRARY_RIGHTS_DESC', 'En activant ce tag, l&rsquo;utilistateur pourra ajouter de nouveaux '._TAGS_DEFAULT.' dans la librairie de Maarch');
if (!defined('_TAG'))
    define('_TAG', _TAG_DEFAULT);
if (!defined('_TAGS'))
    define('_TAGS', _TAGS_DEFAULT);
if (!defined('_TAG_SEPARATOR_HELP'))
    define('_TAG_SEPARATOR_HELP', 'S&eacute;parez les mots-cl&eacute;s en appuyant sur Entrée ou avec des virgules');
if (!defined('_NB_DOCS_FOR_THIS_TAG'))
    define('_NB_DOCS_FOR_THIS_TAG', 'Nbre de documents taggu&eacute;s');
if (!defined('_TAGOTHER_OPTIONS'))
    define('_TAGOTHER_OPTIONS', 'Autres options');
if (!defined('_TAG_FUSION_ACTIONLABEL'))
    define('_TAG_FUSION_ACTIONLABEL', 'Fusionner le tag avec');
if (!defined('_TAGFUSION'))
    define('_TAGFUSION', 'Fusion');
if (!defined('_TAGFUSION_GOODRESULT'))
    define('_TAGFUSION_GOODRESULT', 'Ces tags sont maintenant fusionn&eacute;s');
if (!defined('_TAG_ALREADY_EXISTS'))
    define('_TAG_ALREADY_EXISTS', 'Ce tag existe d&eacute;j&agrave;');
if (!defined('_CHOOSE_TAG'))
    define('_CHOOSE_TAG', 'Choix des mots-cl&eacute;s');
if (!defined('_TAG_SEARCH'))
    define('_TAG_SEARCH', 'Mots-cl&eacute;s');
if (!defined('_TAGNONE'))
    define('_TAGNONE', 'Aucun');
if (!defined('_ALL_TAG_DELETED_FOR_RES_ID'))
    define('_ALL_TAG_DELETED_FOR_RES_ID', 'Tous les tags sont supprim&eacute; pour la ressource');
if (!defined('_TAGCLICKTODEL'))
    define('_TAGCLICKTODEL', 'supprimer');
?>

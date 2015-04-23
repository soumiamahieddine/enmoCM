<?php
if (!defined('_SIGN_DOCS'))
    define('_SIGN_DOCS', 'Signer les documents');

// CIRCUIT DE VISA
if (!defined('_VISA_WORKFLOW'))
    define('_VISA_WORKFLOW', 'Circuit de visa');
if (!defined('_VISA_WORKFLOW_COMMENT'))   
	define('_VISA_WORKFLOW_COMMENT', 'Gestion du circuit de visa');
if (!defined('_VIEW_VISA_WORKFLOW'))
    define('_VIEW_VISA_WORKFLOW', 'Visualisation du circuit de visa');
if (!defined('_VIEW_VISA_WORKFLOW_DESC'))
    define('_VIEW_VISA_WORKFLOW_DESC', 'Permet de visualiser le circuit de visa dans les parties de liste de diffusion et dans celles d\'avancement.');
if (!defined('_CONFIG_VISA_WORKFLOW'))
    define('_CONFIG_VISA_WORKFLOW', 'Configuration du circuit de visa');
if (!defined('_CONFIG_VISA_WORKFLOW_DESC'))
    define('_CONFIG_VISA_WORKFLOW_DESC', 'Permet de configurer le circuit de visa que devra prendre le courrier');
if (!defined('_EMPTY_USER_LIST'))
    define('_EMPTY_USER_LIST', 'La liste des utilisateurs est vide');
if (!defined('_VISA_ANSWERS'))
    define('_VISA_ANSWERS', 'Viser les projets de réponse');
	
if (!defined('_VISA_ANSWERS_DESC'))
    define('_VISA_ANSWERS_DESC', 'Permet de viser les projets de réponse');

// CIRCUIT D'AVIS
if (!defined('_AVIS_WORKFLOW'))
    define('_AVIS_WORKFLOW', 'Circuit d\'avis');
if (!defined('_CONFIG_AVIS_WORKFLOW'))
    define('_CONFIG_AVIS_WORKFLOW', 'Configuration du circuit d\'avis');
if (!defined('_CONFIG_AVIS_WORKFLOW_DESC'))
    define('_CONFIG_AVIS_WORKFLOW_DESC', 'Permet de configurer le circuit d\'avis du courrier');

if (!defined('_THUMBPRINT'))
    define('_THUMBPRINT', 'Empreinte numérique');

// Actions
if (!defined('_SEND_MAIL'))    define( '_SEND_MAIL', 'Envoi du dossier par courriel');
if (!defined('_IMPRIM_DOSSIER'))    define( '_IMPRIM_DOSSIER', 'Impression du dossier');
if (!defined('_PROCEED_WORKFLOW'))    define( '_PROCEED_WORKFLOW', 'Poursuivre le circuit de visa');
if (!defined('_VISA_MAIL'))    define( '_VISA_MAIL', 'Viser le courrier');
if (!defined('_PREPARE_VISA'))    define( '_PREPARE_VISA', 'Préparer le circuit de visa');
?>
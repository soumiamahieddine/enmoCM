<?php
/**
* core tools Class
*
*  Contains all the functions to load core and others
*
* @package  maarch
* @version 3.0
* @since 10/2005
* @license GPL v3
* @author  Laurent Giovannoni  <dev@maarch.org>
*
*/
require_once 'core/core_tables.php';

class business_app_tools extends dbquery
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
    * Build Maarch business app configuration into sessions vars with a xml
    * configuration file
    */
    public function build_business_app_config()
    {
        // build Maarch business app configuration into sessions vars
        $_SESSION['showmenu'] = 'oui';

        $core = new core_tools();
        if (file_exists(
            $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
            . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . 'apps'
            . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
            . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'config.xml'
        )
        ) {
            $path = $_SESSION['config']['corepath'] . 'custom'
                  . DIRECTORY_SEPARATOR . $_SESSION['custom_override_id']
                  . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR
                  . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . 'xml'
                  . DIRECTORY_SEPARATOR . 'config.xml';
        } else {
            $path = 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
                  . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR
                  . 'config.xml';
        }
        $xmlconfig = simplexml_load_file($path);
        if ($xmlconfig <> false) {
            $config = $xmlconfig->CONFIG;
            $_SESSION['config']['businessappname'] =
                (string) $config->businessappname;
            //$_SESSION['config']['businessapppath'] = (string) $config->businessapppath;
            //##############

            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
                $protocol = 'https';
            } else {
                $protocol = 'http';
            }
            if ($_SERVER['SERVER_PORT'] <> 443 && $protocol == 'https') {
                $serverPort = ':' . $_SERVER['SERVER_PORT'];
            } else if ($_SERVER['SERVER_PORT'] <> 80 && $protocol == 'http') {
                $serverPort = ':' . $_SERVER['SERVER_PORT'];
            } else {
                $serverPort = '';
            }

            //##############
            if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])
                && $_SERVER['HTTP_X_FORWARDED_HOST'] <> ''
            ) {
                $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
            } else {
                $host = $_SERVER['HTTP_HOST'];
            }

            $tmp = $host;
            if (! preg_match('/:[0-9]+$/', $host)) {
                $tmp = $host.$serverPort;
            }
            $_SESSION['config']['businessappurl'] = $protocol . '://' . $tmp
                . str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
            $_SESSION['config']['databaseserver'] =
                (string) $config->databaseserver;
            $_SESSION['config']['databaseserverport'] =
                (string) $config->databaseserverport;
            $_SESSION['config']['databasetype'] =
                (string) $config->databasetype;
            $_SESSION['config']['databasename'] =
                (string) $config->databasename;
            $_SESSION['config']['databaseschema'] =
                (string) $config->databaseschema;
            $_SESSION['config']['databaseuser'] =
                (string) $config->databaseuser;
            $_SESSION['config']['databasepassword'] =
                (string) $config->databasepassword;
            $_SESSION['config']['databasesearchlimit'] =
                (string) $config->databasesearchlimit;
            $_SESSION['config']['nblinetoshow'] =
                (string) $config->nblinetoshow;
            $_SESSION['config']['limitcharsearch'] =
                (string) $config->limitcharsearch;
            $_SESSION['config']['lang'] = (string) $config->lang;
            $_SESSION['config']['adminmail'] = (string) $config->adminmail;
            $_SESSION['config']['adminname'] = (string) $config->adminname;
            $_SESSION['config']['debug'] = (string) $config->debug;
            $_SESSION['config']['applicationname'] = (string) $config->applicationname;
            $_SESSION['config']['defaultPage'] = (string) $config->defaultPage;
            $_SESSION['config']['exportdirectory'] = (string) $config->exportdirectory;
            $_SESSION['config']['tmppath'] = (string) $config->tmppath;
            $_SESSION['config']['cookietime'] = (string) $config->CookieTime;
            $_SESSION['config']['ldap'] = (string) $config->ldap;
            $_SESSION['config']['userdefaultpassword'] = (string) $config->userdefaultpassword;
            if (isset($config->showfooter)) {
                $_SESSION['config']['showfooter'] = (string) $config->showfooter;
            } else {
                $_SESSION['config']['showfooter'] = 'true';
            }
            //$_SESSION['config']['databaseworkspace'] = (string) $config->databaseworkspace;

            $tablename = $xmlconfig->TABLENAME;
            $_SESSION['tablename']['doctypes_first_level'] = (string) $tablename->doctypes_first_level;
            $_SESSION['tablename']['doctypes_second_level'] = (string) $tablename->doctypes_second_level;
            $_SESSION['tablename']['mlb_doctype_ext'] = (string) $tablename->mlb_doctype_ext;
            $_SESSION['tablename']['doctypes_indexes'] = (string) $tablename->doctypes_indexes;
            $_SESSION['tablename']['saved_queries'] = (string) $tablename->saved_queries;
            $_SESSION['tablename']['contacts'] = (string) $tablename->contacts;
            $i = 0;

            if ( isset($_SESSION['custom_override_id']) && file_exists(
            	'custom/' . $_SESSION['custom_override_id'] . '/'
                . $_SESSION['config']['lang'] . '.php'
            )
            ) {
               include_once 'custom/' . $_SESSION['custom_override_id'] . '/'
                . $_SESSION['config']['lang'] . '.php';
            }
            include_once 'apps' . DIRECTORY_SEPARATOR
                    . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR
                    . 'lang' . DIRECTORY_SEPARATOR . $_SESSION['config']['lang']
                    . ".php";
            $_SESSION['collections'] = array();
            foreach ($xmlconfig->COLLECTION as $col) {
                $tmp = (string) $col->label;
                if (!empty($tmp) && defined($tmp) && constant($tmp) <> NULL) {
                	$tmp = constant($tmp);
                }
                $extensions = $col->extensions;
                $tab = array();

                if ($extensions->count()) {
                    $extensionTables = $extensions->table;
                    if ($extensionTables->count() > 0) {
                        foreach ($extensions->table as $table) {
                            if (strlen($extensionTables) > 0) {
                                array_push($tab, (string) $table);
                            }
                        }
                    }
                }
                if (isset($col->table) && ! empty($col->table)) {
                    $_SESSION['collections'][$i] = array(
                        'id' => (string) $col->id,
                        'label' => (string) $tmp,
                        'table' => (string) $col->table,
                        'view' => (string) $col->view,
                        'adr' => (string) $col->adr,
                        'index_file' => (string) $col->index_file,
                        'script_add' => (string) $col->script_add,
                        'script_search' => (string) $col->script_search,
                        'script_search_result' => (string) $col->script_search_result,
                        'script_details' => (string) $col->script_details,
                        'path_to_lucene_index' => (string) $col->path_to_lucene_index,
                        'extensions' => $tab,
                    );
                    $i++;
                } else {
                    $_SESSION['collections'][$i] = array(
                        'id' => (string) $col->id,
                        'label' => (string) $tmp,
                        'view' => (string) $col->view,
                        'adr' => (string) $col->adr,
                        'index_file' => (string) $col->index_file,
                        'script_add' => (string) $col->script_add,
                        'script_search' => (string) $col->script_search,
                        'script_search_result' => (string) $col->script_search_result,
                        'script_details' => (string) $col->script_details,
                        'path_to_lucene_index' => (string) $col->path_to_lucene_index,
                        'extensions' => $tab,
                    );
                }
            }
            $history = $xmlconfig->HISTORY;
            $_SESSION['history']['usersdel'] = (string) $history->usersdel;
            $_SESSION['history']['usersban'] = (string) $history->usersban;
            $_SESSION['history']['usersadd'] = (string) $history->usersadd;
            $_SESSION['history']['usersup'] = (string) $history->usersup;
            $_SESSION['history']['usersval'] = (string) $history->usersval;
            $_SESSION['history']['doctypesdel'] = (string) $history->doctypesdel;
            $_SESSION['history']['doctypesadd'] = (string) $history->doctypesadd;
            $_SESSION['history']['doctypesup'] = (string) $history->doctypesup;
            $_SESSION['history']['doctypesval'] = (string) $history->doctypesval;
            $_SESSION['history']['doctypesprop'] = (string) $history->doctypesprop;
            $_SESSION['history']['usergroupsdel'] = (string) $history->usergroupsdel;
            $_SESSION['history']['usergroupsban'] = (string) $history->usergroupsban;
            $_SESSION['history']['usergroupsadd'] = (string) $history->usergroupsadd;
            $_SESSION['history']['usergroupsup'] = (string) $history->usergroupsup;
            $_SESSION['history']['usergroupsval'] = (string) $history->usergroupsval;
            $_SESSION['history']['structuredel'] = (string) $history->structuredel;
            $_SESSION['history']['structureadd'] = (string) $history->structureadd;
            $_SESSION['history']['structureup'] = (string) $history->structureup;
            $_SESSION['history']['subfolderdel'] = (string) $history->subfolderdel;
            $_SESSION['history']['subfolderadd'] = (string) $history->subfolderadd;
            $_SESSION['history']['subfolderup'] = (string) $history->subfolderup;
            $_SESSION['history']['resadd'] = (string) $history->resadd;
            $_SESSION['history']['resup'] = (string) $history->resup;
            $_SESSION['history']['resdel'] = (string) $history->resdel;
            $_SESSION['history']['resview'] = (string) $history->resview;
            $_SESSION['history']['userlogin'] = (string) $history->userlogin;
            $_SESSION['history']['userlogout'] = (string) $history->userlogout;
            $_SESSION['history']['actionadd'] = (string) $history->actionadd;
            $_SESSION['history']['actionup'] = (string) $history->actionup;
            $_SESSION['history']['actiondel'] = (string) $history->actiondel;
            $_SESSION['history']['contactadd'] = (string) $history->contactadd;
            $_SESSION['history']['contactup'] = (string) $history->contactup;
            $_SESSION['history']['contactdel'] = (string) $history->contactdel;
            $_SESSION['history']['statusadd'] = (string) $history->statusadd;
            $_SESSION['history']['statusup'] = (string) $history->statusup;
            $_SESSION['history']['statusdel'] = (string) $history->statusdel;
            $_SESSION['history']['docserversadd'] = (string) $history->docserversadd;
            $_SESSION['history']['docserversdel'] = (string) $history->docserversdel;
            $_SESSION['history']['docserversallow'] = (string) $history->docserversallow;
            $_SESSION['history']['docserversban'] = (string) $history->docserversban;
            //$_SESSION['history']['docserversclose'] = (string) $history->docserversclose;
            $_SESSION['history']['docserverslocationsadd'] = (string) $history->docserverslocationsadd;
            $_SESSION['history']['docserverslocationsdel'] = (string) $history->docserverslocationsdel;
            $_SESSION['history']['docserverslocationsallow'] = (string) $history->docserverslocationsallow;
            $_SESSION['history']['docserverslocationsban'] = (string) $history->docserverslocationsban;
            $_SESSION['history']['docserverstypesadd'] = (string) $history->docserverstypesadd;
            $_SESSION['history']['docserverstypesdel'] = (string) $history->docserverstypesdel;
            $_SESSION['history']['docserverstypesallow'] = (string) $history->docserverstypesallow;
            $_SESSION['history']['docserverstypesban'] = (string) $history->docserverstypesban;
            $_SESSION['history_keywords'] = array();
            foreach ($xmlconfig->KEYWORDS as $keyword) {
                $tmp = (string) $keyword->label;
                if (!empty($tmp) && defined($tmp) && constant($tmp) <> NULL) {
                	$tmp = constant($tmp);
                }

                array_push(
                    $_SESSION['history_keywords'],
                    array(
                        'id'    => (string) $keyword->id,
                        'label' => $tmp,
                    )
                );
            }

            $i = 0;
            foreach ($xmlconfig->MODULES as $modules) {

                $_SESSION['modules'][$i] = array(
                    'moduleid' => (string) $modules->moduleid,
                    //,"comment" => (string) $MODULES->comment
                );
                $i ++;
            }
            $this->_loadActionsPages();
        }
    }

    /**
    * Load actions in session
    */
    private function _loadActionsPages()
    {
        if (isset($_SESSION['config']['corepath'])
            && isset($_SESSION['config']['app_id'])
            && isset($_SESSION['config']['lang']
        )
        ) {
            $core = new core_tools();
            if (file_exists(
                $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
                . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . 'core'
                . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR
                . 'actions_pages.xml'
            )
            ) {
                $path = $_SESSION['config']['corepath'] . 'custom'
                      . DIRECTORY_SEPARATOR . $_SESSION['custom_override_id']
                      . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR
                      . 'xml' . DIRECTORY_SEPARATOR . 'actions_pages.xml';
            } else {
                $path = 'core' . DIRECTORY_SEPARATOR . 'xml'
                      . DIRECTORY_SEPARATOR . 'actions_pages.xml';
            }
            $xmlfile = simplexml_load_file($path);
            $langPath = 'apps' . DIRECTORY_SEPARATOR
                       . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR
                       . 'lang' . DIRECTORY_SEPARATOR
                       . $_SESSION['config']['lang'] . '.php';

            $i = 0;
            foreach ($xmlfile->ACTIONPAGE as $actionPage) {
                $label = (string) $actionPage->LABEL;
             	if (!empty($label) && defined($label)
             		&& constant($label) <> NULL
             	) {
                	$label = constant($label);
                }
                $keyword = '';
                if (isset($actionPage->KEYWORD)
                    && ! empty($actionPage->KEYWORD)
                ) {
                    $keyword = (string) $actionPage->KEYWORD;
                }
                $createFlag = 'N';
                if (isset($actionPage->FLAG_CREATE)
                    && (string) $actionPage->FLAG_CREATE == 'true'
                ) {
                    $createFlag = 'Y';
                }

                $_SESSION['actions_pages'][$i] = array(
                    'ID' => (string) $actionPage->ID,
                    'LABEL' => $label,
                    'NAME' => (string) $actionPage->NAME,
                    'ORIGIN' => (string) $actionPage->ORIGIN,
                    'MODULE' => (string) $actionPage->MODULE,
                    'KEYWORD' => $keyword,
                    'FLAG_CREATE' => $createFlag,
                );
                $i++;

            }
        }
    }

    private function _loadEntrepriseVar()
    {
        $core = new core_tools();
        if (file_exists(
            $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
            . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR
            . 'apps'.DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
            . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR
            . 'entreprise.xml'
        )
        ) {
            $path = $_SESSION['config']['corepath'] . 'custom'
                  . DIRECTORY_SEPARATOR . $_SESSION['custom_override_id']
                  . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR
                  . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . 'xml'
                  . DIRECTORY_SEPARATOR . 'entreprise.xml';
        } else {
            $path = 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
                  . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR
                  . 'entreprise.xml';
        }
        $xmlfile = simplexml_load_file($path);
        $langPath = 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
                  . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR
                  . $_SESSION['config']['lang'] . '.php';

        $categories = $xmlfile->categories;
        $_SESSION['mail_categories'] = array();
        foreach ($categories->category as $cat) {
            $label = (string) $cat->label;
        	if (!empty($label) && defined($label)
             	&& constant($label) <> NULL
             ) {
                $label = constant($label);
            }
            $_SESSION['mail_categories'][(string) $cat->id] = $label;
        }
        $_SESSION['default_category'] = (string) $categories->default_category;

        $_SESSION['mail_natures'] = array();
        $mailNatures = $xmlfile->mail_natures;
        foreach ($mailNatures->nature as $nature ) {
            $label = (string) $nature->label;
            if (!empty($label) && defined($label)
             	&& constant($label) <> NULL
             ) {
                $label = constant($label);
            }
            $_SESSION['mail_natures'][(string) $nature->id] = $label;
        }
        $_SESSION['default_mail_nature'] = (string) $mailNatures->default_nature;

        $_SESSION['mail_priorities'] = array();
        $mailPriorities = $xmlfile->priorities;
        $i = 0;
        foreach ($mailPriorities->priority as $priority ) {
            $label = (string) $priority;
            if (!empty($label) && defined($label)
            	&& constant($label) <> NULL
            ) {
                $label = constant($label);
            }
            $_SESSION['mail_priorities'][$i] = $label;
            $i++;
        }
        $_SESSION['default_mail_priority'] = (string) $mailPriorities->default_priority;

        $_SESSION['mail_titles'] = array();
        $mailTitles = $xmlfile->titles;
        $i = 0;
        foreach ($mailTitles->nature as $title ) {
            $label = (string) $title->label;
        	if (!empty($label) && defined($label)
            	&& constant($label) <> NULL
            ) {
                $label = constant($label);
            }
            $_SESSION['mail_titles'][(string) $title->id] = $label;
        }
        $_SESSION['default_mail_title'] = (string) $mailTitles->default_title;
    }

    public function compare_base_version($xmlVersionBase)
    {
        // Compare version value beetwen version base xml file and version base
        // value in the database
        if (file_exists(
            $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
            . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR
            . $xmlVersionBase
        )
        ) {
            $path = $_SESSION['config']['corepath'] . 'custom'
                  . DIRECTORY_SEPARATOR . $_SESSION['custom_override_id']
                  . DIRECTORY_SEPARATOR . $xmlVersionBase;
        } else {
            $path = $xmlVersionBase;
        }
        $xmlBase = simplexml_load_file($path);
        //Find value in the xml database_version tag
        if ($xmlBase) {
            $_SESSION['maarch_entreprise']
                ['xml_versionbase'] = (string) $xmlBase->database_version;
        } else {
            $_SESSION['maarch_entreprise']['xml_versionbase'] = 'none';
        }
        $checkBase = new dbquery();
        $checkBase->connect();
        $query = "select param_value_int from " . PARAM_TABLE
               . " where id = 'database_version'";

        $checkBase->query($query); //Find value in parameters table on database
        if ($checkBase->nb_result() == 0) {
            $_SESSION['maarch_entreprise']['database_version'] = "none";
        } else {
            $vbg = $checkBase->fetch_object();
            $_SESSION['maarch_entreprise']
                ['database_version'] = $vbg->param_value_int;
        }
        //If this two parameters is not find, this is the end of this function
        if ($_SESSION['maarch_entreprise']['xml_versionbase'] <> 'none' ) {
            if (($_SESSION['maarch_entreprise']['xml_versionbase'] > $_SESSION['maarch_entreprise']['database_version'])
                || ($_SESSION['maarch_entreprise']['database_version'] == 'none')
            ) {
                $_SESSION['error'] .= '<p style="color:#346DC4;border:1px'
                                    . 'solid blue">'
                                    . _VERSION_BASE_AND_XML_BASEVERSION_NOT_MATCH
                                    . '</p>';
            }
        }
    }

    public function load_features($xmlFeatures)
    {
        $_SESSION['features'] = array();
        //Defines all features by  default at 'false'
        $_SESSION['features']['personal_contact'] = "false";
        $_SESSION['features']['search_notes'] = "false";
        $_SESSION['features']['dest_to_copy_during_redirection'] = "false";
        $_SESSION['features']['show_types_tree'] = "false";
        $_SESSION['features']['create_public_contact'] = "false";
        if (file_exists(
            $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
            . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR
            . $xmlFeatures
        )
        ) {
            $path = $_SESSION['config']['corepath'] . 'custom'
                  . DIRECTORY_SEPARATOR . $_SESSION['custom_override_id']
                  . DIRECTORY_SEPARATOR . $xmlFeatures;
        } else {
            $path = $xmlFeatures;
        }

        $xmlfeatures = simplexml_load_file($path);
        if ($xmlfeatures) {
            $feats = $xmlfeatures->FEATURES;
            $_SESSION['features']['personal_contact'] = (string) $feats->personal_contact;
            $_SESSION['features']['search_notes'] = (string) $feats->search_notes;
            $_SESSION['features']['dest_to_copy_during_redirection'] = (string) $feats->dest_to_copy_during_redirection;
            $_SESSION['features']['show_types_tree'] = (string) $feats->show_types_tree;
            $_SESSION['features']['create_public_contact'] = (string) $feats->create_public_contact;
        }
    }

    /**
    * Loads current folder identifier in session
    *
    */
    private function _loadCurrentFolder($userId)
    {
        if (isset($userId)) {
            $this->connect();
            $this->query(
                "select custom_t1 from " . USERS_TABLE . " where user_id = '"
                . $userId . "'"
            );
            $res = $this->fetch_object();

            $_SESSION['current_folder_id'] = $res->custom_t1;
        }
    }

    /**
    * Loads app specific vars in session
    *
    */
    public function load_app_var_session($userData)
    {
        $this->_loadCurrentFolder($userData['UserId']);
        $this->_loadEntrepriseVar();
        $this->load_features(
            'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
            . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'features.xml'
        );
        if (file_exists(
            $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
            . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . 'apps'
            . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
            . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR
            . 'docservers_features.xml'
        )
        ) {
            $path = $_SESSION['config']['corepath'] . 'custom'
                  . DIRECTORY_SEPARATOR . $_SESSION['custom_override_id']
                  . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR
                  . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . 'xml'
                  . DIRECTORY_SEPARATOR . 'docservers_features.xml';
        } else {
            $path = 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
                  . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR
                  . 'docservers_features.xml';
        }
        $_SESSION['docserversFeatures'] = array();
        $_SESSION['docserversFeatures'] = functions::object2array(
            simplexml_load_file($path)
        );
    }

    /**
    * Return a specific path or false
    *
    */
    public function insert_app_page($name)
    {
        if (! isset($name) || empty($name)) {
            return false;
        }
        if ($name == 'structures' || $name == 'structures_list_by_name'
            || $name == 'structure_up' || $name == 'structure_del'
        ) {
            $path = 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
                  . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR
                  . 'architecture' . DIRECTORY_SEPARATOR . 'structures'
                  . DIRECTORY_SEPARATOR . $name . '.php';
            return $path;
        } else if ($name == 'subfolders' || $name == 'subfolders_list_by_name'
            || $name == 'subfolder_up' || $name == 'subfolder_del'
        ) {
            $path = 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
                  . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR
                  . 'architecture' . DIRECTORY_SEPARATOR . 'subfolders'
                  . DIRECTORY_SEPARATOR . $name . '.php';
            return $path;
        } else if ($name == 'types' || $name == 'types_up'
            || $name == 'types_up_db' || $name == 'types_add'
            || $name == 'types_del' || $name == 'get_index'
            || $name == 'choose_index' || $name == 'choose_coll'
            || $name == 'types_list_by_name'
        ) {
            $path = 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
                  . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR
                  . 'architecture' . DIRECTORY_SEPARATOR . 'types'
                  . DIRECTORY_SEPARATOR . $name . '.php';

            return $path;
        } else {
            return false;
        }
    }

    public function get_titles()
    {
        $core = new core_tools();
        if (file_exists(
            $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
            . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . 'apps'
            . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
            . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR
            . 'entreprise.xml'
        )
        ) {
            $path = $_SESSION['config']['corepath'] . 'custom'
                  . DIRECTORY_SEPARATOR . $_SESSION['custom_override_id']
                  . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR
                  . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . 'xml'
                  . DIRECTORY_SEPARATOR . 'entreprise.xml';
        } else {
            $path = 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
                  . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR
                  . 'entreprise.xml';
        }
        $xmlfile = simplexml_load_file($path);
        $langPath = 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
                  . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR
                  . $_SESSION['config']['lang'] . '.php';

        $resTitles = array();
        $titles = $xmlfile->titles;
        foreach ($titles->title as $title ) {
            $label = (string) $title->label;
            if (!empty($label) && defined($label)
	            && constant($label) <> NULL
	        ) {
	            $label = constant($label);
	        }

            $resTitles[(string) $title->id] = $label;
        }

        asort($resTitles, SORT_LOCALE_STRING);
        $defaultTitle = (string) $titles->default_title;
        return array('titles' => $resTitles, 'default_title' => $defaultTitle);
    }


    public function get_label_title($titleId)
    {
        $core = new core_tools();
        if (file_exists(
            $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
            . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . 'apps'
            . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
            . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR
            . 'entreprise.xml'
        )
        ) {
            $path = $_SESSION['config']['corepath'] . 'custom'
                  . DIRECTORY_SEPARATOR . $_SESSION['custom_override_id']
                  . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR
                  . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR .'xml'
                  . DIRECTORY_SEPARATOR . 'entreprise.xml';
        } else {
            $path = 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
            . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR
            . 'entreprise.xml';
        }
        $xmlfile = simplexml_load_file($path);
        $langPath = 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
                  . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR
                  . $_SESSION['config']['lang'] . '.php';
        $titles = $xmlfile->titles;
        foreach ($titles->title as $title ) {
            if ($titleId == (string) $title->id) {
                $label = (string) $title->label;
	            if (!empty($label) && defined($label)
	            	&& constant($label) <> NULL
	            ) {
	                $label = constant($label);
	            }

                return $label;
            }
        }
        return '';
    }
}

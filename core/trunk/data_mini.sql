--
-- PostgreSQL database
--


SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;


--
-- Utilisateurs et groupes
--

INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('superadmin', '17c4520f6cfd1ab53d8745e84681eb49', 'Super', 'ADMIN', '+33 1 47 24 51', 'info@maarch.org', 'Maarch', '11', NULL, NULL, 'e657b3542b0362910db9195cb0fd0fb5', '2012-02-28 10:02:08', 'Y', 'N', NULL, 'OK', 'standard', NULL);

INSERT INTO usergroups (group_id, group_desc, administrator, custom_right1, custom_right2, custom_right3, custom_right4, enabled) VALUES ('ADMINISTRATEUR', 'Administrateurs fonctionnels', 'N', 'N', 'N', 'N', 'N', 'Y');


INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'adv_search_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_users');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_groups');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_architecture');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'view_history');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'view_history_batch');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_status');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_actions');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'reopen_mail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_docservers');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_baskets');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'update_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'join_res_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'join_res_case_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'close_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'manage_entities');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'update_list_diff_in_details');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_templates');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_reports');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'reports');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'view_versions');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'add_new_version');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'tag_view');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'add_tag_to_res');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'delete_tag_to_res');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_tag');


--
-- Corbeilles, actions et redirections
--

INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, enabled) VALUES ('letterbox_coll', 'IndexingBasket', 'Courriers à indexer', 'Corbeille d''indexation', ' ', 'N', 'Y', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, enabled) VALUES ('letterbox_coll', 'CopyMailBasket', '03 - Courriers en copie', 'Corbeille d''information', '(res_id in (select res_id from listinstance WHERE coll_id = ''letterbox_coll'' and item_type = ''user_id'' and item_id = @user and item_mode = ''cc'') or res_id in (select res_id from listinstance WHERE coll_id = ''letterbox_coll'' and item_type = ''entity_id'' and item_mode = ''cc'' and item_id in (@my_entities))) and status <> ''DEL''', 'N', 'Y', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, enabled) VALUES ('letterbox_coll', 'MyBasket', '01 - Courriers a traiter', 'Corbeille de traitement', '(status =''NEW'' or status =''COU'') and dest_user = @user and type_id not in (100)', 'N', 'Y', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, enabled) VALUES ('letterbox_coll', 'LateMailBasket', '02 - Courriers en retard', 'Courriers en retard', 'destination in (@my_entities, @subentities[@my_primary_entity]) and (status <> ''DEL'' AND status <> ''REP'') and (now() > process_limit_date)', 'N', 'Y', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, enabled) VALUES ('letterbox_coll', 'DepartmentBasket', '10 - Courriers de ma direction', 'Corbeille de supervision', 'destination in (@my_entities, @subentities[@my_primary_entity]) and (status <> ''DEL'' AND status <> ''REP'' and status <> ''VAL'')', 'N', 'Y', 'Y');

INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (1, 'redirect', 'Rediriger', 'NONE', 'Y', 'Y', 'redirect', 'Y', 'entities', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (2, 'to_validate', 'Valider', 'VAL', 'Y', 'N', 'confirm_status', 'N', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (18, 'indexing', 'Valider courrier', 'NEW', 'N', 'Y', 'validate_mail', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (19, '', 'Traiter document', 'COU', 'N', 'Y', 'process', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (20, '', 'Cloturer', 'END', 'N', 'Y', 'close_mail', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (21, 'indexing', 'Indexation', 'INIT', 'N', 'Y', 'index_mlb', 'Y', 'apps', 'Y');


INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('COU', 'En cours', 'Y', 'mail.gif', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('DEL', 'Supprimé', 'Y', NULL, 'apps', 'N', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('END', 'Clos', 'Y', 'mail_end.gif', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('NEW', 'Nouveau', 'Y', '', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('RET', 'Retour courrier', 'N', '', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('VIS', 'A approuver', 'N', '', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('SIG', 'A imprimer', 'N', '', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('UNS', 'Rejeté', 'N', '', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('FACREJ', 'Facture rejetée', 'N', '', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('FACVAL', 'Facture validée', 'N', '', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('VAL', 'A Valider', 'Y', '', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('INIT', 'Nouveau courrier non validé', 'Y', '', 'apps', 'Y', 'Y');

--
-- groupbasket
--


--
-- action_groupbasket
--


--
-- groupbasket_redirect
--

--
-- Plan de classement
--


--
-- Folders
--




--
-- Divers
--

INSERT INTO parameters (id, param_value_string, param_value_int, param_value_date) VALUES ('apa_reservation_batch', NULL, 1, NULL);
INSERT INTO parameters (id, param_value_string, param_value_int, param_value_date) VALUES ('workbatch_rec', '', 7, NULL);
INSERT INTO parameters (id, param_value_string, param_value_int, param_value_date) VALUES ('folder_id_increment', '', 200, NULL);
INSERT INTO parameters (id, param_value_string, param_value_int, param_value_date) VALUES ('work_batch_autoimport_id', NULL, 1, NULL);
INSERT INTO parameters (id, param_value_string, param_value_int, param_value_date) VALUES ('postindexing_workbatch', NULL, 40, NULL);
INSERT INTO parameters (id, param_value_string, param_value_int, param_value_date) VALUES ('database_version', NULL, 130, NULL);


INSERT INTO security (security_id, group_id, coll_id, where_clause, maarch_comment, can_insert, can_update, can_delete, rights_bitmask, mr_start_date, mr_stop_date, where_target) VALUES (41, 'ADMINISTRATEUR', 'letterbox_coll', 'DESTINATION in (@my_entities)', 'Administration', 'N', 'N', 'N', 0, NULL, NULL, 'DOC');

--
-- Modèles et notification
--

INSERT INTO templates (template_id, template_label, template_comment, template_content) VALUES (2, 'AR_MAARCH', 'Accusé de réception Maarch', '<p style="TEXT-ALIGN: left"><img src="img/default_maarch.gif" alt="" width="278" height="80" />&nbsp;</p>
<p><em><font face="Arial Black" size="2" color="#3366ff">La gestion de courriers Open source !</font></em><br />Mail : info@maarch.org<br />Web : http://www.maarch.org</p>
<p style="TEXT-ALIGN: right"><font size="2">Nanterre, le [NOW]</font></p>
<p><font size="2">Cher&nbsp;&nbsp;[CONTACT_LASTNAME]</font></p>
<p><font size="2">Nous accusons r&eacute;ception de votre courrier du <strong>BLABLA</strong>, et mettons tout en oeuvre pour vous r&eacute;pondre dans les plus brefs d&eacute;lais.</font></p>
<p><font size="2">Sachez qu''en cas de besoin urgent nos bureaux sont ouverts de 8h00 &agrave; 15h00, du lundi au samedi.</font></p>
<p><font size="2">Le num&eacute;ro vert o&ugrave; vous pouvez nous appeler est le 0800 455 24.</font></p>
<p><font size="2">Votre n&deg; de dossier &agrave; rappeler dans toute correspondance est le : [CHRONO]</font></p>
<p>&nbsp;</p>
<p><font size="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Cordialement,</font></p>
<p><font size="2"><em>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; [CURRENT_USER_FIRSTNAME] [CURRENT_USER_LASTNAME]<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; [DESTINATION]<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; T&eacute;l : [CURRENT_USER_PHONE]<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Courriel : [CURRENT_USER_EMAIL]</em></font></p>
<p>&nbsp;</p>');
INSERT INTO templates (template_id, template_label, template_comment, template_content) VALUES (1, 'TEST', 'Test des mots-clés', '<h1>Liste des mots-cl&eacute;s utilisables dans les mod&egrave;les de reponse</h1>
<p>&nbsp;</p>
<table border="0">
<tbody>
<tr>
<td><font size="2">Contact externe: Civilit&eacute;&nbsp;</font></td>
<td><strong><font size="2">[CONTACT_TITLE]</font></strong></td>
</tr>
<tr>
<td><font size="2">Contact externe: Nom</font></td>
<td><strong><font size="2">[CONTACT_LASTNAME]</font></strong></td>
</tr>
<tr>
<td><font size="2">Contact externe: Pr&eacute;nom</font></td>
<td><strong><font size="2">[CONTACT_FIRSTNAME]</font></strong></td>
</tr>
<tr>
<td><font size="2">Contact externe : Organisation</font></td>
<td><strong><font size="2">[CONTACT_SOCIETY]</font></strong></td>
</tr>
<tr>
<td><font size="2">Contact externe : Adresse N&deg;</font></td>
<td><strong><font size="2">[CONTACT_ADRS_NUM]</font></strong></td>
</tr>
<tr>
<td><font size="2">Contact externe : Adresse Rue</font></td>
<td><strong><font size="2">[CONTACT_ADRS_STREET]</font></strong></td>
</tr>
<tr>
<td><font size="2">Contact externe : Adresse Complement</font></td>
<td><strong><font size="2">[CONTACT_ADRS_COMP]</font></strong></td>
</tr>
<tr>
<td><font size="2">Contact externe : Adresse Ville</font></td>
<td><strong><font size="2">[CONTACT_ADRS_TOWN]</font></strong></td>
</tr>
<tr>
<td><font size="2">Contact externe : Adresse CP</font></td>
<td><strong><font size="2">[CONTACT_ADRS_PC]</font></strong></td>
</tr>
<tr>
<td><font size="2">Contact externe : Adresse Pays</font></td>
<td><strong><font size="2">[CONTACT_ADRS_COUNTRY]</font></strong></td>
</tr>
<tr>
<td><font size="2">Contact interne: Nom</font></td>
<td><strong><font size="2">[USER_LASTNAME]</font></strong></td>
</tr>
<tr>
<td><font size="2">Contact interne: Pr&eacute;nom</font></td>
<td><strong><font size="2">[USER_FIRSTNAME]</font></strong></td>
</tr>
<tr>
<td><font size="2">Courrier: Service Traitant</font></td>
<td><strong><font size="2">[DESTINATION]</font></strong></td>
</tr>
<tr>
<td><font size="2">Courrier: Type de document</font></td>
<td><strong><font size="2">[DOCTYPE]</font></strong></td>
</tr>
<tr>
<td><font size="2">Courrier: Cat&eacute;gorie</font></td>
<td><strong><font size="2">[CAT_ID]</font></strong></td>
</tr>
<tr>
<td><font size="2">Courrier: Nature</font></td>
<td><strong><font size="2">[NATURE]</font></strong></td>
</tr>
<tr>
<td><font size="2">Courrier: Date d''arriv&eacute;e&nbsp;&nbsp;</font></td>
<td><strong><font size="2">[ADMISSION_DATE]</font></strong></td>
</tr>
<tr>
<td><font size="2">Courrier: Date du courrier&nbsp;&nbsp;</font></td>
<td><strong><font size="2">[DOC_DATE]</font></strong></td>
</tr>
<tr>
<td><font size="2">Courrier: Date limite de traitement&nbsp;&nbsp;</font></td>
<td><strong><font size="2">[PROCESS_LIMIT_DATE]</font></strong></td>
</tr>
<tr>
<td><font size="2">Courrier: Notes de traitement&nbsp;&nbsp;</font></td>
<td><strong><font size="2">[PROCESS_NOTES]</font></strong></td>
</tr>
<tr>
<td><font size="2">Courrier: Date de cl&ocirc;ture&nbsp;&nbsp;</font></td>
<td><strong><font size="2">[CLOSING_DATE]</font></strong></td>
</tr>
<tr>
<td><font size="2">Courrier: Objet</font></td>
<td><strong><font size="2">[SUBJECT]</font></strong></td>
</tr>
<tr>
<td><font size="2">Courrier: Num&eacute;ro chrono</font></td>
<td><strong><font size="2">[CHRONO]</font></strong></td>
</tr>
<tr>
<td><font size="2">Document: Auteur</font></td>
<td><strong><font size="2">[AUTHOR]</font></strong></td>
</tr>
<tr>
<td><font size="2">Document: Date d''enregistrement</font></td>
<td><strong><font size="2">[CREATION_DATE]</font></strong></td>
</tr>
<tr>
<td><font size="2">Sp&eacute;cial: Date du jour</font></td>
<td><strong><font size="2">[NOW]</font></strong></td>
</tr>
<tr>
<td><font size="2">Sp&eacute;cial: Nom du destinataire traitant</font></td>
<td><strong><font size="2">[CURRENT_USER_LASTNAME]</font></strong></td>
</tr>
<tr>
<td><font size="2">Sp&eacute;cial: Pr&eacute;nom du destinataire traitant</font></td>
<td><strong><font size="2">[CURRENT_USER_FIRSTNAME]</font></strong></td>
</tr>
<tr>
<td><font size="2">Sp&eacute;cial: T&eacute;l&eacute;phone du destinataire traitant</font></td>
<td><strong><font size="2">[CURRENT_USER_PHONE]</font></strong></td>
</tr>
<tr>
<td><font size="2">Sp&eacute;cial: Mail du destinataire traitant</font></td>
<td><strong><font size="2">[CURRENT_USER_EMAIL]</font></strong></td>
</tr>
</tbody>
</table>
<p><font size="2">&nbsp;</font></p>');
INSERT INTO templates (template_id, template_label, template_comment, template_content) VALUES (3, 'AppelTel', 'Appel téléphonique', '<p><font size="\\&quot;5\\&quot;"><strong>APPEL TELEPHONIQUE</strong></font></p>
<p><font size="\\&quot;2\\&quot;">Bonjour,</font></p>
<p><font size="\\&quot;2\\&quot;">Vous avez re&ccedil;u un appel t&eacute;l&eacute;phonique dont voici les informations :</font></p>
<ul>
<li><font size="\\&quot;2\\&quot;">Date : </font></li>
<li><font size="\\&quot;2\\&quot;">Heure :</font></li>
<li><font size="\\&quot;2\\&quot;">Soci&eacute;t&eacute; :</font></li>
<li><font size="\\&quot;2\\&quot;">Contact :</font></li>
</ul>
<p><font size="\\&quot;2\\&quot;">Notes : </font></p>');
INSERT INTO templates (template_id, template_label, template_comment, template_content) VALUES (20, 'Notifications événement', 'Test des notifications événements système', '<p>{#translate._HELLO} {#mergefield.recipient.firstname} {#mergefield.recipient.lastname},</p>
<p>&nbsp;</p>
<p>Voici la liste des &eacute;v&eacute;nements {#mergefield.template_association.description} :</p>
<p>&nbsp;</p>
<p>{#mergetable.evenements}</p>');
INSERT INTO templates (template_id, template_label, template_comment, template_content) VALUES (21, 'Diffusion de courrier', 'Notification de diffusion de courrier', '<p>Bonjour {#mergefield.recipient.firstname} {#mergefield.recipient.lastname},</p>
<p>&nbsp;</p>
<p>Voici la liste des courriers qui vous ont &eacute;t&eacute; envoy&eacute;s :</p>
<p>&nbsp;</p>
<p>{#mergetable.courriers}</p>
<p>&nbsp;</p>');



--
-- Stockage et cycle de vie
--

INSERT INTO lc_policies (policy_id, policy_name, policy_desc) VALUES ('FNTC', 'FNTC standard archiving policy', '3 months fast cache, immediate double backup on AIP, final sort: offline after 10 years');

INSERT INTO lc_cycles (policy_id, cycle_id, cycle_desc, sequence_number, where_clause, break_key, validation_mode) VALUES ('FNTC', 'INIT', 'Initial location', 0, '1=1', 'doc_custom_t1', 'AUTO');
INSERT INTO lc_cycles (policy_id, cycle_id, cycle_desc, sequence_number, where_clause, break_key, validation_mode) VALUES ('FNTC', 'DISPOSAL', 'Disposal', 3, 'current_date >= creation_date::timestamp + interval ''10'' year', '', 'USER');
INSERT INTO lc_cycles (policy_id, cycle_id, cycle_desc, sequence_number, where_clause, break_key, validation_mode) VALUES ('FNTC', 'OAIS', 'FASTHD cache is purged. Resource lays only on OAIS docservers', 2, 'current_date >= creation_date::timestamp + interval ''3'' month', '', 'AUTO');
INSERT INTO lc_cycles (policy_id, cycle_id, cycle_desc, sequence_number, where_clause, break_key, validation_mode) VALUES ('FNTC', 'OAIS_CACHED', 'Immediate copy on OAIS main and backup docservers. Resource is still present on FASTHD', 1, 'current_date >= creation_date::timestamp + interval ''7'' day', '', 'AUTO');

INSERT INTO lc_cycle_steps (policy_id, cycle_id, cycle_step_id, cycle_step_desc, docserver_type_id, is_allow_failure, step_operation, sequence_number, is_must_complete, preprocess_script, postprocess_script) VALUES ('FNTC', 'INIT', 'INIT', 'Initial location', 'FASTHD', 'N', 'NONE', 1, 'N', NULL, NULL);
INSERT INTO lc_cycle_steps (policy_id, cycle_id, cycle_step_id, cycle_step_desc, docserver_type_id, is_allow_failure, step_operation, sequence_number, is_must_complete, preprocess_script, postprocess_script) VALUES ('FNTC', 'OAIS_CACHED', 'COPY_MAIN', 'Immediate copy on main OAIS docserver', 'OAIS_MAIN', 'N', 'COPY', 1, 'Y', NULL, NULL);
INSERT INTO lc_cycle_steps (policy_id, cycle_id, cycle_step_id, cycle_step_desc, docserver_type_id, is_allow_failure, step_operation, sequence_number, is_must_complete, preprocess_script, postprocess_script) VALUES ('FNTC', 'OAIS_CACHED', 'COPY_SAFE', 'Immediate copy on main OAIS docserver', 'OAIS_SAFE', 'N', 'COPY', 2, 'Y', NULL, NULL);
INSERT INTO lc_cycle_steps (policy_id, cycle_id, cycle_step_id, cycle_step_desc, docserver_type_id, is_allow_failure, step_operation, sequence_number, is_must_complete, preprocess_script, postprocess_script) VALUES ('FNTC', 'OAIS', 'PURGE', 'Purge after 3 months', 'FASTHD', 'N', 'PURGE', 1, 'N', NULL, NULL);
INSERT INTO lc_cycle_steps (policy_id, cycle_id, cycle_step_id, cycle_step_desc, docserver_type_id, is_allow_failure, step_operation, sequence_number, is_must_complete, preprocess_script, postprocess_script) VALUES ('FNTC', 'DISPOSAL', 'FINAL_MAIN', 'Disposal', 'OAIS_MAIN', 'N', 'NONE', 1, 'N', NULL, NULL);

INSERT INTO docserver_locations (docserver_location_id, ipv4, ipv6, net_domain, mask, net_link, enabled) VALUES ('NANTERRE', '127.0.0.1', '', 'MAARCH', '255.255.255.0', NULL, 'Y');
INSERT INTO docserver_locations (docserver_location_id, ipv4, ipv6, net_domain, mask, net_link, enabled) VALUES ('NICE', '192.168.21.63', '', '', '', NULL, 'Y');

INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, is_container, container_max_number, is_compressed, compression_mode, is_meta, meta_template, is_logged, log_template, is_signed, fingerprint_mode) VALUES ('FASTHD', 'FASTHD', 'Y', 'N', 0, 'N', 'NONE', 'N', 'NONE', 'N', 'NONE', 'Y', 'SHA256');
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, is_container, container_max_number, is_compressed, compression_mode, is_meta, meta_template, is_logged, log_template, is_signed, fingerprint_mode) VALUES ('OAIS_MAIN', 'Main OAIS store', 'Y', 'Y', 100, 'Y', '7Z', 'Y', 'OAIS_std.dtd', 'Y', 'log_std.dtd', 'Y', 'SHA512');
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, is_container, container_max_number, is_compressed, compression_mode, is_meta, meta_template, is_logged, log_template, is_signed, fingerprint_mode) VALUES ('OFFLINE', 'Off line tape', 'Y', 'Y', 1000, 'Y', '7Z', 'Y', 'OAIS_std.dtd', 'Y', 'log_std.dtd', 'Y', 'SHA512');
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, is_container, container_max_number, is_compressed, compression_mode, is_meta, meta_template, is_logged, log_template, is_signed, fingerprint_mode) VALUES ('OAIS_SAFE', 'Distant backup OAIS store', 'Y', 'Y', 20, 'Y', 'ZIP', 'Y', 'OAIS_std.dtd', 'Y', 'log_std.dtd', 'Y', 'SHA512');
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, is_container, container_max_number, is_compressed, compression_mode, is_meta, meta_template, is_logged, log_template, is_signed, fingerprint_mode) VALUES ('TEMPLATES', 'TEMPLATES', 'Y', 'N', 0, 'N', 'NONE', 'N', 'NONE', 'N', 'NONE', 'N', 'NONE');

INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) VALUES ('OFFLINE_1', 'OFFLINE', 'Off line tape', 'N', 'Y', 50000000000, 1, 'C:\\maarch\\docservers\\entreprise\\offline\\', NULL, NULL, NULL, '2011-01-13 16:58:24.00929', NULL, 'res_coll', 30, 'NANTERRE', 4);
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) VALUES ('FASTHD_AI', 'FASTHD', 'Fast internal disc bay for autoimport', 'N', 'Y', 50000000000, 1, 'C:\\maarch\\docservers\\entreprise\\ai\\', NULL, NULL, NULL, '2011-01-07 13:43:48.696644', NULL, 'res_coll', 10, 'NANTERRE', 1);
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) VALUES ('OAIS_MAIN_1', 'OAIS_MAIN', 'Main OAIS store', 'N', 'Y', 50000000000, 1, 'C:\\maarch\\docservers\\entreprise\\OAIS_main\\', NULL, NULL, NULL, '2011-01-13 14:48:27.901368', NULL, 'res_coll', 20, 'NANTERRE', 2);
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) VALUES ('OAIS_SAFE_1', 'OAIS_SAFE', 'Distant backup OAIS store', 'N', 'Y', 50000000000, 1, 'C:\\maarch\\docservers\\entreprise\\OAIS_safe\\', NULL, NULL, NULL, '2011-01-13 14:49:05.095119', NULL, 'res_coll', 20, 'NICE', 3);
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) VALUES ('FASTHD_MAN', 'FASTHD', 'Fast internal disc bay for letterbox mode', 'N', 'Y', 50000000000, 1, 'C:\\maarch\\docservers\\entreprise\\manual\\', NULL, NULL, NULL, '2011-01-13 14:47:49.197164', NULL, 'letterbox_coll', 10, 'NANTERRE', 2);
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) VALUES ('TEMPLATES', 'TEMPLATES', 'Templates', 'N', 'Y', 50000000000, 1, 'C:\\maarch\\docservers\\entreprise\\templates\\', NULL, NULL, NULL, '2012-04-01 14:49:05.095119', NULL, 'templates', 1, 'NANTERRE', 1);

--
-- annuaire/contacts
--


--
-- Archivage physique
--




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

INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('rrenaud', 'ef9689be896dacd901cae4f13593e90d', 'Robert', 'RENAUD', '', 'info@maarch.org', '', '0', NULL, NULL, 'f2e8a41dfb14cb10fefe5620efde6f3a', '2012-02-22 16:02:22', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('ccordy', 'ef9689be896dacd901cae4f13593e90d', 'Chloé', 'CORDY', '', 'info@maarch.org', '', '0', NULL, NULL, '6cee607907e2f25198dfd0d86676738d', '2012-02-22 16:02:23', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('ssissoko', 'ef9689be896dacd901cae4f13593e90d', 'Sylvain', 'SISSOKO', '', 'info@maarch.org', '', '0', NULL, NULL, '85185818fbe92d32f1ab0a06c3d199e2', '2012-02-17 11:02:39', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('nnataliu', 'ef9689be896dacd901cae4f13593e90d', 'Nancy', 'NATALY', NULL, 'info@maarch.org', NULL, '0', NULL, NULL, NULL, NULL, 'Y', 'Y', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('ddur', 'ef9689be896dacd901cae4f13593e90d', 'Dominique', 'DUR', '', 'info@maarch.org', '', '0', NULL, NULL, 'e599f40bcfe6517f871a298d705a3f58', '2012-02-22 17:02:23', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('jjane', 'ef9689be896dacd901cae4f13593e90d', 'Jenny', 'JANE', '', 'info@maarch.org', '', '0', NULL, NULL, '9855381ca9bcf90a1138508d2ddf6316', '2012-02-22 16:02:23', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('eerine', 'ef9689be896dacd901cae4f13593e90d', 'Edith', 'ERINA', '', 'info@maarch.org', '', '0', NULL, NULL, '076e854f5044d61a5a1ad6809705613a', '2012-02-17 13:02:18', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('kkaar', 'ef9689be896dacd901cae4f13593e90d', 'Katy', 'KAAR', NULL, 'info@maarch.org', NULL, '0', NULL, NULL, NULL, NULL, 'Y', 'Y', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('bboule', 'ef9689be896dacd901cae4f13593e90d', 'Bruno', 'BOULE', '', 'info@maarch.org', '', '0', NULL, NULL, '1282a5592996068c04b00c31cc72a6d5', '2012-02-23 08:02:53', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('ppetit', 'ef9689be896dacd901cae4f13593e90d', 'Patricia', 'PETIT', '', 'info@maarch.org', '', '0', NULL, NULL, '7212973abc788e0daa00b9f3a6657e95', '2012-02-23 08:02:53', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('aackermann', 'ef9689be896dacd901cae4f13593e90d', 'Amanda', 'ACKERMANN', NULL, 'info@maarch.org', NULL, '0', NULL, NULL, NULL, NULL, 'Y', 'Y', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('ppruvost', 'ef9689be896dacd901cae4f13593e90d', 'Pierre', 'PRUVOST', '', 'info@maarch.org', '', '0', NULL, NULL, '48e6b9a881a44cb95e883c4ec4708046', '2012-02-24 15:02:11', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('ttong', 'ef9689be896dacd901cae4f13593e90d', 'Tony', 'TONG', '', 'info@maarch.org', '', '0', NULL, NULL, '7ed03f46403a018a58d2d2ff3da8cd85', '2012-02-23 08:02:55', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('sstar', 'ef9689be896dacd901cae4f13593e90d', 'Suzanne', 'STAR', '', 'info@maarch.org', '', '0', NULL, NULL, 'e3db0c66afa62e72758c568e1ba4b48e', '2012-02-23 09:02:24', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('ssaporta', 'ef9689be896dacd901cae4f13593e90d', 'Sabrina', 'SAPORTA', '', 'info@maarch.org', '', '0', NULL, NULL, '9f576ee66ec17d3af5838f930d2fa9a6', '2012-02-22 16:02:22', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('ccharles', 'ef9689be896dacd901cae4f13593e90d', 'Charlotte', 'CHARLES', '', 'info@maarch.org', '', '0', NULL, NULL, '559ff86ca8aa70c456ebed8e45b8ffac', '2012-02-24 11:02:34', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('mmanfred', 'ef9689be896dacd901cae4f13593e90d', 'Martin', 'MANFRED', '', 'info@maarch.org', '', '0', NULL, NULL, '989c67fde3ebb43c223d8b270be735f1', '2012-02-28 10:02:10', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('bblier', 'ef9689be896dacd901cae4f13593e90d', 'Bernard', 'BLIER', '', 'info@maarch.org', '', '0', NULL, NULL, '79f6b52cdad073bb8d618edcdb1e61fc', '2012-02-28 10:02:22', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('ddaull', 'ef9689be896dacd901cae4f13593e90d', 'Denis', 'DAULL', '', 'info@maarch.org', '', '0', NULL, NULL, 'cd06eeaa8b5379f41d7f4bfc32f3688b', '2012-02-28 10:02:06', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('bbain', 'ef9689be896dacd901cae4f13593e90d', 'Barbara', 'BAIN', '', 'info@maarch.org', '', '0', NULL, NULL, '4c087c76c038bdd5ba9172834b88e8f5', '2012-02-28 10:02:11', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('dmartin', 'ef9689be896dacd901cae4f13593e90d', 'Denis', 'MARTIN', '', 'info@maarch.org', '', '0', NULL, NULL, '4c087c76c038bdd5ba9172834b88e8f5', '2012-02-28 10:02:11', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('superadmin', '17c4520f6cfd1ab53d8745e84681eb49', 'Super', 'ADMIN', '+33 1 47 24 51', 'info@maarch.org', 'Maarch', '11', NULL, NULL, 'e657b3542b0362910db9195cb0fd0fb5', '2012-02-28 10:02:08', 'Y', 'N', NULL, 'OK', 'standard', NULL);

INSERT INTO usergroups (group_id, group_desc, administrator, custom_right1, custom_right2, custom_right3, custom_right4, enabled) VALUES ('ADMINISTRATEUR', 'Administrateurs fonctionnels', 'N', 'N', 'N', 'N', 'N', 'Y');
INSERT INTO usergroups (group_id, group_desc, administrator, custom_right1, custom_right2, custom_right3, custom_right4, enabled) VALUES ('COURRIER', 'Opérateurs de numérisation', 'N', 'N', 'N', 'N', 'N', 'Y');
INSERT INTO usergroups (group_id, group_desc, administrator, custom_right1, custom_right2, custom_right3, custom_right4, enabled) VALUES ('FINANCE', 'Superviseurs finance', 'N', 'N', 'N', 'N', 'N', 'Y');
INSERT INTO usergroups (group_id, group_desc, administrator, custom_right1, custom_right2, custom_right3, custom_right4, enabled) VALUES ('ELU', 'Elus', 'N', 'N', 'N', 'N', 'N', 'Y');
INSERT INTO usergroups (group_id, group_desc, administrator, custom_right1, custom_right2, custom_right3, custom_right4, enabled) VALUES ('RESPONSABLE', 'Responsables de direction', 'N', 'N', 'N', 'N', 'N', 'Y');
INSERT INTO usergroups (group_id, group_desc, administrator, custom_right1, custom_right2, custom_right3, custom_right4, enabled) VALUES ('RESP_COURRIER', 'Superviseurs courrier', 'N', 'N', 'N', 'N', 'N', 'Y');
INSERT INTO usergroups (group_id, group_desc, administrator, custom_right1, custom_right2, custom_right3, custom_right4, enabled) VALUES ('AGENT', 'Agents', 'N', 'N', 'N', 'N', 'N', 'Y');
INSERT INTO usergroups (group_id, group_desc, administrator, custom_right1, custom_right2, custom_right3, custom_right4, enabled) VALUES ('MODIF', 'modification', 'N', 'N', 'N', 'N', 'N', 'Y');

INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ssissoko', 'ADMINISTRATEUR', 'N', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ssissoko', 'RESPONSABLE', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ppruvost', 'AGENT', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ddur', 'ELU', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('eerine', 'AGENT', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ppetit', 'RESP_COURRIER', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ppetit', 'RESPONSABLE', 'N', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ssaporta', 'AGENT', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ttong', 'AGENT', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ccharles', 'AGENT', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ccordy', 'AGENT', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('sstar', 'RESPONSABLE', 'N', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('sstar', 'FINANCE', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('bblier', 'ADMINISTRATEUR', 'N', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('bblier', 'COURRIER', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('bbain', 'AGENT', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('bboule', 'AGENT', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('aackermann', 'AGENT', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('jjane', 'ELU', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('kkaar', 'AGENT', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('mmanfred', 'RESPONSABLE', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('nnataliu', 'AGENT', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('rrenaud', 'RESPONSABLE', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ddaull', 'RESP_COURRIER', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ddaull', 'RESPONSABLE', 'N', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('dmartin', 'AGENT', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('dmartin', 'MODIF', 'N', '');

INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'adv_search_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'my_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'print_details');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'update_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'join_res_case_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'add_copy_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'record_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'adv_search_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_groups');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'view_history');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_status');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_docservers');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'update_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'join_res_case_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'manage_entities');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_templates');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'reports');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'add_copy_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'admin_templates');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('FINANCE', 'admin');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('FINANCE', 'adv_search_invoices');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('FINANCE', 'index_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('FINANCE', 'view_technical_infos');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('FINANCE', 'view_baskets');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('FINANCE', 'join_res_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('FINANCE', 'close_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('FINANCE', 'add_copy_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('FINANCE', 'reports');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ELU', 'print_details');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ELU', 'reports');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'search_customer');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'my_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'view_baskets');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'join_res_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'close_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'add_copy_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'admin_templates');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'record_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'admin');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'search_customer');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'my_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'view_baskets');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'join_res_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'close_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'add_copy_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'admin_templates');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'record_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'search_customer');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'view_technical_infos');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'view_baskets');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'join_res_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'add_cases');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'reports');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'admin');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'search_customer');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'admin_users');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'admin_architecture');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'view_history_batch');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'my_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'admin_docservers');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'view_baskets');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'join_res_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'close_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'adv_search_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'index_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'admin_groups');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'view_history');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'admin_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'reopen_mail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'view_technical_infos');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'update_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'join_res_case_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'add_cases');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'update_list_diff_in_details');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'reports');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'put_in_validation');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('FINANCE', 'adv_search_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('FINANCE', 'search_customer');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('FINANCE', 'my_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('FINANCE', 'print_details');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('FINANCE', 'update_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('FINANCE', 'join_res_case_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('FINANCE', 'add_cases');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('FINANCE', 'update_list_diff_in_details');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('FINANCE', 'record_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ELU', 'view_technical_infos');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ELU', 'view_baskets');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'adv_search_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'index_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'view_technical_infos');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'update_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'join_res_case_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'add_cases');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'update_list_diff_in_details');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'reports');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'adv_search_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'index_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'view_technical_infos');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'update_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'join_res_case_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'add_cases');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'update_list_diff_in_details');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'reports');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_users');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_architecture');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'view_history_batch');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_actions');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'reopen_mail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_baskets');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'join_res_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'close_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'update_list_diff_in_details');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_reports');

INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('VILLE', 'Ville de Choisy-le-Roi', 'Ville de Choisy-le-Roi', 'Y', '', '', '', '', '', '', '', '', '', 'Direction');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('CAB', 'CAB:Cabinet du Maire', 'CAB:Cabinet du Maire', 'Y', '', '', '', '', '', '', '', '', 'VILLE', 'Direction');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('ELUS', 'ELUS:Ensemble des élus', 'ELUS:Ensemble des élus', 'Y', '', '', '', '', '', '', '', '', 'VILLE', 'Direction');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('DSI', 'DGSDSI:Direction des Systèmes d''Information', 'DGSDSI:Direction des Systèmes d''Information', 'Y', '', '', '', '', '', '', '', '', 'DGS', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PCU', 'DGSDAAPCU:Pôle Culturel', 'DGSDAAPCU:Pôle Culturel', 'Y', '', '', '', '', '', '', '', '', 'DAA', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PTE', 'DGSPAAPTE:Pôle Technique', 'DGSPAAPTE:Pôle Technique', 'Y', '', '', '', '', '', '', '', '', 'DAA', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PJS', 'DGSDAAPJS:Pôle Jeunesse et Sport', 'DGSDAAPJS:Pôle Jeunesse et Sport', 'Y', '', '', '', '', '', '', '', '', 'DAA', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PE', 'DGSDAAPJSPE:Petite enfance', 'DGSDAAPJSPE:Petite enfance', 'Y', '', '', '', '', '', '', '', '', 'PJS', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('SP', 'DGSDAAPJSSP:Sport', 'DGSDAAPJSSP:Sport', 'Y', '', '', '', '', '', '', '', '', 'PJS', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('COU', 'DGSDSGCOU:Service Courrier', 'DGSDSGCOU:Service Courrier', 'Y', '', '', '', '', '', '', '', '', 'DSG', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('FIN', 'DGSFIN:Direction des Finances', 'DGSFIN:Direction des Finances', 'Y', '', '', '', '', '', '', '', '', 'DGS', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PJU', 'DGSFINPJU:Pôle Juridique', 'DGSFINPJU:Pôle Juridique', 'Y', '', '', '', '', '', '', '', '', 'FIN', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('DAA', 'DGSDAA:Direction Générale Adjointe', 'DGSDAA:Direction Générale Adjointe', 'Y', '', '', '', '', '', '', '', '', 'DGS', 'Bureau');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('DGS', 'DGS:Direction Générale des Services', 'DGS:Direction Générale des Services', 'Y', '', '', '', '', '', '', '', '', 'VILLE', 'Direction');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PSF', 'DGSPSF:Pôle des Services Fonctionnels', 'DGSPSF:Services Fonctionnels', 'Y', '', '', '', '', '', '', '', '', 'DSG', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PSO', 'DGSDAAPSO:Pôle Social', 'DGSDAAPSO:Pôle Social', 'Y', '', '', '', '', '', '', '', '', 'DAA', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('DSG', 'DGSDSG:Secrétariat Général', 'DGSDSG:Secrétariat Général', 'Y', '', '', '', '', '', '', '', '', 'DGS', 'Direction');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('DRH', 'DGSDRH:Direction des Ressources Humaines', 'DGSDRH:Direction des Ressources Humaines', 'Y', '', '', '', '', '', '', '', '', 'DGS', 'Service');

INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ssissoko', 'DSI', '', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ppruvost', 'DRH', 'Directeur ressources humaines', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('eerina', 'CAB', 'Assistante', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ppetit', 'VILLE', 'Maire', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ccharles', 'PTE', 'Responsable pôle technique', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ccordy', 'DSI', 'Assistante', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('sstar', 'FIN', 'Directrice financière', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('bbain', 'PJS', 'Responsable pôle jeunesse et sport', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('bboule', 'PCU', 'Responsable pôle culturel', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('aackermann', 'PSF', 'Responsable pôle des services fonctionnels', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('jjane', 'ELUS', 'Elue Petite Enfance	', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('kkaar', 'DAA', 'Assistante', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('mmanfred', 'DAA', 'Directeur général adjoint', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('nnataliu', 'PSO', 'Responsable pôle social', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('rrenaud', 'DGS', 'Directeur général des services', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ddur', 'ELUS', 'Elue Chargé des sports', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ddaull', 'DSG', 'Directeur secrétariat général', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('bblier', 'COU', 'Responsable courrier', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ssaporta', 'PE', 'Responsable service de la petite enfance', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ttong', 'SP', 'Responsable service des Sports', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('dmartin', 'PJU', 'Responsable service Juridique', 'Y');

INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'COM', 'entity_id', 0, 'sstar', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'DRS', 'entity_id', 0, 'bboule', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'DSP', 'entity_id', 0, 'bbain', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'CAB', 'entity_id', 0, 'ppetit', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'CAB', 'entity_id', 0, 'eerine', 'user_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'DSI', 'entity_id', 0, 'ccordy', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'DSI', 'entity_id', 0, 'ssissoko', 'user_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'PCU', 'entity_id', 0, 'bboule', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'PTE', 'entity_id', 0, 'ccharles', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'PJS', 'entity_id', 0, 'bbain', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'COU', 'entity_id', 0, 'bblier', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'FIN', 'entity_id', 0, 'sstar', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'FIN', 'entity_id', 0, 'jjane', 'user_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'DAA', 'entity_id', 0, 'mmanfred', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'DAA', 'entity_id', 0, 'kkaar', 'user_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'DGS', 'entity_id', 0, 'rrenaud', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'PE', 'entity_id', 0, 'ssaporta', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'PSF', 'entity_id', 0, 'aackermann', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'PSO', 'entity_id', 0, 'nnataliu', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'DSG', 'entity_id', 0, 'ddaull', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'SP', 'entity_id', 0, 'ttong', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'DRH', 'entity_id', 0, 'ppruvost', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'PJU', 'entity_id', 0, 'dmartin', 'user_id', 'dest', 'DOC');

INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'COM', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'DRS', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'DSP', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'CAB', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'DSI', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'PCU', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'PTE', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'PJS', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'COU', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'FIN', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'DAA', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'PE', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'PSF', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'PSO', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'SP', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'DRH', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'PJU', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC');




--
-- Corbeilles, actions et redirections
--

INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'RejectBillBasket', 'Mes Factures rejetées', 'Corbeille des factures rejetées', 'status=''UNS'' and DEST_USER=@user and type_id in (94,96)', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'WaitingBillBasket', 'Factures à valider', 'Corbeille des factures à valider', 'status=''VAL'' and type_id in (94,96)', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'SignedBasket', 'Courriers visés par la direction', 'Corbeilles des courriers visés, à imprimer', 'status=''SIG'' and DEST_USER = @user', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'LateMailBasket', 'Courriers en retard', 'Courriers en retard', 'destination in (@my_entities, @subentities[@my_primary_entity]) and (status <> ''DEL'' AND status <> ''REP'') and (now() > process_limit_date)', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'ValidBillBasket', 'Mes Factures validées', 'Corbeilles des factures validées', '(status=''NEW'' or status=''COU'') and type_id in (94,96) and DEST_USER=@user', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'SigningBasket', 'Courriers à viser', 'Corbeille des courriers à approuver', 'STATUS = ''VIS'' and DESTINATION in (@subentities[@my_entities])', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'QualificationBasket', 'Courriers à qualifier', 'Corbeille de qualification', 'status=''ATT''', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'IndexingBasket', 'Courriers à indexer', 'Corbeille d''indexation', ' ', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'DepartmentBasket', 'Courriers de ma direction', 'Corbeille de supervision', 'destination in (@my_entities, @subentities[@my_primary_entity]) and (status <> ''DEL'' AND status <> ''REP'' and status <> ''VAL'')', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'UnsignedBasket', 'Courriers réponses rejetés', 'Corbeille des courriers rejetés', 'status=''UNS'' and DEST_USER=@user and type_id not in (94,96)', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'MyBasket', 'Courriers a traiter', 'Corbeille de traitement', '(status =''NEW'' or status =''COU'') and dest_user = @user and type_id not in (94,96)', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'ValidationBasket', 'Courriers à valider', 'Corbeille de validation', '(status = ''VAL'' and destination <>''COU'' and type_id not in (94,96))', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'CopyMailBasket', 'Courriers en copie', 'Corbeille d''information', '(res_id in (select res_id from listinstance WHERE coll_id = ''letterbox_coll'' and item_type = ''user_id'' and item_id = @user and item_mode = ''cc'') or res_id in (select res_id from listinstance WHERE coll_id = ''letterbox_coll'' and item_type = ''entity_id'' and item_mode = ''cc'' and item_id in (@my_entities))) and status <> ''DEL''', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'RetourCourrier', 'Retours Courrier', 'Courriers retournés au service Courrier', 'STATUS=''RET''', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'InitBasket', 'Courriers à initialiser', 'Courriers en attente d envoi en validation', 'STATUS=''INIT''', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'RecordBasket', 'Enregistrer un Courrier', 'Enregistrer un Courrier par les services', ' ', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'ContribBasket', 'Courrier pour contribution', 'Courrier pour contribution', '(doc_custom_t6 = @user or doc_custom_t7 in (@my_entities)) and (status = ''NEW'' or status = ''COU'')', 'N', 'Y');


INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (2, 'to_validate', 'Valider', 'VAL', 'Y', 'N', 'confirm_status', 'N', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (1, 'redirect', 'Rediriger', 'NONE', 'Y', 'Y', 'redirect', 'Y', 'entities', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (19, '', 'Traiter document', 'COU', 'N', 'Y', 'process', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (3, '', 'Retourner au service Courrier', 'RET', 'N', 'Y', 'confirm_status', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (100, '', 'Voir le document', '', 'N', 'Y', 'view', 'N', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (21, 'indexing', 'Indexation', 'INIT', 'N', 'Y', 'index_mlb', 'Y', 'apps', 'Y');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (101, '', 'Envoyer pour visa', 'VIS', 'N', 'Y', '', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (102, '', 'Viser', 'SIG', 'N', 'Y', '', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (103, '', 'Rejeter', 'UNS', 'N', 'Y', '', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (20, '', 'Cloturer', 'END', 'N', 'Y', 'close_mail', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (22, 'indexing', 'Envoyer pour validation', 'VAL', 'N', 'Y', 'validate_mail', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (18, 'indexing', 'Valider courrier', 'NEW', 'N', 'Y', 'validate_mail', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (112, 'indexing', 'Enregistrer un courrier', 'NEW', 'N', 'Y', 'index_mlb', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (113, 'redirect', 'Ajouter en copie', '', 'N', 'Y', 'put_in_copy', 'Y', 'apps', 'N');


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
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('FINANCE', 'SignedBasket', 4, NULL, NULL, 'documents_list', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('FINANCE', 'WaitingBillBasket', 3, NULL, NULL, 'documents_list', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('FINANCE', 'LateMailBasket', 5, NULL, NULL, 'auth_dep', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('FINANCE', 'UnsignedBasket', 6, NULL, NULL, 'documents_list', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('FINANCE', 'CopyMailBasket', 8, NULL, NULL, 'auth_dep', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('FINANCE', 'MyBasket', 9, NULL, NULL, 'auth_dep', 'N', 'N', 'N');

INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('AGENT', 'SignedBasket', 14, NULL, NULL, 'documents_list', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('AGENT', 'RejectBillBasket', 11, NULL, NULL, 'documents_list', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('AGENT', 'LateMailBasket', 1, NULL, NULL, 'auth_dep', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('AGENT', 'ValidBillBasket', 13, NULL, NULL, 'documents_list', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('AGENT', 'UnsignedBasket', 9, NULL, NULL, 'documents_list', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('AGENT', 'CopyMailBasket', 3, NULL, NULL, 'auth_dep', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('AGENT', 'MyBasket', 21, NULL, NULL, 'auth_dep', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('AGENT', 'RecordBasket', 23, NULL, NULL, 'redirect_to_action', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('AGENT', 'ContribBasket', 27, NULL, NULL, 'auth_dep', 'N', 'N', 'N');

INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESPONSABLE', 'SignedBasket', 13, NULL, NULL, 'documents_list', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESPONSABLE', 'ValidAnswerBasket', 7, NULL, NULL, 'documents_list', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESPONSABLE', 'DepartmentBasket', 14, NULL, NULL, 'auth_dep', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESPONSABLE', 'CopyMailBasket', 3, NULL, NULL, 'auth_dep', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESPONSABLE', 'SigningBasket', 15, NULL, NULL, 'documents_list', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESPONSABLE', 'MyBasket', 17, NULL, NULL, 'auth_dep', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESPONSABLE', 'RecordBasket', 20, NULL, NULL, 'redirect_to_action', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESPONSABLE', 'ContribBasket', 23, NULL, NULL, 'auth_dep', 'N', 'N', 'N');

INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESP_COURRIER', 'SigningBasket', 4, NULL, NULL, 'documents_list', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESP_COURRIER', 'ValidationBasket', 8, NULL, NULL, 'documents_list', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESP_COURRIER', 'RetourCourrier', 2, NULL, NULL, 'documents_list', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESP_COURRIER', 'CopyMailBasket', 6, NULL, NULL, 'documents_list', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESP_COURRIER', 'MyBasket', 17, NULL, NULL, 'auth_dep', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESP_COURRIER', 'RecordBasket', 23, NULL, NULL, 'redirect_to_action', 'N', 'N', 'N');

INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('COURRIER', 'InitBasket', 4, NULL, NULL, 'documents_list', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('COURRIER', 'QualificationBasket', 18, NULL, NULL, 'auth_dep', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('COURRIER', 'IndexingBasket', 16, NULL, NULL, 'redirect_to_action', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('COURRIER', 'RetourCourrier', 12, NULL, NULL, 'documents_list', 'N', 'N', 'N');

INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('ELU', 'DepartmentBasket', 1, NULL, NULL, 'auth_dep', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('ELU', 'CopyMailBasket', 2, NULL, NULL, 'auth_dep', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('ELU', 'MyBasket', 4, NULL, NULL, 'auth_dep', 'N', 'N', 'N');

--
-- action_groupbasket
--

INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'FINANCE', 'SignedBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'FINANCE', 'SignedBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (3, '', 'FINANCE', 'CopyMailBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'FINANCE', 'CopyMailBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (103, '', 'FINANCE', 'WaitingBillBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (102, '', 'FINANCE', 'WaitingBillBasket', 'Y', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'FINANCE', 'WaitingBillBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'FINANCE', 'LateMailBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (21, '', 'FINANCE', 'LateMailBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (3, '', 'FINANCE', 'LateMailBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'FINANCE', 'LateMailBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (101, '', 'FINANCE', 'UnsignedBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'FINANCE', 'UnsignedBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (102, '', 'FINANCE', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (103, '', 'FINANCE', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'FINANCE', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'FINANCE', 'MyBasket', 'N', 'N', 'Y');

INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'AGENT', 'SignedBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'AGENT', 'SignedBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (3, '', 'AGENT', 'CopyMailBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (3, '', 'AGENT', 'LateMailBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'AGENT', 'LateMailBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (21, '', 'AGENT', 'LateMailBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'AGENT', 'LateMailBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (101, '', 'AGENT', 'UnsignedBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'AGENT', 'UnsignedBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'AGENT', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (101, '', 'AGENT', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (1, '', 'AGENT', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'AGENT', 'MyBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (112, '', 'AGENT', 'RecordBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (101, '', 'AGENT', 'RecordBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'AGENT', 'ContribBasket', 'N', 'N', 'Y');

INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'RESPONSABLE', 'SignedBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'RESPONSABLE', 'SignedBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'RESPONSABLE', 'CopyMailBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (113, '', 'RESPONSABLE', 'CopyMailBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'RESPONSABLE', 'ValidAnswerBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (102, '', 'RESPONSABLE', 'SigningBasket', 'Y', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (103, '', 'RESPONSABLE', 'SigningBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'RESPONSABLE', 'SigningBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'RESPONSABLE', 'DepartmentBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (3, '', 'RESPONSABLE', 'DepartmentBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'RESPONSABLE', 'DepartmentBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (1, '', 'RESPONSABLE', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'RESPONSABLE', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'RESPONSABLE', 'MyBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (101, '', 'RESPONSABLE', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'RESPONSABLE', 'ContribBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (112, '', 'RESPONSABLE', 'RecordBasket', 'N', 'N', 'Y');

INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'RESP_COURRIER', 'CopyMailBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (21, '', 'RESP_COURRIER', 'RetourCourrier', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (103, '', 'RESP_COURRIER', 'SigningBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (102, '', 'RESP_COURRIER', 'SigningBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'RESP_COURRIER', 'SigningBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'RESP_COURRIER', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (1, '', 'RESP_COURRIER', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (3, '', 'RESP_COURRIER', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'RESP_COURRIER', 'MyBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (101, '', 'RESP_COURRIER', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (3, '', 'RESP_COURRIER', 'ValidationBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (18, '', 'RESP_COURRIER', 'ValidationBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'RESP_COURRIER', 'ValidationBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'RESP_COURRIER', 'SignedBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'RESP_COURRIER', 'SignedBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (112, '', 'RESP_COURRIER', 'RecordBasket', 'N', 'N', 'Y');

INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (21, '', 'COURRIER', 'RetourCourrier', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (22, '', 'COURRIER', 'QualificationBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (21, '', 'COURRIER', 'IndexingBasket', 'N', 'N', 'Y');


INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'ELU', 'DepartmentBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'ELU', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (1, '', 'ELU', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (3, '', 'ELU', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'ELU', 'MyBasket', 'N', 'N', 'Y');

--
-- groupbasket_redirect
--

INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (100, 'COURRIER', 'QualificationBasket', 21, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (101, 'COURRIER', 'QualificationBasket', 22, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (102, 'COURRIER', 'IndexingBasket', 21, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (103, 'COURRIER', 'RetourCourrier', 21, '', 'ALL_ENTITIES', 'ENTITY');

INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (104, 'AGENT', 'LateMailBasket', 21, '', 'ENTITIES_JUST_BELOW', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (105, 'AGENT', 'MyBasket', 1, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (106, 'AGENT', 'RecordBasket', 112, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (107, 'AGENT', 'IndexingBasket', 21, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (108, 'RESPONSABLE', 'recordbasket', 112, '', 'ALL_ENTITIES', 'ENTITY');

INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (109, 'FINANCE', 'LateMailBasket', 21, '', 'ENTITIES_JUST_BELOW', 'ENTITY');

INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (110, 'RESP_COURRIER', 'ValidationBasket', 18, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (111, 'RESP_COURRIER', 'RetourCourrier', 21, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (112, 'RESP_COURRIER', 'MyBasket', 1, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (113, 'RESP_COURRIER', 'RetourCourrier', 1, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (114, 'RESP_COURRIER', 'recordbasket', 112, '', 'ALL_ENTITIES', 'ENTITY');

INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (115, 'ELU', 'MyBasket', 1, '', 'ALL_ENTITIES', 'ENTITY');

INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (116, 'RESPONSABLE', 'MyBasket', 1, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (117, 'RESPONSABLE', 'recordbasket', 112, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (118, 'RESPONSABLE', 'CopyMailBasket', 113, '', 'ALL_ENTITIES', 'USERS');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (119, 'RESPONSABLE', 'CopyMailBasket', 113, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (120, 'RESPONSABLE', 'IndexingBasket', 21, '', 'ALL_ENTITIES', 'ENTITY');

--
-- Plan de classement
--
INSERT INTO foldertypes (foldertype_id, foldertype_label, maarch_comment, retention_time, custom_d1, custom_f1, custom_n1, custom_t1, custom_d2, custom_f2, custom_n2, custom_t2, custom_d3, custom_f3, custom_n3, custom_t3, custom_d4, custom_f4, custom_n4, custom_t4, custom_d5, custom_f5, custom_n5, custom_t5, custom_d6, custom_t6, custom_d7, custom_t7, custom_d8, custom_t8, custom_d9, custom_t9, custom_d10, custom_t10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, coll_id) VALUES (5, 'Courriers', '', NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'letterbox_coll');

INSERT INTO doctypes_first_level (doctypes_first_level_id, doctypes_first_level_label, css_style, enabled) VALUES (30, 'Achats', 'orange_style_big', 'Y');
INSERT INTO doctypes_first_level (doctypes_first_level_id, doctypes_first_level_label, css_style, enabled) VALUES (20, 'GDD', 'black_style_big', 'Y');
INSERT INTO doctypes_first_level (doctypes_first_level_id, doctypes_first_level_label, css_style, enabled) VALUES (10, 'Courriers des services', 'blue_style_big', 'Y');

INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (10, 'Autres', 10, 'blue_style', 'Y');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (20, 'Dossiers civils', 10, 'blue_style', 'Y');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (30, 'Dossiers du Personnel', 10, 'blue_style', 'Y');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (40, 'Dossiers juridiques', 10, 'blue_style', 'Y');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (50, 'Factures', 20, 'orange_style', 'Y');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (60, 'Engagements', 20, 'orange_style', 'Y');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (70, 'Demandes GDD', 30, 'black_style', 'Y');

INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 10, 'Appel Téléphonique', 'Y', 10, 10, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 15, 'Divers', 'Y', 10, 10, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 16, 'Demande	', 'Y', 10, 10, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 17, 'Convocation	', 'Y', 10, 10, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 20, 'Invitation', 'Y', 10, 10, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 25, 'Rapport ou compte-rendu', 'Y', 10, 10, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 26, 'Abonnement', 'Y', 10, 10, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 27, 'Offre commerciale', 'Y', 10, 10, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 28, 'Réponse', 'Y', 10, 10, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 30, 'Arrêté municipal', 'Y', 10, 20, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 35, 'Délibération', 'Y', 10, 20, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 40, 'Etat civil', 'Y', 10, 20, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 45, 'Candidature', 'Y', 10, 30, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 50, 'Contrat de travail', 'Y', 10, 30, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 55, 'Contravention', 'Y', 10, 40, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 60, 'Extrait de main courante', 'Y', 10, 40, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 65, 'Réclamation', 'Y', 10, 40, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 66, 'Litige', 'Y', 10, 40, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 70, 'Contrat', 'Y', 10, 40, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 75, 'Avenant', 'Y', 10, 40, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 76, 'Préavis de grève', 'Y', 10, 40, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 80, 'Demande Divers', 'Y', 20, 70, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 85, 'Demande Environnement', 'Y', 20, 70, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 90, 'Demande Urbanisme', 'Y', 20, 70, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 95, 'Bon de commande', 'Y', 30, 60, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 100, 'Facture fournisseur', 'Y', 30, 60, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 105, 'Devis', 'Y', 30, 60, NULL, NULL);

INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (100, 'letterbox_coll', 'custom_t1', 'Y');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (100, 'letterbox_coll', 'custom_f1', 'Y');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (66, 'letterbox_coll', 'custom_t6', 'N');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (66, 'letterbox_coll', 'custom_t7', 'N');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (17, 'letterbox_coll', 'custom_t6', 'N');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (17, 'letterbox_coll', 'custom_t7', 'N');

INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (10, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (15, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (16, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (17, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (20, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (25, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (26, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (27, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (28, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (30, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (35, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (40, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (45, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (50, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (55, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (60, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (65, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (66, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (70, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (75, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (76, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (80, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (85, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (90, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (95, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (100, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (105, 21, 14, 1);

INSERT INTO templates_doctype_ext (template_id, type_id, is_generated) VALUES (3, 10, 'Y');


--
-- Folders
--

INSERT INTO foldertypes_doctypes_level1 (foldertype_id, doctypes_first_level_id) VALUES (5, 10);
INSERT INTO foldertypes_doctypes_level1 (foldertype_id, doctypes_first_level_id) VALUES (5, 20);
INSERT INTO foldertypes_doctypes_level1 (foldertype_id, doctypes_first_level_id) VALUES (5, 30);

INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (20, 'LITIGE', 5, 0, 'Litiges tous domaines', NULL, NULL, NULL, 'superadmin', 'NEW', 1, '2012-03-02 18:30:40.311', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:30:40.311');
INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (21, 'LF', 5, 20, 'Litige financier', NULL, NULL, NULL, 'superadmin', 'NEW', 2, '2012-03-02 18:30:58.112', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:30:58.112');
INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (22, 'LE', 5, 20, 'Litige enfance', NULL, NULL, NULL, 'superadmin', 'NEW', 2, '2012-03-02 18:31:13.79', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:31:13.79');
INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (23, 'COURRIERS', 5, 0, 'Courriers', NULL, NULL, NULL, 'superadmin', 'NEW', 1, '2012-03-02 18:31:27.487', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:31:27.487');
INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (24, 'JSP', 5, 0, 'Jeunesse et Sport', NULL, NULL, NULL, 'superadmin', 'NEW', 1, '2012-03-02 18:32:07.22', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:32:07.22');
INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (25, 'CLUB', 5, 24, 'Clubs et associations sportives', NULL, NULL, NULL, 'superadmin', 'NEW', 2, '2012-03-02 18:32:24.602', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:32:24.602');
INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (26, 'ETB', 5, 24, 'Etablissements scolaires', NULL, NULL, NULL, 'superadmin', 'NEW', 2, '2012-03-02 18:32:43.228', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:32:43.228');
INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (27, 'PI', 5, 23, 'Partenariat institutionnel', NULL, NULL, NULL, 'superadmin', 'NEW', 2, '2012-03-02 18:33:01.543', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:33:01.543');
INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (28, 'LP', 5, 23, 'LaPoste', NULL, NULL, NULL, 'superadmin', 'NEW', 2, '2012-03-02 18:33:17.705', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:33:17.705');
INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (29, 'PSF', 5, 0, 'POLE SERVICES FONCTIONNELS', NULL, NULL, NULL, 'superadmin', 'NEW', 1, '2012-03-02 18:32:07.22', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:32:07.22');
INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (30, 'PSF - BUDGET', 5, 29, 'BUDGET', NULL, NULL, NULL, 'superadmin', 'NEW', 2, '2012-03-02 18:32:07.22', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:32:07.22');




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
INSERT INTO security (security_id, group_id, coll_id, where_clause, maarch_comment, can_insert, can_update, can_delete, rights_bitmask, mr_start_date, mr_stop_date, where_target) VALUES (42, 'COURRIER', 'letterbox_coll', 'typist=@user', 'COURRIER', 'N', 'N', 'N', 153, NULL, NULL, 'DOC');
INSERT INTO security (security_id, group_id, coll_id, where_clause, maarch_comment, can_insert, can_update, can_delete, rights_bitmask, mr_start_date, mr_stop_date, where_target) VALUES (43, 'FINANCE', 'letterbox_coll', 'DESTINATION in (@subentities[@my_entities])', 'FINANCE', 'N', 'N', 'N', 0, NULL, NULL, 'DOC');
INSERT INTO security (security_id, group_id, coll_id, where_clause, maarch_comment, can_insert, can_update, can_delete, rights_bitmask, mr_start_date, mr_stop_date, where_target) VALUES (44, 'ELU', 'letterbox_coll', 'DEST_USER=@user', 'ELU', 'N', 'N', 'N', 0, NULL, NULL, 'DOC');
INSERT INTO security (security_id, group_id, coll_id, where_clause, maarch_comment, can_insert, can_update, can_delete, rights_bitmask, mr_start_date, mr_stop_date, where_target) VALUES (45, 'RESPONSABLE', 'letterbox_coll', 'DESTINATION = @my_primary_entity or DESTINATION in (@subentities[@my_primary_entity])', 'RESPONSABLE', 'N', 'N', 'N', 128, NULL, NULL, 'DOC');
INSERT INTO security (security_id, group_id, coll_id, where_clause, maarch_comment, can_insert, can_update, can_delete, rights_bitmask, mr_start_date, mr_stop_date, where_target) VALUES (46, 'RESP_COURRIER', 'letterbox_coll', 'DESTINATION IN (@subentities[@my_entities])', 'Profil responsable', 'N', 'N', 'N', 153, NULL, NULL, 'DOC');
INSERT INTO security (security_id, group_id, coll_id, where_clause, maarch_comment, can_insert, can_update, can_delete, rights_bitmask, mr_start_date, mr_stop_date, where_target) VALUES (47, 'AGENT', 'letterbox_coll', 'DESTINATION in (@my_entities)', 'AGENT', 'N', 'N', 'N', 0, NULL, NULL, 'DOC');
INSERT INTO security (security_id, group_id, coll_id, where_clause, maarch_comment, can_insert, can_update, can_delete, rights_bitmask, mr_start_date, mr_stop_date, where_target) VALUES (49, 'RESP_COURRIER', 'letterbox_coll', 'DESTINATION IN (@subentities[@my_entities])', 'Profil responsable', 'N', 'N', 'N', 153, NULL, NULL, 'DOC');
INSERT INTO security (security_id, group_id, coll_id, where_clause, maarch_comment, can_insert, can_update, can_delete, rights_bitmask, mr_start_date, mr_stop_date, where_target) VALUES (50, 'MODIF', 'letterbox_coll', 'DESTINATION in (@my_entities)', 'modification', 'N', 'N', 'N', 8, NULL, NULL, 'DOC');

--
-- Modèles et notification
--

INSERT INTO templates (id, label, creation_date, template_comment, content) VALUES (2, 'AR_MAARCH', '2009-08-20 16:01:00', 'Accusé de réception Maarch', '<p style="TEXT-ALIGN: left"><img src="img/default_maarch.gif" alt="" width="278" height="80" />&nbsp;</p>
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
INSERT INTO templates (id, label, creation_date, template_comment, content) VALUES (1, 'TEST', '2009-08-20 16:01:00', 'Test des mots-clés', '<h1>Liste des mots-cl&eacute;s utilisables dans les mod&egrave;les de reponse</h1>
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
INSERT INTO templates (id, label, creation_date, template_comment, content) VALUES (3, 'AppelTel', '2011-06-06 11:38:48.126', 'Appel téléphonique', '<p><font size="\\&quot;5\\&quot;"><strong>APPEL TELEPHONIQUE</strong></font></p>
<p><font size="\\&quot;2\\&quot;">Bonjour,</font></p>
<p><font size="\\&quot;2\\&quot;">Vous avez re&ccedil;u un appel t&eacute;l&eacute;phonique dont voici les informations :</font></p>
<ul>
<li><font size="\\&quot;2\\&quot;">Date : </font></li>
<li><font size="\\&quot;2\\&quot;">Heure :</font></li>
<li><font size="\\&quot;2\\&quot;">Soci&eacute;t&eacute; :</font></li>
<li><font size="\\&quot;2\\&quot;">Contact :</font></li>
</ul>
<p><font size="\\&quot;2\\&quot;">Notes : </font></p>');
INSERT INTO templates (id, label, creation_date, template_comment, content) VALUES (20, 'Notifications événement', '2012-01-27 10:41:34.237', 'Test des notifications événements système', '<p>{#translate._HELLO} {#mergefield.recipient.firstname} {#mergefield.recipient.lastname},</p>
<p>&nbsp;</p>
<p>Voici la liste des &eacute;v&eacute;nements {#mergefield.template_association.description} :</p>
<p>&nbsp;</p>
<p>{#mergetable.evenements}</p>');
INSERT INTO templates (id, label, creation_date, template_comment, content) VALUES (21, 'Diffusion de courrier', '2012-02-06 10:14:49.088', 'Notification de diffusion de courrier', '<p>Bonjour {#mergefield.recipient.firstname} {#mergefield.recipient.lastname},</p>
<p>&nbsp;</p>
<p>Voici la liste des courriers qui vous ont &eacute;t&eacute; envoy&eacute;s :</p>
<p>&nbsp;</p>
<p>{#mergetable.courriers}</p>
<p>&nbsp;</p>');


INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (20, 2, 'destination', 'VILLE', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (21, 2, 'destination', 'CAB', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (22, 2, 'destination', 'DGS', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (23, 2, 'destination', 'DSI', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (24, 2, 'destination', 'FIN', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (25, 2, 'destination', 'DAA', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (26, 2, 'destination', 'PCU', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (27, 2, 'destination', 'PTE', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (28, 2, 'destination', 'PJS', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (29, 2, 'destination', 'PE', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (30, 2, 'destination', 'SP', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (31, 2, 'destination', 'PSO', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (32, 2, 'destination', 'DSG', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (33, 2, 'destination', 'COU', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (34, 2, 'destination', 'PSF', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (35, 2, 'destination', 'DRH', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (36, 1, 'destination', 'VILLE', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (37, 1, 'destination', 'CAB', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (38, 1, 'destination', 'DGS', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (39, 1, 'destination', 'DSI', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (40, 1, 'destination', 'FIN', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (41, 1, 'destination', 'DAA', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (42, 1, 'destination', 'PCU', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (43, 1, 'destination', 'PTE', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (44, 1, 'destination', 'PJS', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (45, 1, 'destination', 'PE', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (46, 1, 'destination', 'SP', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (47, 1, 'destination', 'PSO', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (48, 1, 'destination', 'DSG', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (49, 1, 'destination', 'COU', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (50, 1, 'destination', 'PSF', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (51, 1, 'destination', 'DRH', 'entities', NULL, NULL, NULL, NULL, NULL, 'N');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module, notification_id, description, diffusion_type, diffusion_properties, diffusion_content, is_attached) VALUES (52, 21, 'event', '18', 'notifications', 'NOTIF_VALIDATION', 'Nouveau courrier validé', 'dest_user', '', 'letterbox', 'Y');


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

INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) VALUES ('OFFLINE_1', 'OFFLINE', 'Off line tape', 'N', 'Y', 50000000000, 1, 'C:\\maarch\\docservers\\entreprise_trunk\\offline\\', NULL, NULL, NULL, '2011-01-13 16:58:24.00929', NULL, 'res_coll', 10, 'NANTERRE', 4);
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) VALUES ('FASTHD_AI', 'FASTHD', 'Fast internal disc bay for autoimport', 'N', 'Y', 50000000000, 1, 'C:\\maarch\\docservers\\entreprise_trunk\\ai\\', NULL, NULL, NULL, '2011-01-07 13:43:48.696644', NULL, 'res_coll', 20, 'NANTERRE', 1);
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) VALUES ('OAIS_MAIN_1', 'OAIS_MAIN', 'Main OAIS store', 'N', 'Y', 50000000000, 1, 'C:\\maarch\\docservers\\entreprise_trunk\\OAIS_main\\', NULL, NULL, NULL, '2011-01-13 14:48:27.901368', NULL, 'res_coll', 10, 'NANTERRE', 2);
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) VALUES ('OAIS_SAFE_1', 'OAIS_SAFE', 'Distant backup OAIS store', 'N', 'Y', 50000000000, 1, 'C:\\maarch\\docservers\\entreprise_trunk\\OAIS_safe\\', NULL, NULL, NULL, '2011-01-13 14:49:05.095119', NULL, 'res_coll', 10, 'NICE', 3);
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) VALUES ('FASTHD_MAN', 'FASTHD', 'Fast internal disc bay for letterbox mode', 'N', 'Y', 50000000000, 1, 'C:\\maarch\\docservers\\entreprise_trunk\\manual\\', NULL, NULL, NULL, '2011-01-13 14:47:49.197164', NULL, 'letterbox_coll', 10, 'NANTERRE', 2);

--
-- annuaire/contacts
--

INSERT INTO contacts (contact_id, lastname, firstname, society, function, address_num, address_street, address_complement, address_town, address_postal_code, address_country, email, phone, other_data, is_corporate_person, user_id, title, enabled) VALUES (108, '', '', 'LA POSTE', '', '', '', '', '', '', '', '', '', '', 'Y', '', 'title1', 'Y');
INSERT INTO contacts (contact_id, lastname, firstname, society, function, address_num, address_street, address_complement, address_town, address_postal_code, address_country, email, phone, other_data, is_corporate_person, user_id, title, enabled) VALUES (109, '', '', 'FRANCE TELECOM', '', '', '', '', '', '', '', '', '', '', 'Y', '', 'title1', 'Y');
INSERT INTO contacts (contact_id, lastname, firstname, society, function, address_num, address_street, address_complement, address_town, address_postal_code, address_country, email, phone, other_data, is_corporate_person, user_id, title, enabled) VALUES (110, '', '', 'Transport Choisy', '', '', '', '', '', '', '', '', '', '', 'Y', '', 'title1', 'Y');




--
-- Archivage physique
--

INSERT INTO ar_batch (arbatch_id, title, subject, description, arbox_id, status, creation_date, retention_time, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_t7, custom_t8, custom_t9, custom_t10, custom_t11) VALUES (1, '1', NULL, NULL, 1, 'NEW', '2009-09-16 18:26:27.979', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'bblier', NULL, NULL, NULL, NULL, NULL, NULL, '2009-09-16 18:26:27.979', NULL, NULL, NULL, NULL, 'LETTERBOX', NULL, NULL, NULL, NULL, NULL);

INSERT INTO ar_boxes (arbox_id, title, subject, description, entity_id, arcontainer_id, status, creation_date, retention_time, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_t7, custom_t8, custom_t9, custom_t10, custom_t11) VALUES (1, 'Boite ENTRANT 001', NULL, NULL, NULL, 0, 'NEW', '2009-09-16 15:59:34.436', NULL, NULL, NULL, NULL, NULL, 'PA', NULL, NULL, NULL, 'superadmin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO ar_boxes (arbox_id, title, subject, description, entity_id, arcontainer_id, status, creation_date, retention_time, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_t7, custom_t8, custom_t9, custom_t10, custom_t11) VALUES (2, 'Boite ENTRANT 002', NULL, NULL, NULL, 0, 'NEW', '2009-09-16 15:59:54.176', NULL, NULL, NULL, NULL, NULL, 'PA', NULL, NULL, NULL, 'superadmin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO ar_boxes (arbox_id, title, subject, description, entity_id, arcontainer_id, status, creation_date, retention_time, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_t7, custom_t8, custom_t9, custom_t10, custom_t11) VALUES (3, 'Boite SORTANT 001', NULL, NULL, NULL, 0, 'NEW', '2009-09-16 16:00:07.569', NULL, NULL, NULL, NULL, NULL, 'PA', NULL, NULL, NULL, 'superadmin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO ar_boxes (arbox_id, title, subject, description, entity_id, arcontainer_id, status, creation_date, retention_time, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_t7, custom_t8, custom_t9, custom_t10, custom_t11) VALUES (4, 'Boite INTERNE 001', NULL, NULL, NULL, 0, 'NEW', '2009-09-16 16:00:29.896', NULL, NULL, NULL, NULL, NULL, 'PA', NULL, NULL, NULL, 'superadmin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO ar_boxes (arbox_id, title, subject, description, entity_id, arcontainer_id, status, creation_date, retention_time, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_t7, custom_t8, custom_t9, custom_t10, custom_t11) VALUES (5, 'Boite PROJET 001', NULL, NULL, NULL, 0, 'NEW', '2009-09-16 16:01:00.765', NULL, NULL, NULL, NULL, NULL, 'PA', NULL, NULL, NULL, 'superadmin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

INSERT INTO ar_container_types (ctype_id, ctype_desc, size_x, size_y, size_z) VALUES ('BOITE', 'Boite archive standard', 0, 0, 0);
INSERT INTO ar_container_types (ctype_id, ctype_desc, size_x, size_y, size_z) VALUES ('CONTENEUR', 'Conteneur de 5 boites', 0, 0, 0);

INSERT INTO ar_natures (arnature_id, arnature_desc, arnature_retention, entity_id, enabled) VALUES ('DOSPROJ', 'Dossiers de projet', 10, 'COR', 'Y');
INSERT INTO ar_natures (arnature_id, arnature_desc, arnature_retention, entity_id, enabled) VALUES ('DOSTECH', 'Dossiers techniques', 10, 'COR', 'Y');
INSERT INTO ar_natures (arnature_id, arnature_desc, arnature_retention, entity_id, enabled) VALUES ('DOSRH', 'Dossiers RH', 30, 'COR', 'Y');
INSERT INTO ar_natures (arnature_id, arnature_desc, arnature_retention, entity_id, enabled) VALUES ('DOSACC', 'Dossiers comptables', 10, 'COR', 'Y');

INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (1, 'FR01', 'A', 1, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (2, 'FR01', 'A', 1, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (3, 'FR01', 'A', 1, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (4, 'FR01', 'A', 2, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (5, 'FR01', 'A', 2, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (6, 'FR01', 'A', 2, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (7, 'FR01', 'A', 3, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (8, 'FR01', 'A', 3, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (9, 'FR01', 'A', 3, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (10, 'FR01', 'A', 4, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (11, 'FR01', 'A', 4, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (12, 'FR01', 'A', 4, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (13, 'FR01', 'A', 5, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (14, 'FR01', 'A', 5, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (15, 'FR01', 'A', 5, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (16, 'FR01', 'A', 6, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (17, 'FR01', 'A', 6, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (18, 'FR01', 'A', 6, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (19, 'FR01', 'A', 7, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (20, 'FR01', 'A', 7, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (21, 'FR01', 'A', 7, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (22, 'FR01', 'A', 8, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (23, 'FR01', 'A', 8, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (24, 'FR01', 'A', 8, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (25, 'FR01', 'A', 9, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (26, 'FR01', 'A', 9, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (27, 'FR01', 'A', 9, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (28, 'FR01', 'A', 10, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (29, 'FR01', 'A', 10, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (30, 'FR01', 'A', 10, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (31, 'FR01', 'B', 1, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (32, 'FR01', 'B', 1, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (33, 'FR01', 'B', 1, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (34, 'FR01', 'B', 2, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (35, 'FR01', 'B', 2, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (36, 'FR01', 'B', 2, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (37, 'FR01', 'B', 3, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (38, 'FR01', 'B', 3, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (39, 'FR01', 'B', 3, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (40, 'FR01', 'B', 4, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (41, 'FR01', 'B', 4, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (42, 'FR01', 'B', 4, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (43, 'FR01', 'B', 5, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (44, 'FR01', 'B', 5, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (45, 'FR01', 'B', 5, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (46, 'FR01', 'B', 6, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (47, 'FR01', 'B', 6, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (48, 'FR01', 'B', 6, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (49, 'FR01', 'B', 7, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (50, 'FR01', 'B', 7, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (51, 'FR01', 'B', 7, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (52, 'FR01', 'B', 8, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (53, 'FR01', 'B', 8, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (54, 'FR01', 'B', 8, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (55, 'FR01', 'B', 9, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (56, 'FR01', 'B', 9, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (57, 'FR01', 'B', 9, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (58, 'FR01', 'B', 10, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (59, 'FR01', 'B', 10, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (60, 'FR01', 'B', 10, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (61, 'FR01', 'C', 1, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (62, 'FR01', 'C', 1, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (63, 'FR01', 'C', 1, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (64, 'FR01', 'C', 2, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (65, 'FR01', 'C', 2, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (66, 'FR01', 'C', 2, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (67, 'FR01', 'C', 3, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (68, 'FR01', 'C', 3, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (69, 'FR01', 'C', 3, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (70, 'FR01', 'C', 4, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (71, 'FR01', 'C', 4, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (72, 'FR01', 'C', 4, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (73, 'FR01', 'C', 5, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (74, 'FR01', 'C', 5, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (75, 'FR01', 'C', 5, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (76, 'FR01', 'C', 6, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (77, 'FR01', 'C', 6, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (78, 'FR01', 'C', 6, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (79, 'FR01', 'C', 7, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (80, 'FR01', 'C', 7, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (81, 'FR01', 'C', 7, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (82, 'FR01', 'C', 8, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (83, 'FR01', 'C', 8, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (84, 'FR01', 'C', 8, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (85, 'FR01', 'C', 9, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (86, 'FR01', 'C', 9, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (87, 'FR01', 'C', 9, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (88, 'FR01', 'C', 10, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (89, 'FR01', 'C', 10, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (90, 'FR01', 'C', 10, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (91, 'FR01', 'D', 1, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (92, 'FR01', 'D', 1, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (93, 'FR01', 'D', 1, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (94, 'FR01', 'D', 2, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (95, 'FR01', 'D', 2, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (96, 'FR01', 'D', 2, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (97, 'FR01', 'D', 3, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (98, 'FR01', 'D', 3, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (99, 'FR01', 'D', 3, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (100, 'FR01', 'D', 4, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (101, 'FR01', 'D', 4, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (102, 'FR01', 'D', 4, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (103, 'FR01', 'D', 5, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (104, 'FR01', 'D', 5, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (105, 'FR01', 'D', 5, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (106, 'FR01', 'D', 6, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (107, 'FR01', 'D', 6, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (108, 'FR01', 'D', 6, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (109, 'FR01', 'D', 7, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (110, 'FR01', 'D', 7, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (111, 'FR01', 'D', 7, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (112, 'FR01', 'D', 8, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (113, 'FR01', 'D', 8, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (114, 'FR01', 'D', 8, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (115, 'FR01', 'D', 9, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (116, 'FR01', 'D', 9, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (117, 'FR01', 'D', 9, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (118, 'FR01', 'D', 10, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (119, 'FR01', 'D', 10, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (120, 'FR01', 'D', 10, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (121, 'FR01', 'E', 1, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (122, 'FR01', 'E', 1, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (123, 'FR01', 'E', 1, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (124, 'FR01', 'E', 2, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (125, 'FR01', 'E', 2, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (126, 'FR01', 'E', 2, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (127, 'FR01', 'E', 3, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (128, 'FR01', 'E', 3, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (129, 'FR01', 'E', 3, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (130, 'FR01', 'E', 4, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (131, 'FR01', 'E', 4, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (132, 'FR01', 'E', 4, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (133, 'FR01', 'E', 5, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (134, 'FR01', 'E', 5, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (135, 'FR01', 'E', 5, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (136, 'FR01', 'E', 6, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (137, 'FR01', 'E', 6, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (138, 'FR01', 'E', 6, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (139, 'FR01', 'E', 7, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (140, 'FR01', 'E', 7, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (141, 'FR01', 'E', 7, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (142, 'FR01', 'E', 8, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (143, 'FR01', 'E', 8, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (144, 'FR01', 'E', 8, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (145, 'FR01', 'E', 9, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (146, 'FR01', 'E', 9, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (147, 'FR01', 'E', 9, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (148, 'FR01', 'E', 10, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (149, 'FR01', 'E', 10, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (150, 'FR01', 'E', 10, 3, 4, 4);

INSERT INTO ar_sites (site_id, site_desc, entity_id) VALUES ('FR01', 'Site de Paris', 'COU');
INSERT INTO ar_sites (site_id, site_desc, entity_id) VALUES ('DK01', 'Site de Dakar', 'COU');


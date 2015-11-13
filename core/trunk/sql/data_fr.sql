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

-- *************************************************************************************************************************************************************************************************************************** --
-- *************************************************************************************************************************************************************************************************************************** --
-- *************************************************************************************************************************************************************************************************************************** --
-- *************************************************************************************************************************************************************************************************************************** --
-- *************************************************************************************************************************************************************************************************************************** --

-- ************************************************************************* --
--                                                                           --
--                  COMMON DATAS                                          --
--                                                                           --
-- ************************************************************************* --

------------
--DOCSERVER_LOCATIONS--
------------
INSERT INTO docserver_locations (docserver_location_id, ipv4, ipv6, net_domain, mask, net_link, enabled) VALUES ('NANTERRE', '127.0.0.1', '', 'MAARCH', '255.255.255.0', NULL, 'Y');
INSERT INTO docserver_locations (docserver_location_id, ipv4, ipv6, net_domain, mask, net_link, enabled) VALUES ('NICE', '192.168.21.63', '', '', '', NULL, 'Y');

------------
--DOCSERVER_TYPES-
------------
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, is_container, container_max_number, is_compressed, compression_mode, is_meta, meta_template, is_logged, log_template, is_signed, fingerprint_mode) VALUES ('FASTHD', 'FASTHD', 'Y', 'N', 0, 'N', 'NONE', 'N', 'NONE', 'N', 'NONE', 'Y', 'SHA256');
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, is_container, container_max_number, is_compressed, compression_mode, is_meta, meta_template, is_logged, log_template, is_signed, fingerprint_mode) VALUES ('TEMPLATES', 'TEMPLATES', 'Y', 'N', 0, 'N', 'NONE', 'N', 'NONE', 'N', 'NONE', 'N', 'NONE');
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, is_container, container_max_number, is_compressed, compression_mode, is_meta, meta_template, is_logged, log_template, is_signed, fingerprint_mode) VALUES ('OAIS_MAIN', 'Main OAIS store', 'Y', 'Y', 100, 'Y', '7Z', 'Y', 'OAIS_std.dtd', 'Y', 'log_std.dtd', 'Y', 'SHA512');
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, is_container, container_max_number, is_compressed, compression_mode, is_meta, meta_template, is_logged, log_template, is_signed, fingerprint_mode) VALUES ('OFFLINE', 'Off line tape', 'Y', 'Y', 1000, 'Y', '7Z', 'Y', 'OAIS_std.dtd', 'Y', 'log_std.dtd', 'Y', 'SHA512');
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, is_container, container_max_number, is_compressed, compression_mode, is_meta, meta_template, is_logged, log_template, is_signed, fingerprint_mode) VALUES ('OAIS_SAFE', 'Distant backup OAIS store', 'Y', 'Y', 20, 'Y', 'ZIP', 'Y', 'OAIS_std.dtd', 'Y', 'log_std.dtd', 'Y', 'SHA512');
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, is_container, container_max_number, is_compressed, compression_mode, is_meta, meta_template, is_logged, log_template, is_signed, fingerprint_mode) VALUES ('TNL', 'Thumbnails', 'Y', 'N', 0, 'N', 'NONE', 'N', 'NONE', 'N', 'NONE', 'Y', 'NONE');
------------
--DOCSERVERS-
------------
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) VALUES ('TEMPLATES', 'TEMPLATES', '[system] Templates', 'N', 'Y', 50000000000, 1, '/opt/maarch/docservers/templates/', NULL, NULL, NULL, '2012-04-01 14:49:05.095119', NULL, 'templates', 1, 'NANTERRE', 1);
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) VALUES ('TNL', 'TNL', 'Server for thumbnails of documents', 'N', 'Y', 50000000000, 0, '/opt/maarch/docservers/thumbnails_mlb/', NULL, NULL, NULL, '2015-03-16 14:47:49.197164', NULL, 'letterbox_coll', 11, 'NANTERRE', 3);

------------
--USERS-
------------
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('superadmin', '964a5502faec7a27f63ab5f7bddbe1bd8a685616a90ffcba633b5ad404569bd8fed4693cc00474a4881f636f3831a3e5a36bda049c568a89cfe54b1285b0c13e', 'Super', 'ADMIN', '0147245159', 'info@maarch.org', 'Maarch', '11', NULL, NULL, 'e657b3542b0362910db9195cb0fd0fb5', '2012-02-28 10:02:08', 'Y', 'N', NULL, 'OK', 'standard', NULL);

------------
--USERS_ENTITIES-
------------
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ggrand', 'COR', 'Responsable correspondant Archive', 'Y');

------------
--STATUS-
------------
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('COU', 'En cours', 'Y', 'fm-letter-status-inprogress', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('DEL', 'Supprimé', 'Y', 'fm-letter-del', 'apps', 'N', 'Y');
INSERT INTO status VALUES ('END', 'Clos / fin du workflow', 'Y', 'N', 'fm-letter-status-end', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('NEW', 'Nouveau', 'Y', 'fm-letter-status-new', 'apps', 'Y', 'Y');
INSERT INTO status VALUES ('RET', 'Retour courrier ou document en qualification', 'N', 'N', 'fm-letter-status-rejected', 'apps', 'Y', 'Y');
INSERT INTO status VALUES ('SIG', 'A signer', 'N', 'N', 'fm-file-fingerprint', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('UNS', 'Rejeté', 'N', 'fm-letter-status-rejected', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('VAL', 'A Valider', 'Y', 'fm-letter-status-aval', 'apps', 'Y', 'Y');
INSERT INTO status VALUES ('INIT', 'Nouveau courrier ou document non qualifié', 'Y', 'N', 'fm-letter-status-attr', 'apps', 'Y', 'Y');
INSERT INTO status VALUES ('VIS', 'A viser', 'N', 'N', 'fm-letter-status-aval', 'apps', 'Y', 'Y');

INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('SMART', 'Nouvelle demande Maarch Mairie', 'N', '', 'apps', 'Y', 'N');

---------------
-- PJ STATUS --
---------------

INSERT INTO status (id, label_status, is_system) VALUES ('A_TRA', 'A traiter', 'N');
INSERT INTO status (id, label_status, is_system) VALUES ('TRA', 'Traité', 'N');
INSERT INTO status (id, label_status, is_system) VALUES ('OBS', 'Obsolète', 'N');

------------
--PARAMETERS--
------------
INSERT INTO parameters (id, param_value_string, param_value_int, param_value_date) VALUES ('apa_reservation_batch', NULL, 1, NULL);
INSERT INTO parameters (id, param_value_string, param_value_int, param_value_date) VALUES ('workbatch_rec', '', 7, NULL);
INSERT INTO parameters (id, param_value_string, param_value_int, param_value_date) VALUES ('folder_id_increment', '', 200, NULL);
INSERT INTO parameters (id, param_value_string, param_value_int, param_value_date) VALUES ('work_batch_autoimport_id', NULL, 1, NULL);
INSERT INTO parameters (id, param_value_string, param_value_int, param_value_date) VALUES ('postindexing_workbatch', NULL, 40, NULL);
INSERT INTO parameters (id, param_value_string, param_value_int, param_value_date) VALUES ('database_version', NULL, 150, NULL);

------------------
--CONTACTS_TYPES--
------------------

INSERT INTO contact_types (id, label, contact_target) VALUES (100, '1. Entreprises', 'corporate');
INSERT INTO contact_types (id, label, contact_target) VALUES (101, '2. Associations', 'both');
INSERT INTO contact_types (id, label, contact_target) VALUES (102, '3. Administrations', 'corporate');
INSERT INTO contact_types (id, label, contact_target) VALUES (103, '4. Collectivités territoriales', 'corporate');
INSERT INTO contact_types (id, label, contact_target) VALUES (104, '5. Autorités juridictionnelles', 'corporate');
INSERT INTO contact_types (id, label, contact_target) VALUES (105, '6. Organisations syndicales', 'corporate');
INSERT INTO contact_types (id, label, contact_target) VALUES (106, '0. Particuliers', 'no_corporate');
INSERT INTO contact_types (id, label, contact_target) VALUES (107, '7. Banques', 'corporate');
INSERT INTO contact_types (id, label, contact_target) VALUES (108, '8. CCI', 'corporate');



INSERT INTO contact_purposes (id, label) VALUES (1, 'Siège social France');
INSERT INTO contact_purposes (id, label) VALUES (2, 'Siège social Sénégal');

INSERT INTO contacts_v2 (contact_id, contact_type, is_corporate_person, society, society_short, firstname, lastname, title, function, other_data, user_id, entity_id, creation_date, update_date, enabled) VALUES (1, 100, 'Y', 'MAARCH', '', '', '', '', '', 'Editeur du logiciel libre Maarch', 'bblier', 'VILLE', '2015-04-24 12:43:54.97424', NULL, 'Y');

INSERT INTO contact_addresses (id, contact_id, contact_purpose_id, departement, firstname, lastname, title, function, occupancy, address_num, address_street, address_complement, address_town, address_postal_code, address_country, phone, email, website, salutation_header, salutation_footer, other_data, user_id, entity_id, is_private, enabled) VALUES (1, 1, 1, '', '', '', '', '', '', '11', 'Boulevard du Sud-Est', '', 'NANTERRE', '', '', '', 'info@maarch.org', 'http://www.maarch.com', '', '', '', 'bblier', 'VILLE', 'N', 'Y');
INSERT INTO contact_addresses (id, contact_id, contact_purpose_id, departement, firstname, lastname, title, function, occupancy, address_num, address_street, address_complement, address_town, address_postal_code, address_country, phone, email, website, salutation_header, salutation_footer, other_data, user_id, entity_id, is_private, enabled) VALUES (2, 1, 2, '', '', '', '', '', '', '', 'Sacré Coeur 3', 'Villa 9653 4ème phase', 'DAKAR', '', 'SENEGAL', '', '', '', '', '', '', 'bblier', 'VILLE', 'N', 'Y');


------------
--DIFFLIST_TYPES--
------------
INSERT INTO difflist_types (difflist_type_id, difflist_type_label, difflist_type_roles, allow_entities, is_system) VALUES ('entity_id', 'Diffusion aux services', 'dest copy', 'Y', 'Y');
INSERT INTO difflist_types (difflist_type_id, difflist_type_label, difflist_type_roles, allow_entities, is_system) VALUES ('type_id', 'Diffusion selon le type de document', 'dest copy', 'Y', 'Y');
INSERT INTO difflist_types (difflist_type_id, difflist_type_label, difflist_type_roles, allow_entities, is_system) VALUES ('foldertype_id', 'Diffusion selon le type de dossiers', 'dest copy', 'Y', 'Y');


-- *************************************************************************************************************************************************************************************************************************** --
-- *************************************************************************************************************************************************************************************************************************** --
-- *************************************************************************************************************************************************************************************************************************** --
-- *************************************************************************************************************************************************************************************************************************** --
-- *************************************************************************************************************************************************************************************************************************** --

-- ************************************************************************* --
--                                                                           --
--                  LETTERBOX DATAS                                          --
--                                                                           --
-- ************************************************************************* --

------------
--USERS--
------------
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('rrenaud', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'Robert', 'RENAUD', '', 'info@maarch.org', '', '0', NULL, NULL, 'f2e8a41dfb14cb10fefe5620efde6f3a', '2012-02-22 16:02:22', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('ccordy', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'Chloé', 'CORDY', '', 'info@maarch.org', '', '0', NULL, NULL, '6cee607907e2f25198dfd0d86676738d', '2012-02-22 16:02:23', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('ssissoko', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'Sylvain', 'SISSOKO', '', 'info@maarch.org', '', '0', NULL, NULL, '85185818fbe92d32f1ab0a06c3d199e2', '2012-02-17 11:02:39', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('nnataliu', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'Nancy', 'NATALY', NULL, 'info@maarch.org', NULL, '0', NULL, NULL, NULL, NULL, 'Y', 'Y', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('ddur', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'Dominique', 'DUR', '', 'info@maarch.org', '', '0', NULL, NULL, 'e599f40bcfe6517f871a298d705a3f58', '2012-02-22 17:02:23', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('jjane', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'Jenny', 'JANE', '', 'info@maarch.org', '', '0', NULL, NULL, '9855381ca9bcf90a1138508d2ddf6316', '2012-02-22 16:02:23', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('eerina', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'Edith', 'ERINA', '', 'info@maarch.org', '', '0', NULL, NULL, '076e854f5044d61a5a1ad6809705613a', '2012-02-17 13:02:18', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('kkaar', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'Katy', 'KAAR', NULL, 'info@maarch.org', NULL, '0', NULL, NULL, NULL, NULL, 'Y', 'Y', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('bboule', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'Bruno', 'BOULE', '', 'info@maarch.org', '', '0', NULL, NULL, '1282a5592996068c04b00c31cc72a6d5', '2012-02-23 08:02:53', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('ppetit', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'Patricia', 'PETIT', '', 'info@maarch.org', '', '0', NULL, NULL, '7212973abc788e0DGA00b9f3a6657e95', '2012-02-23 08:02:53', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('aackermann', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'Amanda', 'ACKERMANN', NULL, 'info@maarch.org', NULL, '0', NULL, NULL, NULL, NULL, 'Y', 'Y', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('ppruvost', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'Pierre', 'PRUVOST', '', 'info@maarch.org', '', '0', NULL, NULL, '48e6b9a881a44cb95e883c4ec4708046', '2012-02-24 15:02:11', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('ttong', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'Tony', 'TONG', '', 'info@maarch.org', '', '0', NULL, NULL, '7ed03f46403a018a58d2d2ff3da8cd85', '2012-02-23 08:02:55', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('sstar', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'Suzanne', 'STAR', '', 'info@maarch.org', '', '0', NULL, NULL, 'e3db0c66afa62e72758c568e1ba4b48e', '2012-02-23 09:02:24', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('ssaporta', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'Sabrina', 'SAPORTA', '', 'info@maarch.org', '', '0', NULL, NULL, '9f576ee66ec17d3af5838f930d2fa9a6', '2012-02-22 16:02:22', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('ccharles', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'Charlotte', 'CHARLES', '', 'info@maarch.org', '', '0', NULL, NULL, '559ff86ca8aa70c456ebed8e45b8ffac', '2012-02-24 11:02:34', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('mmanfred', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'Martin', 'MANFRED', '', 'info@maarch.org', '', '0', NULL, NULL, '989c67fde3ebb43c223d8b270be735f1', '2012-02-28 10:02:10', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('bblier', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'Bernard', 'BLIER', '', 'info@maarch.org', '', '0', NULL, NULL, '79f6b52cdad073bb8d618edcdb1e61fc', '2012-02-28 10:02:22', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('ddaull', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'Denis', 'DAULL', '', 'info@maarch.org', '', '0', NULL, NULL, 'cd06eeaa8b5379f41d7f4bfc32f3688b', '2012-02-28 10:02:06', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('bbain', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'Barbara', 'BAIN', '', 'info@maarch.org', '', '0', NULL, NULL, '4c087c76c038bdd5ba9172834b88e8f5', '2012-02-28 10:02:11', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('jjonasz', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'Jean', 'JONASZ', '', 'info@maarch.org', '', '0', NULL, NULL, '4c087c76c038bdd5ba9172834b88e8f5', '2012-02-28 10:02:11', 'Y', 'N', NULL, 'OK', 'standard', NULL);
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay_number, status, loginmode, docserver_location_id) VALUES ('ggrand', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'George', 'GRAND', '', 'info@maarch.org', '', '0', NULL, NULL, '4c087c76c038bdd5ba9172834b88e8f5', '2012-02-28 10:02:11', 'Y', 'N', NULL, 'OK', 'standard', NULL);

------------
--USERGROUPS--
------------
INSERT INTO usergroups VALUES ('ADMINISTRATEUR', 'Administrateurs fonctionnels', 'N', 'N', 'N', 'N', 'N', 'Y');
INSERT INTO usergroups VALUES ('AGENT', 'Agents', 'N', 'N', 'N', 'N', 'N', 'Y');
INSERT INTO usergroups VALUES ('COURRIER', 'Operateurs de scan', 'N', 'N', 'N', 'N', 'N', 'Y');
INSERT INTO usergroups VALUES ('ELU', 'Elus', 'N', 'N', 'N', 'N', 'N', 'Y');
INSERT INTO usergroups VALUES ('RESP_COURRIER', 'Superviseurs courrier', 'N', 'N', 'N', 'N', 'N', 'Y');
INSERT INTO usergroups VALUES ('RESPONSABLE', 'Responsables de direction', 'N', 'N', 'N', 'N', 'N', 'Y');

------------
--USERGROUP_CONTENT--
------------
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ssissoko', 'RESPONSABLE', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ppruvost', 'AGENT', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ddur', 'ELU', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('eerina', 'AGENT', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ppetit', 'RESPONSABLE', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ssaporta', 'AGENT', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ttong', 'AGENT', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ccharles', 'AGENT', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ccordy', 'AGENT', 'Y', '');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('sstar', 'RESPONSABLE', 'N', '');
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
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('jjonasz', 'AGENT', 'Y', '');

------------
--USERGROUPS_SERVICES--
------------
INSERT INTO usergroups_services VALUES ('RESPONSABLE', 'index_mlb');
INSERT INTO usergroups_services VALUES ('RESPONSABLE', 'view_technical_infos');
INSERT INTO usergroups_services VALUES ('RESPONSABLE', 'add_links');
INSERT INTO usergroups_services VALUES ('RESPONSABLE', 'update_case');
INSERT INTO usergroups_services VALUES ('RESPONSABLE', 'join_res_case_in_process');
INSERT INTO usergroups_services VALUES ('RESPONSABLE', 'add_cases');
INSERT INTO usergroups_services VALUES ('RESPONSABLE', 'update_list_diff_in_details');
INSERT INTO usergroups_services VALUES ('RESPONSABLE', 'admin_templates');
INSERT INTO usergroups_services VALUES ('RESPONSABLE', 'add_new_version');
INSERT INTO usergroups_services VALUES ('RESPONSABLE', 'add_tag_to_res');
INSERT INTO usergroups_services VALUES ('RESPONSABLE', 'view_baskets');
INSERT INTO usergroups_services VALUES ('RESPONSABLE', 'sendmail');
INSERT INTO usergroups_services VALUES ('RESPONSABLE', 'edit_attachments_from_detail');
INSERT INTO usergroups_services VALUES ('RESPONSABLE', 'modify_attachments');
INSERT INTO usergroups_services VALUES ('RESPONSABLE', 'delete_attachments');
INSERT INTO usergroups_services VALUES ('RESPONSABLE', 'adv_search_mlb');
INSERT INTO usergroups_services VALUES ('RESPONSABLE', 'view_doc_history');

INSERT INTO usergroups_services VALUES ('AGENT', 'adv_search_mlb');
INSERT INTO usergroups_services VALUES ('AGENT', 'index_mlb');
INSERT INTO usergroups_services VALUES ('AGENT', 'my_contacts');
INSERT INTO usergroups_services VALUES ('AGENT', 'view_technical_infos');
INSERT INTO usergroups_services VALUES ('AGENT', 'print_details');
INSERT INTO usergroups_services VALUES ('AGENT', 'add_links');
INSERT INTO usergroups_services VALUES ('AGENT', 'view_baskets');
INSERT INTO usergroups_services VALUES ('AGENT', 'update_case');
INSERT INTO usergroups_services VALUES ('AGENT', 'join_res_case');
INSERT INTO usergroups_services VALUES ('AGENT', 'join_res_case_in_process');
INSERT INTO usergroups_services VALUES ('AGENT', 'add_cases');
INSERT INTO usergroups_services VALUES ('AGENT', 'add_copy_in_process');
INSERT INTO usergroups_services VALUES ('AGENT', 'update_list_diff_in_details');
INSERT INTO usergroups_services VALUES ('AGENT', 'folder_search');
INSERT INTO usergroups_services VALUES ('AGENT', 'view_folder_tree');
INSERT INTO usergroups_services VALUES ('AGENT', 'create_folder');
INSERT INTO usergroups_services VALUES ('AGENT', 'add_new_version');
INSERT INTO usergroups_services VALUES ('AGENT', 'tag_view');
INSERT INTO usergroups_services VALUES ('AGENT', 'add_tag_to_res');
INSERT INTO usergroups_services VALUES ('AGENT', 'sendmail');
INSERT INTO usergroups_services VALUES ('AGENT', 'edit_attachments_from_detail');
INSERT INTO usergroups_services VALUES ('AGENT', 'modify_attachments');
INSERT INTO usergroups_services VALUES ('AGENT', 'delete_attachments');

INSERT INTO usergroups_services VALUES ('COURRIER', 'admin');
INSERT INTO usergroups_services VALUES ('COURRIER', 'adv_search_mlb');
INSERT INTO usergroups_services VALUES ('COURRIER', 'index_mlb');
INSERT INTO usergroups_services VALUES ('COURRIER', 'admin_architecture');
INSERT INTO usergroups_services VALUES ('COURRIER', 'my_contacts');
INSERT INTO usergroups_services VALUES ('COURRIER', 'view_technical_infos');
INSERT INTO usergroups_services VALUES ('COURRIER', 'put_in_validation');
INSERT INTO usergroups_services VALUES ('COURRIER', 'add_links');
INSERT INTO usergroups_services VALUES ('COURRIER', 'view_baskets');
INSERT INTO usergroups_services VALUES ('COURRIER', 'update_case');
INSERT INTO usergroups_services VALUES ('COURRIER', 'join_res_case');
INSERT INTO usergroups_services VALUES ('COURRIER', 'join_res_case_in_process');
INSERT INTO usergroups_services VALUES ('COURRIER', 'close_case');
INSERT INTO usergroups_services VALUES ('COURRIER', 'add_cases');
INSERT INTO usergroups_services VALUES ('COURRIER', 'add_copy_in_process');
INSERT INTO usergroups_services VALUES ('COURRIER', 'update_list_diff_in_details');
INSERT INTO usergroups_services VALUES ('COURRIER', 'view_folder_tree');
INSERT INTO usergroups_services VALUES ('COURRIER', '_print_sep');
INSERT INTO usergroups_services VALUES ('COURRIER', 'reports');
INSERT INTO usergroups_services VALUES ('COURRIER', 'add_new_version');
INSERT INTO usergroups_services VALUES ('COURRIER', 'tag_view');
INSERT INTO usergroups_services VALUES ('COURRIER', 'add_tag_to_res');
INSERT INTO usergroups_services VALUES ('COURRIER', 'delete_tag_to_res');
INSERT INTO usergroups_services VALUES ('COURRIER', 'create_tag');
INSERT INTO usergroups_services VALUES ('COURRIER', 'sendmail');


INSERT INTO usergroups_services VALUES ('ELU', 'adv_search_mlb');
INSERT INTO usergroups_services VALUES ('ELU', 'index_mlb');
INSERT INTO usergroups_services VALUES ('ELU', 'view_technical_infos');
INSERT INTO usergroups_services VALUES ('ELU', 'print_details');
INSERT INTO usergroups_services VALUES ('ELU', 'add_links');
INSERT INTO usergroups_services VALUES ('ELU', 'view_baskets');
INSERT INTO usergroups_services VALUES ('ELU', 'add_copy_in_process');
INSERT INTO usergroups_services VALUES ('ELU', 'update_list_diff_in_details');
INSERT INTO usergroups_services VALUES ('ELU', 'reports');
INSERT INTO usergroups_services VALUES ('ELU', 'add_new_version');
INSERT INTO usergroups_services VALUES ('ELU', 'tag_view');
INSERT INTO usergroups_services VALUES ('ELU', 'add_tag_to_res');
INSERT INTO usergroups_services VALUES ('ELU', 'sendmail');

INSERT INTO usergroups_services VALUES ('RESP_COURRIER', 'admin');
INSERT INTO usergroups_services VALUES ('RESP_COURRIER', 'index_mlb');
INSERT INTO usergroups_services VALUES ('RESP_COURRIER', 'view_technical_infos');
INSERT INTO usergroups_services VALUES ('RESP_COURRIER', 'update_case');
INSERT INTO usergroups_services VALUES ('RESP_COURRIER', 'join_res_case_in_process');
INSERT INTO usergroups_services VALUES ('RESP_COURRIER', 'add_cases');
INSERT INTO usergroups_services VALUES ('RESP_COURRIER', 'update_list_diff_in_details');
INSERT INTO usergroups_services VALUES ('RESP_COURRIER', 'admin_templates');
INSERT INTO usergroups_services VALUES ('RESP_COURRIER', 'tag_view');
INSERT INTO usergroups_services VALUES ('RESP_COURRIER', 'view_baskets');
INSERT INTO usergroups_services VALUES ('RESP_COURRIER', 'adv_search_mlb');

INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'admin');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'adv_search_mlb');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'admin_users');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'admin_groups');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'admin_architecture');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'view_history');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'view_history_batch');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'admin_status');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'admin_actions');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'admin_contacts');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'reopen_mail');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'admin_docservers');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'admin_baskets');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'update_case');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'join_res_case');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'join_res_case_in_process');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'close_case');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'manage_entities');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'update_list_diff_in_details');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'admin_templates');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'admin_reports');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'reports');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'add_new_version');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'admin_tag');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'tag_view');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'add_tag_to_res');
INSERT INTO usergroups_services VALUES ('ADMINISTRATEUR', 'delete_tag_to_res');

------------
--ENTITIES--
------------
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('VILLE', 'Ville de Maarch-les-bains', 'Ville de Maarch-les-bains', 'Y', '', '', '', '', '', '', '', '', '', 'Direction');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('CAB', 'CAB:Cabinet du Maire', 'CAB:Cabinet du Maire', 'Y', '', '', '', '', '', '', '', '', 'VILLE', 'Direction');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('ELUS', 'ELUS:Ensemble des élus', 'ELUS:Ensemble des élus', 'Y', '', '', '', '', '', '', '', '', 'VILLE', 'Direction');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('DSI', 'DGSDSI:Direction des Systèmes d''Information', 'DGSDSI:Direction des Systèmes d''Information', 'Y', '', '', '', '', '', '', '', '', 'DGS', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PCU', 'DGSDGAPCU:Pôle Culturel', 'DGSDGAPCU:Pôle Culturel', 'Y', '', '', '', '', '', '', '', '', 'DGA', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PTE', 'DGSPAAPTE:Pôle Technique', 'DGSPAAPTE:Pôle Technique', 'Y', '', '', '', '', '', '', '', '', 'DGA', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PJS', 'DGSDGAPJS:Pôle Jeunesse et Sport', 'DGSDGAPJS:Pôle Jeunesse et Sport', 'Y', '', '', '', '', '', '', '', '', 'DGA', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PE', 'DGSDGAPJSPE:Petite enfance', 'DGSDGAPJSPE:Petite enfance', 'Y', '', '', '', '', '', '', '', '', 'PJS', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('SP', 'DGSDGAPJSSP:Sport', 'DGSDGAPJSSP:Sport', 'Y', '', '', '', '', '', '', '', '', 'PJS', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('COU', 'DGSDSGCOU:Service Courrier', 'DGSDSGCOU:Service Courrier', 'Y', '', '', '', '', '', '', '', '', 'DSG', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('FIN', 'DGSFIN:Direction des Finances', 'DGSFIN:Direction des Finances', 'Y', '', '', '', '', '', '', '', '', 'DGS', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PJU', 'DGSFINPJU:Pôle Juridique', 'DGSFINPJU:Pôle Juridique', 'Y', '', '', '', '', '', '', '', '', 'FIN', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('DGA', 'DGSDGA:Direction Générale Adjointe', 'DGSDGA:Direction Générale Adjointe', 'Y', '', '', '', '', '', '', '', '', 'DGS', 'Bureau');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('DGS', 'DGS:Direction Générale des Services', 'DGS:Direction Générale des Services', 'Y', '', '', '', '', '', '', '', '', 'VILLE', 'Direction');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PSF', 'DGSPSF:Pôle des Services Fonctionnels', 'DGSPSF:Services Fonctionnels', 'Y', '', '', '', '', '', '', '', '', 'DSG', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PSO', 'DGSDGAPSO:Pôle Social', 'DGSDGAPSO:Pôle Social', 'Y', '', '', '', '', '', '', '', '', 'DGA', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('DSG', 'DGSDSG:Secrétariat Général', 'DGSDSG:Secrétariat Général', 'Y', '', '', '', '', '', '', '', '', 'DGS', 'Direction');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('DRH', 'DGSDRH:Direction des Ressources Humaines', 'DGSDRH:Direction des Ressources Humaines', 'Y', '', '', '', '', '', '', '', '', 'DGS', 'Service');
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('COR', 'DGSDSGCOUCOR:Correspondants Archive', 'DGSDSGCOUCOR:Correspondants Archive', 'Y', '', '', '', '', '', '', '', '', 'COU', 'Service');

------------
--USERS_ENTITIES--
------------
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
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('jjane', 'ELUS', 'Elue Petite Enfance    ', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('kkaar', 'DGA', 'Assistante', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('mmanfred', 'DGA', 'Directeur général adjoint', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('nnataliu', 'PSO', 'Responsable pôle social', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('rrenaud', 'DGS', 'Directeur général des services', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ddur', 'ELUS', 'Elue Chargé des sports', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ddaull', 'DSG', 'Directeur secrétariat général', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('bblier', 'COU', 'Responsable courrier', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ssaporta', 'PE', 'Responsable service de la petite enfance', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ttong', 'SP', 'Responsable service des Sports', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('jjonasz', 'PJU', 'Responsable service Juridique', 'Y');

------------
--LISTMODELS--
------------
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'COM', 'entity_id', 0, 'sstar', 'user_id', 'dest', 'DOC', NULL, 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'DRS', 'entity_id', 0, 'bboule', 'user_id', 'dest', 'DOC', NULL, 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'DSP', 'entity_id', 0, 'bbain', 'user_id', 'dest', 'DOC', NULL, 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'COM', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC', NULL, 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'DRS', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC', NULL, 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'DSP', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC', NULL, 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'CAB', 'entity_id', 0, 'ppetit', 'user_id', 'dest', 'DOC', 'CAB:Cabinet du Maire', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'CAB', 'entity_id', 0, 'eerina', 'user_id', 'cc', 'DOC', 'CAB:Cabinet du Maire', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'DSI', 'entity_id', 0, 'ccordy', 'user_id', 'cc', 'DOC', 'DGSDSI:Direction des Systèmes d''Information', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'DSI', 'entity_id', 0, 'ssissoko', 'user_id', 'dest', 'DOC', 'DGSDSI:Direction des Systèmes d''Information', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'PCU', 'entity_id', 0, 'bboule', 'user_id', 'dest', 'DOC', 'DGSDGAPCU:Pôle Culturel', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'PTE', 'entity_id', 0, 'ccharles', 'user_id', 'dest', 'DOC', 'DGSPAAPTE:Pôle Technique', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'PJS', 'entity_id', 0, 'bbain', 'user_id', 'dest', 'DOC', 'DGSDGAPJS:Pôle Jeunesse et Sport', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'COU', 'entity_id', 0, 'bblier', 'user_id', 'dest', 'DOC', 'DGSDSGCOU:Service Courrier et Archive', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'FIN', 'entity_id', 0, 'sstar', 'user_id', 'dest', 'DOC', 'DGSFIN:Direction des Finances', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'FIN', 'entity_id', 0, 'jjane', 'user_id', 'cc', 'DOC', 'DGSFIN:Direction des Finances', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'DGA', 'entity_id', 0, 'mmanfred', 'user_id', 'dest', 'DOC', 'DGSDGA:Direction Générale Adjointe', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'DGA', 'entity_id', 0, 'kkaar', 'user_id', 'cc', 'DOC', 'DGSDGA:Direction Générale Adjointe', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'DGS', 'entity_id', 0, 'rrenaud', 'user_id', 'dest', 'DOC', 'DGS:Direction Générale des Services', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'PE', 'entity_id', 0, 'ssaporta', 'user_id', 'dest', 'DOC', 'DGSDGAPJSPE:Petite enfance', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'PSF', 'entity_id', 0, 'aackermann', 'user_id', 'dest', 'DOC', 'DGSPSF:Pôle des Services Fonctionnels', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'PSO', 'entity_id', 0, 'nnataliu', 'user_id', 'dest', 'DOC', 'DGSDGAPSO:Pôle Social', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'DSG', 'entity_id', 0, 'ddaull', 'user_id', 'dest', 'DOC', 'DGSDSG:Secrétariat Général', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'SP', 'entity_id', 0, 'ttong', 'user_id', 'dest', 'DOC', 'DGSDGAPJSSP:Sport', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'DRH', 'entity_id', 0, 'ppruvost', 'user_id', 'dest', 'DOC', 'DGSDRH:Direction des Ressources Humaines', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'PJU', 'entity_id', 0, 'jjonasz', 'user_id', 'dest', 'DOC', 'DGSFINPJU:Pôle Juridique', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'CAB', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC', 'CAB:Cabinet du Maire', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'DSI', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC', 'DGSDSI:Direction des Systèmes d''Information', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'PCU', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC', 'DGSDGAPCU:Pôle Culturel', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'PTE', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC', 'DGSPAAPTE:Pôle Technique', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'PJS', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC', 'DGSDGAPJS:Pôle Jeunesse et Sport', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'COU', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC', 'DGSDSGCOU:Service Courrier et Archive', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'FIN', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC', 'DGSFIN:Direction des Finances', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'DGA', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC', 'DGSDGA:Direction Générale Adjointe', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'PE', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC', 'DGSDGAPJSPE:Petite enfance', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'PSF', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC', 'DGSPSF:Pôle des Services Fonctionnels', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'PSO', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC', 'DGSDGAPSO:Pôle Social', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'SP', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC', 'DGSDGAPJSSP:Sport', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'DRH', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC', 'DGSDRH:Direction des Ressources Humaines', 'Y');
INSERT INTO listmodels (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible) VALUES ('letterbox_coll', 'PJU', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'DOC', 'DGSFINPJU:Pôle Juridique', 'Y');

------------
--BASKETS--
------------
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, enabled) VALUES ('letterbox_coll', 'SigningBasket', '11 - Courriers à viser', 'Corbeille des courriers à approuver', 'STATUS = ''VIS'' and DESTINATION in (@subentities[@my_entities])', 'N', 'Y', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, enabled) VALUES ('letterbox_coll', 'QualificationBasket', '22 - Courriers à qualifier', 'Corbeille de qualification', 'status=''ATT''', 'N', 'Y', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, enabled) VALUES ('letterbox_coll', 'IndexingBasket', '00 - Courriers à indexer', 'Corbeille d''indexation', ' ', 'N', 'Y', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, enabled) VALUES ('letterbox_coll', 'CopyMailBasket', '03 - Courriers en copie', 'Corbeille d''information', '(res_id in (select res_id from listinstance WHERE coll_id = ''letterbox_coll'' and item_type = ''user_id'' and item_id = @user and item_mode = ''cc'') or res_id in (select res_id from listinstance WHERE coll_id = ''letterbox_coll'' and item_type = ''entity_id'' and item_mode = ''cc'' and item_id in (@my_entities))) and status <> ''DEL'' and res_id not in (select res_id from res_mark_as_read WHERE user_id = @user)', 'N', 'Y', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, enabled) VALUES ('letterbox_coll', 'RetourCourrier', '21 - Retours Courrier', 'Courriers retournés au service Courrier', 'STATUS=''RET''', 'N', 'Y', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, enabled) VALUES ('letterbox_coll', 'InitBasket', '20 - Courriers pour validation DSG', 'Courriers en attente d envoi en validation', 'STATUS=''INIT''', 'N', 'Y', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, enabled) VALUES ('letterbox_coll', 'ContribBasket', '04 - Courrier pour contribution', 'Courrier pour contribution', '(doc_custom_t6 = @user or doc_custom_t7 in (@my_entities)) and (status = ''NEW'' or status = ''COU'')', 'N', 'Y', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, enabled) VALUES ('letterbox_coll', 'ValidationBasket', '00 - Courriers à valider', 'Corbeille de validation', '(status = ''VAL'' and destination <>''COU'')', 'N', 'Y', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, enabled) VALUES ('letterbox_coll', 'MyBasket', '01 - Courriers à traiter', 'Corbeille de traitement', '(status =''NEW'' or status =''COU'') and dest_user = @user and type_id not in (100)', 'N', 'Y', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, enabled) VALUES ('letterbox_coll', 'LateMailBasket', '02 - Courriers en retard', 'Courriers en retard', 'destination in (@my_entities, @subentities[@my_primary_entity]) and (status <> ''DEL'' AND status <> ''REP'') and (now() > process_limit_date)', 'N', 'Y', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, enabled) VALUES ('letterbox_coll', 'DepartmentBasket', '10 - Courriers de ma direction', 'Corbeille de supervision', 'destination in (@my_entities, @subentities[@my_primary_entity]) and (status <> ''DEL'' AND status <> ''REP'' and status <> ''VAL'')', 'N', 'Y', 'Y');

INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'EmailsToQualify', '23 - Courriels à qualifier', 'Courriels à qualifier', 'status=''MAQUAL'' and (dest_user = '''' or dest_user is null)', 'N', 'Y');

INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, is_folder_basket, enabled) VALUES ('letterbox_coll', 'FoldersMyBasket', '[dossier] 90 - Mes dossiers à traiter', 'Dossiers à traiter', 'status = ''FOLDNEW'' and count_document > 0 and dest_user = @user', 'N', 'Y', 'Y', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, is_folder_basket, enabled) VALUES ('letterbox_coll', 'FoldersValidationBasket', '[dossier] 91 - Dossiers à valider', 'Corbeilles des dossiers à valider', 'status = ''FOLDVAL'' and count_document > 0', 'N', 'Y', 'Y', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, is_folder_basket, enabled) VALUES ('letterbox_coll', 'FoldersRejectedBasket', '[dossier] 92 - Dossiers rejetés', 'Corbeille des dossiers rejetés', 'status = ''FOLDREJ'' and count_document > 0', 'N', 'Y', 'Y', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, is_folder_basket, enabled) VALUES ('letterbox_coll', 'FoldersIncompleteBasket', '[dossier] 93 - Dossiers incomplets', 'Corbeille des dossiers incomplets', 'status = ''FOLDNOT'' and count_document > 0', 'N', 'Y', 'Y', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, is_folder_basket, enabled) VALUES ('letterbox_coll', 'FoldersTreatBasket', '[dossier] 94 - Dossiers traités', 'Corbeille des dossiers traités', 'status = ''FOLDTRT'' and count_document > 0', 'N', 'Y', 'Y', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, is_folder_basket, enabled) VALUES ('letterbox_coll', 'FoldersDepartmentBasket', '[dossier] 95 - Dossiers', 'Corbeille de dossiers', 'status = ''FOLDNEW'' and count_document > 0', 'N', 'Y', 'Y', 'Y');

INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, enabled) VALUES ('letterbox_coll', 'AlloMairieBasket', 'Demandes Maarch Mairie', 'Corbeilles des demandes Maarch Mairie', 'status = ''SMART''', 'N', 'Y', 'Y');

------------
--ACTIONS--
------------
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (1, 'redirect', 'Rediriger', '_NOSTATUS_', 'Y', 'Y', 'redirect', 'Y', 'entities', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (2, 'to_validate', 'Valider', 'VAL', 'Y', 'N', 'confirm_status', 'N', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (3, '', 'Renvoyer en qualification', 'INIT', 'N', 'Y', 'confirm_status', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (18, 'indexing', 'Valider courrier', 'NEW', 'N', 'Y', 'validate_mail', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (19, '', 'Traiter courrier', 'COU', 'N', 'Y', 'process', 'N', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (20, '', 'Cloturer', 'END', 'N', 'Y', 'close_mail', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (21, 'indexing', 'Indexation', 'INIT', 'N', 'Y', 'index_mlb', 'Y', 'apps', 'Y');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (22, 'indexing', 'Envoyer pour validation', 'VAL', 'N', 'Y', 'validate_mail', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (100, '', 'Voir le document', '', 'N', 'Y', 'view', 'N', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (101, '', 'Envoyer pour visa', 'VIS', 'N', 'Y', '', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (102, '', 'Viser', 'SIG', 'N', 'Y', '', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (103, '', 'Rejeter', 'UNS', 'N', 'Y', '', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (112, 'indexing', 'Enregistrer un courrier', 'NEW', 'N', 'Y', 'index_mlb', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (113, 'redirect', 'Ajouter en copie', '', 'N', 'Y', 'put_in_copy', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (114, '', 'Retirer le courrier de la corbeille', '', 'N', 'Y', 'mark_as_read', 'Y', 'apps', 'N');

INSERT INTO actions (id, keyword, label_action, id_status, is_system, is_folder_action, enabled, action_page, history, origin, create_id) VALUES (300, 'redirect', '[dossier] Rediriger le dossier', '_NOSTATUS_', 'Y', 'Y', 'Y', 'redirect_folder', 'Y', 'folder', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, is_folder_action, enabled, action_page, history, origin, create_id) VALUES (301, '', '[dossier] Valider le dossier', 'FOLDVAL', 'N', 'Y', 'Y', 'confirm_status', 'Y', 'folder', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, is_folder_action, enabled, action_page, history, origin, create_id) VALUES (302, '', '[dossier] Supprimer le dossier', 'FOLDDEL', 'N', 'Y','Y', 'confirm_status', 'Y', 'folder', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, is_folder_action, enabled, action_page, history, origin, create_id) VALUES (303, '', '[dossier] Rejeter le dossier', 'FOLDREJ', 'N', 'Y', 'Y', 'confirm_status', 'Y', 'folder', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, is_folder_action, enabled, action_page, history, origin, create_id) VALUES (304, '', '[dossier] Completer le dossier', 'FOLDNOT', 'N', 'Y', 'Y', 'confirm_status', 'Y', 'folder', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, is_folder_action, enabled, action_page, history, origin, create_id) VALUES (305, '', '[dossier] Dossier traité', 'FOLDTRT', 'N', 'Y', 'Y', 'confirm_status', 'Y', 'folder', 'N');

------------
--STATUS--
------------
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('MAQUAL', 'Email à qualifier', 'N', '', 'apps', 'Y', 'Y');

INSERT INTO status (id, label_status, is_system, is_folder_status, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('FOLDNEW', '[dossier] Nouveau dossier', 'N', 'Y', 'fm-classification-plan-l1', 'folder', 'N', 'Y');
INSERT INTO status (id, label_status, is_system, is_folder_status, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('FOLDVAL', '[dossier] Dossier validé', 'N', 'Y', 'fm-classification-plan-l1', 'folder', 'N', 'Y');
INSERT INTO status (id, label_status, is_system, is_folder_status, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('FOLDREJ', '[dossier] Dossier rejeté', 'N', 'Y', 'fm-classification-plan-l1', 'folder', 'N', 'Y');
INSERT INTO status (id, label_status, is_system, is_folder_status, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('FOLDTRT', '[dossier] Dossier traité', 'N', 'Y', 'fm-classification-plan-l1', 'folder', 'N', 'Y');
INSERT INTO status (id, label_status, is_system, is_folder_status, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('FOLDNOT', '[dossier] Dossier incomplet', 'N', 'Y', 'fm-classification-plan-l1', 'folder', 'N', 'Y');
INSERT INTO status (id, label_status, is_system, is_folder_status, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('FOLDDEL', '[dossier] Dossier supprimé', 'N', 'Y', 'fm-classification-plan-l1', 'folder', 'N', 'Y');

------------
--GROUPBASKET--
------------
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('AGENT', 'LateMailBasket', 1, NULL, NULL, 'list_with_attachments', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('AGENT', 'CopyMailBasket', 3, NULL, NULL, 'list_copies', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('AGENT', 'MyBasket', 21, NULL, NULL, 'list_with_attachments', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('AGENT', 'ContribBasket', 27, NULL, NULL, 'list_with_attachments', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('AGENT', 'IndexingBasket', 29, NULL, NULL, 'redirect_to_action', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESPONSABLE', 'DepartmentBasket', 14, NULL, NULL, 'list_with_attachments', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESPONSABLE', 'CopyMailBasket', 3, NULL, NULL, 'list_copies', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESPONSABLE', 'SigningBasket', 15, NULL, NULL, 'list_with_attachments', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESPONSABLE', 'MyBasket', 17, NULL, NULL, 'list_with_attachments', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESPONSABLE', 'ContribBasket', 23, NULL, NULL, 'list_with_attachments', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESPONSABLE', 'IndexingBasket', 112, NULL, NULL, 'redirect_to_action', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESP_COURRIER', 'SigningBasket', 4, NULL, NULL, 'list_with_attachments', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESP_COURRIER', 'ValidationBasket', 8, NULL, NULL, 'list_with_attachments', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESP_COURRIER', 'RetourCourrier', 2, NULL, NULL, 'list_with_attachments', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESP_COURRIER', 'CopyMailBasket', 6, NULL, NULL, 'list_copies', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESP_COURRIER', 'MyBasket', 17, NULL, NULL, 'list_with_attachments', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESP_COURRIER', 'IndexingBasket', 112, NULL, NULL, 'redirect_to_action', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('COURRIER', 'InitBasket', 4, NULL, NULL, 'list_with_attachments', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('COURRIER', 'QualificationBasket', 18, NULL, NULL, 'list_with_attachments', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('COURRIER', 'IndexingBasket', 16, NULL, NULL, 'redirect_to_action', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('COURRIER', 'RetourCourrier', 12, NULL, NULL, 'list_with_attachments', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('ELU', 'DepartmentBasket', 1, NULL, NULL, 'list_with_attachments', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('ELU', 'CopyMailBasket', 2, NULL, NULL, 'list_copies', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('ELU', 'MyBasket', 4, NULL, NULL, 'list_with_attachments', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('ELU', 'IndexingBasket', 5, NULL, NULL, 'redirect_to_action', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('COURRIER', 'EmailsToQualify', 33, NULL, NULL, 'list_with_attachments', 'N', 'N', 'N');

INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESPONSABLE', 'FoldersDepartmentBasket', 1, NULL, NULL, 'list_folders', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESPONSABLE', 'FoldersTreatBasket', 2, NULL, NULL, 'list_folders', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESPONSABLE', 'FoldersValidationBasket', 3, NULL, NULL, 'list_folders', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESPONSABLE', 'FoldersRejectedBasket', 4, NULL, NULL, 'list_folders', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESPONSABLE', 'FoldersIncompleteBasket', 5, NULL, NULL, 'list_folders', 'N', 'N', 'N');

INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('COURRIER', 'AlloMairieBasket', 9, NULL, NULL, 'list_with_attachments', 'N', 'N', 'N');

------------
--ACTIONS_GROUPBASKETS--
------------
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (3, '', 'AGENT', 'CopyMailBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (114, '', 'AGENT', 'CopyMailBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (3, '', 'AGENT', 'LateMailBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'AGENT', 'LateMailBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (21, '', 'AGENT', 'LateMailBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'AGENT', 'LateMailBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'AGENT', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (414, '', 'AGENT', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (415, '', 'AGENT', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (1, '', 'AGENT', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'AGENT', 'MyBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'AGENT', 'ContribBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (112, '', 'AGENT', 'IndexingBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'RESPONSABLE', 'CopyMailBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (113, '', 'RESPONSABLE', 'CopyMailBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (114, '', 'RESPONSABLE', 'CopyMailBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'RESPONSABLE', 'ValidAnswerBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (102, '', 'RESPONSABLE', 'SigningBasket', 'Y', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (103, '', 'RESPONSABLE', 'SigningBasket', 'Y', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'RESPONSABLE', 'SigningBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'RESPONSABLE', 'DepartmentBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (3, '', 'RESPONSABLE', 'DepartmentBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'RESPONSABLE', 'DepartmentBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (1, '', 'RESPONSABLE', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'RESPONSABLE', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'RESPONSABLE', 'MyBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (414, '', 'RESPONSABLE', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'RESPONSABLE', 'ContribBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (112, '', 'RESPONSABLE', 'IndexingBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'RESP_COURRIER', 'CopyMailBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (114, '', 'RESP_COURRIER', 'CopyMailBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (21, '', 'RESP_COURRIER', 'RetourCourrier', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (103, '', 'RESP_COURRIER', 'SigningBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (102, '', 'RESP_COURRIER', 'SigningBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'RESP_COURRIER', 'SigningBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'RESP_COURRIER', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (1, '', 'RESP_COURRIER', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (3, '', 'RESP_COURRIER', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'RESP_COURRIER', 'MyBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (414, '', 'RESP_COURRIER', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (3, '', 'RESP_COURRIER', 'ValidationBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (18, '', 'RESP_COURRIER', 'ValidationBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'RESP_COURRIER', 'ValidationBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (112, '', 'RESP_COURRIER', 'IndexingBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (21, '', 'COURRIER', 'RetourCourrier', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (22, '', 'COURRIER', 'QualificationBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (21, '', 'COURRIER', 'IndexingBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'ELU', 'DepartmentBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'ELU', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (1, '', 'ELU', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (3, '', 'ELU', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'ELU', 'MyBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (112, '', 'ELU', 'IndexingBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (18, '', 'COURRIER', 'InitBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (22, '', 'COURRIER', 'EmailsToQualify', 'N', 'N', 'Y');

INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (300, '', 'RESPONSABLE', 'FoldersDepartmentBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (301, '', 'RESPONSABLE', 'FoldersDepartmentBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (302, '', 'RESPONSABLE', 'FoldersDepartmentBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (303, '', 'RESPONSABLE', 'FoldersDepartmentBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (304, '', 'RESPONSABLE', 'FoldersDepartmentBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (305, '', 'RESPONSABLE', 'FoldersDepartmentBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'COURRIER', 'AlloMairieBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (1, '', 'COURRIER', 'AlloMairieBasket', 'Y', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'COURRIER', 'AlloMairieBasket', 'N', 'N', 'Y');

------------
--GROUPBASKET_REDIRECT--
------------
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (100, 'COURRIER', 'QualificationBasket', 21, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (101, 'COURRIER', 'QualificationBasket', 22, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (102, 'COURRIER', 'IndexingBasket', 21, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (103, 'COURRIER', 'RetourCourrier', 21, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (104, 'COURRIER', 'ValidationBasket', 21, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (110, 'AGENT', 'LateMailBasket', 21, '', 'ENTITIES_JUST_BELOW', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (111, 'AGENT', 'MyBasket', 1, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (112, 'AGENT', 'IndexingBasket', 21, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (113, 'AGENT', 'IndexingBasket', 112, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (130, 'RESP_COURRIER', 'ValidationBasket', 18, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (131, 'RESP_COURRIER', 'RetourCourrier', 21, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (132, 'RESP_COURRIER', 'MyBasket', 1, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (133, 'RESP_COURRIER', 'RetourCourrier', 1, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (134, 'RESP_COURRIER', 'IndexingBasket', 112, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (140, 'ELU', 'MyBasket', 1, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (141, 'ELU', 'IndexingBasket', 112, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (150, 'RESPONSABLE', 'MyBasket', 1, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (151, 'RESPONSABLE', 'CopyMailBasket', 113, '', 'ALL_ENTITIES', 'USERS');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (152, 'RESPONSABLE', 'CopyMailBasket', 113, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (153, 'RESPONSABLE', 'IndexingBasket', 21, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (154, 'RESPONSABLE', 'IndexingBasket', 112, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (155, 'COURRIER', 'EmailsToQualify', 22, '', 'ALL_ENTITIES', 'ENTITY');

INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (160, 'RESPONSABLE', 'FoldersDepartmentBasket', 300, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (161, 'RESPONSABLE', 'FoldersDepartmentBasket', 300, '', 'ALL_ENTITIES', 'USERS');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (163, 'COURRIER', 'AlloMairieBasket', 1, '', 'ALL_ENTITIES', 'ENTITY');
------------
--FOLDERTYPES--
------------
INSERT INTO foldertypes (foldertype_id, foldertype_label, maarch_comment, retention_time, custom_d1, custom_f1, custom_n1, custom_t1, custom_d2, custom_f2, custom_n2, custom_t2, custom_d3, custom_f3, custom_n3, custom_t3, custom_d4, custom_f4, custom_n4, custom_t4, custom_d5, custom_f5, custom_n5, custom_t5, custom_d6, custom_t6, custom_d7, custom_t7, custom_d8, custom_t8, custom_d9, custom_t9, custom_d10, custom_t10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, coll_id) 
VALUES (5, 'Les courriers', '', NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'letterbox_coll');

------------
--DOCTYPES_FIRST_LEVEL--
------------
INSERT INTO doctypes_first_level (doctypes_first_level_id, doctypes_first_level_label, css_style, enabled) VALUES (20, 'MAARCH MAIRIE', 'black_style_big', 'Y');
INSERT INTO doctypes_first_level (doctypes_first_level_id, doctypes_first_level_label, css_style, enabled) VALUES (10, 'Courriers des services', 'blue_style_big', 'Y');
ALTER SEQUENCE doctypes_first_level_id_seq RESTART WITH 201;
------------
--DOCTYPES_SECOND_LEVEL--
------------
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (10, 'Autres', 10, 'blue_style', 'Y');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (20, 'Dossiers civils', 10, 'blue_style', 'Y');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (40, 'Dossiers juridiques', 10, 'blue_style', 'Y');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (50, 'Demandes GDD', 20, 'black_style', 'Y');

------------
--DOCTYPES--
------------
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 1, 'Email', 'Y', 10, 10, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 10, 'Appel Téléphonique', 'Y', 10, 10, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 15, 'Divers', 'Y', 10, 10, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 16, 'Demande ', 'Y', 10, 10, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 17, 'Convocation ', 'Y', 10, 10, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 20, 'Invitation', 'Y', 10, 10, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 25, 'Rapport ou compte-rendu', 'Y', 10, 10, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 26, 'Abonnement', 'Y', 10, 10, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 27, 'Offre commerciale', 'Y', 10, 10, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 28, 'Réponse', 'Y', 10, 10, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 30, 'Arrêté municipal', 'Y', 10, 20, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 35, 'Délibération', 'Y', 10, 20, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 40, 'Etat civil', 'Y', 10, 20, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 55, 'Contravention', 'Y', 10, 40, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 60, 'Extrait de main courante', 'Y', 10, 40, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 65, 'Réclamation', 'Y', 10, 40, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 66, 'Litige', 'Y', 10, 40, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 70, 'Contrat', 'Y', 10, 40, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 75, 'Avenant', 'Y', 10, 40, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 76, 'Préavis de grève', 'Y', 10, 40, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 80, 'Demande Divers', 'Y', 20, 50, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 85, 'Demande Environnement', 'Y', 20, 50, NULL, NULL);
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 90, 'Demande Urbanisme', 'Y', 20, 50, NULL, NULL);

INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention) VALUES ('letterbox_coll', 99, 'Demande Maarch Mairie', 'Y', 10, 10, NULL, NULL);
------------
--DOCTYPES_INDEXES--
------------
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (1, 'letterbox_coll', 'custom_t10', 'N');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (1, 'letterbox_coll', 'custom_t11', 'N');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (1, 'letterbox_coll', 'custom_t12', 'N');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (1, 'letterbox_coll', 'custom_t13', 'N');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (1, 'letterbox_coll', 'custom_t14', 'N');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (80, 'letterbox_coll', 'custom_t6', 'N');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (80, 'letterbox_coll', 'custom_t7', 'N');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (85, 'letterbox_coll', 'custom_t6', 'N');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (85, 'letterbox_coll', 'custom_t7', 'N');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (90, 'letterbox_coll', 'custom_t6', 'N');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (90, 'letterbox_coll', 'custom_t7', 'N');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (66, 'letterbox_coll', 'custom_t6', 'N');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (66, 'letterbox_coll', 'custom_t7', 'N');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (17, 'letterbox_coll', 'custom_t6', 'N');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (17, 'letterbox_coll', 'custom_t7', 'N');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (16, 'letterbox_coll', 'custom_t6', 'N');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (16, 'letterbox_coll', 'custom_t7', 'N');

------------
--MLB_DOCTYPES_EXT--
------------
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (1, 21, 14, 1);
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

------------
--TEMPLATES_DOCTYPE_EXT--
------------
INSERT INTO templates_doctype_ext (template_id, type_id, is_generated) VALUES (3, 10, 'Y');

------------
--FOLDERTYPES_DOCTYPES_LEVEL1--
------------
INSERT INTO foldertypes_doctypes_level1 (foldertype_id, doctypes_first_level_id) VALUES (5, 10);
INSERT INTO foldertypes_doctypes_level1 (foldertype_id, doctypes_first_level_id) VALUES (5, 20);
INSERT INTO foldertypes_doctypes_level1 (foldertype_id, doctypes_first_level_id) VALUES (5, 30);

------------
--FOLDERS--
------------
INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (20, 'LITIGE', 5, 0, 'Litiges tous domaines', NULL, NULL, NULL, 'superadmin', 'FOLDNEW', 1, '2012-03-02 18:30:40.311', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:30:40.311');
INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (21, 'LF', 5, 20, 'Litige financier', NULL, NULL, NULL, 'superadmin', 'FOLDNEW', 2, '2012-03-02 18:30:58.112', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:30:58.112');
INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (22, 'LE', 5, 20, 'Litige enfance', NULL, NULL, NULL, 'superadmin', 'FOLDNEW', 2, '2012-03-02 18:31:13.79', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:31:13.79');
INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (23, 'COURRIERS', 5, 0, 'Courriers', NULL, NULL, NULL, 'superadmin', 'FOLDNEW', 1, '2012-03-02 18:31:27.487', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:31:27.487');
INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (24, 'JSP', 5, 0, 'Jeunesse et Sport', NULL, NULL, NULL, 'superadmin', 'FOLDNEW', 1, '2012-03-02 18:32:07.22', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:32:07.22');
INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (25, 'CLUB', 5, 24, 'Clubs et associations sportives', NULL, NULL, NULL, 'superadmin', 'FOLDNEW', 2, '2012-03-02 18:32:24.602', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:32:24.602');
INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (26, 'ETB', 5, 24, 'Etablissements scolaires', NULL, NULL, NULL, 'superadmin', 'FOLDNEW', 2, '2012-03-02 18:32:43.228', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:32:43.228');
INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (27, 'PI', 5, 23, 'Partenariat institutionnel', NULL, NULL, NULL, 'superadmin', 'FOLDNEW', 2, '2012-03-02 18:33:01.543', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:33:01.543');
INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (28, 'LP', 5, 23, 'LaPoste', NULL, NULL, NULL, 'superadmin', 'FOLDNEW', 2, '2012-03-02 18:33:17.705', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:33:17.705');
INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (29, 'PSF', 5, 0, 'POLE SERVICES FONCTIONNELS', NULL, NULL, NULL, 'superadmin', 'FOLDNEW', 1, '2012-03-02 18:32:07.22', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:32:07.22');
INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (30, 'PSF - BUDGET', 5, 29, 'BUDGET', NULL, NULL, NULL, 'superadmin', 'FOLDNEW', 2, '2012-03-02 18:32:07.22', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:32:07.22');

------------
--SECURITY--
------------
INSERT INTO security (security_id, group_id, coll_id, where_clause, maarch_comment, can_insert, can_update, can_delete, rights_bitmask, mr_start_date, mr_stop_date, where_target) VALUES (41, 'ADMINISTRATEUR', 'letterbox_coll', 'DESTINATION in (@my_entities)', 'Administration', 'N', 'N', 'N', 0, NULL, NULL, 'DOC');
INSERT INTO security (security_id, group_id, coll_id, where_clause, maarch_comment, can_insert, can_update, can_delete, rights_bitmask, mr_start_date, mr_stop_date, where_target) VALUES (42, 'COURRIER', 'letterbox_coll', 'typist=@user', 'COURRIER', 'N', 'N', 'N', 153, NULL, NULL, 'DOC');
INSERT INTO security (security_id, group_id, coll_id, where_clause, maarch_comment, can_insert, can_update, can_delete, rights_bitmask, mr_start_date, mr_stop_date, where_target) VALUES (44, 'ELU', 'letterbox_coll', 'DEST_USER=@user', 'ELU', 'N', 'N', 'N', 0, NULL, NULL, 'DOC');
INSERT INTO security (security_id, group_id, coll_id, where_clause, maarch_comment, can_insert, can_update, can_delete, rights_bitmask, mr_start_date, mr_stop_date, where_target) VALUES (45, 'RESPONSABLE', 'letterbox_coll', 'DESTINATION = @my_primary_entity or DESTINATION in (@subentities[@my_primary_entity])', 'RESPONSABLE', 'N', 'N', 'N', 128, NULL, NULL, 'DOC');
INSERT INTO security (security_id, group_id, coll_id, where_clause, maarch_comment, can_insert, can_update, can_delete, rights_bitmask, mr_start_date, mr_stop_date, where_target) VALUES (46, 'RESP_COURRIER', 'letterbox_coll', 'DESTINATION IN (@subentities[@my_entities])', 'Profil responsable', 'N', 'N', 'N', 153, NULL, NULL, 'DOC');
INSERT INTO security (security_id, group_id, coll_id, where_clause, maarch_comment, can_insert, can_update, can_delete, rights_bitmask, mr_start_date, mr_stop_date, where_target) VALUES (47, 'AGENT', 'letterbox_coll', 'DESTINATION in (@my_entities)', 'AGENT', 'N', 'N', 'N', 0, NULL, NULL, 'DOC');
INSERT INTO security (security_id, group_id, coll_id, where_clause, maarch_comment, can_insert, can_update, can_delete, rights_bitmask, mr_start_date, mr_stop_date, where_target) VALUES (49, 'RESP_COURRIER', 'letterbox_coll', 'DESTINATION IN (@subentities[@my_entities])', 'Profil responsable', 'N', 'N', 'N', 153, NULL, NULL, 'DOC');
INSERT INTO security (security_id, group_id, coll_id, where_clause, maarch_comment, can_insert, can_update, can_delete, rights_bitmask, mr_start_date, mr_stop_date, where_target) VALUES (50, 'MODIF', 'letterbox_coll', 'DESTINATION in (@my_entities)', 'modification', 'N', 'N', 'N', 8, NULL, NULL, 'DOC');

--------------------
--KEYWORDS / TAGS --
--------------------
INSERT INTO tags (tag_label, coll_id, res_id) VALUES ('SEMINAIRE', 'letterbox_coll', 0);
INSERT INTO tags (tag_label, coll_id, res_id) VALUES ('INNOVATION', 'letterbox_coll', 0);
INSERT INTO tags (tag_label, coll_id, res_id) VALUES ('MAARCH', 'letterbox_coll', 0);
INSERT INTO tags (tag_label, coll_id, res_id) VALUES ('ENVIRONNEMENT', 'letterbox_coll', 0);
INSERT INTO tags (tag_label, coll_id, res_id) VALUES ('PARTENARIAT', 'letterbox_coll', 0);
INSERT INTO tags (tag_label, coll_id, res_id) VALUES ('JUMELAGE', 'letterbox_coll', 0);
INSERT INTO tags (tag_label, coll_id, res_id) VALUES ('ECONOMIE', 'letterbox_coll', 0);
INSERT INTO tags (tag_label, coll_id, res_id) VALUES ('ASSOCIATIONS', 'letterbox_coll', 0);
INSERT INTO tags (tag_label, coll_id, res_id) VALUES ('RH', 'letterbox_coll', 0);
INSERT INTO tags (tag_label, coll_id, res_id) VALUES ('BUDGET', 'letterbox_coll', 0);
INSERT INTO tags (tag_label, coll_id, res_id) VALUES ('QUARTIERS', 'letterbox_coll', 0);
INSERT INTO tags (tag_label, coll_id, res_id) VALUES ('LITTORAL', 'letterbox_coll', 0);
INSERT INTO tags (tag_label, coll_id, res_id) VALUES ('SPORT', 'letterbox_coll', 0);

------------
--TEMPLATES--
------------
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target) 
VALUES (3, 'AppelTel', 'Appel téléphonique', 
'<p><font size="\\&quot;5\\&quot;"><strong>APPEL TELEPHONIQUE</strong></font></p>
<p><font size="\\&quot;2\\&quot;">Bonjour,</font></p>
<p><font size="\\&quot;2\\&quot;">Vous avez re&ccedil;u un appel t&eacute;l&eacute;phonique dont voici les informations :</font></p>
<ul>
<li><font size="\\&quot;2\\&quot;">Date : </font></li>
<li><font size="\\&quot;2\\&quot;">Heure :</font></li>
<li><font size="\\&quot;2\\&quot;">Soci&eacute;t&eacute; :</font></li>
<li><font size="\\&quot;2\\&quot;">Contact :</font></li>
</ul>
<p><font size="\\&quot;2\\&quot;">Notes : </font></p>',
'HTML', NULL, NULL, '', '', 'doctypes');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target)  
VALUES (2, '[notification] Notifications événement', 'Notifications des événements système', 
'<p><font face="verdana,geneva" size="1">Bonjour [recipient.firstname] [recipient.lastname],</font></p>
<p><font face="verdana,geneva" size="1"> </font></p>
<p><font face="verdana,geneva" size="1">Voici la liste des &eacute###v&eacute###nements de l''application qui vous sont notifi&eacute###s ([notification.description]) :</font></p>
<table style="width: 800px### height: 36px###" border="0" cellspacing="1" cellpadding="1">
<tbody>
<tr>
<td style="width: 150px### background-color: #0099ff###"><font face="verdana,geneva" size="1"><strong><font color="#FFFFFF">Date</font></strong></font></td>
<td style="width: 150px### background-color: #0099ff###"><font face="verdana,geneva" size="1"><strong><font color="#FFFFFF">Utilisateur </font></strong></font><font face="verdana,geneva" size="1"><strong></strong></font></td>
<td style="width: 500px### background-color: #0099ff###"><font face="verdana,geneva" size="1"><strong><font color="#FFFFFF">Description</font></strong></font></td>
</tr>
<tr>
<td><font face="verdana,geneva" size="1">[events.event_date###block=tr###frm=dd/mm/yyyy hh:nn:ss]</font></td>
<td><font face="verdana,geneva" size="1">[events.user_id]</font></td>
<td><font face="verdana,geneva" size="1">[events.event_info]</font></td>
</tr>
</tbody>
</table>', 
'HTML', NULL, NULL, '', 'notif_events', 'notifications');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target)  
VALUES (4, '[notification courrier] Diffusion de courrier en copie', '[notification] Diffusion de courrier en copie', '<p><font face="arial,helvetica,sans-serif" size="2">Bonjour [recipient.firstname] [recipient.lastname],</font></p>
<p> </p>
<p><font face="arial,helvetica,sans-serif" size="2">Voici la liste des nouveaux courriers qui vous ont été envoyés en copie :</font></p>
<table style="border: 1pt solid #000000### width: 1582px### height: 77px###" border="1" cellspacing="1" cellpadding="5" frame="box">
<tbody>
<tr>
<td><font face="arial,helvetica,sans-serif"><strong><font size="2">Référence</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif"><strong><font size="2">Origine</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif"><strong><font size="2">Emetteur</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#000000"><strong>Date</strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#000000"><strong>Objet</strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#000000"><strong>Type</strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#FFFFFF"><strong>Liens</strong></font></td>
</tr>
<tr>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.res_id]</font></td>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.typist]</font></td>
<td>
<p><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.contact_society] [res_letterbox.contact_firstname] [res_letterbox.contact_lastname][res_letterbox.function][res_letterbox.address_num][res_letterbox.address_street][res_letterbox.address_postal_code][res_letterbox.address_town]</font></p>
</td>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.doc_date###block=tr###frm=dd/mm/yyyy]</font></td>
<td><font face="arial,helvetica,sans-serif" color="#FF0000"><strong><font size="2">[res_letterbox.subject]</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.type_label]</font></td>
<td><font face="arial,helvetica,sans-serif"><a href="[res_letterbox.linktodetail]" name="detail">detail</a> <a href="[res_letterbox.linktodoc]" name="doc">Afficher</a></font></td>
</tr>
</tbody>
</table>', 'HTML', NULL, NULL, 'ODP: open_office_presentation', 'letterbox_events', 'notifications');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target)  
VALUES (5, '[notification courrier] Alerte 2', '[notification] Alerte 2', '<p><font face="arial,helvetica,sans-serif" size="2">Bonjour [recipient.firstname] [recipient.lastname],</font></p>
<p> </p>
<p><font face="arial,helvetica,sans-serif" size="2">Voici la liste des courriers dont la date limite de traitement est dépassée :n</font></p>
<table style="border: 1pt solid #000000### width: 1582px### height: 77px###" border="1" cellspacing="1" cellpadding="5" frame="box">
<tbody>
<tr>
<td><font face="arial,helvetica,sans-serif"><strong><font size="2">Référence</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif"><strong><font size="2">Origine</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif"><strong><font size="2">Emetteur</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#000000"><strong>Date</strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#000000"><strong>Objet</strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#000000"><strong>Type</strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#FFFFFF"><strong>Liens</strong></font></td>
</tr>
<tr>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.res_id]</font></td>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.typist]</font></td>
<td>
<p><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.contact_society] [res_letterbox.contact_firstname] [res_letterbox.contact_lastname][res_letterbox.function][res_letterbox.address_num][res_letterbox.address_street][res_letterbox.address_postal_code][res_letterbox.address_town]</font></p>
<p><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.tag_label]</font></p>
</td>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.doc_date###block=tr###frm=dd/mm/yyyy]</font></td>
<td><font face="arial,helvetica,sans-serif" color="#FF0000"><strong><font size="2">[res_letterbox.subject]</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.type_label]</font></td>
<td><font face="arial,helvetica,sans-serif"><a href="[res_letterbox.linktoprocess]" name="traiter">traiter</a> <a href="[res_letterbox.linktodoc]" name="doc">Afficher</a></font></td>
</tr>
</tbody>
</table>', 'HTML', NULL, NULL, 'ODP: open_office_presentation', 'letterbox_events', 'notifications');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target)  
VALUES (6, '[notification courrier] Alerte 1', '[notification] Alerte 1', '<p><font face="arial,helvetica,sans-serif" size="2">Bonjour [recipient.firstname] [recipient.lastname],</font></p>
<p> </p>
<p><font face="arial,helvetica,sans-serif" size="2"> </font></p>
<p> </p>
<p><font face="arial,helvetica,sans-serif" size="2">Voici la liste des courriers toujours en attente de traitement :</font></p>
<p> </p>
<table style="border: 1pt solid #000000### width: 1582px### height: 77px###" border="1" cellspacing="1" cellpadding="5" frame="box">
<tbody>
<tr>
<td><font face="arial,helvetica,sans-serif"><strong><font size="2">Référence</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif"><strong><font size="2">Origine</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif"><strong><font size="2">Emetteur</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#000000"><strong>Date</strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#000000"><strong>Objet</strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#000000"><strong>Type</strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#FFFFFF"><strong>Liens</strong></font></td>
</tr>
<tr>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.res_id]</font></td>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.typist]</font></td>
<td>
<p><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.contact_society] [res_letterbox.contact_firstname] [res_letterbox.contact_lastname][res_letterbox.function][res_letterbox.address_num][res_letterbox.address_street][res_letterbox.address_postal_code][res_letterbox.address_town]</font></p>
<p><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.tag_label]</font></p>
</td>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.doc_date###block=tr###frm=dd/mm/yyyy]</font></td>
<td><font face="arial,helvetica,sans-serif" color="#FF0000"><strong><font size="2">[res_letterbox.subject]</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.type_label]</font></td>
<td><font face="arial,helvetica,sans-serif"><a href="[res_letterbox.linktoprocess]" name="traiter">traiter</a> <a href="[res_letterbox.linktodoc]" name="doc">Afficher</a></font></td>
</tr>
</tbody>
</table>', 'HTML', NULL, NULL, 'ODP: open_office_presentation', 'letterbox_events', 'notifications');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target)  
VALUES (7, '[notification courrier] Diffusion de courrier', '[notification] Diffusion de courrier à traiter', '<p><font face="arial,helvetica,sans-serif" size="2">Bonjour [recipient.firstname] [recipient.lastname],</font></p>
<p> </p>
<p><font face="arial,helvetica,sans-serif" size="2"> </font></p>
<p> </p>
<p><font face="arial,helvetica,sans-serif" size="2">Voici la liste des nouveaux courriers qui vous ont été envoyés :</font></p>
<p> </p>
<table style="border: 1pt solid #000000### width: 1582px### height: 77px###" border="1" cellspacing="1" cellpadding="5" frame="box">
<tbody>
<tr>
<td><font face="arial,helvetica,sans-serif"><strong><font size="2">Référence</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif"><strong><font size="2">Origine</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif"><strong><font size="2">Emetteur</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#000000"><strong>Date</strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#000000"><strong>Objet</strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#000000"><strong>Type</strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#FFFFFF"><strong>Liens</strong></font></td>
</tr>
<tr>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.res_id]</font></td>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.typist]</font></td>
<td>
<p><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.contact_society] [res_letterbox.contact_firstname] [res_letterbox.contact_lastname][res_letterbox.function][res_letterbox.address_num][res_letterbox.address_street][res_letterbox.address_postal_code][res_letterbox.address_town]</font></p>
</td>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.doc_date###block=tr###frm=dd/mm/yyyy]</font></td>
<td><font face="arial,helvetica,sans-serif" color="#FF0000"><strong><font size="2">[res_letterbox.subject]</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.type_label]</font></td>
<td><font face="arial,helvetica,sans-serif"><a href="[res_letterbox.linktodetail]" name="detail">detail</a> <a href="[res_letterbox.linktodoc]" name="doc">Afficher</a></font></td>
</tr>
</tbody>
</table>', 'HTML', NULL, NULL, 'ODP: open_office_presentation', 'letterbox_events', 'notifications');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target)  
VALUES (8, '[notification courrier] Nouvelle annotation', '[notification] Nouvelle annotation', '<p><font face="verdana,geneva" size="2">Bonjour [recipient.firstname] [recipient.lastname], [recipient.text]</font></p>
<p>&nbsp###</p>
<p><font face="verdana,geneva" size="2"> </font></p>
<p>&nbsp###</p>
<p><font face="verdana,geneva" size="2">Voici la liste des notes pour les courriers suivants :</font></p>
<p>&nbsp###</p>
<table style="width: 982px### height: 77px###" border="1" cellspacing="3" cellpadding="3" frame="box">
<tbody>
<tr>
<td><strong>Reference</strong></td>
<td><strong>Num</strong></td>
<td><strong>Date</strong></td>
<td><strong>Objet</strong></td>
<td><strong>Note</strong></td>
<td><strong>Ajout&eacute### par</strong></td>
<td><strong>Contact</strong></td>
<td><strong>Liens</strong></td>
</tr>
<tr>
<td>[notes.identifier]</td>
<td>[notes.# ###frm=0000]</td>
<td>[notes.doc_date###block=tr###frm=dd/mm/yyyy]</td>
<td>[notes.subject]</td>
<td>[notes.note_text]</td>
<td>[notes.user_id]</td>
<td>[notes.contact_society] [notes.contact_firstname] [notes.contact_lastname]</td>
<td><a href="[notes.linktodetail]" name="detail">d&eacute###tail</a> <a href="[notes.linktodoc]" name="doc">doc</a></td>
</tr>
</tbody>
</table>', 'HTML', NULL, NULL, 'ODP: open_office_presentation', 'notes', 'notifications');
INSERT INTO templates VALUES (100, '[maarch mairie] Enregistrement demande - DIVERS', '[maarch mairie] Enregistrement demande - DIVERS', '<p style="text-align: center###"><span style="font-size: small### text-decoration: underline###">ENREGISTREMENT DEMANDE Maarch Mairie - DIVERS</span></p>
<p style="text-align: center###">&nbsp###</p>
<table style="border: 1pt solid #000000### width: 800px### background-color: #40a497###" border="1" cellspacing="1" cellpadding="5">
<tbody>
<tr>
<td style="width: 200px###">DECLARATION DU BESOIN</td>
<td>DATE: [dates]</td>
<td>HEURE: [time]</td>
</tr>
</tbody>
</table>
<table style="border: 1pt solid #000000### width: 800px###" border="1" cellspacing="1" cellpadding="5">
<tbody>
<tr>
<td style="width: 200px### background-color: #40a497###">OBJET</td>
<td style="background-color: #bef3ec###">&nbsp###</td>
</tr>
<tr>
<td style="width: 200px### background-color: #40a497###">DATE ET HEURE ESTIMEES DU DECLENCHEMENT</td>
<td>&nbsp###</td>
</tr>
<tr>
<td style="width: 200px### background-color: #40a497###">IMPLANTATION / LOCALISATION</td>
<td style="background-color: #bef3ec###">&nbsp###</td>
</tr>
<tr>
<td style="width: 200px### background-color: #40a497###">DESCRIPTION</td>
<td>&nbsp###</td>
</tr>
<tr>
<td style="width: 200px### background-color: #40a497###">ETENDUE DU PROBLEME</td>
<td style="background-color: #bef3ec###">&nbsp###</td>
</tr>
<tr>
<td style="width: 200px### background-color: #40a497###">NATURE DU DESAGREMENT</td>
<td>&nbsp###</td>
</tr>
<tr>
<td style="width: 200px### background-color: #40a497###">DELAIS DE TRAITEMENT SOUHAITE</td>
<td style="background-color: #bef3ec###">&nbsp###</td>
</tr>
<tr>
<td style="width: 200px### background-color: #40a497###">AUTRES OBSERVATIONS</td>
<td>&nbsp###</td>
</tr>
</tbody>
</table>', 'HTML', NULL, NULL, 'DOCX: demo_document_msoffice', '', 'doctypes');
INSERT INTO templates VALUES (101, '[maarch mairie] Clôture de demande', '[maarch mairie] Clôture de demande', '<p style="text-align: left###"><span style="font-size: small###">&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###&nbsp###</span><span style="text-decoration: underline###"><span style="font-size: small###">CLOTURE DEMANDE Maarch Mairie - [res_letterbox.type_label] - [res_letterbox.res_id] </span></span></p>
<p style="text-align: center###">&nbsp###</p>
<table style="background-color: #a8c33c### width: 800px### border: #000000 1pt solid###" border="1" cellspacing="1" cellpadding="5">
<tbody>
<tr>
<td style="width: 200px###">CLOTURE&nbsp###DE LA DEMANDE</td>
<td>DATE: [dates]</td>
<td>HEURE: [time]</td>
</tr>
</tbody>
</table>
<table style="width: 800px### border: #000000 1pt solid###" border="1" cellspacing="1" cellpadding="5">
<tbody>
<tr>
<td style="width: 200px### background-color: #a8c33c###">OBJET</td>
<td style="background-color: #e1f787###">&nbsp###[res_letterbox.subject]</td>
</tr>
<tr>
<td style="width: 200px### background-color: #a8c33c###">ACTIONS CONDUITES</td>
<td>&nbsp###</td>
</tr>
<tr>
<td style="width: 200px### background-color: #a8c33c###">DATE DE REMISE EN ETAT / SERVICE</td>
<td style="background-color: #e1f787###">&nbsp###</td>
</tr>
<tr>
<td style="width: 200px### background-color: #a8c33c###">CONSIGNES COMPLEMENTAIRES</td>
<td>&nbsp###</td>
</tr>
<tr>
<td style="width: 200px### background-color: #a8c33c###">AUTRES OBSERVATIONS</td>
<td style="background-color: #e1f787###">&nbsp###</td>
</tr>
<tr>
<td style="width: 200px### background-color: #a8c33c###">&nbsp###</td>
<td>&nbsp###</td>
</tr>
<tr>
<td style="width: 200px### background-color: #a8c33c###">&nbsp###</td>
<td style="background-color: #e1f787###">&nbsp###</td>
</tr>
</tbody>
</table>', 'HTML', NULL, NULL, 'DOCX: demo_document_msoffice', '', 'doctypes');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (102, 'Passer me voir', 'Passer me voir', 'Passer me voir à mon bureau, merci.', 'TXT', NULL, NULL, 'XLSX: demo_spreadsheet_msoffice', '', 'notes', 'all');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (103, 'Compléter', 'Compléter', 'Le projet de réponse doit être complété/révisé sur les points suivants : \n\n- ', 'TXT', NULL, NULL, 'XLSX: demo_spreadsheet_msoffice', '', 'notes', 'all');



------------
--NOTIFICATIONS--
------------
INSERT INTO notifications (notification_sid, notification_id, description, event_id, notification_mode, template_id, diffusion_type, diffusion_properties, attachfor_type, attachfor_properties, rss_url_template) 
VALUES (2, 'USERS', '[administration] Actions sur les utilisateurs de l''application', 'users%', 'EMAIL', 2, 'user', 'superadmin', '', '', 'http://localhost/maarch_entreprise');
INSERT INTO notifications (notification_sid, notification_id, description, event_id, notification_mode, template_id, rss_url_template, diffusion_type, diffusion_properties, attachfor_type, attachfor_properties, is_enabled) 
VALUES (3, 'NCC', 'Nouveaux courriers en copie', '18', 'EMAIL', 4, '', 'copy_list', '', '', '', 'Y');
INSERT INTO notifications (notification_sid, notification_id, description, event_id, notification_mode, template_id, rss_url_template, diffusion_type, diffusion_properties, attachfor_type, attachfor_properties, is_enabled) 
VALUES (4, 'RET2', '2ie alerte sur courriers en retard', 'alert2', 'EMAIL', 5, '', 'dest_user', '', '', '', 'Y');
INSERT INTO notifications (notification_sid, notification_id, description, event_id, notification_mode, template_id, rss_url_template, diffusion_type, diffusion_properties, attachfor_type, attachfor_properties, is_enabled) 
VALUES (5, 'RET1', '1ère alerte sur courriers en retard', 'alert1', 'EMAIL', 6, '', 'dest_user', '', '', '', 'Y');
INSERT INTO notifications (notification_sid, notification_id, description, event_id, notification_mode, template_id, rss_url_template, diffusion_type, diffusion_properties, attachfor_type, attachfor_properties, is_enabled) 
VALUES (6, 'NCT', 'Nouveaux courriers à traiter', '18', 'EMAIL', 7, '', 'dest_user', '', '', '', 'Y');
INSERT INTO notifications (notification_sid, notification_id, description, event_id, notification_mode, template_id, rss_url_template, diffusion_type, diffusion_properties, attachfor_type, attachfor_properties, is_enabled) 
VALUES (7, 'ANC', 'Nouvelle annotation sur courrier en copie', 'noteadd', 'EMAIL', 8, '', 'copy_list', '', '', '', 'Y');
INSERT INTO notifications (notification_sid, notification_id, description, event_id, notification_mode, template_id, rss_url_template, diffusion_type, diffusion_properties, attachfor_type, attachfor_properties, is_enabled) 
VALUES (8, 'AND', 'Nouvelle annotation sur courrier destinataire', 'noteadd', 'EMAIL', 8, '', 'dest_user', '', '', '', 'Y');
INSERT INTO notifications (notification_sid, notification_id, description, event_id, notification_mode, template_id, rss_url_template, diffusion_type, diffusion_properties, attachfor_type, attachfor_properties, is_enabled) 
VALUES (9, 'RED', 'Redirection de courrier', '1', 'EMAIL', 7, '', 'dest_user', '', '', '', 'Y');
------------
--TEMPLATES_ASSOCIATION--
------------
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module) VALUES (20, 3, 'destination', 'VILLE', 'entities');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module) VALUES (21, 3, 'destination', 'CAB', 'entities');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module) VALUES (22, 3, 'destination', 'DGS', 'entities');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module) VALUES (23, 3, 'destination', 'DSI', 'entities');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module) VALUES (24, 3, 'destination', 'FIN', 'entities');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module) VALUES (25, 3, 'destination', 'DGA', 'entities');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module) VALUES (26, 3, 'destination', 'PCU', 'entities');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module) VALUES (27, 3, 'destination', 'PTE', 'entities');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module) VALUES (28, 3, 'destination', 'PJS', 'entities');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module) VALUES (29, 3, 'destination', 'PE', 'entities');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module) VALUES (30, 3, 'destination', 'SP', 'entities');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module) VALUES (31, 3, 'destination', 'PSO', 'entities');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module) VALUES (32, 3, 'destination', 'DSG', 'entities');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module) VALUES (33, 3, 'destination', 'COU', 'entities');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module) VALUES (34, 3, 'destination', 'PSF', 'entities');
INSERT INTO templates_association (system_id, template_id, what, value_field, maarch_module) VALUES (35, 3, 'destination', 'DRH', 'entities');

INSERT INTO templates_association VALUES (100, 101, 'destination', 'CAB', 'entities');
INSERT INTO templates_association VALUES (101, 101, 'destination', 'DGS', 'entities');
INSERT INTO templates_association VALUES (102, 101, 'destination', 'DGA', 'entities');
INSERT INTO templates_association VALUES (103, 101, 'destination', 'PCU', 'entities');
INSERT INTO templates_association VALUES (104, 101, 'destination', 'PJS', 'entities');
INSERT INTO templates_association VALUES (105, 101, 'destination', 'PE', 'entities');
INSERT INTO templates_association VALUES (106, 101, 'destination', 'SP', 'entities');
INSERT INTO templates_association VALUES (107, 101, 'destination', 'PSO', 'entities');
INSERT INTO templates_association VALUES (108, 101, 'destination', 'DRH', 'entities');
INSERT INTO templates_association VALUES (109, 101, 'destination', 'DSG', 'entities');
INSERT INTO templates_association VALUES (110, 101, 'destination', 'COU', 'entities');
INSERT INTO templates_association VALUES (111, 101, 'destination', 'COR', 'entities');
INSERT INTO templates_association VALUES (112, 101, 'destination', 'DSI', 'entities');
INSERT INTO templates_association VALUES (113, 101, 'destination', 'FIN', 'entities');
INSERT INTO templates_association VALUES (114, 101, 'destination', 'PJU', 'entities');
INSERT INTO templates_association VALUES (115, 101, 'destination', 'PTE', 'entities');
INSERT INTO templates_association VALUES (116, 101, 'destination', 'PSF', 'entities');
INSERT INTO templates_association VALUES (117, 101, 'destination', 'ELUS', 'entities');
INSERT INTO templates_association VALUES (118, 101, 'destination', 'VILLE', 'entities');
INSERT INTO templates_association VALUES (119, 101, 'destination', 'CCAS', 'entities');
INSERT INTO templates_association VALUES (120, 101, 'destination', 'AD06', 'entities');

INSERT INTO templates_association VALUES (50, 102, 'destination', 'CAB', 'entities');
INSERT INTO templates_association VALUES (51, 102, 'destination', 'DGS', 'entities');
INSERT INTO templates_association VALUES (52, 102, 'destination', 'DGA', 'entities');
INSERT INTO templates_association VALUES (53, 102, 'destination', 'PCU', 'entities');
INSERT INTO templates_association VALUES (54, 102, 'destination', 'PJS', 'entities');
INSERT INTO templates_association VALUES (55, 102, 'destination', 'PE', 'entities');
INSERT INTO templates_association VALUES (56, 102, 'destination', 'SP', 'entities');
INSERT INTO templates_association VALUES (57, 102, 'destination', 'PSO', 'entities');
INSERT INTO templates_association VALUES (58, 102, 'destination', 'DRH', 'entities');
INSERT INTO templates_association VALUES (59, 102, 'destination', 'DSG', 'entities');
INSERT INTO templates_association VALUES (60, 102, 'destination', 'COU', 'entities');
INSERT INTO templates_association VALUES (61, 102, 'destination', 'COR', 'entities');
INSERT INTO templates_association VALUES (62, 102, 'destination', 'DSI', 'entities');
INSERT INTO templates_association VALUES (63, 102, 'destination', 'FIN', 'entities');
INSERT INTO templates_association VALUES (64, 102, 'destination', 'PJU', 'entities');
INSERT INTO templates_association VALUES (65, 102, 'destination', 'PTE', 'entities');
INSERT INTO templates_association VALUES (66, 102, 'destination', 'PSF', 'entities');
INSERT INTO templates_association VALUES (67, 102, 'destination', 'ELUS', 'entities');
INSERT INTO templates_association VALUES (68, 102, 'destination', 'VILLE', 'entities');
INSERT INTO templates_association VALUES (69, 102, 'destination', 'CCAS', 'entities');
INSERT INTO templates_association VALUES (70, 102, 'destination', 'AD06', 'entities');

INSERT INTO templates_association VALUES (71, 103, 'destination', 'CAB', 'entities');
INSERT INTO templates_association VALUES (72, 103, 'destination', 'DGS', 'entities');
INSERT INTO templates_association VALUES (73, 103, 'destination', 'DGA', 'entities');
INSERT INTO templates_association VALUES (74, 103, 'destination', 'PCU', 'entities');
INSERT INTO templates_association VALUES (75, 103, 'destination', 'PJS', 'entities');
INSERT INTO templates_association VALUES (76, 103, 'destination', 'PE', 'entities');
INSERT INTO templates_association VALUES (77, 103, 'destination', 'SP', 'entities');
INSERT INTO templates_association VALUES (78, 103, 'destination', 'PSO', 'entities');
INSERT INTO templates_association VALUES (79, 103, 'destination', 'DRH', 'entities');
INSERT INTO templates_association VALUES (80, 103, 'destination', 'DSG', 'entities');
INSERT INTO templates_association VALUES (81, 103, 'destination', 'COU', 'entities');
INSERT INTO templates_association VALUES (82, 103, 'destination', 'COR', 'entities');
INSERT INTO templates_association VALUES (83, 103, 'destination', 'DSI', 'entities');
INSERT INTO templates_association VALUES (84, 103, 'destination', 'FIN', 'entities');
INSERT INTO templates_association VALUES (85, 103, 'destination', 'PJU', 'entities');
INSERT INTO templates_association VALUES (86, 103, 'destination', 'PTE', 'entities');
INSERT INTO templates_association VALUES (87, 103, 'destination', 'PSF', 'entities');
INSERT INTO templates_association VALUES (88, 103, 'destination', 'ELUS', 'entities');
INSERT INTO templates_association VALUES (89, 103, 'destination', 'VILLE', 'entities');
INSERT INTO templates_association VALUES (90, 103, 'destination', 'CCAS', 'entities');
INSERT INTO templates_association VALUES (91, 103, 'destination', 'AD06', 'entities');

------------
--DOCSERVERS--
------------
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) VALUES ('FASTHD_MAN', 'FASTHD', 'Fast internal disc bay for letterbox mode', 'N', 'Y', 50000000000, 1, '/opt/maarch/docservers/FASTHD_MAN/', NULL, NULL, NULL, '2011-01-13 14:47:49.197164', NULL, 'letterbox_coll', 10, 'NANTERRE', 2);

INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) 
VALUES ('FASTHD_AI', 'FASTHD', 'Fast internal disc bay for autoimport', 'N', 'Y', 50000000000, 1, '/opt/maarch/docservers/ai/', NULL, NULL, NULL, '2011-01-07 13:43:48.696644', NULL, 'letterbox_coll', 11, 'NANTERRE', 1);

-- *************************************************************************************************************************************************************************************************************************** --
-- *************************************************************************************************************************************************************************************************************************** --
-- *************************************************************************************************************************************************************************************************************************** --
-- *************************************************************************************************************************************************************************************************************************** --
-- *************************************************************************************************************************************************************************************************************************** --

-- ************************************************************************* --
--                                                                           --
--                  VISAS AND E-SIGNATURE                                --
--                                                                           --
-- ************************************************************************* --
-- AJOUT DU TYPE DE LISTE DE DIFFUSION POUR LE CIRCUIT DE VISA
INSERT INTO difflist_types VALUES ('VISA_CIRCUIT', 'Circuit de visa', 'visa sign ', 'N', 'N');

-- AJOUT DES BANNETTES 
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, is_folder_basket, enabled)
 VALUES ('letterbox_coll', 'EvisBasket', '12 - Courriers à e-viser', 'Courriers à e-viser', 'status=''EVIS'' and (res_id,@user) IN (SELECT res_id, item_id FROM listinstance
 	WHERE item_mode = ''visa'' and process_date ISNULL and res_view_letterbox.res_id = res_id order by listinstance_id asc limit 1)', 'N', 'Y', 'N', 'Y');

INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, is_folder_basket, enabled)
 VALUES ('letterbox_coll', 'EsigBasket', '13 - Courriers à e-signer', 'Courriers à e-signer', 'status=''ESIG'' and (res_id,@user) IN (SELECT res_id, item_id FROM listinstance
WHERE item_mode = ''sign'' and process_date ISNULL and res_view_letterbox.res_id = res_id order by listinstance_id asc limit 1)', 'N', 'Y', 'N', 'Y');


INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, is_folder_basket, enabled) VALUES ('letterbox_coll', 'EenvBasket', '14 - Courriers à e-envoyer', 'Courriers à e-envoyer', 'status=''EENV''', 'N', 'Y', 'N', 'Y');

-- AJOUT DES STATUS 
INSERT INTO status (id, label_status, is_system, is_folder_status, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('DIMP', 'Dossier à imprimer', 'N', 'N', 'fm-letter-status-aimp', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, is_folder_status, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('EENV', 'A e-envoyer', 'N', 'N', 'fm-letter-status-aenv', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, is_folder_status, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('ESIG', 'A e-signer', 'N', 'N', 'fm-file-fingerprint', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, is_folder_status, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('EVIS', 'A e-viser', 'N', 'N', 'fm-letter-status-aval', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, is_folder_status, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('PVAL', 'Projet de réponse à valider', 'N', 'N', 'fm-letter-status-aval', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, is_folder_status, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('CVAL', 'Circuit de visa à valider', 'N', 'N', 'fm-file-cycle-policy', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, is_folder_status, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('WAIT', 'En attente  de la réponse signée', 'N', 'N', 'fm-letter-status-wait', 'apps', 'Y', 'Y');

-- AJOUT DES ACTIONS
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (400, '', 'Envoyer le projet de réponse', 'PVAL', 'N', 'Y', '', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (401, '', 'Préparer le circuit de visa', '_NOSTATUS_', 'N', 'Y', 'prepare_visa', 'Y', 'visa', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (402, '', 'Transmettre le circuit de visa', 'CVAL', 'N', 'Y', '', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (403, '', 'Envoyer pour e-visa et signature papier', 'EVIS', 'N', 'Y', '', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (404, '', 'Valider et envoyer pour impression', 'DIMP', 'N', 'Y', '', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (405, '', 'Viser le courrier', '_NOSTATUS_', 'N', 'Y', 'visa_mail', 'Y', 'visa', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (407, '', 'Renvoyer pour traitement', 'COU', 'N', 'Y', 'confirm_status', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (408, '', 'Demander une révision mineure', 'REV', 'N', 'Y', 'confirm_status', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (409, '', 'E-Parapheur à imprimer', 'DIMP', 'N', 'Y', '', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (410, '', 'Transmettre la réponse signée', 'EENV', 'N', 'Y', 'confirm_status', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (411, '', 'Transmettre pour classement', 'CLAS', 'N', 'Y', '', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (412, '', 'Imprimer le dossier', 'WAIT', 'N', 'Y', 'print_folder', 'Y', 'visa', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (413, '', 'E-envoyer un dossier', '_NOSTATUS_', 'N', 'Y', 'send_email', 'Y', 'visa', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (414, '', 'Envoyer pour e-visa et e-signature', '_NOSTATUS_', 'N', 'Y', 'send_to_visa', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (415, '', 'Envoyer pour e-signature', 'ESIG', 'N', 'Y', 'redirect_visa_sign', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (416, '', 'Viser et poursuivre le circuit', '_NOSTATUS_', 'N', 'Y', 'visa_workflow', 'Y', 'visa', 'N');

-- SERVICES POUR VISA
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'config_visa_workflow');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'config_avis_workflow');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'config_visa_workflow');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'config_avis_workflow');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'visa_documents');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'sign_document');

-- AFFECTATION BANNETTES
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESPONSABLE', 'EvisBasket', 13, NULL, NULL, 'list_with_attachments', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('RESPONSABLE', 'EsigBasket', 13, NULL, NULL, 'list_with_attachments', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('COURRIER', 'EenvBasket', 13, NULL, NULL, 'list_with_attachments', 'N', 'N', 'N');
-- ACTIONS
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (408, '', 'RESPONSABLE', 'EvisBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (407, '', 'RESPONSABLE', 'EvisBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (405, '', 'RESPONSABLE', 'EvisBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (416, '', 'RESPONSABLE', 'EvisBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (416, '', 'RESPONSABLE', 'EsigBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (405, '', 'RESPONSABLE', 'EsigBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (408, '', 'RESPONSABLE', 'EsigBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (407, '', 'RESPONSABLE', 'EsigBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (410, '', 'RESPONSABLE', 'EsigBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'COURRIER', 'EenvBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'COURRIER', 'EenvBasket', 'Y', 'N', 'N');
-- BANNETTE SECONDAIRE POUR LE GROUPE DES SUPERVISEURS DE COURRIER

INSERT INTO user_baskets_secondary (system_id, user_id, group_id, basket_id) VALUES (1, 'ddaull', 'RESPONSABLE', 'EvisBasket');

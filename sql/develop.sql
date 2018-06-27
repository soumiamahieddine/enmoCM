-- SQL for the current dev only

UPDATE actions_groupbaskets set used_in_basketlist = 'Y', used_in_action_page = 'Y' WHERE default_action_list = 'Y';
UPDATE actions_groupbaskets set used_in_action_page = 'Y' WHERE used_in_basketlist = 'N' AND used_in_action_page = 'N';
DELETE FROM usergroups_services WHERE service_id = 'view_baskets';

DROP TABLE IF EXISTS contacts_groups;
CREATE TABLE contacts_groups
(
  id serial,
  label character varying(32) NOT NULL,
  description character varying(255) NOT NULL,
  public boolean NOT NULL,
  owner integer NOT NULL,
  entity_owner character varying(32) NOT NULL,
  CONSTRAINT contacts_groups_pkey PRIMARY KEY (id),
  CONSTRAINT contacts_groups_key UNIQUE (label, owner)
)
WITH (OIDS=FALSE);

DROP TABLE IF EXISTS contacts_groups_lists;
CREATE TABLE contacts_groups_lists
(
  id serial,
  contacts_groups_id integer NOT NULL,
  contact_addresses_id integer NOT NULL,
  CONSTRAINT contacts_groups_lists_pkey PRIMARY KEY (id),
  CONSTRAINT contacts_groups_lists_key UNIQUE (contacts_groups_id, contact_addresses_id)
)
WITH (OIDS=FALSE);

/* Docservers */
ALTER TABLE docservers DROP COLUMN IF EXISTS docserver_location_id;
ALTER TABLE docservers DROP COLUMN IF EXISTS ext_docserver_info;
ALTER TABLE docservers DROP COLUMN IF EXISTS chain_before;
ALTER TABLE docservers DROP COLUMN IF EXISTS chain_after;
ALTER TABLE docservers DROP COLUMN IF EXISTS closing_date;
ALTER TABLE docservers DROP COLUMN IF EXISTS enabled;
ALTER TABLE docservers DROP COLUMN IF EXISTS adr_priority_number;
ALTER TABLE docservers DROP COLUMN IF EXISTS priority_number;
ALTER TABLE docservers DROP COLUMN IF EXISTS id;
ALTER TABLE docservers ADD COLUMN id serial;
ALTER TABLE docservers ADD UNIQUE (id);
ALTER TABLE docserver_types DROP COLUMN IF EXISTS is_container;
ALTER TABLE docserver_types DROP COLUMN IF EXISTS container_max_number;
ALTER TABLE docserver_types DROP COLUMN IF EXISTS is_compressed;
ALTER TABLE docserver_types DROP COLUMN IF EXISTS compression_mode;
ALTER TABLE docserver_types DROP COLUMN IF EXISTS is_meta;
ALTER TABLE docserver_types DROP COLUMN IF EXISTS meta_template;
ALTER TABLE docserver_types DROP COLUMN IF EXISTS is_logged;
ALTER TABLE docserver_types DROP COLUMN IF EXISTS log_template;
ALTER TABLE docserver_types DROP COLUMN IF EXISTS is_signed;
DROP TABLE IF EXISTS docserver_locations;
UPDATE docservers set is_readonly = 'Y' WHERE docserver_id = 'FASTHD_AI';

/* Templates */
ALTER TABLE templates_association DROP COLUMN IF EXISTS system_id;
ALTER TABLE templates_association DROP COLUMN IF EXISTS what;
ALTER TABLE templates_association DROP COLUMN IF EXISTS maarch_module;
ALTER TABLE templates_association DROP COLUMN IF EXISTS id;
ALTER TABLE templates_association ADD COLUMN id serial;
ALTER TABLE templates_association ADD UNIQUE (id);
UPDATE templates SET template_content = REPLACE(template_content, '###', ';');
UPDATE templates SET template_content = REPLACE(template_content, '___', '--');

/* Refactoring */
DROP VIEW IF EXISTS af_view_customer_target_view;
DROP VIEW IF EXISTS af_view_customer_view;
DROP VIEW IF EXISTS af_view_year_target_view;
DROP VIEW IF EXISTS af_view_year_view;
DROP TABLE IF EXISTS allowed_ip;
DROP TABLE IF EXISTS af_security;
DROP TABLE IF EXISTS af_view_customer_target;
DROP TABLE IF EXISTS af_view_year_target;
DROP VIEW IF EXISTS res_view;
DROP TABLE IF EXISTS res_x;
DROP TABLE IF EXISTS res_version_x;
DROP TABLE IF EXISTS adr_x;
ALTER TABLE baskets DROP COLUMN IF EXISTS is_generic;
ALTER TABLE baskets DROP COLUMN IF EXISTS except_notif;
ALTER TABLE security DROP COLUMN IF EXISTS can_insert;
ALTER TABLE security DROP COLUMN IF EXISTS can_update;
ALTER TABLE security DROP COLUMN IF EXISTS can_delete;
ALTER TABLE security DROP COLUMN IF EXISTS rights_bitmask;
ALTER TABLE security DROP COLUMN IF EXISTS mr_start_date;
ALTER TABLE security DROP COLUMN IF EXISTS mr_stop_date;
ALTER TABLE security DROP COLUMN IF EXISTS where_target;
ALTER TABLE users DROP COLUMN IF EXISTS ra_code;
ALTER TABLE users DROP COLUMN IF EXISTS ra_expiration_date;

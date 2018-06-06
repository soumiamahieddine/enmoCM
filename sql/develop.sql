-- SQL for the current dev only

UPDATE actions_groupbaskets set used_in_basketlist = 'Y', used_in_action_page = 'Y' WHERE default_action_list = 'Y';
UPDATE actions_groupbaskets set used_in_action_page = 'Y' WHERE used_in_basketlist = 'N' AND used_in_action_page = 'N';

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
ALTER TABLE docservers DROP COLUMN IF EXISTS id;
ALTER TABLE docservers ADD COLUMN id serial;
ALTER TABLE docservers ADD UNIQUE (id);
DROP TABLE IF EXISTS docserver_locations;


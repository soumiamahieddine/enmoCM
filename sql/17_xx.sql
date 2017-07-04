-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 17.06 to 17.XX          --
--                                                                          --
--                                                                          --
-- *************************************************************************--

DROP SEQUENCE IF EXISTS priorities_seq CASCADE;
CREATE SEQUENCE priorities_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;

DROP TABLE IF EXISTS priorities;
CREATE TABLE priorities
(
  id bigint NOT NULL DEFAULT nextval('priorities_seq'::regclass),
  label_priority character varying(128) NOT NULL,
  color_priority character varying(255) DEFAULT NULL::character varying,
  working_days character varying(1) DEFAULT NULL::character varying,
  delays character varying(10) DEFAULT NULL::character varying,
  CONSTRAINT priorities_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);


ALTER TABLE status DROP COLUMN IF EXISTS identifier;
ALTER TABLE status ADD COLUMN identifier serial;

ALTER TABLE users DROP COLUMN IF EXISTS id;
ALTER TABLE users ADD COLUMN id serial;
ALTER TABLE users ADD UNIQUE (id);

ALTER TABLE user_signatures DROP COLUMN IF EXISTS user_serial_id;
ALTER TABLE user_signatures ADD COLUMN user_serial_id integer;
ALTER TABLE user_signatures ADD FOREIGN KEY (user_serial_id) REFERENCES users(id);
UPDATE user_signatures set user_serial_id = (select id FROM users where users.user_id = user_signatures.user_id)
ALTER TABLE user_signatures ALTER COLUMN user_serial_id set not null;
ALTER TABLE user_signatures DROP COLUMN IF EXISTS user_id;

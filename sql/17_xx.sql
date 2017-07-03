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

CREATE SEQUENCE event_stack_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;

CREATE TABLE event_stack
(
  system_id bigint NOT NULL DEFAULT nextval('event_stack_seq'::regclass),
  ta_sid bigint NOT NULL,
  table_name character varying(32) NOT NULL,
  record_id character varying(255) NOT NULL,
  event_date timestamp without time zone NOT NULL,
  exec_date timestamp without time zone NOT NULL,
  exec_result character varying(50) NOT NULL,
  CONSTRAINT event_stack_pkey PRIMARY KEY (system_id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE event_stack OWNER TO postgres;

CREATE SEQUENCE email_stack_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;

CREATE TABLE email_stack
(
  system_id bigint NOT NULL DEFAULT nextval('email_stack_seq'::regclass),
  sender character varying(255) NOT NULL,
  reply_to character varying(255),
  recipient character varying(2000) NOT NULL,
  cc character varying(2000),
  bcc character varying(2000),
  subject character varying(255),
  html_body text,
  text_body text,
  charset character varying(50) NOT NULL,
  exec_date timestamp without time zone,
  exec_result character varying(50),
  CONSTRAINT email_stack_pkey PRIMARY KEY (system_id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE email_stack OWNER TO postgres;

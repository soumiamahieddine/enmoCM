CREATE TABLE templates_association
(
  system_id bigint NOT NULL DEFAULT nextval('templates_association_seq'::regclass),
  template_id bigint NOT NULL,
  what character varying(255) NOT NULL,
  value_field character varying(255) NOT NULL,
  maarch_module character varying(255) NOT NULL DEFAULT 'apps'::character varying,
  description character varying(255),
  diffusion_type character varying(50),
  diffusion_properties character varying(255),
  exclusion_type character varying(50),
  exclusion_properties character varying(255),
  is_attached boolean NOT NULL DEFAULT false,
  CONSTRAINT templates_association_pkey PRIMARY KEY (system_id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE templates_association OWNER TO postgres;


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


CREATE TABLE notification_stack
(
  system_id bigint NOT NULL DEFAULT nextval('notification_stack_seq'::regclass),
  event_sid bigint NOT NULL,
  template_id bigint NOT NULL,
  table_name character varying(32) NOT NULL,
  record_id character varying(255) NOT NULL,
  exec_date timestamp without time zone NOT NULL,
  exec_result character varying(50) NOT NULL,
  CONSTRAINT notification_stack_pkey PRIMARY KEY (system_id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE notification_stack OWNER TO postgres;

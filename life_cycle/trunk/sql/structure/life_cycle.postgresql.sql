CREATE TABLE lc_policies
(
   policy_id character varying(32) NOT NULL, 
   policy_name character varying(255) NOT NULL,
   policy_desc character varying(255) NOT NULL,
   CONSTRAINT lc_policies_pkey PRIMARY KEY (policy_id)
) 
WITH (OIDS = FALSE);


CREATE TABLE lc_cycles
(
   policy_id character varying(32) NOT NULL,
   cycle_id character varying(32) NOT NULL, 
   cycle_desc character varying(255) NOT NULL,
   sequence_number integer NOT NULL,
   where_clause text, 
   validation_mode character varying(32) NOT NULL, 
   CONSTRAINT lc_cycle_pkey PRIMARY KEY (policy_id, cycle_id)
) 
WITH (OIDS = FALSE);

CREATE TABLE lc_cycle_steps
(
   policy_id character varying(32) NOT NULL,
   cycle_id character varying(32) NOT NULL, 
   cycle_step_id character varying(32) NOT NULL, 
   cycle_step_desc character varying(255) NOT NULL,
   docserver_type_id character varying(32) NOT NULL,
   is_allow_failure boolean NOT NULL DEFAULT false,
   coll_id character varying(32) NOT NULL DEFAULT 'coll_1'::character varying,
   step_operation character varying(32) NOT NULL,
   sequence_number integer NOT NULL,
   is_must_complete boolean NOT NULL DEFAULT false,
   preprocess_script character varying(255) DEFAULT NULL, 
   postprocess_script character varying(255) DEFAULT NULL,
   CONSTRAINT lc_cycle_steps_pkey PRIMARY KEY (policy_id, cycle_id, cycle_step_id, docserver_type_id)
) 
WITH (OIDS = FALSE);

CREATE TABLE lc_stack
(
   policy_id character varying(32) NOT NULL,
   cycle_id character varying(32) NOT NULL, 
   cycle_step_id character varying(32) NOT NULL, 
   coll_id character varying(32) NOT NULL,
   res_id bigint NOT NULL, 
   cnt_retry integer DEFAULT NULL, 
   status character(1) NOT NULL,
   work_fields character varying(32),
   CONSTRAINT lc_stack_pkey PRIMARY KEY (policy_id, cycle_id, cycle_step_id, res_id)
) 
WITH (OIDS = FALSE);


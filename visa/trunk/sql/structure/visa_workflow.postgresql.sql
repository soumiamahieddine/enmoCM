ALTER TABLE listinstance ADD process_date timestamp without time zone;
ALTER TABLE listinstance ADD process_comment timestamp without time zone;

ALTER TABLE listinstance_history_details ADD process_date timestamp without time zone;
ALTER TABLE listinstance_history_details ADD process_comment timestamp without time zone;
-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 19.04 to develop                                --
--                                                                          --
--                                                                          --
-- *************************************************************************--
UPDATE parameters SET param_value_string = '19.10.1' WHERE id = 'database_version';

UPDATE users SET status = 'SPD' WHERE enabled = 'N' and (status = 'OK' or status = 'ABS');
ALTER TABLE users DROP COLUMN IF EXISTS enabled;

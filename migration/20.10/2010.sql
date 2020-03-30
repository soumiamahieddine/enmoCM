-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 20.03 to 20.10                                  --
--                                                                          --
--                                                                          --
-- *************************************************************************--
UPDATE parameters SET param_value_string = '20.10' WHERE id = 'database_version';

/*REPORTS*/
DROP TABLE IF EXISTS usergroups_reports;
DELETE FROM usergroups_services WHERE service_id IN ('reports', 'admin_reports');

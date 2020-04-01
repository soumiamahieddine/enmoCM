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

ALTER TABLE users DROP COLUMN IF EXISTS refresh_token;
ALTER TABLE users ADD COLUMN refresh_token jsonb NOT NULL DEFAULT '[]';

DO $$ BEGIN
    IF (SELECT count(column_name) from information_schema.columns where table_name = 'users_email_signatures' and column_name = 'user_id' and data_type != 'integer') THEN
        ALTER TABLE users_email_signatures ADD COLUMN user_id_tmp INTEGER;
        UPDATE users_email_signatures set user_id_tmp = (select id FROM users where users.user_id = users_email_signatures.user_id);
        ALTER TABLE users_email_signatures ALTER COLUMN user_id_tmp set not null;
        ALTER TABLE users_email_signatures DROP COLUMN IF EXISTS user_id;
        ALTER TABLE users_email_signatures RENAME COLUMN user_id_tmp TO user_id;
    END IF;
END$$;

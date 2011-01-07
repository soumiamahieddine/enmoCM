--truncate table res_x
--select creation_date from res_view where creation_date < creation_date::timestamp + '7 day'::interval
--select res_id from res_view where current_date >= creation_date::timestamp + '7 day'::interval
--select res_id from res_view where current_date >= creation_date::timestamp + '3 month'::interval
--select creation_date, creation_date::timestamp + '7 day'::interval as creation_date_new from res_view
delete from res_x where typist = 'AUTOIMPORT';
update res_x set creation_date = creation_date::timestamp - '10 day'::interval where typist = 'AUTOIMPORT';

update res_x set policy_id = 'FNTC', cycle_id = 'INIT' where typist = 'AUTOIMPORT';
truncate table lc_stack;
truncate table adr_x;

-- step 1:
--php fill_stack.php -c config/config.xml -coll lagi_coll -p FNTC -cy OAIS_CACHED
--php process_stack.php -c config/config.xml -coll lagi_coll -p FNTC -cy OAIS_CACHED
--select * from lc_cycle_steps where policy_id = 'FNTC' and cycle_id = 'OAIS_CACHED'
--select * from docservers where docserver_type_id = 'OAIS_SAFE' order by priority_number
--select * from lc_stack where policy_id = 'FNTC' and cycle_id = 'OAIS_CACHED' and cycle_step_id = 'COPY_MAIN' and status = 'I' and coll_id = 'lagi_coll'
--update lc_stack set status = 'P' where policy_id = 'FNTC' and cycle_id = 'OAIS_CACHED' and cycle_step_id = 'COPY_MAIN' and coll_id = 'lagi_coll' and res_id = 130606
--update res_x set path = 't0000011#100105#0311#', filename = '0001.tgz';
--update adr_x set path = '1#'
-- step 2:
--delete from adr_x where docserver_id = 'STS_TARZIP_1';
--php fill_stack.php -c config/config.xml -t res_x -coll lagi_coll -p FNTC -cy STEP_2_OAIS -s STEP_2_OAIS
--php process_stack.php -c config/config.xml -t res_x -coll  lagi_coll -p FNTC -cy STEP_2_OAIS -adr adr_x

--select * from res_x where typist = 'AUTOIMPORT';

--insert into history (table_name, record_id, event_type, user_id, event_date, info, id_module) values ('lc_stack', '7754566', 'ADD', 'LC_BOT', '28/12/2010 10:24:43', 'full stack', 'life_cycle')

select sum(filesize) from res_x where typist = 'AUTOIMPORT';

select * from adr_x where res_id = 7755112;


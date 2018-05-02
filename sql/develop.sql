-- SQL for the current dev only

UPDATE actions_groupbaskets set used_in_basketlist = 'Y', used_in_action_page = 'Y' WHERE default_action_list = 'Y';
UPDATE actions_groupbaskets set used_in_action_page = 'Y' WHERE used_in_basketlist = 'N' AND used_in_action_page = 'N';


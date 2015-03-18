-- AJOUT DU TYPE DE LISTE DE DIFFUSION POUR LE CIRCUIT DE VISA
INSERT INTO difflist_types VALUES ('VISA_CIRCUIT', 'Circuit de visa', 'visa sign ', 'N', 'N');

-- AJOUT DES BANNETTES 
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, is_folder_basket, enabled) VALUES ('letterbox_coll', 'EvisBasket', '[courrier] Courriers à e-viser', 'Courriers à e-viser', 'status=''EVIS'' and (res_id,@user) IN (SELECT DISTINCT ON (res_id)
       res_id, item_id
FROM   listinstance
WHERE item_mode = ''visa'' and process_date ISNULL
ORDER  BY res_id asc)', 'N', 'Y', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, is_visible, is_folder_basket, enabled) VALUES ('letterbox_coll', 'EsigBasket', '[courrier] Courriers à e-signer', 'Courriers à e-signer', 'status=''ESIG'' and (res_id,@user) IN (SELECT DISTINCT ON (res_id)
       res_id, item_id
FROM   listinstance
WHERE item_mode = ''sign'' and process_date ISNULL
ORDER  BY res_id asc)', 'N', 'Y', 'N', 'Y');


-- AJOUT DES STATUS 
INSERT INTO status (id, label_status, is_system, is_folder_status, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('DIMP', 'Dossier à imprimer', 'N', 'N', '', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, is_folder_status, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('EENV', 'A e-envoyer', 'N', 'N', '', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, is_folder_status, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('ESIG', 'A e-signer', 'N', 'N', '', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, is_folder_status, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('EVIS', 'A e-viser', 'N', 'N', '', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, is_folder_status, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('PVAL', 'Projet de réponse à valider', 'N', 'N', '', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, is_folder_status, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('CVAL', 'Circuit de visa à valider', 'N', 'N', '', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, is_folder_status, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('WAIT', 'En attente  de la réponse signée', 'N', 'N', '', 'apps', 'Y', 'Y');

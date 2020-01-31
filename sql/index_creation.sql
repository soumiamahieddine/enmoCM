-- res_letterbox
CREATE INDEX type_id_idx ON res_letterbox (type_id);
CREATE INDEX typist_idx ON res_letterbox (typist);
CREATE INDEX doc_date_idx ON res_letterbox (doc_date);
CREATE INDEX status_idx ON res_letterbox (status);
CREATE INDEX destination_idx ON res_letterbox (destination);
CREATE INDEX dest_user_idx ON res_letterbox (dest_user);
CREATE INDEX res_letterbox_docserver_id_idx ON res_letterbox (docserver_id);
CREATE INDEX res_letterbox_filename_idx ON res_letterbox (filename);
CREATE INDEX res_departure_date_idx ON res_letterbox (departure_date);
CREATE INDEX res_barcode_idx ON res_letterbox (barcode);
CREATE INDEX category_id_idx ON res_letterbox (category_id);

-- res_attachments
CREATE INDEX res_id_idx ON res_attachments (res_id);
CREATE INDEX res_id_master_idx ON res_attachments (res_id_master);
CREATE INDEX res_att_external_id_idx ON res_attachments (external_id);

-- listinstance
CREATE INDEX res_id_listinstance_idx ON listinstance (res_id);
CREATE INDEX sequence_idx ON listinstance (sequence);
CREATE INDEX item_id_idx ON listinstance (item_id);
CREATE INDEX item_type_idx ON listinstance (item_type);
CREATE INDEX item_mode_idx ON listinstance (item_mode);
CREATE INDEX listinstance_difflist_type_idx ON listinstance (difflist_type);

-- contacts
CREATE INDEX firstname_idx ON contacts (firstname);
CREATE INDEX lastname_idx ON contacts (lastname);
CREATE INDEX company_idx ON contacts (society);

-- doctypes_first_level
CREATE INDEX doctypes_first_level_label_idx ON doctypes_first_level (doctypes_first_level_label);

-- doctypes_second_level
CREATE INDEX doctypes_second_level_label_idx ON doctypes_second_level (doctypes_second_level_label);

-- doctypes
CREATE INDEX description_idx ON doctypes (description);

-- entities
CREATE INDEX entity_label_idx ON entities (entity_label);
CREATE INDEX entity_id_idx ON entities (entity_id);
CREATE INDEX entity_folder_import_idx ON entities (folder_import);

-- groupbasket_redirect
CREATE INDEX groupbasket_redirect_group_id_idx ON groupbasket_redirect (group_id);
CREATE INDEX groupbasket_redirect_basket_id_idx ON groupbasket_redirect (basket_id);
-- CREATE INDEX groupbasket_redirect_action_id_idx ON groupbasket_redirect (action_id);
-- CREATE INDEX groupbasket_redirect_entity_id_idx ON groupbasket_redirect (entity_id);

-- history
CREATE INDEX table_name_idx ON history (table_name);
CREATE INDEX record_id_idx ON history (record_id);
CREATE INDEX event_type_idx ON history (event_type);
CREATE INDEX user_id_idx ON history (user_id);

-- notes
CREATE INDEX identifier_idx ON notes (identifier);
CREATE INDEX notes_user_id_idx ON notes (user_id);

-- saved_queries
CREATE INDEX user_id_queries_idx ON saved_queries (user_id);

-- users
CREATE INDEX lastname_users_idx ON users (lastname);

-- listinstance_history_details
CREATE INDEX listinstance_history_id_idx ON listinstance_history_details (listinstance_history_id);

-- res_mark_as_read
CREATE INDEX user_id_res_mark_as_read_idx ON res_mark_as_read (user_id);

-- listmodels
CREATE INDEX object_id_listmodels_idx ON listmodels (object_id);

-- resource_contacts
CREATE INDEX resource_contacts_res_id_idx ON resource_contacts (res_id);

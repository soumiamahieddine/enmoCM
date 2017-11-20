#!/bin/bash
Mypath='/var/www/html/oem_V2/modules/convert/batch'
cd $Mypath
ConfigPath='/var/www/html/oem_V2/modules/convert/batch/config'

rm convert_letterbox_coll_config_only_indexes.lck
rm convert_letterbox_coll_config_only_indexes_error.lck

rm convert_attachments_coll_config_only_indexes.lck
rm convert_attachments_coll_config_only_indexes_error.lck

php $Mypath/fill_stack.php -c $ConfigPath/config_only_indexes.xml -coll letterbox_coll
php $Mypath/fill_stack.php -c $ConfigPath/config_only_indexes.xml -coll attachments_coll
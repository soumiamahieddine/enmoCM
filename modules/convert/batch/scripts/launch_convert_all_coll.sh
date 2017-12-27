#!/bin/bash
Mypath='/var/www/html/oem_V2/modules/convert/batch'
cd $Mypath
ConfigPath='/var/www/html/oem_V2/modules/convert/batch/config'

#rm convert_letterbox_coll_config.lck
#rm convert_letterbox_coll_config_error.lck

php $Mypath/fill_stack.php -c $ConfigPath/config.xml -coll letterbox_coll
php $Mypath/fill_stack.php -c $ConfigPath/config.xml -coll attachments_coll
php $Mypath/fill_stack.php -c $ConfigPath/config.xml -coll attachments_version_coll
php $Mypath/fill_stack.php -c $ConfigPath/config.xml -coll calendar_coll
php $Mypath/fill_stack.php -c $ConfigPath/config.xml -coll folder_coll
php $Mypath/fill_stack.php -c $ConfigPath/config.xml -coll chrono_coll
php $Mypath/fill_stack.php -c $ConfigPath/config.xml -coll reprise_coll

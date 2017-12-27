#!/bin/bash
Mypath='/var/www/html/maarch_oem/modules/convert/batch'
cd $Mypath
ConfigPath='/var/www/html/maarch_oem/modules/convert/batch/config'

rm convert_letterbox_coll_config.lck
rm convert_letterbox_coll_config_error.lck

php $Mypath/fill_stack.php -c $ConfigPath/config.xml -coll letterbox_coll

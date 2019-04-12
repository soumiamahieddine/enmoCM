#!/bin/sh
cd /var/www/html/MaarchCourrier/modules/notifications/batch/
emailStackPath='/var/www/html/MaarchCourrier/modules/notifications/batch/process_email_stack.php'
php $emailStackPath -c /var/www/html/MaarchCourrier/modules/notifications/batch/config/config.xml


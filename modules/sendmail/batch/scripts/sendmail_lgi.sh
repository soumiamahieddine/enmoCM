#!/bin/sh
cd /var/www/html/MaarchCourrier/modules/sendmail/batch/
emailsPath='/var/www/html/MaarchCourrier/modules/sendmail/batch/process_emails.php'
php $emailsPath -c /var/www/html/MaarchCourrier/modules/sendmail/batch/config/config.xml


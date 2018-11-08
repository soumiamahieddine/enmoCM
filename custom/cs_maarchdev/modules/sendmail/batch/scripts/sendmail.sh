#!/bin/bash
cd /var/www/html/maarchdev/modules/sendmail/batch/
emailStackPath='/var/www/html/maarchdev/modules/sendmail/batch/process_emails.php'
php $emailStackPath -c /var/www/html/maarchdev/custom/cs_maarchdev/modules/sendmail/batch/config/config.xml
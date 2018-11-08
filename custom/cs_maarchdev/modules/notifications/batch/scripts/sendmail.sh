#!/bin/bash
cd /var/www/html/maarchdev/modules/notifications/batch/
emailStackPath='/var/www/html/maarchdev/modules/notifications/batch/process_email_stack.php'
php $emailStackPath -c /var/www/html/maarchdev/custom/cs_maarchdev/modules/notifications/batch/config/config.xml
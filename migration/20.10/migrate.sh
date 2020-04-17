#!/bin/sh
php ./migrateModulesConfig.php
php ./migrateNotificationsProperties.php
php ./migrateCustomXml.php # mettre en dernier

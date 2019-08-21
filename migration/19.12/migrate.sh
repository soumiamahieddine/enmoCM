#!/bin/sh
php ./migrateCustomFields.php
php ./migrateFullText.php
php ./migrateIndexing.php
php ./migrateServicesEntities.php
php ./migrateMenuEntities.php

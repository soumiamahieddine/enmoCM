#!/bin/sh
php ./migrateFullText.php
php ./migrateIndexing.php
php ./migrateServicesEntities.php
php ./migrateMenuEntities.php

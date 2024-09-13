<?php

defined( 'TVE_LEADS_DB_UPGRADE' ) or exit();

global $wpdb;

$table = tve_leads_table_name( 'form_variations' );

$wpdb->query( "ALTER TABLE `{$table}` CHANGE COLUMN `content` `content` LONGTEXT NULL COLLATE 'utf8mb4_unicode_ci'" );

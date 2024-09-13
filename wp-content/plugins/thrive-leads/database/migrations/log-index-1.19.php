<?php

defined( 'TVE_LEADS_DB_UPGRADE' ) or exit();
global $wpdb, $tvedb;

$log_table = tve_leads_table_name( 'event_log' );
$wpdb->query( "ALTER TABLE `{$log_table}` ADD INDEX( `date` )" );
$wpdb->query( "ALTER TABLE `{$log_table}` ADD INDEX (  `event_type` ,  `main_group_id` ,  `form_type_id` ,  `variation_key` )" );

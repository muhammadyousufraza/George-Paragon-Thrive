<?php

defined( 'TVE_LEADS_DB_UPGRADE' ) or exit();
global $wpdb, $tvedb;

$form_variations = tve_leads_table_name( 'form_variations' );
$wpdb->query( "ALTER TABLE `{$form_variations}` ADD INDEX( `post_status` )" );
$wpdb->query( "ALTER TABLE `{$form_variations}` ADD INDEX( `post_parent` )" );
$wpdb->query( "ALTER TABLE `{$form_variations}` ADD INDEX( `parent_id` )" );
$wpdb->query( "ALTER TABLE `{$form_variations}` ADD INDEX( `state_order` )" );

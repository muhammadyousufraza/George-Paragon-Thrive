<?php

defined( 'TVE_LEADS_DB_UPGRADE' ) or exit();
global $wpdb;

$form_summary = tve_leads_table_name( 'form_summary' );

$sql = "CREATE TABLE IF NOT EXISTS {$form_summary}(
    `id` INT( 11 ) AUTO_INCREMENT,
    `date` VARCHAR(10) NULL DEFAULT NULL,
    `main_group_id` INT( 11 ) NULL DEFAULT NULL,
    `form_type_id` INT( 11 ) NULL,
    `variation_key` INT( 11 ) NULL,
    `impression_count` INT( 11 ) NULL DEFAULT 0,
    `unique_visitor_count` INT( 11 ) NULL DEFAULT 0,
    `conversion_count` INT( 11 ) NULL DEFAULT 0,
     PRIMARY KEY( `id` ),
     KEY `date` (`date`),
     KEY `variation_key` (`variation_key`)
 )";

$wpdb->query( $sql );

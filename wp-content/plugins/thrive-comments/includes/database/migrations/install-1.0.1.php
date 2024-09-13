<?php
global $wpdb;
$collate = '';

if ( $wpdb->has_cap( 'collation' ) ) {
	$collate = $wpdb->get_charset_collate();
}

$extra = $wpdb->prefix . Thrive_Comments_Constants::DB_PREFIX . 'logs';

$sql = "CREATE TABLE IF NOT EXISTS {$extra} (
	`id` INT NOT NULL AUTO_INCREMENT ,
	`email` VARCHAR(190) NOT NULL ,
	`achievement` VARCHAR(190),
	`extra` VARCHAR(190),
	PRIMARY KEY (`id`),
	INDEX `email_index` (`email`)
) $collate";

$extra = $wpdb->prefix . Thrive_Comments_Constants::DB_PREFIX . 'email_hash';

$sql_email = "CREATE TABLE IF NOT EXISTS {$extra} (
	`id` INT NOT NULL AUTO_INCREMENT ,
	`email` VARCHAR(190) NOT NULL ,
	`email_hash` VARCHAR(190),
	PRIMARY KEY (`id`)
) $collate";

if ( $wpdb->query( $sql ) === false ) {
	return false;
}

if ( $wpdb->query( $sql_email ) === false ) {
	return false;
}

return true;

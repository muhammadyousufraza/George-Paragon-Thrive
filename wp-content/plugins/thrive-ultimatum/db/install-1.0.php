<?php
/**
 * Migration for creating required database tables
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

/** @var TD_DB_Migration $installer */
$installer = $this;

$installer->create_table( 'events', "
	`id` BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`campaign_id` BIGINT(20) UNSIGNED NOT NULL,
	`days` INT(5) UNSIGNED NOT NULL DEFAULT 0,
	`hours` INT(5) UNSIGNED NOT NULL DEFAULT 0,
	`trigger_options` TEXT NULL COLLATE 'utf8_general_ci',
	`actions` TEXT NULL COLLATE 'utf8_general_ci',
	`type` ENUM('time','conv', 'start') NULL DEFAULT 'time'", true );

$installer->create_table( 'designs', "
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `post_parent` BIGINT(20) NOT NULL,
    `post_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
    `post_type` VARCHAR(20) NOT NULL,
    `post_title` TEXT COLLATE 'utf8_general_ci' NOT NULL,
    `content` LONGTEXT COLLATE 'utf8_general_ci' NULL DEFAULT NULL,
    `tcb_fields` LONGTEXT NULL DEFAULT NULL,
    parent_id INT( 11 ) NULL DEFAULT '0'", true );

$installer->create_table( 'settings_templates', "
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `show_options` LONGTEXT NULL,
    `hide_options` LONGTEXT NULL,
    PRIMARY KEY (`id`)" );

$installer->create_table( 'settings_campaign', "
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `campaign_id` BIGINT(20) NOT NULL,
    `description` VARCHAR(255),
    `show_options` LONGTEXT NULL,
    `hide_options` LONGTEXT NULL,
    PRIMARY KEY (`id`)" );

$installer->create_table( 'event_log', "
		`id` BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`campaign_id` BIGINT(20) UNSIGNED NOT NULL,
		`date` DATETIME NULL DEFAULT NULL,
		`type` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0", true );

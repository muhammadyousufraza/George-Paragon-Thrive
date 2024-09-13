<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
/** @var TD_DB_Migration $installer */
$installer = $this;
$installer->create_table( 'tests', "
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`page_id` BIGINT(20) UNSIGNED NOT NULL ,
	`title` TEXT NOT NULL,
	`status` ENUM('running','completed','archived') NOT NULL DEFAULT 'running',
	`auto_win_enabled` TINYINT UNSIGNED NOT NULL DEFAULT '1',
	`auto_win_min_conversions` INT UNSIGNED NOT NULL DEFAULT '100',
	`auto_win_min_duration` TINYINT UNSIGNED NOT NULL DEFAULT '14',
	`auto_win_chance_original` DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT '95.00',
	`notes` TEXT NOT NULL,
	`type` VARCHAR(20) NOT NULL DEFAULT 'monetary',
	`date_added` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`date_started` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`date_completed` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`id`),
	INDEX `idx_page_id` (`page_id`)
", true );

$installer->create_table( 'test_items', "
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`page_id` BIGINT(20) UNSIGNED NOT NULL,
	`variation_id` BIGINT(20) UNSIGNED NOT NULL,
	`test_id` BIGINT(20) UNSIGNED NOT NULL,
	`title` TEXT NOT NULL,
	`is_control` TINYINT UNSIGNED NOT NULL DEFAULT '0',
	`is_winner` TINYINT UNSIGNED NOT NULL DEFAULT '0',
	`impressions` INT UNSIGNED  NOT NULL DEFAULT '0',
	`unique_impressions` INT UNSIGNED  NOT NULL DEFAULT '0',
	`conversions` INT UNSIGNED  NOT NULL DEFAULT '0',
	`revenue` DECIMAL(10,2) UNSIGNED  NOT NULL DEFAULT '0.0',
	`active` TINYINT UNSIGNED NOT NULL DEFAULT '1',
	`stopped_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`id`),
	INDEX `idx_page_id` (`page_id`),
	INDEX `idx_variation_id` (`variation_id`),
	INDEX `idx_test_id` (`test_id`)", true );

$installer->create_table( 'event_log', "
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`page_id` BIGINT(20) UNSIGNED NOT NULL,
	`variation_id` BIGINT(20) UNSIGNED NOT NULL,
	`test_id` BIGINT(20) UNSIGNED NOT NULL,
	`date` DATETIME NULL,
	`event_type` TINYINT( 2 ),
	PRIMARY KEY (`id`),
	INDEX `idx_page_id` (`page_id`),
	INDEX `idx_variation_id` (`variation_id`),
	INDEX `idx_test_id` (`test_id`)", true );

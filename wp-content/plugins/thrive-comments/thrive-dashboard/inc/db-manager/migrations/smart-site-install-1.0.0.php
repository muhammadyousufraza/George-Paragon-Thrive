<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

global $wpdb;

/** @var $this TD_DB_Migration */
$this->create_table( 'td_groups', '
    `id` INT( 11 ) AUTO_INCREMENT,
     `name` TEXT NOT NULL,
     `is_default` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
     PRIMARY KEY( `id` )' );

$this->create_table( 'td_fields', '
    `id` INT( 11 ) AUTO_INCREMENT,
    `group_id` INT( 11 ) NOT NULL,
     `name` TEXT NOT NULL,
     `type` INT NOT NULL,
     `data` TEXT NULL,
     `is_default` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
     PRIMARY KEY( `id` )' );

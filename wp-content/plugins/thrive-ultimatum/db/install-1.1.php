<?php
/**
 * Migration for creating required table for emails log
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

/** @var TD_DB_Migration $installer */
$installer = $this;

$installer->create_table('emails', "
    `id` BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `campaign_id` BIGINT(20) UNSIGNED NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `started` DATETIME NOT NULL,
    `type` VARCHAR(255) NOT NULL DEFAULT 'url',
    `end` TINYINT UNSIGNED NOT NULL DEFAULT 0");

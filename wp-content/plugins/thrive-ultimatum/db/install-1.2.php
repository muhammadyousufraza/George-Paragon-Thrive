<?php
/**
 * Migration for creating required table for emails log
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

/** @var TD_DB_Migration $installer */
$installer = $this;

$installer->add_or_modify_column( 'emails', 'has_impression', 'TINYINT UNSIGNED NOT NULL DEFAULT 0' );

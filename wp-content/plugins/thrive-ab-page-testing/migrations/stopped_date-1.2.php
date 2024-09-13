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

$installer->add_or_modify_column( 'test_items', 'stopped_date', "DATETIME NULL DEFAULT '0000-00-00 00:00:00'" );

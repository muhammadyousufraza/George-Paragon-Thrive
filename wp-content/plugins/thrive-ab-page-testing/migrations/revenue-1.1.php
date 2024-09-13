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

$installer->add_or_modify_column( 'event_log', 'revenue', "DECIMAL(10,2 ) NOT NULL DEFAULT '0' AFTER `event_type`" )
          ->add_or_modify_column( 'event_log', 'goal_page', "BIGINT(20) NULL DEFAULT NULL AFTER `revenue`" )
          ->add_or_modify_column( 'tests', 'goal_pages', "LONGTEXT NULL DEFAULT NULL" );

<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

/** @var $this TD_DB_Migration */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

$this->add_or_modify_column( 'td_fields', 'identifier', 'VARCHAR(32) NULL DEFAULT NULL AFTER `id`' );

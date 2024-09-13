<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/** @var $this TD_DB_Migration */
$this->add_or_modify_column( 'access_history', 'reason', 'TINYINT NULL DEFAULT NULL' );

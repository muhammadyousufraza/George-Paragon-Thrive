<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/** @var $this TD_DB_Migration $questions */
$this->add_or_modify_column( 'users', 'date_finished', 'DATETIME DEFAULT NULL AFTER date_started' );

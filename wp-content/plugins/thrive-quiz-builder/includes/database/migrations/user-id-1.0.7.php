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
$this->add_or_modify_column( 'users', 'wp_user_id', 'BIGINT NOT NULL;' );
$this->add_or_modify_column( 'users', 'object_id', 'BIGINT NOT NULL;' );
$this->create_index( 'IDX_users_wp_user_id', 'users', 'wp_user_id' );

<?php
/**
 * Thrive Themes  https://thrivethemes.com
 *
 * @package thrive-graph-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/** @var $this TD_DB_Migration $questions */

$answers = tge_table_name( 'answers' );
$this->add_or_modify_column( $answers, 'is_right', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER `points`' );
